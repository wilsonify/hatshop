#!/bin/bash
set -euxo pipefail
DB_PORT="5432"


echo "Executing c0801_create_shopping_cart_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0801_create_shopping_cart_table.sql"
echo "Done with c0801_create_shopping_cart_table.sql"

echo "Executing c0802_create_shopping_cart_add_product_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0802_create_shopping_cart_add_product_function.sql"
echo "Done with c0802_create_shopping_cart_add_product_function.sql"

echo "Executing c0803_create_shopping_cart_update_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0803_create_shopping_cart_update_function.sql"
echo "Done with c0803_create_shopping_cart_update_function.sql"

echo "Executing c0804_create_shopping_cart_remove_product_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0804_create_shopping_cart_remove_product_function.sql"
echo "Done with c0804_create_shopping_cart_remove_product_function.sql"

echo "Executing c0805_create_cart_product_type.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0805_create_cart_product_type.sql"
echo "Done with c0805_create_cart_product_type.sql"

echo "Executing c0806_create_shopping_cart_get_products_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0806_create_shopping_cart_get_products_function.sql"
echo "Done with c0806_create_shopping_cart_get_products_function.sql"

echo "Executing c0807_create_cart_saved_product_type.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0807_create_cart_saved_product_type.sql"
echo "Done with c0807_create_cart_saved_product_type.sql"

echo "Executing c0808_create_shopping_cart_get_saved_products_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0808_create_shopping_cart_get_saved_products_function.sql"
echo "Done with c0808_create_shopping_cart_get_saved_products_function.sql"

echo "Executing c0809_create_shopping_cart_get_total_amount_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0809_create_shopping_cart_get_total_amount_function.sql"
echo "Done with c0809_create_shopping_cart_get_total_amount_function.sql"

echo "Executing c0810_create_shopping_cart_save_product_for_later_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0810_create_shopping_cart_save_product_for_later_function.sql"
echo "Done with c0810_create_shopping_cart_save_product_for_later_function.sql"

echo "Executing c0811_create_shopping_cart_move_product_to_cart_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0811_create_shopping_cart_move_product_to_cart_function.sql"
echo "Done with c0811_create_shopping_cart_move_product_to_cart_function.sql"

echo "Executing c0812_updates_catalog_delete_product_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0812_updates_catalog_delete_product_function.sql"
echo "Done with c0812_updates_catalog_delete_product_function.sql"

echo "Executing c0813_create_shopping_cart_count_old_carts_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0813_create_shopping_cart_count_old_carts_function.sql"
echo "Done with c0813_create_shopping_cart_count_old_carts_function.sql"

echo "Executing c0814_create_shopping_cart_delete_old_carts_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0814_create_shopping_cart_delete_old_carts_function.sql"
echo "Done with c0814_create_shopping_cart_delete_old_carts_function.sql"


echo "All SQL scripts executed successfully!"
