import assert from 'node:assert/strict';
import { test } from 'node:test';
import { applyCartMutation, parseCartMutation, readCart } from '../services/cart.ts';
import { ValidationError } from '../services/validation.ts';
import { fakeDb } from './fake-db.ts';

const CART = 'a'.repeat(128);

function form(fields: Record<string, string | string[]>): FormData {
  const data = new FormData();
  for (const [key, value] of Object.entries(fields)) {
    for (const item of Array.isArray(value) ? value : [value]) data.append(key, item);
  }
  return data;
}

test('parses an add action with a product id', () => {
  const mutation = parseCartMutation(form({ action: 'add', productId: '12' }));
  assert.deepEqual(mutation, { action: 'add', productId: 12 });
});

test('rejects unknown actions and bad ids', () => {
  assert.throws(() => parseCartMutation(form({ action: 'destroy', productId: '1' })), ValidationError);
  assert.throws(() => parseCartMutation(form({ action: 'add', productId: '0' })), ValidationError);
  assert.throws(() => parseCartMutation(form({ action: 'add', productId: 'abc' })), ValidationError);
});

test('parses quantity updates pairwise', () => {
  const mutation = parseCartMutation(
    form({ action: 'update', productId: ['3', '4'], quantity: ['2', '5'] }),
  );
  assert.deepEqual(mutation, {
    action: 'update',
    quantities: [
      { productId: 3, quantity: 2 },
      { productId: 4, quantity: 5 },
    ],
  });
});

test('mismatched quantity pairs are rejected', () => {
  assert.throws(
    () => parseCartMutation(form({ action: 'update', productId: ['3', '4'], quantity: ['2'] })),
    ValidationError,
  );
});

test('update sends one database call, and quantity 0 removes the line', async () => {
  const db = fakeDb();
  await applyCartMutation(db, CART, {
    action: 'update',
    quantities: [
      { productId: 3, quantity: 0 },
      { productId: 4, quantity: 2 },
    ],
  });

  assert.equal(db.countOf(/shopping_cart_remove_product/), 1);
  assert.equal(db.countOf(/shopping_cart_update/), 1);
  assert.deepEqual(db.callParams(/shopping_cart_update/)[0]?.slice(1), [[4], [2]]);
});

test('each mutation maps to exactly one cart function', async () => {
  const cases: Array<[Parameters<typeof applyCartMutation>[2], RegExp]> = [
    [{ action: 'add', productId: 1 }, /shopping_cart_add_product/],
    [{ action: 'remove', productId: 1 }, /shopping_cart_remove_product/],
    [{ action: 'save', productId: 1 }, /shopping_cart_save_product_for_later/],
    [{ action: 'move', productId: 1 }, /shopping_cart_move_product_to_cart/],
  ];
  for (const [mutation, pattern] of cases) {
    const db = fakeDb();
    await applyCartMutation(db, CART, mutation);
    assert.equal(db.calls.length, 1, `${mutation.action} issues one statement`);
    assert.match(db.calls[0]!.sql, pattern);
    assert.equal(db.calls[0]!.params[0], CART);
  }
});

test('cart totals come from the database, never from the caller', async () => {
  const db = fakeDb([
    [/shopping_cart_get_products/, () => [{ product_id: 1, name: 'Hat', price: '10.00', quantity: 2, subtotal: '20.00' }]],
    [/shopping_cart_get_saved_products/, () => [{ product_id: 2, name: 'Cap', price: '5.00' }]],
    [/shopping_cart_get_total_amount/, () => [{ total: '20.00' }]],
  ]);

  const cart = await readCart(db, CART, 150);
  assert.equal(cart.total, '20.00');
  assert.equal(cart.itemCount, 2);
  assert.equal(cart.isEmpty, false);
  assert.equal(cart.saved.length, 1);
});

test('an empty cart reports zero rather than null', async () => {
  const db = fakeDb([[/shopping_cart_get_total_amount/, () => [{ total: null }]]]);
  const cart = await readCart(db, CART, 150);
  assert.equal(cart.total, '0.00');
  assert.equal(cart.isEmpty, true);
});
