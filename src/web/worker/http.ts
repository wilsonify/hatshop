/** HTTP response construction with a single place for security headers. */

const SECURITY_HEADERS: Record<string, string> = {
  'content-security-policy':
    "default-src 'self'; img-src 'self' data:; style-src 'self'; script-src 'self'; connect-src 'self'; form-action 'self'; base-uri 'none'; frame-ancestors 'none'; object-src 'none'",
  'x-content-type-options': 'nosniff',
  'x-frame-options': 'DENY',
  'referrer-policy': 'strict-origin-when-cross-origin',
  'permissions-policy': 'geolocation=(), microphone=(), camera=()',
  'cross-origin-opener-policy': 'same-origin',
  'cross-origin-resource-policy': 'same-origin',
};

function headersWith(init: Record<string, string>, cookies: string[]): Headers {
  const headers = new Headers(init);
  for (const cookie of cookies) headers.append('set-cookie', cookie);
  return headers;
}

export function html(body: string, status = 200, cookies: string[] = []): Response {
  return new Response(body, {
    status,
    headers: headersWith({ 'content-type': 'text/html; charset=utf-8', 'cache-control': 'no-store' }, cookies),
  });
}

export function redirect(location: string, cookies: string[] = [], status = 303): Response {
  return new Response(null, {
    status,
    headers: headersWith({ location, 'cache-control': 'no-store' }, cookies),
  });
}

export function json(data: unknown, status = 200, cookies: string[] = []): Response {
  return new Response(JSON.stringify(data), {
    status,
    headers: headersWith({ 'content-type': 'application/json; charset=utf-8', 'cache-control': 'no-store' }, cookies),
  });
}

export function health(status: number, body: Record<string, unknown>): Response {
  return new Response(JSON.stringify(body), {
    status,
    headers: { 'content-type': 'application/json; charset=utf-8', 'cache-control': 'no-store' },
  });
}

/** Adds HSTS on HTTPS and the common hardening headers to any response. */
export function harden(response: Response, url: URL): Response {
  const secured = new Response(response.body, response);
  for (const [name, value] of Object.entries(SECURITY_HEADERS)) {
    if (!secured.headers.has(name)) secured.headers.set(name, value);
  }
  if (url.protocol === 'https:') {
    secured.headers.set('strict-transport-security', 'max-age=31536000; includeSubDomains');
  }
  return secured;
}

export function wantsHtml(req: Request): boolean {
  return (req.headers.get('accept') ?? '').includes('text/html');
}
