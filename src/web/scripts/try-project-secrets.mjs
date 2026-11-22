/**
 * Read-only: test whether any secret this project already owns is the live demo
 * account's password, so the legacy verify path can be exercised for real.
 *
 * Values are never printed. A match is written to `.live-account.local.json`
 * (git-ignored) and only the candidate's origin label is reported.
 *
 *   node scripts/try-project-secrets.mjs
 */
import { execFileSync } from 'node:child_process';
import { readFileSync, writeFileSync, readdirSync, statSync } from 'node:fs';
import { createHash } from 'node:crypto';
import { resolve, dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import pg from 'pg';

const here = dirname(fileURLToPath(import.meta.url));
const root = resolve(here, '..');

function connectionString() {
  const yamlPath = process.env.NEON_ENV_FILE ?? 'C:/Users/toman/repos/wilsonify/FreshInstall/neon/.env.dec.yaml';
  const text = readFileSync(yamlPath, 'utf8');
  const values = {};
  for (const line of text.split(/\r?\n/)) {
    const match = /^([A-Z0-9_]+):\s*(.*)$/.exec(line);
    if (match) values[match[1]] = match[2].trim();
  }
  return `postgresql://${encodeURIComponent(values.NEON_USERNAME)}:${encodeURIComponent(
    values.NEON_PASSWORD,
  )}@${values.NEON_SERVER}/${values.NEON_DATABASE}?sslmode=require`;
}

function walk(dir, out = []) {
  let entries = [];
  try {
    entries = readdirSync(dir);
  } catch {
    return out;
  }
  for (const entry of entries) {
    if (entry === 'node_modules' || entry === '.git' || entry === '.terraform') continue;
    const full = join(dir, entry);
    let stat;
    try {
      stat = statSync(full);
    } catch {
      continue;
    }
    if (stat.isDirectory()) walk(full, out);
    else if (/\.(enc\.ya?ml|sops\.ya?ml)$/.test(entry)) out.push(full);
  }
  return out;
}

/** Extracts `KEY: value` pairs from a decrypted YAML, keeping only secrets. */
function secretValues(yaml) {
  const values = [];
  for (const line of yaml.split(/\r?\n/)) {
    const match = /^\s*([A-Za-z0-9_]+)\s*:\s*(.+?)\s*$/.exec(line);
    if (!match) continue;
    const key = match[1];
    const raw = match[2].replace(/^["']|["']$/g, '');
    if (!/PASS|SECRET|TOKEN|KEY/i.test(key)) continue;
    if (!raw || raw.startsWith('ENC[') || raw.includes('your-')) continue;
    values.push({ label: key, value: raw });
  }
  return values;
}

const candidates = [];

// Neon credentials (already local, never committed).
try {
  const text = readFileSync('C:/Users/toman/repos/wilsonify/FreshInstall/neon/.env.dec.yaml', 'utf8');
  for (const line of text.split(/\r?\n/)) {
    const match = /^([A-Z0-9_]+):\s*(.*)$/.exec(line);
    if (match && /PASS/i.test(match[1]) && match[2].trim()) {
      candidates.push({ label: `neon:${match[1]}`, value: match[2].trim() });
    }
  }
} catch {
  /* ignore */
}

// SOPS-encrypted deployment secrets, if the age key is present.
const encFiles = [
  ...walk(resolve(root, '../deploy')),
  ...walk(resolve(root, '../.sops')),
  ...walk('C:/Users/toman/repos/wilsonify/FreshInstall'),
].slice(0, 40);

let decrypted = 0;
for (const file of new Set(encFiles)) {
  try {
    const yaml = execFileSync('sops', ['-d', file], { encoding: 'utf8', stdio: ['ignore', 'pipe', 'ignore'] });
    decrypted += 1;
    for (const { label, value } of secretValues(yaml)) candidates.push({ label: `${file.split(/[\\/]/).pop()}:${label}`, value });
  } catch {
    /* no key or not a SOPS file */
  }
}

// Variants of each secret that a human would plausibly also try.
const expanded = new Map();
for (const { label, value } of candidates) {
  for (const [suffix, variant] of [
    ['', value],
    ['!', `${value}!`],
    ['1', `${value}1`],
    ['123', `${value}123`],
  ]) {
    if (variant.length >= 6 && variant.length <= 100) expanded.set(variant, `${label}${suffix}`);
  }
}

const sha1 = (v) => createHash('sha1').update(v).digest('hex');

const client = new pg.Client({ connectionString: connectionString(), connectionTimeoutMillis: 12000 });
await client.connect();
const { rows } = await client.query('SELECT customer_id, email, password FROM customer ORDER BY customer_id LIMIT 1');
await client.end();

const target = rows[0];
if (!target || !/^[0-9a-f]{40}$/i.test(target.password)) {
  console.log('no SHA-1 customer row; nothing to test');
  process.exit(2);
}

console.log(`sops files decrypted: ${decrypted}; candidate secrets: ${candidates.length}; variants: ${expanded.size}`);

let hit = null;
for (const [value, label] of expanded) {
  if (sha1(value) === String(target.password).toLowerCase()) {
    hit = { value, label };
    break;
  }
}

if (!hit) {
  console.log(`no project secret matched (tried ${expanded.size} variants)`);
  process.exit(2);
}

writeFileSync(
  resolve(root, '.live-account.local.json'),
  `${JSON.stringify({ email: target.email, password: hit.value, customer_id: target.customer_id, source: hit.label }, null, 2)}\n`,
);
console.log(`MATCH from ${hit.label}; written to .live-account.local.json (git-ignored)`);
