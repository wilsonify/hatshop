# Outputs for HatShop Database Terraform Module

output "database_name" {
  description = "The name of the PostgreSQL database"
  value       = var.db_name
}

output "database_host" {
  description = "The host of the PostgreSQL database"
  value       = var.db_host
}

output "database_port" {
  description = "The port of the PostgreSQL database"
  value       = var.db_port
}

output "tables_created" {
  description = "List of tables created by this module"
  value = [
    "department",
    "category",
    "product",
    "product_category",
    "shopping_cart",
    "shipping_region",
    "shipping",
    "tax",
    "customer",
    "orders",
    "order_detail",
    "audit",
    "review"
  ]
}

output "custom_types_created" {
  description = "List of custom PostgreSQL types created by this module"
  value = var.create_types ? [
    "department_list",
    "department_details",
    "category_list",
    "category_details",
    "product_list",
    "product_details",
    "department_category",
    "category_product",
    "product_info",
    "product_category_details",
    "cart_product",
    "cart_saved_product",
    "order_short_details",
    "order_details",
    "product_recommendation",
    "customer_login_info",
    "customer_list",
    "order_info",
    "review_info"
  ] : []
}
