<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/app.php';
use App\Config\Database;

try {
    $pdo = Database::connect();
    foreach (glob(dirname(__DIR__) . '/database/migrations/*.sql') as $file) {
        $pdo->exec(file_get_contents($file));
        fwrite(STDOUT, basename($file) . " applied\n");
    }
} catch (Throwable $error) {
    error_log('Migration failed: ' . $error->getMessage());
    fwrite(STDERR, "Migration failed\n");
    exit(1);
}
