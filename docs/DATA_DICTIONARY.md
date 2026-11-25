# Data dictionary

The exact schema, types, primary keys, foreign keys, checks, and indexes are maintained in `database/migrations/001_create_ais_schema.sql` and `002_complete_schema_indexes.sql`.

Key units and assumptions:

- `farms.area_hectares`: hectares
- `plantings.area_planted_ha`: hectares
- `plantings.seed_quantity_kg`: kilograms, confirmed only as an SDD assumption
- `harvests.quantity_kg`: kilograms
- `selling_price_per_kg`: Philippine pesos per kilogram, shown explicitly as an estimate
- Proposed planting statuses are Planned, Ready for Harvest, and Cancelled; the source explicitly confirms Growing and Harvested.
