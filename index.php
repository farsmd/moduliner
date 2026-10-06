<?php
/**
 * Moduliner — مینی‌فریم‌ورک PHP ماژولار (HMVC)
 * نسخه ۰٫۰٫۱
 *
 * نقطه ورود (Front Controller) — همه درخواست‌ها از طریق .htaccess به اینجا می‌آیند
 */

declare(strict_types=1);

define('MODULINER_VERSION', '0.2.0');
define('BASE_PATH', __DIR__);
define('CORE_PATH', BASE_PATH . '/core');
define('MODULES_PATH', BASE_PATH . '/modules');
// مسیر پایه برای نصب در ساب‌فولدر (مثل /shop) — در روت خالی است
$__scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php')), '/');
if ($__scriptDir === '' || $__scriptDir === '/' || $__scriptDir === '.') { $__scriptDir = ''; }
define('BASE_URL', $__scriptDir);
define('DB_PATH', BASE_PATH . '/database/app.sqlite');

// بارگذار خودکار هوشمند — کلاس‌ها بر اساس namespace در لحظه نیاز لود می‌شن
spl_autoload_register(function (string $class): void {
    // Core\X → core/X.php
    if (str_starts_with($class, 'Core\\')) {
        $file = CORE_PATH . '/' . substr($class, 5) . '.php';
        if (is_file($file)) { require_once $file; return; }
    }
    // Modules\{name}\X → modules/{name}/X.php (با تبدیل namespace به مسیر)
    if (str_starts_with($class, 'Modules\\')) {
        $parts = explode('\\', $class);
        // Modules\user\Controller → modules/user/Controller.php
        // Modules\user\Models\UserModel → modules/user/Models/UserModel.php
        array_shift($parts); // حذف Modules
        $module = strtolower(array_shift($parts));
        $file = MODULES_PATH . '/' . $module . '/' . implode('/', $parts) . '.php';
        if (is_file($file)) { require_once $file; return; }
    }
});

// اگر نصب نشده → پیام راهنما (نصب فقط با نصب‌کننده تک‌فایل)
if (!is_file(BASE_PATH . '/database/installed.lock')) {
    http_response_code(503);
    ?>
    <!DOCTYPE html>
    <html dir="rtl" lang="fa">
    <head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>مودولاینر — نصب نشده</title>
    <style>body{font-family:Tahoma,sans-serif;background:#1a1a2e;color:#eee;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:16px}.box{background:#16213e;border-radius:12px;padding:32px;max-width:480px;text-align:center}h1{color:#e8b923;font-size:20px;margin-bottom:12px}p{font-size:14px;color:#aaa;line-height:1.9}code{background:#0f0f1a;padding:2px 8px;border-radius:4px;color:#e8b923}</style>
    </head>
    <body><div class="box">
        <h1>مودولاینر نصب نشده است</h1>
        <p>فایل <code dir="ltr">install.php</code> را در همین پوشه آپلود کنید و در مرورگر اجراش کنید تا نصب انجام شود.</p>
    </div></body></html>
    <?php
    exit;
}

// راه‌اندازی اپلیکیشن
require_once CORE_PATH . '/App.php';
(new Core\App())->run();
