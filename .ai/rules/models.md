---
paths:
  - 'app/Models/*.php'
---

# Models

## Team is the product tenant; Stancl Tenant is infrastructure
Tenant = Team for identification and lifecycle (slug in the path, current_team_id). Do not make Team extend Stancl's BaseTenant. App\Models\Tenant lives on the central DB with team_id; CreateTeam/ProvisionTeamTenant creates the tenant database, deleting the Team deletes that database. Domain data (mesas, reservas) belongs in the tenant DB. Users, teams, memberships, invitations, sessions, cache, and jobs stay on the central connection. Identify by path slug, never subdomain in v1. Team::factory() must not provision a database.
