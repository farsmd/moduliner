<?php
declare(strict_types=1);

namespace Modules\settings\Models;

use Core\Model;

/**
 * مدل تنظیمات — کلید/مقدار با برچسب فارسی برای فرم پنل
 */
class SettingModel extends Model
{
    protected function migrate(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS settings (
                key TEXT PRIMARY KEY,
                value TEXT NOT NULL DEFAULT '',
                label TEXT NOT NULL DEFAULT '',
                type TEXT NOT NULL DEFAULT 'text',
                position INTEGER NOT NULL DEFAULT 0
            )
        ");
    }

    /** تنظیمات پیش‌فرض */
    private function defaults(): array
    {
        return [
            ['key' => 'site_title', 'value' => 'وب‌سایت من', 'label' => 'عنوان سایت', 'type' => 'text', 'position' => 1],
            ['key' => 'site_tagline', 'value' => '', 'label' => 'توضیح کوتاه سایت', 'type' => 'text', 'position' => 2],
            ['key' => 'footer_text', 'value' => 'ساخته شده با مودولاینر', 'label' => 'متن فوتر', 'type' => 'text', 'position' => 3],
        ];
    }

    /** ساخت پیش‌فرض‌ها اگر نباشند (مقادیر موجود دست نمی‌خورد) */
    public function ensureDefaults(): void
    {
        foreach ($this->defaults() as $d) {
            $exists = $this->fetchOne('SELECT key FROM settings WHERE key = :k', [':k' => $d['key']]);
            if ($exists === null) {
                $this->query(
                    'INSERT INTO settings (key, value, label, type, position) VALUES (:k, :v, :l, :t, :p)',
                    [':k' => $d['key'], ':v' => $d['value'], ':l' => $d['label'], ':t' => $d['type'], ':p' => $d['position']]
                );
            }
        }
    }

    public function all(): array
    {
        $this->ensureDefaults();
        return $this->fetchAll('SELECT * FROM settings ORDER BY position ASC');
    }

    /**
     * خواندن یک تنظیم — نقطه دسترسی ماژول‌های دیگر
     * مثال: SettingModel::get('site_title', 'وب‌سایت')
     */
    public static function get(string $key, string $default = ''): string
    {
        $m = new self();
        $row = $m->fetchOne('SELECT value FROM settings WHERE key = :k', [':k' => $key]);
        return $row !== null ? (string) $row['value'] : $default;
    }

    /** ذخیره — فقط کلیدهای شناخته‌شده (کلید ناشناس نادیده گرفته می‌شود) */
    public function saveAll(array $values): void
    {
        $this->ensureDefaults();
        $keys = array_column($this->all(), 'key');
        foreach ($keys as $k) {
            if (array_key_exists($k, $values)) {
                $this->query(
                    'UPDATE settings SET value = :v WHERE key = :k',
                    [':v' => (string) $values[$k], ':k' => $k]
                );
            }
        }
    }
}
