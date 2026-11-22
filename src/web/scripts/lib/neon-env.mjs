/**
 * One reader for the decrypted Neon env file and its connection string.
 *
 * Every maintenance script used to carry its own copy of this parser and URL
 * builder, which drifted (some honoured the Hyperdrive local override and
 * NEON_SSLMODE, others hard-coded `require`). This module is that single copy.
 *
 * Nothing here prints a credential.
 */
import { readFileSync } from 'node:fs';

/** Default decrypted env file produced by the Neon Terraform/SOPS workflow. */
export const NEON_ENV_FILE =
  process.env.NEON_ENV_FILE ?? 'C:/Users/toman/repos/wilsonify/FreshInstall/neon/.env.dec.yaml';

const REQUIRED = ['NEON_USERNAME', 'NEON_PASSWORD', 'NEON_SERVER', 'NEON_DATABASE'];

/** Parses flat `KEY: value` lines without a backtracking regular expression. */
export function parseEnvText(text) {
  const values = {};
  for (const line of text.split(/\r?\n/)) {
    const at = line.indexOf(':');
    if (at <= 0) continue;
    values[line.slice(0, at).trim()] = line.slice(at + 1).trim();
  }
  return values;
}

export function readEnvFile(path = NEON_ENV_FILE) {
  return parseEnvText(readFileSync(path, 'utf8'));
}

/**
 * Connection string for the live database.
 *
 * `CLOUDFLARE_HYPERDRIVE_LOCAL_CONNECTION_STRING_HYPERDRIVE` wins when set, so a
 * script and `wrangler dev` can share one credential source. Otherwise the file is
 * read and the credentials are URL-encoded; `sslmode` defaults to NEON_SSLMODE and
 * then to `require`.
 */
export function neonConnectionString({ envFile = NEON_ENV_FILE, sslMode } = {}) {
  const override = process.env.CLOUDFLARE_HYPERDRIVE_LOCAL_CONNECTION_STRING_HYPERDRIVE;
  if (override) return override;

  const env = readEnvFile(envFile);
  const missing = REQUIRED.filter((key) => !env[key]);
  if (missing.length > 0) throw new Error(`Missing ${missing.join(', ')} in ${envFile}`);

  const ssl = sslMode ?? env.NEON_SSLMODE ?? 'require';
  return (
    `postgresql://${encodeURIComponent(env.NEON_USERNAME)}:` +
    `${encodeURIComponent(env.NEON_PASSWORD)}@${env.NEON_SERVER}/${env.NEON_DATABASE}` +
    `?sslmode=${ssl}`
  );
}
