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
     * منوی سایدبار — از دیتابیس (ماژول menu)، فیلترشده بر اساس نقش
     * اگر جدول خالی باشد، خودکار از menu.php ماژول‌ها ایمپورت می‌شود
     * @return array<int, array{module:string,title:string,url:string,icon:string,roles:array}>
     */
    public function menuItems(string $userRole): array
    {
        $menu = new \Modules\menu\Models\MenuModel();
        return $menu->forRole($userRole);
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
