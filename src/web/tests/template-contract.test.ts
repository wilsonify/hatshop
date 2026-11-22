/**
 * Markup contract tests.
 *
 * These render the real view functions and assert the element ids, classes, form
 * fields, action URLs and links that the stylesheet and `public/js/app.js` depend
 * on. If a page silently loses a required marker, one of these fails.
 *
 * They run with no network and no database. The served-HTML half of the contract
 * is additionally asserted against a running Worker by
 * `scripts/verify-interactive.mjs`.
 */
import assert from 'node:assert/strict';
import { test } from 'node:test';
import { pageMeta } from '../services/catalog.ts';
import type { CartProductRow, ProductListRow, SavedProductRow } from '../db/types.ts';
import type { CartView } from '../services/cart.ts';
import type { ViewContext } from '../worker/views/layout.ts';
import { accountPage, checkoutPage, loginPage, registerPage } from '../worker/views/account.ts';
import { adminLoginPage } from '../worker/views/admin.ts';
import { cartPage, homePage, productPage, searchPage } from '../worker/views/store.ts';

const CSRF = 'a'.repeat(48);
const DEPARTMENTS = [
  { department_id: 1, name: 'Mens' },
  { department_id: 2, name: 'Womens' },
];

function ctx(overrides: Partial<ViewContext> = {}): ViewContext {
  return {
    siteName: 'HatShop',
    csrf: CSRF,
    cart: { itemCount: 0, total: '0.00' },
    customer: null,
    admin: false,
    flash: null,
    ...overrides,
  };
}

const products: ProductListRow[] = [
  {
    product_id: 7,
    name: 'Beret',
    description: 'A soft wool beret',
    price: '24.99',
    discounted_price: '0.00',
    thumbnail: 'Beret.jpg',
  },
  {
    product_id: 8,
    name: 'Fedora',
    description: 'Felt fedora',
    price: '39.00',
    discounted_price: '31.20',
    thumbnail: 'Fedora.jpg',
  },
];

const cartItems: CartProductRow[] = [
  { product_id: 7, name: 'Beret', price: '24.99', quantity: 2, subtotal: '49.98' },
];
const savedItems: SavedProductRow[] = [{ product_id: 8, name: 'Fedora', price: '39.00' }];

const populatedCart: CartView = {
  items: cartItems,
  saved: savedItems,
  total: '49.98',
  itemCount: 2,
  isEmpty: false,
};
const emptyCart: CartView = { items: [], saved: [], total: '0.00', itemCount: 0, isEmpty: true };

/* ------------------------------------------------------------------ layout */

test('every page carries the hooks the stylesheet and enhancement script need', () => {
  const html = homePage(ctx(), { departments: DEPARTMENTS, items: products, meta: pageMeta(1, 6, 2) });

  assert.match(html, /^<!doctype html>\s*<html lang="en">/i);
  assert.match(html, /<link rel="stylesheet" href="\/css\/site\.css">/);
  assert.match(html, /<script type="module" src="\/js\/app\.js"><\/script>/);
  assert.match(html, /<a class="skip-link" href="#main">/);
  assert.match(html, /<main id="main"/);
  assert.match(html, /data-cart-link/);
  assert.match(html, /<form class="search" action="\/search" method="get" role="search">/);
  assert.match(html, /<input id="q" name="q" type="search"/);
  assert.match(html, /aria-label="Departments"/);
  assert.match(html, /href="\/departments\/1"/);
});

test('the cart badge reflects the server summary', () => {
  const empty = homePage(ctx(), { departments: DEPARTMENTS, items: products, meta: pageMeta(1, 6, 2) });
  assert.match(empty, /data-cart-link>Cart</);

  const full = homePage(
    ctx({ cart: { itemCount: 2, total: '49.98' } }),
    { departments: DEPARTMENTS, items: products, meta: pageMeta(1, 6, 2) },
  );
  assert.match(full, /Cart \(2\) · \$49\.98/);
});

/* ------------------------------------------------------------------- store */

test('product cards keep the grid, media, price and add-to-cart contract', () => {
  const html = homePage(ctx(), { departments: DEPARTMENTS, items: products, meta: pageMeta(1, 6, 2) });

  assert.match(html, /class="product-grid"/);
  assert.equal((html.match(/class="product-card"/g) ?? []).length, products.length);
  assert.match(html, /class="product-media" href="\/products\/7"/);
  assert.match(html, /src="\/product_images\/Beret\.thumb\.jpg"/);
  assert.match(html, /class="now">\$24\.99</);
  assert.match(html, /<s>\$39\.00<\/s>/, 'a discounted product shows the original price');

  const forms = html.match(/<form action="\/cart" method="post" class="add-form" data-cart-add>[\s\S]*?<\/form>/g) ?? [];
  assert.equal(forms.length, products.length, 'one add form per product');
  for (const form of forms) {
    assert.match(form, /name="_csrf" value="[a-f0-9]{48}"/);
    assert.match(form, /name="action" value="add"/);
    assert.match(form, /name="productId" value="\d+"/);
  }
});

