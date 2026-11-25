BEGIN;
DO $$ BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname='trg_farms_updated') THEN
    CREATE TRIGGER trg_farms_updated BEFORE UPDATE ON public.farms FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();
  END IF;
  IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname='trg_crops_updated') THEN
    CREATE TRIGGER trg_crops_updated BEFORE UPDATE ON public.crops FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();
  END IF;
  IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname='trg_plantings_updated') THEN
    CREATE TRIGGER trg_plantings_updated BEFORE UPDATE ON public.plantings FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();
  END IF;
  IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname='trg_harvests_updated') THEN
    CREATE TRIGGER trg_harvests_updated BEFORE UPDATE ON public.harvests FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();
  END IF;
  IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname='trg_app_users_updated') THEN
    CREATE TRIGGER trg_app_users_updated BEFORE UPDATE ON public.app_users FOR EACH ROW EXECUTE FUNCTION public.set_updated_at();
  END IF;
END $$;

CREATE OR REPLACE FUNCTION public.check_harvest_date() RETURNS trigger LANGUAGE plpgsql AS $$
DECLARE v_planting_date date;
BEGIN
  SELECT planting_date INTO v_planting_date FROM public.plantings WHERE planting_id=NEW.planting_id;
  IF v_planting_date IS NULL THEN RAISE EXCEPTION 'Planting record does not exist'; END IF;
  IF NEW.harvest_date < v_planting_date THEN RAISE EXCEPTION 'Harvest date cannot be before planting date'; END IF;
  RETURN NEW;
END $$;
DO $$ BEGIN
  IF NOT EXISTS (SELECT 1 FROM pg_trigger WHERE tgname='trg_harvest_date') THEN
    CREATE TRIGGER trg_harvest_date BEFORE INSERT OR UPDATE ON public.harvests
      FOR EACH ROW EXECUTE FUNCTION public.check_harvest_date();
  END IF;
END $$;

CREATE INDEX IF NOT EXISTS idx_farms_farmer_id ON public.farms(farmer_id);
CREATE INDEX IF NOT EXISTS idx_farms_soil_type_id ON public.farms(soil_type_id);
CREATE INDEX IF NOT EXISTS idx_farms_irrigation_type_id ON public.farms(irrigation_type_id);
CREATE INDEX IF NOT EXISTS idx_plantings_farm_id ON public.plantings(farm_id);
CREATE INDEX IF NOT EXISTS idx_plantings_crop_id ON public.plantings(crop_id);
CREATE INDEX IF NOT EXISTS idx_plantings_status_id ON public.plantings(status_id);
CREATE INDEX IF NOT EXISTS idx_plantings_planting_date ON public.plantings(planting_date DESC);
CREATE INDEX IF NOT EXISTS idx_harvests_planting_id ON public.harvests(planting_id);
CREATE INDEX IF NOT EXISTS idx_harvests_harvest_date ON public.harvests(harvest_date DESC);
CREATE INDEX IF NOT EXISTS idx_harvests_quality_grade_id ON public.harvests(quality_grade_id);
CREATE INDEX IF NOT EXISTS idx_audit_logs_created_at ON public.audit_logs(created_at DESC);
CREATE INDEX IF NOT EXISTS idx_audit_logs_entity ON public.audit_logs(entity_table, record_pk);
COMMIT;
