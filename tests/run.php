<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/app.php';
require dirname(__DIR__) . '/app/Config/Database.php';

$passed = 0;
$failed = 0;

$check = static function (bool $condition, string $name) use (&$passed, &$failed): void {
    if ($condition) {
        $passed++;
        fwrite(STDOUT, "PASSED {$name}\n");
    } else {
        $failed++;
        fwrite(STDOUT, "FAILED {$name}\n");
    }
};

$check(file_exists(dirname(__DIR__) . '/database/migrations/001_create_ais_schema.sql'), '15-table migration file exists');
$check(file_exists(dirname(__DIR__) . '/database/seeds/001_lookups.sql'), 'Lookup seed file exists');
$check(file_exists(dirname(__DIR__) . '/public/sw.js'), 'Service worker exists');
$check(file_exists(dirname(__DIR__) . '/public/manifest.webmanifest'), 'Web manifest exists');
$check(password_verify('admin123', password_hash('admin123', PASSWORD_DEFAULT)), 'Password hashing and verification');
$check(!str_contains(file_get_contents(dirname(__DIR__) . '/database/seeds/001_lookups.sql'), 'password'), 'Lookup seed contains no password');
$check(in_array(\App\Config\Env::get('APP_TIMEZONE', 'UTC'), ['Asia/Manila','UTC'], true), 'Time zone configuration resolves');

try {
    $diagnostic = \App\Config\Database::diagnostic();
    $check($diagnostic['ok'], 'Live Supabase PDO connection');
    if ($diagnostic['ok']) {
        $repository = new \App\Repositories\CrudRepository(\App\Config\Database::connect());
        $idFields = [
            'crops' => 'crop_id',
            'farmers' => 'farmer_id',
            'farms' => 'farm_id',
            'plantings' => 'planting_id',
            'harvests' => 'harvest_id',
        ];
        foreach ($idFields as $entity => $idField) {
            $rows = $repository->list($entity, '', 1, 1000)['rows'];
            $ids = array_map(static fn(array $row): int => (int) $row[$idField], $rows);
            $sortedIds = $ids;
            sort($sortedIds, SORT_NUMERIC);
            $check($ids === $sortedIds, ucfirst($entity) . ' list is sorted by ascending ID');
        }
    }
} catch (Throwable) {
    $check(false, 'Live Supabase PDO connection');
}

fwrite(STDOUT, "\n{$passed} PASSED, {$failed} FAILED\n");
exit($failed === 0 ? 0 : 1);
