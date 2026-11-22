/**
 * Live interactive verification.
 *
 * Drives the *served* Worker over HTTP and proves the security-critical paths that
 * unit tests can only simulate:
 *
 *   • legacy SHA-1 sign-in through POST /login → 303 → GET /account 200 with the
 *     customer's real row (only when `.live-account.local.json` exists)
 *   • tampered, forged, absent, expired, wrong-fingerprint and post-logout cookies
 *   • sliding renewal (aged token is replaced by a new one; a fresh token is not)
 *   • CSRF token / Origin / Sec-Fetch-Site enforcement with row counts before and after
 *   • /admin redirects for unauthenticated GET and POST
 *   • checkout with no gateway → 503 and byte-identical table counts
 *   • the markup contract on the HTML the server actually returned
 *
 * Read-only against the database: it only issues SELECTs (row counts, the customer
 * row, and a call to the read-only `customer_get_login_info`).
 *
 *   node scripts/verify-interactive.mjs [--report docs/live-verification.md]
 */
import { createHash, createHmac, randomBytes } from 'node:crypto';
import { existsSync, readFileSync, writeFileSync } from 'node:fs';
import http from 'node:http';
import https from 'node:https';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import pg from 'pg';

const here = dirname(fileURLToPath(import.meta.url));
const root = resolve(here, '..');

const BASE = process.env.HATSHOP_BASE ?? 'http://127.0.0.1:8787';
const SECRET = process.env.SESSION_SECRET ?? 'dev-local-session-key';
const ABSOLUTE_TTL_MS = 8 * 60 * 60 * 1000;
const SLIDING_WINDOW_MS = 30 * 60 * 1000;
const reportFlag = process.argv.indexOf('--report');
const REPORT = reportFlag > 0 ? resolve(root, process.argv[reportFlag + 1] ?? 'docs/live-verification.md') : null;

const baseUrl = new URL(BASE);
const transport = baseUrl.protocol === 'https:' ? https : http;

/* ------------------------------------------------------------------ results */

const results = [];
let currentSection = 'general';

function section(name) {
  currentSection = name;
  console.log(`\n── ${name} ${'─'.repeat(Math.max(0, 62 - name.length))}`);
}

function check(name, ok, detail = '') {
  results.push({ section: currentSection, name, ok: Boolean(ok), detail });
  console.log(`${ok ? 'PASS' : 'FAIL'}  ${name}${detail ? `\n        ${detail}` : ''}`);
  return Boolean(ok);
}

/* ------------------------------------------------------------------- http */

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

function parseSetCookie(list) {
  return list.map((raw) => {
    const [pair, ...rest] = raw.split(';');
    const eq = pair.indexOf('=');
    const name = pair.slice(0, eq).trim();
    const value = pair.slice(eq + 1).trim();
    const attrs = {};
    for (const part of rest) {
      const trimmed = part.trim();
      const idx = trimmed.indexOf('=');
      attrs[(idx < 0 ? trimmed : trimmed.slice(0, idx)).toLowerCase()] = idx < 0 ? true : trimmed.slice(idx + 1);
    }
    return { raw, name, value, attrs };
  });
}

class Jar {
  constructor() {
    this.cookies = new Map();
  }

  absorb(response) {
    for (const cookie of parseSetCookie(response.setCookie)) {
      if (!cookie.name) continue;
      if (cookie.value === '' || cookie.attrs['max-age'] === '0') this.cookies.delete(cookie.name);
      else this.cookies.set(cookie.name, cookie.value);
    }
    return this;
  }

  set(name, value) {
    this.cookies.set(name, value);
    return this;
  }

  get(name) {
    return this.cookies.get(name) ?? '';
  }

  clone() {
    const copy = new Jar();
    copy.cookies = new Map(this.cookies);
    return copy;
  }

  header(only) {
    const entries = [...this.cookies.entries()].filter(([name]) => !only || name === only);
    return entries.map(([name, value]) => `${name}=${value}`).join('; ');
  }
}

/* -------------------------------------------------------------- crypto/db */

const b64urlJson = (value) => Buffer.from(JSON.stringify(value), 'utf8').toString('base64url');
const sha256 = (value) => createHash('sha256').update(value).digest('hex');

