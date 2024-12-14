#!/bin/bash
set -euxo pipefail
DB_PORT="5432"


echo "Executing c0501-alter-product-table-adding-search_vector-field.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0501-alter-product-table-adding-search_vector-field.sql"
echo "Done with c0501-alter-product-table-adding-search_vector-field.sql"



echo "All SQL scripts executed successfully!"
