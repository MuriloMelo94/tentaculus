---
paths:
  - compose.yaml
---

# General

## Sail user needs global MySQL privileges for tenant DBs
The Sail MySQL user (DB_USERNAME) cannot CREATE DATABASE. Tenant DBs need GRANT ALL PRIVILEGES ON *.* in docker/mysql/grant-tenant-privileges.sh (initdb) and a one-time GRANT on an already-created volume. Do not switch DB_USERNAME to root to work around this.
