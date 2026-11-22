/** Account, order history and checkout pages. */
import type { OrderDetailRow, ShippingRegionRow } from '../../db/types.ts';
import type { CheckoutOptions } from '../../services/checkout.ts';
import type { CartView } from '../../services/cart.ts';
import { statusLabel } from '../../services/orders.ts';
import { dateLabel, e, money } from '../html.ts';
import { breadcrumbs, layout, type NavDepartment, type ViewContext } from './layout.ts';

export function loginPage(
  ctx: ViewContext,
  data: { departments: NavDepartment[]; error?: string; email?: string },
): string {
  const body = `
<h1>Sign in</h1>
${data.error ? `<p class="flash flash-error" role="alert">${e(data.error)}</p>` : ''}
<form action="/login" method="post" class="card stacked narrow">
  <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
  <label>Email <input type="email" name="email" value="${e(data.email ?? '')}" autocomplete="username" required></label>
  <label>Password <input type="password" name="password" autocomplete="current-password" required></label>
  <button type="submit" class="primary">Sign in</button>
  <p class="muted">No account? <a href="/register">Register</a>.</p>
</form>`;
  return layout(ctx, 'Sign in', body, { departments: data.departments });
}

export function registerPage(
  ctx: ViewContext,
  data: { departments: NavDepartment[]; error?: string; registrationDisabled?: string; name?: string; email?: string },
): string {
  const body = `
<h1>Create an account</h1>
${
  data.registrationDisabled
    ? `<p class="flash flash-error" role="alert">${e(data.registrationDisabled)}</p>`
    : `${data.error ? `<p class="flash flash-error" role="alert">${e(data.error)}</p>` : ''}
<form action="/register" method="post" class="card stacked narrow">
  <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
  <label>Name <input type="text" name="name" maxlength="50" value="${e(data.name ?? '')}" autocomplete="name" required></label>
  <label>Email <input type="email" name="email" maxlength="100" value="${e(data.email ?? '')}" autocomplete="email" required></label>
  <label>Password <input type="password" name="password" minlength="10" autocomplete="new-password" required>
    <small class="muted">At least 10 characters, including a letter and a number.</small></label>
  <label>Confirm password <input type="password" name="confirm" minlength="10" autocomplete="new-password" required></label>
  <button type="submit" class="primary">Create account</button>
</form>`
}`;
  return layout(ctx, 'Register', body, { departments: data.departments });
}

