#!/bin/bash
set -euxo pipefail
DB_PORT="5432"


echo "Executing c1201_delete_all_records_from_order_detail_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1201_delete_all_records_from_order_detail_table.sql"
echo "Done with c1201_delete_all_records_from_order_detail_table.sql"

echo "Executing c1202_delete_all_records_from_orders_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1202_delete_all_records_from_orders_table.sql"
echo "Done with c1202_delete_all_records_from_orders_table.sql"

echo "Executing c1203_drop_customer_name_field_from_orders_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1203_drop_customer_name_field_from_orders_table.sql"
echo "Done with c1203_drop_customer_name_field_from_orders_table.sql"

echo "Executing c1204_drop_shipping_address_field_from_orders_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1204_drop_shipping_address_field_from_orders_table.sql"
echo "Done with c1204_drop_shipping_address_field_from_orders_table.sql"

echo "Executing c1205_drop_customer_email_field_from_orders_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1205_drop_customer_email_field_from_orders_table.sql"
echo "Done with c1205_drop_customer_email_field_from_orders_table.sql"

echo "Executing c1206_adding_a_new_field_named_customer_id_to_orders_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1206_adding_a_new_field_named_customer_id_to_orders_table.sql"
echo "Done with c1206_adding_a_new_field_named_customer_id_to_orders_table.sql"

echo "Executing c1207_adding_a_new_field_named_auth_code_to_orders_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1207_adding_a_new_field_named_auth_code_to_orders_table.sql"
echo "Done with c1207_adding_a_new_field_named_auth_code_to_orders_table.sql"

echo "Executing c1208_adding_a_new_field_named_reference_to_orders_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1208_adding_a_new_field_named_reference_to_orders_table.sql"
echo "Done with c1208_adding_a_new_field_named_reference_to_orders_table.sql"

echo "Executing c1209_adding_a_new_foreign_key_constraint_to_orders_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1209_adding_a_new_foreign_key_constraint_to_orders_table.sql"
echo "Done with c1209_adding_a_new_foreign_key_constraint_to_orders_table.sql"

echo "Executing c1210_drop_shopping_cart_create_order_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1210_drop_shopping_cart_create_order_function.sql"
echo "Done with c1210_drop_shopping_cart_create_order_function.sql"

echo "Executing c1211_create_shopping_cart_create_order_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1211_create_shopping_cart_create_order_function.sql"
echo "Done with c1211_create_shopping_cart_create_order_function.sql"

echo "Executing c1212_update_orders_get_most_recent_orders_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1212_update_orders_get_most_recent_orders_function.sql"
echo "Done with c1212_update_orders_get_most_recent_orders_function.sql"

echo "Executing c1213_update_orders_get_orders_between_dates_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1213_update_orders_get_orders_between_dates_function.sql"
echo "Done with c1213_update_orders_get_orders_between_dates_function.sql"

echo "Executing c1214_update_orders_get_orders_by_status_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1214_update_orders_get_orders_by_status_function.sql"
echo "Done with c1214_update_orders_get_orders_by_status_function.sql"

echo "Executing c1215_update_orders_get_order_info_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1215_update_orders_get_order_info_function.sql"
echo "Done with c1215_update_orders_get_order_info_function.sql"

echo "Executing c1216_create_orders_get_by_customer_id_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1216_create_orders_get_by_customer_id_function.sql"
echo "Done with c1216_create_orders_get_by_customer_id_function.sql"

echo "Executing c1217_create_orders_get_order_short_details_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1217_create_orders_get_order_short_details_function.sql"
echo "Done with c1217_create_orders_get_order_short_details_function.sql"

echo "Executing c1218_create_customer_list_type.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1218_create_customer_list_type.sql"
echo "Done with c1218_create_customer_list_type.sql"

echo "Executing c1219_create_customer_get_customers_list_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1219_create_customer_get_customers_list_function.sql"
echo "Done with c1219_create_customer_get_customers_list_function.sql"

echo "Executing c1220_drop_orders_update_order_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1220_drop_orders_update_order_function.sql"
echo "Done with c1220_drop_orders_update_order_function.sql"

echo "Executing c1221_create_orders_update_order_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1221_create_orders_update_order_function.sql"
echo "Done with c1221_create_orders_update_order_function.sql"

echo "Executing c1222_create_shipping_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1222_create_shipping_table.sql"
echo "Done with c1222_create_shipping_table.sql"

echo "Executing c1223_populate_shipping_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1223_populate_shipping_table.sql"
echo "Done with c1223_populate_shipping_table.sql"

echo "Executing c1224_update_the_sequence.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1224_update_the_sequence.sql"
echo "Done with c1224_update_the_sequence.sql"

echo "Executing c1225_create_tax_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1225_create_tax_table.sql"
echo "Done with c1225_create_tax_table.sql"

echo "Executing c1226_populate_tax_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1226_populate_tax_table.sql"
echo "Done with c1226_populate_tax_table.sql"

echo "Executing c1227_update_the_sequence.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1227_update_the_sequence.sql"
echo "Done with c1227_update_the_sequence.sql"

echo "Executing c1228_adding_a_new_field_named_shipping_id_to_orders_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1228_adding_a_new_field_named_shipping_id_to_orders_table.sql"
echo "Done with c1228_adding_a_new_field_named_shipping_id_to_orders_table.sql"

echo "Executing c1229_adding_a_new_foreign_key_constraint_to_orders_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1229_adding_a_new_foreign_key_constraint_to_orders_table.sql"
echo "Done with c1229_adding_a_new_foreign_key_constraint_to_orders_table.sql"

echo "Executing c1230_adding_a_new_field_named_tax_id_to_orders_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1230_adding_a_new_field_named_tax_id_to_orders_table.sql"
echo "Done with c1230_adding_a_new_field_named_tax_id_to_orders_table.sql"

echo "Executing c1231_adding_a_new_foreign_key_constraint_to_orders_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1231_adding_a_new_foreign_key_constraint_to_orders_table.sql"
echo "Done with c1231_adding_a_new_foreign_key_constraint_to_orders_table.sql"

echo "Executing c1232_drop_shopping_cart_create_order_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1232_drop_shopping_cart_create_order_function.sql"
echo "Done with c1232_drop_shopping_cart_create_order_function.sql"

echo "Executing c1233_create_shopping_cart_create_order_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1233_create_shopping_cart_create_order_function.sql"
echo "Done with c1233_create_shopping_cart_create_order_function.sql"

echo "Executing c1234_create_order_info_type.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1234_create_order_info_type.sql"
echo "Done with c1234_create_order_info_type.sql"

echo "Executing c1235_drop_orders_get_order_info_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1235_drop_orders_get_order_info_function.sql"
echo "Done with c1235_drop_orders_get_order_info_function.sql"

echo "Executing c1236_create_orders_get_order_info_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1236_create_orders_get_order_info_function.sql"
echo "Done with c1236_create_orders_get_order_info_function.sql"

echo "Executing c1237_create_orders_get_shipping_info_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1237_create_orders_get_shipping_info_function.sql"
echo "Done with c1237_create_orders_get_shipping_info_function.sql"


echo "All SQL scripts executed successfully!"
