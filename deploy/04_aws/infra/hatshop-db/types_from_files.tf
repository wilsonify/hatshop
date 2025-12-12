# PostgreSQL Custom Types for HatShop Database
# Reads SQL directly from source files using file() function

resource "postgresql_extension" "plpgsql" {
  name = "plpgsql"
}

resource "postgresql_script" "type_department_list" {
  count         = var.create_types ? 1 : 0
  name          = "type_department_list"
  create_script = file("${local.sql_base_path}/${local.type_files.department_list}")
  drop_script   = "DROP TYPE IF EXISTS department_list CASCADE;"
}

resource "postgresql_script" "type_department_details" {
  count         = var.create_types ? 1 : 0
  name          = "type_department_details"
  create_script = file("${local.sql_base_path}/${local.type_files.department_details}")
  drop_script   = "DROP TYPE IF EXISTS department_details CASCADE;"
}

resource "postgresql_script" "type_category_list" {
  count         = var.create_types ? 1 : 0
  name          = "type_category_list"
  create_script = file("${local.sql_base_path}/${local.type_files.category_list}")
  drop_script   = "DROP TYPE IF EXISTS category_list CASCADE;"
}

resource "postgresql_script" "type_category_details" {
  count         = var.create_types ? 1 : 0
  name          = "type_category_details"
  create_script = file("${local.sql_base_path}/${local.type_files.category_details}")
  drop_script   = "DROP TYPE IF EXISTS category_details CASCADE;"
}

resource "postgresql_script" "type_product_list" {
  count         = var.create_types ? 1 : 0
  name          = "type_product_list"
  create_script = file("${local.sql_base_path}/${local.type_files.product_list}")
  drop_script   = "DROP TYPE IF EXISTS product_list CASCADE;"
}

resource "postgresql_script" "type_product_details" {
  count         = var.create_types ? 1 : 0
  name          = "type_product_details"
  create_script = file("${local.sql_base_path}/${local.type_files.product_details}")
  drop_script   = "DROP TYPE IF EXISTS product_details CASCADE;"
}

resource "postgresql_script" "type_department_category" {
  count         = var.create_types ? 1 : 0
  name          = "type_department_category"
  create_script = file("${local.sql_base_path}/${local.type_files.department_category}")
  drop_script   = "DROP TYPE IF EXISTS department_category CASCADE;"
}

resource "postgresql_script" "type_category_product" {
  count         = var.create_types ? 1 : 0
  name          = "type_category_product"
  create_script = file("${local.sql_base_path}/${local.type_files.category_product}")
  drop_script   = "DROP TYPE IF EXISTS category_product CASCADE;"
}

resource "postgresql_script" "type_product_info" {
  count         = var.create_types ? 1 : 0
  name          = "type_product_info"
  create_script = file("${local.sql_base_path}/${local.type_files.product_info}")
  drop_script   = "DROP TYPE IF EXISTS product_info CASCADE;"
}

resource "postgresql_script" "type_product_category_details" {
  count         = var.create_types ? 1 : 0
  name          = "type_product_category_details"
  create_script = file("${local.sql_base_path}/${local.type_files.product_category_details}")
  drop_script   = "DROP TYPE IF EXISTS product_category_details CASCADE;"
}

resource "postgresql_script" "type_cart_product" {
  count         = var.create_types ? 1 : 0
  name          = "type_cart_product"
  create_script = file("${local.sql_base_path}/${local.type_files.cart_product}")
  drop_script   = "DROP TYPE IF EXISTS cart_product CASCADE;"
}

resource "postgresql_script" "type_cart_saved_product" {
  count         = var.create_types ? 1 : 0
  name          = "type_cart_saved_product"
  create_script = file("${local.sql_base_path}/${local.type_files.cart_saved_product}")
  drop_script   = "DROP TYPE IF EXISTS cart_saved_product CASCADE;"
}

resource "postgresql_script" "type_order_short_details" {
  count         = var.create_types ? 1 : 0
  name          = "type_order_short_details"
  create_script = file("${local.sql_base_path}/${local.type_files.order_short_details}")
  drop_script   = "DROP TYPE IF EXISTS order_short_details CASCADE;"
}

resource "postgresql_script" "type_order_details" {
  count         = var.create_types ? 1 : 0
  name          = "type_order_details"
  create_script = file("${local.sql_base_path}/${local.type_files.order_details}")
  drop_script   = "DROP TYPE IF EXISTS order_details CASCADE;"
}

resource "postgresql_script" "type_product_recommendation" {
  count         = var.create_types ? 1 : 0
  name          = "type_product_recommendation"
  create_script = file("${local.sql_base_path}/${local.type_files.product_recommendation}")
  drop_script   = "DROP TYPE IF EXISTS product_recommendation CASCADE;"
}

resource "postgresql_script" "type_customer_login_info" {
  count         = var.create_types ? 1 : 0
  name          = "type_customer_login_info"
  create_script = file("${local.sql_base_path}/${local.type_files.customer_login_info}")
  drop_script   = "DROP TYPE IF EXISTS customer_login_info CASCADE;"
}

resource "postgresql_script" "type_customer_list" {
  count         = var.create_types ? 1 : 0
  name          = "type_customer_list"
  create_script = file("${local.sql_base_path}/${local.type_files.customer_list}")
  drop_script   = "DROP TYPE IF EXISTS customer_list CASCADE;"
}

resource "postgresql_script" "type_order_info" {
  count         = var.create_types ? 1 : 0
  name          = "type_order_info"
  create_script = file("${local.sql_base_path}/${local.type_files.order_info}")
  drop_script   = "DROP TYPE IF EXISTS order_info CASCADE;"
}

resource "postgresql_script" "type_review_info" {
  count         = var.create_types ? 1 : 0
  name          = "type_review_info"
  create_script = file("${local.sql_base_path}/${local.type_files.review_info}")
  drop_script   = "DROP TYPE IF EXISTS review_info CASCADE;"
}
