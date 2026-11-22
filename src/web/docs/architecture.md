# Architecture

HatShop is an HTML-native storefront on **Cloudflare Workers Free**, reading and
writing the **existing Neon PostgreSQL database** through **Hyperdrive**. The PHP
application in `src/hatshop/` is untouched and remains the reference.

```
Browser
  │  semantic HTML, one small ES module, native form validation
  ▼
Cloudflare Worker (Workers Free: 100k req/day, 10 ms CPU, 128 MB)
  ├── /                 server-rendered HTML from database functions
  ├── /products/:id     …
  ├── POST /cart        cart mutations (one plpgsql call each)
  ├── POST /checkout    order → authorize → capture (Authorize.Net sandbox)
  ├── /admin/*          role-gated administration
  ├── /health           database probe for operations
  └── static            public/ served by the asset binding, no Worker CPU
  ▼
Hyperdrive (Free: 100k queries/day) → Neon PostgreSQL 17.12 (aws-us-east-2)
```

## The central decision: call the database, do not reimplement it

The live database contains 75 plpgsql functions and 32 composite types that
implement catalog listing, pagination, full-text search, cart arithmetic, order
creation, order status and audit trails. `services/*` orchestrates those calls;
`db/queries.ts` contains every SQL statement in the application.

That keeps three things true at once: business behaviour matches the PHP
application, Worker CPU stays tiny (work happens in PostgreSQL), and there is one
place to review SQL.

## Where CPU goes (the Free plan's real constraint)

Free Workers get **10 ms CPU per request**; waiting on the database is not CPU
time. Consequences:

- Pages are server-rendered HTML strings, not a client framework. Rendering a
  product grid is string concatenation over ≤ 6 rows.
- The catalog functions truncate descriptions in SQL, so no large payload is
  parsed in the isolate.
- Static assets never enter the Worker.
- Password hashing is the only genuinely expensive operation; see below.

## Password storage: measured, not assumed

Live facts (see `schema-inventory.md`): `customer.password` is `VARCHAR(50)` and
the single existing row is a 40-hex **SHA-1** digest. The current PHP code writes
SHA-256 (64 chars), which does not fit that column — the live data and the PHP
code have already diverged.

Measured PBKDF2-SHA256 cost on the development machine (V8, `npm run bench`):

| Iterations | Median | Verdict against 10 ms CPU |
| --- | --- | --- |
| 100,000 | 10.17 ms | over budget on its own |
| 210,000 | 21.16 ms | over budget |
| 600,000 | 59.49 ms | far over budget |

So a modern KDF is **not** viable on Workers Free for this schema, and it cannot
be stored anyway. The application therefore:

1. Verifies legacy SHA-1 **and** SHA-256 digests, so the existing account can sign
   in (`auth/password.ts`).
2. Supports three explicit policies via `PASSWORD_STORAGE_POLICY`:
   - `disabled` (default) — registration and password change refuse, explaining
     why, rather than silently writing a weak digest;
   - `sha1-legacy` — 40 chars, fits `VARCHAR(50)`, documented as a compromise;
   - `pbkdf2` — only valid after migration 002 widens the column.
3. Never breaks a login because an upgrade failed: upgrading is best-effort and
   gated behind the policy.

The alternative that would cost ~0 Worker CPU — `pgcrypto` in PostgreSQL
(`crypt`/`gen_salt`) — is recorded as an option, not applied: the extension is
not installed.

## Sessions

- HMAC-SHA256 signed cookie (`hatshop_sid`), `HttpOnly`, `Secure` on HTTPS,
  `SameSite=Lax`, `Max-Age=28800`.
- **Renewal semantics:** each token carries `iat`/`exp` with `exp = iat + 8 h`.
  Once a presented token is **30 minutes old**, the response re-issues a *new*
  token (new `iat`, new `exp`), so a session in continuous use keeps a rolling
  8-hour window. An idle token is never renewed and dies at its own `exp`.
  Expired tokens are also cleared from the browser on the next request.
- The `fp` claim is a fingerprint of the stored password digest, so a password
  change invalidates every issued session with no server state.
- The `ep` claim carries a session epoch, consulted only when
  `SESSION_EPOCH_ENABLED=1`, which requires the reviewed (unapplied) migration
  001.
