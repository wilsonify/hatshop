/** Password storage policy: what this schema can actually hold. */
import {
  DEFAULT_ITERATIONS,
  MAX_LEGACY_HASH_LEN,
  hashPassword,
  needsUpgrade,
  sha1Hex,
  verifyPassword,
} from '../auth/password.ts';
import { getCustomer, updateAccount } from '../db/queries.ts';
import type { Db } from '../db/types.ts';
import * as V from './validation.ts';

/**
 * `customer.password` is VARCHAR(50) on the live database.
 *
 *  - `pbkdf2`      → needs the approved widen migration (002). ~80 chars.
 *  - `sha1-legacy` → 40 hex chars, matches the one row that already exists.
 *  - `disabled`    → default: registration and password change refuse rather than
 *                    silently downgrade to an unsalted SHA-1 digest.
 */
export type PasswordPolicy = 'pbkdf2' | 'sha1-legacy' | 'disabled';

export function parsePolicy(value: string | undefined): PasswordPolicy {
  if (value === 'pbkdf2' || value === 'sha1-legacy') return value;
  return 'disabled';
}

export interface HashedPassword {
  hash: string;
  policy: PasswordPolicy;
}

export class PasswordPolicyError extends Error {}

export async function hashForStorage(
  password: string,
  policy: PasswordPolicy,
  iterations = DEFAULT_ITERATIONS,
): Promise<HashedPassword> {
  if (policy === 'disabled') {
    throw new PasswordPolicyError(
      'Passwords cannot be stored: customer.password is VARCHAR(50). Apply the reviewed migration (migrations/002_widen_customer_password.sql) and set PASSWORD_STORAGE_POLICY=pbkdf2.',
    );
  }
  const hash = policy === 'pbkdf2' ? await hashPassword(password, iterations) : await sha1Hex(password);
  if (hash.length > MAX_LEGACY_HASH_LEN) {
    throw new PasswordPolicyError(
      `The selected password policy produces a ${hash.length}-character hash, which does not fit VARCHAR(50).`,
    );
  }
  return { hash, policy };
}

export interface AuthOutcome {
  customerId: number;
  /** True when the stored digest is legacy and should be re-hashed after login. */
  upgradeable: boolean;
}

/** Verifies a login. Read-only: no writes, no audit rows, no lockout state. */
export async function authenticate(db: Db, rawEmail: unknown, rawPassword: unknown): Promise<AuthOutcome | null> {
  const email = V.email(rawEmail);
  const password = typeof rawPassword === 'string' ? rawPassword : '';
  if (!password) return null;

  const { getLoginInfo } = await import('../db/queries.ts');
  const info = await getLoginInfo(db, email);
  if (!info || !info.password) {
    // Hash a dummy value so timing does not reveal whether the email exists.
    await verifyPassword(password, '0'.repeat(40));
    return null;
  }
  if (!(await verifyPassword(password, info.password))) return null;
  return { customerId: info.customer_id, upgradeable: needsUpgrade(info.password) };
}

/**
 * Best-effort transparent upgrade after a successful login.
 *
 * Only runs when the policy can produce a storable hash; it reads the current
 * row and rewrites it unchanged apart from the digest, and never fails a login.
 */
export async function upgradeStoredHash(
  db: Db,
  customerId: number,
  password: string,
  policy: PasswordPolicy,
  iterations = DEFAULT_ITERATIONS,
): Promise<boolean> {
  if (policy === 'disabled' || policy === 'sha1-legacy') return false;
  try {
    const customer = await getCustomer(db, customerId);
    if (!customer) return false;
    const { hash } = await hashForStorage(password, policy, iterations);
    await updateAccount(
      db,
      customerId,
      customer.name,
      customer.email,
      hash,
      customer.day_phone ?? '',
      customer.eve_phone ?? '',
      customer.mob_phone ?? '',
    );
    return true;
  } catch {
    return false;
  }
}

export interface RegistrationInput {
  name: string;
  email: string;
  password: string;
}

export function validateRegistration(input: {
  name: unknown;
  email: unknown;
  password: unknown;
  confirm: unknown;
}): RegistrationInput {
  const name = V.text(input.name, 'name', 50, { required: true });
  const email = V.email(input.email);
  const password = V.password(input.password);
  const confirm = typeof input.confirm === 'string' ? input.confirm : '';
  if (password !== confirm) throw new V.ValidationError('confirm', 'Passwords do not match');
  return { name, email, password };
}
