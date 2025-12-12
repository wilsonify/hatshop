# PostgreSQL Functions for HatShop Database - Orders Functions
# Uses cyrilgdn/postgresql provider

# Get most recent orders
resource "postgresql_script" "func_orders_get_most_recent_orders" {
  count = var.create_functions ? 1 : 0
  name  = "func_orders_get_most_recent_orders"
  depends_on = [
    postgresql_script.table_orders,
    postgresql_script.table_customer,
    postgresql_script.type_order_short_details
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION orders_get_most_recent_orders(INTEGER)
    RETURNS SETOF order_short_details LANGUAGE plpgsql AS $$
      DECLARE
        inCount ALIAS FOR $1;
        outOrderShortDetailsRow order_short_details;
      BEGIN
        FOR outOrderShortDetailsRow IN
          SELECT   o.order_id, o.total_amount, o.created_on,
                   o.shipped_on, o.status, c.name
          FROM     orders o
          INNER JOIN customer c
                       ON o.customer_id = c.customer_id
          ORDER BY o.created_on DESC
          LIMIT    inCount
        LOOP
          RETURN NEXT outOrderShortDetailsRow;
        END LOOP;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS orders_get_most_recent_orders(INTEGER) CASCADE;"
}

# Get orders between dates
resource "postgresql_script" "func_orders_get_orders_between_dates" {
  count = var.create_functions ? 1 : 0
  name  = "func_orders_get_orders_between_dates"
  depends_on = [
    postgresql_script.table_orders,
    postgresql_script.table_customer,
    postgresql_script.type_order_short_details
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION orders_get_orders_between_dates(TIMESTAMP, TIMESTAMP)
    RETURNS SETOF order_short_details LANGUAGE plpgsql AS $$
      DECLARE
        inStartDate ALIAS FOR $1;
        inEndDate   ALIAS FOR $2;
        outOrderShortDetailsRow order_short_details;
      BEGIN
        FOR outOrderShortDetailsRow IN
          SELECT   o.order_id, o.total_amount, o.created_on,
                   o.shipped_on, o.status, c.name
          FROM     orders o
          INNER JOIN customer c
                       ON o.customer_id = c.customer_id
          WHERE    o.created_on >= inStartDate AND o.created_on <= inEndDate
          ORDER BY o.created_on DESC
        LOOP
          RETURN NEXT outOrderShortDetailsRow;
        END LOOP;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS orders_get_orders_between_dates(TIMESTAMP, TIMESTAMP) CASCADE;"
}

# Get orders by status
resource "postgresql_script" "func_orders_get_orders_by_status" {
  count = var.create_functions ? 1 : 0
  name  = "func_orders_get_orders_by_status"
  depends_on = [
    postgresql_script.table_orders,
    postgresql_script.table_customer,
    postgresql_script.type_order_short_details
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION orders_get_orders_by_status(INTEGER)
    RETURNS SETOF order_short_details LANGUAGE plpgsql AS $$
      DECLARE
        inStatus ALIAS FOR $1;
        outOrderShortDetailsRow order_short_details;
      BEGIN
        FOR outOrderShortDetailsRow IN
          SELECT   o.order_id, o.total_amount, o.created_on,
                   o.shipped_on, o.status, c.name
          FROM     orders o
          INNER JOIN customer c
                       ON o.customer_id = c.customer_id
          WHERE    o.status = inStatus
          ORDER BY o.created_on DESC
        LOOP
          RETURN NEXT outOrderShortDetailsRow;
        END LOOP;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS orders_get_orders_by_status(INTEGER) CASCADE;"
}

# Get order info
resource "postgresql_script" "func_orders_get_order_info" {
  count = var.create_functions ? 1 : 0
  name  = "func_orders_get_order_info"
  depends_on = [
    postgresql_script.table_orders,
    postgresql_script.table_shipping,
    postgresql_script.table_tax,
    postgresql_script.type_order_info
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION orders_get_order_info(INTEGER)
    RETURNS order_info LANGUAGE plpgsql AS $$
      DECLARE
        inOrderId ALIAS FOR $1;
        outOrderInfoRow order_info;
      BEGIN
        SELECT INTO outOrderInfoRow
                    o.order_id, o.total_amount, o.created_on, o.shipped_on,
                    CASE o.status
                      WHEN 0 THEN 'pending'
                      WHEN 1 THEN 'confirmed'
                      WHEN 2 THEN 'cancelled'
                      WHEN 3 THEN 'completed'
                    END,
                    o.comments, o.customer_id, o.auth_code, o.reference,
                    o.shipping_id, s.shipping_type, s.shipping_cost,
                    o.tax_id, t.tax_type, t.tax_percentage
        FROM   orders o
        LEFT OUTER JOIN shipping s
                          ON o.shipping_id = s.shipping_id
        LEFT OUTER JOIN tax t
                          ON o.tax_id = t.tax_id
        WHERE  o.order_id = inOrderId;
        RETURN outOrderInfoRow;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS orders_get_order_info(INTEGER) CASCADE;"
}

# Get order details
resource "postgresql_script" "func_orders_get_order_details" {
  count = var.create_functions ? 1 : 0
  name  = "func_orders_get_order_details"
  depends_on = [
    postgresql_script.table_order_detail,
    postgresql_script.type_order_details
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION orders_get_order_details(INTEGER)
    RETURNS SETOF order_details LANGUAGE plpgsql AS $$
      DECLARE
        inOrderId ALIAS FOR $1;
        outOrderDetailsRow order_details;
      BEGIN
        FOR outOrderDetailsRow IN
          SELECT order_id, product_id, product_name, quantity, unit_cost,
                 quantity * unit_cost AS subtotal
          FROM   order_detail
          WHERE  order_id = inOrderId
        LOOP
          RETURN NEXT outOrderDetailsRow;
        END LOOP;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS orders_get_order_details(INTEGER) CASCADE;"
}

# Update order
resource "postgresql_script" "func_orders_update_order" {
  count = var.create_functions ? 1 : 0
  name  = "func_orders_update_order"
  depends_on = [postgresql_script.table_orders]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION orders_update_order(INTEGER, INTEGER, VARCHAR(255))
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inOrderId  ALIAS FOR $1;
        inStatus   ALIAS FOR $2;
        inComments ALIAS FOR $3;
      BEGIN
        UPDATE orders
        SET    status = inStatus, comments = inComments
        WHERE  order_id = inOrderId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS orders_update_order(INTEGER, INTEGER, VARCHAR(255)) CASCADE;"
}

# Get orders by customer ID
resource "postgresql_script" "func_orders_get_by_customer_id" {
  count = var.create_functions ? 1 : 0
  name  = "func_orders_get_by_customer_id"
  depends_on = [
    postgresql_script.table_orders,
    postgresql_script.table_customer,
    postgresql_script.type_order_short_details
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION orders_get_by_customer_id(INTEGER)
    RETURNS SETOF order_short_details LANGUAGE plpgsql AS $$
      DECLARE
        inCustomerId ALIAS FOR $1;
        outOrderShortDetailsRow order_short_details;
      BEGIN
        FOR outOrderShortDetailsRow IN
          SELECT   o.order_id, o.total_amount, o.created_on,
                   o.shipped_on, o.status, c.name
          FROM     orders o
          INNER JOIN customer c
                       ON o.customer_id = c.customer_id
          WHERE    o.customer_id = inCustomerId
          ORDER BY o.created_on DESC
        LOOP
          RETURN NEXT outOrderShortDetailsRow;
        END LOOP;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS orders_get_by_customer_id(INTEGER) CASCADE;"
}

# Get order short details
resource "postgresql_script" "func_orders_get_order_short_details" {
  count = var.create_functions ? 1 : 0
  name  = "func_orders_get_order_short_details"
  depends_on = [
    postgresql_script.table_orders,
    postgresql_script.table_customer,
    postgresql_script.type_order_short_details
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION orders_get_order_short_details(INTEGER)
    RETURNS order_short_details LANGUAGE plpgsql AS $$
      DECLARE
        inOrderId ALIAS FOR $1;
        outOrderShortDetailsRow order_short_details;
      BEGIN
        SELECT INTO outOrderShortDetailsRow
                    o.order_id, o.total_amount, o.created_on,
                    o.shipped_on, o.status, c.name
        FROM   orders o
        INNER JOIN customer c
                     ON o.customer_id = c.customer_id
        WHERE  o.order_id = inOrderId;
        RETURN outOrderShortDetailsRow;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS orders_get_order_short_details(INTEGER) CASCADE;"
}

# Get shipping info
resource "postgresql_script" "func_orders_get_shipping_info" {
  count = var.create_functions ? 1 : 0
  name  = "func_orders_get_shipping_info"
  depends_on = [postgresql_script.table_shipping]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION orders_get_shipping_info(INTEGER)
    RETURNS shipping LANGUAGE plpgsql AS $$
      DECLARE
        inShippingRegionId ALIAS FOR $1;
        outShippingRow shipping;
      BEGIN
        FOR outShippingRow IN
          SELECT shipping_id, shipping_type, shipping_cost, shipping_region_id
          FROM   shipping
          WHERE  shipping_region_id = inShippingRegionId
        LOOP
          RETURN NEXT outShippingRow;
        END LOOP;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS orders_get_shipping_info(INTEGER) CASCADE;"
}

# Create audit record
resource "postgresql_script" "func_orders_create_audit" {
  count = var.create_functions ? 1 : 0
  name  = "func_orders_create_audit"
  depends_on = [postgresql_script.table_audit]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION orders_create_audit(INTEGER, TEXT, INTEGER)
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inOrderId       ALIAS FOR $1;
        inMessage       ALIAS FOR $2;
        inMessageNumber ALIAS FOR $3;
      BEGIN
        INSERT INTO audit (order_id, created_on, message, message_number)
               VALUES (inOrderId, NOW(), inMessage, inMessageNumber);
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS orders_create_audit(INTEGER, TEXT, INTEGER) CASCADE;"
}

# Update order status
resource "postgresql_script" "func_orders_update_status" {
  count = var.create_functions ? 1 : 0
  name  = "func_orders_update_status"
  depends_on = [postgresql_script.table_orders]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION orders_update_status(INTEGER, INTEGER)
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inOrderId ALIAS FOR $1;
        inStatus  ALIAS FOR $2;
      BEGIN
        UPDATE orders
        SET    status = inStatus
        WHERE  order_id = inOrderId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS orders_update_status(INTEGER, INTEGER) CASCADE;"
}

# Set auth code
resource "postgresql_script" "func_orders_set_auth_code" {
  count = var.create_functions ? 1 : 0
  name  = "func_orders_set_auth_code"
  depends_on = [postgresql_script.table_orders]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION orders_set_auth_code(INTEGER, VARCHAR(50), VARCHAR(50))
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inOrderId   ALIAS FOR $1;
        inAuthCode  ALIAS FOR $2;
        inReference ALIAS FOR $3;
      BEGIN
        UPDATE orders
        SET    auth_code = inAuthCode, reference = inReference
        WHERE  order_id = inOrderId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS orders_set_auth_code(INTEGER, VARCHAR(50), VARCHAR(50)) CASCADE;"
}

# Set date shipped
resource "postgresql_script" "func_orders_set_date_shipped" {
  count = var.create_functions ? 1 : 0
  name  = "func_orders_set_date_shipped"
  depends_on = [postgresql_script.table_orders]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION orders_set_date_shipped(INTEGER)
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inOrderId ALIAS FOR $1;
      BEGIN
        UPDATE orders
        SET    shipped_on = NOW()
        WHERE  order_id = inOrderId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS orders_set_date_shipped(INTEGER) CASCADE;"
}

# Get audit trail
resource "postgresql_script" "func_orders_get_audit_trail" {
  count = var.create_functions ? 1 : 0
  name  = "func_orders_get_audit_trail"
  depends_on = [postgresql_script.table_audit]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION orders_get_audit_trail(INTEGER)
    RETURNS SETOF audit LANGUAGE plpgsql AS $$
      DECLARE
        inOrderId ALIAS FOR $1;
        outAuditRow audit;
      BEGIN
        FOR outAuditRow IN
          SELECT audit_id, order_id, created_on, message, message_number
          FROM   audit
          WHERE  order_id = inOrderId
          ORDER BY created_on DESC
        LOOP
          RETURN NEXT outAuditRow;
        END LOOP;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS orders_get_audit_trail(INTEGER) CASCADE;"
}

# Get product recommendations based on orders
resource "postgresql_script" "func_catalog_get_recommendations" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_get_recommendations"
  depends_on = [
    postgresql_script.table_order_detail,
    postgresql_script.table_product,
    postgresql_script.type_product_recommendation
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_get_recommendations(INTEGER, INTEGER)
    RETURNS SETOF product_recommendation LANGUAGE plpgsql AS $$
      DECLARE
        inProductId ALIAS FOR $1;
        inShortProductDescriptionLength ALIAS FOR $2;
        outProductRecommendationRow product_recommendation;
      BEGIN
        FOR outProductRecommendationRow IN
          SELECT   od.product_id, od.product_name, p.description
          FROM     order_detail od
          INNER JOIN product p
                       ON od.product_id = p.product_id
          WHERE    od.order_id IN (
                     SELECT DISTINCT order_id
                     FROM   order_detail
                     WHERE  product_id = inProductId)
          AND      od.product_id != inProductId
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

  drop_script = "DROP FUNCTION IF EXISTS catalog_get_recommendations(INTEGER, INTEGER) CASCADE;"
}
