<?php
declare(strict_types=1);

namespace Core;

/**
 * اتصال سینگلتون به SQLite — فقط یک کانکشن در کل چرخه درخواست
 */
class Database
{
    private static ?\PDO $instance = null;

    public static function get(): \PDO
    {
        if (self::$instance === null) {
            $dir = dirname(DB_PATH);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            self::$instance = new \PDO('sqlite:' . DB_PATH, null, null, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
            // کلید خارجی فعال
            self::$instance->exec('PRAGMA foreign_keys = ON');
        }
        return self::$instance;
    }

    /** جلوگیری از ساخت نمونه مستقیم */
    private function __construct() {}
    private function __clone() {}
}
