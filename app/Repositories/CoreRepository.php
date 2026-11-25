<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class CoreRepository extends BaseRepository
{
    public function dashboard(): array
    {
        $row = $this->pdo->query(
            'SELECT
              (SELECT COUNT(*) FROM public.farmers) farmer_count,
              (SELECT COUNT(*) FROM public.farms) farm_count,
              (SELECT COUNT(*) FROM public.crops) crop_count,
              (SELECT COUNT(*) FROM public.plantings) planting_count,
              (SELECT COUNT(*) FROM public.harvests) harvest_count,
              (SELECT COALESCE(SUM(quantity_kg),0) FROM public.harvests) harvest_kg,
              (SELECT COALESCE(SUM(quantity_kg*selling_price_per_kg) FILTER (WHERE selling_price_per_kg IS NOT NULL),0) FROM public.harvests) harvest_value'
        )->fetch();
        $status = $this->pdo->query(
            'SELECT ps.status_name, COUNT(p.*) planting_count
             FROM public.planting_statuses ps LEFT JOIN public.plantings p ON p.status_id=ps.status_id
             GROUP BY ps.status_id,ps.status_name ORDER BY ps.status_id'
        )->fetchAll();
        $plantings = $this->pdo->query(
            'SELECT p.planting_id,f.farm_name,c.crop_name,cr.status_name,p.planting_date
             FROM public.plantings p JOIN public.farms f ON f.farm_id=p.farm_id
             JOIN public.crops c ON c.crop_id=p.crop_id
             JOIN public.planting_statuses cr ON cr.status_id=p.status_id
             ORDER BY p.planting_date DESC,p.planting_id DESC LIMIT 5'
        )->fetchAll();
        $harvests = $this->pdo->query(
            'SELECT h.harvest_id,h.harvest_date,h.quantity_kg,c.crop_name,f.farm_name
             FROM public.harvests h JOIN public.plantings p ON p.planting_id=h.planting_id
             JOIN public.farms f ON f.farm_id=p.farm_id JOIN public.crops c ON c.crop_id=p.crop_id
             ORDER BY h.harvest_date DESC,h.harvest_id DESC LIMIT 5'
        )->fetchAll();
        return ['totals' => $row, 'statuses' => $status, 'plantings' => $plantings, 'harvests' => $harvests];
    }

    public function reports(array $filters): array
    {
        $sql = 'SELECT h.harvest_id,h.harvest_date,h.quantity_kg,h.selling_price_per_kg,
                       h.quantity_kg*COALESCE(h.selling_price_per_kg,0) estimated_value,
                       f.farm_name,c.crop_name,c.variety,q.grade_name,r.remark_description
                FROM public.harvests h
                JOIN public.plantings p ON p.planting_id=h.planting_id
                JOIN public.farms f ON f.farm_id=p.farm_id
                JOIN public.crops c ON c.crop_id=p.crop_id
                JOIN public.harvest_quality_grades q ON q.quality_grade_id=h.quality_grade_id
                JOIN public.harvest_remarks r ON r.remark_id=h.remark_id
                WHERE 1=1';
        $params = [];
        if (($filters['start'] ?? '') !== '') {
            $sql .= ' AND h.harvest_date >= :start';
            $params['start'] = $filters['start'];
        }
        if (($filters['end'] ?? '') !== '') {
            $sql .= ' AND h.harvest_date <= :end';
            $params['end'] = $filters['end'];
        }
        if (($filters['farm'] ?? '') !== '') {
            $sql .= ' AND f.farm_id=:farm';
            $params['farm'] = (int) $filters['farm'];
        }
        $sql .= ' ORDER BY h.harvest_date DESC,h.harvest_id DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        $quantity = 0.0;
        $value = 0.0;
        foreach ($rows as $row) {
            $quantity += (float) $row['quantity_kg'];
            if ($row['selling_price_per_kg'] !== null) {
                $value += (float) $row['estimated_value'];
            }
        }
        return ['rows' => $rows, 'quantity' => $quantity, 'value' => $value];
    }
}
