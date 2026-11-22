/**
 * Step 0 — read-only introspection of the live Neon database.
 *
 * Reads connection details from the SOPS-managed decrypted env file next to the
 * authoritative Neon infrastructure repo. Credentials are never printed.
 *
 *   node scripts/introspect.mjs [--out <path>]
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { dirname, resolve } from 'node:path';
import pg from 'pg';

const ENV_FILE =
  'C:/Users/toman/repos/wilsonify/FreshInstall/neon/.env.dec.yaml';

function readEnv() {
  const text = readFileSync(ENV_FILE, 'utf8');
  const out = {};
  for (const line of text.split(/\r?\n/)) {
    if (!/^[A-Z0-9_]+:/.test(line)) continue;
    const i = line.indexOf(':');
    out[line.slice(0, i)] = line.slice(i + 1).trim();
  }
  return out;
}

const env = readEnv();
const required = ['NEON_USERNAME', 'NEON_PASSWORD', 'NEON_SERVER', 'NEON_DATABASE'];
for (const key of required) {
  if (!env[key]) throw new Error(`Missing ${key} in ${ENV_FILE}`);
}

const connectionString =
  `postgresql://${encodeURIComponent(env.NEON_USERNAME)}:` +
  `${encodeURIComponent(env.NEON_PASSWORD)}@${env.NEON_SERVER}/${env.NEON_DATABASE}` +
  `?sslmode=${env.NEON_SSLMODE || 'require'}`;

const client = new pg.Client({ connectionString, connectionTimeoutMillis: 15000 });
await client.connect();

const snapshot = {};

snapshot.identity = (
  await client.query(`
    SELECT current_database() AS database,
           current_user       AS role,
           version()          AS version,
           inet_server_addr()::text AS server_addr,
           pg_is_in_recovery() AS replica
  `)
).rows[0];

snapshot.extensions = (
  await client.query(`
    SELECT extname, extversion FROM pg_extension ORDER BY extname
  `)
).rows;

snapshot.columns = (
  await client.query(`
    SELECT c.table_name, c.column_name, c.data_type,
           c.character_maximum_length AS max_len,
           c.numeric_precision AS num_precision, c.numeric_scale AS num_scale,
           c.is_nullable, c.column_default
    FROM information_schema.columns c
    WHERE c.table_schema = 'public'
    ORDER BY c.table_name, c.ordinal_position
  `)
).rows;

snapshot.tables = (
  await client.query(`
    SELECT c.relname AS table_name, c.reltuples::bigint AS approx_rows
    FROM pg_class c
    JOIN pg_namespace n ON n.oid = c.relnamespace
    WHERE n.nspname = 'public' AND c.relkind = 'r'
    ORDER BY c.relname
  `)
).rows;

snapshot.functions = (
  await client.query(`
    SELECT p.proname,
           pg_get_function_identity_arguments(p.oid) AS args,
           pg_get_function_result(p.oid)           AS returns,
           p.provolatile                            AS volatility,
           p.prosecdef                              AS security_definer,
           l.lanname                                AS language,
           CASE WHEN p.prorettype = 'trigger'::regtype THEN 'trigger' ELSE 'normal' END AS kind
    FROM pg_proc p
    JOIN pg_namespace n ON n.oid = p.pronamespace
    JOIN pg_language l  ON l.oid = p.prolang
    WHERE n.nspname = 'public' AND l.lanname <> 'internal'
    ORDER BY p.proname
  `)
).rows;

snapshot.types = (
  await client.query(`
    SELECT t.typname,
           string_agg(a.attname || ' ' || format_type(a.atttypid, a.atttypmod), ', ' ORDER BY a.attnum) AS attributes
    FROM pg_type t
    JOIN pg_namespace n ON n.oid = t.typnamespace
    JOIN pg_class c      ON c.oid = t.typrelid
    JOIN pg_attribute a  ON a.attrelid = c.oid AND a.attnum > 0 AND NOT a.attisdropped
    WHERE n.nspname = 'public' AND t.typtype = 'c'
    GROUP BY t.typname
    ORDER BY t.typname
  `)
).rows;

snapshot.sequences = (
  await client.query(`
    SELECT sequencename, last_value FROM pg_sequences WHERE schemaname = 'public' ORDER BY sequencename
  `)
).rows;

// Exact row counts: the dataset is small enough for this to be cheap.
const tableNames = snapshot.tables.map((t) => t.table_name);
snapshot.row_counts = {};
for (const name of tableNames) {
  const res = await client.query(`SELECT count(*)::bigint AS n FROM "${name}"`);
  snapshot.row_counts[name] = Number(res.rows[0].n);
}

// Password hash *shape* only — lengths and prefix kinds, never values.
snapshot.password_shape = (
  await client.query(`
    SELECT length(password) AS len,
           count(*)::int     AS n,
           count(*) FILTER (WHERE password ~ '^[0-9a-f]{64}$') AS sha256_hex_like,
           count(*) FILTER (WHERE password LIKE '%$%')         AS has_separator
    FROM customer
    GROUP BY length(password)
    ORDER BY len
  `)
).rows;

snapshot.product_sample = (
  await client.query(`
    SELECT product_id, name, price, discounted_price, image, thumbnail
    FROM product ORDER BY product_id LIMIT 3
  `)
).rows;

await client.end();

const json = JSON.stringify(snapshot, null, 2);
const argIdx = process.argv.indexOf('--out');
if (argIdx > -1) {
  const here = dirname(fileURLToPath(import.meta.url));
  const out = resolve(here, '..', process.argv[argIdx + 1]);
  writeFileSync(out, json);
  console.log(`wrote ${out}`);
}

console.log(
  JSON.stringify(
    {
      identity: { database: snapshot.identity.database, version: snapshot.identity.version.split(' ').slice(0, 2).join(' ') },
      counts: {
        tables: snapshot.tables.length,
        functions: snapshot.functions.length,
        types: snapshot.types.length,
        extensions: snapshot.extensions.length,
      },
      extensions: snapshot.extensions.map((e) => `${e.extname}@${e.extversion}`),
      password_shape: snapshot.password_shape,
      rows: snapshot.row_counts,
    },
    null,
    2
  )
);
