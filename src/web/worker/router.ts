/** Minimal router: ordered (method, pattern) pairs, no framework. */

export interface RouteMatch {
  name: string;
  params: Record<string, string>;
}

interface RouteDef {
  method: 'GET' | 'POST';
  pattern: RegExp;
  name: string;
}

const ROUTES: RouteDef[] = [
  { method: 'GET', pattern: /^\/$/, name: 'home' },
  { method: 'GET', pattern: /^\/health$/, name: 'health' },
  { method: 'GET', pattern: /^\/departments\/(\d+)$/, name: 'department' },
  { method: 'GET', pattern: /^\/categories\/(\d+)$/, name: 'category' },
  { method: 'GET', pattern: /^\/products\/(\d+)$/, name: 'product' },
  { method: 'POST', pattern: /^\/products\/(\d+)\/reviews$/, name: 'postReview' },
  { method: 'GET', pattern: /^\/search$/, name: 'search' },
  { method: 'GET', pattern: /^\/cart$/, name: 'cart' },
  { method: 'POST', pattern: /^\/cart$/, name: 'cartAction' },
  { method: 'GET', pattern: /^\/login$/, name: 'loginForm' },
  { method: 'POST', pattern: /^\/login$/, name: 'login' },
  { method: 'GET', pattern: /^\/register$/, name: 'registerForm' },
  { method: 'POST', pattern: /^\/register$/, name: 'register' },
  { method: 'POST', pattern: /^\/logout$/, name: 'logout' },
  { method: 'GET', pattern: /^\/account$/, name: 'account' },
  { method: 'POST', pattern: /^\/account$/, name: 'accountUpdate' },
  { method: 'POST', pattern: /^\/account\/address$/, name: 'addressUpdate' },
  { method: 'POST', pattern: /^\/account\/password$/, name: 'passwordUpdate' },
  { method: 'GET', pattern: /^\/orders$/, name: 'orders' },
  { method: 'GET', pattern: /^\/orders\/(\d+)$/, name: 'order' },
  { method: 'GET', pattern: /^\/checkout$/, name: 'checkoutForm' },
  { method: 'POST', pattern: /^\/checkout$/, name: 'checkout' },
  { method: 'GET', pattern: /^\/admin\/login$/, name: 'adminLoginForm' },
  { method: 'POST', pattern: /^\/admin\/login$/, name: 'adminLogin' },
  { method: 'POST', pattern: /^\/admin\/logout$/, name: 'adminLogout' },
  { method: 'GET', pattern: /^\/admin$/, name: 'adminHome' },
  { method: 'GET', pattern: /^\/admin\/products$/, name: 'adminProducts' },
  { method: 'GET', pattern: /^\/admin\/products\/(\d+)$/, name: 'adminProduct' },
  { method: 'GET', pattern: /^\/admin\/orders$/, name: 'adminOrders' },
  { method: 'GET', pattern: /^\/admin\/orders\/(\d+)$/, name: 'adminOrder' },
  { method: 'GET', pattern: /^\/admin\/catalog$/, name: 'adminCatalog' },
  { method: 'GET', pattern: /^\/admin\/customers$/, name: 'adminCustomers' },
  { method: 'GET', pattern: /^\/admin\/customers\/(\d+)$/, name: 'adminCustomer' },
  { method: 'POST', pattern: /^\/admin\/action$/, name: 'adminAction' },
];

const NAMED_PARAMS: Record<string, string[]> = {
  department: ['departmentId'],
  category: ['categoryId'],
  product: ['productId'],
  postReview: ['productId'],
  order: ['orderId'],
  adminProduct: ['productId'],
  adminOrder: ['orderId'],
  adminCustomer: ['customerId'],
};

export function matchRoute(method: string, pathname: string): RouteMatch | null {
  const verb = method === 'HEAD' ? 'GET' : method;
  for (const route of ROUTES) {
    if (route.method !== verb) continue;
    const match = route.pattern.exec(pathname);
    if (!match) continue;
    const names = NAMED_PARAMS[route.name] ?? [];
    const params: Record<string, string> = {};
    names.forEach((name, index) => {
      params[name] = match[index + 1] ?? '';
    });
    return { name: route.name, params };
  }
  return null;
}

export const ROUTE_NAMES = ROUTES.map((route) => `${route.method} ${route.name}`);
