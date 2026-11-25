<?php

declare(strict_types=1);

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $file = dirname(__DIR__) . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

require dirname(__DIR__) . '/app/Config/Env.php';
\App\Config\Env::load(dirname(__DIR__) . '/.env');

date_default_timezone_set(\App\Config\Env::get('APP_TIMEZONE', 'UTC') ?? 'UTC');
$debug = (\App\Config\Env::get('APP_DEBUG', 'false') === 'true');
ini_set('display_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/storage/logs/app.log');

$secure = (\App\Config\Env::get('SESSION_SECURE_COOKIE', 'false') === 'true');
session_name(\App\Config\Env::get('SESSION_NAME', 'ais_session') ?? 'ais_session');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $secure,
    'httponly' => (\App\Config\Env::get('SESSION_HTTP_ONLY', 'true') === 'true'),
    'samesite' => \App\Config\Env::get('SESSION_SAME_SITE', 'Lax') ?? 'Lax',
]);
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