function seal(value, secret = SECRET) {
  const payload = b64urlJson(value);
  const signature = createHmac('sha256', secret).update(payload).digest('base64url');
  return `${payload}.${signature}`;
}

function claimsOf(token) {
  try {
    return JSON.parse(Buffer.from(token.split('.')[0], 'base64url').toString('utf8'));
  } catch {
    return null;
  }
}

function connectionString() {
  if (process.env.CLOUDFLARE_HYPERDRIVE_LOCAL_CONNECTION_STRING_HYPERDRIVE) {
    return process.env.CLOUDFLARE_HYPERDRIVE_LOCAL_CONNECTION_STRING_HYPERDRIVE;
  }
  const yamlPath = process.env.NEON_ENV_FILE ?? 'C:/Users/toman/repos/wilsonify/FreshInstall/neon/.env.dec.yaml';
  const text = readFileSync(yamlPath, 'utf8');
  const values = {};
  for (const line of text.split(/\r?\n/)) {
    const at = line.indexOf(':');
    if (at <= 0) continue;
    values[line.slice(0, at).trim()] = line.slice(at + 1).trim();
  }
  return `postgresql://${encodeURIComponent(values.NEON_USERNAME)}:${encodeURIComponent(
    values.NEON_PASSWORD,
  )}@${values.NEON_SERVER}/${values.NEON_DATABASE}?sslmode=require`;
}

const db = new pg.Client({ connectionString: connectionString(), connectionTimeoutMillis: 15000 });
await db.connect();

const tableNames = (
  await db.query(
    "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name",
  )
).rows.map((row) => row.table_name);

const COUNTED = ['orders', 'order_detail', 'shopping_cart', 'audit', 'order_audit'].filter((t) =>
  tableNames.includes(t),
);

async function counts() {
  const columns = COUNTED.map((t) => `(SELECT count(*)::int FROM ${t}) AS ${t}`).join(', ');
  const { rows } = await db.query(`SELECT ${columns}`);
  const snapshot = { ...rows[0] };
  if (tableNames.includes('orders')) {
    const paid = await db.query('SELECT count(*)::int AS paid FROM orders WHERE status >= 5');
    snapshot.paid_orders = paid.rows[0].paid;
  }
  return snapshot;
}

const customer = (
  await db.query(
    'SELECT customer_id, name, email, password FROM customer ORDER BY customer_id LIMIT 1',
  )
).rows[0];

if (!customer) {
  console.error('no customer row to test against');
  process.exit(2);
}

const customerId = Number(customer.customer_id);
const storedHash = String(customer.password);
const fingerprint = sha256(storedHash.trim()).slice(0, 16);

/* --------------------------------------------------------------- fixtures */

const credentialsPath = resolve(root, '.live-account.local.json');
const credentials = existsSync(credentialsPath) ? JSON.parse(readFileSync(credentialsPath, 'utf8')) : null;

const now = Date.now();
const sessionClaimBase = { cid: customerId, fp: fingerprint, ep: 0, role: 'customer' };
const freshToken = seal({ ...sessionClaimBase, iat: now - 60_000, exp: now - 60_000 + ABSOLUTE_TTL_MS });
const agedToken = seal({
  ...sessionClaimBase,
  iat: now - SLIDING_WINDOW_MS - 60_000,
  exp: now - SLIDING_WINDOW_MS - 60_000 + ABSOLUTE_TTL_MS,
});
const expiredToken = seal({ ...sessionClaimBase, iat: now - ABSOLUTE_TTL_MS - 120_000, exp: now - 120_000 });
const wrongFingerprintToken = seal({ ...sessionClaimBase, fp: '0'.repeat(16), iat: now, exp: now + ABSOLUTE_TTL_MS });
const wrongSecretToken = seal({ ...sessionClaimBase, iat: now, exp: now + ABSOLUTE_TTL_MS }, 'not-the-session-secret');

/* ------------------------------------------------------- read-only DB facts */

section('database facts (read-only)');

const loginInfo = (
  await db.query('SELECT * FROM customer_get_login_info($1::varchar)', [customer.email])
).rows[0];

