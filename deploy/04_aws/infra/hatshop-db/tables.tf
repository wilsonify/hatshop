# PostgreSQL Tables for HatShop Database
# Uses cyrilgdn/postgresql provider for table management

# Department table - main department entity
resource "postgresql_script" "table_department" {
  name = "table_department"

  create_script = <<-SQL
    CREATE TABLE IF NOT EXISTS department (
      department_id SERIAL        NOT NULL,
      name          VARCHAR(50)   NOT NULL,
      description   VARCHAR(1000),
      CONSTRAINT pk_department_id PRIMARY KEY (department_id)
    );
  SQL

  drop_script = "DROP TABLE IF EXISTS department CASCADE;"
}

# Category table - categories belong to departments
resource "postgresql_script" "table_category" {
  name = "table_category"
  depends_on = [postgresql_script.table_department]

  create_script = <<-SQL
    CREATE TABLE IF NOT EXISTS category (
      category_id   SERIAL        NOT NULL,
      department_id INTEGER       NOT NULL,
      name          VARCHAR(50)   NOT NULL,
      description   VARCHAR(1000),
      CONSTRAINT pk_category_id   PRIMARY KEY (category_id),
      CONSTRAINT fk_department_id FOREIGN KEY (department_id)
                 REFERENCES department (department_id)
                 ON UPDATE RESTRICT ON DELETE RESTRICT
    );
  SQL

  drop_script = "DROP TABLE IF EXISTS category CASCADE;"
}

# Product table - core product entity with full-text search support
resource "postgresql_script" "table_product" {
  name = "table_product"

  create_script = <<-SQL
    CREATE TABLE IF NOT EXISTS product (
      product_id       SERIAL         NOT NULL,
      name             VARCHAR(50)    NOT NULL,
      description      VARCHAR(1000)  NOT NULL,
      price            NUMERIC(10, 2) NOT NULL,
      discounted_price NUMERIC(10, 2) NOT NULL DEFAULT 0.00,
      image            VARCHAR(150),
      thumbnail        VARCHAR(150),
      display          SMALLINT       NOT NULL DEFAULT 0,
      search_vector    TSVECTOR,
      CONSTRAINT pk_product PRIMARY KEY (product_id)
    );
  SQL

  drop_script = "DROP TABLE IF EXISTS product CASCADE;"
}

# Product-Category junction table - many-to-many relationship
resource "postgresql_script" "table_product_category" {
  name = "table_product_category"
  depends_on = [
    postgresql_script.table_product,
    postgresql_script.table_category
  ]

  create_script = <<-SQL
    CREATE TABLE IF NOT EXISTS product_category (
      product_id  INTEGER NOT NULL,
      category_id INTEGER NOT NULL,
      CONSTRAINT pk_product_id_category_id PRIMARY KEY (product_id, category_id),
      CONSTRAINT fk_product_id             FOREIGN KEY (product_id)
                 REFERENCES product (product_id)
                 ON UPDATE RESTRICT ON DELETE RESTRICT,
      CONSTRAINT fk_category_id            FOREIGN KEY (category_id)
                 REFERENCES category (category_id)
                 ON UPDATE RESTRICT ON DELETE RESTRICT
    );
  SQL

  drop_script = "DROP TABLE IF EXISTS product_category CASCADE;"
}

# Index for full-text search on product table
resource "postgresql_script" "index_search_vector" {
  name = "index_search_vector"
  depends_on = [postgresql_script.table_product]

  create_script = <<-SQL
    CREATE INDEX IF NOT EXISTS idx_search_vector
    ON product USING gin(search_vector);
  SQL

  drop_script = "DROP INDEX IF EXISTS idx_search_vector;"
}

# Shopping cart table - stores cart items
resource "postgresql_script" "table_shopping_cart" {
  name = "table_shopping_cart"
  depends_on = [postgresql_script.table_product]

  create_script = <<-SQL
    CREATE TABLE IF NOT EXISTS shopping_cart (
      cart_id     CHAR(128)  NOT NULL,
      product_id  INTEGER   NOT NULL,
      quantity    INTEGER   NOT NULL,
      buy_now     BOOLEAN   NOT NULL DEFAULT true,
      added_on    TIMESTAMP NOT NULL,
      CONSTRAINT pk_cart_id_product_id PRIMARY KEY (cart_id, product_id),
      CONSTRAINT fk_product_id         FOREIGN KEY (product_id)
                 REFERENCES product (product_id)
                 ON UPDATE RESTRICT ON DELETE RESTRICT
    );
  SQL

  drop_script = "DROP TABLE IF EXISTS shopping_cart CASCADE;"
}

