# Functional inventory: PHP reference → Worker rewrite

The PHP application (`src/hatshop/`) is the reference. Its 29 Smarty templates and
22 plugin functions map to the routes below. Business rules live in the database
functions named in the last column.

## Storefront

| PHP template / plugin | Route | Database functions used |
| --- | --- | --- |
| `index.tpl` + `first_page_contents.tpl` (`load_products_list`) | `GET /` | `catalog_count_products_on_catalog`, `catalog_get_products_on_catalog`, `catalog_get_departments_list` |
| `departments_list.tpl` (`load_departments_list`) | every page nav | `catalog_get_departments_list` |
| `department.tpl` (`load_department`) | `GET /departments/:id` | `catalog_get_department_details`, `catalog_get_categories_list`, `catalog_count_products_on_department`, `catalog_get_products_on_department` |
| `categories_list.tpl` (`load_categories_list`) | department page | `catalog_get_categories_list` |
| `products_list.tpl` | department/category/search | as above |
| `product.tpl` (`load_product`) | `GET /products/:id` | `catalog_get_product_details`, `catalog_get_product_reviews`, `catalog_get_recommendations` |
| `search_box.tpl`, `search_results.tpl` (`load_search_box`) | `GET /search?q=` | `catalog_count_search_result`, `catalog_search` (supports all-words) |
| `cart_summary.tpl` (`load_cart_summary`) | header badge | one combined statement (`cartSummary`) |
| `cart_details.tpl` (`load_cart_details`) | `GET /cart` | `shopping_cart_get_products`, `shopping_cart_get_saved_products`, `shopping_cart_get_total_amount` |
| cart actions | `POST /cart` | `shopping_cart_add_product`, `_update`, `_remove_product`, `_save_product_for_later`, `_move_product_to_cart` |
| recommendations (ch. 10) | product page | `catalog_get_recommendations`, `shopping_cart_get_recommendations` |
| reviews (ch. 16) | product page + `POST /products/:id/reviews` | `catalog_get_product_reviews`, `catalog_create_product_review` |

## Customer

| PHP template | Route | Database functions |
| --- | --- | --- |
| `customer_login.tpl` (`load_customer_login`) | `GET/POST /login` | `customer_get_login_info` |
| — (registration) | `GET/POST /register` | `customer_add` |
| `customer_logged.tpl` | header | session claim only |
| `customer_details.tpl` | `GET/POST /account` | `customer_get_customer`, `customer_update_account` |
| `customer_address.tpl` | `POST /account/address` | `customer_update_address`, `customer_get_shipping_regions` |
| `customer_credit_card.tpl` | **not ported** | `customer_update_credit_card` — storing card data is refused by design |
| order history (ch. 9/12) | `GET /orders`, `GET /orders/:id` | `orders_get_by_customer_id`, `orders_get_order_info`, `orders_get_order_details` |
| `checkout_info.tpl`, `checkout_not_logged.tpl` (`load_checkout_info`) | `GET/POST /checkout` | `shopping_cart_get_total_amount`, `shopping_cart_create_order`, `orders_set_auth_code`, `orders_update_status`, `orders_create_audit` |

## Administration

| PHP template / plugin | Route | Database functions |
| --- | --- | --- |
| `admin_login.tpl` (`load_admin_login`) | `GET/POST /admin/login` | none (secret-based credentials) |
| `admin_menu.tpl` | every admin page | — |
| `admin_departments.tpl` (`load_admin_departments`) | `GET /admin`, `GET /admin/catalog` | `catalog_get_departments_list`, `catalog_add_department`, `catalog_update_department`, `catalog_delete_department` |
| `admin_categories.tpl` (`load_admin_categories`) | `GET /admin/catalog` | `catalog_get_categories`, `catalog_get_department_categories`, add/update/delete category |
| `admin_products.tpl` (`load_admin_products`) | `GET /admin/products` | direct read of `product` (all rows, incl. hidden) |
| `admin_product.tpl` (`load_admin_product`) | `GET /admin/products/:id` | `catalog_get_product_info`, `catalog_get_product_details`, `catalog_get_categories_for_product`, `catalog_update_product`, `catalog_set_product_display_option`, `catalog_set_image`, `catalog_assign_product_to_category`, `catalog_delete_product` |
| `admin_orders.tpl` (`load_admin_orders`) | `GET /admin/orders` | `orders_get_most_recent_orders` |
| `admin_order_details.tpl` (`load_admin_order_details`) | `GET /admin/orders/:id` | `orders_get_order_info`, `orders_get_order_details`, `orders_get_audit_trail`, `orders_update_status`, `orders_update_order`, `orders_set_date_shipped` |
| — | `GET /admin/customers`, `/admin/customers/:id` | `customer_get_customers_list`, `customer_get_customer` |

## Deliberate differences from the PHP application

| Area | PHP behaviour | Rewrite |
| --- | --- | --- |
| Rendering | Smarty templates compiled to disk | server-rendered HTML strings; the filesystem is read-only |
| Sessions | PHP session files | signed cookies (`architecture.md`) |
| Catalog admin images | uploads via `move_uploaded_file` | image *reference* only; uploads need object storage (approval gate) |
| Emails | `@mail()` in the order pipeline | not sent; audit rows recorded |
| Admin credentials | hardcoded in `Config.php` | `ADMIN_USERNAME` + SHA-256 digest secret, constant-time compare |
| Credit cards | encrypted and stored in `customer.credit_card` | never stored or displayed |
| Feature flags | `HATSHOP_CHAPTER_LEVEL` | fixed feature set (all chapters up to 12 are implemented) |
| Recommend/search paging | Smarty + plugin globals | explicit page/offset parameters |

## Not preserved (and why)

- PayPal redirect flow (ch. 6, `paypal_*` config): the useful checkout path is the
  Authorize.Net pipeline; a second payment provider would add a second state
  machine with no additional business value here.
- Amazon web services integration (ch. 17): the legacy XML service it calls is
  retired; `Amazon.php` remains in the PHP tree for reference.
- Multi-chapter `HATSHOP_CHAPTER_LEVEL` switching: the rewrite targets the full
  feature set (chapters 1–12 plus reviews), which is what production runs.