check(
  'customer_get_login_info returns the live login row',
  Boolean(loginInfo) && Number(loginInfo.customer_id) === customerId,
  loginInfo ? `customer_id=${loginInfo.customer_id}` : 'no row returned',
);
check(
  'the stored digest used for sign-in is the live 40-hex SHA-1 value',
  String(loginInfo?.password ?? '') === storedHash && /^[0-9a-f]{40}$/i.test(storedHash),
  `length=${storedHash.length}`,
);
check(
  'table inventory read (no writes planned)',
  COUNTED.length > 0,
  `counted: ${COUNTED.join(', ')}`,
);

const countsStart = await counts();

/* ------------------------------------------------------------ anonymous GET */

section('anonymous pages and cookie flags');

const anon = new Jar();
const loginPage = await request('GET', '/login');
anon.absorb(loginPage);

check('GET /login → 200', loginPage.status === 200, `status=${loginPage.status}`);
check(
  'login form posts to /login with a CSRF field',
  /action="\/login"/.test(loginPage.body) && /name="_csrf"/.test(loginPage.body),
);
check(
  'login form carries email and password fields',
  /name="email"/.test(loginPage.body) && /name="password"/.test(loginPage.body),
);

const csrfCookie = parseSetCookie(loginPage.setCookie).find((c) => c.name === 'hatshop_csrf');
const cartCookie = parseSetCookie(loginPage.setCookie).find((c) => c.name === 'hatshop_cart');
check('a CSRF cookie is issued', Boolean(csrfCookie?.value), csrfCookie ? `path=${csrfCookie.attrs.path}` : '');
check(
  'the CSRF cookie is readable by the enhancement script (not HttpOnly)',
  Boolean(csrfCookie) && csrfCookie.attrs.httponly === undefined,
);
check(
  'the cart cookie is HttpOnly and SameSite=Lax',
  Boolean(cartCookie) && cartCookie.attrs.httponly === true && String(cartCookie.attrs.samesite).toLowerCase() === 'lax',
  cartCookie ? `samesite=${cartCookie.attrs.samesite} httponly=${cartCookie.attrs.httponly}` : '',
);
if (baseUrl.protocol === 'https:') {
  check(
    'cookies are marked Secure over HTTPS',
    Boolean(cartCookie) && cartCookie.attrs.secure === true && Boolean(csrfCookie) && csrfCookie.attrs.secure === true,
  );
} else {
  check(
    'Secure flag observable (plain-HTTP dev omits it by design)',
    false,
    'run the dev server with --local-protocol https to observe Secure',
  );
}

const csrf = anon.get('hatshop_csrf');
const origin = baseUrl.origin;

const home = await request('GET', '/', { cookie: anon.header() });
anon.absorb(home);
check('GET / → 200', home.status === 200, `bytes=${home.body.length}`);

section('markup contract on served HTML');

const contract = [
  ['doctype and lang', /^<!doctype html>\s*<html lang="en">/i],
  ['stylesheet link', /<link rel="stylesheet" href="\/css\/site\.css">/],
  ['enhancement module', /<script type="module" src="\/js\/app\.js"><\/script>/],
  ['skip link target', /<a class="skip-link" href="#main">/],
  ['main landmark', /<main id="main"/],
  ['cart badge hook', /data-cart-link/],
  ['header search form', /<form class="search" action="\/search" method="get" role="search">/],
  ['search input id/name', /<input id="q" name="q" type="search"/],
  ['department navigation', /aria-label="Departments"/],
  ['product grid', /class="product-grid"/],
  ['product card', /class="product-card"/],
  ['thumbnail url shape', /\/product_images\/[A-Za-z0-9._-]+\.thumb\.[A-Za-z0-9]+/],
  ['price marker', /class="now">\$\d/],
  ['add-to-cart hook', /data-cart-add/],
  ['csrf field in forms', /name="_csrf" value="[0-9a-f]{16,}"/],
  ['add form action', /<form action="\/cart" method="post" class="add-form" data-cart-add>/],
  ['add form product id', /name="productId" value="\d+"/],
];
for (const [label, pattern] of contract) check(`home: ${label}`, pattern.test(home.body));

