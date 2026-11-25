-- Source: screenshots embedded in the supplied data.docx.
-- The current schema stores categories and descriptive labels in lookup tables.
-- This one-time import fails rather than overwriting any existing core rows.
BEGIN;

DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM public.crops)
       OR EXISTS (SELECT 1 FROM public.farmers)
       OR EXISTS (SELECT 1 FROM public.farms)
       OR EXISTS (SELECT 1 FROM public.plantings)
       OR EXISTS (SELECT 1 FROM public.harvests) THEN
        RAISE EXCEPTION 'Core tables are not empty; compare existing records before importing document rows';
    END IF;
END
$$;

INSERT INTO public.harvest_remarks (remark_id, remark_description)
OVERRIDING SYSTEM VALUE
VALUES
    (6, 'High quality harvest'),
    (7, 'Good harvest'),
    (8, 'Acceptable quality')
ON CONFLICT (remark_description) DO NOTHING;

INSERT INTO public.crops
    (crop_id, crop_name, variety, category_id, growing_period_days, season_id)
OVERRIDING SYSTEM VALUE
VALUES
    (1, 'Rice',        'NSIC Rc 222',  1, 115, 1),
    (2, 'Corn',        'Yellow Corn',  1, 100, 2),
    (3, 'Tomato',      'Diamante Max', 2,  90, 2),
    (4, 'Eggplant',    'Morena',       2, 100, 3),
    (5, 'Banana',      'Lakatan',      3, 300, 3),
    (6, 'Mango',       'Carabao',      3, 150, 2),
    (7, 'Peanut',      'BPI Pn 9',     4, 110, 2),
    (8, 'Cassava',     'Golden Yellow',5, 240, 3),
    (9, 'Sweet Potato','NSIC Sp 30',   5, 120, 3),
    (10,'Watermelon',  'Sweet 16',     3,  85, 2);

INSERT INTO public.farmers
    (farmer_id, first_name, last_name, contact_number, address, date_registered)
OVERRIDING SYSTEM VALUE
VALUES
    (1,  'Juan',    'Dela Cruz',   '09171234567', 'Cebu City',          '2026-10-26'),
    (2,  'Maria',   'Santos',      '09181234567', 'Carcar City, Cebu',  '2026-10-26'),
    (3,  'Pedro',   'Reyes',       '09191234567', 'Danao City, Cebu',   '2026-10-26'),
    (4,  'Ana',     'Garcia',      '09201234567', 'Argao, Cebu',        '2026-10-26'),
    (5,  'Jose',    'Mendoza',     '09211234567', 'Bogo City, Cebu',    '2026-10-26'),
    (6,  'Ramon',   'Flores',      '09221234567', 'Toledo City, Cebu',  '2026-10-26'),
    (7,  'Elena',   'Cruz',        '09231234567', 'Naga City, Cebu',    '2026-10-26'),
    (8,  'Antonio', 'Lim',         '09241234567', 'Minglanilla, Cebu',  '2026-10-26'),
    (9,  'Rosa',    'Fernandez',   '09251234567', 'Liloan, Cebu',       '2026-10-26'),
    (10, 'Mario',   'Villanueva',  '09261234567', 'Compostela, Cebu',   '2026-10-26'),
    (11, 'Teresa',  'Navarro',     '09271234567', 'Talisay City, Cebu', '2026-10-26'),
    (12, 'Carlos',  'Ramos',       '09281234567', 'Consolacion, Cebu',  '2026-10-26'),
    (13, 'Liza',    'Torres',      '09291234567', 'Dalaguete, Cebu',    '2026-10-26'),
    (14, 'Roberto', 'Castillo',    '09301234567', 'Barili, Cebu',       '2026-10-26'),
    (15, 'Susan',   'Aquino',      '09311234567', 'Sibonga, Cebu',      '2026-10-26');

-- Farm 14 is omitted because its name is clipped in the document image.
INSERT INTO public.farms
    (farm_id, farmer_id, farm_name, location, area_hectares, soil_type_id, irrigation_type_id)
