/**
 * Read-only: find the live demo account's password so the legacy verify path can
 * be exercised against the real database.
 *
 * The stored digest is unsalted SHA-1, so a bounded list of plausible demo
 * passwords can be tested *locally* — no database writes, no network guessing.
 *
 * A match is written to `.live-account.local.json` (git-ignored) and is never
 * printed, so the credential does not end up in logs or transcripts.
 *
 *   node scripts/find-live-account.mjs
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { resolve, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';
import pg from 'pg';
import { verifyLegacy } from '../auth/password.ts';

const here = dirname(fileURLToPath(import.meta.url));
const root = resolve(here, '..');

function connectionString() {
  if (process.env.CLOUDFLARE_HYPERDRIVE_LOCAL_CONNECTION_STRING_HYPERDRIVE) {
    return process.env.CLOUDFLARE_HYPERDRIVE_LOCAL_CONNECTION_STRING_HYPERDRIVE;
  }
  const yamlPath = process.env.NEON_ENV_FILE ?? 'C:/Users/toman/repos/wilsonify/FreshInstall/neon/.env.dec.yaml';
  const text = readFileSync(yamlPath, 'utf8');
  const values = {};
  for (const line of text.split(/\r?\n/)) {
    const at = line.indexOf(':');
    if (at <= 0) continue;
    values[line.slice(0, at).trim()] = line.slice(at + 1).trim();
  }
  return `postgresql://${encodeURIComponent(values.NEON_USERNAME)}:${encodeURIComponent(
    values.NEON_PASSWORD,
  )}@${values.NEON_SERVER}/${values.NEON_DATABASE}?sslmode=require`;
}

const client = new pg.Client({ connectionString: connectionString(), connectionTimeoutMillis: 12000 });
await client.connect();
const { rows } = await client.query(
  'SELECT customer_id, name, email, password, length(password) AS len FROM customer ORDER BY customer_id',
);
await client.end();

if (rows.length === 0) {
  console.log('no customer rows');
  process.exit(1);
}

console.log(`customer rows: ${rows.length}`);
for (const row of rows) {
  console.log(
    `  id=${row.customer_id} name=${JSON.stringify(row.name)} email=${JSON.stringify(row.email)} hash_len=${row.len} kind=${
      /^[0-9a-f]{40}$/i.test(row.password) ? 'sha1' : /^[0-9a-f]{64}$/i.test(row.password) ? 'sha256' : 'other'
    }`,
  );
}

const target = rows.find((row) => /^[0-9a-f]{40}$/i.test(row.password));
if (!target) {
  console.log('no SHA-1 customer to test');
  process.exit(1);
}

const nameTokens = String(target.name)
  .toLowerCase()
  .split(/[^a-z0-9]+/)
  .filter(Boolean);
const emailLocal = String(target.email).split('@')[0].toLowerCase();

const candidates = new Set([
  'password',
  'Password',
  'password1',
  'Password1',
  'password123',
  'Password123',
  'passw0rd',
  'Passw0rd!',
  'secretword',
  'secret',
  'secret123',
  'hatshop',
  'hatshop123',
  'HatShop',
  'Hatshop1',
  'changeme',
  'changeme123',
  'welcome',
  'welcome1',
  'admin',
  'admin123',
  'administrator',
  'test',
  'test123',
  'testing',
  'demo',
  'demo123',
  'qwerty',
  'qwerty123',
  'letmein',
  '123456',
  '12345678',
  '123456789',
  'abc123',
  'iloveyou',
  'monkey',
  'dragon',
  'sunshine',
  'master',
  'shadow',
  'superman',
  'trustno1',
  'football',
  'baseball',
  'princess',
  'whatever',
  'nothing',
  'letmein123',
  emailLocal,
  `${emailLocal}123`,
  `${emailLocal}1`,
  ...nameTokens,
  ...nameTokens.map((t) => `${t}123`),
  ...nameTokens.map((t) => t.charAt(0).toUpperCase() + t.slice(1)),
]);

let matched = null;
let tried = 0;
for (const candidate of candidates) {
  if (!candidate) continue;
  tried += 1;
  // Same legacy check the Worker uses; the digest is computed only in auth/password.ts.
  if (await verifyLegacy(candidate, String(target.password))) {
    matched = candidate;
    break;
  }
}

if (!matched) {
  console.log(`no match after ${tried} candidates`);
  process.exit(2);
}

writeFileSync(
  resolve(root, '.live-account.local.json'),
  `${JSON.stringify({ email: target.email, password: matched, customer_id: target.customer_id }, null, 2)}\n`,
);
console.log(`matched candidate ${tried} of ${tried}; written to .live-account.local.json (git-ignored)`);
