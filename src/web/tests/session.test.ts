import assert from 'node:assert/strict';
import { test } from 'node:test';
import {
  ABSOLUTE_TTL_MS,
  SLIDING_WINDOW_MS,
  createAdminSession,
  createSession,
  newCartId,
  open,
  parseCookies,
  readSession,
  seal,
  serializeCookie,
} from '../auth/session.ts';
import { verifyCsrf } from '../auth/csrf.ts';
import { sha1Hex } from '../auth/password.ts';

const SECRET = 'test-secret-value-not-used-anywhere';
const LEGACY = await sha1Hex('password');

test('a session round-trips and reports its customer', async () => {
  const token = await createSession(42, LEGACY, SECRET);
  const state = await readSession(token, SECRET);
  assert.ok(state);
  assert.equal(state.claims.cid, 42);
  assert.equal(state.claims.role, 'customer');
  assert.equal(state.expired, false);
});

test('a tampered payload or signature is rejected', async () => {
  const token = await createSession(42, LEGACY, SECRET);
  const [payload, signature] = token.split('.');
  const forged = Buffer.from(JSON.stringify({ cid: 1, iat: 0, exp: 9e12, fp: 'x', ep: 0, role: 'customer' }))
    .toString('base64url');

  assert.equal(await readSession(`${forged}.${signature}`, SECRET), null, 'payload swap');
  assert.equal(await readSession(`${payload}.deadbeef`, SECRET), null, 'signature swap');
  assert.equal(await readSession(token, 'a-different-secret'), null, 'wrong key');
});

test('an aged session asks for renewal; an idle one still expires', async () => {
  const issued = 1_700_000_000_000;
  const token = await createSession(7, LEGACY, SECRET, issued);

  const fresh = await readSession(token, SECRET, issued + 1000);
  assert.ok(fresh);
  assert.equal(fresh.refresh, false, 'a just-issued session is not renewed');

  const aged = await readSession(token, SECRET, issued + SLIDING_WINDOW_MS + 1);
  assert.ok(aged);
  assert.equal(aged.refresh, true, 'renewed once older than the sliding window');

  const expired = await readSession(token, SECRET, issued + ABSOLUTE_TTL_MS + 1);
  assert.ok(expired);
  assert.equal(expired.expired, true);
  assert.equal(expired.refresh, false, 'an expired session is not renewed');
});

test('a renewed session carries a new issued-at and a fresh 8 h expiry', async () => {
  const issued = 1_700_000_000_000;
  const renewedAt = issued + SLIDING_WINDOW_MS + 5;
  const renewed = await createSession(7, LEGACY, SECRET, renewedAt);
  const state = await readSession(renewed, SECRET, renewedAt);

  assert.equal(state?.claims.iat, renewedAt);
  assert.equal(state?.claims.exp, renewedAt + ABSOLUTE_TTL_MS);
  assert.equal(state?.refresh, false);
  assert.equal(state?.claims.cid, 7, 'renewal keeps the customer');
});

test('changing the password invalidates the session fingerprint', async () => {
  const token = await createSession(42, LEGACY, SECRET);
  const state = await readSession(token, SECRET);
  const newFingerprint = (await import('../auth/password.ts')).passwordFingerprint;
  assert.notEqual(state?.claims.fp, await newFingerprint(await sha1Hex('new-password')));
});

test('admin sessions carry the admin role and no customer id', async () => {
  const token = await createAdminSession('admin', SECRET);
  const state = await readSession(token, SECRET);
  assert.equal(state?.claims.role, 'admin');
  assert.equal(state?.claims.cid, 0);
});

test('cart ids are 128 hex characters, matching CHAR(128)', () => {
  const id = newCartId();
  assert.match(id, /^[0-9a-f]{128}$/);
  assert.notEqual(id, newCartId());
});

test('cookie serialisation sets the hardening attributes', () => {
  const cookie = serializeCookie('hatshop_sid', 'abc', true, { maxAge: 1000 });
  assert.match(cookie, /HttpOnly/);
  assert.match(cookie, /Secure/);
  assert.match(cookie, /SameSite=Lax/);
  assert.match(cookie, /Path=\//);
  assert.match(cookie, /Max-Age=1/);

  const local = serializeCookie('hatshop_sid', 'abc', false);
  assert.doesNotMatch(local, /Secure/, 'plain HTTP development must not require Secure');
});

test('cookies parse and ignore malformed segments', () => {
  const parsed = parseCookies('a=1; b=2; malformed; c=');
  assert.equal(parsed.a, '1');
  assert.equal(parsed.b, '2');
  assert.equal(parsed.c, '');
});

test('sealed values survive JSON round-trips and reject bad input', async () => {
  const token = await seal({ n: 'nonce-value' }, SECRET);
  assert.deepEqual(await open(token, SECRET), { n: 'nonce-value' });
  assert.equal(await open(undefined, SECRET), null);
  assert.equal(await open('', SECRET), null);
  assert.equal(await open('no-dot', SECRET), null);
});

test('CSRF accepts only same-origin requests with a matching token', async () => {
  const url = new URL('https://hatshop.example/cart');
  const sameOrigin = new Request(url, { method: 'POST', headers: { origin: 'https://hatshop.example' } });
  const foreign = new Request(url, { method: 'POST', headers: { origin: 'https://evil.example' } });

  assert.equal(verifyCsrf({ cookieToken: 'token-a' }, sameOrigin, url, 'token-a'), true);
  assert.equal(verifyCsrf({ cookieToken: 'token-a' }, sameOrigin, url, 'token-b'), false);
  assert.equal(verifyCsrf({ cookieToken: 'token-a' }, foreign, url, 'token-a'), false);
  assert.equal(verifyCsrf({ cookieToken: undefined }, sameOrigin, url, 'token-a'), false);
  assert.equal(verifyCsrf({ cookieToken: 'token-a' }, sameOrigin, url, ''), false);

  const crossSite = new Request(url, {
    method: 'POST',
    headers: { origin: 'https://hatshop.example', 'sec-fetch-site': 'cross-site' },
  });
  assert.equal(verifyCsrf({ cookieToken: 'token-a' }, crossSite, url, 'token-a'), false);
});
