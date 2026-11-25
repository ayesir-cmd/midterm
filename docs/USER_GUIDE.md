# User guide

Administrator and Staff sign in through `/login`. A forced first-login password change appears when required.

Dependency order for demo data:

1. Create or seed farmers.
2. Create farms.
3. Create crops.
4. Create plantings.
5. Create harvests.

Staff can search and manage operational records and reports but cannot delete records or access Users, Lookups, or Audit Logs. Administrator-only account and audit controls are enforced by PHP middleware.
