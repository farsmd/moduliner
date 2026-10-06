<?php
declare(strict_types=1);

namespace Modules\update\Models;

use Core\Model;

/**
 * مدل آپدیت — بررسی نسخه از گیت‌هاب و اعمال به‌روزرسانی
 * مخزن از تنظیمات خوانده می‌شود (پیش‌فرض: farsmd/moduliner)
 */
class UpdateModel extends Model
{
    protected function migrate(): void
    {
        // این ماژول جدول اختصاصی نمی‌خواهد — نسخه در فایل نگهداری می‌شود
    }

    public function currentVersion(): string
    {
        return defined('MODULINER_VERSION') ? MODULINER_VERSION : '0.0.0';
    }

    public function repo(): string
    {
        // قابل‌تنظیم از طریق فایل config
        $configFile = BASE_PATH . '/config.php';
        if (is_file($configFile)) {
            $config = require $configFile;
            if (is_array($config) && !empty($config['github_repo'])) {
                return (string) $config['github_repo'];
            }
        }
        return 'farsmd/moduliner';
    }

    /**
     * دریافت آخرین ریلیز از گیت‌هاب
     * @return array{version: string, url: string, notes: string}|null
     */
    public function latestRelease(): ?array
    {
        $repo = $this->repo();
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 15,
                'header' => "User-Agent: Moduliner-Updater\r\nAccept: application/vnd.github+json\r\n",
            ],
        ]);
        $json = @file_get_contents("https://api.github.com/repos/{$repo}/releases/latest", false, $ctx);
        if ($json === false) { return null; }
        $data = json_decode($json, true);
        if (!is_array($data) || empty($data['tag_name'])) { return null; }
        return [
            'version' => ltrim((string) $data['tag_name'], 'v'),
            'url' => (string) ($data['zipball_url'] ?? ''),
            'notes' => (string) ($data['body'] ?? ''),
        ];
    }

    public function hasUpdate(): bool
    {
        $latest = $this->latestRelease();
        if ($latest === null) { return false; }
        return version_compare($latest['version'], $this->currentVersion(), '>');
    }

    /**
     * اعمال آپدیت — دانلود ZIP، بکاپ، استخراج
     * فایل‌های محافظت‌شده هرگز بازنویسی نمی‌شن: database/، config.php
     */
    public function applyUpdate(string $zipUrl): array
    {
        $result = ['ok' => false, 'message' => ''];
        $tmpZip = sys_get_temp_dir() . '/moduliner_update_' . time() . '.zip';
        $tmpDir = sys_get_temp_dir() . '/moduliner_update_' . time();

        try {
            // دانلود
            $ctx = stream_context_create(['http' => ['timeout' => 60, 'header' => "User-Agent: Moduliner-Updater\r\n"]]);
            $zipData = @file_get_contents($zipUrl, false, $ctx);
            if ($zipData === false) {
                throw new \RuntimeException('دانلود آپدیت ناموفق بود.');
            }
            file_put_contents($tmpZip, $zipData);

            // بکاپ خودکار
            $backupDir = BASE_PATH . '/database/backups';
            if (!is_dir($backupDir)) { mkdir($backupDir, 0755, true); }
            $backupFile = $backupDir . '/pre_update_' . date('Ymd_His') . '.zip';
            $this->createBackup($backupFile);

            // استخراج
            $zip = new \ZipArchive();
            if ($zip->open($tmpZip) !== true) {
                throw new \RuntimeException('فایل ZIP خراب است.');
            }
            mkdir($tmpDir, 0755, true);
            $zip->extractTo($tmpDir);
            $zip->close();

            // پیدا کردن پوشه اصلی داخل ZIP (github: repo-tag/)
            $entries = scandir($tmpDir);
            $srcDir = null;
            foreach ($entries as $e) {
                if ($e !== '.' && $e !== '..' && is_dir("$tmpDir/$e")) { $srcDir = "$tmpDir/$e"; break; }
            }
            if ($srcDir === null) {
                throw new \RuntimeException('ساختار ZIP نامعتبر است.');
            }

            // کپی فایل‌ها (به‌جز محافظت‌شده‌ها)
            // install.php نصب‌کننده تک‌فایل است و جزئی از سیستم نیست
            $protected = ['database', 'config.php', '.git', 'install.php'];
            $copied = $this->copyRecursive($srcDir, BASE_PATH, $protected);

            $result = ['ok' => true, 'message' => "آپدیت انجام شد. {$copied} فایل به‌روز شد. بکاپ: " . basename($backupFile)];

        } catch (\Throwable $e) {
            $result = ['ok' => false, 'message' => $e->getMessage()];
        } finally {
            @unlink($tmpZip);
            $this->removeDir($tmpDir);
        }
        return $result;
    }

    private function createBackup(string $backupFile): void
    {
        $zip = new \ZipArchive();
        $zip->open($backupFile, \ZipArchive::CREATE);
        // فقط فایل‌های حیاتی: دیتابیس فعال
        $dbFile = \Core\Database::path();
        if (is_file($dbFile)) {
            $zip->addFile($dbFile, 'database/' . basename($dbFile));
        }
        // اشاره‌گر دیتابیس فعال هم حفظ شود
        $activePtr = BASE_PATH . '/database/active.txt';
        if (is_file($activePtr)) {
            $zip->addFile($activePtr, 'database/active.txt');
        }
        $zip->close();
    }

    private function copyRecursive(string $src, string $dst, array $protected): int
    {
        $count = 0;
        foreach (scandir($src) as $item) {
            if ($item === '.' || $item === '..') { continue; }
            if (in_array($item, $protected, true)) { continue; }
            $s = "$src/$item";
            $d = "$dst/$item";
            if (is_dir($s)) {
                if (!is_dir($d)) { mkdir($d, 0755, true); }
                $count += $this->copyRecursive($s, $d, $protected);
            } else {
                if (@copy($s, $d)) { $count++; }
            }
        }
        return $count;
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) { return; }
        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') { continue; }
            $p = "$dir/$item";
            is_dir($p) ? $this->removeDir($p) : @unlink($p);
        }
        @rmdir($dir);
    }
}