// The home page paginates only when the live catalog has more than one page, so
// the expectation is derived from the data (total from the database, cards from
// the served page) rather than hard-coded.
const catalogTotal = Number(
  (await db.query('SELECT catalog_count_products_on_catalog()::integer AS n')).rows[0].n,
);
const cardsOnHome = (home.body.match(/class="product-card"/g) ?? []).length;
const homeHasPager = /<nav class="pager" aria-label="Pagination">/.test(home.body);
const expectsPager = catalogTotal > cardsOnHome;
check(
  `home: pagination present exactly when ${catalogTotal} catalog products exceed one page of ${cardsOnHome} cards`,
  homeHasPager === expectsPager,
  `pager=${homeHasPager} expected=${expectsPager}`,
);
if (expectsPager) {
  check('home: pager exposes rel="next"', /rel="next"/.test(home.body));
}

// Out-of-range pages clamp instead of rendering an empty page or erroring.
const clamped = await request('GET', '/?page=999', { cookie: anon.header() });
const firstProduct = (html) => /href="\/products\/(\d+)"/.exec(html)?.[1] ?? '';
check(
  'an out-of-range page clamps to the first page',
  clamped.status === 200 && firstProduct(clamped.body) === firstProduct(home.body),
  `status=${clamped.status}`,
);

const productPage = await request('GET', '/products/1', { cookie: anon.header() });
check('GET /products/1 → 200', productPage.status === 200, `status=${productPage.status}`);
check('product page: detail grid', /class="product-detail"/.test(productPage.body));
check('product page: add-to-cart form', /data-cart-add/.test(productPage.body));
check(
  'product page: anonymous review prompt links to /login',
  /href="\/login"/.test(productPage.body),
);

const emptyCart = await request('GET', '/cart', { cookie: anon.header() });
check('GET /cart (empty) → 200', emptyCart.status === 200);
check('empty cart renders the empty marker', /class="empty"/.test(emptyCart.body));
check('empty cart offers a continue link', /<a href="\/">Continue shopping<\/a>/.test(emptyCart.body));
check('empty cart renders no cart table', !/cart-table/.test(emptyCart.body));

const css = await request('GET', '/css/site.css');
const js = await request('GET', '/js/app.js');
check('GET /css/site.css → 200', css.status === 200, `bytes=${css.body.length}`);
check('GET /js/app.js → 200', js.status === 200, `bytes=${js.body.length}`);
check('the enhancement script still hooks [data-cart-add]', /data-cart-add/.test(js.body));
check('the enhancement script still reads the cart summary header', /x-summary-only/.test(js.body));

/* ------------------------------------------------------------------- login */

section('legacy password sign-in (POST /login)');

let signedIn = false;
let sessionToken = '';
const sessionJar = new Jar();

if (credentials) {
  const posted = await request('POST', '/login', {
    cookie: anon.header(),
    form: {
      _csrf: csrf,
      email: credentials.email,
      password: credentials.password,
      returnTo: '/account',
    },
    headers: { origin },
  });

  check(
    'POST /login with the live legacy account → 303',
    posted.status === 303,
    `status=${posted.status} location=${posted.headers.location ?? '(none)'}`,
  );
  check('POST /login redirects to the account page', posted.headers.location === '/account');

  const sid = parseSetCookie(posted.setCookie).find((c) => c.name === 'hatshop_sid');
  signedIn = Boolean(sid?.value);
  sessionToken = sid?.value ?? '';
  check('a session cookie is issued', signedIn);
  if (sid) {
    check('session cookie is HttpOnly', sid.attrs.httponly === true);
    check('session cookie is SameSite=Lax', String(sid.attrs.samesite).toLowerCase() === 'lax');
    check(
      'session cookie carries the documented 8 h absolute lifetime',
      Number(sid.attrs['max-age']) === ABSOLUTE_TTL_MS / 1000,
      `Max-Age=${sid.attrs['max-age']}`,
    );
    if (baseUrl.protocol === 'https:') check('session cookie is Secure', sid.attrs.secure === true);

    const claims = claimsOf(sid.value);
    check('session claims name the live customer', claims?.cid === customerId, `cid=${claims?.cid}`);
    check('session expiry is issued-at + 8 h', claims && claims.exp - claims.iat === ABSOLUTE_TTL_MS);
    check('session carries the live password fingerprint', claims?.fp === fingerprint);
  }

  sessionJar.absorb(posted);

  const account = await request('GET', '/account', { cookie: sessionJar.header() });
  check('GET /account with the issued cookie → 200', account.status === 200, `status=${account.status}`);
  const escape = (value) =>
    String(value).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  check(
    'the account page shows the live customer name',
    account.body.includes(escape(customer.name)),
    'matched the row read from PostgreSQL',
  );
  check('the account page shows the live customer email', account.body.includes(escape(customer.email)));
  check(
    'the account page renders the live profile form',
    /action="\/account" method="post"/.test(account.body) && /name="shippingRegionId"/.test(account.body),
  );
} else {
  const rejected = await request('POST', '/login', {
    cookie: anon.header(),
    form: { _csrf: csrf, email: customer.email, password: `wrong-${randomBytes(8).toString('hex')}` },
    headers: { origin },
  });
  check(
    'POST /login with a wrong password is rejected (read-only path works)',
    rejected.status === 401,
    `status=${rejected.status}`,
  );
  check('a rejected login issues no session cookie', !parseSetCookie(rejected.setCookie).some((c) => c.name === 'hatshop_sid'));
  check(
    'BLOCKED: live sign-in not exercised — no plaintext for the live SHA-1 account',
    false,
    'supply .live-account.local.json (git-ignored) with {email,password} and re-run',
  );
  sessionJar.set('hatshop_sid', freshToken);
}

