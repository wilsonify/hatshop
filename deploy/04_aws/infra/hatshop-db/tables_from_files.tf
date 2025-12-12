# PostgreSQL Tables for HatShop Database
# Reads SQL directly from source files using file() function

# Department table
resource "postgresql_script" "table_department" {
  name          = "table_department"
  create_script = file("${local.sql_base_path}/${local.table_files.department}")
  drop_script   = "DROP TABLE IF EXISTS department CASCADE;"
}

# Category table
resource "postgresql_script" "table_category" {
  name       = "table_category"
  depends_on = [postgresql_script.table_department]

  create_script = file("${local.sql_base_path}/${local.table_files.category}")
  drop_script   = "DROP TABLE IF EXISTS category CASCADE;"
}

# Product table
resource "postgresql_script" "table_product" {
  name          = "table_product"
  create_script = file("${local.sql_base_path}/${local.table_files.product}")
  drop_script   = "DROP TABLE IF EXISTS product CASCADE;"
}

# Product-Category junction table
resource "postgresql_script" "table_product_category" {
  name = "table_product_category"
  depends_on = [
    postgresql_script.table_product,
    postgresql_script.table_category
  ]

  create_script = file("${local.sql_base_path}/${local.table_files.product_category}")
  drop_script   = "DROP TABLE IF EXISTS product_category CASCADE;"
}

# Index for full-text search on product table
resource "postgresql_script" "index_search_vector" {
  name       = "index_search_vector"
  depends_on = [postgresql_script.table_product]

  create_script = file("${local.sql_base_path}/${local.table_files.search_index}")
  drop_script   = "DROP INDEX IF EXISTS idx_search_vector;"
}

# Shopping cart table
resource "postgresql_script" "table_shopping_cart" {
  name       = "table_shopping_cart"
  depends_on = [postgresql_script.table_product]

  create_script = file("${local.sql_base_path}/${local.table_files.shopping_cart}")
  drop_script   = "DROP TABLE IF EXISTS shopping_cart CASCADE;"
}

# Shipping region table
resource "postgresql_script" "table_shipping_region" {
  name          = "table_shipping_region"
  create_script = file("${local.sql_base_path}/${local.table_files.shipping_region}")
  drop_script   = "DROP TABLE IF EXISTS shipping_region CASCADE;"
}

# Shipping table
resource "postgresql_script" "table_shipping" {
  name       = "table_shipping"
  depends_on = [postgresql_script.table_shipping_region]

  create_script = file("${local.sql_base_path}/${local.table_files.shipping}")
  drop_script   = "DROP TABLE IF EXISTS shipping CASCADE;"
}

# Tax table
resource "postgresql_script" "table_tax" {
  name          = "table_tax"
  create_script = file("${local.sql_base_path}/${local.table_files.tax}")
  drop_script   = "DROP TABLE IF EXISTS tax CASCADE;"
}

# Customer table
resource "postgresql_script" "table_customer" {
  name       = "table_customer"
  depends_on = [postgresql_script.table_shipping_region]

  create_script = file("${local.sql_base_path}/${local.table_files.customer}")
  drop_script   = "DROP TABLE IF EXISTS customer CASCADE;"
}

# Orders table
resource "postgresql_script" "table_orders" {
  name = "table_orders"
  depends_on = [
    postgresql_script.table_customer,
    postgresql_script.table_shipping,
    postgresql_script.table_tax
  ]

  create_script = file("${local.sql_base_path}/${local.table_files.orders}")
  drop_script   = "DROP TABLE IF EXISTS orders CASCADE;"
}

# Order detail table
resource "postgresql_script" "table_order_detail" {
  name       = "table_order_detail"
  depends_on = [postgresql_script.table_orders]

  create_script = file("${local.sql_base_path}/${local.table_files.order_detail}")
  drop_script   = "DROP TABLE IF EXISTS order_detail CASCADE;"
}

# Audit table
resource "postgresql_script" "table_audit" {
  name       = "table_audit"
  depends_on = [postgresql_script.table_orders]

  create_script = file("${local.sql_base_path}/${local.table_files.audit}")
  drop_script   = "DROP TABLE IF EXISTS audit CASCADE;"
}

# Review table
resource "postgresql_script" "table_review" {
  name = "table_review"
  depends_on = [
    postgresql_script.table_customer,
    postgresql_script.table_product
  ]

  create_script = file("${local.sql_base_path}/${local.table_files.review}")
  drop_script   = "DROP TABLE IF EXISTS review CASCADE;"
}
