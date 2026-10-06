<?php
declare(strict_types=1);

namespace Modules\user\Models;

use Core\Model;

/**
 * مدل کاربر — جدول‌سازی و منطق دیتابیس داخل خود ماژول
 * نقش‌ها قابل‌توسعه‌اند (owner، admin، user، ...)
 */
class UserModel extends Model
{
    protected function migrate(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                full_name TEXT DEFAULT '',
                role TEXT NOT NULL DEFAULT 'user',
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at TEXT NOT NULL DEFAULT (datetime('now'))
            )
        ");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_users_role ON users (role)");
    }

    public function findByUsername(string $username): ?array
    {
        return $this->fetchOne(
            'SELECT * FROM users WHERE username = :u AND is_active = 1',
            [':u' => $username]
        );
    }

    public function findById(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM users WHERE id = :i', [':i' => $id]);
    }

    public function create(string $username, string $password, string $fullName = '', string $role = 'user'): int
    {
        $this->query(
            'INSERT INTO users (username, password_hash, full_name, role) VALUES (:u, :p, :n, :r)',
            [
                ':u' => $username,
                ':p' => password_hash($password, PASSWORD_DEFAULT),
                ':n' => $fullName,
                ':r' => $role,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function verifyPassword(array $user, string $password): bool
    {
        return password_verify($password, $user['password_hash']);
    }

    public function count(): int
    {
        $row = $this->fetchOne('SELECT COUNT(*) AS c FROM users');
        return (int) ($row['c'] ?? 0);
    }

    /** اولین کاربر به‌صورت خودکار owner می‌شود */
    public function ensureOwnerExists(): void
    {
        if ($this->count() === 0) {
            $this->create('admin', 'admin123', 'مدیر سیستم', 'owner');
        }
    }
}
