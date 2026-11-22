/** Storefront pages: home, department, category, product, search, cart. */
import type { CartProductRow, ProductListRow, ReviewRow, SavedProductRow } from '../../db/types.ts';
import type { PageMeta } from '../../services/catalog.ts';
import type { CartView } from '../../services/cart.ts';
import { dateLabel, e, money, paginationLinks, productImage, truncate } from '../html.ts';
import { breadcrumbs, layout, type NavDepartment, type ViewContext } from './layout.ts';

function priceBlock(row: { price: string; discounted_price?: string | null }): string {
  const discounted = Number(row.discounted_price ?? 0);
  if (discounted > 0) {
    return `<p class="price"><span class="now">${money(discounted)}</span> <s>${money(row.price)}</s></p>`;
  }
  return `<p class="price"><span class="now">${money(row.price)}</span></p>`;
}

function productCard(ctx: ViewContext, row: ProductListRow): string {
  return `<article class="product-card">
  <a class="product-media" href="/products/${e(row.product_id)}">
    <img src="${e(productImage(row.thumbnail, true))}" alt="${e(row.name)}" loading="lazy" width="220" height="180">
  </a>
  <div class="product-body">
    <h3><a href="/products/${e(row.product_id)}">${e(row.name)}</a></h3>
    ${priceBlock(row)}
    <p class="muted">${e(truncate(row.description, 120))}</p>
    <form action="/cart" method="post" class="add-form" data-cart-add>
      <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
      <input type="hidden" name="action" value="add">
      <input type="hidden" name="productId" value="${e(row.product_id)}">
      <button type="submit">Add to cart</button>
    </form>
  </div>
</article>`;
}

function productGrid(ctx: ViewContext, items: ProductListRow[], emptyMessage: string): string {
  if (items.length === 0) return `<p class="empty">${e(emptyMessage)}</p>`;
  return `<div class="product-grid">${items.map((row) => productCard(ctx, row)).join('')}</div>`;
}

export function pager(meta: PageMeta, href: (page: number) => string): string {
  const links = paginationLinks(meta.page, meta.pages, href);
  if (links.length === 0) return '';
  return `<nav class="pager" aria-label="Pagination">
    ${meta.page > 1 ? `<a rel="prev" href="${e(href(meta.page - 1))}">Previous</a>` : ''}
    ${links
      .map((link) =>
        link.current
          ? `<span aria-current="page">${link.page}</span>`
          : `<a href="${e(link.href)}">${link.page}</a>`,
      )
      .join('')}
    ${meta.page < meta.pages ? `<a rel="next" href="${e(href(meta.page + 1))}">Next</a>` : ''}
  </nav>`;
}

export function homePage(
  ctx: ViewContext,
  data: { departments: NavDepartment[]; items: ProductListRow[]; meta: PageMeta },
): string {
  const body = `
<section class="hero">
  <h1>Hats for every head</h1>
  <p>Department stores, category browsing and full-text search over ${e(data.meta.total)} products — served as plain HTML from the edge.</p>
</section>
<h2>Latest arrivals</h2>
${productGrid(ctx, data.items, 'No products are listed yet.')}
${pager(data.meta, (page) => (page === 1 ? '/' : `/?page=${page}`))}`;
  return layout(ctx, 'Shop', body, { departments: data.departments });
}

export function departmentPage(
  ctx: ViewContext,
  data: {
    departments: NavDepartment[];
    departmentId: number;
    details: { name: string; description: string | null } | null;
    categories: Array<{ category_id: number; name: string }>;
    items: ProductListRow[];
    meta: PageMeta;
  },
): string {
  const name = data.details?.name ?? 'Department';
  const body = `
${breadcrumbs([{ label: 'Home', href: '/' }, { label: name }])}
<h1>${e(name)}</h1>
${data.details?.description ? `<p class="lede">${e(data.details.description)}</p>` : ''}
${
  data.categories.length
    ? `<nav class="chips" aria-label="Categories">${data.categories
        .map((c) => `<a href="/categories/${e(c.category_id)}">${e(c.name)}</a>`)
        .join('')}</nav>`
    : ''
}
${productGrid(ctx, data.items, 'This department has no products yet.')}
${pager(data.meta, (page) => `/departments/${data.departmentId}?page=${page}`)}`;
  return layout(ctx, name, body, { departments: data.departments });
}

