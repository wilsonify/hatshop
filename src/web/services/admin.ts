/**
 * Administration: authentication and catalog/order actions.
 *
 * Admin credentials live in Worker secrets (ADMIN_USERNAME, ADMIN_PASSWORD_HASH)
 * and never in the database or the repository. Every action is dispatched from a
 * single validated endpoint, after a server-side role check.
 */
import { sha256Hex, timingSafeEqual } from '../auth/password.ts';
import {
  addCategory,
  addDepartment,
  addProductToCategory,
  assignProductToCategory,
  deleteCategory,
  deleteDepartment,
  deleteProduct,
  getDepartments,
  getAllCategories,
  getCategoriesForProduct,
  getCategoryProducts,
  getCustomer,
  getCustomersList,
  getDepartmentCategories,
  getOrderInfo,
  getOrderPaymentRefs,
  getProductInfo,
  listAllProducts,
  markShipped,
  moveProductToCategory,
  removeProductFromCategory,
  setProductDisplayOption,
  updateCategory,
  updateDepartment,
  updateOrder,
  updateOrderStatus,
  updateProduct,
} from '../db/queries.ts';
import type { Db } from '../db/types.ts';
import * as V from './validation.ts';

export interface AdminCredentials {
  ADMIN_USERNAME?: string;
  ADMIN_PASSWORD_HASH?: string;
}

/** True when admin login is even possible (secrets present). */
export function adminConfigured(env: AdminCredentials): boolean {
  return Boolean(env.ADMIN_USERNAME && env.ADMIN_PASSWORD_HASH);
}

/**
 * Verifies admin credentials. The configured value is a SHA-256 hex digest so the
 * plaintext password is never stored anywhere.
 */
export async function verifyAdmin(env: AdminCredentials, username: unknown, password: unknown): Promise<boolean> {
  if (!adminConfigured(env)) return false;
  const user = typeof username === 'string' ? username.trim() : '';
  const pass = typeof password === 'string' ? password : '';
  const expectedUser = env.ADMIN_USERNAME ?? '';
  const expectedHash = (env.ADMIN_PASSWORD_HASH ?? '').trim().toLowerCase();
  if (!/^[0-9a-f]{64}$/.test(expectedHash)) return false;
  // Always hash, so a missing user and a wrong password cost the same.
  const actualHash = await sha256Hex(pass);
  const userOk = timingSafeEqual(user.toLowerCase(), expectedUser.toLowerCase());
  const passOk = timingSafeEqual(actualHash, expectedHash);
  return userOk && passOk;
}

export interface AdminDashboard {
  departments: Array<{ department_id: number; name: string; description: string | null }>;
  categories: Array<{ category_id: number; name: string; description: string | null }>;
  products: Array<{ product_id: number; name: string; price: string; discounted_price: string; display: number }>;
  customers: Array<{ customer_id: number; name: string }>;
}

export async function adminDashboard(db: Db): Promise<AdminDashboard> {
  const [departments, categories, products, customers] = await Promise.all([
    getDepartments(db),
    getAllCategories(db),
    listAllProducts(db, 200, 0),
    getCustomersList(db),
  ]);
  return { departments, categories, products, customers };
}

export async function catalogDetail(db: Db, departmentId: number) {
  const [categories, products] = await Promise.all([
    getDepartmentCategories(db, departmentId),
    getCategoryProducts(db, departmentId),
  ]);
  return { categories, products };
}

export async function productDetail(db: Db, productId: number) {
  const [info, categories, allCategories] = await Promise.all([
    getProductInfo(db, productId),
    getCategoriesForProduct(db, productId),
    getAllCategories(db),
  ]);
  return { info, categories, allCategories };
}

export async function customerDetail(db: Db, customerId: number) {
  const customer = await getCustomer(db, customerId);
  if (!customer) return null;
  // credit_card is deliberately not returned to the view layer.
  const { credit_card: _unused, ...safe } = customer;
  return safe;
}

export type AdminActionResult = { ok: true; message: string } | { ok: false; message: string };

const ok = (message: string): AdminActionResult => ({ ok: true, message });
const fail = (message: string): AdminActionResult => ({ ok: false, message });

/** Image names are validated as plain file names: no paths, no traversal. */
function imageName(value: unknown, field: string): string {
  const name = V.text(value, field, 150);
  if (name === '') return '';
  if (!/^[A-Za-z0-9._-]+$/.test(name)) throw new V.ValidationError(field, 'Use a plain file name');
  return name;
}

