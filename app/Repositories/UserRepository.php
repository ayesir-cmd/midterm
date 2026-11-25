<?php

declare(strict_types=1);

namespace App\Repositories;

final class UserRepository extends BaseRepository
{
    public function findForLogin(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.user_id,u.full_name,u.email,u.password_hash,u.is_active,u.must_change_password,
                    u.failed_login_count,u.locked_until,r.role_name
             FROM public.app_users u JOIN public.app_roles r ON r.role_id=u.role_id
             WHERE lower(u.email)=lower(:email) LIMIT 1'
        );
        $stmt->execute(['email' => trim($email)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function recordLogin(int $userId): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE public.app_users SET last_login_at=now(),failed_login_count=0,locked_until=NULL WHERE user_id=:id'
        );
        $stmt->execute(['id' => $userId]);
    }

    public function list(): array
    {
        return $this->pdo->query(
            'SELECT u.user_id,u.full_name,u.email,u.is_active,u.must_change_password,u.last_login_at,r.role_name
             FROM public.app_users u JOIN public.app_roles r ON r.role_id=u.role_id ORDER BY u.full_name'
        )->fetchAll();
    }

    public function roles(): array
    {
        return $this->pdo->query('SELECT role_id,role_name FROM public.app_roles ORDER BY role_id')->fetchAll();
    }

    public function create(array $values): int
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO public.app_users(full_name,email,password_hash,role_id,is_active,must_change_password)
                 VALUES(:full_name,:email,:password_hash,:role_id,true,true) RETURNING user_id'
            );
            $stmt->execute($values);
            $id = (int) $stmt->fetchColumn();
            $this->audit((int) ($_SESSION['user']['user_id'] ?? 0), 'INSERT', 'app_users', $id, null, ['full_name' => $values['full_name'], 'email' => $values['email'], 'role_id' => $values['role_id']]);
            $this->pdo->commit();
            return $id;
        } catch (\Throwable $error) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $error;
        }
    }

    public function setPassword(int $userId, string $hash): void
    {
        $stmt = $this->pdo->prepare(
            'UPDATE public.app_users SET password_hash=:hash,must_change_password=false WHERE user_id=:id'
        );
        $stmt->execute(['hash' => $hash, 'id' => $userId]);
    }
}
