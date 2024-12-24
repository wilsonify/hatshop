#!/bin/bash
set -euxo pipefail
DB_PORT="5432"


echo "Executing c0201-CREATE-USER.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0201-CREATE-USER.sql"
echo "Done with c0201-CREATE-USER.sql"

echo "Executing c0202-CREATE-DATABASE.sql"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0202-CREATE-DATABASE.sql"
echo "Done with c0202-CREATE-DATABASE.sql"



echo "All SQL scripts executed successfully!"
