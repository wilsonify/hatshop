/** GET routes for the storefront and customer account. */
import { newCsrfToken, seal, serializeCookie } from '../../auth/session.ts';
import { parsePolicy } from '../../services/accounts.ts';
import { readCart } from '../../services/cart.ts';
import { catalogPage, categoryPage, departmentPage, departments, productPage, searchPage } from '../../services/catalog.ts';
import { checkoutOptions } from '../../services/checkout.ts';
import { orderForCustomer, ordersForCustomer } from '../../services/orders.ts';
import { paymentConfig } from '../../services/payments.ts';
import { getCustomer, getShippingRegions } from '../../db/queries.ts';
import { descriptionLength, productsPerPage } from '../context.ts';
import { html, json, redirect } from '../http.ts';
import { accountPage, checkoutPage, loginPage, orderPage, ordersPage, registerPage } from '../views/account.ts';
import { errorPage } from '../views/layout.ts';
import {
  cartPage,
  categoryPage as categoryView,
  departmentPage as departmentView,
  homePage,
  productPage as productView,
  searchPage as searchView,
} from '../views/store.ts';
import { CHK_COOKIE, CHK_TTL_MS } from './constants.ts';
import type { Handler } from './_shared.ts';

const NAV_LIMIT = 6;

function pageParam(url: URL): number {
  const raw = Number.parseInt(url.searchParams.get('page') ?? '1', 10);
  return Number.isFinite(raw) && raw > 0 ? raw : 1;
}

export const home: Handler = async ({ env, url, ctx, db }) => {
  const [nav, page] = await Promise.all([
    departments(db),
    catalogPage(db, pageParam(url), productsPerPage(env), descriptionLength(env)),
  ]);
  return html(homePage(ctx.view, { departments: nav.slice(0, NAV_LIMIT), items: page.items, meta: page.meta }));
};

export const department: Handler = async ({ env, url, ctx, db, params }) => {
  const departmentId = Number(params.departmentId);
  const [nav, data] = await Promise.all([
    departments(db),
    departmentPage(db, departmentId, pageParam(url), productsPerPage(env), descriptionLength(env)),
  ]);
  if (!data.details) return html(errorPage(ctx.view, 404, 'That department does not exist.'), 404);
  return html(
    departmentView(ctx.view, {
      departments: nav.slice(0, NAV_LIMIT),
      departmentId,
      details: data.details,
      categories: data.categories,
      items: data.items,
      meta: data.meta,
    }),
  );
};

export const category: Handler = async ({ env, url, ctx, db, params }) => {
  const categoryId = Number(params.categoryId);
  const [nav, data] = await Promise.all([
    departments(db),
    categoryPage(db, categoryId, pageParam(url), productsPerPage(env), descriptionLength(env)),
  ]);
  if (!data.details) return html(errorPage(ctx.view, 404, 'That category does not exist.'), 404);
  return html(
    categoryView(ctx.view, {
      categories: nav.slice(0, NAV_LIMIT),
      categoryId,
      details: data.details,
      items: data.items,
      meta: data.meta,
    }),
  );
};

export const product: Handler = async ({ env, ctx, db, params }) => {
  const productId = Number(params.productId);
  const [nav, data] = await Promise.all([departments(db), productPage(db, productId, descriptionLength(env))]);
  if (!data.details || Number(data.details.product_id ?? 0) === 0) {
    return html(errorPage(ctx.view, 404, 'That product does not exist.'), 404);
  }
  return html(
    productView(ctx.view, {
      departments: nav.slice(0, NAV_LIMIT),
      productId,
      details: data.details,
      reviews: data.reviews,
      recommendations: data.recommendations,
    }),
  );
};

