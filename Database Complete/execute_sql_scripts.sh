#!/bin/bash
set -euxo pipefail
DB_PORT="5432"


echo "Executing 01_Create_hatshop_tables.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "01_Create_hatshop_tables.sql"
echo "Done with 01_Create_hatshop_tables.sql"

echo "Executing 02_Create_department_table.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "02_Create_department_table.sql"
echo "Done with 02_Create_department_table.sql"

echo "Executing 03_Create_category_table.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "03_Create_category_table.sql"
echo "Done with 03_Create_category_table.sql"

echo "Executing 04_Create_product_table.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "04_Create_product_table.sql"
echo "Done with 04_Create_product_table.sql"

echo "Executing 05_Create_index_for_search_vector_field_in_product_table.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "05_Create_index_for_search_vector_field_in_product_table.sql"
echo "Done with 05_Create_index_for_search_vector_field_in_product_table.sql"

echo "Executing 06_Create_product_category_table.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "06_Create_product_category_table.sql"
echo "Done with 06_Create_product_category_table.sql"

echo "Executing 07_Create_shopping_cart_table.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "07_Create_shopping_cart_table.sql"
echo "Done with 07_Create_shopping_cart_table.sql"

echo "Executing 08_Create_shipping_region_table.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "08_Create_shipping_region_table.sql"
echo "Done with 08_Create_shipping_region_table.sql"

echo "Executing 09_Create_shipping_table.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "09_Create_shipping_table.sql"
echo "Done with 09_Create_shipping_table.sql"

echo "Executing 10_Create_tax_table.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "10_Create_tax_table.sql"
echo "Done with 10_Create_tax_table.sql"

echo "Executing 11_Create_customer_table.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "11_Create_customer_table.sql"
echo "Done with 11_Create_customer_table.sql"

echo "Executing 12_Create_orders_table.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "12_Create_orders_table.sql"
echo "Done with 12_Create_orders_table.sql"

echo "Executing 13_Create_order_detail_table.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "13_Create_order_detail_table.sql"
echo "Done with 13_Create_order_detail_table.sql"

echo "Executing 14_Create_audit_table.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "14_Create_audit_table.sql"
echo "Done with 14_Create_audit_table.sql"

echo "Executing 15_Create_review_table.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "15_Create_review_table.sql"
echo "Done with 15_Create_review_table.sql"

echo "Executing 16_Create_hatshop_types.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "16_Create_hatshop_types.sql"
echo "Done with 16_Create_hatshop_types.sql"

echo "Executing 17_Create_department_list_type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "17_Create_department_list_type.sql"
echo "Done with 17_Create_department_list_type.sql"

echo "Executing 18_Create_department_details_type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "18_Create_department_details_type.sql"
echo "Done with 18_Create_department_details_type.sql"

echo "Executing 19_Create_category_list_type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "19_Create_category_list_type.sql"
echo "Done with 19_Create_category_list_type.sql"

echo "Executing 20_Create_category_details_type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "20_Create_category_details_type.sql"
echo "Done with 20_Create_category_details_type.sql"

echo "Executing 21_Create_product_list_type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "21_Create_product_list_type.sql"
echo "Done with 21_Create_product_list_type.sql"

echo "Executing 22_Create_product_details_type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "22_Create_product_details_type.sql"
echo "Done with 22_Create_product_details_type.sql"

echo "Executing 23_Create_department_category_type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "23_Create_department_category_type.sql"
echo "Done with 23_Create_department_category_type.sql"

echo "Executing 24_Create_category_product_type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "24_Create_category_product_type.sql"
echo "Done with 24_Create_category_product_type.sql"

echo "Executing 25_Create_product_info_type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "25_Create_product_info_type.sql"
echo "Done with 25_Create_product_info_type.sql"

echo "Executing 26_Create_product_category_details_type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "26_Create_product_category_details_type.sql"
echo "Done with 26_Create_product_category_details_type.sql"

echo "Executing 27_Create_cart_product_type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "27_Create_cart_product_type.sql"
echo "Done with 27_Create_cart_product_type.sql"

echo "Executing 28_Create_cart_saved_product_type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "28_Create_cart_saved_product_type.sql"
echo "Done with 28_Create_cart_saved_product_type.sql"

echo "Executing 29_Create_order_short_details_type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "29_Create_order_short_details_type.sql"
echo "Done with 29_Create_order_short_details_type.sql"

echo "Executing 30_Create_order_details_type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "30_Create_order_details_type.sql"
echo "Done with 30_Create_order_details_type.sql"

echo "Executing 31_Create_product_recommendation_type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "31_Create_product_recommendation_type.sql"
echo "Done with 31_Create_product_recommendation_type.sql"

