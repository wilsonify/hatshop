#!/bin/bash
set -euxo pipefail
DB_PORT="5432"


echo "Executing c0701 - Create catalog_get_departments function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0701 - Create catalog_get_departments function.sql"
echo "Done with c0701 - Create catalog_get_departments function.sql"

echo "Executing c0702 - Create catalog_update_department function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0702 - Create catalog_update_department function.sql"
echo "Done with c0702 - Create catalog_update_department function.sql"

echo "Executing c0703 - Create catalog_delete_department function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0703 - Create catalog_delete_department function.sql"
echo "Done with c0703 - Create catalog_delete_department function.sql"

echo "Executing c0704 - Create catalog_add_department function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0704 - Create catalog_add_department function.sql"
echo "Done with c0704 - Create catalog_add_department function.sql"

echo "Executing c0705 - Create department_category type.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0705 - Create department_category type.sql"
echo "Done with c0705 - Create department_category type.sql"

echo "Executing c0708 - Create catalog_get_department_categories function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0708 - Create catalog_get_department_categories function.sql"
echo "Done with c0708 - Create catalog_get_department_categories function.sql"

echo "Executing c0709 - Create catalog_add_category function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0709 - Create catalog_add_category function.sql"
echo "Done with c0709 - Create catalog_add_category function.sql"

echo "Executing c0710 - Create catalog_delete_category function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0710 - Create catalog_delete_category function.sql"
echo "Done with c0710 - Create catalog_delete_category function.sql"

echo "Executing c0711 - Create catalog_update_category function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0711 - Create catalog_update_category function.sql"
echo "Done with c0711 - Create catalog_update_category function.sql"

echo "Executing c0712 - Create category_product type.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0712 - Create category_product type.sql"
echo "Done with c0712 - Create category_product type.sql"

echo "Executing c0713 - Create catalog_get_category_products function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0713 - Create catalog_get_category_products function.sql"
echo "Done with c0713 - Create catalog_get_category_products function.sql"

echo "Executing c0714 - Create catalog_add_product_to_category function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0714 - Create catalog_add_product_to_category function.sql"
echo "Done with c0714 - Create catalog_add_product_to_category function.sql"

echo "Executing c0715 - Create catalog_update_product function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0715 - Create catalog_update_product function.sql"
echo "Done with c0715 - Create catalog_update_product function.sql"

echo "Executing c0716 - Create catalog_delete_product function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0716 - Create catalog_delete_product function.sql"
echo "Done with c0716 - Create catalog_delete_product function.sql"

echo "Executing c0717 - Create catalog_remove_product_from_category function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0717 - Create catalog_remove_product_from_category function.sql"
echo "Done with c0717 - Create catalog_remove_product_from_category function.sql"

echo "Executing c0718 - Create catalog_get_categories function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0718 - Create catalog_get_categories function.sql"
echo "Done with c0718 - Create catalog_get_categories function.sql"

echo "Executing c0719 - Create product_info type.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0719 - Create product_info type.sql"
echo "Done with c0719 - Create product_info type.sql"

echo "Executing c0720 - Create catalog_get_product_info function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0720 - Create catalog_get_product_info function.sql"
echo "Done with c0720 - Create catalog_get_product_info function.sql"

echo "Executing c0721 - Create product_category_details type.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0721 - Create product_category_details type.sql"
echo "Done with c0721 - Create product_category_details type.sql"

echo "Executing c0722 - Create catalog_get_categories_for_product function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0722 - Create catalog_get_categories_for_product function.sql"
echo "Done with c0722 - Create catalog_get_categories_for_product function.sql"

echo "Executing c0723 - Create catalog_set_product_display_option function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0723 - Create catalog_set_product_display_option function.sql"
echo "Done with c0723 - Create catalog_set_product_display_option function.sql"

echo "Executing c0724 - Create catalog_assign_product_to_category function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0724 - Create catalog_assign_product_to_category function.sql"
echo "Done with c0724 - Create catalog_assign_product_to_category function.sql"

echo "Executing c0725 - Create catalog_move_product_to_category function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0725 - Create catalog_move_product_to_category function.sql"
echo "Done with c0725 - Create catalog_move_product_to_category function.sql"

echo "Executing c0726 - Create catalog_set_image function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0726 - Create catalog_set_image function.sql"
echo "Done with c0726 - Create catalog_set_image function.sql"

echo "Executing c0727 - Create catalog_set_thumbnail function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0727 - Create catalog_set_thumbnail function.sql"
echo "Done with c0727 - Create catalog_set_thumbnail function.sql"


echo "All SQL scripts executed successfully!"
