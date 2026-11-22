import assert from 'node:assert/strict';
import { test } from 'node:test';
import { matchRoute } from '../worker/router.ts';
import { e, money, paginationLinks, productImage, truncate } from '../worker/html.ts';
import type { RequestContext } from '../worker/context.ts';
import type { Env } from '../worker/env.ts';
import { pageMeta, searchPage as searchService } from '../services/catalog.ts';
import { fakeDb, one } from './fake-db.ts';

const env: Env = {
  ASSETS: { fetch: async () => new Response('static') },
  HYPERDRIVE: { connectionString: 'postgresql://unused' },
  SITE_NAME: 'HatShop',
  PRODUCTS_PER_PAGE: '6',
  SHORT_DESCRIPTION_LENGTH: '150',
};

function context(overrides: Partial<RequestContext> & { admin?: boolean; customerId?: number | null } = {}): RequestContext {
  const { admin = false, customerId = null, ...rest } = overrides;
  return {
    cartId: 'c'.repeat(128),
    customerId,
    cookies: [],
    secure: false,
    view: {
      siteName: 'HatShop',
      csrf: 'csrf-token',
      cart: { itemCount: 0, total: '0.00' },
      customer: customerId ? { customerId, name: 'Ada' } : null,
      admin,
      flash: null,
    },
    ...rest,
  };
}

test('routes resolve with named parameters', () => {
  assert.equal(matchRoute('GET', '/')?.name, 'home');
  assert.equal(matchRoute('GET', '/departments/3')?.params.departmentId, '3');
  assert.equal(matchRoute('GET', '/products/91')?.params.productId, '91');
  assert.equal(matchRoute('POST', '/checkout')?.name, 'checkout');
  assert.equal(matchRoute('GET', '/admin/orders/12')?.params.orderId, '12');
  assert.equal(matchRoute('GET', '/nope'), null);
  assert.equal(matchRoute('DELETE', '/cart'), null);
  assert.equal(matchRoute('HEAD', '/')?.name, 'home', 'HEAD uses the GET route');
});

test('pagination clamps and computes offsets', () => {
  const meta = pageMeta(2, 6, 45);
  assert.equal(meta.pages, 8);
  assert.equal(meta.offset, 6);

  assert.equal(pageMeta(99, 6, 45).page, 8, 'out-of-range page clamps');
  assert.equal(pageMeta(0, 6, 45).page, 1);
  assert.equal(pageMeta(1, 6, 0).pages, 1);
});

test('pagination links stay within bounds', () => {
  const links = paginationLinks(5, 20, (page) => `/x?page=${page}`);
  assert.equal(links.length, 9);
  assert.equal(links[0]?.page, 1);
  assert.equal(links.at(-1)?.page, 9);
  assert.equal(links.find((l) => l.current)?.page, 5);
});

test('HTML escaping blocks script injection', () => {
  assert.equal(e('<script>alert(1)</script>'), '&lt;script&gt;alert(1)&lt;/script&gt;');
  assert.equal(e('" onmouseover="x'), '&quot; onmouseover=&quot;x');
  assert.equal(e(null), '');
});

test('image references cannot escape the asset directory', () => {
  assert.equal(productImage('Hat.jpg'), '/product_images/Hat.jpg');
  assert.equal(productImage('Hat.jpg', true), '/product_images/Hat.thumb.jpg');
  assert.equal(productImage('../../etc/passwd'), '/images/not_available.jpg');
  assert.equal(productImage('evil<script>.jpg'), '/images/not_available.jpg');
  assert.equal(productImage(null), '/images/not_available.jpg');
  assert.equal(productImage('Hat.thumb.jpg', true), '/product_images/Hat.thumb.jpg');
});

test('money and truncation format predictably', () => {
  assert.equal(money('12.5'), '$12.50');
  assert.equal(money(null), '$0.00');
  assert.equal(money('not-a-number'), '$0.00');
  assert.equal(truncate('abcdef', 4), 'abc…');
  assert.equal(truncate('abc', 4), 'abc');
});

test('search sends database-derived words and reports an empty query', async () => {
  const db = fakeDb([
    [/catalog_count_search_result/, one({ n: 1 })],
    [/catalog_search/, () => [{ product_id: 9, name: 'Cap', description: 'x', price: '9.00', discounted_price: '0.00', thumbnail: null }]],
  ]);

  const hit = await searchService(db, 'Red  Hat!', true, 1, 6, 150);
  assert.deepEqual(hit.words, ['red', 'hat']);
  assert.equal(hit.meta.total, 1);
  assert.equal(db.callParams(/catalog_search/)[0]?.[1], 'on', 'all-words flag passed through');

  const empty = await searchService(fakeDb(), '', false, 1, 6, 150);
  assert.equal(empty.empty, true);
  assert.equal(empty.items.length, 0);
});

test('home page renders database products as escaped HTML', async () => {
  const { home } = await import('../worker/routes/pages.ts');
  const db = fakeDb([
    [/catalog_count_products_on_catalog/, one({ n: 1 })],
    [
      /catalog_get_products_on_catalog/,
      () => [
        {
          product_id: 1,
          name: '<script>alert(1)</script>',
          description: 'A hat',
          price: '20.00',
          discounted_price: '15.00',
          thumbnail: 'Hat.jpg',
        },
      ],
    ],
    [/catalog_get_departments_list/, () => [{ department_id: 1, name: 'Mens', description: null }]],
  ]);

  const response = await home({
    name: 'home',
    params: {},
    req: new Request('http://localhost/'),
    env,
    url: new URL('http://localhost/'),
    ctx: context(),
    db,
  });

  const body = await response.text();
  assert.equal(response.status, 200);
  assert.match(body, /&lt;script&gt;alert\(1\)&lt;\/script&gt;/);
  assert.ok(!body.includes('<script>alert(1)</script>'), 'raw script tag must not be emitted');
  assert.match(body, /\$15\.00/);
  assert.match(body, /Mens/, 'department navigation is rendered');
});

test('a missing product returns 404 rather than an empty page', async () => {
  const { product } = await import('../worker/routes/pages.ts');
  const db = fakeDb();
  const response = await product({
    name: 'product',
    params: { productId: '999' },
    req: new Request('http://localhost/products/999'),
    env,
    url: new URL('http://localhost/products/999'),
    ctx: context(),
    db,
  });
  assert.equal(response.status, 404);
});

test('admin pages redirect to the login screen without an admin session', async () => {
  const { adminHome } = await import('../worker/routes/admin.ts');
  const response = await adminHome({
    name: 'adminHome',
    params: {},
    req: new Request('http://localhost/admin'),
    env,
    url: new URL('http://localhost/admin'),
    ctx: context(),
    db: fakeDb(),
  });

  assert.equal(response.status, 303);
  assert.equal(response.headers.get('location'), '/admin/login');
});

test('customer account requires a session', async () => {
  const { account } = await import('../worker/routes/pages.ts');
  const response = await account({
    name: 'account',
    params: {},
    req: new Request('http://localhost/account'),
    env,
    url: new URL('http://localhost/account'),
    ctx: context(),
    db: fakeDb(),
  });
  assert.equal(response.status, 303);
  assert.equal(response.headers.get('location'), '/login');
});
