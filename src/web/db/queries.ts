/**
 * Every SQL statement in the application.
 *
 * Rules:
 *  - Only the live database's functions are called; business rules are not
 *    reimplemented here (the live snapshot is docs/schema-snapshot.json).
 *  - All values are bound parameters. Parameter casts disambiguate the
 *    overloaded functions (`shopping_cart_create_order`, `orders_update_order`).
 *  - Two plain SELECTs exist (tax list, shipping for a region, admin product
 *    list) because the database exposes no function for them.
 */
import type {
  CartProductRow,
  CategoryListRow,
  CustomerRow,
  Db,
  DepartmentRow,
  OrderDetailRow,
  OrderRow,
  OrderShortRow,
  ProductDetailsRow,
  ProductListRow,
  RecommendationRow,
  ReviewRow,
  SavedProductRow,
  ShippingRegionRow,
  ShippingRow,
  TaxRow,
} from './types.ts';

const rows = async <T>(db: Db, sql: string, params: readonly unknown[] = []): Promise<T[]> =>
  (await db.query(sql, params)).rows as T[];

const first = async <T>(db: Db, sql: string, params: readonly unknown[] = []): Promise<T | null> =>
  (await db.query(sql, params)).rows[0] as T | undefined ?? null;

/* ---------------------------------------------------------------- catalog */

export const getDepartments = (db: Db) =>
  rows<DepartmentRow>(db, `SELECT * FROM catalog_get_departments_list()`);

export const getDepartmentDetails = (db: Db, departmentId: number) =>
  first<{ name: string; description: string | null }>(
    db,
    `SELECT * FROM catalog_get_department_details($1::integer)`,
    [departmentId],
  );

export const getCategoriesList = (db: Db, departmentId: number) =>
  rows<CategoryListRow>(db, `SELECT * FROM catalog_get_categories_list($1::integer)`, [departmentId]);

export const getCategoryDetails = (db: Db, categoryId: number) =>
  first<{ name: string; description: string | null }>(
    db,
    `SELECT * FROM catalog_get_category_details($1::integer)`,
    [categoryId],
  );

export const countProductsOnCatalog = (db: Db) =>
  first<{ n: number }>(db, `SELECT catalog_count_products_on_catalog()::integer AS n`);

export const getProductsOnCatalog = (db: Db, descriptionLength: number, perPage: number, page: number) =>
  rows<ProductListRow>(db, `SELECT * FROM catalog_get_products_on_catalog($1::integer, $2::integer, $3::integer)`, [
    descriptionLength,
    perPage,
    page,
  ]);

export const countProductsOnDepartment = (db: Db, departmentId: number) =>
  first<{ n: number }>(db, `SELECT catalog_count_products_on_department($1::integer)::integer AS n`, [departmentId]);

export const getProductsOnDepartment = (
  db: Db,
  departmentId: number,
  descriptionLength: number,
  perPage: number,
  page: number,
) =>
  rows<ProductListRow>(
    db,
    `SELECT * FROM catalog_get_products_on_department($1::integer, $2::integer, $3::integer, $4::integer)`,
    [departmentId, descriptionLength, perPage, page],
  );

export const countProductsInCategory = (db: Db, categoryId: number) =>
  first<{ n: number }>(db, `SELECT catalog_count_products_in_category($1::integer)::integer AS n`, [categoryId]);

export const getProductsInCategory = (
  db: Db,
  categoryId: number,
  descriptionLength: number,
  perPage: number,
  page: number,
) =>
  rows<ProductListRow>(
    db,
    `SELECT * FROM catalog_get_products_in_category($1::integer, $2::integer, $3::integer, $4::integer)`,
    [categoryId, descriptionLength, perPage, page],
  );

export const searchProducts = (
  db: Db,
  words: string[],
  allWords: string,
  descriptionLength: number,
  perPage: number,
  page: number,
) =>
  rows<ProductListRow>(
    db,
    `SELECT * FROM catalog_search($1::text[], $2::varchar(3), $3::integer, $4::integer, $5::integer)`,
    [words, allWords, descriptionLength, perPage, page],
  );