export const search: Handler = async ({ env, url, ctx, db }) => {
  const query = (url.searchParams.get('q') ?? '').slice(0, 100);
  const allWords = url.searchParams.get('all') === '1';
  const [nav, data] = await Promise.all([
    departments(db),
    searchPage(db, query, allWords, pageParam(url), productsPerPage(env), descriptionLength(env)),
  ]);
  return html(
    searchView(ctx.view, {
      departments: nav.slice(0, NAV_LIMIT),
      query,
      allWords,
      words: data.words,
      items: data.items,
      meta: data.meta,
      empty: data.empty,
    }),
  );
};

export const cart: Handler = async ({ env, req, ctx, db }) => {
  const [nav, view] = await Promise.all([departments(db), readCart(db, ctx.cartId, descriptionLength(env))]);
  // Progressive enhancement asks for the badge numbers only.
  if (req.headers.get('x-summary-only') === '1') {
    return json({ items: view.itemCount, total: view.total });
  }
  return html(cartPage(ctx.view, { departments: nav.slice(0, NAV_LIMIT), cart: view }));
};

export const loginForm: Handler = async ({ ctx, db }) => {
  const nav = await departments(db);
  return html(loginPage(ctx.view, { departments: nav.slice(0, NAV_LIMIT), email: '' }));
};

export const registerForm: Handler = async ({ env, ctx, db }) => {
  const nav = await departments(db);
  const policy = parsePolicy(env.PASSWORD_STORAGE_POLICY);
  return html(
    registerPage(ctx.view, {
      departments: nav.slice(0, NAV_LIMIT),
      registrationDisabled:
        policy === 'disabled'
          ? 'Registration is unavailable on this deployment: customer.password is VARCHAR(50), so a secure hash cannot be stored. Apply the reviewed migration and set PASSWORD_STORAGE_POLICY.'
          : undefined,
    }),
  );
};

export const account: Handler = async ({ env, ctx, db }) => {
  if (ctx.customerId === null) return redirect('/login');
  const [nav, customer, regions] = await Promise.all([
    departments(db),
    getCustomer(db, ctx.customerId),
    getShippingRegions(db),
  ]);
  if (!customer) return redirect('/login');
  return html(
    accountPage(ctx.view, {
      departments: nav.slice(0, NAV_LIMIT),
      customer,
      regions,
      passwordPolicy: parsePolicy(env.PASSWORD_STORAGE_POLICY),
    }),
  );
};

export const orders: Handler = async ({ ctx, db }) => {
  if (ctx.customerId === null) return redirect('/login');
  const [nav, list] = await Promise.all([departments(db), ordersForCustomer(db, ctx.customerId)]);
  return html(ordersPage(ctx.view, { departments: nav.slice(0, NAV_LIMIT), orders: list }));
};

export const order: Handler = async ({ ctx, db, params }) => {
  if (ctx.customerId === null) return redirect('/login');
  const orderId = Number(params.orderId);
  const [nav, data] = await Promise.all([departments(db), orderForCustomer(db, ctx.customerId, orderId)]);
  if (!data) return html(errorPage(ctx.view, 404, 'That order was not found on your account.'), 404);
  return html(
    orderPage(ctx.view, { departments: nav.slice(0, NAV_LIMIT), orderId, order: data.order, details: data.details }),
  );
};

export const checkoutForm: Handler = async ({ env, ctx, db }) => {
  if (ctx.customerId === null) return redirect('/login');

  const [nav, cartView, options] = await Promise.all([
    departments(db),
    readCart(db, ctx.cartId, descriptionLength(env)),
    checkoutOptions(db, ctx.customerId),
  ]);

  const nonce = newCsrfToken();
  if (env.SESSION_SECRET) {
    ctx.cookies.push(
      serializeCookie(CHK_COOKIE, await seal({ n: nonce }, env.SESSION_SECRET), ctx.secure, {
        maxAge: CHK_TTL_MS,
      }),
    );
  }

  return html(
    checkoutPage(ctx.view, {
      departments: nav.slice(0, NAV_LIMIT),
      cart: cartView,
      options,
      paymentsConfigured: paymentConfig(env).configured,
      nonce,
    }),
  );
};