echo "Executing 32_Create_customer_login_info_type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "32_Create_customer_login_info_type.sql"
echo "Done with 32_Create_customer_login_info_type.sql"

echo "Executing 33_Create_customer_list_type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "33_Create_customer_list_type.sql"
echo "Done with 33_Create_customer_list_type.sql"

echo "Executing 34_Create_order_info_type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "34_Create_order_info_type.sql"
echo "Done with 34_Create_order_info_type.sql"

echo "Executing 35_Create_review_info_type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "35_Create_review_info_type.sql"
echo "Done with 35_Create_review_info_type.sql"

echo "Executing 36_Create_hatshop_functions.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "36_Create_hatshop_functions.sql"
echo "Done with 36_Create_hatshop_functions.sql"

echo "Executing 37_Create_catalog_get_departments_list.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "37_Create_catalog_get_departments_list.sql"
echo "Done with 37_Create_catalog_get_departments_list.sql"

echo "Executing 38_Create_catalog_get_department_details_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "38_Create_catalog_get_department_details_function.sql"
echo "Done with 38_Create_catalog_get_department_details_function.sql"

echo "Executing 39_Create_catalog_get_categories_list_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "39_Create_catalog_get_categories_list_function.sql"
echo "Done with 39_Create_catalog_get_categories_list_function.sql"

echo "Executing 40_Create_catalog_get_category_details_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "40_Create_catalog_get_category_details_function.sql"
echo "Done with 40_Create_catalog_get_category_details_function.sql"

echo "Executing 41_Create_catalog_count_products_in_category_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "41_Create_catalog_count_products_in_category_function.sql"
echo "Done with 41_Create_catalog_count_products_in_category_function.sql"

echo "Executing 42_Create_catalog_get_products_in_category_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "42_Create_catalog_get_products_in_category_function.sql"
echo "Done with 42_Create_catalog_get_products_in_category_function.sql"

echo "Executing 43_Create_catalog_count_products_on_department_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "43_Create_catalog_count_products_on_department_function.sql"
echo "Done with 43_Create_catalog_count_products_on_department_function.sql"

echo "Executing 44_Create_catalog_get_products_on_department_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "44_Create_catalog_get_products_on_department_function.sql"
echo "Done with 44_Create_catalog_get_products_on_department_function.sql"

echo "Executing 45_Create_catalog_count_products_on_catalog_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "45_Create_catalog_count_products_on_catalog_function.sql"
echo "Done with 45_Create_catalog_count_products_on_catalog_function.sql"

echo "Executing 46_Create_catalog_get_products_on_catalog_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "46_Create_catalog_get_products_on_catalog_function.sql"
echo "Done with 46_Create_catalog_get_products_on_catalog_function.sql"

echo "Executing 47_Create_catalog_get_product_details_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "47_Create_catalog_get_product_details_function.sql"
echo "Done with 47_Create_catalog_get_product_details_function.sql"

echo "Executing 48_Create_catalog_flag_stop_words_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "48_Create_catalog_flag_stop_words_function.sql"
echo "Done with 48_Create_catalog_flag_stop_words_function.sql"

echo "Executing 49_Create_catalog_search_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "49_Create_catalog_search_function.sql"
echo "Done with 49_Create_catalog_search_function.sql"

echo "Executing 50_Create_catalog_get_departments_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "50_Create_catalog_get_departments_function.sql"
echo "Done with 50_Create_catalog_get_departments_function.sql"

echo "Executing 51_Create_catalog_update_department_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "51_Create_catalog_update_department_function.sql"
echo "Done with 51_Create_catalog_update_department_function.sql"

echo "Executing 52_Create_catalog_delete_department_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "52_Create_catalog_delete_department_function.sql"
echo "Done with 52_Create_catalog_delete_department_function.sql"

echo "Executing 53_Create_catalog_add_department_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "53_Create_catalog_add_department_function.sql"
echo "Done with 53_Create_catalog_add_department_function.sql"

echo "Executing 54_Create_catalog_get_department_categories_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "54_Create_catalog_get_department_categories_function.sql"
echo "Done with 54_Create_catalog_get_department_categories_function.sql"

echo "Executing 55_Create_catalog_add_category_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "55_Create_catalog_add_category_function.sql"
echo "Done with 55_Create_catalog_add_category_function.sql"

echo "Executing 56_Create_catalog_delete_category_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "56_Create_catalog_delete_category_function.sql"
echo "Done with 56_Create_catalog_delete_category_function.sql"

echo "Executing 57_Create_catalog_update_category_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "57_Create_catalog_update_category_function.sql"
echo "Done with 57_Create_catalog_update_category_function.sql"

echo "Executing 58_Create_catalog_get_category_products_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "58_Create_catalog_get_category_products_function.sql"
echo "Done with 58_Create_catalog_get_category_products_function.sql"

