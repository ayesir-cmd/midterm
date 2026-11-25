-- Environment-gated in application CLI; repeatable and synthetic.
BEGIN;
WITH source AS (
  SELECT f.farm_id,c.crop_id,ps.status_id,
         (CASE c.crop_name WHEN 'Rice' THEN '2026-06-10' WHEN 'Corn' THEN '2026-05-15' ELSE '2026-07-01' END)::date planting_date,
         0.40::numeric area_ha,12.00::numeric seed_kg,
         (CASE c.crop_name WHEN 'Rice' THEN '2026-10-08' WHEN 'Corn' THEN '2026-08-18' ELSE '2026-09-15' END)::date expected_date
  FROM public.farms f CROSS JOIN public.crops c
  JOIN public.planting_statuses ps ON ps.status_name='Growing'
  WHERE f.farm_name LIKE 'DEMO %' AND c.crop_name IN ('Rice','Corn','Tomato')
  LIMIT 6
)
INSERT INTO public.plantings(farm_id,crop_id,planting_date,area_planted_ha,seed_quantity_kg,expected_harvest_date,status_id)
SELECT farm_id,crop_id,planting_date,area_ha,seed_kg,expected_date,status_id FROM source s
WHERE NOT EXISTS (
 SELECT 1 FROM public.plantings p
 WHERE p.farm_id=s.farm_id AND p.crop_id=s.crop_id AND p.planting_date=s.planting_date
);

WITH source AS (
  SELECT p.planting_id,(p.planting_date+90)::date harvest_date,
         850.00::numeric quantity_kg,22.50::numeric price,
         1::smallint grade_id,1::smallint remark_id
  FROM public.plantings p
  JOIN public.crops c ON c.crop_id=p.crop_id
  WHERE p.planting_id IN (
    SELECT planting_id FROM public.plantings
    WHERE farm_id IN (SELECT farm_id FROM public.farms WHERE farm_name LIKE 'DEMO %')
  ) LIMIT 3
)
INSERT INTO public.harvests(planting_id,harvest_date,quantity_kg,selling_price_per_kg,quality_grade_id,remark_id)
SELECT planting_id,harvest_date,quantity_kg,price,grade_id,remark_id FROM source s
WHERE NOT EXISTS (SELECT 1 FROM public.harvests h WHERE h.planting_id=s.planting_id);
COMMIT;