OVERRIDING SYSTEM VALUE
VALUES
    (1,  1,  'Dela Cruz Farm',               'Cebu City',          2.50, 1, 1),
    (2,  2,  'Santos Rice Farm',             'Carcar City, Cebu',  4.20, 2, 2),
    (3,  3,  'Reyes Agricultural Farm',      'Danao City, Cebu',   3.80, 3, 1),
    (4,  4,  'Garcia Vegetable Farm',        'Argao, Cebu',        1.75, 1, 3),
    (5,  5,  'Mendoza Farm',                 'Bogo City, Cebu',    5.00, 4, 2),
    (6,  6,  'Flores Corn Farm',             'Toledo City, Cebu',  3.40, 3, 1),
    (7,  7,  'Cruz Family Farm',             'Naga City, Cebu',    2.80, 1, 2),
    (8,  8,  'Lim Agricultural Farm',        'Minglanilla, Cebu',  2.25, 2, 3),
    (9,  9,  'Fernandez Farm',               'Liloan, Cebu',       3.10, 3, 1),
    (10, 10, 'Villanueva Green Farm',        'Compostela, Cebu',   4.50, 1, 2),
    (11, 11, 'Navarro Rice Field',           'Talisay City, Cebu', 3.75, 4, 2),
    (12, 12, 'Ramos Farm',                   'Consolacion, Cebu',  2.60, 1, 1),
    (13, 13, 'Torres Vegetable Farm',        'Dalaguete, Cebu',    1.80, 3, 3),
    (15, 15, 'Aquino Farm',                  'Sibonga, Cebu',      3.20, 1, 1);

-- The document shows planting IDs 11-28. ID 14 depends on the omitted Farm 14.
INSERT INTO public.plantings
    (planting_id, farm_id, crop_id, planting_date, area_planted_ha,
     seed_quantity_kg, expected_harvest_date, status_id)
OVERRIDING SYSTEM VALUE
VALUES
    (11, 11,  1, '2026-02-09', 0.50, 20.00, '2026-06-04', 4),
    (12, 12,  2, '2026-02-12', 0.60, 25.00, '2026-05-23', 4),
    (13, 13,  3, '2026-02-15', 0.70, 30.00, '2026-05-16', 4),
    (15, 15,  5, '2026-02-21', 0.90, 40.00, '2026-12-18', 4),
    (16,  1,  6, '2026-02-24', 1.00, 45.00, '2026-07-24', 4),
    (17,  2,  7, '2026-02-27', 1.10, 10.00, '2026-06-17', 4),
    (18,  3,  8, '2026-03-02', 1.20, 15.00, '2026-10-28', 4),
    (19,  4,  9, '2026-03-05', 1.30, 20.00, '2026-07-03', 4),
    (20,  5, 10, '2026-03-08', 1.40, 25.00, '2026-06-01', 4),
    (21,  6,  1, '2026-03-11', 0.50, 30.00, '2026-07-04', 2),
    (22,  7,  2, '2026-03-14', 0.60, 35.00, '2026-06-22', 2),
    (23,  8,  3, '2026-03-17', 0.70, 40.00, '2026-06-15', 2),
    (24,  9,  4, '2026-03-20', 0.80, 45.00, '2026-06-28', 2),
    (25, 10,  5, '2026-03-23', 0.90, 10.00, '2027-01-17', 2),
    (26, 11,  6, '2026-03-26', 1.00, 15.00, '2026-08-23', 2),
    (27, 12,  7, '2026-03-29', 1.10, 20.00, '2026-07-17', 2),
    (28, 13,  8, '2026-04-01', 1.20, 25.00, '2026-11-27', 2);

-- Harvests 1-10 reference absent planting IDs 1-10; harvest 14 references omitted planting 14.
INSERT INTO public.harvests
    (harvest_id, planting_id, harvest_date, quantity_kg, selling_price_per_kg,
     quality_grade_id, remark_id)