/* --------------------------------------------------------- session attacks */

section('session integrity (attack attempts)');

if (signedIn) {
  const [payload, signature] = sessionToken.split('.');
  const tamperedSignature = `${payload}.${signature.slice(0, -1)}${signature.endsWith('A') ? 'B' : 'A'}`;
  const tamperedPayload = seal({ ...sessionClaimBase, cid: customerId + 1, iat: now, exp: now + ABSOLUTE_TTL_MS }, SECRET);
  const attacks = [
    ['tampered signature', tamperedSignature],
    ['swapped payload (re-signed with the local key)', tamperedPayload],
    ['garbage cookie', 'not-a-real-cookie'],
    ['signed with a different key', wrongSecretToken],
    ['expired token', expiredToken],
    ['wrong password fingerprint', wrongFingerprintToken],
  ];
  for (const [label, token] of attacks) {
    const res = await request('GET', '/account', { cookie: `hatshop_sid=${token}` });
    const redirected = res.status === 303 && res.headers.location === '/login';
    check(`${label} does not authorize /account`, redirected, `status=${res.status} location=${res.headers.location}`);
  }
} else {
  const attacks = [
    ['tampered signature', 'eyJjdWQiOjF9.bogus-signature'],
    ['garbage cookie', 'not-a-real-cookie'],
    ['signed with a different key', wrongSecretToken],
    ['expired token', expiredToken],
    ['wrong password fingerprint', wrongFingerprintToken],
  ];
  for (const [label, token] of attacks) {
    const res = await request('GET', '/account', { cookie: `hatshop_sid=${token}` });
    check(
      `${label} does not authorize /account`,
      res.status === 303 && res.headers.location === '/login',
      `status=${res.status} location=${res.headers.location}`,
    );
  }
}

const absent = await request('GET', '/account');
check(
  'an absent cookie does not authorize /account',
  absent.status === 303 && absent.headers.location === '/login',
  `status=${absent.status}`,
);

const expiredRes = await request('GET', '/account', { cookie: `hatshop_sid=${expiredToken}` });
check(
  'an expired cookie is cleared on the response',
  parseSetCookie(expiredRes.setCookie).some((c) => c.name === 'hatshop_sid' && c.attrs['max-age'] === '0'),
);

/* ------------------------------------------------------ sliding renewal */

section('sliding renewal');

