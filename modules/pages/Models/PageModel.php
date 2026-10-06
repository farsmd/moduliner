<?php
declare(strict_types=1);

namespace Modules\pages\Models;

use Core\Model;

/**
 * مدل صفحات سایت — محتوای عمومی وب‌سایت
 */
class PageModel extends Model
{
    protected function migrate(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS pages (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                title TEXT NOT NULL,
                slug TEXT NOT NULL UNIQUE,
                content TEXT NOT NULL DEFAULT '',
                status TEXT NOT NULL DEFAULT 'draft',
                is_home INTEGER NOT NULL DEFAULT 0,
                position INTEGER NOT NULL DEFAULT 0,
                created_at TEXT NOT NULL DEFAULT (datetime('now'))
            )
        ");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_pages_status ON pages (status)");
    }

    public function count(): int
    {
        $row = $this->fetchOne('SELECT COUNT(*) AS c FROM pages');
        return (int) ($row['c'] ?? 0);
    }

    /** همه صفحات برای پنل */
    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM pages ORDER BY position ASC, id ASC');
    }

    /** صفحات منتشرشده برای سایت عمومی */
    public function published(): array
    {
        return $this->fetchAll(
            "SELECT * FROM pages WHERE status = 'published' ORDER BY position ASC, id ASC"
        );
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM pages WHERE id = :i', [':i' => $id]);
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->fetchOne('SELECT * FROM pages WHERE slug = :s', [':s' => $slug]);
    }

    /** صفحه اصلی: منتشرشده و is_home، وگرنه اولین منتشرشده */
    public function homepage(): ?array
    {
        $home = $this->fetchOne(
            "SELECT * FROM pages WHERE status = 'published' AND is_home = 1 LIMIT 1"
        );
        if ($home !== null) { return $home; }
        return $this->fetchOne(
            "SELECT * FROM pages WHERE status = 'published' ORDER BY position ASC, id ASC LIMIT 1"
        );
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $sql = 'SELECT id FROM pages WHERE slug = :s';
        $params = [':s' => $slug];
        if ($exceptId !== null) {
            $sql .= ' AND id != :e';
            $params[':e'] = $exceptId;
        }
        return $this->fetchOne($sql, $params) !== null;
    }

    private function nextPosition(): int
    {
        $row = $this->fetchOne('SELECT MAX(position) AS m FROM pages');
        return (int) ($row['m'] ?? 0) + 1;
    }

    public function create(array $data): int
    {
        if (!empty($data['is_home'])) {
            $this->query('UPDATE pages SET is_home = 0');
        }
        $this->query(
            'INSERT INTO pages (title, slug, content, status, is_home, position) VALUES (:t, :s, :c, :st, :h, :p)',
            [
                ':t' => $data['title'],
                ':s' => $data['slug'],
                ':c' => $data['content'],
                ':st' => $data['status'],
                ':h' => !empty($data['is_home']) ? 1 : 0,
                ':p' => $this->nextPosition(),
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        if (!empty($data['is_home'])) {
            $this->query('UPDATE pages SET is_home = 0');
        }
        $this->query(
            'UPDATE pages SET title = :t, slug = :s, content = :c, status = :st, is_home = :h WHERE id = :id',
            [
                ':t' => $data['title'],
                ':s' => $data['slug'],
                ':c' => $data['content'],
                ':st' => $data['status'],
                ':h' => !empty($data['is_home']) ? 1 : 0,
                ':id' => $id,
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->query('DELETE FROM pages WHERE id = :i', [':i' => $id]);
    }

    public function toggleStatus(int $id): void
    {
        $this->query(
            "UPDATE pages SET status = CASE WHEN status = 'published' THEN 'draft' ELSE 'published' END WHERE id = :i",
            [':i' => $id]
        );
    }

    /** ساخت صفحه خوش‌آمد پیش‌فرض اگر جدولی خالی باشد */
    public function ensureDefaultPage(): void
    {
        if ($this->count() > 0) { return; }
        $this->create([
            'title' => 'صفحه اصلی',
            'slug' => 'home',
            'content' => "به وب‌سایت مودولاینر خوش آمدید.\n\nاین یک صفحه نمونه است. از پنل مدیریت (بخش صفحات) می‌توانید آن را ویرایش کنید یا صفحات جدید بسازید.",
            'status' => 'published',
            'is_home' => true,
        ]);
    }
}
