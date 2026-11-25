<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\View;
use App\Middleware\Auth;
use App\Repositories\UserRepository;

final class AdminController
{
    private UserRepository $users;

    public function __construct()
    {
        $this->users = new UserRepository(Database::connect());
    }

    public function users(): void
    {
        Auth::requireRole('Administrator');
        View::render('admin/users.php', ['title' => 'User Management', 'active' => 'users', 'users' => $this->users->list(), 'roles' => $this->users->roles()]);
    }

    public function createUser(): void
    {
        Auth::requireRole('Administrator');
        Auth::verifyCsrf();
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $roleId = (int) ($_POST['role_id'] ?? 0);
        $password = (string) ($_POST['temporary_password'] ?? '');
        if ($fullName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $roleId <= 0 || strlen($password) < 12) {
            $_SESSION['flash_error'] = 'Provide a name, valid email, role, and temporary password of at least 12 characters.';
            header('Location: /users');
            exit;
        }
        try {
            $this->users->create([
                'full_name' => $fullName,
                'email' => $email,
                'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                'role_id' => $roleId,
            ]);
            $_SESSION['flash_success'] = 'User created. The user must change the temporary password at first login.';
        } catch (\Throwable $error) {
            error_log('AIS user creation failed: ' . $error->getMessage());
            $_SESSION['flash_error'] = 'The user could not be created.';
        }
        header('Location: /users');
        exit;
    }

    public function lookups(): void
    {
        Auth::requireRole('Administrator');
        $pdo = Database::connect();
        $tables = [
            'crop_categories' => 'category_name', 'growing_seasons' => 'season_name',
            'soil_types' => 'soil_type_name', 'irrigation_types' => 'irrigation_type_name',
            'planting_statuses' => 'status_name', 'harvest_quality_grades' => 'grade_name',
            'harvest_remarks' => 'remark_description',
        ];
        $lookups = [];
        foreach ($tables as $table => $label) {
            $lookups[$table] = $pdo->query("SELECT * FROM public.{$table} ORDER BY 1")->fetchAll();
        }
        View::render('admin/lookups.php', ['title' => 'Lookup Values', 'active' => 'lookups', 'lookups' => $lookups]);
    }

    public function audit(): void
    {
        Auth::requireRole('Administrator');
        $pdo = Database::connect();
        $rows = $pdo->query(
            'SELECT l.log_id,l.action,l.entity_table,l.record_pk,l.created_at,u.full_name
             FROM public.audit_logs l LEFT JOIN public.app_users u ON u.user_id=l.actor_user_id
             ORDER BY l.created_at DESC,l.log_id DESC LIMIT 100'
        )->fetchAll();
        View::render('admin/audit.php', ['title' => 'Audit Logs', 'active' => 'audit', 'rows' => $rows]);
    }
}