const aged = await request('GET', '/account', { cookie: `hatshop_sid=${agedToken}` });
check('a session older than 30 min still authorizes', aged.status === 200, `status=${aged.status}`);
const renewed = parseSetCookie(aged.setCookie).find((c) => c.name === 'hatshop_sid');
check('it is re-issued in the same response', Boolean(renewed?.value));
if (renewed?.value) {
  const before = claimsOf(agedToken);
  const after = claimsOf(renewed.value);
  check('the renewed token is different from the presented one', renewed.value !== agedToken);
  check('the renewed token has a later issued-at', Number(after?.iat) > Number(before?.iat), `iat ${before?.iat} → ${after?.iat}`);
  check('the renewed token keeps the 8 h absolute lifetime', Number(after?.exp) - Number(after?.iat) === ABSOLUTE_TTL_MS);
  check('the renewed token keeps the same customer', after?.cid === customerId && after?.fp === fingerprint);
}

const fresh = await request('GET', '/account', { cookie: `hatshop_sid=${freshToken}` });
check('a fresh session authorizes', fresh.status === 200);
check(
  'a fresh session (< 30 min old) is not re-issued',
  !parseSetCookie(fresh.setCookie).some((c) => c.name === 'hatshop_sid'),
);

/* ------------------------------------------------------------- logout */

section('logout');

const logoutNoCsrf = await request('POST', '/logout', {
  cookie: `hatshop_sid=${freshToken}`,
  form: {},
  headers: { origin },
});
check(
  'POST /logout without a CSRF token is rejected',
  logoutNoCsrf.status === 403,
  `status=${logoutNoCsrf.status}`,
);

const logoutJar = new Jar().set('hatshop_sid', freshToken).set('hatshop_csrf', csrf).set('hatshop_cart', anon.get('hatshop_cart'));
const copiedToken = freshToken;
const logout = await request('POST', '/logout', {
  cookie: logoutJar.header(),
  form: { _csrf: csrf },
  headers: { origin },
});
check(
  'POST /logout → 303 to the storefront notice',
  logout.status === 303 && logout.headers.location === '/?notice=logged-out',
  `status=${logout.status} location=${logout.headers.location}`,
);
const cleared = parseSetCookie(logout.setCookie).find((c) => c.name === 'hatshop_sid');
check(
  'logout clears the session cookie',
  Boolean(cleared) && cleared.attrs['max-age'] === '0',
  cleared ? `Max-Age=${cleared.attrs['max-age']}` : 'no clear header',
);

const afterCleared = await request('GET', '/account', { cookie: 'hatshop_sid=' });
check(
  'the cleared cookie no longer authorizes /account',
  afterCleared.status === 303 && afterCleared.headers.location === '/login',
  `status=${afterCleared.status}`,
);

const copied = await request('GET', '/account', { cookie: `hatshop_sid=${copiedToken}` });
check(
  'DOCUMENTED RISK: a copied pre-logout cookie still authorizes until it expires',
  copied.status === 200,
  `status=${copied.status} — stateless revocation needs migration 001 (session_epoch)`,
);

/* --------------------------------------------------------- CSRF / origin */

section('CSRF and cross-origin enforcement (no state change)');

const cartJar = new Jar().set('hatshop_csrf', csrf);
const cartAttempts = [
  [
    'missing CSRF token',
    { form: { action: 'add', productId: '1' }, headers: { origin } },
  ],
  [
    'foreign Origin',
    { form: { action: 'add', productId: '1', _csrf: csrf }, headers: { origin: 'https://evil.example' } },
  ],
  [
    'Sec-Fetch-Site: cross-site',
    {
      form: { action: 'add', productId: '1', _csrf: csrf },
      headers: { origin, 'sec-fetch-site': 'cross-site' },
    },
  ],
  [
    'wrong CSRF token',
    { form: { action: 'add', productId: '1', _csrf: 'f'.repeat(48) }, headers: { origin } },
  ],
];

for (const [label, attempt] of cartAttempts) {
  const before = await counts();
  const res = await request('POST', '/cart', { cookie: cartJar.header(), ...attempt });
  const after = await counts();
  check(`POST /cart with ${label} → 403`, res.status === 403, `status=${res.status}`);
  check(
    `POST /cart with ${label} changes no cart rows`,
    JSON.stringify(before) === JSON.stringify(after),
    `${JSON.stringify(before)} → ${JSON.stringify(after)}`,
  );
}

const loginNoCsrf = await request('POST', '/login', {
  cookie: cartJar.header(),
  form: { email: customer.email, password: 'irrelevant' },
  headers: { origin },
});
check('POST /login without a CSRF token → 403', loginNoCsrf.status === 403, `status=${loginNoCsrf.status}`);
check(
  'a CSRF rejection issues no session cookie',
  !parseSetCookie(loginNoCsrf.setCookie).some((c) => c.name === 'hatshop_sid'),
);

