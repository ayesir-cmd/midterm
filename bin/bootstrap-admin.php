<?php

declare(strict_types=1);

require dirname(__DIR__) . '/bootstrap/app.php';
use App\Config\Database;

if (\App\Config\Env::get('APP_ENV', 'local') === 'production') {
    $password = getenv('INITIAL_ADMIN_PASSWORD');
    $email = getenv('INITIAL_ADMIN_EMAIL') ?: null;
    if (!$password || !$email) {
        fwrite(STDERR, "Production bootstrap requires INITIAL_ADMIN_EMAIL and INITIAL_ADMIN_PASSWORD.\n");
        exit(1);
    }
} else {
    $email = 'admin@gmail.com';
    $password = 'admin123';
}

try {
    $pdo = Database::connect();
    $pdo->beginTransaction();
    $existing = $pdo->prepare('SELECT user_id FROM public.app_users WHERE lower(email)=lower(:email)');
    $existing->execute(['email' => $email]);
    if ($existing->fetch()) {
        $pdo->commit();
        fwrite(STDOUT, "Administrator already exists; existing credentials were not reset.\n");
        exit(0);
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
        'INSERT INTO public.app_users(full_name,email,password_hash,role_id,is_active,must_change_password)
         VALUES(:name,:email,:hash,1,true,true)'
    );
    $stmt->execute(['name' => 'Administrator', 'email' => $email, 'hash' => $hash]);
    $pdo->commit();
    fwrite(STDOUT, "Administrator bootstrap created. A password change is required on first login.\n");
} catch (Throwable $error) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    error_log('Administrator bootstrap failed: ' . $error->getMessage());
    fwrite(STDERR, "Administrator bootstrap failed.\n");
    exit(1);
}