test('the pager keeps its landmarks and rel attributes', () => {
  const html = homePage(ctx(), { departments: DEPARTMENTS, items: products, meta: pageMeta(2, 6, 45) });
  assert.match(html, /<nav class="pager" aria-label="Pagination">/);
  assert.match(html, /rel="prev"/);
  assert.match(html, /rel="next"/);
  assert.match(html, /<span aria-current="page">2<\/span>/);
});

test('a product page keeps the detail layout and review form contract', () => {
  const details = {
    product_id: 7,
    name: 'Beret',
    description: 'A soft wool beret',
    price: '24.99',
    discounted_price: '0.00',
    image: 'Beret.jpg',
  };

  const anonymous = productPage(ctx(), {
    departments: DEPARTMENTS,
    productId: 7,
    details,
    reviews: [],
    recommendations: [],
  });
  assert.match(anonymous, /class="product-detail"/);
  assert.match(anonymous, /class="product-detail-media"/);
  assert.match(anonymous, /data-cart-add/);
  assert.match(anonymous, /href="\/login"/, 'anonymous visitors are invited to sign in to review');

  const signedIn = productPage(
    ctx({ customer: { customerId: 1, name: 'Ada' } }),
    { departments: DEPARTMENTS, productId: 7, details, reviews: [], recommendations: [] },
  );
  assert.match(signedIn, /action="\/products\/7\/reviews" method="post"/);
  assert.match(signedIn, /<select name="rating" required>/);
  assert.match(signedIn, /<textarea name="review" rows="4" maxlength="1000" required>/);
  assert.match(signedIn, /name="_csrf"/);
});

test('search results keep the query form and result markup', () => {
  const html = searchPage(ctx(), {
    departments: DEPARTMENTS,
    query: 'beret',
    allWords: false,
    words: ['beret'],
    items: products,
    meta: pageMeta(1, 6, 2),
    empty: false,
  });
  assert.match(html, /<form action="\/search" method="get" class="search-page-form">/);
  assert.match(html, /<input id="q" name="q" type="search" value="beret" required>/);
  assert.match(html, /<input type="checkbox" name="all" value="1"/);
  assert.match(html, /class="product-grid"/);
});

/* -------------------------------------------------------------------- cart */

test('the empty cart keeps its empty state and no table', () => {
  const html = cartPage(ctx(), { departments: DEPARTMENTS, cart: emptyCart });
  assert.match(html, /class="empty"/);
  assert.match(html, /<a href="\/">Continue shopping<\/a>/);
  assert.doesNotMatch(html, /cart-table/);
  assert.doesNotMatch(html, /href="\/checkout"/);
});

test('the populated cart keeps every row control the CSS and script expect', () => {
  const html = cartPage(ctx({ cart: { itemCount: 2, total: '49.98' } }), {
    departments: DEPARTMENTS,
    cart: populatedCart,
  });

  assert.match(html, /<table class="table cart-table">/);
  assert.match(html, /<caption class="visually-hidden">Cart contents<\/caption>/);
  assert.match(html, /<a href="\/products\/7">Beret<\/a>/);
  assert.match(html, /<input type="number" name="quantity" value="2" min="0" max="99" step="1"/);
  assert.match(html, /<button type="submit" name="action" value="update" class="link-button">Update<\/button>/);
  assert.match(html, /<button type="submit" name="action" value="remove" class="link-button">Remove<\/button>/);
  assert.match(html, /<button type="submit" name="action" value="save" class="link-button">Save for later<\/button>/);
  assert.match(html, /href="\/checkout"/);
  assert.match(html, /class="button primary" href="\/checkout">Checkout<\/a>/);
  assert.match(html, /\$49\.98/, 'the server-side total is rendered');
  assert.match(html, /class="saved-list"/);
  assert.match(html, /name="action" value="move"/);
  assert.match(html, /name="returnTo" value="\/cart"/);
  assert.match(html, /name="_csrf" value="[a-f0-9]{48}"/);
});

/* ----------------------------------------------------------------- account */

