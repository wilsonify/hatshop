/** Worker bindings and configuration. Secrets are provided by `wrangler secret`. */
export interface Env {
  /** Static asset bundle (public/). */
  ASSETS: { fetch(request: Request): Promise<Response> };
  /** Hyperdrive binding; locally supplied via wrangler dev. */
  HYPERDRIVE: { connectionString: string };

  SITE_NAME?: string;
  ENVIRONMENT?: string;

  /** HMAC key for session, cart and admin cookies. Required for any auth route. */
  SESSION_SECRET?: string;

  /** Admin credentials: username plus a SHA-256 hex digest of the password. */
  ADMIN_USERNAME?: string;
  ADMIN_PASSWORD_HASH?: string;

  /** `pbkdf2` (needs migration 002) | `sha1-legacy` | unset = disabled. */
  PASSWORD_STORAGE_POLICY?: string;
  PASSWORD_ITERATIONS?: string;

  /** Only meaningful once migration 001 is applied. */
  SESSION_EPOCH_ENABLED?: string;

  /** `sandbox` (default) or `production`. */
  PAYMENT_MODE?: string;
  AUTHORIZE_NET_URL?: string;
  AUTHORIZE_NET_LOGIN_ID?: string;
  AUTHORIZE_NET_TRANSACTION_KEY?: string;

  PRODUCTS_PER_PAGE?: string;
  SHORT_DESCRIPTION_LENGTH?: string;

  /** Enables the dev-only /__bench password hashing measurement. */
  ENABLE_BENCH?: string;
}

export function num(value: string | undefined, fallback: number): number {
  const parsed = Number.parseInt(value ?? '', 10);
  return Number.isFinite(parsed) && parsed > 0 ? parsed : fallback;
}

export function bool(value: string | undefined): boolean {
  return value === '1' || value === 'true' || value === 'yes';
}

export function siteName(env: Env): string {
  return env.SITE_NAME || 'HatShop';
}
