BEGIN;
INSERT INTO public.crop_categories(category_id,category_name) VALUES
(1,'Cereal'),(2,'Vegetable'),(3,'Fruit'),(4,'Legume'),(5,'Root Crop')
ON CONFLICT(category_id) DO UPDATE SET category_name=EXCLUDED.category_name;
INSERT INTO public.growing_seasons(season_id,season_name) VALUES
(1,'Wet Season'),(2,'Dry Season'),(3,'Year Round') ON CONFLICT(season_id) DO UPDATE SET season_name=EXCLUDED.season_name;
INSERT INTO public.soil_types(soil_type_id,soil_type_name) VALUES
(1,'Loam'),(2,'Clay Loam'),(3,'Sandy Loam'),(4,'Clay') ON CONFLICT(soil_type_id) DO UPDATE SET soil_type_name=EXCLUDED.soil_type_name;
INSERT INTO public.irrigation_types(irrigation_type_id,irrigation_type_name) VALUES
(1,'Rainfed'),(2,'Irrigated'),(3,'Drip Irrigation') ON CONFLICT(irrigation_type_id) DO UPDATE SET irrigation_type_name=EXCLUDED.irrigation_type_name;
INSERT INTO public.planting_statuses(status_id,status_name) VALUES
(1,'Planned'),(2,'Growing'),(3,'Ready for Harvest'),(4,'Harvested'),(5,'Cancelled')
ON CONFLICT(status_id) DO UPDATE SET status_name=EXCLUDED.status_name;
INSERT INTO public.harvest_quality_grades(quality_grade_id,grade_name) VALUES
(1,'Grade A'),(2,'Grade B'),(3,'Grade C') ON CONFLICT(quality_grade_id) DO UPDATE SET grade_name=EXCLUDED.grade_name;
INSERT INTO public.harvest_remarks(remark_id,remark_description) VALUES
(1,'Good Harvest'),(2,'Acceptable Quality'),(3,'Low Yield'),(4,'Poor Quality'),(5,'Damaged Harvest')
ON CONFLICT(remark_id) DO UPDATE SET remark_description=EXCLUDED.remark_description;
INSERT INTO public.app_roles(role_id,role_name,description) VALUES
(1,'Administrator','Full system administration'),(2,'Staff','Operational data entry and reporting')
ON CONFLICT(role_id) DO UPDATE SET role_name=EXCLUDED.role_name, description=EXCLUDED.description;
COMMIT;