# Shipping region table - regions for shipping
resource "postgresql_script" "table_shipping_region" {
  name = "table_shipping_region"

  create_script = <<-SQL
    CREATE TABLE IF NOT EXISTS shipping_region (
      shipping_region_id SERIAL       NOT NULL,
      shipping_region    VARCHAR(100) NOT NULL,
      CONSTRAINT pk_shipping_region_id PRIMARY KEY (shipping_region_id)
    );
  SQL

  drop_script = "DROP TABLE IF EXISTS shipping_region CASCADE;"
}

# Shipping table - shipping options per region
resource "postgresql_script" "table_shipping" {
  name = "table_shipping"
  depends_on = [postgresql_script.table_shipping_region]

  create_script = <<-SQL
    CREATE TABLE IF NOT EXISTS shipping (
      shipping_id        SERIAL         NOT NULL,
      shipping_type      VARCHAR(100)   NOT NULL,
      shipping_cost      NUMERIC(10, 2) NOT NULL,
      shipping_region_id INTEGER        NOT NULL,
      CONSTRAINT pk_shipping_id        PRIMARY KEY (shipping_id),
      CONSTRAINT fk_shipping_region_id FOREIGN KEY (shipping_region_id)
                 REFERENCES shipping_region (shipping_region_id)
                 ON UPDATE RESTRICT ON DELETE RESTRICT
    );
  SQL

  drop_script = "DROP TABLE IF EXISTS shipping CASCADE;"
}

# Tax table - tax rates
resource "postgresql_script" "table_tax" {
  name = "table_tax"

  create_script = <<-SQL
    CREATE TABLE IF NOT EXISTS tax (
      tax_id         SERIAL         NOT NULL,
      tax_type       VARCHAR(100)   NOT NULL,
      tax_percentage NUMERIC(10, 2) NOT NULL,
      CONSTRAINT pk_tax_id PRIMARY KEY (tax_id)
    );
  SQL

  drop_script = "DROP TABLE IF EXISTS tax CASCADE;"
}

# Customer table - customer accounts
resource "postgresql_script" "table_customer" {
  name = "table_customer"
  depends_on = [postgresql_script.table_shipping_region]

  create_script = <<-SQL
    CREATE TABLE IF NOT EXISTS customer (
      customer_id        SERIAL        NOT NULL,
      name               VARCHAR(50)   NOT NULL,
      email              VARCHAR(100)  NOT NULL,
      password           VARCHAR(50)   NOT NULL,
      credit_card        TEXT,
      address_1          VARCHAR(100),
      address_2          VARCHAR(100),
      city               VARCHAR(100),
      region             VARCHAR(100),
      postal_code        VARCHAR(100),
      country            VARCHAR(100),
      shipping_region_id INTEGER       NOT NULL  DEFAULT 1,
      day_phone          VARCHAR(100),
      eve_phone          VARCHAR(100),
      mob_phone          VARCHAR(100),
      CONSTRAINT pk_customer_id        PRIMARY KEY (customer_id),
      CONSTRAINT fk_shipping_region_id FOREIGN KEY (shipping_region_id)
                 REFERENCES shipping_region (shipping_region_id)
                 ON UPDATE RESTRICT ON DELETE RESTRICT,
      CONSTRAINT uk_email              UNIQUE (email)
    );
  SQL

  drop_script = "DROP TABLE IF EXISTS customer CASCADE;"
}

