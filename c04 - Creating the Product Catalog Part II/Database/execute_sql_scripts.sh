#!/bin/bash
set -euxo pipefail
DB_PORT="5432"

echo "Executing c0401-create-category-table.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0401-create-category-table.sql"
echo "Done with c0401-create-category-table.sql"

echo "Executing c0402-populate-category-table.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0402-populate-category-table.sql"
echo "Done with c0402-populate-category-table.sql"

echo "Executing c0403-update-sequence.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0403-update-sequence.sql"
echo "Done with c0403-update-sequence.sql"

echo "Executing c0404-create-product-table.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0404-create-product-table.sql"
echo "Done with c0404-create-product-table.sql"

echo "Executing c0405-populate-product-table.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0405-populate-product-table.sql"
echo "Done with c0405-populate-product-table.sql"

echo "Executing c0406-update-sequence.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0406-update-sequence.sql"
echo "Done with c0406-update-sequence.sql"

echo "Executing c0407-create-product_category-table.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0407-create-product_category-table.sql"
echo "Done with c0407-create-product_category-table.sql"

echo "Executing c0408-populate-product_category-table.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0408-populate-product_category-table.sql"
echo "Done with c0408-populate-product_category-table.sql"

echo "Executing c0409-create-department_details-type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0409-create-department_details-type.sql"
echo "Done with c0409-create-department_details-type.sql"

echo "Executing c0410-create-catalog_get_department_details-function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0410-create-catalog_get_department_details-function.sql"
echo "Done with c0410-create-catalog_get_department_details-function.sql"

echo "Executing c0411-create-category_list-type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0411-create-category_list-type.sql"
echo "Done with c0411-create-category_list-type.sql"

echo "Executing c0412-create-catalog_get_categories_list-function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0412-create-catalog_get_categories_list-function.sql"
echo "Done with c0412-create-catalog_get_categories_list-function.sql"

echo "Executing c0413-create-category_details-type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0413-create-category_details-type.sql"
echo "Done with c0413-create-category_details-type.sql"

echo "Executing c0414-create-catalog_get_category_details-function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0414-create-catalog_get_category_details-function.sql"
echo "Done with c0414-create-catalog_get_category_details-function.sql"

echo "Executing c0415-create-catalog_count_products_in_category-function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0415-create-catalog_count_products_in_category-function.sql"
echo "Done with c0415-create-catalog_count_products_in_category-function.sql"

echo "Executing c0416-create-product_list-type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0416-create-product_list-type.sql"
echo "Done with c0416-create-product_list-type.sql"

echo "Executing c0417-create-catalog_get_products_in_category-function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0417-create-catalog_get_products_in_category-function.sql"
echo "Done with c0417-create-catalog_get_products_in_category-function.sql"

echo "Executing c0418-create-catalog_count_products_on_department-function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0418-create-catalog_count_products_on_department-function.sql"
echo "Done with c0418-create-catalog_count_products_on_department-function.sql"

echo "Executing c0419-create-catalog_get_products_on_department-function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0419-create-catalog_get_products_on_department-function.sql"
echo "Done with c0419-create-catalog_get_products_on_department-function.sql"

echo "Executing c0420-create-catalog_count_products_on_catalog-function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0420-create-catalog_count_products_on_catalog-function.sql"
echo "Done with c0420-create-catalog_count_products_on_catalog-function.sql"

echo "Executing c0421-create-catalog_get_products_on_catalog-function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0421-create-catalog_get_products_on_catalog-function.sql"
echo "Done with c0421-create-catalog_get_products_on_catalog-function.sql"

echo "Executing c0422-create-product_details-type.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0422-create-product_details-type.sql"
echo "Done with c0422-create-product_details-type.sql"

echo "Executing c0423-create-catalog_get_product_details-function.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0423-create-catalog_get_product_details-function.sql"
echo "Done with c0423-create-catalog_get_product_details-function.sql"

echo "Executing c0424-populate_product.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0424-populate_product.sql"
echo "Done with c0424-populate_product.sql"

echo "Executing c0425-populate_product_category.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0425-populate_product_category.sql"
echo "Done with c0425-populate_product_category.sql"

echo "All SQL scripts executed successfully!"
