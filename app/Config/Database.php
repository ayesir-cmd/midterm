<?php

declare(strict_types=1);

namespace App\Config;

use PDO;
use RuntimeException;

final class Database
{
    public static function connect(): PDO
    {
        $host = Env::required('DB_HOST');
        $port = Env::get('DB_PORT', '5432') ?? '5432';
        $name = Env::get('DB_NAME', 'postgres') ?? 'postgres';
        $user = Env::required('DB_USERNAME');
        $password = Env::required('DB_PASSWORD');
        $sslMode = Env::get('DB_SSLMODE', 'require') ?? 'require';

        $dsn = "pgsql:host={$host};port={$port};dbname={$name};sslmode={$sslMode}";
        $rootCert = Env::get('DB_SSLROOTCERT');
        if ($sslMode === 'verify-full' && $rootCert) {
            $dsn .= ';sslrootcert=' . $rootCert;
        }

        return new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }

    public static function diagnostic(): array
    {
        try {
            $pdo = self::connect();
            $version = (string) $pdo->query('SELECT version()')->fetchColumn();
            $schema = (bool) $pdo->query("SELECT to_regclass('public.farmers')")->fetchColumn();
            return ['ok' => true, 'server' => str_starts_with($version, 'PostgreSQL'), 'schema' => $schema];
        } catch (\Throwable $error) {
            error_log('Database diagnostic failed: ' . $error->getMessage());
            return ['ok' => false, 'server' => false, 'schema' => false];
        }
    }
}
