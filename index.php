<?php
/**
 * Moduliner — مینی‌فریم‌ورک PHP ماژولار (HMVC)
 * نسخه ۰٫۰٫۱
 *
 * نقطه ورود (Front Controller) — همه درخواست‌ها از طریق .htaccess به اینجا می‌آیند
 */

declare(strict_types=1);

define('MODULINER_VERSION', '0.0.3');
define('BASE_PATH', __DIR__);
define('CORE_PATH', BASE_PATH . '/core');
define('MODULES_PATH', BASE_PATH . '/modules');
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

// اگر نصب نشده → نصاب خودکار
if (!is_file(BASE_PATH . '/database/installed.lock') && is_file(BASE_PATH . '/install.php')) {
    // به‌جز خود صفحه نصب
    $reqUri = $_SERVER['REQUEST_URI'] ?? '';
    if (!str_contains($reqUri, 'install.php')) {
        header('Location: install.php');
        exit;
    }
}

// راه‌اندازی اپلیکیشن
require_once CORE_PATH . '/App.php';
(new Core\App())->run();
