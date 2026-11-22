/**
 * CSRF protection: double-submit token plus an Origin/Sec-Fetch-Site gate.
 *
 * The token cookie is intentionally readable by JavaScript (not HttpOnly) so
 * progressive-enhancement code can echo it in a header; the authoritative check
 * is server-side and compares cookie, form field and header.
 */
import { timingSafeEqual } from './password.ts';
import { newCsrfToken } from './session.ts';

export const CSRF_FIELD = '_csrf';
export const CSRF_HEADER = 'x-csrf-token';

export { newCsrfToken };

export interface CsrfContext {
  /** Value stored in the visitor's CSRF cookie. */
  cookieToken: string | undefined;
}

export async function readSubmittedToken(req: Request, form?: FormData): Promise<string> {
  const header = req.headers.get(CSRF_HEADER);
  if (header) return header;
  if (form) {
    const value = form.get(CSRF_FIELD);
    if (typeof value === 'string') return value;
  }
  return '';
}

/**
 * True when the request may mutate state.
 *
 * Both a same-origin signal and a matching token are required; a missing token
 * or a foreign Origin is a rejection, never a warning.
 */
export function isSameOrigin(req: Request, url: URL): boolean {
  const origin = req.headers.get('origin');
  if (origin) {
    try {
      if (new URL(origin).host !== url.host) return false;
    } catch {
      return false;
    }
  }
  const site = req.headers.get('sec-fetch-site');
  if (site && site !== 'same-origin' && site !== 'none') return false;
  return true;
}

export function verifyCsrf(ctx: CsrfContext, req: Request, url: URL, submitted: string): boolean {
  if (!isSameOrigin(req, url)) return false;
  const expected = ctx.cookieToken;
  if (!expected || !submitted) return false;
  return timingSafeEqual(expected, submitted);
}
