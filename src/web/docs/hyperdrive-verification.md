# Hyperdrive / PostgreSQL verification

The integration point between the Worker and Neon is the highest-risk part of this
migration, so each property was checked rather than assumed. Results below come
from actual runs — against a local `wrangler dev` session, the live database, and
the deployed Worker.

## What was observed

| Property | Method | Result |
| --- | --- | --- |
| Driver works against the live endpoint | `GET /health` → `withDb` → `SELECT 1` | **200** `{"status":"ok","database":"ok"}` |
| TLS is mandatory | connect with `sslmode=disable` | **refused**: `connection is insecure (try using sslmode=require)` |
| TLS is in use with `sslmode=require` | connect with `require` | **connected and queries succeeded** |
| TLS inspected *inside* the database | `pg_stat_ssl` for `pg_backend_pid()` | `ssl = false` — this is the **pooler → Postgres** hop, not the client hop (see note) |
| Transactions | `BEGIN; SELECT; ROLLBACK` on one client | **supported**, rolled back cleanly |
| Named prepared statements | same named statement executed twice on one connection | **accepted** (weak test: no pooler re-dispatch in between) |
| Connection reuse | 5 sequential `SELECT 1` on one client | 35.4, 37.5, 36.8, 33.1, 33.2 ms — no reconnect cost |
| Concurrency on one client | 5 parallel queries | 5 results in 174.4 ms (driver serialises) |
| Exact catalog contract | catalog listing call with the app's parameters | 3 rows, columns `product_id,name,description,price,discounted_price,thumbnail` — matches `ProductListRow` |
| Pooling mode of the endpoint | `SHOW pool_mode` | not exposed by Neon's pooler |
| End-to-end pages through the app | 12 HTTP requests against `wrangler dev` | all expected statuses (see report) |
| Interactive paths (POST) | `scripts/verify-interactive.mjs` | 100 checks, 0 failures — see [`live-verification.md`](live-verification.md) |
| Deployed Worker → Hyperdrive → Neon | `GET https://hatshop.tom-andrew-wilson.workers.dev/health` | **200** `{"status":"ok","database":"ok"}` |

**TLS note.** `pg_stat_ssl.ssl = false` describes the session the Postgres backend
sees. Neon's pooler terminates TLS and then connects to the database, so the client
hop can be TLS while the backend hop is not. The client hop was proven separately:
`sslmode=disable` is rejected, and `require` connects.

## Latency budget

Each query round trip to the pooled endpoint measured ~33–37 ms from this machine.
A storefront page makes 2–4 queries (~70–150 ms wall), which is *wall* time, not
CPU time, so it does not consume the 10 ms CPU budget. It does mean the Worker
should run close to the database; `placement` and a Cloudflare account in the same
region keep this at Hyperdrive's ~1–3 ms hop.

## What could NOT be verified

1. **Hyperdrive's own pooling and transaction pinning.** The deployed Worker now
   reaches Neon through Hyperdrive (the `/health` row above), but the pinning
   behaviour itself still needs a `wrangler dev --remote` run against the deployed
   configuration; that run is not part of this record.
2. **Whether the pooler re-dispatches between statements.** If it does, session
   state (`SET`) would not persist. This application does not rely on session
   state: every plpgsql function is a single statement, and checkout never uses
   `SET`. That is why the risk is acceptable.
3. **Named prepared statement reuse across pooler dispatches.** The application
   never names prepared statements (positional parameters only), so a pooler
   rejection cannot occur in production code.

The behaviour proven through the running Worker — cookies, session attacks,
renewal, CSRF, the admin gate, checkout refusal and the markup contract — is
recorded in [`live-verification.md`](live-verification.md).