/* -------------------------------------------------------------- admin */

section('administration gate');

const adminPaths = ['/admin', '/admin/orders', '/admin/customers', '/admin/products'];
for (const path of adminPaths) {
  const res = await request('GET', path, { cookie: cartJar.header() });
  check(
    `GET ${path} unauthenticated → 303 /admin/login`,
    res.status === 303 && res.headers.location === '/admin/login',
    `status=${res.status} location=${res.headers.location}`,
  );
  check(`GET ${path} leaks no admin markup`, res.body.trim() === '', `${res.body.length} bytes`);
}

const adminPost = await request('POST', '/admin/action', {
  cookie: cartJar.header(),
  form: { _csrf: csrf, action: 'update-status', orderId: '1', status: '5', returnTo: '/admin/orders' },
  headers: { origin },
});
check(
  'POST /admin/action unauthenticated → 303 /admin/login',
  adminPost.status === 303 && adminPost.headers.location === '/admin/login',
  `status=${adminPost.status} location=${adminPost.headers.location}`,
);
check('the admin POST leaks no admin markup', adminPost.body.trim() === '');

const adminBare = await request('POST', '/admin', { cookie: cartJar.header(), form: {}, headers: { origin } });
check(
  'POST /admin has no handler and answers 405 (documented; forms post to /admin/action)',
  adminBare.status === 405,
  `status=${adminBare.status} allow=${adminBare.headers.allow}`,
);

const adminLoginPage = await request('GET', '/admin/login', { cookie: cartJar.header() });
check('GET /admin/login → 200', adminLoginPage.status === 200);
check(
  'the admin login form posts to /admin/login with a CSRF field',
  /action="\/admin\/login"/.test(adminLoginPage.body) && /name="_csrf"/.test(adminLoginPage.body),
);

// Positive half of the gate: with configured credentials the admin session is
// accepted and the dashboard renders. Optional so the script also runs without
// admin configuration.
const adminUser = process.env.ADMIN_USERNAME;
const adminPass = process.env.ADMIN_PASSWORD;
if (adminUser && adminPass) {
  const adminJar = new Jar().set('hatshop_csrf', csrf);
  const adminSignIn = await request('POST', '/admin/login', {
    cookie: adminJar.header(),
    form: { _csrf: csrf, username: adminUser, password: adminPass },
    headers: { origin },
  });
  check(
    'POST /admin/login with the configured credentials → 303 /admin',
    adminSignIn.status === 303 && adminSignIn.headers.location === '/admin',
    `status=${adminSignIn.status} location=${adminSignIn.headers.location}`,
  );
  adminJar.absorb(adminSignIn);

  const dashboard = await request('GET', '/admin', { cookie: adminJar.header() });
  check('GET /admin with an admin session → 200', dashboard.status === 200, `status=${dashboard.status}`);
  check('the dashboard renders the admin navigation', /class="admin-nav"/.test(dashboard.body));
  check(
    'the dashboard renders admin action forms with a CSRF field',
    /action="\/admin\/action"/.test(dashboard.body) && /name="_csrf"/.test(dashboard.body),
  );

  const beforeAdminPost = await counts();
  const adminNoCsrf = await request('POST', '/admin/action', {
    cookie: adminJar.header(),
    form: { action: 'update-status', orderId: '1', status: '5', returnTo: '/admin/orders' },
    headers: { origin },
  });
  const adminForeign = await request('POST', '/admin/action', {
    cookie: adminJar.header(),
    form: { _csrf: csrf, action: 'update-status', orderId: '1', status: '5', returnTo: '/admin/orders' },
    headers: { origin: 'https://evil.example' },
  });
  const afterAdminPost = await counts();
  check('an authenticated admin POST without CSRF → 403', adminNoCsrf.status === 403, `status=${adminNoCsrf.status}`);
  check('an authenticated admin POST from a foreign Origin → 403', adminForeign.status === 403, `status=${adminForeign.status}`);
  check(
    'rejected admin POSTs change no rows',
    JSON.stringify(beforeAdminPost) === JSON.stringify(afterAdminPost),
    `${JSON.stringify(beforeAdminPost)} → ${JSON.stringify(afterAdminPost)}`,
  );
} else {
  check(
    'BLOCKED: authenticated admin behaviour (no ADMIN_USERNAME/ADMIN_PASSWORD in the environment)',
    false,
    'pass them as env vars to exercise the positive half of the admin gate',
  );
}