echo "Executing 59_Create_catalog_add_product_to_category_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "59_Create_catalog_add_product_to_category_function.sql"
echo "Done with 59_Create_catalog_add_product_to_category_function.sql"

echo "Executing 60_Create_catalog_update_product_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "60_Create_catalog_update_product_function.sql"
echo "Done with 60_Create_catalog_update_product_function.sql"

echo "Executing 61_Create_catalog_delete_product_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "61_Create_catalog_delete_product_function.sql"
echo "Done with 61_Create_catalog_delete_product_function.sql"

echo "Executing 62_Create_catalog_remove_product_from_category_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "62_Create_catalog_remove_product_from_category_function.sql"
echo "Done with 62_Create_catalog_remove_product_from_category_function.sql"

echo "Executing 63_Create_catalog_get_categories_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "63_Create_catalog_get_categories_function.sql"
echo "Done with 63_Create_catalog_get_categories_function.sql"

echo "Executing 64_Create_catalog_get_product_info_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "64_Create_catalog_get_product_info_function.sql"
echo "Done with 64_Create_catalog_get_product_info_function.sql"

echo "Executing 65_Create_catalog_get_categories_for_product_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "65_Create_catalog_get_categories_for_product_function.sql"
echo "Done with 65_Create_catalog_get_categories_for_product_function.sql"

echo "Executing 66_Create_catalog_set_product_display_option_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "66_Create_catalog_set_product_display_option_function.sql"
echo "Done with 66_Create_catalog_set_product_display_option_function.sql"

echo "Executing 67_Create_catalog_assign_product_to_category_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "67_Create_catalog_assign_product_to_category_function.sql"
echo "Done with 67_Create_catalog_assign_product_to_category_function.sql"

echo "Executing 68_Create_catalog_move_product_to_category_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "68_Create_catalog_move_product_to_category_function.sql"
echo "Done with 68_Create_catalog_move_product_to_category_function.sql"

echo "Executing 69_Create_catalog_set_image_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "69_Create_catalog_set_image_function.sql"
echo "Done with 69_Create_catalog_set_image_function.sql"

echo "Executing 70_Create_catalog_set_thumbnail_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "70_Create_catalog_set_thumbnail_function.sql"
echo "Done with 70_Create_catalog_set_thumbnail_function.sql"

echo "Executing 71_Create_shopping_cart_add_product_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "71_Create_shopping_cart_add_product_function.sql"
echo "Done with 71_Create_shopping_cart_add_product_function.sql"

echo "Executing 72_Create_shopping_cart_update_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "72_Create_shopping_cart_update_function.sql"
echo "Done with 72_Create_shopping_cart_update_function.sql"

echo "Executing 73_Create_shopping_cart_remove_product_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "73_Create_shopping_cart_remove_product_function.sql"
echo "Done with 73_Create_shopping_cart_remove_product_function.sql"

echo "Executing 74_Create_shopping_cart_get_products_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "74_Create_shopping_cart_get_products_function.sql"
echo "Done with 74_Create_shopping_cart_get_products_function.sql"

echo "Executing 75_Create_shopping_cart_get_saved_products_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "75_Create_shopping_cart_get_saved_products_function.sql"
echo "Done with 75_Create_shopping_cart_get_saved_products_function.sql"

echo "Executing 76_Create_shopping_cart_get_total_amount_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "76_Create_shopping_cart_get_total_amount_function.sql"
echo "Done with 76_Create_shopping_cart_get_total_amount_function.sql"

echo "Executing 77_Create_shopping_cart_save_product_for_later_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "77_Create_shopping_cart_save_product_for_later_function.sql"
echo "Done with 77_Create_shopping_cart_save_product_for_later_function.sql"

echo "Executing 78_Create_shopping_cart_move_product_to_cart_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "78_Create_shopping_cart_move_product_to_cart_function.sql"
echo "Done with 78_Create_shopping_cart_move_product_to_cart_function.sql"

echo "Executing 79_Create_shopping_cart_count_old_carts_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "79_Create_shopping_cart_count_old_carts_function.sql"
echo "Done with 79_Create_shopping_cart_count_old_carts_function.sql"

echo "Executing 80_Create_shopping_cart_delete_old_carts_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "80_Create_shopping_cart_delete_old_carts_function.sql"
echo "Done with 80_Create_shopping_cart_delete_old_carts_function.sql"

echo "Executing 81_Create_shopping_cart_empty_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "81_Create_shopping_cart_empty_function.sql"
echo "Done with 81_Create_shopping_cart_empty_function.sql"

echo "Executing 82_Create_shopping_cart_create_order_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "82_Create_shopping_cart_create_order_function.sql"
echo "Done with 82_Create_shopping_cart_create_order_function.sql"