- **Residual risk, documented and demonstrated:** without migration 001 a copied
  cookie stays valid until it expires; logout clears the cookie but cannot revoke a
  copy. `scripts/verify-interactive.mjs` asserts exactly this (the copied
  pre-logout token still returns 200), so the limitation is visible rather than
  assumed.
- No KV, no session table: neither is needed for the behaviour above.

## What has been proven, and how

Unit tests (`npm test`, 70 tests) cover the logic with a fake `Db`. The
security-critical behaviour is additionally proven against a **running Worker and
the live database** by `scripts/verify-interactive.mjs` →
[`live-verification.md`](live-verification.md) (100 checks, read-only SQL):

| Behaviour | Proof |
| --- | --- |
| Cookie flags | served `Set-Cookie` over HTTPS: `HttpOnly`, `Secure`, `SameSite=Lax`, `Max-Age=28800` |
| Session integrity | tampered signature, swapped payload, garbage, wrong key, expired and wrong-fingerprint cookies each redirect `/account` → `/login` |
| Sliding renewal | an aged token is replaced by a token with a later `iat` and a fresh 8 h `exp`; a fresh token is not replaced |
| Logout | clears the cookie (`Max-Age=0`), cleared value no longer authorizes, **copied** value still does |
| CSRF | missing token, wrong token, foreign `Origin`, `Sec-Fetch-Site: cross-site` → 403 for `/cart` (authenticated admin POSTs included) with table counts unchanged |
| Admin gate | unauthenticated `/admin*` GETs and `/admin/action` POST → 303 `/admin/login` with an empty body; authenticated POSTs without CSRF → 403 |
| Checkout refusal | unconfigured gateway → 503 with `orders`, `order_detail`, `shopping_cart`, `audit` counts byte-identical |
| Markup contract | required ids/classes/form fields/action URLs asserted on served HTML (and by `tests/template-contract.test.ts` offline) |

**Legacy sign-in is proven against the live account.** The owner approved a
temporary password reset for the single live customer, which made the real
`POST /login` path exercisable: 303 → `/account`, 200 with that customer's live
name, email and profile form, and a cookie whose `exp - iat` is exactly 8 h. The
original 40-hex SHA-1 digest was then written back and verified byte for byte, and
a probe confirmed the temporary password no longer signs in (401).
`scripts/temp-password-reset.mjs` is the only script that writes customer data;
it backs the original digest up locally (git-ignored), refuses to run twice, and
verifies the restore before deleting the backup.

**Still unverified:** browser-level visual parity against the PHP templates. No
browser automation was installed (it cannot install cleanly here), so the contract
above is structural — ids, classes, form fields, action URLs and links — not
pixel-level.

## Security posture

- All SQL is parameterized with explicit casts; no string interpolation of values.
- CSRF: double-submit token **and** Origin/`Sec-Fetch-Site` checks on every POST.
- XSS: every interpolated value passes through `e()`; the CSP forbids inline
  script and style, so a template bug cannot execute inline JS.
- Card data goes browser → Worker → gateway and is never stored; only the
  provider's transaction id and auth code are persisted.
- Authorization is always server-side: admin routes check the admin claim, order
  pages check ownership by looking the order up in the customer's own list.
- Error responses are generic; details go to `console.error` only.
- `ADMIN_PASSWORD_HASH` is a SHA-256 digest, so the plaintext admin password is
  not stored anywhere.

## Free-tier accounting

| Service | Free allowance | Use here |
| --- | --- | --- |
| Workers requests | 100,000/day | one request per page or action |
| Workers CPU | 10 ms/request | static-free pages; rendering is string work |
| Workers memory | 128 MB/isolate | no large buffers; `pg` client only |
| Worker bundle | 64 MiB uncompressed | ~1.3 MB including `pg` |
| Static assets | free, unlimited requests | CSS, JS, 91 product images |
| Hyperdrive | 100,000 queries/day | 1–4 queries per page |
| Cron Triggers | 5/account | not used |
| R2 | 10 GB, 1M Class A | **not used**; uploads disabled pending approval |
| Email | none native | order notification emails are not sent |

No paid service is required by this architecture. R2 and email would each add a
dependency and are deliberately left off.

## Deliberate omissions

- No framework, no template engine, no build step beyond Wrangler's bundler.
- No Docker, no Node server: the runtime is `workerd`.
- No D1 and no database migration: Neon stays the system of record.
- No image uploads yet: Workers have no writable filesystem and R2 is a gate.
- The PHP application is not deleted; it remains the behavioural reference.
