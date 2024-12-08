#!/bin/bash
set -euxo pipefail
DB_PORT="5432"

echo "Executing c0301-CREATE-TABLE"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0301-CREATE-TABLE.sql"
echo "Done with c0301-CREATE-TABLE.sql"

echo "Executing c0302-INSERT-INTO"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0302-INSERT-INTO.sql"
echo "Done with c0302-INSERT-INTO.sql"

echo "Executing c0303-ALTER-SEQUENCE"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0303-ALTER-SEQUENCE.sql"
echo "Done with c0303-ALTER-SEQUENCE.sql"

echo "Executing c0304-CREATE-TYPE"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0304-CREATE-TYPE.sql"
echo "Done with c0304-CREATE-TYPE.sql"

echo "Executing c0305-CREATE-FUNCTION"
psql -U "$HATSHOP_DB_USERNAME" -h "$HATSHOP_DB_SERVER" -p "$DB_PORT" -d "$HATSHOP_DB_DATABASE" -f "c0305-CREATE-FUNCTION.sql"
echo "Done with c0305-CREATE-FUNCTION.sql"


echo "All SQL scripts executed successfully!"
