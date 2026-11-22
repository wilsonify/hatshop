/**
 * Worker entry point.
 *
 * Responsibilities: security headers, one database connection per request,
 * error containment (no stack traces, no driver messages) and the health probe.
 * Route handlers contain the feature logic.
 */
import { ValidationError } from '../services/validation.ts';
import { PasswordPolicyError } from '../services/accounts.ts';
import { withDb } from '../db/client.ts';
import type { Db } from '../db/types.ts';
import { runBench } from './bench.ts';
import { buildContext, type RequestContext } from './context.ts';
import type { Env } from './env.ts';
import { harden, health, html } from './http.ts';
import { matchRoute } from './router.ts';
import { errorPage } from './views/layout.ts';
import * as adminRoutes from './routes/admin.ts';
import * as apiRoutes from './routes/api.ts';
import * as pageRoutes from './routes/pages.ts';
import type { Handler, RouteArgs } from './routes/_shared.ts';

const HANDLERS: Record<string, Handler> = {
  home: pageRoutes.home,
  department: pageRoutes.department,
  category: pageRoutes.category,
  product: pageRoutes.product,
  search: pageRoutes.search,
  cart: pageRoutes.cart,
  loginForm: pageRoutes.loginForm,
  registerForm: pageRoutes.registerForm,
  account: pageRoutes.account,
  orders: pageRoutes.orders,
  order: pageRoutes.order,
  checkoutForm: pageRoutes.checkoutForm,
  cartAction: apiRoutes.cartAction,
  login: apiRoutes.login,
  register: apiRoutes.register,
  logout: apiRoutes.logout,
  accountUpdate: apiRoutes.accountUpdate,
  addressUpdate: apiRoutes.addressUpdate,
  passwordUpdate: apiRoutes.passwordUpdate,
  postReview: apiRoutes.postReview,
  checkout: apiRoutes.checkout,
  adminLoginForm: adminRoutes.adminLoginForm,
  adminLogin: adminRoutes.adminLogin,
  adminLogout: adminRoutes.adminLogout,
  adminHome: adminRoutes.adminHome,
  adminProducts: adminRoutes.adminProducts,
  adminProduct: adminRoutes.adminProduct,
  adminOrders: adminRoutes.adminOrders,
  adminOrder: adminRoutes.adminOrder,
  adminCatalog: adminRoutes.adminCatalog,
  adminCustomers: adminRoutes.adminCustomers,
  adminCustomer: adminRoutes.adminCustomer,
  adminAction: adminRoutes.adminAction,
};

function withCookies(response: Response, cookies: string[]): Response {
  if (cookies.length === 0) return response;
  const headers = new Headers(response.headers);
  const existing = headers.get('set-cookie') ?? '';
  for (const cookie of cookies) {
    const name = cookie.split('=')[0] ?? '';
    // A route that already set this cookie wins (e.g. a fresh session on login).
    if (name && existing.includes(`${name}=`)) continue;
    headers.append('set-cookie', cookie);
  }
  return new Response(response.body, { status: response.status, headers });
}

function renderFailure(error: unknown, ctx: RequestContext | null): Response {
  if (error instanceof ValidationError) {
    const message = ctx ? error.message : 'Please check the values you entered.';
    return html(ctx ? errorPage(ctx.view, 400, message) : message, 400);
  }
  if (error instanceof PasswordPolicyError) {
    return html(ctx ? errorPage(ctx.view, 503, error.message) : error.message, 503);
  }

  console.error('Unhandled request failure', error instanceof Error ? error.message : error);

  const code = (error as { code?: string }).code ?? '';
  const isConnectivity = /^(08|57P|ECONN|ETIMEDOUT|ENOTFOUND)/.test(code);
  const message = isConnectivity
    ? 'The database is temporarily unavailable. Please try again shortly.'
    : 'The store could not complete that request.';
  const status = isConnectivity ? 503 : 500;
  return html(ctx ? errorPage(ctx.view, status, message) : message, status);
}

async function handleHealth(env: Env): Promise<Response> {
  try {
    await withDb(env, async (db: Db) => db.query('SELECT 1 AS ok'));
    return health(200, { status: 'ok', database: 'ok' });
  } catch {
    return health(503, { status: 'degraded', database: 'unavailable' });
  }
}

async function dispatch(args: RouteArgs): Promise<Response> {
  const handler = HANDLERS[args.name];
  if (!handler) return html(errorPage(args.ctx.view, 404, 'Page not found.'), 404);
  return handler(args);
}

export default {
  async fetch(request: Request, env: Env): Promise<Response> {
    const url = new URL(request.url);

    try {
      if (url.pathname === '/__bench' && env.ENABLE_BENCH) {
        return harden(await runBench(env), url);
      }

      const route = matchRoute(request.method, url.pathname);
      if (!route) {
        if (request.method === 'GET' || request.method === 'HEAD') {
          return harden(await env.ASSETS.fetch(request), url);
        }
        return harden(
          new Response('Method not allowed', { status: 405, headers: { allow: 'GET, HEAD, POST' } }),
          url,
        );
      }

      if (route.name === 'health') {
        return harden(await handleHealth(env), url);
      }

      const response = await withDb(env, async (db) => {
        const ctx = await buildContext(request, env, url, db);
        try {
          const form = request.method === 'POST' ? await request.formData() : undefined;
          const result = await dispatch({
            name: route.name,
            params: route.params,
            req: request,
            env,
            url,
            ctx,
            db,
            form,
          });
          return withCookies(result, ctx.cookies);
        } catch (error) {
          return withCookies(renderFailure(error, ctx), ctx.cookies);
        }
      });

      return harden(response, url);
    } catch (error) {
      return harden(renderFailure(error, null), url);
    }
  },
};
