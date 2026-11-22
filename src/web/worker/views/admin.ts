/** Administration pages. Every page here is reachable only after a role check. */
import type { OrderDetailRow } from '../../db/types.ts';
import type { AdminDashboard } from '../../services/admin.ts';
import { statusLabel } from '../../services/orders.ts';
import { dateLabel, e, money } from '../html.ts';
import { layout, type NavDepartment, type ViewContext } from './layout.ts';

const ADMIN_NAV = `<nav class="admin-nav" aria-label="Administration">
  <a href="/admin">Dashboard</a>
  <a href="/admin/products">Products</a>
  <a href="/admin/catalog">Catalog</a>
  <a href="/admin/orders">Orders</a>
  <a href="/admin/customers">Customers</a>
  <form action="/admin/logout" method="post" class="inline">
    <input type="hidden" name="_csrf" value="CSRF_PLACEHOLDER">
    <button type="submit" class="link-button">Sign out</button>
  </form>
</nav>`;

function adminLayout(ctx: ViewContext, title: string, body: string): string {
  const nav = ADMIN_NAV.replace('CSRF_PLACEHOLDER', e(ctx.csrf));
  return layout(ctx, `${title} · Admin`, `${nav}${body}`, { wide: true });
}

export function adminLoginPage(ctx: ViewContext, data: { error?: string; configured: boolean }): string {
  const body = `
<h1>Administration</h1>
${
  data.configured
    ? `<form action="/admin/login" method="post" class="card stacked narrow">
        <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
        ${data.error ? `<p class="flash flash-error" role="alert">${e(data.error)}</p>` : ''}
        <label>Username <input type="text" name="username" autocomplete="username" required></label>
        <label>Password <input type="password" name="password" autocomplete="current-password" required></label>
        <button type="submit" class="primary">Sign in</button>
      </form>`
    : `<p class="flash flash-error" role="alert">Administration is not configured. Set the ADMIN_USERNAME and ADMIN_PASSWORD_HASH secrets.</p>`
}`;
  return layout(ctx, 'Admin sign in', body);
}

export function adminHomePage(ctx: ViewContext, data: AdminDashboard): string {
  const body = `
<h1>Dashboard</h1>
<p class="muted">${data.products.length} products · ${data.categories.length} categories · ${data.departments.length} departments · ${data.customers.length} customers.</p>
<div class="split">
  <section class="card">
    <h2>Departments</h2>
    <ul>${data.departments.map((d) => `<li>${e(d.name)}</li>`).join('')}</ul>
    <form action="/admin/action" method="post" class="stacked">
      <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
      <input type="hidden" name="action" value="add-department">
      <label>New department <input type="text" name="name" maxlength="50" required></label>
      <label>Description <input type="text" name="description" maxlength="1000"></label>
      <button type="submit">Add department</button>
    </form>
  </section>
  <section class="card">
    <h2>Categories</h2>
    <ul>${data.categories.map((c) => `<li>${e(c.name)}</li>`).join('')}</ul>
    <form action="/admin/action" method="post" class="stacked">
      <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
      <input type="hidden" name="action" value="add-category">
      <label>Department
        <select name="departmentId" required>
          ${data.departments.map((d) => `<option value="${e(d.department_id)}">${e(d.name)}</option>`).join('')}
        </select>
      </label>
      <label>New category <input type="text" name="name" maxlength="50" required></label>
      <label>Description <input type="text" name="description" maxlength="1000"></label>
      <button type="submit">Add category</button>
    </form>
  </section>
</div>`;
  return adminLayout(ctx, 'Dashboard', body);
}

export function adminProductsPage(
  ctx: ViewContext,
  data: { products: Array<{ product_id: number; name: string; price: string; discounted_price: string; display: number }> },
): string {
  const body = `
<h1>Products</h1>
<table class="table">
  <caption class="visually-hidden">All products</caption>
  <thead><tr><th scope="col">ID</th><th scope="col">Name</th><th scope="col">Price</th><th scope="col">Discounted</th><th scope="col">Visible</th><th scope="col">Edit</th></tr></thead>
  <tbody>${data.products
    .map(
      (p) => `<tr>
        <td>${e(p.product_id)}</td>
        <th scope="row">${e(p.name)}</th>
        <td>${money(p.price)}</td>
        <td>${money(p.discounted_price)}</td>
        <td>${Number(p.display) === 1 ? 'Yes' : 'No'}</td>
        <td><a href="/admin/products/${e(p.product_id)}">Edit</a></td>
      </tr>`,
    )
    .join('')}</tbody>
</table>`;
  return adminLayout(ctx, 'Products', body);
}

