/**
 * Minimal database surface used by services.
 *
 * Services receive a `Db`, never an `Env`, so business logic can be unit tested
 * against a fake without a Worker runtime or a network connection.
 */
export interface Db {
  query<T extends Record<string, unknown> = Record<string, unknown>>(
    sql: string,
    params?: readonly unknown[],
  ): Promise<{ rows: T[] }>;
}

/** Row shapes returned by the live catalog/order functions (see docs/schema-inventory.md). */
export interface DepartmentRow {
  department_id: number;
  name: string;
  description: string | null;
}

export interface CategoryListRow {
  category_id: number;
  name: string;
}

export interface ProductListRow {
  product_id: number;
  name: string;
  description: string | null;
  price: string;
  discounted_price: string | null;
  thumbnail: string | null;
}

export interface ProductDetailsRow {
  product_id: number;
  name: string;
  description: string | null;
  price: string;
  discounted_price: string | null;
  image: string | null;
}

export interface RecommendationRow {
  product_id: number;
  name: string;
  description: string | null;
}

export interface ReviewRow {
  customer_name: string | null;
  review: string;
  rating: number;
  created_on: string;
}

export interface CartProductRow {
  product_id: number;
  name: string;
  price: string;
  quantity: number;
  subtotal: string;
}

export interface SavedProductRow {
  product_id: number;
  name: string;
  price: string;
}

export interface CustomerRow {
  customer_id: number;
  name: string;
  email: string;
  password: string;
  credit_card: string | null;
  address_1: string | null;
  address_2: string | null;
  city: string | null;
  region: string | null;
  postal_code: string | null;
  country: string | null;
  shipping_region_id: number;
  day_phone: string | null;
  eve_phone: string | null;
  mob_phone: string | null;
}

export interface ShippingRegionRow {
  shipping_region_id: number;
  shipping_region: string;
}

export interface ShippingRow {
  shipping_id: number;
  shipping_type: string;
  shipping_cost: string;
  shipping_region_id: number;
}

export interface TaxRow {
  tax_id: number;
  tax_type: string;
  tax_percentage: string;
}

export interface OrderShortRow {
  order_id: number;
  total_amount: string;
  created_on: string;
  shipped_on: string | null;
  status: number;
  customer_name: string | null;
}

export interface OrderRow {
  order_id: number;
  total_amount: string;
  created_on: string;
  shipped_on: string | null;
  status: number;
  comments: string | null;
  customer_name: string | null;
  shipping_address: string | null;
  customer_email: string | null;
}

export interface OrderDetailRow {
  order_id: number;
  product_id: number;
  product_name: string;
  quantity: number;
  unit_cost: string;
  subtotal: string;
}