/** Single dispatch point for admin mutations. */
export async function runAdminAction(db: Db, form: FormData): Promise<AdminActionResult> {
  const action = V.text(form.get('action'), 'action', 40, { required: true });

  switch (action) {
    case 'update-product': {
      const id = V.integer(form.get('productId'), 'productId', { min: 1 });
      await updateProduct(
        db,
        id,
        V.text(form.get('name'), 'name', 50, { required: true }),
        V.text(form.get('description'), 'description', 1000, { required: true }),
        V.money(form.get('price'), 'price'),
        V.money(form.get('discountedPrice') || '0', 'discountedPrice'),
      );
      return ok('Product updated.');
    }
    case 'add-product': {
      const categoryId = V.integer(form.get('categoryId'), 'categoryId', { min: 1 });
      await addProductToCategory(
        db,
        categoryId,
        V.text(form.get('name'), 'name', 50, { required: true }),
        V.text(form.get('description'), 'description', 1000, { required: true }),
        V.money(form.get('price'), 'price'),
      );
      return ok('Product added.');
    }
    case 'delete-product': {
      await deleteProduct(db, V.integer(form.get('productId'), 'productId', { min: 1 }));
      return ok('Product deleted.');
    }
    case 'set-display': {
      const display = form.get('display') === '1' ? 1 : 0;
      await setProductDisplayOption(db, V.integer(form.get('productId'), 'productId', { min: 1 }), display);
      return ok(display ? 'Product is visible.' : 'Product hidden.');
    }
    case 'set-image': {
      const id = V.integer(form.get('productId'), 'productId', { min: 1 });
      const image = imageName(form.get('image'), 'image');
      const { catalogSetImage } = await import('../db/admin-images.ts');
      await catalogSetImage(db, id, image);
      return ok('Image reference updated.');
    }
    case 'add-department': {
      await addDepartment(
        db,
        V.text(form.get('name'), 'name', 50, { required: true }),
        V.text(form.get('description'), 'description', 1000),
      );
      return ok('Department added.');
    }
    case 'update-department': {
      await updateDepartment(
        db,
        V.integer(form.get('departmentId'), 'departmentId', { min: 1 }),
        V.text(form.get('name'), 'name', 50, { required: true }),
        V.text(form.get('description'), 'description', 1000),
      );
      return ok('Department updated.');
    }
    case 'delete-department': {
      await deleteDepartment(db, V.integer(form.get('departmentId'), 'departmentId', { min: 1 }));
      return ok('Department deleted.');
    }
    case 'add-category': {
      await addCategory(
        db,
        V.integer(form.get('departmentId'), 'departmentId', { min: 1 }),
        V.text(form.get('name'), 'name', 50, { required: true }),
        V.text(form.get('description'), 'description', 1000),
      );
      return ok('Category added.');
    }
    case 'update-category': {
      await updateCategory(
        db,
        V.integer(form.get('categoryId'), 'categoryId', { min: 1 }),
        V.text(form.get('name'), 'name', 50, { required: true }),
        V.text(form.get('description'), 'description', 1000),
      );
      return ok('Category updated.');
    }
    case 'delete-category': {
      await deleteCategory(db, V.integer(form.get('categoryId'), 'categoryId', { min: 1 }));
      return ok('Category deleted.');
    }
    case 'assign-category': {
      await assignProductToCategory(
        db,
        V.integer(form.get('productId'), 'productId', { min: 1 }),
        V.integer(form.get('categoryId'), 'categoryId', { min: 1 }),
      );
      return ok('Product assigned to category.');
    }
    case 'move-category': {
      await moveProductToCategory(
        db,
        V.integer(form.get('productId'), 'productId', { min: 1 }),
        V.integer(form.get('sourceCategoryId'), 'sourceCategoryId', { min: 1 }),
        V.integer(form.get('targetCategoryId'), 'targetCategoryId', { min: 1 }),
      );
      return ok('Product moved.');
    }
    case 'remove-category': {
      await removeProductFromCategory(
        db,
        V.integer(form.get('productId'), 'productId', { min: 1 }),
        V.integer(form.get('categoryId'), 'categoryId', { min: 1 }),
      );
      return ok('Product removed from category.');
    }
    case 'update-order-status': {
      const orderId = V.integer(form.get('orderId'), 'orderId', { min: 1 });
      const status = V.integer(form.get('status'), 'status', { min: 0, max: 8 });
      const [existing, refs] = await Promise.all([getOrderInfo(db, orderId), getOrderPaymentRefs(db, orderId)]);
      if (!existing) return fail('Order not found.');
      // Never let an admin jump an unpaid order straight to a paid state.
      if (status >= 5 && Number(existing.status) < 3 && !refs?.auth_code) {
        return fail('This order has no payment authorization; it cannot be marked paid.');
      }
      await updateOrderStatus(db, orderId, status);
      return ok(`Order status set to ${status}.`);
    }
    case 'mark-shipped': {
      const orderId = V.integer(form.get('orderId'), 'orderId', { min: 1 });
      await markShipped(db, orderId);
      await updateOrderStatus(db, orderId, 6);
      return ok('Order marked as shipped.');
    }
    case 'update-order': {
      const orderId = V.integer(form.get('orderId'), 'orderId', { min: 1 });
      await updateOrder(
        db,
        orderId,
        V.integer(form.get('status'), 'status', { min: 0, max: 8 }),
        V.text(form.get('comments'), 'comments', 255),
        V.text(form.get('customerName'), 'customerName', 50),
        V.text(form.get('shippingAddress'), 'shippingAddress', 255),
        V.text(form.get('customerEmail'), 'customerEmail', 50),
      );
      return ok('Order updated.');
    }
    default:
      return fail('Unknown action.');
  }
}
