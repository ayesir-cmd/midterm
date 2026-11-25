BEGIN;
INSERT INTO public.farmers(first_name,last_name,contact_number,address,date_registered)
SELECT v.first_name,v.last_name,v.contact_number,v.address,v.date_registered::date
FROM (VALUES
('Maria','DEMO Santos','+639170000101','Poblacion, Talisay, Cebu','2025-01-10'),
('Juan','DEMO Garcia','+639170000102','Cabancalan, Mandaue City, Cebu','2025-02-14'),
('Elena','DEMO Reyes','+639170000103','Naga, Cebu','2025-03-20')
) AS v(first_name,last_name,contact_number,address,date_registered)
WHERE NOT EXISTS (SELECT 1 FROM public.farmers f WHERE f.first_name=v.first_name AND f.last_name=v.last_name);

WITH demo_farms AS (
SELECT farmer_id, farm_name, location, area, soil_id, irrigation_id FROM (
SELECT f.farmer_id,'DEMO Green Valley Farm' AS farm_name,'Talisay uplands, Cebu' AS location,2.50::numeric AS area,1 AS soil_id,2 AS irrigation_id FROM public.farmers f WHERE f.last_name='DEMO Santos'
UNION ALL SELECT f.farmer_id,'DEMO Coastal Plot','Minglanilla, Cebu',1.20::numeric,3,1 FROM public.farmers f WHERE f.last_name='DEMO Garcia'
UNION ALL SELECT f.farmer_id,'DEMO Highland Acres','Naga, Cebu',3.75::numeric,2,2 FROM public.farmers f WHERE f.last_name='DEMO Reyes'
) q
)
INSERT INTO public.farms(farmer_id,farm_name,location,area_hectares,soil_type_id,irrigation_type_id)
SELECT farmer_id,farm_name,location,area,soil_id,irrigation_id FROM demo_farms d
WHERE NOT EXISTS (SELECT 1 FROM public.farms f WHERE f.farmer_id=d.farmer_id AND f.farm_name=d.farm_name);

INSERT INTO public.crops(crop_name,variety,category_id,growing_period_days,season_id)
SELECT v.crop_name,v.variety,v.category_id,v.days,v.season_id
FROM (VALUES
('Rice','Native Lowland',1,120,1),('Corn','Yellow Hybrid',1,95,2),('Tomato','Red Cherry',2,85,3),
('Eggplant','Casino',2,75,3),('Banana','Lakatan',3,300,3),('Mango','Carabao',3,240,2),
('Peanut','Runner',4,110,1),('Cassava','Golden',5,270,3),('Sweet Potato','Orange',5,120,2),
('Watermelon','Sugar Baby',3,85,2)
) AS v(crop_name,variety,category_id,days,season_id)
WHERE NOT EXISTS (SELECT 1 FROM public.crops c WHERE c.crop_name=v.crop_name AND c.variety=v.variety);
COMMIT;
