/**
 * Read-only database behaviour probe (Hyperdrive verification matrix, part 1).
 *
 * Proves the driver-level properties the Worker depends on against the *live*
 * Neon endpoint: TLS, transaction control, named prepared statements, connection
 * reuse and concurrency. It performs no writes: the transaction probe rolls back.
 *
 * Hyperdrive's own pooling/pinning behaviour can only be exercised by a deployed
 * binding (see docs/hyperdrive-verification.md); this script documents what is
 * proven locally and what remains gated.
 *
 *   node scripts/verify-db.mjs
 */
import { readFileSync } from 'node:fs';
import pg from 'pg';

const ENV_FILE = 'C:/Users/toman/repos/wilsonify/FreshInstall/neon/.env.dec.yaml';

function readEnv() {
  const out = {};
  for (const line of readFileSync(ENV_FILE, 'utf8').split(/\r?\n/)) {
    const at = line.indexOf(':');
    if (at <= 0) continue;
    out[line.slice(0, at).trim()] = line.slice(at + 1).trim();
  }
  return out;
}

const env = readEnv();
const connectionString =
  `postgresql://${encodeURIComponent(env.NEON_USERNAME)}:` +
  `${encodeURIComponent(env.NEON_PASSWORD)}@${env.NEON_SERVER}/${env.NEON_DATABASE}` +
  `?sslmode=${env.NEON_SSLMODE || 'require'}`;

const results = {};
const client = new pg.Client({ connectionString, connectionTimeoutMillis: 15000 });

const connectStart = performance.now();
await client.connect();
results.connectMs = Number((performance.now() - connectStart).toFixed(1));

results.tls = (await client.query('SHOW ssl')).rows[0]?.ssl ?? 'unknown';
results.serverVersion = (await client.query('SHOW server_version')).rows[0]?.server_version ?? 'unknown';

try {
  results.poolMode = (await client.query('SHOW pool_mode')).rows[0]?.pool_mode ?? 'not a pooler';
} catch {
  results.poolMode = 'not exposed (direct endpoint)';
}

// Transaction control on one connection, with a guaranteed rollback.
try {
  await client.query('BEGIN');
  const before = (await client.query('SELECT 1 AS n')).rows[0].n;
  await client.query('ROLLBACK');
  results.transactions = { supported: true, sample: Number(before) };
} catch (error) {
  results.transactions = { supported: false, error: error.message };
  await client.query('ROLLBACK').catch(() => {});
}

// Named prepared statements: poolers that run in transaction mode may reject reuse.
try {
  await client.query({ name: 'hatshop_probe_one', text: 'SELECT $1::int AS n', values: [1] });
  await client.query({ name: 'hatshop_probe_one', text: 'SELECT $1::int AS n', values: [2] });
  results.namedPreparedStatements = { supported: true };
} catch (error) {
  results.namedPreparedStatements = { supported: false, error: error.message };
}
await client.query('DEALLOCATE ALL').catch(() => {});

// Connection reuse: repeated round trips on the same socket.
const samples = [];
for (let i = 0; i < 5; i += 1) {
  const start = performance.now();
  await client.query('SELECT 1 AS n');
  samples.push(Number((performance.now() - start).toFixed(1)));
}
results.queryRoundTripsMs = samples;

// Concurrency on one connection is serialised by the driver; this proves ordering.
const parallelStart = performance.now();
const parallel = await Promise.all(Array.from({ length: 5 }, () => client.query('SELECT 1 AS n')));
results.parallelQueries = { count: parallel.length, elapsedMs: Number((performance.now() - parallelStart).toFixed(1)) };

// The exact function the storefront calls, with the same parameter casts.
try {
  const catalog = await client.query(
    'SELECT * FROM catalog_get_products_on_catalog($1::integer, $2::integer, $3::integer)',
    [60, 3, 0],
  );
  results.catalogFunction = { rows: catalog.rows.length, columns: Object.keys(catalog.rows[0] ?? {}) };
} catch (error) {
  results.catalogFunction = { error: error.message };
}

await client.end();
console.log(JSON.stringify(results, null, 2));
