#!/bin/bash
set -euxo pipefail
DB_PORT="5432"

echo "Executing c0401-create-category-table.sql..."
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0401-create-category-table.sql"
echo "Done with c0401-create-category-table.sql"


echo "All SQL scripts executed successfully!"