# Orders table - customer orders
resource "postgresql_script" "table_orders" {
  name = "table_orders"
  depends_on = [
    postgresql_script.table_customer,
    postgresql_script.table_shipping,
    postgresql_script.table_tax
  ]

  create_script = <<-SQL
    CREATE TABLE IF NOT EXISTS orders (
      order_id         SERIAL        NOT NULL,
      total_amount     NUMERIC(10,2) NOT NULL DEFAULT 0.00,
      created_on       TIMESTAMP     NOT NULL,
      shipped_on       TIMESTAMP,
      status           INTEGER       NOT NULL DEFAULT 0,
      comments         VARCHAR(255),
      customer_id      INTEGER,
      auth_code        VARCHAR(50),
      reference        VARCHAR(50),
      shipping_id      INTEGER,
      tax_id           INTEGER,
      CONSTRAINT pk_order_id PRIMARY KEY (order_id),
      CONSTRAINT fk_customer_id FOREIGN KEY (customer_id)
                 REFERENCES customer (customer_id)
                 ON UPDATE RESTRICT ON DELETE RESTRICT,
      CONSTRAINT fk_shipping_id FOREIGN KEY (shipping_id)
                 REFERENCES shipping (shipping_id)
                 ON UPDATE RESTRICT ON DELETE RESTRICT,
      CONSTRAINT fk_tax_id FOREIGN KEY (tax_id)
                 REFERENCES tax (tax_id)
                 ON UPDATE RESTRICT ON DELETE RESTRICT
    );
  SQL

  drop_script = "DROP TABLE IF EXISTS orders CASCADE;"
}

# Order detail table - line items in orders
resource "postgresql_script" "table_order_detail" {
  name = "table_order_detail"
  depends_on = [postgresql_script.table_orders]

  create_script = <<-SQL
    CREATE TABLE IF NOT EXISTS order_detail (
      order_id     INTEGER        NOT NULL,
      product_id   INTEGER        NOT NULL,
      product_name VARCHAR(50)    NOT NULL,
      quantity     INTEGER        NOT NULL,
      unit_cost    NUMERIC(10, 2) NOT NULL,
      CONSTRAINT pk_order_id_product_id PRIMARY KEY (order_id, product_id),
      CONSTRAINT fk_order_id            FOREIGN KEY (order_id)
                 REFERENCES orders (order_id)
                 ON UPDATE RESTRICT ON DELETE RESTRICT
    );
  SQL

  drop_script = "DROP TABLE IF EXISTS order_detail CASCADE;"
}

# Audit table - order audit trail
resource "postgresql_script" "table_audit" {
  name = "table_audit"
  depends_on = [postgresql_script.table_orders]

  create_script = <<-SQL
    CREATE TABLE IF NOT EXISTS audit (
      audit_id       SERIAL    NOT NULL,
      order_id       INTEGER   NOT NULL,
      created_on     TIMESTAMP NOT NULL,
      message        TEXT      NOT NULL,
      message_number INTEGER   NOT NULL,
      CONSTRAINT pk_audit_id PRIMARY KEY (audit_id),
      CONSTRAINT fk_order_id FOREIGN KEY (order_id)
                 REFERENCES orders (order_id)
                 ON UPDATE RESTRICT ON DELETE RESTRICT
    );
  SQL

  drop_script = "DROP TABLE IF EXISTS audit CASCADE;"
}

# Review table - product reviews by customers
resource "postgresql_script" "table_review" {
  name = "table_review"
  depends_on = [
    postgresql_script.table_customer,
    postgresql_script.table_product
  ]

  create_script = <<-SQL
    CREATE TABLE IF NOT EXISTS review (
      review_id   SERIAL    NOT NULL,
      customer_id INTEGER   NOT NULL,
      product_id  INTEGER   NOT NULL,
      review      TEXT      NOT NULL,
      rating      SMALLINT  NOT NULL,
      created_on  TIMESTAMP NOT NULL,
      CONSTRAINT pk_review_id PRIMARY KEY (review_id),
      CONSTRAINT fk_customer_id FOREIGN KEY (customer_id)
                 REFERENCES customer (customer_id)
                 ON UPDATE RESTRICT ON DELETE RESTRICT,
      CONSTRAINT fk_product_id FOREIGN KEY (product_id)
                 REFERENCES product (product_id)
                 ON UPDATE RESTRICT ON DELETE RESTRICT
    );
  SQL

  drop_script = "DROP TABLE IF EXISTS review CASCADE;"
}