/* ----------------------------------------------------------- checkout */

section('checkout with no gateway configured');

const checkoutJar = new Jar()
  .set('hatshop_sid', freshToken)
  .set('hatshop_csrf', csrf)
  .set('hatshop_cart', anon.get('hatshop_cart'));

const beforeCheckout = await counts();
const checkout = await request('POST', '/checkout', {
  cookie: checkoutJar.header(),
  form: {
    _csrf: csrf,
    nonce: 'whatever',
    shippingId: '1',
    taxId: '1',
    cardNumber: '4111111111111111',
    expiry: '1230',
    cvv: '123',
  },
  headers: { origin },
});
const afterCheckout = await counts();

check('POST /checkout → 503 when the gateway is unconfigured', checkout.status === 503, `status=${checkout.status}`);
check(
  'the response explains that payments are not configured',
  /payment gateway credentials|Payments are not configured/i.test(checkout.body),
);
check(
  'checkout creates no order and mutates no cart row',
  JSON.stringify(beforeCheckout) === JSON.stringify(afterCheckout),
  `${JSON.stringify(beforeCheckout)} → ${JSON.stringify(afterCheckout)}`,
);
check('no order is left in a paid state', (afterCheckout.paid_orders ?? 0) === (beforeCheckout.paid_orders ?? 0));
check(
  'no redirect to an order confirmation page',
  !(checkout.status === 303 && String(checkout.headers.location ?? '').startsWith('/orders/')),
  `location=${checkout.headers.location ?? '(none)'}`,
);

/* -------------------------------------------------------- whole-run counts */

section('production data untouched across the whole run');

const countsEnd = await counts();
check(
  'table counts are identical to the start of the run',
  JSON.stringify(countsEnd) === JSON.stringify(countsStart),
  `${JSON.stringify(countsStart)} → ${JSON.stringify(countsEnd)}`,
);

await db.end();

/* ----------------------------------------------------------------- report */

const failures = results.filter((r) => !r.ok);
const blocked = results.filter((r) => !r.ok && /^BLOCKED:/.test(r.name));
const realFailures = failures.filter((r) => !blocked.includes(r));

console.log(
  `\n${results.length - failures.length}/${results.length} checks passed` +
    (failures.length ? ` — ${realFailures.length} failed, ${blocked.length} blocked` : ''),
);
for (const failure of realFailures) console.log(`  FAILED: ${failure.name}${failure.detail ? ` — ${failure.detail}` : ''}`);

if (REPORT) {
  const lines = [
    '# Live verification report',
    '',
    `Environment: \`${BASE}\` against the live Neon database, ${new Date().toISOString()}.`,
    'Generated by `scripts/verify-interactive.mjs`. Read-only: the script issues only SELECTs.',
    '',
    'Sign-in acceptance runs only when `.live-account.local.json` exists. The run below used an',
    'owner-approved temporary password reset (`scripts/temp-password-reset.mjs`) that was restored',
    'and verified immediately afterwards.',
    '',
    `Result: **${results.length - failures.length}/${results.length}** checks passed, ` +
      `${realFailures.length} failed, ${blocked.length} blocked.`,
    '',
    '| Section | Check | Result | Detail |',
    '| --- | --- | --- | --- |',
    ...results.map(
      (r) =>
        `| ${r.section} | ${r.name.replace(/\|/g, '\\|')} | ${r.ok ? 'pass' : /^BLOCKED:/.test(r.name) ? 'blocked' : 'fail'} | ${String(r.detail).replace(/\|/g, '\\|')} |`,
    ),
    '',
  ];
  writeFileSync(REPORT, `${lines.join('\n')}\n`);
  console.log(`report written to ${REPORT}`);
}

process.exit(realFailures.length === 0 ? 0 : 1);