export function categoryPage(
  ctx: ViewContext,
  data: {
    categories: NavDepartment[];
    categoryId: number;
    details: { name: string; description: string | null } | null;
    items: ProductListRow[];
    meta: PageMeta;
  },
): string {
  const name = data.details?.name ?? 'Category';
  const body = `
${breadcrumbs([{ label: 'Home', href: '/' }, { label: name }])}
<h1>${e(name)}</h1>
${data.details?.description ? `<p class="lede">${e(data.details.description)}</p>` : ''}
${productGrid(ctx, data.items, 'This category has no products yet.')}
${pager(data.meta, (page) => `/categories/${data.categoryId}?page=${page}`)}`;
  return layout(ctx, name, body, { departments: data.categories });
}

export function productPage(
  ctx: ViewContext,
  data: {
    departments: NavDepartment[];
    productId: number;
    details: {
      product_id: number;
      name: string;
      description: string | null;
      price: string;
      discounted_price: string | null;
      image: string | null;
    } | null;
    reviews: ReviewRow[];
    recommendations: Array<{ product_id: number; name: string; description: string | null }>;
  },
): string {
  if (!data.details) {
    return layout(
      ctx,
      'Product not found',
      `<h1>Product not found</h1><p><a href="/">Back to the shop</a></p>`,
      { departments: data.departments },
    );
  }
  const details = data.details;
  const reviewForm = ctx.customer
    ? `<form action="/products/${e(data.productId)}/reviews" method="post" class="card stacked">
        <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
        <h2>Write a review</h2>
        <label>Rating
          <select name="rating" required>
            <option value="5">5 — excellent</option>
            <option value="4">4</option>
            <option value="3">3</option>
            <option value="2">2</option>
            <option value="1">1 — poor</option>
          </select>
        </label>
        <label>Review
          <textarea name="review" rows="4" maxlength="1000" required></textarea>
        </label>
        <button type="submit">Publish review</button>
      </form>`
    : `<p class="muted"><a href="/login">Sign in</a> to write a review.</p>`;

  const body = `
${breadcrumbs([{ label: 'Home', href: '/' }, { label: details.name }])}
<article class="product-detail">
  <div class="product-detail-media">
    <img src="${e(productImage(details.image))}" alt="${e(details.name)}" width="420" height="340">
  </div>
  <div class="product-detail-body">
    <h1>${e(details.name)}</h1>
    ${priceBlock(details)}
    <p>${e(details.description ?? '')}</p>
    <form action="/cart" method="post" class="add-form" data-cart-add>
      <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
      <input type="hidden" name="action" value="add">
      <input type="hidden" name="productId" value="${e(details.product_id)}">
      <button type="submit" class="primary">Add to cart</button>
    </form>
  </div>
</article>
<section>
  <h2>Reviews</h2>
  ${
    data.reviews.length === 0
      ? '<p class="muted">No reviews yet.</p>'
      : `<ul class="reviews">${data.reviews
          .map(
            (r) => `<li>
              <p class="review-meta"><strong>${e(r.customer_name ?? 'Customer')}</strong>
              <span aria-label="${e(r.rating)} out of 5">${'★'.repeat(Number(r.rating))}${'☆'.repeat(Math.max(0, 5 - Number(r.rating)))}</span>
              <time datetime="${e(r.created_on)}">${e(dateLabel(r.created_on))}</time></p>
              <p>${e(r.review)}</p>
            </li>`,
          )
          .join('')}</ul>`
  }
  ${reviewForm}
</section>
${
  data.recommendations.length
    ? `<section><h2>You may also like</h2><div class="product-grid">${data.recommendations
        .map(
          (r) => `<article class="product-card"><div class="product-body">
            <h3><a href="/products/${e(r.product_id)}">${e(r.name)}</a></h3>
            <p class="muted">${e(truncate(r.description, 100))}</p>
            <p><a href="/products/${e(r.product_id)}">View product</a></p>
          </div></article>`,
        )
        .join('')}</div></section>`
    : ''
}`;
  return layout(ctx, details.name, body, { departments: data.departments });
}

