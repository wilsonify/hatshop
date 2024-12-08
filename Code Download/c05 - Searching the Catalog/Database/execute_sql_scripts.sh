#!/bin/bash
set -euxo pipefail
DB_PORT="5432"

echo "Executing c0401-create-category-table.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0401-create-category-table.sql"
echo "Done with c0401-create-category-table.sql"

echo "Executing c0501-alter-product-table-adding-search_vector-field.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0501-alter-product-table-adding-search_vector-field.sql"
echo "Done with c0501-alter-product-table-adding-search_vector-field.sql"

echo "Executing c0502-create-index-for-search_vector-field-in-product-table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0502-create-index-for-search_vector-field-in-product-table.sql"
echo "Done with c0502-create-index-for-search_vector-field-in-product-table.sql"

echo "Executing c0503-update-newly-added-search_vector-field-from-product-table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0503-update-newly-added-search_vector-field-from-product-table.sql"
echo "Done with c0503-update-newly-added-search_vector-field-from-product-table.sql"

echo "Executing c0504-create-catalog_flag_stop_words-function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0504-create-catalog_flag_stop_words-function.sql"
echo "Done with c0504-create-catalog_flag_stop_words-function.sql"

echo "Executing c0505-function-catalog_count_search_result.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0505-function-catalog_count_search_result.sql"
echo "Done with c0505-function-catalog_count_search_result.sql"

echo "Executing c0506-create-catalog_search-function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0506-create-catalog_search-function.sql"
echo "Done with c0506-create-catalog_search-function.sql"


echo "All SQL scripts executed successfully!"
