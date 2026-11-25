<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\View;
use App\Middleware\Auth;
use App\Repositories\CrudRepository;

final class CrudController
{
    private CrudRepository $repository;
    private array $labels = [
        'farmers' => 'Farmer', 'farms' => 'Farm', 'crops' => 'Crop',
        'plantings' => 'Planting', 'harvests' => 'Harvest',
    ];

    public function __construct()
    {
        $this->repository = new CrudRepository(Database::connect());
    }

    public function index(string $entity): void
    {
        Auth::requireLogin();
        $search = trim((string) ($_GET['q'] ?? ''));
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $result = $this->repository->list($entity, $search, $page, 20);
        View::render("crud/{$entity}/index.php", [
            'title' => ucfirst($entity), 'active' => $entity, 'entity' => $entity,
            'label' => $this->labels[$entity], 'search' => $search,
        ] + $result);
    }

    public function create(string $entity): void
    {
        Auth::requireLogin();
        $lookups = $this->lookups($entity);
        View::render("crud/{$entity}/form.php", [
            'title' => 'Add ' . $this->labels[$entity], 'active' => $entity,
            'entity' => $entity, 'label' => $this->labels[$entity], 'record' => null,
        ] + $lookups);
    }

    public function edit(string $entity, int $id): void
    {
        Auth::requireLogin();
        $record = $this->repository->find($entity, $id);
        if (!$record) {
            http_response_code(404);
            require dirname(__DIR__) . '/Views/errors/404.php';
            return;
        }
        $lookups = $this->lookups($entity);
        View::render("crud/{$entity}/form.php", [
            'title' => 'Edit ' . $this->labels[$entity], 'active' => $entity,
            'entity' => $entity, 'label' => $this->labels[$entity], 'record' => $record,
        ] + $lookups);
    }

    public function save(string $entity): void
    {
        Auth::requireLogin();
        Auth::verifyCsrf();
        $id = isset($_POST['id']) ? (int) $_POST['id'] : null;
        $values = $this->validate($entity);
        try {
            $this->repository->save($entity, $values, $id);
            $_SESSION['flash_success'] = $this->labels[$entity] . ' saved successfully.';
        } catch (\Throwable $error) {
            error_log('AIS save failed: ' . $error->getMessage());
            $_SESSION['flash_error'] = 'The record could not be saved. Check its values and dependencies.';
        }
        header('Location: /' . $entity);
        exit;
    }

    public function delete(string $entity, int $id): void
    {
        Auth::requireRole('Administrator');
        Auth::verifyCsrf();
        try {
            $this->repository->delete($entity, $id);
            $_SESSION['flash_success'] = $this->labels[$entity] . ' deleted.';
        } catch (\Throwable $error) {
            error_log('AIS delete failed: ' . $error->getMessage());
            $_SESSION['flash_error'] = 'This record is referenced by other records and cannot be deleted.';
        }
        header('Location: /' . $entity);
        exit;
    }

    private function lookups(string $entity): array
    {
        $common = [
            'farmers' => fn() => $this->repository->lookup('farmers'),
            'farms' => fn() => $this->repository->lookup('farms'),
            'crops' => fn() => $this->repository->lookup('crops'),
        ];
        if ($entity === 'farms') return ['farmers' => $common['farmers'](), 'soils' => $this->repository->lookup('soils'), 'irrigations' => $this->repository->lookup('irrigations')];
        if ($entity === 'crops') return ['categories' => $this->repository->lookup('categories'), 'seasons' => $this->repository->lookup('seasons')];
        if ($entity === 'plantings') return ['farms' => $common['farms'](), 'crops' => $common['crops'](), 'statuses' => $this->repository->lookup('statuses')];
        if ($entity === 'harvests') return ['plantings' => $this->repository->lookup('plantings'), 'grades' => $this->repository->lookup('grades'), 'remarks' => $this->repository->lookup('remarks')];
        return [];
    }

    private function validate(string $entity): array
    {
        $text = static fn(string $key, int $max): string => substr(trim((string) ($_POST[$key] ?? '')), 0, $max);
        $int = static fn(string $key): int => (int) ($_POST[$key] ?? 0);
        $decimal = static fn(string $key): ?string => (($value = trim((string) ($_POST[$key] ?? ''))) === '' ? null : $value);
        if ($entity === 'farmers') {
            $values = ['first_name' => $text('first_name',50), 'last_name' => $text('last_name',50), 'contact_number' => $text('contact_number',20), 'address' => $text('address',150), 'date_registered' => $text('date_registered',10)];
            if ($values['first_name'] === '' || $values['last_name'] === '' || $values['address'] === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $values['date_registered'])) throw new \InvalidArgumentException('Invalid farmer');
            return $values;
        }
        if ($entity === 'farms') {
            $values = ['farmer_id' => $int('farmer_id'), 'farm_name' => $text('farm_name',100), 'location' => $text('location',150), 'area_hectares' => $decimal('area_hectares'), 'soil_type_id' => $int('soil_type_id'), 'irrigation_type_id' => $int('irrigation_type_id')];
            if (min($values['farmer_id'],$values['soil_type_id'],$values['irrigation_type_id']) <= 0 || $values['farm_name'] === '' || $values['location'] === '' || !is_numeric((string) $values['area_hectares']) || (float) $values['area_hectares'] <= 0) throw new \InvalidArgumentException('Invalid farm');
            return $values;
        }
        if ($entity === 'crops') {
            $values = ['crop_name' => $text('crop_name',80), 'variety' => $text('variety',80), 'category_id' => $int('category_id'), 'growing_period_days' => $int('growing_period_days'), 'season_id' => $int('season_id')];
            if ($values['crop_name'] === '' || $values['variety'] === '' || min($values['category_id'],$values['season_id'],$values['growing_period_days']) <= 0) throw new \InvalidArgumentException('Invalid crop');
            return $values;
        }
        if ($entity === 'plantings') {
            $values = ['farm_id' => $int('farm_id'), 'crop_id' => $int('crop_id'), 'planting_date' => $text('planting_date',10), 'area_planted_ha' => $decimal('area_planted_ha'), 'seed_quantity_kg' => $decimal('seed_quantity_kg'), 'expected_harvest_date' => $text('expected_harvest_date',10), 'status_id' => $int('status_id')];
            if (min($values['farm_id'],$values['crop_id'],$values['status_id']) <= 0 || $values['area_planted_ha'] === null || (float) $values['area_planted_ha'] <= 0 || $values['seed_quantity_kg'] === null || (float) $values['seed_quantity_kg'] < 0 || $values['expected_harvest_date'] < $values['planting_date']) throw new \InvalidArgumentException('Invalid planting');
            return $values;
        }
        $values = ['planting_id' => $int('planting_id'), 'harvest_date' => $text('harvest_date',10), 'quantity_kg' => $decimal('quantity_kg'), 'selling_price_per_kg' => $decimal('selling_price_per_kg'), 'quality_grade_id' => $int('quality_grade_id'), 'remark_id' => $int('remark_id')];
        if ($values['planting_id'] <= 0 || $values['quantity_kg'] === null || (float) $values['quantity_kg'] <= 0 || $values['selling_price_per_kg'] !== null && (float) $values['selling_price_per_kg'] < 0 || min($values['quality_grade_id'],$values['remark_id']) <= 0) throw new \InvalidArgumentException('Invalid harvest');
        return $values;
    }
}