export const countSearchResults = (db: Db, words: string[], allWords: string) =>
  first<{ n: number }>(db, `SELECT catalog_count_search_result($1::text[], $2::varchar(3))::integer AS n`, [
    words,
    allWords,
  ]);

export const flagStopWords = (db: Db, words: string[]) =>
  rows<{ w: number }>(db, `SELECT * FROM catalog_flag_stop_words($1::text[])`, [words]);

export const getProductDetails = (db: Db, productId: number) =>
  first<ProductDetailsRow>(db, `SELECT * FROM catalog_get_product_details($1::integer)`, [productId]);

export const getProductReviews = (db: Db, productId: number) =>
  rows<ReviewRow>(db, `SELECT * FROM catalog_get_product_reviews($1::integer)`, [productId]);

export const getRecommendations = (db: Db, productId: number, descriptionLength: number) =>
  rows<RecommendationRow>(db, `SELECT * FROM catalog_get_recommendations($1::integer, $2::integer)`, [
    productId,
    descriptionLength,
  ]);

/** WRITE: creates a review row for a signed-in customer. */
export const createProductReview = (db: Db, customerId: number, productId: number, review: string, rating: number) =>
  db.query(`SELECT catalog_create_product_review($1::integer, $2::integer, $3::text, $4::smallint)`, [
    customerId,
    productId,
    review,
    rating,
  ]);

/* --------------------------------------------------------------- customer */

/** READ-ONLY: the only customer function safe to call during login. */
export const getLoginInfo = (db: Db, email: string) =>
  first<{ customer_id: number; password: string }>(
    db,
    `SELECT * FROM customer_get_login_info($1::varchar(100))`,
    [email],
  );

export const getCustomer = (db: Db, customerId: number) =>
  first<CustomerRow>(db, `SELECT * FROM customer_get_customer($1::integer)`, [customerId]);

export const getShippingRegions = (db: Db) =>
  rows<ShippingRegionRow>(db, `SELECT * FROM customer_get_shipping_regions()`);

export const getShippingForRegion = (db: Db, regionId: number) =>
  rows<ShippingRow>(
    db,
    `SELECT shipping_id, shipping_type, shipping_cost, shipping_region_id
       FROM shipping WHERE shipping_region_id = $1::integer ORDER BY shipping_cost`,
    [regionId],
  );

export const getTaxRates = (db: Db) =>
  rows<TaxRow>(db, `SELECT tax_id, tax_type, tax_percentage FROM tax ORDER BY tax_id`);

/** WRITE: registers a customer. */
export const addCustomer = (db: Db, name: string, email: string, passwordHash: string) =>
  first<{ customer_id: number }>(db, `SELECT customer_add($1::varchar, $2::varchar, $3::varchar)::integer AS customer_id`, [
    name,
    email,
    passwordHash,
  ]);

/** WRITE: profile/account update (name, email, password, day/eve/mob phone). */
export const updateAccount = (
  db: Db,
  customerId: number,
  name: string,
  email: string,
  password: string,
  dayPhone: string,
  evePhone: string,
  mobPhone: string,
) =>
  db.query(
    `SELECT customer_update_account($1::integer, $2::varchar, $3::varchar, $4::varchar, $5::varchar, $6::varchar, $7::varchar)`,
    [customerId, name, email, password, dayPhone, evePhone, mobPhone],
  );

/** WRITE: address + shipping region. */
export const updateAddress = (
  db: Db,
  customerId: number,
  address1: string,
  address2: string,
  city: string,
  region: string,
  postalCode: string,
  country: string,
  shippingRegionId: number,
) =>
  db.query(
    `SELECT customer_update_address($1::integer, $2::varchar, $3::varchar, $4::varchar, $5::varchar, $6::varchar, $7::varchar, $8::integer)`,
    [customerId, address1, address2, city, region, postalCode, country, shippingRegionId],
  );