export function accountPage(
  ctx: ViewContext,
  data: {
    departments: NavDepartment[];
    customer: {
      name: string;
      email: string;
      address_1: string | null;
      address_2: string | null;
      city: string | null;
      region: string | null;
      postal_code: string | null;
      country: string | null;
      shipping_region_id: number;
      day_phone: string | null;
      eve_phone: string | null;
      mob_phone: string | null;
    };
    regions: ShippingRegionRow[];
    passwordPolicy: 'pbkdf2' | 'sha1-legacy' | 'disabled';
  },
): string {
  const body = `
${breadcrumbs([{ label: 'Home', href: '/' }, { label: 'Your account' }])}
<h1>Your account</h1>
<div class="split">
  <form action="/account" method="post" class="card stacked">
    <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
    <h2>Profile</h2>
    <label>Name <input type="text" name="name" maxlength="50" value="${e(data.customer.name)}" required></label>
    <label>Email <input type="email" name="email" maxlength="100" value="${e(data.customer.email)}" required></label>
    <label>Day phone <input type="tel" name="dayPhone" maxlength="100" value="${e(data.customer.day_phone ?? '')}"></label>
    <label>Evening phone <input type="tel" name="evePhone" maxlength="100" value="${e(data.customer.eve_phone ?? '')}"></label>
    <label>Mobile <input type="tel" name="mobPhone" maxlength="100" value="${e(data.customer.mob_phone ?? '')}"></label>
    <button type="submit" class="primary">Save profile</button>
  </form>

  <form action="/account/address" method="post" class="card stacked">
    <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
    <h2>Address</h2>
    <label>Address line 1 <input type="text" name="address1" maxlength="100" value="${e(data.customer.address_1 ?? '')}"></label>
    <label>Address line 2 <input type="text" name="address2" maxlength="100" value="${e(data.customer.address_2 ?? '')}"></label>
    <label>City <input type="text" name="city" maxlength="100" value="${e(data.customer.city ?? '')}"></label>
    <label>Region <input type="text" name="region" maxlength="100" value="${e(data.customer.region ?? '')}"></label>
    <label>Postal code <input type="text" name="postalCode" maxlength="100" value="${e(data.customer.postal_code ?? '')}"></label>
    <label>Country <input type="text" name="country" maxlength="100" value="${e(data.customer.country ?? '')}"></label>
    <label>Shipping region
      <select name="shippingRegionId" required>
        ${data.regions
          .map(
            (r) =>
              `<option value="${e(r.shipping_region_id)}" ${
                Number(r.shipping_region_id) === Number(data.customer.shipping_region_id) ? 'selected' : ''
              }>${e(r.shipping_region)}</option>`,
          )
          .join('')}
      </select>
    </label>
    <button type="submit" class="primary">Save address</button>
  </form>
</div>

<form action="/account/password" method="post" class="card stacked narrow">
  <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
  <h2>Change password</h2>
  ${
    data.passwordPolicy === 'disabled'
      ? `<p class="flash flash-error">Password changes are unavailable: the existing schema stores passwords in 50 characters. Apply the reviewed migration (migrations/002_widen_customer_password.sql) and set PASSWORD_STORAGE_POLICY=pbkdf2.</p>`
      : ''
  }
  <label>Current password <input type="password" name="currentPassword" autocomplete="current-password" required></label>
  <label>New password <input type="password" name="newPassword" minlength="10" autocomplete="new-password" required></label>
  <label>Confirm new password <input type="password" name="confirmPassword" minlength="10" autocomplete="new-password" required></label>
  <button type="submit" ${data.passwordPolicy === 'disabled' ? 'disabled' : ''}>Update password</button>
</form>`;
  return layout(ctx, 'Your account', body, { departments: data.departments });
}

export function ordersPage(
  ctx: ViewContext,
  data: {
    departments: NavDepartment[];
    orders: Array<{ order_id: number; total_amount: string; created_on: string; status: number; shipped_on: string | null }>;
  },
): string {
  const body = `
${breadcrumbs([{ label: 'Home', href: '/' }, { label: 'Orders' }])}
<h1>Your orders</h1>
${
  data.orders.length === 0
    ? '<p class="empty">You have not placed an order yet.</p>'
    : `<table class="table">
        <caption class="visually-hidden">Order history</caption>
        <thead><tr><th scope="col">Order</th><th scope="col">Placed</th><th scope="col">Status</th><th scope="col">Total</th></tr></thead>
        <tbody>${data.orders
          .map(
            (o) => `<tr>
              <th scope="row"><a href="/orders/${e(o.order_id)}">#${e(o.order_id)}</a></th>
              <td>${e(dateLabel(o.created_on))}</td>
              <td>${e(statusLabel(Number(o.status)))}</td>
              <td>${money(o.total_amount)}</td>
            </tr>`,
          )
          .join('')}</tbody>
      </table>`
}`;
  return layout(ctx, 'Your orders', body, { departments: data.departments });
}

export function orderPage(
  ctx: ViewContext,
  data: {
    departments: NavDepartment[];
    orderId: number;
    order: { total_amount: string; created_on: string; status: number; shipped_on: string | null } | null;
    details: OrderDetailRow[];
  },
): string {
  if (!data.order) {
    return layout(ctx, 'Order not found', '<h1>Order not found</h1><p><a href="/orders">Back to your orders</a></p>', {
      departments: data.departments,
    });
  }
  const order = data.order;
  const body = `
${breadcrumbs([{ label: 'Home', href: '/' }, { label: 'Orders', href: '/orders' }, { label: `#${data.orderId}` }])}
<h1>Order #${e(data.orderId)}</h1>
<p>Placed ${e(dateLabel(order.created_on))} · ${e(statusLabel(Number(order.status)))}</p>
<table class="table">
  <caption class="visually-hidden">Order lines</caption>
  <thead><tr><th scope="col">Product</th><th scope="col">Unit price</th><th scope="col">Qty</th><th scope="col">Subtotal</th></tr></thead>
  <tbody>${data.details
    .map(
      (line) => `<tr>
        <th scope="row">${e(line.product_name)}</th>
        <td>${money(line.unit_cost)}</td>
        <td>${e(line.quantity)}</td>
        <td>${money(line.subtotal)}</td>
      </tr>`,
    )
    .join('')}</tbody>
  <tfoot><tr><th scope="row" colspan="3">Total</th><td>${money(order.total_amount)}</td></tr></tfoot>
</table>`;
  return layout(ctx, `Order #${data.orderId}`, body, { departments: data.departments });
}

