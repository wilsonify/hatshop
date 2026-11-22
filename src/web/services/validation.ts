/** Input validation shared by every route. Throws `ValidationError`. */

export class ValidationError extends Error {
  readonly field: string;
  constructor(field: string, message: string) {
    super(message);
    this.field = field;
    this.name = 'ValidationError';
  }
}

/** Trimmed string without control characters, capped at the column width. */
export function text(value: unknown, field: string, max: number, { required = false } = {}): string {
  const raw = typeof value === 'string' ? value : '';
  const clean = raw.replace(/[\u0000-\u0008\u000b\u000c\u000e-\u001f]/g, '').trim();
  if (required && clean.length === 0) throw new ValidationError(field, `${field} is required`);
  if (clean.length > max) throw new ValidationError(field, `${field} must be ${max} characters or fewer`);
  return clean;
}

export function integer(value: unknown, field: string, { min = 0, max = 2_147_483_647 } = {}): number {
  const n = typeof value === 'number' ? value : Number.parseInt(String(value ?? ''), 10);
  if (!Number.isInteger(n) || n < min || n > max) throw new ValidationError(field, `${field} is invalid`);
  return n;
}

/** Money as a canonical 2-decimal string; the database receives a numeric, never a float. */
export function money(value: unknown, field: string): string {
  const raw = String(value ?? '').trim();
  if (!/^\d{1,8}(\.\d{1,2})?$/.test(raw)) throw new ValidationError(field, `${field} must be a price like 12.50`);
  return Number(raw).toFixed(2);
}

export function email(value: unknown, field = 'email'): string {
  const raw = text(value, field, 100, { required: true }).toLowerCase();
  if (!/^[^@\s]+@[^@\s.]+\.[^@\s]{2,}$/.test(raw)) throw new ValidationError(field, 'Enter a valid email address');
  return raw;
}

/** Password policy: 10+ characters, at least one letter and one digit. */
export function password(value: unknown, field = 'password'): string {
  const raw = typeof value === 'string' ? value : '';
  if (raw.length < 10) throw new ValidationError(field, 'Password must be at least 10 characters');
  if (raw.length > 200) throw new ValidationError(field, 'Password is too long');
  if (!/[A-Za-z]/.test(raw) || !/\d/.test(raw)) {
    throw new ValidationError(field, 'Password must contain a letter and a number');
  }
  return raw;
}

/** Parses "1,2,3" / repeated form fields into unique positive ints. */
export function idList(values: unknown[], field: string): number[] {
  const out = new Set<number>();
  for (const value of values) {
    const n = integer(value, field, { min: 1 });
    out.add(n);
  }
  return [...out];
}

export function firstError(error: unknown): { field: string; message: string } {
  if (error instanceof ValidationError) return { field: error.field, message: error.message };
  return { field: '', message: 'Please check the values you entered.' };
}
