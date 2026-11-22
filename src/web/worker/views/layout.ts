import { e, money } from '../html.ts';

export interface Flash {
  kind: 'ok' | 'error';
  message: string;
}

export interface ViewContext {
  siteName: string;
  csrf: string;
  cart: { itemCount: number; total: string } | null;
  customer: { customerId: number; name: string } | null;
  admin: boolean;
  flash: Flash | null;
}

export interface NavDepartment {
  department_id: number;
  name: string;
}

export function layout(
  ctx: ViewContext,
  title: string,
  body: string,
  options: { departments?: NavDepartment[]; wide?: boolean } = {},
): string {
  const navDepartments = options.departments ?? [];
  const cartLabel =
    ctx.cart && ctx.cart.itemCount > 0 ? `Cart (${ctx.cart.itemCount}) · ${money(ctx.cart.total)}` : 'Cart';

  return `<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>${e(title)} · ${e(ctx.siteName)}</title>
<meta name="description" content="HatShop — hats, caps and headwear.">
<link rel="stylesheet" href="/css/site.css">
<link rel="icon" href="/images/favicon.ico" sizes="any">
</head>
<body>
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header">
  <div class="wrap header-inner">
    <a class="brand" href="/">${e(ctx.siteName)}</a>
    <form class="search" action="/search" method="get" role="search">
      <label class="visually-hidden" for="q">Search products</label>
      <input id="q" name="q" type="search" placeholder="Search hats…" autocomplete="off" required>
      <button type="submit">Search</button>
    </form>
    <nav class="account-nav" aria-label="Account">
      <a href="/cart" class="cart-link" data-cart-link>${e(cartLabel)}</a>
      ${
        ctx.customer
          ? `<a href="/account">${e(ctx.customer.name)}</a><a href="/orders">Orders</a>
             <form action="/logout" method="post" class="inline">
               <input type="hidden" name="_csrf" value="${e(ctx.csrf)}">
               <button type="submit" class="link-button">Sign out</button>
             </form>`
          : `<a href="/login">Sign in</a><a href="/register">Register</a>`
      }
      ${ctx.admin ? '<a href="/admin">Admin</a>' : ''}
    </nav>
  </div>
  <nav class="departments" aria-label="Departments">
    <div class="wrap">
      <a href="/">Home</a>
      ${navDepartments
        .map((d) => `<a href="/departments/${e(d.department_id)}">${e(d.name)}</a>`)
        .join('\n      ')}
    </div>
  </nav>
</header>
${
  ctx.flash
    ? `<div class="wrap"><p class="flash flash-${ctx.flash.kind}" role="status">${e(ctx.flash.message)}</p></div>`
    : ''
}
<main id="main" class="${options.wide ? 'wrap wide' : 'wrap'}">
${body}
</main>
<footer class="site-footer">
  <div class="wrap">
    <p>${e(ctx.siteName)} — a demonstration storefront. Payments run in the payment provider's sandbox.</p>
  </div>
</footer>
<script type="module" src="/js/app.js"></script>
</body>
</html>`;
}

export function errorPage(ctx: ViewContext, status: number, message: string): string {
  return layout(
    ctx,
    `Error ${status}`,
    `<section class="card">
      <h1>Something went wrong</h1>
      <p>${e(message)}</p>
      <p><a href="/">Back to the shop</a></p>
    </section>`,
  );
}

export function breadcrumbs(trail: Array<{ label: string; href?: string }>): string {
  const items = trail.map((crumb, index) => {
    const isLast = index === trail.length - 1;
    const label = e(crumb.label);
    if (crumb.href && !isLast) return `<li><a href="${e(crumb.href)}">${label}</a></li>`;
    return `<li aria-current="page">${label}</li>`;
  });
  return `<nav aria-label="Breadcrumb"><ol class="breadcrumbs">${items.join('')}</ol></nav>`;
}
