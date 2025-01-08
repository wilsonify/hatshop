#!/bin/bash
set -euxo pipefail
DB_PORT="5432"

echo "Executing 01_create_orders_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0901_create_orders_table.sql"
echo "Done with 01_create_orders_table.sql"

echo "Executing 02_create_order_detail_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0902_create_order_detail_table.sql"
echo "Done with 02_create_order_detail_table.sql"

echo "Executing 03_create_shopping_cart_empty_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0903_create_shopping_cart_empty_function.sql"
echo "Done with 03_create_shopping_cart_empty_function.sql"

echo "Executing 04_create_shopping_cart_create_order_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0904_create_shopping_cart_create_order_function.sql"
echo "Done with 04_create_shopping_cart_create_order_function.sql"

echo "Executing 05_create_order_short_details_type.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0905_create_order_short_details_type.sql"
echo "Done with 05_create_order_short_details_type.sql"

echo "Executing 06_create_orders_get_most_recent_orders_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0906_create_orders_get_most_recent_orders_function.sql"
echo "Done with 06_create_orders_get_most_recent_orders_function.sql"

echo "Executing 07_create_orders_get_orders_between_dates_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0907_create_orders_get_orders_between_dates_function.sql"
echo "Done with 07_create_orders_get_orders_between_dates_function.sql"

echo "Executing 08_create_orders_get_orders_by_status_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0908_create_orders_get_orders_by_status_function.sql"
echo "Done with 08_create_orders_get_orders_by_status_function.sql"

echo "Executing 09_create_orders_get_order_info_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0909_create_orders_get_order_info_function.sql"
echo "Done with 09_create_orders_get_order_info_function.sql"

echo "Executing 10_create_order_details_type.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0910_create_order_details_type.sql"
echo "Done with 10_create_order_details_type.sql"

echo "Executing 11_create_orders_get_order_details_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0911_create_orders_get_order_details_function.sql"
echo "Done with 11_create_orders_get_order_details_function.sql"

echo "Executing 12_create_orders_update_order_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0912_create_orders_update_order_function.sql"
echo "Done with 12_create_orders_update_order_function.sql"


echo "All SQL scripts executed successfully!"
