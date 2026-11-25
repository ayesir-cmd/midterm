<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/app.php';
use App\Config\Database;

if (\App\Config\Env::get('APP_ENV', 'local') === 'production' &&
    \App\Config\Env::get('ALLOW_DEMO_SEED', 'false') !== 'true') {
    fwrite(STDERR, "Demo seeding is disabled in production. Set ALLOW_DEMO_SEED=true explicitly if authorized.\n");
    exit(1);
}

try {
    $pdo = Database::connect();
    foreach (glob(dirname(__DIR__) . '/database/seeds/*.sql') as $file) {
        $pdo->exec(file_get_contents($file));
    }
    fwrite(STDOUT, "Lookups and demo seed scripts applied.\n");
} catch (Throwable $error) {
    error_log('Seed failed: ' . $error->getMessage());
    fwrite(STDERR, "Seed failed\n");
    exit(1);
}
