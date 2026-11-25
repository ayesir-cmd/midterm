# Rootsy

Rootsy is a server-rendered PHP 8.2+ agriculture information system using Supabase PostgreSQL. The project is implemented as 15 normalized tables with prepared PDO queries, role middleware, CSRF protection, audit logging, reports, PWA assets, and Render deployment configuration.

## Local setup

1. Copy `.env.example` to `.env`.
2. Set the Supabase host, port, database, restricted runtime user, password, and TLS mode.
3. Run `php bin/migrate.php`.
4. Run `php bin/seed.php` for local lookup and synthetic demo data.
5. Run `php bin/bootstrap-admin.php` once.
6. Start with `php -S localhost:8080 -t public`.

The local demo Administrator is `admin@gmail.com` with initial password `admin123`. These values are defined only in the bootstrap CLI and are hashed with `password_hash()` before storage. The account must change its password before access continues. Remove the demo credential before public use.

## Production bootstrap

Set `APP_ENV=production`, `INITIAL_ADMIN_EMAIL`, and `INITIAL_ADMIN_PASSWORD` to environment-provided values, then run `php bin/bootstrap-admin.php`. The bootstrap is idempotent and never resets an existing account. Demo seeding requires `ALLOW_DEMO_SEED=true`, even for a production environment, and is therefore not automatic.

## Supabase

Use a Session Pooler connection on IPv4-only Render hosting or a direct connection where IPv6/networking is supported. Set `DB_SSLMODE=verify-full` and `DB_SSLROOTCERT` when the configured CA is available. Use migration credentials only in migration jobs and a least-privilege runtime role for the web process.

## Verification status

PHP syntax has been checked locally. Live Supabase, migration execution, Docker build, Render deployment, HTTPS, login, CRUD, PWA installation, and screenshots were not verifiable without credentials/available Docker tooling. Database and deployment diagnostics must be run in the target environment.
