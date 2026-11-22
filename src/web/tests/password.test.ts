import assert from 'node:assert/strict';
import { test } from 'node:test';
import {
  MAX_LEGACY_HASH_LEN,
  detectKind,
  hashPassword,
  needsUpgrade,
  passwordFingerprint,
  sha1Hex,
  sha256Hex,
  timingSafeEqual,
  verifyLegacy,
  verifyPassword,
} from '../auth/password.ts';

// Known digests: sha1("password") and sha256("password").
const SHA1_PASSWORD = '5baa61e4c9b93f3f0682250b6cf8331b7ee68fd8';
const SHA256_PASSWORD = '5e884898da28047151d0e56f8dc6292773603d0d6aabbdd62a11ef721d1542d8';

test('detects the live 40-hex digest as legacy SHA-1', async () => {
  assert.equal(detectKind(SHA1_PASSWORD), 'sha1');
  assert.equal(await sha1Hex('password'), SHA1_PASSWORD);
  assert.equal(await verifyLegacy('password', SHA1_PASSWORD), true);
  assert.equal(await verifyLegacy('Password', SHA1_PASSWORD), false);
});

test('verifies SHA-256 digests written by the current PHP code', async () => {
  assert.equal(detectKind(SHA256_PASSWORD), 'sha256');
  assert.equal(await sha256Hex('password'), SHA256_PASSWORD);
  assert.equal(await verifyLegacy('password', SHA256_PASSWORD), true);
});

test('supports the PHP hashPrefix variant', async () => {
  const prefixed = await sha256Hex('pepperpassword');
  assert.equal(await verifyLegacy('password', prefixed, 'pepper'), true);
  // The live application never sets a prefix (setPrefix is only used in tests).
  assert.equal(await verifyLegacy('password', prefixed), false);
});

test('rejects unknown digest shapes instead of guessing', async () => {
  assert.equal(detectKind(''), 'none');
  assert.equal(detectKind('not-a-hash'), 'none');
  assert.equal(await verifyLegacy('password', 'not-a-hash'), false);
});

test('PBKDF2 round-trips and is rejected by the legacy verifier', async () => {
  const hash = await hashPassword('correct horse battery', 1000);
  assert.match(hash, /^pbkdf2\$1000\$[A-Za-z0-9_-]+\$[A-Za-z0-9_-]+$/);
  assert.equal(await verifyPassword('correct horse battery', hash), true);
  assert.equal(await verifyPassword('wrong password', hash), false);
  assert.equal(needsUpgrade(hash), false, 'a modern hash must not be scheduled for upgrade');
});

test('documents why migration 002 is required', async () => {
  const legacy = SHA1_PASSWORD;
  const modern = await hashPassword('correct horse battery', 210_000);
  assert.ok(legacy.length <= MAX_LEGACY_HASH_LEN, 'legacy SHA-1 fits VARCHAR(50)');
  assert.ok(modern.length > MAX_LEGACY_HASH_LEN, 'PBKDF2 does not fit VARCHAR(50)');
  assert.ok(SHA256_PASSWORD.length > MAX_LEGACY_HASH_LEN, 'even SHA-256 does not fit VARCHAR(50)');
});

test('fingerprints are stable, short and do not reveal the hash', async () => {
  const a = await passwordFingerprint(SHA1_PASSWORD);
  const b = await passwordFingerprint(SHA1_PASSWORD);
  const c = await passwordFingerprint(SHA256_PASSWORD);
  assert.equal(a, b);
  assert.notEqual(a, c);
  assert.equal(a.length, 16);
  assert.ok(!a.includes(SHA1_PASSWORD));
});

test('constant-time comparison behaves like equality', () => {
  assert.equal(timingSafeEqual('abc', 'abc'), true);
  assert.equal(timingSafeEqual('abc', 'abd'), false);
  assert.equal(timingSafeEqual('abc', 'abcd'), false);
});
