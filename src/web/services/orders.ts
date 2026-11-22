/** Order read models. Ownership is checked against the customer's own order list. */
import {
  getAuditTrail,
  getOrderDetails,
  getOrderInfo,
  getOrdersForCustomer,
  getMostRecentOrders,
} from '../db/queries.ts';
import type { Db } from '../db/types.ts';

export const ORDER_STATUS_LABELS: Readonly<Record<number, string>> = {
  0: 'Placed',
  1: 'Customer notified',
  2: 'Funds checked',
  3: 'Authorized — awaiting stock',
  4: 'Stock confirmed',
  5: 'Paid',
  6: 'Goods shipped',
  7: 'Shipping confirmed',
  8: 'Completed',
};

export function statusLabel(status: number): string {
  return ORDER_STATUS_LABELS[status] ?? `Status ${status}`;
}

export async function ordersForCustomer(db: Db, customerId: number) {
  return getOrdersForCustomer(db, customerId);
}

export async function recentOrders(db: Db, limit = 50) {
  return getMostRecentOrders(db, limit);
}

/**
 * Returns an order only when it belongs to the customer.
 *
 * `orders_get_order_info` does not expose customer_id, so ownership is proven by
 * looking the id up in that customer's own order list first.
 */
export async function orderForCustomer(db: Db, customerId: number, orderId: number) {
  const own = await getOrdersForCustomer(db, customerId);
  if (!own.some((row) => Number(row.order_id) === orderId)) return null;
  const [order, details] = await Promise.all([getOrderInfo(db, orderId), getOrderDetails(db, orderId)]);
  return order ? { order, details } : null;
}

/** Admin view: order, lines and audit trail. Authorization happens in the route. */
export async function orderForAdmin(db: Db, orderId: number) {
  const [order, details, audit] = await Promise.all([
    getOrderInfo(db, orderId),
    getOrderDetails(db, orderId),
    getAuditTrail(db, orderId),
  ]);
  return order ? { order, details, audit } : null;
}