export const getCustomersList = (db: Db) =>
  rows<{ customer_id: number; name: string }>(db, `SELECT * FROM customer_get_customers_list()`);

/* ------------------------------------------------------------------- cart */

const CART = `$1::char(128)`;

/** WRITE: adds a product (quantity 1) to the cart. */
export const addToCart = (db: Db, cartId: string, productId: number) =>
  db.query(`SELECT shopping_cart_add_product(${CART}, $2::integer)`, [cartId, productId]);

/** WRITE: sets quantities for the given product ids. */
export const updateCart = (db: Db, cartId: string, productIds: number[], quantities: number[]) =>
  db.query(`SELECT shopping_cart_update(${CART}, $2::integer[], $3::integer[])`, [cartId, productIds, quantities]);

/** WRITE: removes a product from the cart. */
export const removeFromCart = (db: Db, cartId: string, productId: number) =>
  db.query(`SELECT shopping_cart_remove_product(${CART}, $2::integer)`, [cartId, productId]);

/** WRITE: moves a product to the saved-for-later list. */
export const saveForLater = (db: Db, cartId: string, productId: number) =>
  db.query(`SELECT shopping_cart_save_product_for_later(${CART}, $2::integer)`, [cartId, productId]);

/** WRITE: moves a saved product back into the cart. */
export const moveToCart = (db: Db, cartId: string, productId: number) =>
  db.query(`SELECT shopping_cart_move_product_to_cart(${CART}, $2::integer)`, [cartId, productId]);

/** WRITE: empties the cart. */
export const emptyCart = (db: Db, cartId: string) =>
  db.query(`SELECT shopping_cart_empty(${CART})`, [cartId]);

export const getCartProducts = (db: Db, cartId: string, descriptionLength: number) =>
  rows<CartProductRow>(db, `SELECT * FROM shopping_cart_get_products(${CART}, $2::integer)`, [
    cartId,
    descriptionLength,
  ]);

export const getSavedProducts = (db: Db, cartId: string) =>
  rows<SavedProductRow>(db, `SELECT * FROM shopping_cart_get_saved_products(${CART})`, [cartId]);

/**
 * One-statement cart badge: item count plus database-computed total.
 * Kept as a single round trip because it runs on every page.
 */
export const cartSummary = (db: Db, cartId: string) =>
  first<{ items: number; total: string | null }>(
    db,
    `SELECT (SELECT COALESCE(SUM(quantity), 0) FROM shopping_cart WHERE cart_id = $1::char(128) AND buy_now)::integer AS items,
            shopping_cart_get_total_amount($1::char(128)) AS total`,
    [cartId],
  );

/** Reads only the stored digest (never displayed); used for session binding. */
export const getPasswordHash = (db: Db, customerId: number) =>
  first<{ password: string }>(db, `SELECT password FROM customer WHERE customer_id = $1::integer`, [customerId]);

/** Only used when SESSION_EPOCH_ENABLED is set (requires migration 001). */
export const sessionEpoch = (db: Db, customerId: number) =>
  first<{ session_epoch: number }>(db, `SELECT session_epoch FROM customer WHERE customer_id = $1::integer`, [
    customerId,
  ]);

export const getCartTotal = (db: Db, cartId: string) =>
  first<{ total: string | null }>(db, `SELECT shopping_cart_get_total_amount(${CART}) AS total`, [cartId]);

export const getCartRecommendations = (db: Db, cartId: string, descriptionLength: number) =>
  rows<RecommendationRow>(db, `SELECT * FROM shopping_cart_get_recommendations(${CART}, $2::integer)`, [
    cartId,
    descriptionLength,
  ]);

/**
 * WRITE (transactional by construction): creates the order and clears the cart
 * inside one plpgsql function, so a failure cannot leave a half-written order.
 */
