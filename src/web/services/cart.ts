/** Cart operations. Totals always come from the database, never from the browser. */
import {
  addToCart,
  getCartProducts,
  getCartTotal,
  getSavedProducts,
  moveToCart,
  removeFromCart,
  saveForLater,
  updateCart,
} from '../db/queries.ts';
import type { CartProductRow, Db, SavedProductRow } from '../db/types.ts';
import * as V from './validation.ts';

export interface CartView {
  items: CartProductRow[];
  saved: SavedProductRow[];
  total: string;
  itemCount: number;
  isEmpty: boolean;
}

export type CartAction = 'add' | 'update' | 'remove' | 'save' | 'move';

export async function readCart(db: Db, cartId: string, descriptionLength: number): Promise<CartView> {
  const [items, saved, totalRow] = await Promise.all([
    getCartProducts(db, cartId, descriptionLength),
    getSavedProducts(db, cartId),
    getCartTotal(db, cartId),
  ]);
  const total = totalRow?.total ?? '0.00';
  const itemCount = items.reduce((sum, item) => sum + Number(item.quantity ?? 0), 0);
  return { items, saved, total: Number(total).toFixed(2), itemCount, isEmpty: items.length === 0 };
}

export interface CartMutation {
  action: CartAction;
  productId?: number;
  quantities?: Array<{ productId: number; quantity: number }>;
}

export function parseCartMutation(form: FormData): CartMutation {
  const action = V.text(form.get('action'), 'action', 10, { required: true });
  if (action === 'update') {
    const ids = form.getAll('productId');
    const qtys = form.getAll('quantity');
    if (ids.length !== qtys.length) throw new V.ValidationError('quantity', 'Quantity data is incomplete');
    const quantities = ids.map((id, index) => ({
      productId: V.integer(id, 'productId', { min: 1 }),
      quantity: V.integer(qtys[index], 'quantity', { min: 0, max: 99 }),
    }));
    return { action: 'update', quantities };
  }
  if (action === 'add' || action === 'remove' || action === 'save' || action === 'move') {
    return { action, productId: V.integer(form.get('productId'), 'productId', { min: 1 }) };
  }
  throw new V.ValidationError('action', 'Unknown cart action');
}

/** Applies one cart mutation. Each branch is a single database function call. */
export async function applyCartMutation(db: Db, cartId: string, mutation: CartMutation): Promise<void> {
  switch (mutation.action) {
    case 'add':
      await addToCart(db, cartId, mutation.productId!);
      return;
    case 'remove':
      await removeFromCart(db, cartId, mutation.productId!);
      return;
    case 'save':
      await saveForLater(db, cartId, mutation.productId!);
      return;
    case 'move':
      await moveToCart(db, cartId, mutation.productId!);
      return;
    case 'update': {
      const quantities = mutation.quantities ?? [];
      if (quantities.length === 0) return;
      // Quantity 0 is the schema's "remove this line" signal.
      const keep = quantities.filter((line) => line.quantity > 0);
      for (const line of quantities.filter((l) => l.quantity === 0)) {
        await removeFromCart(db, cartId, line.productId);
      }
      if (keep.length > 0) {
        await updateCart(
          db,
          cartId,
          keep.map((line) => line.productId),
          keep.map((line) => line.quantity),
        );
      }
      return;
    }
  }
}

/** Server-side total for a cart, used by checkout to size the authorization. */
export async function cartTotal(db: Db, cartId: string): Promise<string> {
  const row = await getCartTotal(db, cartId);
  return Number(row?.total ?? '0').toFixed(2);
}
