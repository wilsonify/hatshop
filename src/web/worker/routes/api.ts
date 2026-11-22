/** POST routes: cart, authentication, account, reviews, checkout. */
import {
  ABSOLUTE_TTL_MS,
  SESSION_COOKIE,
  clearCookie,
  createSession,
  open,
  serializeCookie,
} from '../../auth/session.ts';
import { verifyPassword } from '../../auth/password.ts';
import { verifyCsrf } from '../../auth/csrf.ts';
import {
  addCustomer,
  createProductReview,
  getCustomer,
  getPasswordHash,
  updateAccount,
  updateAddress,
} from '../../db/queries.ts';
import {
  PasswordPolicyError,
  authenticate,
  hashForStorage,
  parsePolicy,
  upgradeStoredHash,
  validateRegistration,
} from '../../services/accounts.ts';
import { applyCartMutation, parseCartMutation } from '../../services/cart.ts';
import { parseCheckoutForm, placeOrder } from '../../services/checkout.ts';
import { paymentConfig } from '../../services/payments.ts';
import * as V from '../../services/validation.ts';
import { html, redirect } from '../http.ts';
import { loginPage, registerPage } from '../views/account.ts';
import { errorPage } from '../views/layout.ts';
import { departments } from '../../services/catalog.ts';
import { CHK_COOKIE } from './constants.ts';
import { noticeUrl, safeReturn, type Handler, type RouteArgs } from './_shared.ts';

const NOTICE_BY_ACTION: Record<string, string> = {
  add: 'cart-added',
  update: 'cart-updated',
  remove: 'cart-removed',
  save: 'cart-saved',
  move: 'cart-moved',
};

/** Central CSRF gate for every mutating route. */
function csrfOk(args: RouteArgs, form: FormData): boolean {
  const submitted =
    args.req.headers.get('x-csrf-token') ??
    (typeof form.get('_csrf') === 'string' ? (form.get('_csrf') as string) : '');
  return verifyCsrf({ cookieToken: args.ctx.view.csrf }, args.req, args.url, submitted);
}

function forbidden(args: RouteArgs): Response {
  return html(errorPage(args.ctx.view, 403, 'That request could not be verified. Reload the page and try again.'), 403);
}

function formExpired(args: RouteArgs, message: string, status = 400): Response {
  return html(errorPage(args.ctx.view, status, message), status);
}

function sessionCookie(args: RouteArgs, token: string): string {
  return serializeCookie(SESSION_COOKIE, token, args.ctx.secure, { maxAge: ABSOLUTE_TTL_MS });
}

export const cartAction: Handler = async (args) => {
  const form = args.form!;
  if (!csrfOk(args, form)) return forbidden(args);

  const mutation = parseCartMutation(form);
  await applyCartMutation(args.db, args.ctx.cartId, mutation);

  const target = safeReturn(form.get('returnTo'), '/cart');
  return redirect(noticeUrl(target, NOTICE_BY_ACTION[mutation.action] ?? 'cart-updated'), args.ctx.cookies);
};

export const login: Handler = async (args) => {
  const form = args.form!;
  if (!csrfOk(args, form)) return forbidden(args);

  const email = String(form.get('email') ?? '');
  const password = String(form.get('password') ?? '');
  const nav = await departments(args.db);

  const outcome = await authenticate(args.db, email, password).catch(() => null);
  if (!outcome) {
    return html(
      loginPage(args.ctx.view, { departments: nav.slice(0, 6), error: 'Email or password is incorrect.', email }),
      401,
    );
  }

  const policy = parsePolicy(args.env.PASSWORD_STORAGE_POLICY);
  const iterations = Number.parseInt(args.env.PASSWORD_ITERATIONS ?? '', 10) || undefined;
  if (outcome.upgradeable) {
    // Best effort: never fail a valid login because an upgrade write failed.
    await upgradeStoredHash(args.db, outcome.customerId, password, policy, iterations);
  }

  const stored = (await getPasswordHash(args.db, outcome.customerId))?.password ?? '';
  if (!args.env.SESSION_SECRET) return formExpired(args, 'Sign-in is unavailable: SESSION_SECRET is not configured.', 503);

  const token = await createSession(outcome.customerId, stored, args.env.SESSION_SECRET);
  return redirect(safeReturn(form.get('returnTo'), '/account'), [sessionCookie(args, token)]);
};