echo "Executing 83_Create_orders_get_most_recent_orders_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "83_Create_orders_get_most_recent_orders_function.sql"
echo "Done with 83_Create_orders_get_most_recent_orders_function.sql"

echo "Executing 84_Create_orders_get_orders_between_dates_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "84_Create_orders_get_orders_between_dates_function.sql"
echo "Done with 84_Create_orders_get_orders_between_dates_function.sql"

echo "Executing 85_Create_orders_get_orders_by_status_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "85_Create_orders_get_orders_by_status_function.sql"
echo "Done with 85_Create_orders_get_orders_by_status_function.sql"

echo "Executing 86_Create_orders_get_order_info_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "86_Create_orders_get_order_info_function.sql"
echo "Done with 86_Create_orders_get_order_info_function.sql"

echo "Executing 87_Create_orders_get_order_details_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "87_Create_orders_get_order_details_function.sql"
echo "Done with 87_Create_orders_get_order_details_function.sql"

echo "Executing 88_Create_orders_update_order_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "88_Create_orders_update_order_function.sql"
echo "Done with 88_Create_orders_update_order_function.sql"

echo "Executing 89_Create_catalog_get_recommendations_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "89_Create_catalog_get_recommendations_function.sql"
echo "Done with 89_Create_catalog_get_recommendations_function.sql"

echo "Executing 90_Create_shopping_cart_get_recommendations_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "90_Create_shopping_cart_get_recommendations_function.sql"
echo "Done with 90_Create_shopping_cart_get_recommendations_function.sql"

echo "Executing 91_Create_customer_get_login_info_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "91_Create_customer_get_login_info_function.sql"
echo "Done with 91_Create_customer_get_login_info_function.sql"

echo "Executing 92_Create_customer_add_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "92_Create_customer_add_function.sql"
echo "Done with 92_Create_customer_add_function.sql"

echo "Executing 93_Create_customer_get_customer_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "93_Create_customer_get_customer_function.sql"
echo "Done with 93_Create_customer_get_customer_function.sql"

echo "Executing 94_Create_customer_update_account_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "94_Create_customer_update_account_function.sql"
echo "Done with 94_Create_customer_update_account_function.sql"

echo "Executing 95_Create_customer_update_credit_card_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "95_Create_customer_update_credit_card_function.sql"
echo "Done with 95_Create_customer_update_credit_card_function.sql"

echo "Executing 96_Create_customer_get_shipping_regions_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "96_Create_customer_get_shipping_regions_function.sql"
echo "Done with 96_Create_customer_get_shipping_regions_function.sql"

echo "Executing 97_Create_customer_update_address_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "97_Create_customer_update_address_function.sql"
echo "Done with 97_Create_customer_update_address_function.sql"

echo "Executing 98_Create_orders_get_by_customer_id_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "98_Create_orders_get_by_customer_id_function.sql"
echo "Done with 98_Create_orders_get_by_customer_id_function.sql"

echo "Executing 99_Create_orders_get_order_short_details_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "99_Create_orders_get_order_short_details_function.sql"
echo "Done with 99_Create_orders_get_order_short_details_function.sql"

echo "Executing 100_Create_customer_get_customers_list_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "100_Create_customer_get_customers_list_function.sql"
echo "Done with 100_Create_customer_get_customers_list_function.sql"

echo "Executing 101_Create_orders_get_shipping_info_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "101_Create_orders_get_shipping_info_function.sql"
echo "Done with 101_Create_orders_get_shipping_info_function.sql"

echo "Executing 102_Create_orders_create_audit_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "102_Create_orders_create_audit_function.sql"
echo "Done with 102_Create_orders_create_audit_function.sql"

echo "Executing 103_Create_orders_update_status_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "103_Create_orders_update_status_function.sql"
echo "Done with 103_Create_orders_update_status_function.sql"

echo "Executing 104_Create_orders_set_auth_code_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "104_Create_orders_set_auth_code_function.sql"
echo "Done with 104_Create_orders_set_auth_code_function.sql"

echo "Executing 105_Create_orders_set_date_shipped_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "105_Create_orders_set_date_shipped_function.sql"
echo "Done with 105_Create_orders_set_date_shipped_function.sql"

echo "Executing 106_Create_orders_get_audit_trail_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "106_Create_orders_get_audit_trail_function.sql"
echo "Done with 106_Create_orders_get_audit_trail_function.sql"

echo "Executing 107_Create_catalog_get_product_reviews_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "107_Create_catalog_get_product_reviews_function.sql"
echo "Done with 107_Create_catalog_get_product_reviews_function.sql"

echo "Executing 108_Create_catalog_create_product_review_function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "108_Create_catalog_create_product_review_function.sql"
echo "Done with 108_Create_catalog_create_product_review_function.sql"


echo "All SQL scripts executed successfully!"
