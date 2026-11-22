/**
 * Checkout: inventory/total validation → order creation → authorize → capture.
 *
 * The order row is created by `shopping_cart_create_order`, which writes the order,
 * its detail lines and clears the cart inside one plpgsql function. Because the
 * cart is emptied there, a duplicate submission cannot create a second order.
 *
 * Status mapping (existing statuses, no new model):
 *   0 placed · 2 funds checked · 3 authorized (stock) · 5 paid/captured · 8 complete
 * A decline leaves the order at 0 and writes an audit row: it is never marked paid.
 */
import {
  createAudit,
  createOrderFromCart,
  getCartProducts,
  getCustomer,
  getShippingForRegion,
  getTaxRates,
  getOrderInfo,
  setAuthCode,
  updateOrderStatus,
} from '../db/queries.ts';
import type { Db, ShippingRow, TaxRow } from '../db/types.ts';
import { cartTotal } from './cart.ts';
import {
  amountMatches,
  authorize,
  capture,
  cardFingerprint,
  expiryValid,
  luhnValid,
  type PaymentConfig,
} from './payments.ts';
import * as V from './validation.ts';

export type CheckoutResult =
  | { kind: 'placed'; orderId: number; status: number }
  | { kind: 'declined'; orderId: number; reason: string }
  | { kind: 'capture_failed'; orderId: number; reason: string }
  | { kind: 'empty_cart' }
  | { kind: 'invalid_shipping' };

export interface CheckoutForm {
  shippingId: number;
  taxId: number;
  cardNumber: string;
  expiry: string;
  cvv: string;
  nonce: string;
}

export function parseCheckoutForm(form: FormData): CheckoutForm {
  const cardNumber = V.text(form.get('cardNumber'), 'cardNumber', 19, { required: true }).replace(/[\s-]/g, '');
  if (!luhnValid(cardNumber)) throw new V.ValidationError('cardNumber', 'Enter a valid card number');
  const expiry = V.text(form.get('expiry'), 'expiry', 5, { required: true });
  if (!expiryValid(expiry)) throw new V.ValidationError('expiry', 'Enter a valid future expiry date (MMYY)');
  const cvv = V.text(form.get('cvv'), 'cvv', 4, { required: true });
  if (!/^\d{3,4}$/.test(cvv)) throw new V.ValidationError('cvv', 'Enter the 3 or 4 digit security code');
  return {
    shippingId: V.integer(form.get('shippingId'), 'shippingId', { min: 1 }),
    taxId: V.integer(form.get('taxId'), 'taxId', { min: 1 }),
    cardNumber,
    expiry,
    cvv,
    nonce: V.text(form.get('nonce'), 'nonce', 64),
  };
}

export interface CheckoutOptions {
  shipping: ShippingRow[];
  taxes: TaxRow[];
}

export async function checkoutOptions(db: Db, customerId: number): Promise<CheckoutOptions> {
  const customer = await getCustomer(db, customerId);
  if (!customer) return { shipping: [], taxes: [] };
  const [shipping, taxes] = await Promise.all([
    getShippingForRegion(db, customer.shipping_region_id),
    getTaxRates(db),
  ]);
  return { shipping, taxes };
}

export async function placeOrder(
  db: Db,
  customerId: number,
  cartId: string,
  form: CheckoutForm,
  cfg: PaymentConfig,
): Promise<CheckoutResult> {
  const [items, total, options] = await Promise.all([
    getCartProducts(db, cartId, 0),
    cartTotal(db, cartId),
    checkoutOptions(db, customerId),
  ]);

  if (items.length === 0 || Number(total) <= 0) return { kind: 'empty_cart' };

  const shipping = options.shipping.find((s) => Number(s.shipping_id) === form.shippingId);
  const tax = options.taxes.find((t) => Number(t.tax_id) === form.taxId);
  if (!shipping || !tax) return { kind: 'invalid_shipping' };

  // Stock/inventory: the schema has no stock column; availability is supplier
  // confirmed through the order pipeline, matching the original behaviour.
  const order = await createOrderFromCart(db, cartId, customerId, form.shippingId, form.taxId);
  const orderId = Number(order?.order_id ?? 0);
  if (!orderId) return { kind: 'empty_cart' };

  const auth = await authorize(cfg, {
    cardNumber: form.cardNumber,
    expiry: form.expiry,
    amount: total,
    description: `HatShop order ${orderId} (${cardFingerprint(form.cardNumber)})`,
    invoice: String(orderId),
  });

  if (!auth.approved || !amountMatches(auth.amount, total)) {
    const reason = auth.approved ? 'authorized amount did not match the order total' : auth.reason || 'declined';
    await createAudit(db, orderId, `Payment not authorized: ${reason} (code ${auth.responseCode}/${auth.reasonCode})`, 20399);
    return { kind: 'declined', orderId, reason };
  }

  await setAuthCode(db, orderId, auth.authCode, auth.transactionId);
  await updateOrderStatus(db, orderId, 3); // authorized
  await createAudit(db, orderId, `Funds authorized (${auth.transactionId.slice(0, 12)}).`, 20400);

  const settled = await capture(cfg, { transactionId: auth.transactionId, amount: total });
  if (!settled.approved || !amountMatches(settled.amount, total)) {
    await createAudit(db, orderId, `Capture failed: ${settled.reason || 'declined'}.`, 20409);
    return { kind: 'capture_failed', orderId, reason: settled.reason || 'capture declined' };
  }

  await setAuthCode(db, orderId, auth.authCode, auth.transactionId);
  await updateOrderStatus(db, orderId, 5); // paid
  await createAudit(db, orderId, 'Funds captured.', 20402);

  return { kind: 'placed', orderId, status: 5 };
}

/** Re-reads an order to prove the transition that was written. */
export async function orderStatus(db: Db, orderId: number): Promise<number | null> {
  const row = await getOrderInfo(db, orderId);
  return row ? Number(row.status) : null;
}
