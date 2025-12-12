# PostgreSQL Custom Types for HatShop Database
# These types are used by various stored functions

# Department list type - used for listing departments
resource "postgresql_extension" "plpgsql" {
  name = "plpgsql"
}

# Since cyrilgdn/postgresql doesn't have native type support,
# we use postgresql_script to create custom types

resource "postgresql_script" "type_department_list" {
  count = var.create_types ? 1 : 0
  name  = "type_department_list"
  
  create_script = <<-SQL
    DO $$
    BEGIN
      IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'department_list') THEN
        CREATE TYPE department_list AS (
          department_id INTEGER,
          name          VARCHAR(50)
        );
      END IF;
    END$$;
  SQL

  drop_script = "DROP TYPE IF EXISTS department_list CASCADE;"
}

resource "postgresql_script" "type_department_details" {
  count = var.create_types ? 1 : 0
  name  = "type_department_details"
  
  create_script = <<-SQL
    DO $$
    BEGIN
      IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'department_details') THEN
        CREATE TYPE department_details AS (
          name        VARCHAR(50),
          description VARCHAR(1000)
        );
      END IF;
    END$$;
  SQL

  drop_script = "DROP TYPE IF EXISTS department_details CASCADE;"
}

resource "postgresql_script" "type_category_list" {
  count = var.create_types ? 1 : 0
  name  = "type_category_list"
  
  create_script = <<-SQL
    DO $$
    BEGIN
      IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'category_list') THEN
        CREATE TYPE category_list AS (
          category_id INTEGER,
          name        VARCHAR(50)
        );
      END IF;
    END$$;
  SQL

  drop_script = "DROP TYPE IF EXISTS category_list CASCADE;"
}

resource "postgresql_script" "type_category_details" {
  count = var.create_types ? 1 : 0
  name  = "type_category_details"
  
  create_script = <<-SQL
    DO $$
    BEGIN
      IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'category_details') THEN
        CREATE TYPE category_details AS (
          name        VARCHAR(50),
          description VARCHAR(1000)
        );
      END IF;
    END$$;
  SQL

  drop_script = "DROP TYPE IF EXISTS category_details CASCADE;"
}

resource "postgresql_script" "type_product_list" {
  count = var.create_types ? 1 : 0
  name  = "type_product_list"
  
  create_script = <<-SQL
    DO $$
    BEGIN
      IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'product_list') THEN
        CREATE TYPE product_list AS (
          product_id       INTEGER,
          name             VARCHAR(50),
          description      VARCHAR(1000),
          price            NUMERIC(10, 2),
          discounted_price NUMERIC(10, 2),
          thumbnail        VARCHAR(150)
        );
      END IF;
    END$$;
  SQL

  drop_script = "DROP TYPE IF EXISTS product_list CASCADE;"
}

resource "postgresql_script" "type_product_details" {
  count = var.create_types ? 1 : 0
  name  = "type_product_details"
  
  create_script = <<-SQL
    DO $$
    BEGIN
      IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'product_details') THEN
        CREATE TYPE product_details AS (
          product_id       INTEGER,
          name             VARCHAR(50),
          description      VARCHAR(1000),
          price            NUMERIC(10, 2),
          discounted_price NUMERIC(10, 2),
          image            VARCHAR(150)
        );
      END IF;
    END$$;
  SQL

  drop_script = "DROP TYPE IF EXISTS product_details CASCADE;"
}

resource "postgresql_script" "type_department_category" {
  count = var.create_types ? 1 : 0
  name  = "type_department_category"
  
  create_script = <<-SQL
    DO $$
    BEGIN
      IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'department_category') THEN
        CREATE TYPE department_category AS (
          category_id INTEGER,
          name        VARCHAR(50),
          description VARCHAR(1000)
        );
      END IF;
    END$$;
  SQL

  drop_script = "DROP TYPE IF EXISTS department_category CASCADE;"
}

resource "postgresql_script" "type_category_product" {
  count = var.create_types ? 1 : 0
  name  = "type_category_product"
  
  create_script = <<-SQL
    DO $$
    BEGIN
      IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'category_product') THEN
        CREATE TYPE category_product AS (
          product_id       INTEGER,
          name             VARCHAR(50),
          description      VARCHAR(1000),
          price            NUMERIC(10, 2),
          discounted_price NUMERIC(10, 2)
        );
      END IF;
    END$$;
  SQL

  drop_script = "DROP TYPE IF EXISTS category_product CASCADE;"
}

resource "postgresql_script" "type_product_info" {
  count = var.create_types ? 1 : 0
  name  = "type_product_info"
  
  create_script = <<-SQL
    DO $$
    BEGIN
      IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'product_info') THEN
        CREATE TYPE product_info AS (
          name      VARCHAR(50),
          image     VARCHAR(150),
          thumbnail VARCHAR(150),
          display   SMALLINT
        );
      END IF;
    END$$;
  SQL

  drop_script = "DROP TYPE IF EXISTS product_info CASCADE;"
}

resource "postgresql_script" "type_product_category_details" {
  count = var.create_types ? 1 : 0
  name  = "type_product_category_details"
  
  create_script = <<-SQL
    DO $$
    BEGIN
      IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'product_category_details') THEN
        CREATE TYPE product_category_details AS (
          category_id   INTEGER,
          department_id INTEGER,
          name          VARCHAR(50)
        );
      END IF;
    END$$;
  SQL

  drop_script = "DROP TYPE IF EXISTS product_category_details CASCADE;"
}