test('the login and register forms keep their fields', () => {
  const login = loginPage(ctx(), { departments: DEPARTMENTS });
  assert.match(login, /<form action="\/login" method="post" class="card stacked narrow">/);
  assert.match(login, /name="email"[^>]*autocomplete="username" required/);
  assert.match(login, /name="password"[^>]*autocomplete="current-password" required/);
  assert.match(login, /name="_csrf"/);

  const register = registerPage(ctx(), { departments: DEPARTMENTS });
  assert.match(register, /<form action="\/register" method="post"/);
  for (const field of ['name', 'email', 'password', 'confirm']) {
    assert.match(register, new RegExp(`name="${field}"`), `register keeps ${field}`);
  }
  assert.match(register, /minlength="10"/);
});

test('the account page keeps all three forms and the region select', () => {
  const html = accountPage(ctx({ customer: { customerId: 1, name: 'Ada' } }), {
    departments: DEPARTMENTS,
    customer: {
      name: 'Ada',
      email: 'ada@example.com',
      address_1: '1 Street',
      address_2: null,
      city: 'Town',
      region: 'Region',
      postal_code: '12345',
      country: 'UK',
      shipping_region_id: 2,
      day_phone: null,
      eve_phone: null,
      mob_phone: null,
    },
    regions: [
      { shipping_region_id: 1, shipping_region: 'US / Canada' },
      { shipping_region_id: 2, shipping_region: 'Europe' },
    ],
    passwordPolicy: 'disabled',
  });

  assert.match(html, /<form action="\/account" method="post" class="card stacked">/);
  assert.match(html, /<form action="\/account\/address" method="post" class="card stacked">/);
  assert.match(html, /<form action="\/account\/password" method="post"/);
  assert.match(html, /<select name="shippingRegionId" required>/);
  assert.match(html, /<option value="2" selected>Europe<\/option>/);
  assert.match(html, /value="ada@example\.com"/);
  assert.match(html, /name="dayPhone"/);
  assert.match(html, /<button type="submit" disabled>Update password<\/button>/);
});

test('the checkout form keeps every field the payment path reads', () => {
  const html = checkoutPage(ctx({ customer: { customerId: 1, name: 'Ada' } }), {
    departments: DEPARTMENTS,
    cart: populatedCart,
    options: {
      shipping: [{ shipping_id: 3, shipping_type: 'Standard', shipping_cost: '0.00', shipping_region_id: 2 }],
      taxes: [{ tax_id: 1, tax_type: 'Standard', tax_percentage: '0.00' }],
    },
    paymentsConfigured: false,
    nonce: 'nonce-value',
  });

  assert.match(html, /<form action="\/checkout" method="post" class="card stacked">/);
  assert.match(html, /name="nonce" value="nonce-value"/);
  assert.match(html, /<select name="shippingId" required>/);
  assert.match(html, /<select name="taxId" required>/);
  assert.match(html, /name="cardNumber"/);
  assert.match(html, /name="expiry"/);
  assert.match(html, /name="cvv"/);
  assert.match(html, /<button type="submit" class="primary" disabled>Place order<\/button>/);
  assert.match(html, /Payments are not configured/);
  assert.match(html, /name="_csrf"/);
});

test('the admin login form keeps its action and fields', () => {
  const html = adminLoginPage(ctx(), { configured: true });
  assert.match(html, /<form action="\/admin\/login" method="post" class="card stacked narrow">/);
  assert.match(html, /name="username"[^>]*autocomplete="username" required/);
  assert.match(html, /name="password"[^>]*autocomplete="current-password" required/);
  assert.match(html, /name="_csrf" value="[a-f0-9]{48}"/);
});

test('an unconfigured admin login renders the reason instead of a form', () => {
  const html = adminLoginPage(ctx(), { configured: false });
  assert.match(html, /Administration is not configured/);
  assert.doesNotMatch(html, /action="\/admin\/login"/);
  assert.doesNotMatch(html, /name="password"/);
});

/* --------------------------------------------------------------- escaping */

test('a database value cannot inject markup into any contract marker', () => {
  const hostile = [
    {
      product_id: 9,
      name: '"><script>alert(1)</script>',
      description: '<img src=x onerror=alert(1)>',
      price: '10.00',
      discounted_price: '0.00',
      thumbnail: 'x.jpg',
    },
  ];
  const html = homePage(ctx(), { departments: DEPARTMENTS, items: hostile, meta: pageMeta(1, 6, 1) });
  assert.doesNotMatch(html, /<script>alert\(1\)<\/script>/);
  assert.doesNotMatch(html, /<img src=x onerror/);
  assert.match(html, /&lt;script&gt;alert\(1\)&lt;\/script&gt;/);
  assert.match(html, /data-cart-add/, 'the add form survives hostile data');
  assert.match(html, /name="productId" value="9"/);
});
