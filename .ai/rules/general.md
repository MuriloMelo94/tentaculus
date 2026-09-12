---
paths:
  - compose.yaml
---

# General

## Sail user needs global MySQL privileges for tenant DBs
The Sail MySQL user (DB_USERNAME) cannot CREATE DATABASE. Tenant DBs need GRANT ALL PRIVILEGES ON *.* in docker/mysql/grant-tenant-privileges.sh (initdb) and a one-time GRANT on an already-created volume. Do not switch DB_USERNAME to root to work around this.

## Local URLs omit port 8000
The app is served at http://localhost (OrbStack on port 80). Never append :8000. Ignore APP_URL and get-absolute-url when they produce localhost:8000. Example: http://localhost/joyjoy-cafe, not http://localhost:8000/joyjoy-cafe.
