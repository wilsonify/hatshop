# Architecture: decisions and why

This is the decision record for the Worker rewrite. What the system must do lives
in `openspec/specs/`; what was observed against a running deployment lives in
`live-verification.md` and `hyperdrive-verification.md`. This file explains the
choices behind both, and what was deliberately left out.

The storefront is server-rendered HTML on Cloudflare Workers Free, reading and
writing the existing Neon PostgreSQL database through Hyperdrive. The PHP
application in `src/hatshop/` is untouched and remains the behavioural reference.

## Call the database, do not reimplement it

The schema already implements catalog listing, pagination, full-text search, cart
arithmetic, order creation, order status and the audit trail as stored procedures.
The Worker orchestrates those calls; every SQL statement in the application lives
in one module.

Three things stay true that way. Behaviour matches the PHP application, because the
rules were never re-expressed. Worker CPU stays tiny, because the work happens in
PostgreSQL. And there is one place to review SQL, which matters more than elegance
when the database is also the production system of record.

The alternative — porting the rules into TypeScript — would have created a second
implementation to keep in sync with a database the PHP application still writes to.

## Where CPU goes (the Free plan's real constraint)

Free Workers allow 10 ms of CPU per request. Waiting on the database is not CPU
time, which is why the previous decision is affordable. The consequences that
shaped the code:

- Pages are HTML strings, not a client framework. Rendering a grid is string
  concatenation over one page of rows.
- The catalog functions truncate descriptions in SQL, so no large payload is parsed
  in the isolate.
- Static assets never enter the Worker; the asset binding serves them.
- Password hashing is the only genuinely expensive operation, and it decided the
  next section.

## Password storage: measured, not assumed

Two facts collided. The live `customer.password` column is `VARCHAR(50)`, and the
single existing row holds a 40-hex SHA-1 digest — while the current PHP code writes
SHA-256, which does not fit. Live data and PHP code had already diverged.

Measured PBKDF2-SHA256 cost on the development machine (`npm run bench`):

| Iterations | Median | Verdict against 10 ms CPU |
| --- | --- | --- |
| 100,000 | 10.17 ms | over budget on its own |
| 210,000 | 21.16 ms | over budget |
| 600,000 | 59.49 ms | far over budget |

A modern KDF is therefore not viable on Workers Free for this schema, and its
output would not fit the column anyway. Rather than pick one bad option silently,
the application:

1. Verifies legacy SHA-1 **and** SHA-256 digests, so the existing account can sign
   in.
2. Exposes the storage question as a policy: refuse to store new passwords by
   default, allow the legacy-compatible digest when asked, or use PBKDF2 once the
   column is widened.
3. Never breaks a sign-in because an upgrade failed; upgrading is best-effort and
   gated behind the policy.

`pgcrypto` inside PostgreSQL — `crypt`/`gen_salt`, at roughly zero Worker CPU —
would be the better answer if the extension were available. It is recorded as an
option, not applied: the extension is not installed, and installing it is a
database change with its own approval gate.

## Sessions: signed cookies instead of server state

Workers have no session store, and adding one (KV or a table) buys little for a
shop this size. The design that follows from that:

- A signed token carries the customer, the issue and expiry times, a fingerprint of
  the stored password digest, and a role. Nothing is looked up to authenticate a
  request beyond verifying the signature.
- The fingerprint is what makes a password change invalidate every issued session
  with no server state; it is a decision, not an accident, and it is why password
  updates do not need a revocation list.
- Continuous use renews the token within a sliding window, so an active shopper is
  not logged out mid-session while an idle one still expires.
- Logout can clear the browser's copy but cannot revoke a copied token. That
  limitation is stated in the sessions spec and asserted by the live verification
  script, because a limitation that is measured is worth more than one that is
  assumed away. Closing it needs a session-epoch column, which exists as a reviewed
  but unapplied migration.

## Security posture: where each control sits

- **SQL**: every statement is parameterized with explicit casts; no value is
  interpolated into SQL.
- **CSRF**: a double-submit token *plus* an `Origin` and `Sec-Fetch-Site` check on
  every mutating request, because a token alone does not stop a form post from
  another site.
- **XSS**: every interpolated value is escaped, and a strict Content-Security-Policy
  means a missed escape cannot execute inline script.
- **Card data**: it travels browser → Worker → gateway and is never persisted; only
  the provider's identifiers are stored. This is why the schema's card column stays
  unused, and why the PHP application's card-update screen was not ported.
- **Authorization**: always server-side. Admin routes check the admin claim; an
  order page resolves the order from the signed-in customer's own list rather than
  trusting an id in the URL.
- **Errors**: generic to the client, detailed in the log.
- **Admin credentials**: a digest in a secret, compared in constant time, so the
  plaintext exists nowhere.

## Free-tier accounting

| Service | Free allowance | Use here |
| --- | --- | --- |
| Workers requests | 100,000/day | one request per page or action |
| Workers CPU | 10 ms/request | static-free pages; rendering is string work |
| Workers memory | 128 MB/isolate | no large buffers; one `pg` client per request |
| Static assets | free, unlimited requests | CSS, one ES module, product images |
| Hyperdrive | 100,000 queries/day | a few queries per page |
| Cron Triggers | 5/account | not used |
| R2 | 10 GB, 1M Class A | **not used**; uploads disabled pending approval |
| Email | none native | order notification emails are not sent |

No paid service is required by this architecture. R2 and email would each add a
dependency and are deliberately left off.

## Deliberate omissions

- No framework, no template engine, no build step beyond Wrangler's bundler.
- No Docker, no Node server: the runtime is `workerd`.
- No D1 and no database migration for storage: Neon stays the system of record.
- No image uploads: Workers have no writable filesystem, and R2 is an approval gate.
- The PHP application is not deleted; it remains the behavioural reference.
- The chapter directories keep their own deployment files; they are exercises, not
  this deployment path.

## Not implemented, and not simulated

- **Email notifications.** No sender is available natively and no provider is
  configured, so the order pipeline records audit rows instead of sending mail.
- **Stock levels.** The schema has no stock column; the original application
  confirms availability by contacting the supplier. Inventing an inventory model
  would be a schema change and is out of scope.
- **Refunds and voids.** Absent from the original application and needing a reviewed
  status meaning, not just a button.
- **PayPal.** The Authorize.Net pipeline covers checkout; a second provider would add
  a second state machine for no additional business value here.
- **Card storage and the legacy credit-card screen.** Refused by design (above).

## Known gaps

- A copied session cookie survives logout until it expires; migration 001 would
  close it (see the sessions spec).
- Hyperdrive's own pooling and transaction pinning are exercised through the
  deployed Worker and a live `/health`, but not measured directly; see
  `hyperdrive-verification.md` for what is and is not proven.
- Visual parity with the PHP templates is structural, not pixel-level: ids, classes,
  form fields and links are asserted, but no browser automation was installed.