export const createOrderFromCart = (
  db: Db,
  cartId: string,
  customerId: number,
  shippingId: number,
  taxId: number,
) =>
  first<{ order_id: number }>(
    db,
    `SELECT shopping_cart_create_order(${CART}, $2::integer, $3::integer, $4::integer)::integer AS order_id`,
    [cartId, customerId, shippingId, taxId],
  );

/* ----------------------------------------------------------------- orders */

export const getOrderInfo = (db: Db, orderId: number) =>
  first<OrderRow>(db, `SELECT * FROM orders_get_order_info($1::integer)`, [orderId]);

/**
 * Payment identifiers live in columns that the `orders` composite type does not
 * include, so they are read directly (read-only, parameterized).
 */
export const getOrderPaymentRefs = (db: Db, orderId: number) =>
  first<{ auth_code: string | null; reference: string | null }>(
    db,
    `SELECT auth_code, reference FROM orders WHERE order_id = $1::integer`,
    [orderId],
  );

export const getOrderShortDetails = (db: Db, orderId: number) =>
  first<OrderShortRow>(db, `SELECT * FROM orders_get_order_short_details($1::integer)`, [orderId]);

export const getOrderDetails = (db: Db, orderId: number) =>
  rows<OrderDetailRow>(db, `SELECT * FROM orders_get_order_details($1::integer)`, [orderId]);

export const getOrdersForCustomer = (db: Db, customerId: number) =>
  rows<OrderShortRow>(db, `SELECT * FROM orders_get_by_customer_id($1::integer)`, [customerId]);

export const getMostRecentOrders = (db: Db, limit: number) =>
  rows<OrderShortRow>(db, `SELECT * FROM orders_get_most_recent_orders($1::integer)`, [limit]);

export const getOrdersByStatus = (db: Db, status: number) =>
  rows<OrderShortRow>(db, `SELECT * FROM orders_get_orders_by_status($1::integer)`, [status]);

export const getAuditTrail = (db: Db, orderId: number) =>
  rows<{ audit_id: number; created_on: string; message: string; message_number: number }>(
    db,
    `SELECT * FROM orders_get_audit_trail($1::integer)`,
    [orderId],
  );

/** WRITE: records provider identifiers on the order (the payment audit point). */
export const setAuthCode = (db: Db, orderId: number, authCode: string, reference: string) =>
  db.query(`SELECT orders_set_auth_code($1::integer, $2::varchar, $3::varchar)`, [orderId, authCode, reference]);

/** WRITE: status transition (0..8). */
export const updateOrderStatus = (db: Db, orderId: number, status: number) =>
  db.query(`SELECT orders_update_status($1::integer, $2::integer)`, [orderId, status]);

export const markShipped = (db: Db, orderId: number) =>
  db.query(`SELECT orders_set_date_shipped($1::integer)`, [orderId]);

export const createAudit = (db: Db, orderId: number, message: string, messageNumber: number) =>
  db.query(`SELECT orders_create_audit($1::integer, $2::text, $3::integer)`, [orderId, message, messageNumber]);

/** WRITE: admin order update (status, comments, customer name, shipping address, email). */
export const updateOrder = (
  db: Db,
  orderId: number,
  status: number,
  comments: string,
  customerName: string,
  shippingAddress: string,
  customerEmail: string,
) =>
  db.query(
    `SELECT orders_update_order($1::integer, $2::integer, $3::varchar, $4::varchar, $5::varchar, $6::varchar)`,
    [orderId, status, comments, customerName, shippingAddress, customerEmail],
  );

/* ------------------------------------------------------------ admin catalog */

/** READ-ONLY: every product, including hidden ones (no function exposes this). */
export const listAllProducts = (db: Db, limit: number, offset: number) =>
  rows<{ product_id: number; name: string; price: string; discounted_price: string; display: number }>(
    db,
    `SELECT product_id, name, price, discounted_price, display
       FROM product ORDER BY product_id LIMIT $1::integer OFFSET $2::integer`,
    [limit, offset],
  );

