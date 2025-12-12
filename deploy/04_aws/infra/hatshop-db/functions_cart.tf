# PostgreSQL Functions for HatShop Database - Shopping Cart Functions
# Uses cyrilgdn/postgresql provider

# Add product to shopping cart
resource "postgresql_script" "func_shopping_cart_add_product" {
  count = var.create_functions ? 1 : 0
  name  = "func_shopping_cart_add_product"
  depends_on = [postgresql_script.table_shopping_cart]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION shopping_cart_add_product(CHAR(128), INTEGER)
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inCartId        ALIAS FOR $1;
        inProductId     ALIAS FOR $2;
        productQuantity INTEGER;
      BEGIN
        SELECT INTO productQuantity
                    quantity
        FROM   shopping_cart
        WHERE  cart_id = inCartId AND product_id = inProductId;
        IF productQuantity IS NULL THEN
          INSERT INTO shopping_cart(cart_id, product_id, quantity, added_on)
                 VALUES (inCartId, inProductId , 1, NOW());
        ELSE
          UPDATE shopping_cart
          SET    quantity = quantity + 1, buy_now = true
          WHERE  cart_id = inCartId AND product_id = inProductId;
        END IF;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS shopping_cart_add_product(CHAR(128), INTEGER) CASCADE;"
}

# Update shopping cart quantity
resource "postgresql_script" "func_shopping_cart_update" {
  count = var.create_functions ? 1 : 0
  name  = "func_shopping_cart_update"
  depends_on = [postgresql_script.table_shopping_cart]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION shopping_cart_update(CHAR(128), INTEGER, INTEGER)
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inCartId    ALIAS FOR $1;
        inProductId ALIAS FOR $2;
        inQuantity  ALIAS FOR $3;
      BEGIN
        IF inQuantity > 0 THEN
          UPDATE shopping_cart
          SET    quantity = inQuantity, buy_now = true, added_on = NOW()
          WHERE  cart_id = inCartId AND product_id = inProductId;
        ELSE
          PERFORM shopping_cart_remove_product(inCartId, inProductId);
        END IF;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS shopping_cart_update(CHAR(128), INTEGER, INTEGER) CASCADE;"
}

# Remove product from shopping cart
resource "postgresql_script" "func_shopping_cart_remove_product" {
  count = var.create_functions ? 1 : 0
  name  = "func_shopping_cart_remove_product"
  depends_on = [postgresql_script.table_shopping_cart]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION shopping_cart_remove_product(CHAR(128), INTEGER)
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inCartId    ALIAS FOR $1;
        inProductId ALIAS FOR $2;
      BEGIN
        DELETE FROM shopping_cart
        WHERE  cart_id = inCartId AND product_id = inProductId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS shopping_cart_remove_product(CHAR(128), INTEGER) CASCADE;"
}