OVERRIDING SYSTEM VALUE
VALUES
    (11, 11, '2026-06-06', 2150.00, 29.00, 2, (SELECT remark_id FROM public.harvest_remarks WHERE remark_description = 'Good harvest')),
    (12, 12, '2026-05-25', 2275.00, 32.50, 3, (SELECT remark_id FROM public.harvest_remarks WHERE remark_description = 'Acceptable quality')),
    (13, 13, '2026-05-18', 2400.00, 36.00, 1, (SELECT remark_id FROM public.harvest_remarks WHERE remark_description = 'High quality harvest')),
    (15, 15, '2026-12-20', 2650.00, 43.00, 3, (SELECT remark_id FROM public.harvest_remarks WHERE remark_description = 'Acceptable quality')),
    (16, 16, '2026-07-26', 2775.00, 46.50, 1, (SELECT remark_id FROM public.harvest_remarks WHERE remark_description = 'High quality harvest')),
    (17, 17, '2026-06-19', 2900.00, 22.00, 2, (SELECT remark_id FROM public.harvest_remarks WHERE remark_description = 'Good harvest')),
    (18, 18, '2026-10-30', 3025.00, 25.50, 3, (SELECT remark_id FROM public.harvest_remarks WHERE remark_description = 'Acceptable quality')),
    (19, 19, '2026-07-05', 3150.00, 29.00, 1, (SELECT remark_id FROM public.harvest_remarks WHERE remark_description = 'High quality harvest')),
    (20, 20, '2026-06-03', 3275.00, 32.50, 2, (SELECT remark_id FROM public.harvest_remarks WHERE remark_description = 'Good harvest'));

SELECT setval(pg_get_serial_sequence('public.crops', 'crop_id'), (SELECT max(crop_id) FROM public.crops), true);
SELECT setval(pg_get_serial_sequence('public.farmers', 'farmer_id'), (SELECT max(farmer_id) FROM public.farmers), true);
SELECT setval(pg_get_serial_sequence('public.farms', 'farm_id'), (SELECT max(farm_id) FROM public.farms), true);
SELECT setval(pg_get_serial_sequence('public.plantings', 'planting_id'), (SELECT max(planting_id) FROM public.plantings), true);
SELECT setval(pg_get_serial_sequence('public.harvests', 'harvest_id'), (SELECT max(harvest_id) FROM public.harvests), true);
SELECT setval(pg_get_serial_sequence('public.harvest_remarks', 'remark_id'), (SELECT max(remark_id) FROM public.harvest_remarks), true);

COMMIT;

-- Verification: counts and duplicate primary keys.
SELECT 'crops' AS table_name, count(*) AS records, count(DISTINCT crop_id) AS distinct_ids FROM public.crops
UNION ALL
SELECT 'farmers', count(*), count(DISTINCT farmer_id) FROM public.farmers
UNION ALL
SELECT 'farms', count(*), count(DISTINCT farm_id) FROM public.farms
UNION ALL
SELECT 'plantings', count(*), count(DISTINCT planting_id) FROM public.plantings
UNION ALL
SELECT 'harvests', count(*), count(DISTINCT harvest_id) FROM public.harvests;

-- Verification: foreign-key violations. Every result should be zero.
SELECT 'farm -> farmer' AS relationship, count(*) AS violations
FROM public.farms f LEFT JOIN public.farmers r ON r.farmer_id = f.farmer_id
WHERE r.farmer_id IS NULL
UNION ALL
SELECT 'planting -> farm/crop', count(*)
FROM public.plantings p
LEFT JOIN public.farms f ON f.farm_id = p.farm_id
LEFT JOIN public.crops c ON c.crop_id = p.crop_id
WHERE f.farm_id IS NULL OR c.crop_id IS NULL
UNION ALL
SELECT 'harvest -> planting', count(*)
FROM public.harvests h LEFT JOIN public.plantings p ON p.planting_id = h.planting_id
WHERE p.planting_id IS NULL;
