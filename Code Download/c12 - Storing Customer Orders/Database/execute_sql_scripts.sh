#!/bin/bash
set -euxo pipefail
DB_PORT="5432"

echo "Executing c1101_create_shipping_region_table.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c1101_create_shipping_region_table.sql"
echo "Done with c1101_create_shipping_region_table.sql"


echo "All SQL scripts executed successfully!"
