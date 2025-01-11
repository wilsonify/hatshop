#!/bin/bash
set -euxo pipefail
DB_PORT="5432"

echo "Executing c1101_create_shipping_region_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1101_create_shipping_region_table.sql"
echo "Done with c1101_create_shipping_region_table.sql"

echo "Executing c1102_populate_shipping_region_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1102_populate_shipping_region_table.sql"
echo "Done with c1102_populate_shipping_region_table.sql"

echo "Executing c1103_update_the_sequence.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1103_update_the_sequence.sql"
echo "Done with c1103_update_the_sequence.sql"

echo "Executing c1104_create_customer_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1104_create_customer_table.sql"
echo "Done with c1104_create_customer_table.sql"

echo "Executing c1105_create_customer_login_info_type.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1105_create_customer_login_info_type.sql"
echo "Done with c1105_create_customer_login_info_type.sql"

echo "Executing c1106_create_customer_get_login_info_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1106_create_customer_get_login_info_function.sql"
echo "Done with c1106_create_customer_get_login_info_function.sql"

echo "Executing c1107_create_customer_add_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1107_create_customer_add_function.sql"
echo "Done with c1107_create_customer_add_function.sql"

echo "Executing c1108_create_customer_get_customer_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1108_create_customer_get_customer_function.sql"
echo "Done with c1108_create_customer_get_customer_function.sql"

echo "Executing c1109_create_customer_update_account_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1109_create_customer_update_account_function.sql"
echo "Done with c1109_create_customer_update_account_function.sql"

echo "Executing c1110_create_customer_update_credit_card_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1110_create_customer_update_credit_card_function.sql"
echo "Done with c1110_create_customer_update_credit_card_function.sql"

echo "Executing c1111_create_customer_get_shipping_regions_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1111_create_customer_get_shipping_regions_function.sql"
echo "Done with c1111_create_customer_get_shipping_regions_function.sql"

echo "Executing c1112_create_customer_update_address_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1112_create_customer_update_address_function.sql"
echo "Done with c1112_create_customer_update_address_function.sql"


echo "All SQL scripts executed successfully!"
