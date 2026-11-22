/**
 * HTTP probe shared by the live verification scripts.
 *
 * `request` returns the superset shape both call sites need
 * (`{ status, headers, body, setCookie }`), and self-signed certificates are
 * accepted because `wrangler dev --local-protocol https` uses one.
 */
import http from 'node:http';
import https from 'node:https';

export const DEFAULT_BASE = process.env.HATSHOP_BASE ?? 'https://127.0.0.1:8787';

export function createProbe(base = DEFAULT_BASE) {
  const baseUrl = new URL(base);
  const transport = baseUrl.protocol === 'https:' ? https : http;

  function request(method, path, { cookie, form, headers = {} } = {}) {
    const body = form ? new URLSearchParams(form).toString() : null;
    const requestHeaders = { ...headers };
    if (body !== null) {
      requestHeaders['content-type'] = 'application/x-www-form-urlencoded';
      requestHeaders['content-length'] = Buffer.byteLength(body);
    }
    if (cookie) requestHeaders.cookie = cookie;

    return new Promise((resolvePromise, reject) => {
      const req = transport.request(
        {
          hostname: baseUrl.hostname,
          port: baseUrl.port,
          path,
          method,
          headers: requestHeaders,
          rejectUnauthorized: false,
        },
        (res) => {
          let text = '';
          res.setEncoding('utf8');
          res.on('data', (chunk) => {
            text += chunk;
          });
          res.on('end', () => {
            resolvePromise({
              status: res.statusCode,
              headers: res.headers,
              body: text,
              setCookie: res.headers['set-cookie'] ?? [],
            });
          });
        },
      );
      req.on('error', reject);
      if (body !== null) req.write(body);
      req.end();
    });
  }

  return { baseUrl, request };
}

/** Reads the CSRF token from Set-Cookie headers without a regular expression. */
export function csrfFrom(setCookie = []) {
  const prefix = 'hatshop_csrf=';
  for (const raw of setCookie) {
    if (!raw.startsWith(prefix)) continue;
    const end = raw.indexOf(';');
    return raw.slice(prefix.length, end < 0 ? undefined : end);
  }
  return '';
}