# Get products from shopping cart
resource "postgresql_script" "func_shopping_cart_get_products" {
  count = var.create_functions ? 1 : 0
  name  = "func_shopping_cart_get_products"
  depends_on = [
    postgresql_script.table_shopping_cart,
    postgresql_script.table_product,
    postgresql_script.type_cart_product
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION shopping_cart_get_products(CHAR(128))
    RETURNS SETOF cart_product LANGUAGE plpgsql AS $$
      DECLARE
        inCartId ALIAS FOR $1;
        outCartProductRow cart_product;
      BEGIN
        FOR outCartProductRow IN
          SELECT     p.product_id, p.name,
                     COALESCE(NULLIF(p.discounted_price, 0), p.price) AS price,
                     sc.quantity,
                     COALESCE(NULLIF(p.discounted_price, 0), p.price) * sc.quantity
                       AS subtotal
          FROM       shopping_cart sc
          INNER JOIN product p
                       ON sc.product_id = p.product_id
          WHERE      sc.cart_id = inCartId AND sc.buy_now
        LOOP
          RETURN NEXT outCartProductRow;
        END LOOP;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS shopping_cart_get_products(CHAR(128)) CASCADE;"
}

# Get saved products from shopping cart
resource "postgresql_script" "func_shopping_cart_get_saved_products" {
  count = var.create_functions ? 1 : 0
  name  = "func_shopping_cart_get_saved_products"
  depends_on = [
    postgresql_script.table_shopping_cart,
    postgresql_script.table_product,
    postgresql_script.type_cart_saved_product
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION shopping_cart_get_saved_products(CHAR(128))
    RETURNS SETOF cart_saved_product LANGUAGE plpgsql AS $$
      DECLARE
        inCartId ALIAS FOR $1;
        outCartSavedProductRow cart_saved_product;
      BEGIN
        FOR outCartSavedProductRow IN
          SELECT     p.product_id, p.name,
                     COALESCE(NULLIF(p.discounted_price, 0), p.price) AS price
          FROM       shopping_cart sc
          INNER JOIN product p
                       ON sc.product_id = p.product_id
          WHERE      sc.cart_id = inCartId AND NOT sc.buy_now
        LOOP
          RETURN NEXT outCartSavedProductRow;
        END LOOP;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS shopping_cart_get_saved_products(CHAR(128)) CASCADE;"
}

# Get total amount from shopping cart
resource "postgresql_script" "func_shopping_cart_get_total_amount" {
  count = var.create_functions ? 1 : 0
  name  = "func_shopping_cart_get_total_amount"
  depends_on = [
    postgresql_script.table_shopping_cart,
    postgresql_script.table_product
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION shopping_cart_get_total_amount(CHAR(128))
    RETURNS NUMERIC(10, 2) LANGUAGE plpgsql AS $$
      DECLARE
        inCartId ALIAS FOR $1;
        outTotalAmount NUMERIC(10, 2);
      BEGIN
        SELECT     INTO outTotalAmount
                   SUM(COALESCE(NULLIF(p.discounted_price, 0), p.price) *
                       sc.quantity)
        FROM       shopping_cart sc
        INNER JOIN product p
                     ON sc.product_id = p.product_id
        WHERE      sc.cart_id = inCartId AND sc.buy_now;
        RETURN outTotalAmount;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS shopping_cart_get_total_amount(CHAR(128)) CASCADE;"
}

# Save product for later
resource "postgresql_script" "func_shopping_cart_save_product_for_later" {
  count = var.create_functions ? 1 : 0
  name  = "func_shopping_cart_save_product_for_later"
  depends_on = [postgresql_script.table_shopping_cart]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION shopping_cart_save_product_for_later(CHAR(128), INTEGER)
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inCartId    ALIAS FOR $1;
        inProductId ALIAS FOR $2;
      BEGIN
        UPDATE shopping_cart
        SET    buy_now = false, added_on = NOW()
        WHERE  cart_id = inCartId AND product_id = inProductId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS shopping_cart_save_product_for_later(CHAR(128), INTEGER) CASCADE;"
}

# Move product to cart
resource "postgresql_script" "func_shopping_cart_move_product_to_cart" {
  count = var.create_functions ? 1 : 0
  name  = "func_shopping_cart_move_product_to_cart"
  depends_on = [postgresql_script.table_shopping_cart]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION shopping_cart_move_product_to_cart(CHAR(128), INTEGER)
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inCartId    ALIAS FOR $1;
        inProductId ALIAS FOR $2;
      BEGIN
        UPDATE shopping_cart
        SET    buy_now = true, added_on = NOW()
        WHERE  cart_id = inCartId AND product_id = inProductId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS shopping_cart_move_product_to_cart(CHAR(128), INTEGER) CASCADE;"
}

# Count old carts
resource "postgresql_script" "func_shopping_cart_count_old_carts" {
  count = var.create_functions ? 1 : 0
  name  = "func_shopping_cart_count_old_carts"
  depends_on = [postgresql_script.table_shopping_cart]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION shopping_cart_count_old_carts(INTEGER)
    RETURNS INTEGER LANGUAGE plpgsql AS $$
      DECLARE
        inDays ALIAS FOR $1;
        outOldShoppingCartsCount INTEGER;
      BEGIN
        SELECT INTO outOldShoppingCartsCount
                    count(*)
        FROM   (SELECT   cart_id
                FROM     shopping_cart
                GROUP BY cart_id
                HAVING   ((NOW() - INTERVAL '1 day' * inDays) >= max(added_on)))
                AS old_carts;
        RETURN outOldShoppingCartsCount;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS shopping_cart_count_old_carts(INTEGER) CASCADE;"
}

# Delete old carts
resource "postgresql_script" "func_shopping_cart_delete_old_carts" {
  count = var.create_functions ? 1 : 0
  name  = "func_shopping_cart_delete_old_carts"
  depends_on = [postgresql_script.table_shopping_cart]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION shopping_cart_delete_old_carts(INTEGER)
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inDays ALIAS FOR $1;
      BEGIN
        DELETE FROM shopping_cart
        WHERE  cart_id IN
               (SELECT   cart_id
                FROM     shopping_cart
                GROUP BY cart_id
                HAVING   ((NOW() - INTERVAL '1 day' * inDays) >= max(added_on)));
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS shopping_cart_delete_old_carts(INTEGER) CASCADE;"
}

# Empty shopping cart
resource "postgresql_script" "func_shopping_cart_empty" {
  count = var.create_functions ? 1 : 0
  name  = "func_shopping_cart_empty"
  depends_on = [postgresql_script.table_shopping_cart]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION shopping_cart_empty(CHAR(128))
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inCartId ALIAS FOR $1;
      BEGIN
        DELETE FROM shopping_cart WHERE cart_id = inCartId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS shopping_cart_empty(CHAR(128)) CASCADE;"
}

# Create order from shopping cart
resource "postgresql_script" "func_shopping_cart_create_order" {
  count = var.create_functions ? 1 : 0
  name  = "func_shopping_cart_create_order"
  depends_on = [
    postgresql_script.table_shopping_cart,
    postgresql_script.table_orders,
    postgresql_script.table_order_detail,
    postgresql_script.table_product,
    postgresql_script.type_cart_product,
    postgresql_script.func_shopping_cart_empty
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION shopping_cart_create_order(CHAR(128), INTEGER, INTEGER, INTEGER)
    RETURNS INTEGER LANGUAGE plpgsql AS $$
      DECLARE
        inCartId     ALIAS FOR $1;
        inCustomerId ALIAS FOR $2;
        inShippingId ALIAS FOR $3;
        inTaxId      ALIAS FOR $4;
        outOrderId INTEGER;
        cartItem cart_product;
        orderTotalAmount NUMERIC(10, 2);
      BEGIN
        INSERT INTO orders (created_on, customer_id, shipping_id, tax_id)
               VALUES (NOW(), inCustomerId, inShippingId, inTaxId);
        SELECT INTO outOrderId
               currval('orders_order_id_seq');
        orderTotalAmount := 0;
        FOR cartItem IN
          SELECT     p.product_id, p.name,
                     COALESCE(NULLIF(p.discounted_price, 0), p.price) AS price,
                     sc.quantity,
                     COALESCE(NULLIF(p.discounted_price, 0), p.price) * sc.quantity
                       AS subtotal
          FROM       shopping_cart sc
          INNER JOIN product p
                       ON sc.product_id = p.product_id
          WHERE      sc.cart_id = inCartId AND sc.buy_now
        LOOP
          INSERT INTO order_detail (order_id, product_id, product_name,
                                    quantity, unit_cost)
                 VALUES (outOrderId, cartItem.product_id, cartItem.name,
                         cartItem.quantity, cartItem.price);
          orderTotalAmount := orderTotalAmount + cartItem.subtotal;
        END LOOP;
        UPDATE orders
        SET    total_amount = orderTotalAmount
        WHERE  order_id = outOrderId;
        PERFORM shopping_cart_empty(inCartId);
        RETURN outOrderId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS shopping_cart_create_order(CHAR(128), INTEGER, INTEGER, INTEGER) CASCADE;"
}

# Get recommendations based on shopping cart
resource "postgresql_script" "func_shopping_cart_get_recommendations" {
  count = var.create_functions ? 1 : 0
  name  = "func_shopping_cart_get_recommendations"
  depends_on = [
    postgresql_script.table_shopping_cart,
    postgresql_script.table_order_detail,
    postgresql_script.table_product,
    postgresql_script.type_product_recommendation
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION shopping_cart_get_recommendations(CHAR(128), INTEGER)
    RETURNS SETOF product_recommendation LANGUAGE plpgsql AS $$
      DECLARE
        inCartId        ALIAS FOR $1;
        inShortProductDescriptionLength ALIAS FOR $2;
        outProductRecommendationRow product_recommendation;
      BEGIN
        FOR outProductRecommendationRow IN
          SELECT   od.product_id, od.product_name, p.description
          FROM     order_detail od
          INNER JOIN product p
                       ON od.product_id = p.product_id
          WHERE    od.order_id IN (
                     SELECT DISTINCT od1.order_id
                     FROM   order_detail od1
                     WHERE  od1.product_id IN (
                              SELECT product_id
                              FROM   shopping_cart
                              WHERE  cart_id = inCartId AND buy_now))
          AND      od.product_id NOT IN (
                     SELECT product_id
                     FROM   shopping_cart
                     WHERE  cart_id = inCartId AND buy_now)
          GROUP BY od.product_id, od.product_name, p.description
          ORDER BY count(od.product_id) DESC
          LIMIT    5
        LOOP
          IF char_length(outProductRecommendationRow.description) >
             inShortProductDescriptionLength THEN
            outProductRecommendationRow.description :=
              substring(outProductRecommendationRow.description, 1,
                        inShortProductDescriptionLength) || '...';
          END IF;
          RETURN NEXT outProductRecommendationRow;
        END LOOP;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS shopping_cart_get_recommendations(CHAR(128), INTEGER) CASCADE;"
}
