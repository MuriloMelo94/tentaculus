---
paths:
  - 'routes/web.php'
  - 'app/Http/Middleware/*.php'
---

# Public vs operation tenant routes

Public and customer routes identify the tenant by path slug and use only `InitializeTenancyForTeam`. `EnsureTeamMembership` belongs on staff/operation routes (`dashboard` and later mesa/reserva management). A customer can authenticate and reserve on the luderia in the URL without being a team member and without having a team of their own.
