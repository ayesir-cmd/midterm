<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class CrudRepository extends BaseRepository
{
    private array $config = [
        'farmers' => ['pk' => 'farmer_id', 'sql' => 'SELECT farmer_id,first_name,last_name,contact_number,address,date_registered,updated_at FROM public.farmers'],
        'farms' => ['pk' => 'farm_id', 'sql' => 'SELECT fm.farm_id,fm.farmer_id,fm.farm_name,fm.location,fm.area_hectares,fm.soil_type_id,fm.irrigation_type_id,CONCAT(f.first_name,\' \',f.last_name) farmer_name,st.soil_type_name,it.irrigation_type_name,fm.updated_at FROM public.farms fm JOIN public.farmers f ON f.farmer_id=fm.farmer_id JOIN public.soil_types st ON st.soil_type_id=fm.soil_type_id JOIN public.irrigation_types it ON it.irrigation_type_id=fm.irrigation_type_id'],
        'crops' => ['pk' => 'crop_id', 'sql' => 'SELECT c.crop_id,c.crop_name,c.variety,c.category_id,c.growing_period_days,c.season_id,cc.category_name,gs.season_name,c.updated_at FROM public.crops c JOIN public.crop_categories cc ON cc.category_id=c.category_id JOIN public.growing_seasons gs ON gs.season_id=c.season_id'],
        'plantings' => ['pk' => 'planting_id', 'sql' => 'SELECT p.planting_id,p.farm_id,p.crop_id,p.planting_date,p.area_planted_ha,p.seed_quantity_kg,p.expected_harvest_date,p.status_id,f.farm_name,c.crop_name,ps.status_name,p.updated_at FROM public.plantings p JOIN public.farms f ON f.farm_id=p.farm_id JOIN public.crops c ON c.crop_id=p.crop_id JOIN public.planting_statuses ps ON ps.status_id=p.status_id'],
        'harvests' => ['pk' => 'harvest_id', 'sql' => 'SELECT h.harvest_id,h.planting_id,h.harvest_date,h.quantity_kg,h.selling_price_per_kg,h.quality_grade_id,h.remark_id,f.farm_name,c.crop_name,q.grade_name,r.remark_description,h.updated_at FROM public.harvests h JOIN public.plantings p ON p.planting_id=h.planting_id JOIN public.farms f ON f.farm_id=p.farm_id JOIN public.crops c ON c.crop_id=p.crop_id JOIN public.harvest_quality_grades q ON q.quality_grade_id=h.quality_grade_id JOIN public.harvest_remarks r ON r.remark_id=h.remark_id'],
    ];

    public function list(string $entity, string $search = '', int $page = 1, int $perPage = 20): array
    {
        if (!isset($this->config[$entity])) throw new \InvalidArgumentException('Unknown entity');
        $base = $this->config[$entity]['sql'];
        $params = [];
        $where = '';
        if ($search !== '') {
            $columns = [
                'farmers' => 'first_name||\' \'||last_name||\' \'||COALESCE(address,\'\')',
                'farms' => 'farm_name||\' \'||location',
                'crops' => 'crop_name||\' \'||variety',
                'plantings' => 'farm_name||\' \'||crop_name',
                'harvests' => 'farm_name||\' \'||crop_name',
            ];
            $where = ' WHERE ' . $columns[$entity] . ' ILIKE :search';
            $params['search'] = '%' . $search . '%';
        }
        $countSql = str_contains($base, ' FROM ') ? 'SELECT COUNT(*) FROM (' . $base . $where . ') count_rows' : '';
        if ($countSql === '') throw new \RuntimeException('Could not count records');
        $countStmt = $this->pdo->prepare($countSql);
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();
        $pk = $this->config[$entity]['pk'];
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $sql = $base . $where . ' ORDER BY ' . $pk . ' ASC LIMIT :limit OFFSET :offset';
        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) $stmt->bindValue($key, $value);
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return ['rows' => $stmt->fetchAll(), 'total' => $total, 'pages' => max(1, (int) ceil($total / $perPage)), 'page' => $page];
    }

    public function find(string $entity, int $id): ?array
    {
        if (!isset($this->config[$entity])) throw new \InvalidArgumentException('Unknown entity');
        $pk = $this->config[$entity]['pk'];
        $stmt = $this->pdo->prepare($this->config[$entity]['sql'] . ' WHERE ' . $this->pkQualified($entity, $pk) . ' = :id');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private function pkQualified(string $entity, string $pk): string
    {
        return match ($entity) {
            'farmers' => 'farmer_id',
            'farms' => 'fm.farm_id',
            'crops' => 'c.crop_id',
            'plantings' => 'p.planting_id',
            'harvests' => 'h.harvest_id',
        };
    }

    public function lookup(string $entity): array
    {
        $queries = [
            'farmers' => 'SELECT farmer_id,first_name||\' \'||last_name label FROM public.farmers ORDER BY last_name',
            'farms' => 'SELECT farm_id,farm_name label FROM public.farms ORDER BY farm_name',
            'crops' => 'SELECT crop_id,crop_name||\' - \'||variety label FROM public.crops ORDER BY crop_name',
            'categories' => 'SELECT category_id,category_name label FROM public.crop_categories WHERE is_active ORDER BY category_name',
            'seasons' => 'SELECT season_id,season_name label FROM public.growing_seasons WHERE is_active ORDER BY season_id',
            'soils' => 'SELECT soil_type_id,soil_type_name label FROM public.soil_types WHERE is_active ORDER BY soil_type_id',
            'irrigations' => 'SELECT irrigation_type_id,irrigation_type_name label FROM public.irrigation_types WHERE is_active ORDER BY irrigation_type_id',
            'statuses' => 'SELECT status_id,status_name label FROM public.planting_statuses WHERE is_active ORDER BY status_id',
            'grades' => 'SELECT quality_grade_id,grade_name label FROM public.harvest_quality_grades WHERE is_active ORDER BY quality_grade_id',
            'remarks' => 'SELECT remark_id,remark_description label FROM public.harvest_remarks WHERE is_active ORDER BY remark_id',
            'plantings' => 'SELECT p.planting_id,f.farm_name||\' / \'||c.crop_name||\' (#\'||p.planting_id||\')\' label FROM public.plantings p JOIN public.farms f ON f.farm_id=p.farm_id JOIN public.crops c ON c.crop_id=p.crop_id ORDER BY p.planting_id DESC',
        ];
        if (!isset($queries[$entity])) throw new \InvalidArgumentException('Unknown lookup');
        return $this->pdo->query($queries[$entity])->fetchAll();
    }

    public function save(string $entity, array $values, ?int $id = null): int
    {
        $actor = (int) ($_SESSION['user']['user_id'] ?? 0);
        $this->pdo->beginTransaction();
        try {
            if ($entity === 'farmers') {
                $sql = $id
                    ? 'UPDATE public.farmers SET first_name=:first_name,last_name=:last_name,contact_number=:contact_number,address=:address,date_registered=:date_registered WHERE farmer_id=:id'
                    : 'INSERT INTO public.farmers(first_name,last_name,contact_number,address,date_registered) VALUES(:first_name,:last_name,:contact_number,:address,:date_registered) RETURNING farmer_id';
            } elseif ($entity === 'farms') {
                $sql = $id
                    ? 'UPDATE public.farms SET farmer_id=:farmer_id,farm_name=:farm_name,location=:location,area_hectares=:area_hectares,soil_type_id=:soil_type_id,irrigation_type_id=:irrigation_type_id WHERE farm_id=:id'
                    : 'INSERT INTO public.farms(farmer_id,farm_name,location,area_hectares,soil_type_id,irrigation_type_id) VALUES(:farmer_id,:farm_name,:location,:area_hectares,:soil_type_id,:irrigation_type_id) RETURNING farm_id';
            } elseif ($entity === 'crops') {
                $sql = $id
                    ? 'UPDATE public.crops SET crop_name=:crop_name,variety=:variety,category_id=:category_id,growing_period_days=:growing_period_days,season_id=:season_id WHERE crop_id=:id'
                    : 'INSERT INTO public.crops(crop_name,variety,category_id,growing_period_days,season_id) VALUES(:crop_name,:variety,:category_id,:growing_period_days,:season_id) RETURNING crop_id';
            } elseif ($entity === 'plantings') {
                $sql = $id      
                    ? 'UPDATE public.plantings SET farm_id=:farm_id,crop_id=:crop_id,planting_date=:planting_date,area_planted_ha=:area_planted_ha,seed_quantity_kg=:seed_quantity_kg,expected_harvest_date=:expected_harvest_date,status_id=:status_id WHERE planting_id=:id'
                    : 'INSERT INTO public.plantings(farm_id,crop_id,planting_date,area_planted_ha,seed_quantity_kg,expected_harvest_date,status_id) VALUES(:farm_id,:crop_id,:planting_date,:area_planted_ha,:seed_quantity_kg,:expected_harvest_date,:status_id) RETURNING planting_id';
            } else {
                $sql = $id
                    ? 'UPDATE public.harvests SET planting_id=:planting_id,harvest_date=:harvest_date,quantity_kg=:quantity_kg,selling_price_per_kg=:selling_price_per_kg,quality_grade_id=:quality_grade_id,remark_id=:remark_id WHERE harvest_id=:id'
                    : 'INSERT INTO public.harvests(planting_id,harvest_date,quantity_kg,selling_price_per_kg,quality_grade_id,remark_id) VALUES(:planting_id,:harvest_date,:quantity_kg,:selling_price_per_kg,:quality_grade_id,:remark_id) RETURNING harvest_id';
            }
            $params = $values;
            if ($id) $params['id'] = $id;
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $savedId = $id ?: (int) $stmt->fetchColumn();
            $this->audit($actor, $id ? 'UPDATE' : 'INSERT', $entity, $savedId, null, $values);
            $this->pdo->commit();
            return $savedId;
        } catch (\Throwable $error) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $error;
        }
    }

    public function delete(string $entity, int $id): void
    {
        $actor = (int) ($_SESSION['user']['user_id'] ?? 0);
        $pk = $this->config[$entity]['pk'];
        $qualified = $this->pkQualified($entity, $pk);
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare('DELETE FROM public.' . $entity . ' WHERE ' . $pk . '=:id');
            $stmt->execute(['id' => $id]);
            if ($stmt->rowCount() !== 1) throw new \RuntimeException('Record not found');
            $this->audit($actor, 'DELETE', $entity, $id, null, null);
            $this->pdo->commit();
        } catch (\Throwable $error) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $error;
        }
    }
}
