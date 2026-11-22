# Hyperdrive / PostgreSQL verification

The integration point between the Worker and Neon is the highest-risk part of this
migration, so each property was checked rather than assumed. Results below are
from actual runs on this machine (`scripts/verify-db.mjs`, `wrangler dev`, and the
HTTP checks in the final report).

## What was verified, and how

| Property | Method | Result |
| --- | --- | --- |
| Driver works against the live endpoint | `wrangler dev` + `GET /health` → `withDb` → `SELECT 1` | **200** `{"status":"ok","database":"ok"}` |
| TLS is mandatory | connect with `sslmode=disable` | **refused**: `connection is insecure (try using sslmode=require)` |
| TLS is in use with `sslmode=require` | connect with `require` | **connected and queries succeeded** |
| TLS inspected *inside* the database | `pg_stat_ssl` for `pg_backend_pid()` | `ssl = false` — this is the **pooler → Postgres** hop, not the client hop (see note) |
| Transactions | `BEGIN; SELECT; ROLLBACK` on one client | **supported**, rolled back cleanly |
| Named prepared statements | same named statement executed twice on one connection | **accepted** (weak test: no pooler re-dispatch in between) |
| Connection reuse | 5 sequential `SELECT 1` on one client | 35.4, 37.5, 36.8, 33.1, 33.2 ms — no reconnect cost |
| Concurrency on one client | 5 parallel queries | 5 results in 174.4 ms (driver serialises) |
| Exact catalog contract | `SELECT * FROM catalog_get_products_on_catalog($1::integer,$2::integer,$3::integer)` | 3 rows, columns `product_id,name,description,price,discounted_price,thumbnail` — matches `ProductListRow` |
| Pooling mode of the endpoint | `SHOW pool_mode` | not exposed by Neon's pooler |
| End-to-end pages through the app | 12 HTTP requests against `wrangler dev` | all expected statuses (see report) |
| Interactive paths (POST) | `scripts/verify-interactive.mjs` | 100 checks, 0 failures — see [`live-verification.md`](live-verification.md) |

**TLS note.** `pg_stat_ssl.ssl = false` describes the session the Postgres backend
sees. Neon's pooler terminates TLS and then connects to the database, so the client
hop can be TLS while the backend hop is not. The client hop was proven separately:
`sslmode=disable` is rejected, and `require` connects.

## Interactive verification (running Worker)

`scripts/verify-interactive.mjs` drives the served Worker over HTTPS against this
database and proves the request pipeline end to end: cookie flag emission, session
attacks, sliding renewal, CSRF/Origin enforcement, the admin gate, the checkout
refusal, and the markup contract on returned HTML — with table row counts captured
before and after. Results: [`live-verification.md`](live-verification.md).

One operational detail worth knowing: `CLOUDFLARE_HYPERDRIVE_LOCAL_CONNECTION_STRING_HYPERDRIVE`
must be in the **process environment**; putting it only in `.dev.vars` is too late
for `wrangler dev`, which fails with "you should use a local Postgres connection
string". All other local values are read from `.dev.vars`.

## Latency budget

Each query round trip to the pooled endpoint measured ~33–37 ms from this machine.
A storefront page makes 2–4 queries (~70–150 ms wall), which is *wall* time, not
CPU time, so it does not consume the 10 ms CPU budget. It does mean the Worker
should run close to the database; `placement` and a Cloudflare account in the same
region keep this at Hyperdrive's ~1–3 ms hop.

## What could NOT be verified locally (gated)

1. **Hyperdrive's own pooling and transaction pinning.** This needs a deployed
   Hyperdrive configuration and `wrangler dev --remote`. No Cloudflare credentials
   are available in this environment, and creating the configuration is an
   approval gate.
2. **Whether the pooler re-dispatches between statements.** If it does, session
   state (`SET`) would not persist. This application does not rely on session
   state: every plpgsql function is a single statement, and checkout never uses
   `SET`. That is why the risk is acceptable.
3. **Named prepared statement reuse across pooler dispatches.** The application
   never names prepared statements (`db/queries.ts` uses positional parameters
   only), so a pooler rejection cannot occur in production code.

## How to run the matrix

```powershell
cd C:\Users\toman\repos\php\hatshop\src\web
node scripts/verify-db.mjs
```

For the remote half, after approval and provisioning:

```powershell
npx wrangler hyperdrive list
npx wrangler dev --remote --port 8787
Invoke-RestMethod http://127.0.0.1:8787/health
```

## Client configuration used

```ts
new Client({
  connectionString: env.HYPERDRIVE.connectionString,
  connectionTimeoutMillis: 8000,
  query_timeout: 8000,
  statement_timeout: 8000,
})
```

- One `Client` per request, always closed in `finally` (`db/client.ts`).
- No `Pool`: Hyperdrive already pools; a Worker-side pool would leak sockets.
- Timeouts keep a hung database from consuming the request budget: the Worker
  returns 503 rather than hanging.