export function checkoutPage(
  ctx: ViewContext,
  data: {
    departments: NavDepartment[];
    cart: CartView;
    options: CheckoutOptions;
    error?: string;
    paymentsConfigured: boolean;
    nonce: string;
    cardholderHint?: string;
  },
): string {
  if (data.cart.isEmpty) {
    return layout(ctx, 'Checkout', `<h1>Checkout</h1><p class="empty">Your cart is empty. <a href="/">Continue shopping</a>.</p>`, {
      departments: data.departments,
    });
  }
  const body = `
${breadcrumbs([{ label: 'Home', href: '/' }, { label: 'Cart', href: '/cart' }, { label: 'Checkout' }])}
<h1>Checkout</h1>
${data.error ? `<p class="flash flash-error" role="alert">${e(data.error)}</p>` : ''}
${
  data.paymentsConfigured
    ? ''
    : `<p class="flash flash-error" role="alert">Payments are not configured on this deployment, so checkout is disabled. Add the AUTHORIZE_NET_LOGIN_ID and AUTHORIZE_NET_TRANSACTION_KEY secrets (sandbox) to enable it.</p>`
}
<div class="split">
  <section class="card">
    <h2>Order summary</h2>
    <table class="table">
      <thead><tr><th scope="col">Product</th><th scope="col">Qty</th><th scope="col">Subtotal</th></tr></thead>
      <tbody>${data.cart.items
        .map(
          (item) => `<tr><th scope="row">${e(item.name)}</th><td>${e(item.quantity)}</td><td>${money(item.subtotal)}</td></tr>`,
        )
        .join('')}</tbody>
      <tfoot><tr><th scope="row" colspan="2">Total</th><td><strong>${money(data.cart.total)}</strong></td></tr></tfoot>
    </table>
    <p class="muted">The total is calculated by the database at the moment the order is created.</p>
  </section>
  <form action="/checkout" method="post" class="card stacked">
    <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
    <input type="hidden" name="nonce" value="${e(data.nonce)}">
    <h2>Shipping and payment</h2>
    <label>Shipping method
      <select name="shippingId" required>
        ${data.options.shipping
          .map((s) => `<option value="${e(s.shipping_id)}">${e(s.shipping_type)} — ${money(s.shipping_cost)}</option>`)
          .join('')}
      </select>
    </label>
    <label>Tax rate
      <select name="taxId" required>
        ${data.options.taxes
          .map((t) => `<option value="${e(t.tax_id)}">${e(t.tax_type)} (${e(t.tax_percentage)}%)</option>`)
          .join('')}
      </select>
    </label>
    <label>Card number <input type="text" name="cardNumber" inputmode="numeric" autocomplete="cc-number" maxlength="19" required></label>
    <label>Expiry (MMYY) <input type="text" name="expiry" inputmode="numeric" autocomplete="cc-exp" maxlength="5" placeholder="1229" required></label>
    <label>Security code <input type="text" name="cvv" inputmode="numeric" autocomplete="cc-csc" maxlength="4" required></label>
    <button type="submit" class="primary" ${data.paymentsConfigured ? '' : 'disabled'}>Place order</button>
    <p class="muted small">Payments are processed by Authorize.Net in sandbox mode. Card details are sent directly to the gateway and are never stored.</p>
  </form>
</div>`;
  return layout(ctx, 'Checkout', body, { departments: data.departments });
}
