#!/bin/bash

# this script requires pg_format to be installed
# Check if dependencies are installed
if ! command -v pg_format &>/dev/null; then
    echo "pg_format is not installed; please install it before running this script"
    exit 1
fi

# instantiate $sql_queries variable from ./migrate.sql
sql_queries=$(cat migrate.sql)

# Format and highlight SQL queries
formatted_sql=$(echo "$sql_queries" | pg_format -)

echo "$formatted_sql" > formatted.sql