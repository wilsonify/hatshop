/**
 * Stateless, signed session and cart cookies.
 *
 * Design (see docs/architecture.md):
 *  - HMAC-SHA256 over a JSON payload; the signature is verified in constant time.
 *  - 8 h absolute lifetime with a 30 min sliding refresh.
 *  - `fp` binds the session to the stored password hash, so changing the password
 *    invalidates every issued session without server-side state.
 *  - `ep` is a session epoch; it is only consulted when SESSION_EPOCH_ENABLED is
 *    set (requires the approved migration). Logout cannot revoke a copied
 *    stateless cookie before that — documented residual risk.
 */
import { passwordFingerprint, sha256Hex, timingSafeEqual, toHex } from './password.ts';

const enc = new TextEncoder();
const dec = new TextDecoder();

export const SESSION_COOKIE = 'hatshop_sid';
export const ADMIN_COOKIE = 'hatshop_admin';
export const CART_COOKIE = 'hatshop_cart';
export const CSRF_COOKIE = 'hatshop_csrf';

export const ABSOLUTE_TTL_MS = 8 * 60 * 60 * 1000;
export const SLIDING_WINDOW_MS = 30 * 60 * 1000;

export interface SessionClaims {
  /** customer_id */
  cid: number;
  /** issued at (ms) */
  iat: number;
  /** expires at (ms) */
  exp: number;
  /** password fingerprint */
  fp: string;
  /** session epoch (0 unless the migration is applied) */
  ep: number;
  /** role: 'customer' | 'admin' */
  role: 'customer' | 'admin';
}

function b64url(bytes: Uint8Array): string {
  let binary = '';
  for (const byte of bytes) binary += String.fromCharCode(byte);
  return btoa(binary).replace(/\+/g, '-').replace(/\//g, '_').replace(/=+$/, '');
}

function fromB64url(value: string): Uint8Array {
  const padded = value.replace(/-/g, '+').replace(/_/g, '/');
  return Uint8Array.from(atob(padded), (c) => c.charCodeAt(0));
}

async function hmacKey(secret: string): Promise<CryptoKey> {
  return crypto.subtle.importKey('raw', enc.encode(secret), { name: 'HMAC', hash: 'SHA-256' }, false, [
    'sign',
    'verify',
  ]);
}

async function sign(payload: string, secret: string): Promise<string> {
  const key = await hmacKey(secret);
  const signature = await crypto.subtle.sign('HMAC', key, enc.encode(payload));
  return b64url(new Uint8Array(signature));
}

export async function seal(value: unknown, secret: string): Promise<string> {
  const payload = b64url(enc.encode(JSON.stringify(value)));
  return `${payload}.${await sign(payload, secret)}`;
}

export async function open<T>(token: string | undefined, secret: string): Promise<T | null> {
  if (!token) return null;
  const dot = token.lastIndexOf('.');
  if (dot <= 0) return null;
  const payload = token.slice(0, dot);
  const signature = token.slice(dot + 1);
  const expected = await sign(payload, secret);
  if (!timingSafeEqual(signature, expected)) return null;
  try {
    return JSON.parse(dec.decode(fromB64url(payload))) as T;
  } catch {
    return null;
  }
}

export async function createSession(
  customerId: number,
  storedHash: string,
  secret: string,
  now = Date.now(),
  epoch = 0,
): Promise<string> {
  const claims: SessionClaims = {
    cid: customerId,
    iat: now,
    exp: now + ABSOLUTE_TTL_MS,
    fp: await passwordFingerprint(storedHash),
    ep: epoch,
    role: 'customer',
  };
  return seal(claims, secret);
}

export async function createAdminSession(username: string, secret: string, now = Date.now()): Promise<string> {
  const claims: SessionClaims = {
    cid: 0,
    iat: now,
    exp: now + ABSOLUTE_TTL_MS,
    fp: await sha256Hex(username),
    ep: 0,
    role: 'admin',
  };
  return seal(claims, secret);
}

export interface SessionState {
  claims: SessionClaims;
  /** True when the cookie should be re-issued (sliding window). */
  refresh: boolean;
  expired: boolean;
}

export async function readSession(
  token: string | undefined,
  secret: string,
  now = Date.now(),
): Promise<SessionState | null> {
  const claims = await open<SessionClaims>(token, secret);
  if (!claims || typeof claims.cid !== 'number' || typeof claims.exp !== 'number') return null;
  const expired = now >= claims.exp;
  // Sliding renewal: a session older than the window is re-issued with a fresh
  // `iat`/`exp`, so continuous use rolls the 8 h window forward while an idle
  // token still dies at its own `exp`.
  const age = typeof claims.iat === 'number' ? now - claims.iat : 0;
  const refresh = !expired && age >= SLIDING_WINDOW_MS;
  return { claims, refresh, expired };
}

/** A 128-hex cart id, matching shopping_cart.cart_id CHAR(128). */
export function newCartId(): string {
  return toHex(crypto.getRandomValues(new Uint8Array(64)).buffer);
}

export function newCsrfToken(): string {
  return toHex(crypto.getRandomValues(new Uint8Array(24)).buffer);
}

export function parseCookies(header: string | null): Record<string, string> {
  const out: Record<string, string> = {};
  if (!header) return out;
  for (const part of header.split(';')) {
    const eq = part.indexOf('=');
    if (eq < 0) continue;
    const name = part.slice(0, eq).trim();
    if (!name) continue;
    out[name] = decodeURIComponent(part.slice(eq + 1).trim());
  }
  return out;
}

export interface CookieOptions {
  maxAge?: number;
  httpOnly?: boolean;
  sameSite?: 'Strict' | 'Lax' | 'None';
  path?: string;
}

export function serializeCookie(name: string, value: string, secure: boolean, options: CookieOptions = {}): string {
  const parts = [`${name}=${encodeURIComponent(value)}`, `Path=${options.path ?? '/'}`];
  parts.push(`SameSite=${options.sameSite ?? 'Lax'}`);
  if (options.httpOnly ?? true) parts.push('HttpOnly');
  if (secure) parts.push('Secure');
  if (options.maxAge !== undefined) parts.push(`Max-Age=${Math.floor(options.maxAge / 1000)}`);
  return parts.join('; ');
}

export function clearCookie(name: string, secure: boolean, path = '/'): string {
  return serializeCookie(name, '', secure, { maxAge: 0, path });
}
