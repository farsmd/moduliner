<?php
declare(strict_types=1);

namespace Modules\menu\Models;

use Core\Model;

/**
 * مدل مدیریت منو — آیتم‌های منوی پنل ادمین در دیتابیس
 * ستون icon: کلید آیکون آماده یا کد SVG خام
 */
class MenuModel extends Model
{
    protected function migrate(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS menu_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                module TEXT NOT NULL DEFAULT 'custom',
                title TEXT NOT NULL,
                url TEXT NOT NULL,
                icon TEXT NOT NULL DEFAULT 'grid',
                roles TEXT NOT NULL DEFAULT '[\"owner\",\"admin\"]',
                position INTEGER NOT NULL DEFAULT 0,
                is_active INTEGER NOT NULL DEFAULT 1
            )
        ");
        $this->db->exec("CREATE UNIQUE INDEX IF NOT EXISTS idx_menu_module_url ON menu_items (module, url)");
    }

    /** آیکون‌های آماده */
    public function icons(): array
    {
        $s = fn($inner) => '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' . $inner . '</svg>';
        return [
            'grid' => $s('<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>'),
            'users' => $s('<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>'),
            'database' => $s('<ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"/><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/>'),
            'update' => $s('<path d="M21 12a9 9 0 1 1-2.64-6.36"/><polyline points="21 3 21 9 15 9"/>'),
            'menu' => $s('<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>'),
            'settings' => $s('<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>'),
            'file' => $s('<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>'),
            'home' => $s('<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>'),
        ];
    }

    /** تبدیل مقدار ستون icon به SVG نهایی */
    public function resolveIcon(string $value): string
    {
        $value = trim($value);
        if (str_starts_with($value, '<svg')) { return $value; }
        return $this->icons()[$value] ?? $this->icons()['grid'];
    }

    /** نام کلید آیکون اگر آماده باشد، وگرنه 'custom' */
    public function iconKey(string $value): string
    {
        $value = trim($value);
        if (str_starts_with($value, '<svg')) {
            $key = array_search($value, $this->icons(), true);
            return $key !== false ? (string) $key : 'custom';
        }
        return isset($this->icons()[$value]) ? $value : 'grid';
    }

    public function count(): int
    {
        $row = $this->fetchOne('SELECT COUNT(*) AS c FROM menu_items');
        return (int) ($row['c'] ?? 0);
    }

    /** همه آیتم‌ها به ترتیب position */
    public function all(): array
    {
        return $this->fetchAll('SELECT * FROM menu_items ORDER BY position ASC, id ASC');
    }

    public function find(int $id): ?array
    {
        return $this->fetchOne('SELECT * FROM menu_items WHERE id = :i', [':i' => $id]);
    }

    /**
     * آیتم‌های منو برای نقش کاربر — خروجی سازگار با سایدبار ادمین
     * در هر فراخوانی، آیتم‌های جدید menu.php ماژول‌ها خودکار اضافه می‌شوند
     * (فقط موارد جدید؛ تغییرات دستی دست‌نخورده می‌ماند)
     */
    public function forRole(string $role): array
    {
        $this->syncFromModules();
        $items = [];
        foreach ($this->all() as $row) {
            if (!(bool) $row['is_active']) { continue; }
            $roles = json_decode((string) $row['roles'], true) ?: [];
            if ($role !== 'owner' && !in_array($role, $roles, true)) { continue; }
            $items[] = [
                'module' => (string) $row['module'],
                'title' => (string) $row['title'],
                'url' => (string) $row['url'],
                'icon' => $this->resolveIcon((string) $row['icon']),
                'roles' => $roles,
            ];
        }
        return $items;
    }

    /**
     * ایمپورت آیتم‌های menu.php ماژول‌ها — فقط موارد جدید (تکراری نمی‌سازد)
     * برمی‌گرداند تعداد آیتم‌های اضافه‌شده
     */
    public function syncFromModules(): int
    {
        $added = 0;
        if (!is_dir(MODULES_PATH)) { return 0; }
        foreach (scandir(MODULES_PATH) as $module) {
            if ($module === '.' || $module === '..') { continue; }
            $menuFile = MODULES_PATH . "/{$module}/menu.php";
            if (!is_file($menuFile)) { continue; }
            $entries = require $menuFile;
            if (!is_array($entries)) { continue; }
            foreach ($entries as $e) {
                if (!is_array($e) || empty($e['title']) || empty($e['url'])) { continue; }
                $exists = $this->fetchOne(
                    'SELECT id FROM menu_items WHERE module = :m AND url = :u',
                    [':m' => $module, ':u' => (string) $e['url']]
                );
                if ($exists !== null) { continue; }
                // INSERT OR IGNORE: امن در برابر race-condition (درخواست‌های هم‌زمان)
                $this->query(
                    'INSERT OR IGNORE INTO menu_items (module, title, url, icon, roles, position) VALUES (:m, :t, :u, :i, :r, :p)',
                    [
                        ':m' => $module,
                        ':t' => (string) $e['title'],
                        ':u' => (string) $e['url'],
                        ':i' => (string) ($e['icon'] ?? 'grid'),
                        ':r' => json_encode(array_values((array) ($e['roles'] ?? ['owner', 'admin'])), JSON_UNESCAPED_UNICODE),
                        ':p' => $this->nextPosition(),
                    ]
                );
                $added++;
            }
        }
        return $added;
    }

    private function nextPosition(): int
    {
        $row = $this->fetchOne('SELECT MAX(position) AS m FROM menu_items');
        return (int) ($row['m'] ?? 0) + 1;
    }

    public function create(array $data): int
    {
        $this->query(
            'INSERT INTO menu_items (module, title, url, icon, roles, position, is_active) VALUES (:m, :t, :u, :i, :r, :p, :a)',
            [
                ':m' => 'custom',
                ':t' => $data['title'],
                ':u' => $data['url'],
                ':i' => $data['icon'],
                ':r' => json_encode(array_values($data['roles']), JSON_UNESCAPED_UNICODE),
                ':p' => $this->nextPosition(),
                ':a' => $data['is_active'] ? 1 : 0,
            ]
        );
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $this->query(
            'UPDATE menu_items SET title = :t, url = :u, icon = :i, roles = :r, is_active = :a WHERE id = :id',
            [
                ':t' => $data['title'],
                ':u' => $data['url'],
                ':i' => $data['icon'],
                ':r' => json_encode(array_values($data['roles']), JSON_UNESCAPED_UNICODE),
                ':a' => $data['is_active'] ? 1 : 0,
                ':id' => $id,
            ]
        );
    }

    public function delete(int $id): void
    {
        $this->query('DELETE FROM menu_items WHERE id = :i', [':i' => $id]);
    }

    public function toggle(int $id): void
    {
        $this->query('UPDATE menu_items SET is_active = 1 - is_active WHERE id = :i', [':i' => $id]);
    }

    /** جابه‌جایی بالا/پایین — جابه‌جایی position با همسایه */
    public function move(int $id, string $dir): void
    {
        $all = $this->all();
        $idx = null;
        foreach ($all as $k => $r) {
            if ((int) $r['id'] === $id) { $idx = $k; break; }
        }
        if ($idx === null) { return; }
        $target = $dir === 'up' ? $idx - 1 : $idx + 1;
        if (!isset($all[$target])) { return; }
        $a = $all[$idx];
        $b = $all[$target];
        $this->query('UPDATE menu_items SET position = :p WHERE id = :i', [':p' => $b['position'], ':i' => (int) $a['id']]);
        $this->query('UPDATE menu_items SET position = :p WHERE id = :i', [':p' => $a['position'], ':i' => (int) $b['id']]);
    }
}
