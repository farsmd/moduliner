<?php
declare(strict_types=1);

namespace Core;

/**
 * اتصال سینگلتون به SQLite — فقط یک کانکشن در کل چرخه درخواست
 * فایل فعال از database/active.txt خوانده می‌شود (پیش‌فرض: app.sqlite)
 */
class Database
{
    private static ?\PDO $instance = null;

    public static function get(): \PDO
    {
        if (self::$instance === null) {
            $dir = dirname(self::path());
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            self::$instance = new \PDO('sqlite:' . self::path(), null, null, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
            // کلید خارجی فعال
            self::$instance->exec('PRAGMA foreign_keys = ON');
        }
        return self::$instance;
    }

    /**
     * مسیر فایل دیتابیس فعال
     * نام فایل از database/active.txt خوانده می‌شود؛ اگر نباشد app.sqlite
     */
    public static function path(): string
    {
        $dir = BASE_PATH . '/database';
        $active = @file_get_contents($dir . '/active.txt');
        $active = $active !== false ? trim($active) : '';
        if (!preg_match('#^[a-zA-Z0-9_.-]+\.sqlite$#', $active)) {
            $active = 'app.sqlite';
        }
        return $dir . '/' . $active;
    }

    /** نام فایل دیتابیس فعال (بدون مسیر) */
    public static function activeFile(): string
    {
        return basename(self::path());
    }

    /** بستن کانکشن فعلی — لازم بعد از بازگردانی یا تعویض دیتابیس */
    public static function reset(): void
    {
        self::$instance = null;
    }

    /** جلوگیری از ساخت نمونه مستقیم */
    private function __construct() {}
    private function __clone() {}
}
