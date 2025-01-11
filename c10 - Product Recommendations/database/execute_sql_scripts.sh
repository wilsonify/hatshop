#!/bin/bash
set -euxo pipefail
DB_PORT="5432"


echo "Executing c1001_create_product_recommendation_type.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1001_create_product_recommendation_type.sql"
echo "Done with c1001_create_product_recommendation_type.sql"

echo "Executing c1002_create_catalog_get_recommendations_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1002_create_catalog_get_recommendations_function.sql"
echo "Done with c1002_create_catalog_get_recommendations_function.sql"

echo "Executing c1003_create_shopping_cart_get_recommendations_function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1003_create_shopping_cart_get_recommendations_function.sql"
echo "Done with c1003_create_shopping_cart_get_recommendations_function.sql"


echo "All SQL scripts executed successfully!"