export function adminProductPage(
  ctx: ViewContext,
  data: {
    productId: number;
    info: { name: string; image: string | null; thumbnail: string | null; display: number } | null;
    description: string;
    price: string;
    discountedPrice: string;
    categories: Array<{ category_id: number; name: string }>;
    allCategories: Array<{ category_id: number; name: string }>;
  },
): string {
  if (!data.info) return adminLayout(ctx, 'Product', '<h1>Product not found</h1>');
  const body = `
<h1>Edit product #${e(data.productId)}</h1>
<div class="split">
  <form action="/admin/action" method="post" class="card stacked">
    <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
    <input type="hidden" name="action" value="update-product">
    <input type="hidden" name="productId" value="${e(data.productId)}">
    <label>Name <input type="text" name="name" maxlength="50" value="${e(data.info.name)}" required></label>
    <label>Description <textarea name="description" rows="6" maxlength="1000" required>${e(data.description)}</textarea></label>
    <label>Price <input type="text" name="price" inputmode="decimal" value="${e(data.price)}" required></label>
    <label>Discounted price <input type="text" name="discountedPrice" inputmode="decimal" value="${e(data.discountedPrice)}"></label>
    <button type="submit" class="primary">Save product</button>
  </form>
  <div class="card stacked">
    <h2>Visibility</h2>
    <form action="/admin/action" method="post" class="stacked">
      <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
      <input type="hidden" name="action" value="set-display">
      <input type="hidden" name="productId" value="${e(data.productId)}">
      <label>Shown in catalog
        <select name="display">
          <option value="1" ${Number(data.info.display) === 1 ? 'selected' : ''}>Yes</option>
          <option value="0" ${Number(data.info.display) === 1 ? '' : 'selected'}>No</option>
        </select>
      </label>
      <button type="submit">Update visibility</button>
    </form>

    <h2>Image</h2>
    <p class="muted small">Uploads are disabled until object storage is approved; reference an existing file in <code>product_images/</code>.</p>
    <form action="/admin/action" method="post" class="stacked">
      <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
      <input type="hidden" name="action" value="set-image">
      <input type="hidden" name="productId" value="${e(data.productId)}">
      <label>Image file <input type="text" name="image" maxlength="150" value="${e(data.info.image ?? '')}"></label>
      <button type="submit">Update image</button>
    </form>

    <h2>Categories</h2>
    <ul>${data.categories.map((c) => `<li>${e(c.name)}</li>`).join('') || '<li class="muted">Unassigned</li>'}</ul>
    <form action="/admin/action" method="post" class="stacked">
      <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
      <input type="hidden" name="action" value="assign-category">
      <input type="hidden" name="productId" value="${e(data.productId)}">
      <label>Assign to
        <select name="categoryId" required>
          ${data.allCategories.map((c) => `<option value="${e(c.category_id)}">${e(c.name)}</option>`).join('')}
        </select>
      </label>
      <button type="submit">Assign</button>
    </form>
    <form action="/admin/action" method="post" class="stacked">
      <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
      <input type="hidden" name="action" value="delete-product">
      <input type="hidden" name="productId" value="${e(data.productId)}">
      <button type="submit" class="danger" onclick="return confirm('Delete this product?')">Delete product</button>
    </form>
  </div>
</div>`;
  return adminLayout(ctx, 'Edit product', body);
}

export function adminOrdersPage(
  ctx: ViewContext,
  data: { orders: Array<{ order_id: number; total_amount: string; created_on: string; status: number; customer_name: string | null }> },
): string {
  const body = `
<h1>Orders</h1>
<table class="table">
  <caption class="visually-hidden">Most recent orders</caption>
  <thead><tr><th scope="col">Order</th><th scope="col">Customer</th><th scope="col">Placed</th><th scope="col">Status</th><th scope="col">Total</th></tr></thead>
  <tbody>${data.orders
    .map(
      (o) => `<tr>
        <th scope="row"><a href="/admin/orders/${e(o.order_id)}">#${e(o.order_id)}</a></th>
        <td>${e(o.customer_name ?? '—')}</td>
        <td>${e(dateLabel(o.created_on))}</td>
        <td>${e(statusLabel(Number(o.status)))}</td>
        <td>${money(o.total_amount)}</td>
      </tr>`,
    )
    .join('') || '<tr><td colspan="5" class="muted">No orders yet.</td></tr>'}</tbody>
</table>`;
  return adminLayout(ctx, 'Orders', body);
}

