/** /admin routes. Every handler after the login page requires an admin session. */
import { ADMIN_COOKIE, ABSOLUTE_TTL_MS, clearCookie, createAdminSession, serializeCookie } from '../../auth/session.ts';
import { verifyCsrf } from '../../auth/csrf.ts';
import { getOrderPaymentRefs, getProductDetails } from '../../db/queries.ts';
import {
  adminConfigured,
  adminDashboard,
  customerDetail,
  productDetail,
  runAdminAction,
  verifyAdmin,
} from '../../services/admin.ts';
import { orderForAdmin, recentOrders } from '../../services/orders.ts';
import * as V from '../../services/validation.ts';
import { html, redirect } from '../http.ts';
import {
  adminCatalogPage,
  adminCustomerPage,
  adminCustomersPage,
  adminHomePage,
  adminLoginPage,
  adminOrderPage,
  adminOrdersPage,
  adminProductPage,
  adminProductsPage,
} from '../views/admin.ts';
import { errorPage } from '../views/layout.ts';
import { noticeUrl, safeReturn, type Handler } from './_shared.ts';

function requireAdmin(args: Parameters<Handler>[0]): Response | null {
  if (args.ctx.view.admin) return null;
  return redirect('/admin/login');
}

function csrfOk(args: Parameters<Handler>[0], form: FormData): boolean {
  const submitted =
    args.req.headers.get('x-csrf-token') ??
    (typeof form.get('_csrf') === 'string' ? (form.get('_csrf') as string) : '');
  return verifyCsrf({ cookieToken: args.ctx.view.csrf }, args.req, args.url, submitted);
}

export const adminLoginForm: Handler = async (args) => {
  if (args.ctx.view.admin) return redirect('/admin');
  return html(adminLoginPage(args.ctx.view, { configured: adminConfigured(args.env) }));
};

export const adminLogin: Handler = async (args) => {
  const form = args.form!;
  if (!csrfOk(args, form)) return html(errorPage(args.ctx.view, 403, 'Reload the page and try again.'), 403);

  const ok = await verifyAdmin(args.env, form.get('username'), form.get('password'));
  if (!ok) {
    return html(
      adminLoginPage(args.ctx.view, {
        error: 'Those credentials were not accepted.',
        configured: adminConfigured(args.env),
      }),
      401,
    );
  }
  if (!args.env.SESSION_SECRET) {
    return html(errorPage(args.ctx.view, 503, 'SESSION_SECRET is not configured.'), 503);
  }

  const token = await createAdminSession(String(form.get('username') ?? ''), args.env.SESSION_SECRET);
  return redirect(
    '/admin',
    [serializeCookie(ADMIN_COOKIE, token, args.ctx.secure, { maxAge: ABSOLUTE_TTL_MS })],
  );
};

export const adminLogout: Handler = async (args) => {
  const form = args.form!;
  if (!csrfOk(args, form)) return html(errorPage(args.ctx.view, 403, 'Reload the page and try again.'), 403);
  return redirect('/admin/login', [clearCookie(ADMIN_COOKIE, args.ctx.secure)]);
};

export const adminHome: Handler = async (args) => {
  const denied = requireAdmin(args);
  if (denied) return denied;
  return html(adminHomePage(args.ctx.view, await adminDashboard(args.db)));
};

export const adminProducts: Handler = async (args) => {
  const denied = requireAdmin(args);
  if (denied) return denied;
  const data = await adminDashboard(args.db);
  return html(adminProductsPage(args.ctx.view, { products: data.products }));
};

export const adminProduct: Handler = async (args) => {
  const denied = requireAdmin(args);
  if (denied) return denied;

  const productId = Number(args.params.productId);
  const [{ info, categories, allCategories }, details] = await Promise.all([
    productDetail(args.db, productId),
    getProductDetails(args.db, productId),
  ]);

  return html(
    adminProductPage(args.ctx.view, {
      productId,
      info,
      description: details?.description ?? '',
      price: details?.price ?? '0.00',
      discountedPrice: details?.discounted_price ?? '0.00',
      categories,
      allCategories,
    }),
  );
};

export const adminOrders: Handler = async (args) => {
  const denied = requireAdmin(args);
  if (denied) return denied;
  return html(adminOrdersPage(args.ctx.view, { orders: await recentOrders(args.db, 100) }));
};

export const adminOrder: Handler = async (args) => {
  const denied = requireAdmin(args);
  if (denied) return denied;
  const orderId = Number(args.params.orderId);
  const [data, refs] = await Promise.all([orderForAdmin(args.db, orderId), getOrderPaymentRefs(args.db, orderId)]);
  if (!data) return html(errorPage(args.ctx.view, 404, 'That order does not exist.'), 404);
  return html(
    adminOrderPage(args.ctx.view, {
      orderId,
      order: { ...data.order, auth_code: refs?.auth_code ?? null, reference: refs?.reference ?? null },
      details: data.details,
      audit: data.audit,
    }),
  );
};

export const adminCatalog: Handler = async (args) => {
  const denied = requireAdmin(args);
  if (denied) return denied;
  const { departments, categories } = await adminDashboard(args.db);
  return html(adminCatalogPage(args.ctx.view, { departments, categories }));
};

export const adminCustomers: Handler = async (args) => {
  const denied = requireAdmin(args);
  if (denied) return denied;
  const { customers } = await adminDashboard(args.db);
  return html(adminCustomersPage(args.ctx.view, { customers }));
};

export const adminCustomer: Handler = async (args) => {
  const denied = requireAdmin(args);
  if (denied) return denied;
  const customerId = Number(args.params.customerId);
  return html(adminCustomerPage(args.ctx.view, { customerId, customer: await customerDetail(args.db, customerId) }));
};

export const adminAction: Handler = async (args) => {
  const denied = requireAdmin(args);
  if (denied) return denied;
  const form = args.form!;
  if (!csrfOk(args, form)) return html(errorPage(args.ctx.view, 403, 'Reload the page and try again.'), 403);

  const target = safeReturn(form.get('returnTo'), '/admin');

  try {
    const result = await runAdminAction(args.db, form);
    return redirect(noticeUrl(target, result.ok ? 'admin-saved' : 'admin-failed'), args.ctx.cookies);
  } catch (error) {
    if (error instanceof V.ValidationError) {
      return redirect(noticeUrl(target, 'admin-failed'), args.ctx.cookies);
    }
    throw error;
  }
};
