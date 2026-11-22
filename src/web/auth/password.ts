/**
 * Password verification and upgrade.
 *
 * Live database facts (captured in docs/schema-snapshot.json; readable form via
 * scripts/build-schema-doc.mjs):
 *  - `customer.password` is VARCHAR(50) on the live database.
 *  - The single existing row stores 40 hex characters, i.e. legacy SHA-1.
 *  - The current PHP code writes SHA-256 (64 hex), which does not fit VARCHAR(50)
 *    — that divergence is recorded, not "fixed" here.
 *
 * Consequence: a modern hash (`pbkdf2$...`, ~80 chars) cannot be stored until the
 * column is widened by an approved migration (migrations/002_*.sql). Upgrading is
 * therefore off by default and never fails a login.
 */

const enc = new TextEncoder();

export function toHex(buffer: ArrayBuffer): string {
  return [...new Uint8Array(buffer)].map((b) => b.toString(16).padStart(2, '0')).join('');
}

export function fromHex(hex: string): Uint8Array {
  const clean = hex.trim();
  const out = new Uint8Array(clean.length / 2);
  for (let i = 0; i < out.length; i += 1) out[i] = Number.parseInt(clean.slice(i * 2, i * 2 + 2), 16);
  return out;
}

/** Constant-time comparison for equal-length strings. */
export function timingSafeEqual(a: string, b: string): boolean {
  if (a.length !== b.length) return false;
  let diff = 0;
  for (let i = 0; i < a.length; i += 1) diff |= a.charCodeAt(i) ^ b.charCodeAt(i);
  return diff === 0;
}

export type LegacyKind = 'sha1' | 'sha256' | 'none';

export function detectKind(stored: string): LegacyKind {
  const value = (stored ?? '').trim();
  if (/^[0-9a-f]{40}$/i.test(value)) return 'sha1';
  if (/^[0-9a-f]{64}$/i.test(value)) return 'sha256';
  return 'none';
}

/**
 * Legacy SHA-1 digest of a password.
 *
 * The only place in this application that computes SHA-1. It exists solely to
 * verify digests already stored in `customer.password` (VARCHAR(50), 40 hex).
 * Every hash this application writes comes from `hashPassword()` (PBKDF2), and the
 * reviewed migration 002 retires this path. Reviewed exemption for typescript:S4790.
 */
export async function sha1Hex(password: string): Promise<string> {
  return toHex(await crypto.subtle.digest('SHA-1', enc.encode(password))); // NOSONAR
}

export async function sha256Hex(value: string): Promise<string> {
  return toHex(await crypto.subtle.digest('SHA-256', enc.encode(value)));
}

/**
 * Verifies a password against a legacy hash.
 *
 * `hashPrefix` mirrors the PHP `PasswordHasher::$hashPrefix`, which is empty in
 * this codebase (setPrefix() is only called from its own unit test).
 */
export async function verifyLegacy(password: string, stored: string, hashPrefix = ''): Promise<boolean> {
  const kind = detectKind(stored);
  if (kind === 'none') return false;
  const candidate = await (kind === 'sha1' ? sha1Hex(password) : sha256Hex(hashPrefix + password));
  return timingSafeEqual(candidate.toLowerCase(), stored.trim().toLowerCase());
}

/* ------------------------------------------------------------- PBKDF2 path */

export const PBKDF2_PREFIX = 'pbkdf2';
export const DEFAULT_ITERATIONS = 210_000;

function b64url(bytes: Uint8Array): string {
  return btoa(String.fromCharCode(...bytes)).replaceAll('+', '-').replaceAll('/', '_').replaceAll('=', '');
}

function fromB64url(value: string): Uint8Array {
  const padded = value.replace(/-/g, '+').replace(/_/g, '/');
  return Uint8Array.from(atob(padded), (c) => c.charCodeAt(0));
}

export async function hashPassword(
  password: string,
  iterations: number = DEFAULT_ITERATIONS,
  salt?: Uint8Array,
): Promise<string> {
  const actualSalt = salt ?? crypto.getRandomValues(new Uint8Array(16));
  const key = await crypto.subtle.importKey('raw', enc.encode(password), 'PBKDF2', false, ['deriveBits']);
  const bits = await crypto.subtle.deriveBits(
    { name: 'PBKDF2', hash: 'SHA-256', salt: actualSalt as unknown as BufferSource, iterations },
    key,
    256,
  );
  return `${PBKDF2_PREFIX}$${iterations}$${b64url(actualSalt)}$${b64url(new Uint8Array(bits))}`;
}

export async function verifyPbkdf2(password: string, stored: string): Promise<boolean> {
  const parts = stored.split('$');
  if (parts.length !== 4 || parts[0] !== PBKDF2_PREFIX) return false;
  const iterations = Number.parseInt(parts[1] ?? '', 10);
  if (!Number.isFinite(iterations) || iterations <= 0) return false;
  try {
    const salt = fromB64url(parts[2] ?? '');
    const key = await crypto.subtle.importKey('raw', enc.encode(password), 'PBKDF2', false, ['deriveBits']);
    const bits = await crypto.subtle.deriveBits(
      { name: 'PBKDF2', hash: 'SHA-256', salt: salt as unknown as BufferSource, iterations },
      key,
      256,
    );
    return timingSafeEqual(b64url(new Uint8Array(bits)), parts[3] ?? '');
  } catch {
    return false;
  }
}

export async function verifyPassword(password: string, stored: string): Promise<boolean> {
  if (stored.startsWith(`${PBKDF2_PREFIX}$`)) return verifyPbkdf2(password, stored);
  return verifyLegacy(password, stored);
}

/** True when the stored hash is a legacy digest that should be upgraded. */
export function needsUpgrade(stored: string): boolean {
  return detectKind(stored) !== 'none';
}

/** Stable session fingerprint of the stored hash (never the hash itself). */
export async function passwordFingerprint(stored: string): Promise<string> {
  return (await sha256Hex(stored.trim())).slice(0, 16);
}

/**
 * Longest hash this application can persist given VARCHAR(50).
 * Guard used before any upgrade write so a failed insert can never break login.
 */
export const MAX_LEGACY_HASH_LEN = 50;
