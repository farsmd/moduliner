<?php
declare(strict_types=1);

namespace Core;

/**
 * موتور اصلی — اسکن ماژول‌ها، بارگذاری روت‌ها، اجرای روتر
 */
class App
{
    private Router $router;

    public function __construct()
    {
        $this->router = new Router();
        $this->startSession();
    }

    public function run(): void
    {
        $this->loadModuleRoutes();
        $this->router->dispatch();
    }

    /** اسکن خودکار پوشه modules و خواندن routes.php هر ماژول */
    private function loadModuleRoutes(): void
    {
        if (!is_dir(MODULES_PATH)) { return; }
        foreach (scandir(MODULES_PATH) as $module) {
            if ($module === '.' || $module === '..') { continue; }
            $routesFile = MODULES_PATH . "/{$module}/routes.php";
            if (!is_file($routesFile)) { continue; }
            // هر routes.php به متغیر $router دسترسی دارد
            $router = $this->router;
            $moduleName = $module;
            require $routesFile;
        }
    }

    private function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_name('MODULINER_SESS');
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }
    }
}
