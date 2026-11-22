/** Per-request context: session, cart identity, CSRF token and flash message. */
import { passwordFingerprint } from '../auth/password.ts';
import {
  ABSOLUTE_TTL_MS,
  CSRF_COOKIE,
  CART_COOKIE,
  SESSION_COOKIE,
  ADMIN_COOKIE,
  clearCookie,
  createSession,
  newCartId,
  newCsrfToken,
  open,
  readSession,
  seal,
  serializeCookie,
  parseCookies,
} from '../auth/session.ts';
import { cartSummary, sessionEpoch } from '../db/queries.ts';
import type { Db } from '../db/types.ts';
import type { Env } from './env.ts';
import { num, siteName } from './env.ts';
import type { Flash, ViewContext } from './views/layout.ts';

export interface RequestContext {
  view: ViewContext;
  cartId: string;
  customerId: number | null;
  /** Cookies to set on the response (cart minting, CSRF, sliding refresh). */
  cookies: string[];
  secure: boolean;
}

const NOTICES: Record<string, Flash> = {
  'cart-added': { kind: 'ok', message: 'Added to your cart.' },
  'cart-updated': { kind: 'ok', message: 'Cart updated.' },
  'cart-removed': { kind: 'ok', message: 'Item removed from your cart.' },
  'cart-saved': { kind: 'ok', message: 'Saved for later.' },
  'cart-moved': { kind: 'ok', message: 'Moved back into your cart.' },
  'order-placed': { kind: 'ok', message: 'Thank you — your order is confirmed and paid.' },
  'logged-out': { kind: 'ok', message: 'You have been signed out.' },
  'account-saved': { kind: 'ok', message: 'Your details were saved.' },
  'password-saved': { kind: 'ok', message: 'Your password was updated.' },
  'admin-saved': { kind: 'ok', message: 'Change saved.' },
  'admin-failed': { kind: 'error', message: 'That change could not be applied.' },
};

export function flashFromUrl(url: URL): Flash | null {
  const notice = url.searchParams.get('notice');
  if (!notice) return null;
  return NOTICES[notice] ?? null;
}

/**
 * Resolves identity for one request.
 *
 * Query budget on a cold page: one statement for the cart summary and, when a
 * session cookie is present, one for the customer. Nothing else is loaded until a
 * route asks for it.
 */
export async function buildContext(req: Request, env: Env, url: URL, db: Db): Promise<RequestContext> {
  const cookies = parseCookies(req.headers.get('cookie'));
  const secure = url.protocol === 'https:';
  const out: string[] = [];
  const secret = env.SESSION_SECRET;

  // Cart identity: minted once, signed, and stable for repeat visits.
  let cartId = '';
  const cartToken = cookies[CART_COOKIE];
  if (cartToken) {
    const claims = await open<{ cid: string }>(cartToken, secret ?? '');
    if (claims?.cid && /^[0-9a-f]{128}$/.test(claims.cid)) cartId = claims.cid;
  }
  if (!cartId) {
    cartId = newCartId();
    if (secret) out.push(serializeCookie(CART_COOKIE, await seal({ cid: cartId }, secret), secure));
  }

  // CSRF token: double-submit cookie, readable by our own JavaScript.
  let csrf = cookies[CSRF_COOKIE] ?? '';
  if (!csrf) {
    csrf = newCsrfToken();
    out.push(serializeCookie(CSRF_COOKIE, csrf, secure, { httpOnly: false }));
  }

  let customerId: number | null = null;
  let customerName: string | null = null;
  let admin = false;

  if (secret) {
    const session = await readSession(cookies[SESSION_COOKIE], secret);
    if (session?.expired) {
      // Expired credentials are cleared so the browser stops sending them.
      out.push(clearCookie(SESSION_COOKIE, secure));
    } else if (session && session.claims.role === 'customer') {
      const epochEnabled = env.SESSION_EPOCH_ENABLED === '1' || env.SESSION_EPOCH_ENABLED === 'true';
      let allowed = true;
      if (epochEnabled) {
        const row = await sessionEpoch(db, session.claims.cid);
        allowed = (row ? Number(row.session_epoch) : 0) === session.claims.ep;
      }

      if (!allowed) {
        out.push(clearCookie(SESSION_COOKIE, secure));
      } else {
        const storedHash = (await customerPasswordHash(db, session.claims.cid)) ?? '';
        if ((await passwordFingerprint(storedHash)) !== session.claims.fp) {
          // The password changed: this token is no longer valid.
          out.push(clearCookie(SESSION_COOKIE, secure));
        } else {
          customerId = session.claims.cid;
          if (session.refresh) {
            // Sliding renewal: issue a *new* token (new iat/exp), never echo the
            // presented one, so the session genuinely rolls forward.
            const renewed = await createSession(customerId, storedHash, secret, Date.now());
            out.push(
              serializeCookie(SESSION_COOKIE, renewed, secure, { maxAge: ABSOLUTE_TTL_MS }),
            );
          }
        }
      }
    }

    const adminSession = await readSession(cookies[ADMIN_COOKIE], secret);
    if (adminSession && !adminSession.expired && adminSession.claims.role === 'admin') admin = true;
  }

  if (customerId !== null) {
    const name = await customerNameFor(db, customerId);
    customerName = name;
    if (!name) customerId = null;
  }

  let cart: { itemCount: number; total: string } | null = null;
  try {
    const summary = await cartSummary(db, cartId);
    const count = summary ? Number(summary.items) : 0;
    const total = summary ? Number(summary.total ?? 0) : 0;
    cart = { itemCount: Number.isFinite(count) ? count : 0, total: total.toFixed(2) };
  } catch {
    cart = null;
  }

  const view: ViewContext = {
    siteName: siteName(env),
    csrf,
    cart,
    customer: customerId !== null && customerName ? { customerId, name: customerName } : null,
    admin,
    flash: flashFromUrl(url),
  };

  return { view, cartId, customerId, cookies: out, secure };
}

/** Reads only the stored digest, needed to bind a session to the password. */
async function customerPasswordHash(db: Db, customerId: number): Promise<string | null> {
  const result = await db.query<{ password: string }>(
    `SELECT password FROM customer WHERE customer_id = $1::integer`,
    [customerId],
  );
  return result.rows[0]?.password ?? null;
}

async function customerNameFor(db: Db, customerId: number): Promise<string | null> {
  const result = await db.query<{ name: string }>(
    `SELECT name FROM customer WHERE customer_id = $1::integer`,
    [customerId],
  );
  return result.rows[0]?.name ?? null;
}

export function productsPerPage(env: Env): number {
  return num(env.PRODUCTS_PER_PAGE, 6);
}

export function descriptionLength(env: Env): number {
  return num(env.SHORT_DESCRIPTION_LENGTH, 150);
}
