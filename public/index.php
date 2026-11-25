<?php

declare(strict_types=1);

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($path === '/health') {
    require dirname(__DIR__) . '/bootstrap/app.php';
    $diagnostic = \App\Config\Database::diagnostic();
    $ready = $diagnostic['ok'] && $diagnostic['schema'];
    http_response_code($ready ? 200 : 503);
    header('Content-Type: application/json');
    echo json_encode(['status' => $ready ? 'ok' : 'unavailable']);
    exit;
}

$publicFiles = [
    '/manifest.webmanifest',
    '/sw.js',
    '/offline.html',
];
$isStatic = str_starts_with($path, '/assets/');
if (in_array($path, $publicFiles, true) || $isStatic) {
    $file = __DIR__ . $path;
    if (is_file($file)) {
        $extension = pathinfo($file, PATHINFO_EXTENSION);
        $types = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'html' => 'text/html',
            'png' => 'image/png',
            'webmanifest' => 'application/manifest+json',
            'json' => 'application/json',
        ];
        header('Content-Type: ' . ($types[$extension] ?? 'application/octet-stream'));
        readfile($file);
        exit;
    }
}

require dirname(__DIR__) . '/bootstrap/app.php';

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Cache-Control: private, no-store');

$routes = dirname(__DIR__) . '/app/Routes/Router.php';
require $routes;
(new \App\Routes\Router())->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