export function searchPage(
  ctx: ViewContext,
  data: {
    departments: NavDepartment[];
    query: string;
    allWords: boolean;
    words: string[];
    items: ProductListRow[];
    meta: PageMeta;
    empty: boolean;
  },
): string {
  const href = (page: number) =>
    `/search?q=${encodeURIComponent(data.query)}${data.allWords ? '&all=1' : ''}&page=${page}`;
  const body = `
<h1>Search</h1>
<form action="/search" method="get" class="search-page-form">
  <label class="visually-hidden" for="q">Search terms</label>
  <input id="q" name="q" type="search" value="${e(data.query)}" required>
  <label class="checkbox"><input type="checkbox" name="all" value="1" ${data.allWords ? 'checked' : ''}> All words must match</label>
  <button type="submit">Search</button>
</form>
${
  data.query === ''
    ? '<p class="empty">Enter one or more words to search the catalog.</p>'
    : data.empty
      ? `<p class="empty">No products matched “${e(data.query)}”.</p>`
      : `<p class="muted">${e(data.meta.total)} result${data.meta.total === 1 ? '' : 's'} for “${e(data.query)}”.</p>
         ${productGrid(ctx, data.items, '')}
         ${pager(data.meta, href)}`
}`;
  return layout(ctx, 'Search', body, { departments: data.departments });
}

export function cartPage(ctx: ViewContext, data: { departments: NavDepartment[]; cart: CartView }): string {
  // Each cell owns its own small form: no hidden `action` input, so the clicked
  // button's value is the action that gets submitted.
  const rows = data.cart.items
    .map(
      (item: CartProductRow) => `<tr>
        <th scope="row"><a href="/products/${e(item.product_id)}">${e(item.name)}</a></th>
        <td>${money(item.price)}</td>
        <td>
          <form action="/cart" method="post" class="row-form">
            <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
            <input type="hidden" name="productId" value="${e(item.product_id)}">
            <input type="hidden" name="returnTo" value="/cart">
            <input type="number" name="quantity" value="${e(item.quantity)}" min="0" max="99" step="1" inputmode="numeric" aria-label="Quantity for ${e(item.name)}">
            <button type="submit" name="action" value="update" class="link-button">Update</button>
          </form>
        </td>
        <td>${money(item.subtotal)}</td>
        <td>
          <form action="/cart" method="post" class="row-form">
            <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
            <input type="hidden" name="productId" value="${e(item.product_id)}">
            <input type="hidden" name="returnTo" value="/cart">
            <button type="submit" name="action" value="remove" class="link-button">Remove</button>
            <button type="submit" name="action" value="save" class="link-button">Save for later</button>
          </form>
        </td>
      </tr>`,
    )
    .join('');

  const saved = data.cart.saved
    .map(
      (item: SavedProductRow) => `<li>${e(item.name)} — ${money(item.price)}
        <form action="/cart" method="post" class="inline">
          <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
          <input type="hidden" name="action" value="move">
          <input type="hidden" name="productId" value="${e(item.product_id)}">
          <button type="submit" class="link-button">Move to cart</button>
        </form></li>`,
    )
    .join('');

  const body = `
<h1>Your cart</h1>
${
  data.cart.isEmpty
    ? '<p class="empty">Your cart is empty. <a href="/">Continue shopping</a>.</p>'
    : `<table class="table cart-table">
        <caption class="visually-hidden">Cart contents</caption>
        <thead><tr><th scope="col">Product</th><th scope="col">Price</th><th scope="col">Qty</th><th scope="col">Subtotal</th><th scope="col">Actions</th></tr></thead>
        <tbody>${rows}</tbody>
        <tfoot><tr><th scope="row" colspan="3">Total</th><td colspan="2"><strong>${money(data.cart.total)}</strong></td></tr></tfoot>
      </table>
      <div class="actions">
        <a class="button primary" href="/checkout">Checkout</a>
        <a href="/">Continue shopping</a>
      </div>`
}
${
  saved
    ? `<section><h2>Saved for later</h2><ul class="saved-list">${saved}</ul></section>`
    : ''
}`;
  return layout(ctx, 'Your cart', body, { departments: data.departments });
}
