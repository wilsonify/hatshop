# Database inventory (live)

Generated from `docs/schema-snapshot.json` by `scripts/build-schema-doc.mjs`.
The snapshot was captured read-only with `scripts/introspect.mjs` against
`neondb` on `127.0.0.1/32`.

| Fact | Value |
| --- | --- |
| Server version | PostgreSQL 17.12 (4d8000a) on aarch64-unknown-linux-gnu, compiled by gcc (Debian 12.2.0-14+deb12u1) 12.2.0, 64-bit |
| Database | `neondb` |
| Role | `neondb_owner` |
| Tables | 13 |
| Public functions | 75 (40 read, 31 write, 4 destructive) |
| Composite types | 32 |
| Installed extensions | plpgsql 1.0 |

## Consequences for the application

1. **The database is the business-logic engine.** 75 plpgsql
   functions implement catalog, cart, order and customer behaviour. The Worker
   calls them; it does not reimplement them.
2. **`pgcrypto` is not installed**, so password hashing cannot be delegated to
   PostgreSQL without a new extension (an approval gate, not applied).
3. **`customer.password` is `VARCHAR(50)`** and the only existing row stores
   **40 hex characters**, i.e. a legacy SHA-1 digest
   (0 SHA-256-shaped values, 0
   modern `$separated` digests). See `payment-states.md` and `architecture.md`
   for what this means for sign-in and for the reviewed migration 002.
4. **`orders` has no `customer_id`** in its composite type; ownership is proven by
   looking an order up in the customer's own order list (`services/orders.ts`).
5. **`orders` exposes `auth_code` and `reference` only as columns**, not through
   `orders_get_order_info`; they are read directly when needed.

## Tables

| Table | Rows | Columns |
| --- | --- | --- |
| `audit` | 0 | audit_id integer, order_id integer, created_on timestamp without time zone, message text, message_number integer |
| `category` | 7 | category_id integer, department_id integer, name character varying(50), description character varying(1000) |
| `customer` | 1 | customer_id integer, name character varying(50), email character varying(100), password character varying(50), credit_card text, address_1 character varying(100), address_2 character varying(100), city character varying(100), region character varying(100), postal_code character varying(100), country character varying(100), shipping_region_id integer, day_phone character varying(100), eve_phone character varying(100), mob_phone character varying(100) |
| `department` | 3 | department_id integer, name character varying(50), description character varying(1000) |
| `order_detail` | 0 | order_id integer, product_id integer, product_name character varying(50), quantity integer, unit_cost numeric |
| `orders` | 0 | order_id integer, total_amount numeric, created_on timestamp without time zone, shipped_on timestamp without time zone, status integer, comments character varying(255), customer_name character varying(50), shipping_address character varying(255), customer_email character varying(50) |
| `product` | 45 | product_id integer, name character varying(50), description character varying(1000), price numeric, discounted_price numeric, image character varying(150), thumbnail character varying(150), display smallint, search_vector tsvector |
| `product_category` | 47 | product_id integer, category_id integer |
| `review` | 0 | review_id integer, customer_id integer, product_id integer, review text, rating smallint, created_on timestamp without time zone |
| `shipping` | 7 | shipping_id integer, shipping_type character varying(100), shipping_cost numeric, shipping_region_id integer |
| `shipping_region` | 4 | shipping_region_id integer, shipping_region character varying(100) |
| `shopping_cart` | 0 | cart_id character(128), product_id integer, quantity integer, buy_now boolean, added_on timestamp without time zone |
| `tax` | 2 | tax_id integer, tax_type character varying(100), tax_percentage numeric |

## Functions by effect

`read` = no data changed by this application · `write` = inserts/updates ·
`destructive` = deletes rows.

