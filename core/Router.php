<?php
declare(strict_types=1);

namespace Core;

/**
 * مسیریاب — متدهای GET و POST
 * روت‌ها از فایل routes.php هر ماژول خوانده می‌شن
 */
class Router
{
    /** @var array<string, array<string, array{module:string, controller:string, method:string}>> */
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, string $module, string $controller, string $method): void
    {
        $this->routes['GET'][$this->normalize($path)] = compact('module', 'controller', 'method');
    }

    public function post(string $path, string $module, string $controller, string $method): void
    {
        $this->routes['POST'][$this->normalize($path)] = compact('module', 'controller', 'method');
    }

    private function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');
        return $path === '/' ? '/' : rtrim($path, '/');
    }

    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';
        // حذف base path اگر پروژه در ساب‌فولدر است
        $scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        if ($scriptDir !== '' && str_starts_with($uri, $scriptDir)) {
            $uri = substr($uri, strlen($scriptDir));
        }
        $path = $this->normalize($uri);

        $route = $this->routes[$method][$path] ?? null;
        if ($route === null) {
            // تلاش برای تطبیق با پارامتر {id} — مثل /user/5
            $route = $this->matchDynamic($method, $path);
        }
        if ($route === null) {
            http_response_code(404);
            echo '<!DOCTYPE html><html dir="rtl" lang="fa"><head><meta charset="utf-8"><title>۴۰۴</title></head>';
            echo '<body style="font-family:Tahoma;text-align:center;padding:60px;"><h1>۴۰۴</h1><p>مسیر پیدا نشد: ' . htmlspecialchars($path) . '</p></body></html>';
            return;
        }

        $class = 'Modules\\' . $route['module'] . '\\Controller';
        if (!class_exists($class)) {
            http_response_code(500);
            echo "Controller not found: {$class}";
            return;
        }
        $controller = new $class();
        $action = $route['method'];
        if (!method_exists($controller, $action)) {
            http_response_code(500);
            echo "Method not found: {$class}::{$action}";
            return;
        }
        $controller->$action(...($route['params'] ?? []));
    }

    /** تطبیق مسیرهای داینامیک مثل /user/{id} */
    private function matchDynamic(string $method, string $path): ?array
    {
        foreach ($this->routes[$method] as $pattern => $route) {
            if (!str_contains($pattern, '{')) { continue; }
            $regex = preg_replace('/\{[^}]+\}/', '([^/]+)', preg_quote($pattern, '#'));
            // preg_quote آکولادها را escape می‌کند — برمی‌گردانیم
            $regex = str_replace(['\\{', '\\}'], ['{', '}'], preg_quote($pattern, '#'));
            $regex = preg_replace('/\{[^}]+\}/', '([^/]+)', $regex);
            if (preg_match('#^' . $regex . '$#', $path, $m)) {
                array_shift($m);
                $route['params'] = array_map('urldecode', $m);
                return $route;
            }
        }
        return null;
    }
}
