# Local values for SQL file paths
# This allows reading SQL directly from the source files

locals {
  sql_base_path = "${path.module}/../../../../src/c00 - PostgreSQL Image/Database Complete/split_sql_files"
  
  # Table SQL files
  table_files = {
    department       = "02_Create_department_table.sql"
    category         = "03_Create_category_table.sql"
    product          = "04_Create_product_table.sql"
    search_index     = "05_Create_index_for_search_vector_field_in_product_table.sql"
    product_category = "06_Create_product_category_table.sql"
    shopping_cart    = "07_Create_shopping_cart_table.sql"
    shipping_region  = "08_Create_shipping_region_table.sql"
    shipping         = "09_Create_shipping_table.sql"
    tax              = "10_Create_tax_table.sql"
    customer         = "11_Create_customer_table.sql"
    orders           = "12_Create_orders_table.sql"
    order_detail     = "13_Create_order_detail_table.sql"
    audit            = "14_Create_audit_table.sql"
    review           = "15_Create_review_table.sql"
  }

  # Type SQL files
  type_files = {
    department_list           = "17_Create_department_list_type.sql"
    department_details        = "18_Create_department_details_type.sql"
    category_list             = "19_Create_category_list_type.sql"
    category_details          = "20_Create_category_details_type.sql"
    product_list              = "21_Create_product_list_type.sql"
    product_details           = "22_Create_product_details_type.sql"
    department_category       = "23_Create_department_category_type.sql"
    category_product          = "24_Create_category_product_type.sql"
    product_info              = "25_Create_product_info_type.sql"
    product_category_details  = "26_Create_product_category_details_type.sql"
    cart_product              = "27_Create_cart_product_type.sql"
    cart_saved_product        = "28_Create_cart_saved_product_type.sql"
    order_short_details       = "29_Create_order_short_details_type.sql"
    order_details             = "30_Create_order_details_type.sql"
    product_recommendation    = "31_Create_product_recommendation_type.sql"
    customer_login_info       = "32_Create_customer_login_info_type.sql"
    customer_list             = "33_Create_customer_list_type.sql"
    order_info                = "34_Create_order_info_type.sql"
    review_info               = "35_Create_review_info_type.sql"
  }

  # Function SQL files - Catalog
  catalog_function_files = {
    get_departments_list         = "37_Create_catalog_get_departments_list.sql"
    get_department_details       = "38_Create_catalog_get_department_details_function.sql"
    get_categories_list          = "39_Create_catalog_get_categories_list_function.sql"
    get_category_details         = "40_Create_catalog_get_category_details_function.sql"
    count_products_in_category   = "41_Create_catalog_count_products_in_category_function.sql"
    get_products_in_category     = "42_Create_catalog_get_products_in_category_function.sql"
    count_products_on_department = "43_Create_catalog_count_products_on_department_function.sql"
    get_products_on_department   = "44_Create_catalog_get_products_on_department_function.sql"
    count_products_on_catalog    = "45_Create_catalog_count_products_on_catalog_function.sql"
    get_products_on_catalog      = "46_Create_catalog_get_products_on_catalog_function.sql"
    get_product_details          = "47_Create_catalog_get_product_details_function.sql"
    flag_stop_words              = "48_Create_catalog_flag_stop_words_function.sql"
    search                       = "49_Create_catalog_search_function.sql"
    get_departments              = "50_Create_catalog_get_departments_function.sql"
    update_department            = "51_Create_catalog_update_department_function.sql"
    delete_department            = "52_Create_catalog_delete_department_function.sql"
    add_department               = "53_Create_catalog_add_department_function.sql"
    get_department_categories    = "54_Create_catalog_get_department_categories_function.sql"
    add_category                 = "55_Create_catalog_add_category_function.sql"
    delete_category              = "56_Create_catalog_delete_category_function.sql"
    update_category              = "57_Create_catalog_update_category_function.sql"
    get_category_products        = "58_Create_catalog_get_category_products_function.sql"
    add_product_to_category      = "59_Create_catalog_add_product_to_category_function.sql"
    update_product               = "60_Create_catalog_update_product_function.sql"
    delete_product               = "61_Create_catalog_delete_product_function.sql"
    remove_product_from_category = "62_Create_catalog_remove_product_from_category_function.sql"
    get_categories               = "63_Create_catalog_get_categories_function.sql"
    get_product_info             = "64_Create_catalog_get_product_info_function.sql"
    get_categories_for_product   = "65_Create_catalog_get_categories_for_product_function.sql"
    set_product_display_option   = "66_Create_catalog_set_product_display_option_function.sql"
    assign_product_to_category   = "67_Create_catalog_assign_product_to_category_function.sql"
    move_product_to_category     = "68_Create_catalog_move_product_to_category_function.sql"
    set_image                    = "69_Create_catalog_set_image_function.sql"
    set_thumbnail                = "70_Create_catalog_set_thumbnail_function.sql"
    get_recommendations          = "89_Create_catalog_get_recommendations_function.sql"
    get_product_reviews          = "9107_Create_catalog_get_product_reviews_function.sql"
    create_product_review        = "9108_Create_catalog_create_product_review_function.sql"
  }

  # Function SQL files - Shopping Cart
  cart_function_files = {
    add_product            = "71_Create_shopping_cart_add_product_function.sql"
    update                 = "72_Create_shopping_cart_update_function.sql"
    remove_product         = "73_Create_shopping_cart_remove_product_function.sql"
    get_products           = "74_Create_shopping_cart_get_products_function.sql"
    get_saved_products     = "75_Create_shopping_cart_get_saved_products_function.sql"
    get_total_amount       = "76_Create_shopping_cart_get_total_amount_function.sql"
    save_product_for_later = "77_Create_shopping_cart_save_product_for_later_function.sql"
    move_product_to_cart   = "78_Create_shopping_cart_move_product_to_cart_function.sql"
    count_old_carts        = "79_Create_shopping_cart_count_old_carts_function.sql"
    delete_old_carts       = "80_Create_shopping_cart_delete_old_carts_function.sql"
    empty                  = "81_Create_shopping_cart_empty_function.sql"
    create_order           = "82_Create_shopping_cart_create_order_function.sql"
    get_recommendations    = "90_Create_shopping_cart_get_recommendations_function.sql"
  }

  # Function SQL files - Orders
  orders_function_files = {
    get_most_recent_orders    = "83_Create_orders_get_most_recent_orders_function.sql"
    get_orders_between_dates  = "84_Create_orders_get_orders_between_dates_function.sql"
    get_orders_by_status      = "85_Create_orders_get_orders_by_status_function.sql"
    get_order_info            = "86_Create_orders_get_order_info_function.sql"
    get_order_details         = "87_Create_orders_get_order_details_function.sql"
    update_order              = "88_Create_orders_update_order_function.sql"
    get_by_customer_id        = "98_Create_orders_get_by_customer_id_function.sql"
    get_order_short_details   = "99_Create_orders_get_order_short_details_function.sql"
    get_shipping_info         = "9101_Create_orders_get_shipping_info_function.sql"
    create_audit              = "9102_Create_orders_create_audit_function.sql"
    update_status             = "9103_Create_orders_update_status_function.sql"
    set_auth_code             = "9104_Create_orders_set_auth_code_function.sql"
    set_date_shipped          = "9105_Create_orders_set_date_shipped_function.sql"
    get_audit_trail           = "9106_Create_orders_get_audit_trail_function.sql"
  }

  # Function SQL files - Customer
  customer_function_files = {
    get_login_info         = "91_Create_customer_get_login_info_function.sql"
    add                    = "92_Create_customer_add_function.sql"
    get_customer           = "93_Create_customer_get_customer_function.sql"
    update_account         = "94_Create_customer_update_account_function.sql"
    update_credit_card     = "95_Create_customer_update_credit_card_function.sql"
    get_shipping_regions   = "96_Create_customer_get_shipping_regions_function.sql"
    update_address         = "97_Create_customer_update_address_function.sql"
    get_customers_list     = "9100_Create_customer_get_customers_list_function.sql"
  }
}