export function adminOrderPage(
  ctx: ViewContext,
  data: {
    orderId: number;
    order: {
      total_amount: string;
      created_on: string;
      status: number;
      shipped_on: string | null;
      comments: string | null;
      customer_name: string | null;
      shipping_address: string | null;
      customer_email: string | null;
      auth_code: string | null;
      reference: string | null;
    } | null;
    details: OrderDetailRow[];
    audit: Array<{ created_on: string; message: string; message_number: number }>;
  },
): string {
  if (!data.order) return adminLayout(ctx, 'Order', '<h1>Order not found</h1>');
  const order = data.order;
  const body = `
<h1>Order #${e(data.orderId)}</h1>
<p>${e(statusLabel(Number(order.status)))} · placed ${e(dateLabel(order.created_on))} · total ${money(order.total_amount)}</p>
<p class="muted">Payment reference: ${e(order.reference ?? 'none')} · auth code: ${e(order.auth_code ?? 'none')}</p>
<div class="split">
  <section class="card">
    <h2>Lines</h2>
    <table class="table">
      <thead><tr><th scope="col">Product</th><th scope="col">Qty</th><th scope="col">Unit</th><th scope="col">Subtotal</th></tr></thead>
      <tbody>${data.details
        .map(
          (line) =>
            `<tr><th scope="row">${e(line.product_name)}</th><td>${e(line.quantity)}</td><td>${money(line.unit_cost)}</td><td>${money(line.subtotal)}</td></tr>`,
        )
        .join('')}</tbody>
    </table>
    <h2>Audit trail</h2>
    <ul class="audit">${data.audit
      .map((a) => `<li><time>${e(dateLabel(a.created_on))}</time> ${e(a.message)} <span class="muted">(${e(a.message_number)})</span></li>`)
      .join('') || '<li class="muted">No audit entries.</li>'}</ul>
  </section>
  <div class="card stacked">
    <h2>Status</h2>
    <form action="/admin/action" method="post" class="stacked">
      <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
      <input type="hidden" name="action" value="update-order-status">
      <input type="hidden" name="orderId" value="${e(data.orderId)}">
      <label>New status
        <select name="status">
          ${[0, 1, 2, 3, 4, 5, 6, 7, 8]
            .map(
              (s) =>
                `<option value="${s}" ${Number(order.status) === s ? 'selected' : ''}>${s} — ${e(statusLabel(s))}</option>`,
            )
            .join('')}
        </select>
      </label>
      <button type="submit">Update status</button>
    </form>
    <form action="/admin/action" method="post" class="stacked">
      <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
      <input type="hidden" name="action" value="mark-shipped">
      <input type="hidden" name="orderId" value="${e(data.orderId)}">
      <button type="submit">Mark as shipped</button>
    </form>
  </div>
</div>`;
  return adminLayout(ctx, 'Order', body);
}

export function adminCatalogPage(
  ctx: ViewContext,
  data: {
    departments: Array<{ department_id: number; name: string; description: string | null }>;
    categories: Array<{ category_id: number; name: string; description: string | null }>;
  },
): string {
  const body = `
<h1>Catalog</h1>
<table class="table">
  <thead><tr><th scope="col">Department</th><th scope="col">Description</th><th scope="col">Actions</th></tr></thead>
  <tbody>${data.departments
    .map(
      (d) => `<tr>
        <th scope="row">${e(d.name)}</th>
        <td>${e(d.description ?? '')}</td>
        <td>
          <form action="/admin/action" method="post" class="inline">
            <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
            <input type="hidden" name="action" value="delete-department">
            <input type="hidden" name="departmentId" value="${e(d.department_id)}">
            <button type="submit" class="link-button danger" onclick="return confirm('Delete department?')">Delete</button>
          </form>
        </td>
      </tr>`,
    )
    .join('')}</tbody>
</table>
<h2>Categories</h2>
<table class="table">
  <thead><tr><th scope="col">Category</th><th scope="col">Description</th><th scope="col">Actions</th></tr></thead>
  <tbody>${data.categories
    .map(
      (c) => `<tr>
        <th scope="row">${e(c.name)}</th>
        <td>${e(c.description ?? '')}</td>
        <td>
          <form action="/admin/action" method="post" class="inline">
            <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
            <input type="hidden" name="action" value="delete-category">
            <input type="hidden" name="categoryId" value="${e(c.category_id)}">
            <button type="submit" class="link-button danger" onclick="return confirm('Delete category?')">Delete</button>
          </form>
        </td>
      </tr>`,
    )
    .join('')}</tbody>
</table>`;
  return adminLayout(ctx, 'Catalog', body);
}

export function adminCustomersPage(
  ctx: ViewContext,
  data: { customers: Array<{ customer_id: number; name: string }> },
): string {
  const body = `
<h1>Customers</h1>
<table class="table">
  <thead><tr><th scope="col">ID</th><th scope="col">Name</th></tr></thead>
  <tbody>${data.customers
    .map(
      (c) => `<tr><td>${e(c.customer_id)}</td><th scope="row"><a href="/admin/customers/${e(c.customer_id)}">${e(c.name)}</a></th></tr>`,
    )
    .join('')}</tbody>
</table>`;
  return adminLayout(ctx, 'Customers', body);
}

export function adminCustomerPage(
  ctx: ViewContext,
  data: {
    customerId: number;
    customer: {
      name: string;
      email: string;
      city: string | null;
      region: string | null;
      country: string | null;
      day_phone: string | null;
    } | null;
  },
): string {
  if (!data.customer) return adminLayout(ctx, 'Customer', '<h1>Customer not found</h1>');
  const c = data.customer;
  const body = `
<h1>${e(c.name)}</h1>
<dl class="details">
  <dt>Email</dt><dd>${e(c.email)}</dd>
  <dt>Location</dt><dd>${e([c.city, c.region, c.country].filter(Boolean).join(', ') || '—')}</dd>
  <dt>Phone</dt><dd>${e(c.day_phone ?? '—')}</dd>
</dl>
<p class="muted small">Stored card data is never displayed.</p>`;
  return adminLayout(ctx, 'Customer', body);
}
