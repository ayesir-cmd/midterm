<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\View;
use App\Middleware\Auth;
use App\Repositories\CoreRepository;
use App\Repositories\CrudRepository;

final class DashboardController
{
    public function index(): void
    {
        Auth::requireLogin();
        if ($_SESSION['user']['must_change_password']) {
            header('Location: /change-password');
            exit;
        }
        $pdo = Database::connect();
        $data = (new CoreRepository($pdo))->dashboard();
        $farms = (new CrudRepository($pdo))->lookup('farms');
        View::render('dashboard/index.php', ['title' => 'Dashboard', 'active' => 'dashboard', 'farms' => $farms] + $data);
    }

    public function reports(): void
    {
        Auth::requireLogin();
        $pdo = Database::connect();
        $repository = new CoreRepository($pdo);
        $filters = [
            'start' => trim((string) ($_GET['start'] ?? '')),
            'end' => trim((string) ($_GET['end'] ?? '')),
            'farm' => trim((string) ($_GET['farm'] ?? '')),
        ];
        $report = $repository->reports($filters);
        $farms = (new CrudRepository($pdo))->lookup('farms');
        View::render('reports/index.php', ['title' => 'Reports', 'active' => 'reports', 'filters' => $filters, 'farms' => $farms] + $report);
    }
}
