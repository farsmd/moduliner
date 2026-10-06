<?php
declare(strict_types=1);

namespace Core;

/**
 * کلاس پایه مدل — هر مدل ماژول از این ارث می‌برد
 * هسته فقط اتصال را می‌دهد؛ جدول‌سازی و کوئری با خود ماژول است
 */
abstract class Model
{
    protected \PDO $db;

    public function __construct()
    {
        $this->db = Database::get();
        $this->migrate();
    }

    /**
     * هر مدل جدول‌های خودش را اینجا می‌سازد (idempotent)
     */
    abstract protected function migrate(): void;

    protected function query(string $sql, array $params = []): \PDOStatement
    {
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st;
    }

    protected function fetchOne(string $sql, array $params = []): ?array
    {
        $row = $this->query($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    protected function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }
}