resource "postgresql_script" "type_cart_product" {
  count = var.create_types ? 1 : 0
  name  = "type_cart_product"
  
  create_script = <<-SQL
    DO $$
    BEGIN
      IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'cart_product') THEN
        CREATE TYPE cart_product AS (
          product_id INTEGER,
          name       VARCHAR(50),
          price      NUMERIC(10, 2),
          quantity   INTEGER,
          subtotal   NUMERIC(10, 2)
        );
      END IF;
    END$$;
  SQL

  drop_script = "DROP TYPE IF EXISTS cart_product CASCADE;"
}

resource "postgresql_script" "type_cart_saved_product" {
  count = var.create_types ? 1 : 0
  name  = "type_cart_saved_product"
  
  create_script = <<-SQL
    DO $$
    BEGIN
      IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'cart_saved_product') THEN
        CREATE TYPE cart_saved_product AS (
          product_id INTEGER,
          name       VARCHAR(50),
          price      NUMERIC(10, 2)
        );
      END IF;
    END$$;
  SQL

  drop_script = "DROP TYPE IF EXISTS cart_saved_product CASCADE;"
}

resource "postgresql_script" "type_order_short_details" {
  count = var.create_types ? 1 : 0
  name  = "type_order_short_details"
  
  create_script = <<-SQL
    DO $$
    BEGIN
      IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'order_short_details') THEN
        CREATE TYPE order_short_details AS (
          order_id      INTEGER,
          total_amount  NUMERIC(10, 2),
          created_on    TIMESTAMP,
          shipped_on    TIMESTAMP,
          status        INTEGER,
          customer_name VARCHAR(50)
        );
      END IF;
    END$$;
  SQL

  drop_script = "DROP TYPE IF EXISTS order_short_details CASCADE;"
}

resource "postgresql_script" "type_order_details" {
  count = var.create_types ? 1 : 0
  name  = "type_order_details"
  
  create_script = <<-SQL
    DO $$
    BEGIN
      IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'order_details') THEN
        CREATE TYPE order_details AS (
          order_id     INTEGER,
          product_id   INTEGER,
          product_name VARCHAR(50),
          quantity     INTEGER,
          unit_cost    NUMERIC(10, 2),
          subtotal     NUMERIC(10, 2)
        );
      END IF;
    END$$;
  SQL

  drop_script = "DROP TYPE IF EXISTS order_details CASCADE;"
}

resource "postgresql_script" "type_product_recommendation" {
  count = var.create_types ? 1 : 0
  name  = "type_product_recommendation"
  
  create_script = <<-SQL
    DO $$
    BEGIN
      IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'product_recommendation') THEN
        CREATE TYPE product_recommendation AS (
          product_id  INTEGER,
          name        VARCHAR(50),
          description VARCHAR(1000)
        );
      END IF;
    END$$;
  SQL

  drop_script = "DROP TYPE IF EXISTS product_recommendation CASCADE;"
}

resource "postgresql_script" "type_customer_login_info" {
  count = var.create_types ? 1 : 0
  name  = "type_customer_login_info"
  
  create_script = <<-SQL
    DO $$
    BEGIN
      IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'customer_login_info') THEN
        CREATE TYPE customer_login_info AS (
          customer_id INTEGER,
          password    VARCHAR(50)
        );
      END IF;
    END$$;
  SQL

  drop_script = "DROP TYPE IF EXISTS customer_login_info CASCADE;"
}

resource "postgresql_script" "type_customer_list" {
  count = var.create_types ? 1 : 0
  name  = "type_customer_list"
  
  create_script = <<-SQL
    DO $$
    BEGIN
      IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'customer_list') THEN
        CREATE TYPE customer_list AS (
          customer_id INTEGER,
          name        VARCHAR(50)
        );
      END IF;
    END$$;
  SQL

  drop_script = "DROP TYPE IF EXISTS customer_list CASCADE;"
}

resource "postgresql_script" "type_order_info" {
  count = var.create_types ? 1 : 0
  name  = "type_order_info"
  
  create_script = <<-SQL
    DO $$
    BEGIN
      IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'order_info') THEN
        CREATE TYPE order_info AS (
          order_id       INTEGER,
          total_amount   NUMERIC(10, 2),
          created_on     TIMESTAMP,
          shipped_on     TIMESTAMP,
          status         VARCHAR(9),
          comments       VARCHAR(255),
          customer_id    INTEGER,
          auth_code      VARCHAR(50),
          reference      VARCHAR(50),
          shipping_id    INTEGER,
          shipping_type  VARCHAR(100),
          shipping_cost  NUMERIC(10, 2),
          tax_id         INTEGER,
          tax_type       VARCHAR(100),
          tax_percentage NUMERIC(10, 2)
        );
      END IF;
    END$$;
  SQL

  drop_script = "DROP TYPE IF EXISTS order_info CASCADE;"
}

resource "postgresql_script" "type_review_info" {
  count = var.create_types ? 1 : 0
  name  = "type_review_info"
  
  create_script = <<-SQL
    DO $$
    BEGIN
      IF NOT EXISTS (SELECT 1 FROM pg_type WHERE typname = 'review_info') THEN
        CREATE TYPE review_info AS (
          customer_name VARCHAR(50),
          review        TEXT,
          rating        SMALLINT,
          created_on    TIMESTAMP
        );
      END IF;
    END$$;
  SQL

  drop_script = "DROP TYPE IF EXISTS review_info CASCADE;"
}
