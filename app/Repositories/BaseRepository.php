<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

abstract class BaseRepository
{
    public function __construct(protected PDO $pdo)
    {
    }

    protected function positiveInt(mixed $value): ?int
    {
        $value = filter_var($value, FILTER_VALIDATE_INT);
        return $value !== false && $value > 0 ? $value : null;
    }

    public function count(string $table): int
    {
        $allowed = ['farmers','farms','crops','plantings','harvests','app_users','audit_logs'];
        if (!in_array($table, $allowed, true)) {
            throw new \InvalidArgumentException('Invalid count table');
        }
        return (int) $this->pdo->query("SELECT COUNT(*) FROM public.{$table}")->fetchColumn();
    }

    protected function audit(?int $actorId, string $action, string $table, mixed $key, ?array $old, ?array $new): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO public.audit_logs(actor_user_id,action,entity_table,record_pk,old_values,new_values)
             VALUES(:actor,:action,:table,:key,:old,:new)'
        );
        $statement->execute([
            'actor' => $actorId,
            'action' => $action,
            'table' => $table,
            'key' => $key === null ? null : (string) $key,
            'old' => $old === null ? null : json_encode($old, JSON_THROW_ON_ERROR),
            'new' => $new === null ? null : json_encode($new, JSON_THROW_ON_ERROR),
        ]);
    }
}
