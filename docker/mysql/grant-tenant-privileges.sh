#!/usr/bin/env bash

# The default Sail user can only use MYSQL_DATABASE. Tenant databases
# (CREATE DATABASE tenant{id}) need global privileges. See:
# https://tenancyforlaravel.com/docs/v3/integrations/sail/

if [ -n "$MYSQL_USER" ]; then
mysql --user=root --password="$MYSQL_ROOT_PASSWORD" <<-EOSQL
    GRANT ALL PRIVILEGES ON *.* TO '$MYSQL_USER'@'%' WITH GRANT OPTION;
    FLUSH PRIVILEGES;
EOSQL
fi
