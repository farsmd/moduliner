<?php
declare(strict_types=1);

namespace Modules\admin\Models;

use Core\Model;

/**
 * مدل ادمین — اسکن منوی ماژول‌ها و آمار سیستم
 * هر ماژول می‌تواند فایل menu.php داشته باشد:
 *   return [['title' => '...', 'url' => '...', 'icon' => '<svg…>', 'roles' => ['owner','admin']]];
 */
class AdminModel extends Model
{
    protected function migrate(): void
    {
        // این ماژول جدول اختصاصی ندارد
    }

    /**
     * منوی همه ماژول‌ها، فیلترشده بر اساس نقش کاربر
     * @return array<int, array{module:string,title:string,url:string,icon:string,roles:array}>
     */
    public function menuItems(string $userRole): array
    {
        $items = [];
        if (!is_dir(MODULES_PATH)) { return $items; }
        foreach (scandir(MODULES_PATH) as $module) {
            if ($module === '.' || $module === '..') { continue; }
            $menuFile = MODULES_PATH . "/{$module}/menu.php";
            if (!is_file($menuFile)) { continue; }
            $entries = require $menuFile;
            if (!is_array($entries)) { continue; }
            foreach ($entries as $e) {
                if (!is_array($e) || empty($e['title']) || empty($e['url'])) { continue; }
                $roles = $e['roles'] ?? ['owner', 'admin'];
                // owner همه‌چیز را می‌بیند
                if ($userRole !== 'owner' && !in_array($userRole, (array) $roles, true)) { continue; }
                $items[] = [
                    'module' => $module,
                    'title' => (string) $e['title'],
                    'url' => (string) $e['url'],
                    'icon' => (string) ($e['icon'] ?? ''),
                    'roles' => (array) $roles,
                ];
            }
        }
        return $items;
    }

    /** تعداد ماژول‌های نصب‌شده */
    public function moduleCount(): int
    {
        if (!is_dir(MODULES_PATH)) { return 0; }
        $n = 0;
        foreach (scandir(MODULES_PATH) as $module) {
            if ($module === '.' || $module === '..') { continue; }
            if (is_file(MODULES_PATH . "/{$module}/routes.php")) { $n++; }
        }
        return $n;
    }

    /** حجم فایل دیتابیس */
    public function databaseSize(): string
    {
        $file = \Core\Database::path();
        if (!is_file($file)) { return '—'; }
        $bytes = filesize($file);
        if ($bytes < 1024) { return $bytes . ' B'; }
        if ($bytes < 1048576) { return round($bytes / 1024, 1) . ' KB'; }
        return round($bytes / 1048576, 2) . ' MB';
    }
}
