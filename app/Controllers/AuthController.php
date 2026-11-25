<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\View;
use App\Middleware\Auth;
use App\Repositories\UserRepository;
use RuntimeException;

final class AuthController
{
    private ?UserRepository $users = null;

    private function users(): UserRepository
    {
        return $this->users ??= new UserRepository(Database::connect());
    }

    public function login(): void
    {
        if (Auth::user()) {
            header('Location: /dashboard');
            exit;
        }
        View::render('auth/login.php', ['title' => 'Sign in', 'layout' => 'auth']);
    }

    public function authenticate(): void
    {
        Auth::verifyCsrf();
        $email = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        try {
            $user = filter_var($email, FILTER_VALIDATE_EMAIL) ? $this->users()->findForLogin($email) : null;
        } catch (RuntimeException $error) {
            error_log('Login lookup failed: ' . $error->getMessage());
            $_SESSION['flash_error'] = 'Sign in is temporarily unavailable. Please try again later.';
            header('Location: /login');
            exit;
        }
        if (!$user || !$user['is_active'] || !password_verify($password, $user['password_hash'])) {
            $_SESSION['flash_error'] = 'Invalid email or password.';
            header('Location: /login');
            exit;
        }
        try {
            $this->users()->recordLogin((int) $user['user_id']);
        } catch (RuntimeException $error) {
            error_log('Login record update failed: ' . $error->getMessage());
            $_SESSION['flash_error'] = 'Sign in is temporarily unavailable. Please try again later.';
            header('Location: /login');
            exit;
        }
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'user_id' => (int) $user['user_id'],
            'full_name' => $user['full_name'],
            'email' => $user['email'],
            'role_name' => $user['role_name'],
            'must_change_password' => (bool) $user['must_change_password'],
        ];
        if ((bool) $user['must_change_password']) {
            header('Location: /change-password');
        } else {
            header('Location: /dashboard');
        }
        exit;
    }

    public function logout(): void
    {
        Auth::verifyCsrf();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', ['expires' => time() - 42000, 'path' => $params['path'], 'secure' => $params['secure'], 'httponly' => $params['httponly'], 'samesite' => $params['samesite']]);
        }
        session_destroy();
        header('Location: /login');
        exit;
    }

    public function changePassword(): void
    {
        Auth::requireLogin();
        View::render('auth/change-password.php', ['title' => 'Change password', 'layout' => 'app', 'active' => 'settings']);
    }

    public function updatePassword(): void
    {
        Auth::requireLogin();
        Auth::verifyCsrf();
        $password = (string) ($_POST['password'] ?? '');
        $confirm = (string) ($_POST['password_confirmation'] ?? '');
        if (strlen($password) < 12 || $password !== $confirm) {
            $_SESSION['flash_error'] = 'Password must be at least 12 characters and both entries must match.';
            header('Location: /change-password');
            exit;
        }
        $this->users()->setPassword((int) $_SESSION['user']['user_id'], password_hash($password, PASSWORD_DEFAULT));
        $_SESSION['user']['must_change_password'] = false;
        $_SESSION['flash_success'] = 'Password changed successfully.';
        header('Location: /dashboard');
        exit;
    }

    public function accessRequest(): void
    {
        View::render('auth/access-request.php', ['title' => 'Account access', 'layout' => 'auth']);
    }
}
