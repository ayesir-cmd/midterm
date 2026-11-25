# ERD

The model contains 15 tables:

- Core: `farmers`, `farms`, `crops`, `plantings`, `harvests`
- Lookup: `crop_categories`, `growing_seasons`, `soil_types`, `irrigation_types`, `planting_statuses`, `harvest_quality_grades`, `harvest_remarks`
- Security: `app_roles`, `app_users`, `audit_logs`

Relationships:

- farmers 1:M farms
- farms 1:M plantings
- crops 1:M plantings
- plantings 1:M harvests
- app_roles 1:M app_users
- app_users 1:M audit_logs, with audit actor set to NULL on user deletion
- lookup tables 1:M their dependent records
