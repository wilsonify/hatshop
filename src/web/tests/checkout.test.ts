import assert from 'node:assert/strict';
import { test, afterEach } from 'node:test';
import { orderStatus, placeOrder, type CheckoutForm } from '../services/checkout.ts';
import type { PaymentConfig } from '../services/payments.ts';
import { fakeDb } from './fake-db.ts';
import { one } from './fake-db.ts';

const CART = 'b'.repeat(128);
const CUSTOMER = 5;

const cfg: PaymentConfig = {
  configured: true,
  gatewayUrl: 'https://test.authorize.net/gateway/transact.dll',
  loginId: 'login',
  transactionKey: 'key',
};

const form: CheckoutForm = {
  shippingId: 3,
  taxId: 1,
  cardNumber: '4111111111111111',
  expiry: '1230',
  cvv: '123',
  nonce: 'nonce',
};

const originalFetch = globalThis.fetch;
afterEach(() => {
  globalThis.fetch = originalFetch;
});

function stubGateway(body: string) {
  globalThis.fetch = (async () => new Response(body, { status: 200 })) as typeof fetch;
}

function dbWith(overrides: {
  items?: number;
  total?: string;
  shipping?: Array<Record<string, unknown>>;
  taxes?: Array<Record<string, unknown>>;
  orderId?: number;
} = {}) {
  const items = overrides.items ?? 1;
  return fakeDb([
    [
      /shopping_cart_get_products/,
      () =>
        items === 0
          ? []
          : [{ product_id: 1, name: 'Hat', price: '25.00', quantity: 1, subtotal: '25.00' }],
    ],
    [/shopping_cart_get_total_amount/, one({ total: overrides.total ?? '25.00' })],
    [/customer_get_customer/, one({ customer_id: CUSTOMER, shipping_region_id: 2, name: 'Ada', email: 'ada@example.com' })],
    [/FROM shipping/, () => overrides.shipping ?? [{ shipping_id: 3, shipping_type: 'Standard', shipping_cost: '0.00', shipping_region_id: 2 }]],
    [/FROM tax/, () => overrides.taxes ?? [{ tax_id: 1, tax_type: 'Standard', tax_percentage: '0.00' }]],
    [/shopping_cart_create_order/, one({ order_id: overrides.orderId ?? 77 })],
    [/orders_get_order_info/, one({ order_id: 77, status: 5 })],
  ]);
}

test('an empty cart never creates an order', async () => {
  stubGateway('1|1|1|Approved|A|Y|T1|1|d|25.00');
  const db = dbWith({ items: 0, total: '0.00' });
  const result = await placeOrder(db, CUSTOMER, CART, form, cfg);

  assert.equal(result.kind, 'empty_cart');
  assert.equal(db.countOf(/shopping_cart_create_order/), 0);
});

test('a shipping method outside the customer region is refused', async () => {
  stubGateway('1|1|1|Approved|A|Y|T1|1|d|25.00');
  const db = dbWith({ shipping: [{ shipping_id: 9, shipping_type: 'Other', shipping_cost: '1.00', shipping_region_id: 2 }] });
  const result = await placeOrder(db, CUSTOMER, CART, form, cfg);

  assert.equal(result.kind, 'invalid_shipping');
  assert.equal(db.countOf(/shopping_cart_create_order/), 0);
});

test('an approved payment writes the reference and walks 3 → 5', async () => {
  stubGateway('1|1|1|Approved|AUTH01|Y|TXN9|77|HatShop|25.00');
  const db = dbWith();
  const result = await placeOrder(db, CUSTOMER, CART, form, cfg);

  assert.equal(result.kind, 'placed');
  if (result.kind === 'placed') {
    assert.equal(result.orderId, 77);
    assert.equal(result.status, 5);
  }

  const auth = db.callParams(/orders_set_auth_code/);
  assert.ok(auth.length >= 1);
  assert.deepEqual(auth[0], [77, 'AUTH01', 'TXN9']);

  const statuses = db.callParams(/orders_update_status/).map((params) => params[1]);
  assert.deepEqual(statuses, [3, 5], 'authorized then captured');
});

test('a decline records an audit row and never marks the order paid', async () => {
  stubGateway('2|1|2|Declined|000000||TXN1|77|d|25.00');
  const db = dbWith();
  const result = await placeOrder(db, CUSTOMER, CART, form, cfg);

  assert.equal(result.kind, 'declined');
  assert.equal(db.countOf(/orders_update_status/), 0, 'status untouched on decline');
  assert.equal(db.countOf(/orders_set_auth_code/), 0, 'no reference stored');
  assert.equal(db.countOf(/orders_create_audit/), 1);
});

test('an approved amount that disagrees with the database total is refused', async () => {
  stubGateway('1|1|1|Approved|AUTH01|Y|TXN9|77|d|99.00');
  const db = dbWith({ total: '25.00' });
  const result = await placeOrder(db, CUSTOMER, CART, form, cfg);

  assert.equal(result.kind, 'declined');
  assert.equal(db.countOf(/orders_update_status/), 0);
  assert.match(String(db.callParams(/orders_create_audit/)[0]?.[1]), /did not match/);
});

test('a failed capture leaves the order authorized, not paid', async () => {
  const responses = [
    '1|1|1|Approved|AUTH01|Y|TXN9|77|d|25.00',
    '3|1|11|Capture failed|000000||TXN9|77|d|25.00',
  ];
  let call = 0;
  globalThis.fetch = (async () => new Response(responses[call++] ?? responses[1]!, { status: 200 })) as typeof fetch;

  const db = dbWith();
  const result = await placeOrder(db, CUSTOMER, CART, form, cfg);

  assert.equal(result.kind, 'capture_failed');
  const statuses = db.callParams(/orders_update_status/).map((params) => params[1]);
  assert.deepEqual(statuses, [3], 'stops at authorized');
});

test('the total used for authorization is the database total', async () => {
  stubGateway('1|1|1|Approved|AUTH01|Y|TXN9|77|d|42.50');
  let sentAmount = '';
  globalThis.fetch = (async (_input: RequestInfo | URL, init?: RequestInit) => {
    const body = new URLSearchParams(String(init?.body ?? ''));
    sentAmount = body.get('x_amount') ?? '';
    return new Response('1|1|1|Approved|AUTH01|Y|TXN9|77|d|42.50', { status: 200 });
  }) as typeof fetch;

  const db = dbWith({ total: '42.50' });
  const result = await placeOrder(db, CUSTOMER, CART, form, cfg);

  assert.equal(result.kind, 'placed');
  assert.equal(sentAmount, '42.50');
});

test('order status is re-read from the database after checkout', async () => {
  const db = dbWith();
  assert.equal(await orderStatus(db, 77), 5);
});