export const register: Handler = async (args) => {
  const form = args.form!;
  if (!csrfOk(args, form)) return forbidden(args);

  const policy = parsePolicy(args.env.PASSWORD_STORAGE_POLICY);
  const nav = await departments(args.db);
  if (policy === 'disabled') {
    return html(
      registerPage(args.ctx.view, {
        departments: nav.slice(0, 6),
        registrationDisabled:
          'Registration is unavailable on this deployment: customer.password is VARCHAR(50). Apply the reviewed migration and set PASSWORD_STORAGE_POLICY.',
      }),
      503,
    );
  }

  let input;
  try {
    input = validateRegistration({
      name: form.get('name'),
      email: form.get('email'),
      password: form.get('password'),
      confirm: form.get('confirm'),
    });
  } catch (error) {
    const { message } = V.firstError(error);
    return html(
      registerPage(args.ctx.view, { departments: nav.slice(0, 6), error: message, email: String(form.get('email') ?? '') }),
      400,
    );
  }

  let hash: string;
  try {
    hash = (await hashForStorage(input.password, policy, Number.parseInt(args.env.PASSWORD_ITERATIONS ?? '', 10) || undefined)).hash;
  } catch (error) {
    if (error instanceof PasswordPolicyError) return formExpired(args, error.message, 503);
    throw error;
  }

  try {
    const created = await addCustomer(args.db, input.name, input.email, hash);
    const customerId = Number(created?.customer_id ?? 0);
    if (!customerId) return formExpired(args, 'The account could not be created.', 500);
    if (!args.env.SESSION_SECRET) return redirect('/login');
    const token = await createSession(customerId, hash, args.env.SESSION_SECRET);
    return redirect('/account', [sessionCookie(args, token)]);
  } catch (error) {
    const code = (error as { code?: string }).code;
    const message =
      code === '23505'
        ? 'An account with that email already exists.'
        : 'The account could not be created. Please try again.';
    return html(
      registerPage(args.ctx.view, { departments: nav.slice(0, 6), error: message, email: input.email, name: input.name }),
      400,
    );
  }
};

export const logout: Handler = async (args) => {
  const form = args.form!;
  if (!csrfOk(args, form)) return forbidden(args);
  return redirect('/?notice=logged-out', [clearCookie(SESSION_COOKIE, args.ctx.secure)]);
};

export const accountUpdate: Handler = async (args) => {
  const form = args.form!;
  if (!csrfOk(args, form)) return forbidden(args);
  if (args.ctx.customerId === null) return redirect('/login');

  const existing = await getCustomer(args.db, args.ctx.customerId);
  if (!existing) return redirect('/login');

  const name = V.text(form.get('name'), 'name', 50, { required: true });
  const email = V.email(form.get('email'));
  await updateAccount(
    args.db,
    args.ctx.customerId,
    name,
    email,
    existing.password,
    V.text(form.get('dayPhone'), 'dayPhone', 100),
    V.text(form.get('evePhone'), 'evePhone', 100),
    V.text(form.get('mobPhone'), 'mobPhone', 100),
  );
  return redirect('/account?notice=account-saved', args.ctx.cookies);
};

export const addressUpdate: Handler = async (args) => {
  const form = args.form!;
  if (!csrfOk(args, form)) return forbidden(args);
  if (args.ctx.customerId === null) return redirect('/login');

  await updateAddress(
    args.db,
    args.ctx.customerId,
    V.text(form.get('address1'), 'address1', 100),
    V.text(form.get('address2'), 'address2', 100),
    V.text(form.get('city'), 'city', 100),
    V.text(form.get('region'), 'region', 100),
    V.text(form.get('postalCode'), 'postalCode', 100),
    V.text(form.get('country'), 'country', 100),
    V.integer(form.get('shippingRegionId'), 'shippingRegionId', { min: 1 }),
  );
  return redirect('/account?notice=account-saved', args.ctx.cookies);
};