| Function | Returns | Effect |
| --- | --- | --- |
| `catalog_count_products_in_category(integer)` | `integer` | read |
| `catalog_count_products_on_catalog()` | `integer` | read |
| `catalog_count_products_on_department(integer)` | `integer` | read |
| `catalog_count_search_result(text[], character varying)` | `integer` | read |
| `catalog_flag_stop_words(text[])` | `SETOF smallint` | read |
| `catalog_get_categories()` | `SETOF department_category` | read |
| `catalog_get_categories_for_product(integer)` | `SETOF product_category_details` | read |
| `catalog_get_categories_list(integer)` | `SETOF category_list` | read |
| `catalog_get_category_details(integer)` | `category_details` | read |
| `catalog_get_category_products(integer)` | `SETOF category_product` | read |
| `catalog_get_department_categories(integer)` | `SETOF department_category` | read |
| `catalog_get_department_details(integer)` | `department_details` | read |
| `catalog_get_departments()` | `SETOF department` | read |
| `catalog_get_departments_list()` | `SETOF department_list` | read |
| `catalog_get_product_details(integer)` | `product_details` | read |
| `catalog_get_product_info(integer)` | `product_info` | read |
| `catalog_get_product_reviews(integer)` | `SETOF review_info` | read |
| `catalog_get_products_in_category(integer, integer, integer, integer)` | `SETOF product_list` | read |
| `catalog_get_products_on_catalog(integer, integer, integer)` | `SETOF product_list` | read |
| `catalog_get_products_on_department(integer, integer, integer, integer)` | `SETOF product_list` | read |
| `catalog_get_recommendations(integer, integer)` | `SETOF product_recommendation` | read |
| `catalog_search(text[], character varying, integer, integer, integer)` | `SETOF product_list` | read |
| `customer_get_customer(integer)` | `customer` | read |
| `customer_get_customers_list()` | `SETOF customer_list` | read |
| `customer_get_login_info(character varying)` | `customer_login_info` | read |
| `customer_get_shipping_regions()` | `SETOF shipping_region` | read |
| `orders_get_audit_trail(integer)` | `SETOF audit` | read |
| `orders_get_by_customer_id(integer)` | `SETOF order_short_details` | read |
| `orders_get_most_recent_orders(integer)` | `SETOF order_short_details` | read |
| `orders_get_order_details(integer)` | `SETOF order_details` | read |
| `orders_get_order_info(integer)` | `orders` | read |
| `orders_get_order_short_details(integer)` | `order_short_details` | read |
| `orders_get_orders_between_dates(timestamp without time zone, timestamp without time zone)` | `SETOF order_short_details` | read |
| `orders_get_orders_by_status(integer)` | `SETOF order_short_details` | read |
| `orders_get_shipping_info(integer)` | `SETOF shipping` | read |
| `shopping_cart_count_old_carts(integer)` | `integer` | read |
| `shopping_cart_get_products(character, integer)` | `SETOF cart_product` | read |
| `shopping_cart_get_recommendations(character, integer)` | `SETOF product_recommendation` | read |
| `shopping_cart_get_saved_products(character)` | `SETOF cart_saved_product` | read |
| `shopping_cart_get_total_amount(character)` | `numeric` | read |
| `catalog_add_category(integer, character varying, character varying)` | `void` | write |
| `catalog_add_department(character varying, character varying)` | `void` | write |
| `catalog_add_product_to_category(integer, character varying, character varying, numeric)` | `void` | write |
| `catalog_assign_product_to_category(integer, integer)` | `void` | write |
| `catalog_create_product_review(integer, integer, text, smallint)` | `void` | write |
| `catalog_move_product_to_category(integer, integer, integer)` | `void` | write |
| `catalog_remove_product_from_category(integer, integer)` | `smallint` | write |
| `catalog_set_image(integer, character varying)` | `void` | write |
| `catalog_set_product_display_option(integer, smallint)` | `void` | write |
| `catalog_set_thumbnail(integer, character varying)` | `void` | write |
| `catalog_update_category(integer, character varying, character varying)` | `void` | write |
| `catalog_update_department(integer, character varying, character varying)` | `void` | write |
| `catalog_update_product(integer, character varying, character varying, numeric, numeric)` | `void` | write |
| `customer_add(character varying, character varying, character varying)` | `integer` | write |
| `customer_update_account(integer, character varying, character varying, character varying, character varying, character varying, character varying)` | `void` | write |
| `customer_update_address(integer, character varying, character varying, character varying, character varying, character varying, character varying, integer)` | `void` | write |
| `customer_update_credit_card(integer, text)` | `void` | write |
| `orders_create_audit(integer, text, integer)` | `void` | write |
| `orders_set_auth_code(integer, character varying, character varying)` | `void` | write |
| `orders_set_date_shipped(integer)` | `void` | write |
| `orders_update_order(integer, integer, character varying, character varying, character varying, character varying)` | `void` | write |
| `orders_update_order(integer, integer, character varying, character varying, character varying)` | `void` | write |
| `orders_update_status(integer, integer)` | `void` | write |
| `shopping_cart_add_product(character, integer)` | `void` | write |
| `shopping_cart_create_order(character, integer, integer, integer)` | `integer` | write |
| `shopping_cart_create_order(character)` | `integer` | write |
| `shopping_cart_empty(character)` | `void` | write |
| `shopping_cart_move_product_to_cart(character, integer)` | `void` | write |
| `shopping_cart_remove_product(character, integer)` | `void` | write |
| `shopping_cart_save_product_for_later(character, integer)` | `void` | write |
| `shopping_cart_update(character, integer[], integer[])` | `void` | write |
| `catalog_delete_category(integer)` | `smallint` | destructive |
| `catalog_delete_department(integer)` | `smallint` | destructive |
| `catalog_delete_product(integer)` | `void` | destructive |
| `shopping_cart_delete_old_carts(integer)` | `void` | destructive |

## Read-only test allowlist

Only these functions are exercised by tests that run against the live database:
`catalog_get_*`, `catalog_count_*`, `catalog_search`, `catalog_flag_stop_words`,
`customer_get_login_info`, `orders_get_*`, `shopping_cart_get_*`. Anything that
writes (registration, cart mutations, reviews, orders, admin actions) requires an
isolated database branch.

## Known database quirks (preserved, not "fixed")

- `shopping_cart_count_old_carts(days)` ignores its argument: the body hard-codes
  `'1' DAYS`. The Worker does not use it.
- `catalog_*_delete_*` functions do not cascade; they return a smallint signalling
  whether the row could be removed. The admin UI reports the outcome and never
  force-deletes.
- Several "get" functions are `VOLATILE` because they are plpgsql; volatility does
  not imply writes for this set.
