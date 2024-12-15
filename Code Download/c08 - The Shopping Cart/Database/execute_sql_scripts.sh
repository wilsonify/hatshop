#!/bin/bash
set -euxo pipefail
DB_PORT="5432"

echo "Executing c0701 - Create catalog_get_departments function.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0701 - Create catalog_get_departments function.sql"
echo "Done with c0701 - Create catalog_get_departments function.sql"

echo "All SQL scripts executed successfully!"