export const passwordUpdate: Handler = async (args) => {
  const form = args.form!;
  if (!csrfOk(args, form)) return forbidden(args);
  if (args.ctx.customerId === null) return redirect('/login');

  const policy = parsePolicy(args.env.PASSWORD_STORAGE_POLICY);
  if (policy === 'disabled') {
    return formExpired(
      args,
      'Password changes are unavailable: the schema stores 50 characters. Apply the reviewed migration and set PASSWORD_STORAGE_POLICY.',
      503,
    );
  }

  const current = (await getPasswordHash(args.db, args.ctx.customerId))?.password ?? '';
  const currentPassword = String(form.get('currentPassword') ?? '');
  if (!(await verifyPassword(currentPassword, current))) {
    return formExpired(args, 'Your current password is incorrect.', 400);
  }

  const newPassword = V.password(form.get('newPassword'));
  if (newPassword !== String(form.get('confirmPassword') ?? '')) {
    throw new V.ValidationError('confirmPassword', 'New passwords do not match');
  }

  const customer = await getCustomer(args.db, args.ctx.customerId);
  if (!customer) return redirect('/login');
  const { hash } = await hashForStorage(newPassword, policy, Number.parseInt(args.env.PASSWORD_ITERATIONS ?? '', 10) || undefined);
  await updateAccount(
    args.db,
    args.ctx.customerId,
    customer.name,
    customer.email,
    hash,
    customer.day_phone ?? '',
    customer.eve_phone ?? '',
    customer.mob_phone ?? '',
  );

  // The session is bound to the password fingerprint, so a new one is issued.
  if (!args.env.SESSION_SECRET) return redirect('/account?notice=password-saved');
  const token = await createSession(args.ctx.customerId, hash, args.env.SESSION_SECRET);
  return redirect('/account?notice=password-saved', [sessionCookie(args, token)]);
};

export const postReview: Handler = async (args) => {
  const form = args.form!;
  if (!csrfOk(args, form)) return forbidden(args);
  if (args.ctx.customerId === null) return redirect('/login');

  const productId = Number(args.params.productId);
  const rating = V.integer(form.get('rating'), 'rating', { min: 1, max: 5 });
  const review = V.text(form.get('review'), 'review', 1000, { required: true });
  await createProductReview(args.db, args.ctx.customerId, productId, review, rating);
  return redirect(noticeUrl(`/products/${productId}`, 'account-saved'), args.ctx.cookies);
};

export const checkout: Handler = async (args) => {
  const form = args.form!;
  if (!csrfOk(args, form)) return forbidden(args);
  if (args.ctx.customerId === null) return redirect('/login');

  const cfg = paymentConfig(args.env);
  if (!cfg.configured) {
    return formExpired(
      args,
      'Checkout is disabled because no payment gateway credentials are configured on this deployment.',
      503,
    );
  }

  // Idempotency: the nonce cookie must match the submitted nonce, and is cleared.
  const cookies = [...args.ctx.cookies, clearCookie(CHK_COOKIE, args.ctx.secure)];
  if (args.env.SESSION_SECRET) {
    const raw = args.req.headers.get('cookie') ?? '';
    const match = raw.split(';').map((c) => c.trim()).find((c) => c.startsWith(`${CHK_COOKIE}=`));
    const token = match ? decodeURIComponent(match.slice(CHK_COOKIE.length + 1)) : '';
    const stored = await open<{ n: string }>(token, args.env.SESSION_SECRET);
    if (!stored || stored.n !== String(form.get('nonce') ?? '')) {
      return formExpired(args, 'That checkout form has expired. Review your cart and try again.', 400);
    }
  }

  const checkoutForm = parseCheckoutForm(form);
  const result = await placeOrder(args.db, args.ctx.customerId, args.ctx.cartId, checkoutForm, cfg);

  switch (result.kind) {
    case 'placed':
      return redirect(noticeUrl(`/orders/${result.orderId}`, 'order-placed'), cookies);
    case 'declined':
      return html(
        errorPage(
          args.ctx.view,
          402,
          `Your payment was declined (${result.reason}). Order #${result.orderId} was saved but is not paid; you can contact support quoting that number.`,
        ),
        402,
      );
    case 'capture_failed':
      return html(
        errorPage(
          args.ctx.view,
          502,
          `Order #${result.orderId} was authorized but the capture did not complete (${result.reason}). No further charge will be made without review.`,
        ),
        502,
      );
    case 'invalid_shipping':
      return formExpired(args, 'Choose a valid shipping method and tax rate.', 400);
    case 'empty_cart':
    default:
      return redirect('/cart', cookies);
  }
};
