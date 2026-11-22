/**
 * Temporary, fully reverted password reset — approved by the project owner solely
 * to prove the legacy SHA-1 sign-in path end to end.
 *
 * This is the ONLY script in the repository that writes to customer data, and it
 * writes exactly two statements:
 *
 *   --set      keeps the original digest in `.password-restore.local.json`
 *              (git-ignored) and sets a random temporary SHA-1 digest;
 *   --restore  writes the original digest back and verifies it byte for byte
 *              before deleting the backup.
 *
 * `--probe <status>` POSTs the temporary password to /login and asserts the
 * expected status (303 while the reset is active, 401 once it has been restored),
 * so the restore is proven effective rather than assumed.
 *
 * No password or digest is ever printed.
 *
 *   node scripts/temp-password-reset.mjs --set
 *   node scripts/temp-password-reset.mjs --probe 303
 *   node scripts/temp-password-reset.mjs --restore
 *   node scripts/temp-password-reset.mjs --probe 401
 */
import { randomBytes } from 'node:crypto';
import { existsSync, readFileSync, unlinkSync, writeFileSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import pg from 'pg';
import { sha1Hex } from '../auth/password.ts';
import { neonConnectionString } from './lib/neon-env.mjs';
import { createProbe, csrfFrom } from './lib/probe.mjs';

const here = dirname(fileURLToPath(import.meta.url));
const root = resolve(here, '..');
const credPath = resolve(root, '.live-account.local.json');
const backupPath = resolve(root, '.password-restore.local.json');

const mode = process.argv[2];
if (!['--set', '--restore', '--probe'].includes(mode)) {
  console.error('usage: --set | --restore | --probe <expected-status>');
  process.exit(64);
}

/* --------------------------------------------------------------- HTTP probe */

const { baseUrl, request } = createProbe();

/* -------------------------------------------------------------------- modes */

if (mode === '--probe') {
  const expected = Number(process.argv[3] ?? 0);
  const credential = JSON.parse(readFileSync(credPath, 'utf8'));
  const landing = await request('GET', '/login');
  const csrf = csrfFrom(landing.headers['set-cookie']);
  const attempt = await request('POST', '/login', {
    cookie: `hatshop_csrf=${csrf}`,
    form: { _csrf: csrf, email: credential.email, password: credential.password },
    headers: { origin: baseUrl.origin },
  });
  const issued = (attempt.headers['set-cookie'] ?? []).some((c) => c.startsWith('hatshop_sid='));
  console.log(
    `login probe: status=${attempt.status} expected=${expected} session-issued=${issued} location=${
      attempt.headers.location ?? '(none)'
    }`,
  );
  if (attempt.status !== expected) process.exit(1);
  if (expected === 303 && !issued) {
    console.error('expected a session cookie for an accepted sign-in');
    process.exit(1);
  }
  process.exit(0);
}

const client = new pg.Client({ connectionString: neonConnectionString(), connectionTimeoutMillis: 15000 });
await client.connect();

try {
  if (mode === '--set') {
    if (existsSync(backupPath)) {
      console.error('refusing: a restore backup already exists; run --restore first');
      process.exit(2);
    }
    const { rows } = await client.query(
      'SELECT customer_id, email, password FROM customer ORDER BY customer_id LIMIT 1',
    );
    const row = rows[0];
    if (!row) throw new Error('no customer row');

    const password = `Temp-${randomBytes(20).toString('hex')}`; // 45 chars, fits VARCHAR(50)
    const digest = await sha1Hex(password);

    writeFileSync(
      backupPath,
      `${JSON.stringify({ customer_id: row.customer_id, email: row.email, original: row.password }, null, 2)}\n`,
      { mode: 0o600 },
    );

    const update = await client.query('UPDATE customer SET password = $1 WHERE customer_id = $2::integer', [
      digest,
      row.customer_id,
    ]);
    if (update.rowCount !== 1) throw new Error(`update touched ${update.rowCount} rows; aborting`);

    const verify = await client.query('SELECT password FROM customer WHERE customer_id = $1::integer', [
      row.customer_id,
    ]);
    if (verify.rows[0]?.password !== digest) throw new Error('verification failed after update');

    writeFileSync(
      credPath,
      `${JSON.stringify(
        { email: row.email, password, customer_id: row.customer_id, source: 'temporary-reset' },
        null,
        2,
      )}\n`,
      { mode: 0o600 },
    );
    console.log(
      `temporary SHA-1 digest set for customer ${row.customer_id} (${digest.length} chars); original digest backed up locally (git-ignored)`,
    );
  }

  if (mode === '--restore') {
    const backup = JSON.parse(readFileSync(backupPath, 'utf8'));
    const update = await client.query('UPDATE customer SET password = $1 WHERE customer_id = $2::integer', [
      backup.original,
      backup.customer_id,
    ]);
    if (update.rowCount !== 1) throw new Error(`restore touched ${update.rowCount} rows; backup kept`);

    const verify = await client.query('SELECT password FROM customer WHERE customer_id = $1::integer', [
      backup.customer_id,
    ]);
    if (verify.rows[0]?.password !== backup.original) {
      throw new Error('restore verification failed; backup kept for a retry');
    }

    unlinkSync(backupPath);
    console.log(`original digest restored and verified for customer ${backup.customer_id}`);
  }
} catch (error) {
  console.error(`FAILED: ${error instanceof Error ? error.message : error}`);
  process.exitCode = 1;
} finally {
  await client.end();
}
