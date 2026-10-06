<?php
declare(strict_types=1);

namespace Modules\database\Models;

use Core\Database;
use Core\Model;

/**
 * مدل مدیریت دیتابیس — بکاپ، بازگردانی، ساخت و تعویض فایل SQLite
 * فقط owner از طریق کنترلر دسترسی دارد
 */
class DatabaseModel extends Model
{
    protected function migrate(): void
    {
        // جدول اختصاصی ندارد
    }

    private function dbDir(): string
    {
        return BASE_PATH . '/database';
    }

    private function backupDir(): string
    {
        $dir = $this->dbDir() . '/backups';
        if (!is_dir($dir)) { mkdir($dir, 0755, true); }
        return $dir;
    }

    /** نام فایل معتبر: فقط حروف/عدد/خط تیره/زیرخط با پسوند sqlite */
    public function validName(string $name): bool
    {
        return (bool) preg_match('#^[a-zA-Z0-9_-]+\.sqlite$#', $name);
    }

    /** آیا فایل واقعاً دیتابیس SQLite است؟ (بررسی هدر) */
    public function isSqlite(string $path): bool
    {
        if (!is_file($path)) { return false; }
        $head = @file_get_contents($path, false, null, 0, 16);
        return $head !== false && str_starts_with($head, "SQLite format 3\0");
    }

    /** اطلاعات اتصال فعلی */
    public function connectionInfo(): array
    {
        $path = Database::path();
        $pdo = Database::get();
        return [
            'file' => Database::activeFile(),
            'path' => $path,
            'exists' => is_file($path),
            'size' => is_file($path) ? filesize($path) : 0,
            'writable' => is_file($path) ? is_writable($path) : is_writable($this->dbDir()),
            'sqlite_version' => (string) $pdo->query('SELECT sqlite_version()')->fetchColumn(),
        ];
    }

    /** آمار جدول‌ها: نام، تعداد ستون، تعداد ردیف + جمع کل */
    public function tableStats(): array
    {
        $pdo = Database::get();
        $tables = $pdo->query(
            "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name"
        )->fetchAll(\PDO::FETCH_COLUMN);

        $rows = [];
        $totalCols = 0;
        $totalRows = 0;
        foreach ($tables as $t) {
            // نام جدول از sqlite_master آمده — امن است، ولی برای احتیاط کوتیشن می‌کنیم
            $quoted = '"' . str_replace('"', '""', (string) $t) . '"';
            $cols = count($pdo->query("PRAGMA table_info({$quoted})")->fetchAll());
            $count = (int) $pdo->query("SELECT COUNT(*) FROM {$quoted}")->fetchColumn();
            $rows[] = ['name' => (string) $t, 'columns' => $cols, 'rows' => $count];
            $totalCols += $cols;
            $totalRows += $count;
        }
        return ['tables' => $rows, 'tableCount' => count($rows), 'columnCount' => $totalCols, 'rowCount' => $totalRows];
    }

    /** لیست فایل‌های دیتابیس (به‌جز پوشه بکاپ) */
    public function listDatabases(): array
    {
        $active = Database::activeFile();
        $out = [];
        foreach (glob($this->dbDir() . '/*.sqlite') ?: [] as $f) {
            $name = basename($f);
            $out[] = [
                'name' => $name,
                'size' => filesize($f),
                'mtime' => filemtime($f),
                'active' => $name === $active,
            ];
        }
        usort($out, fn($a, $b) => [$b['active'], $a['name']] <=> [$a['active'], $b['name']]);
        return $out;
    }

    /** لیست بکاپ‌ها */
    public function listBackups(): array
    {
        $out = [];
        foreach (glob($this->backupDir() . '/*.sqlite') ?: [] as $f) {
            $out[] = ['name' => basename($f), 'size' => filesize($f), 'mtime' => filemtime($f)];
        }
        usort($out, fn($a, $b) => $b['mtime'] <=> $a['mtime']);
        return $out;
    }

    public function backupPath(string $name): string
    {
        return $this->backupDir() . '/' . $name;
    }

    /** گرفتن بکاپ از دیتابیس فعال — برمی‌گرداند نام فایل بکاپ */
    public function makeBackup(string $prefix = 'backup'): string
    {
        Database::reset(); // بستن کانکشن برای کپی تمیز
        $name = $prefix . '_' . date('Ymd_His') . '.sqlite';
        if (!copy(Database::path(), $this->backupPath($name))) {
            throw new \RuntimeException('بکاپ‌گیری ناموفق بود.');
        }
        return $name;
    }

    /**
     * بازگردانی بکاپ روی دیتابیس فعال
     * اول از وضعیت فعلی بکاپ خودکار می‌گیرد
     */
    public function restoreBackup(string $name): void
    {
        if (!$this->validName($name)) { throw new \RuntimeException('نام بکاپ نامعتبر است.'); }
        $src = $this->backupPath($name);
        if (!is_file($src) || !$this->isSqlite($src)) {
            throw new \RuntimeException('فایل بکاپ معتبر نیست.');
        }
        // بکاپ خودکار از وضعیت فعلی قبل از بازگردانی
        $this->makeBackup('pre_restore');
        Database::reset();
        if (!copy($src, Database::path())) {
            throw new \RuntimeException('بازگردانی ناموفق بود.');
        }
    }

    /** ساخت دیتابیس خالی جدید */
    public function createDatabase(string $name): void
    {
        if (!$this->validName($name)) { throw new \RuntimeException('نام معتبر نیست (فقط حروف انگلیسی، عدد، - و _).'); }
        $path = $this->dbDir() . '/' . $name;
        if (is_file($path)) { throw new \RuntimeException('فایلی با این نام وجود دارد.'); }
        $pdo = new \PDO('sqlite:' . $path, null, null, [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
        // VACUUM فایل را واقعاً می‌سازد (اتصال خالی فایل ۰ بایتی می‌سازد)
        $pdo->exec('VACUUM');
        $pdo = null;
        if (!$this->isSqlite($path)) {
            throw new \RuntimeException('ساخت فایل دیتابیس ناموفق بود.');
        }
    }

    /** تعویض دیتابیس فعال */
    public function switchDatabase(string $name): void
    {
        if (!$this->validName($name)) { throw new \RuntimeException('نام نامعتبر است.'); }
        $path = $this->dbDir() . '/' . $name;
        if (!is_file($path) || !$this->isSqlite($path)) {
            throw new \RuntimeException('فایل دیتابیس معتبر نیست.');
        }
        file_put_contents($this->dbDir() . '/active.txt', $name);
        Database::reset();
    }

    /** حذف بکاپ */
    public function deleteBackup(string $name): void
    {
        if (!$this->validName($name)) { throw new \RuntimeException('نام نامعتبر است.'); }
        $path = $this->backupPath($name);
        if (is_file($path)) { unlink($path); }
    }

    public function formatSize(int $bytes): string
    {
        if ($bytes < 1024) { return $bytes . ' B'; }
        if ($bytes < 1048576) { return round($bytes / 1024, 1) . ' KB'; }
        return round($bytes / 1048576, 2) . ' MB';
    }
}