export const getProductInfo = (db: Db, productId: number) =>
  first<{ name: string; image: string | null; thumbnail: string | null; display: number }>(
    db,
    `SELECT * FROM catalog_get_product_info($1::integer)`,
    [productId],
  );

export const getCategoriesForProduct = (db: Db, productId: number) =>
  rows<{ category_id: number; department_id: number; name: string }>(
    db,
    `SELECT * FROM catalog_get_categories_for_product($1::integer)`,
    [productId],
  );

export const getCategoryProducts = (db: Db, categoryId: number) =>
  rows<{ product_id: number; name: string; price: string; discounted_price: string | null }>(
    db,
    `SELECT * FROM catalog_get_category_products($1::integer)`,
    [categoryId],
  );

export const getDepartmentCategories = (db: Db, departmentId: number) =>
  rows<{ category_id: number; name: string; description: string | null }>(
    db,
    `SELECT * FROM catalog_get_department_categories($1::integer)`,
    [departmentId],
  );

export const getAllCategories = (db: Db) =>
  rows<{ category_id: number; name: string; description: string | null }>(
    db,
    `SELECT * FROM catalog_get_categories()`,
  );

/** WRITE: admin catalog mutations. */
export const addDepartment = (db: Db, name: string, description: string) =>
  db.query(`SELECT catalog_add_department($1::varchar, $2::varchar)`, [name, description]);

export const updateDepartment = (db: Db, id: number, name: string, description: string) =>
  db.query(`SELECT catalog_update_department($1::integer, $2::varchar, $3::varchar)`, [id, name, description]);

export const deleteDepartment = (db: Db, id: number) =>
  db.query(`SELECT catalog_delete_department($1::integer)`, [id]);

export const addCategory = (db: Db, departmentId: number, name: string, description: string) =>
  db.query(`SELECT catalog_add_category($1::integer, $2::varchar, $3::varchar)`, [departmentId, name, description]);

export const updateCategory = (db: Db, id: number, name: string, description: string) =>
  db.query(`SELECT catalog_update_category($1::integer, $2::varchar, $3::varchar)`, [id, name, description]);

export const deleteCategory = (db: Db, id: number) =>
  db.query(`SELECT catalog_delete_category($1::integer)`, [id]);

export const updateProduct = (db: Db, id: number, name: string, description: string, price: string, discounted: string) =>
  db.query(`SELECT catalog_update_product($1::integer, $2::varchar, $3::varchar, $4::numeric, $5::numeric)`, [
    id,
    name,
    description,
    price,
    discounted,
  ]);

export const addProductToCategory = (db: Db, categoryId: number, name: string, description: string, price: string) =>
  db.query(`SELECT catalog_add_product_to_category($1::integer, $2::varchar, $3::varchar, $4::numeric)`, [
    categoryId,
    name,
    description,
    price,
  ]);

export const deleteProduct = (db: Db, id: number) =>
  db.query(`SELECT catalog_delete_product($1::integer)`, [id]);

export const assignProductToCategory = (db: Db, productId: number, categoryId: number) =>
  db.query(`SELECT catalog_assign_product_to_category($1::integer, $2::integer)`, [productId, categoryId]);

export const moveProductToCategory = (db: Db, productId: number, sourceCategoryId: number, targetCategoryId: number) =>
  db.query(`SELECT catalog_move_product_to_category($1::integer, $2::integer, $3::integer)`, [
    productId,
    sourceCategoryId,
    targetCategoryId,
  ]);

export const removeProductFromCategory = (db: Db, productId: number, categoryId: number) =>
  db.query(`SELECT catalog_remove_product_from_category($1::integer, $2::integer)`, [productId, categoryId]);

export const setProductDisplayOption = (db: Db, productId: number, display: number) =>
  db.query(`SELECT catalog_set_product_display_option($1::integer, $2::smallint)`, [productId, display]);
