<?php

declare(strict_types=1);

namespace App\Middleware;

final class Auth
{
    public static function user(): ?array
    {
        return isset($_SESSION['user']) ? $_SESSION['user'] : null;
    }

    public static function requireLogin(): void
    {
        if (!self::user()) {
            header('Location: /login');
            exit;
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();
        $role = (string) ($_SESSION['user']['role_name'] ?? '');
        if (!in_array($role, $roles, true)) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';
            exit;
        }
    }

    public static function verifyCsrf(): void
    {
        $token = is_string($_POST['csrf_token'] ?? null) ? $_POST['csrf_token'] : '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            require dirname(__DIR__) . '/Views/errors/403.php';
            exit;
        }
    }
}
