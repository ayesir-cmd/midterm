-- Record counts and primary-key uniqueness.
SELECT 'crops' AS table_name, count(*) AS records, count(DISTINCT crop_id) AS distinct_ids FROM public.crops
UNION ALL
SELECT 'farmers', count(*), count(DISTINCT farmer_id) FROM public.farmers
UNION ALL
SELECT 'farms', count(*), count(DISTINCT farm_id) FROM public.farms
UNION ALL
SELECT 'plantings', count(*), count(DISTINCT planting_id) FROM public.plantings
UNION ALL
SELECT 'harvests', count(*), count(DISTINCT harvest_id) FROM public.harvests;

-- Foreign-key violations. Every result should be zero.
SELECT 'crop -> category/season' AS relationship, count(*) AS violations
FROM public.crops c
LEFT JOIN public.crop_categories cc ON cc.category_id = c.category_id
LEFT JOIN public.growing_seasons gs ON gs.season_id = c.season_id
WHERE cc.category_id IS NULL OR gs.season_id IS NULL
UNION ALL
SELECT 'farm -> farmer/soil/irrigation', count(*)
FROM public.farms f
LEFT JOIN public.farmers r ON r.farmer_id = f.farmer_id
LEFT JOIN public.soil_types s ON s.soil_type_id = f.soil_type_id
LEFT JOIN public.irrigation_types i ON i.irrigation_type_id = f.irrigation_type_id
WHERE r.farmer_id IS NULL OR s.soil_type_id IS NULL OR i.irrigation_type_id IS NULL
UNION ALL
SELECT 'planting -> farm/crop/status', count(*)
FROM public.plantings p
LEFT JOIN public.farms f ON f.farm_id = p.farm_id
LEFT JOIN public.crops c ON c.crop_id = p.crop_id
LEFT JOIN public.planting_statuses s ON s.status_id = p.status_id
WHERE f.farm_id IS NULL OR c.crop_id IS NULL OR s.status_id IS NULL
UNION ALL
SELECT 'harvest -> planting/grade/remark', count(*)
FROM public.harvests h
LEFT JOIN public.plantings p ON p.planting_id = h.planting_id
LEFT JOIN public.harvest_quality_grades q ON q.quality_grade_id = h.quality_grade_id
LEFT JOIN public.harvest_remarks r ON r.remark_id = h.remark_id
WHERE p.planting_id IS NULL OR q.quality_grade_id IS NULL OR r.remark_id IS NULL;

-- Required-value violations. Every result should be zero.
SELECT 'crops' AS table_name, count(*) AS rows_with_missing_values
FROM public.crops
WHERE crop_name IS NULL OR variety IS NULL OR category_id IS NULL
   OR growing_period_days IS NULL OR season_id IS NULL
UNION ALL
SELECT 'farmers', count(*)
FROM public.farmers
WHERE first_name IS NULL OR last_name IS NULL OR address IS NULL OR date_registered IS NULL
UNION ALL
SELECT 'farms', count(*)
FROM public.farms
WHERE farmer_id IS NULL OR farm_name IS NULL OR location IS NULL
   OR area_hectares IS NULL OR soil_type_id IS NULL OR irrigation_type_id IS NULL
UNION ALL
SELECT 'plantings', count(*)
FROM public.plantings
WHERE farm_id IS NULL OR crop_id IS NULL OR planting_date IS NULL
   OR area_planted_ha IS NULL OR seed_quantity_kg IS NULL
   OR expected_harvest_date IS NULL OR status_id IS NULL
UNION ALL
SELECT 'harvests', count(*)
FROM public.harvests
WHERE planting_id IS NULL OR harvest_date IS NULL OR quantity_kg IS NULL
   OR selling_price_per_kg IS NULL OR quality_grade_id IS NULL OR remark_id IS NULL;

-- Missing IDs within the ranges visible in the supplied document.
SELECT 'farm' AS table_name, ids.id AS missing_id
FROM generate_series(1, 15) AS ids(id)
WHERE NOT EXISTS (SELECT 1 FROM public.farms f WHERE f.farm_id = ids.id)
UNION ALL
SELECT 'planting', ids.id
FROM generate_series(1, 28) AS ids(id)
WHERE NOT EXISTS (SELECT 1 FROM public.plantings p WHERE p.planting_id = ids.id)
UNION ALL
SELECT 'harvest', ids.id
FROM generate_series(1, 20) AS ids(id)
WHERE NOT EXISTS (SELECT 1 FROM public.harvests h WHERE h.harvest_id = ids.id)
ORDER BY table_name, missing_id;

-- Database-backed application list queries should sort each module by its ID.
SELECT crop_id FROM public.crops ORDER BY crop_id ASC;
SELECT farmer_id FROM public.farmers ORDER BY farmer_id ASC;
SELECT farm_id FROM public.farms ORDER BY farm_id ASC;
SELECT planting_id FROM public.plantings ORDER BY planting_id ASC;
SELECT harvest_id FROM public.harvests ORDER BY harvest_id ASC;
