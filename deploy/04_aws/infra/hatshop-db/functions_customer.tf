# PostgreSQL Functions for HatShop Database - Customer and Review Functions
# Uses cyrilgdn/postgresql provider

# Get customer login info
resource "postgresql_script" "func_customer_get_login_info" {
  count = var.create_functions ? 1 : 0
  name  = "func_customer_get_login_info"
  depends_on = [
    postgresql_script.table_customer,
    postgresql_script.type_customer_login_info
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION customer_get_login_info(VARCHAR(100))
    RETURNS customer_login_info LANGUAGE plpgsql AS $$
      DECLARE
        inEmail ALIAS FOR $1;
        outCustomerLoginInfoRow customer_login_info;
      BEGIN
        SELECT INTO outCustomerLoginInfoRow
                    customer_id, password
        FROM   customer
        WHERE  email = inEmail;
        RETURN outCustomerLoginInfoRow;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS customer_get_login_info(VARCHAR(100)) CASCADE;"
}

# Add customer
resource "postgresql_script" "func_customer_add" {
  count = var.create_functions ? 1 : 0
  name  = "func_customer_add"
  depends_on = [postgresql_script.table_customer]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION customer_add(VARCHAR(50), VARCHAR(100), VARCHAR(50))
    RETURNS INTEGER LANGUAGE plpgsql AS $$
      DECLARE
        inName     ALIAS FOR $1;
        inEmail    ALIAS FOR $2;
        inPassword ALIAS FOR $3;
        outCustomerId INTEGER;
      BEGIN
        INSERT INTO customer (name, email, password)
               VALUES (inName, inEmail, inPassword);
        SELECT INTO outCustomerId currval('customer_customer_id_seq');
        RETURN outCustomerId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS customer_add(VARCHAR(50), VARCHAR(100), VARCHAR(50)) CASCADE;"
}

# Get customer
resource "postgresql_script" "func_customer_get_customer" {
  count = var.create_functions ? 1 : 0
  name  = "func_customer_get_customer"
  depends_on = [postgresql_script.table_customer]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION customer_get_customer(INTEGER)
    RETURNS customer LANGUAGE plpgsql AS $$
      DECLARE
        inCustomerId ALIAS FOR $1;
        outCustomerRow customer;
      BEGIN
        SELECT INTO outCustomerRow
                    customer_id, name, email, password, credit_card,
                    address_1, address_2, city, region, postal_code,
                    country, shipping_region_id, day_phone, eve_phone, mob_phone
        FROM   customer
        WHERE  customer_id = inCustomerId;
        RETURN outCustomerRow;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS customer_get_customer(INTEGER) CASCADE;"
}

# Update customer account
resource "postgresql_script" "func_customer_update_account" {
  count = var.create_functions ? 1 : 0
  name  = "func_customer_update_account"
  depends_on = [postgresql_script.table_customer]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION customer_update_account(INTEGER, VARCHAR(50), VARCHAR(100), VARCHAR(50),
                                                       VARCHAR(100), VARCHAR(100), VARCHAR(100))
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inCustomerId ALIAS FOR $1;
        inName       ALIAS FOR $2;
        inEmail      ALIAS FOR $3;
        inPassword   ALIAS FOR $4;
        inDayPhone   ALIAS FOR $5;
        inEvePhone   ALIAS FOR $6;
        inMobPhone   ALIAS FOR $7;
      BEGIN
        UPDATE customer
        SET    name = inName, email = inEmail, password = inPassword,
               day_phone = inDayPhone, eve_phone = inEvePhone, mob_phone = inMobPhone
        WHERE  customer_id = inCustomerId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS customer_update_account(INTEGER, VARCHAR(50), VARCHAR(100), VARCHAR(50), VARCHAR(100), VARCHAR(100), VARCHAR(100)) CASCADE;"
}

# Update customer credit card
resource "postgresql_script" "func_customer_update_credit_card" {
  count = var.create_functions ? 1 : 0
  name  = "func_customer_update_credit_card"
  depends_on = [postgresql_script.table_customer]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION customer_update_credit_card(INTEGER, TEXT)
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inCustomerId ALIAS FOR $1;
        inCreditCard ALIAS FOR $2;
      BEGIN
        UPDATE customer
        SET    credit_card = inCreditCard
        WHERE  customer_id = inCustomerId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS customer_update_credit_card(INTEGER, TEXT) CASCADE;"
}

# Get shipping regions
resource "postgresql_script" "func_customer_get_shipping_regions" {
  count = var.create_functions ? 1 : 0
  name  = "func_customer_get_shipping_regions"
  depends_on = [postgresql_script.table_shipping_region]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION customer_get_shipping_regions()
    RETURNS SETOF shipping_region LANGUAGE plpgsql AS $$
      DECLARE
        outShippingRegionRow shipping_region;
      BEGIN
        FOR outShippingRegionRow IN
          SELECT shipping_region_id, shipping_region
          FROM   shipping_region
          ORDER BY shipping_region_id
        LOOP
          RETURN NEXT outShippingRegionRow;
        END LOOP;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS customer_get_shipping_regions() CASCADE;"
}

# Update customer address
resource "postgresql_script" "func_customer_update_address" {
  count = var.create_functions ? 1 : 0
  name  = "func_customer_update_address"
  depends_on = [postgresql_script.table_customer]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION customer_update_address(INTEGER, VARCHAR(100), VARCHAR(100),
                                                       VARCHAR(100), VARCHAR(100), VARCHAR(100),
                                                       VARCHAR(100), INTEGER)
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inCustomerId       ALIAS FOR $1;
        inAddress1         ALIAS FOR $2;
        inAddress2         ALIAS FOR $3;
        inCity             ALIAS FOR $4;
        inRegion           ALIAS FOR $5;
        inPostalCode       ALIAS FOR $6;
        inCountry          ALIAS FOR $7;
        inShippingRegionId ALIAS FOR $8;
      BEGIN
        UPDATE customer
        SET    address_1 = inAddress1, address_2 = inAddress2,
               city = inCity, region = inRegion, postal_code = inPostalCode,
               country = inCountry, shipping_region_id = inShippingRegionId
        WHERE  customer_id = inCustomerId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS customer_update_address(INTEGER, VARCHAR(100), VARCHAR(100), VARCHAR(100), VARCHAR(100), VARCHAR(100), VARCHAR(100), INTEGER) CASCADE;"
}

# Get customers list
resource "postgresql_script" "func_customer_get_customers_list" {
  count = var.create_functions ? 1 : 0
  name  = "func_customer_get_customers_list"
  depends_on = [
    postgresql_script.table_customer,
    postgresql_script.type_customer_list
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION customer_get_customers_list()
    RETURNS SETOF customer_list LANGUAGE plpgsql AS $$
      DECLARE
        outCustomerListRow customer_list;
      BEGIN
        FOR outCustomerListRow IN
          SELECT customer_id, name
          FROM   customer
          ORDER BY name
        LOOP
          RETURN NEXT outCustomerListRow;
        END LOOP;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS customer_get_customers_list() CASCADE;"
}

# Get product reviews
resource "postgresql_script" "func_catalog_get_product_reviews" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_get_product_reviews"
  depends_on = [
    postgresql_script.table_review,
    postgresql_script.table_customer,
    postgresql_script.type_review_info
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_get_product_reviews(INTEGER)
    RETURNS SETOF review_info LANGUAGE plpgsql AS $$
      DECLARE
        inProductId ALIAS FOR $1;
        outReviewInfoRow review_info;
      BEGIN
        FOR outReviewInfoRow IN
          SELECT   c.name, r.review, r.rating, r.created_on
          FROM     review r
          INNER JOIN customer c
                       ON r.customer_id = c.customer_id
          WHERE    r.product_id = inProductId
          ORDER BY r.created_on DESC
        LOOP
          RETURN NEXT outReviewInfoRow;
        END LOOP;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_get_product_reviews(INTEGER) CASCADE;"
}

# Create product review
resource "postgresql_script" "func_catalog_create_product_review" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_create_product_review"
  depends_on = [postgresql_script.table_review]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_create_product_review(INTEGER, INTEGER, TEXT, SMALLINT)
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inCustomerId ALIAS FOR $1;
        inProductId  ALIAS FOR $2;
        inReview     ALIAS FOR $3;
        inRating     ALIAS FOR $4;
      BEGIN
        INSERT INTO review (customer_id, product_id, review, rating, created_on)
               VALUES (inCustomerId, inProductId, inReview, inRating, NOW());
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_create_product_review(INTEGER, INTEGER, TEXT, SMALLINT) CASCADE;"
}
