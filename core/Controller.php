<?php
declare(strict_types=1);

namespace Core;

/**
 * کلاس پایه کنترلر — رندر ویو و هدایت
 */
abstract class Controller
{
    /**
     * رندر ویوی ماژول: modules/{module}/views/{view}.php
     */
    protected function view(string $module, string $view, array $data = []): void
    {
        $file = MODULES_PATH . "/{$module}/views/{$view}.php";
        if (!is_file($file)) {
            http_response_code(500);
            echo "View not found: {$module}/{$view}";
            return;
        }
        extract($data, EXTR_SKIP);
        require $file;
    }

    protected function redirect(string $url): void
    {
        // آدرس نسبی → مطلق با مسیر پایه (کار در ساب‌فولدر هم درست است)
        if (!str_starts_with($url, '/') && !preg_match('#^https?://#i', $url)) {
            $url = BASE_URL . '/' . ltrim($url, '/');
        }
        header('Location: ' . $url);
        exit;
    }

    protected function json(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /** نمایش خطای ۴۰۴ */
    protected function notFound(string $message = 'صفحه پیدا نشد'): void
    {
        http_response_code(404);
        echo '<!DOCTYPE html><html dir="rtl" lang="fa"><head><meta charset="utf-8"><title>۴۰۴</title></head>';
        echo '<body style="font-family:Tahoma;text-align:center;padding:60px;"><h1>۴۰۴</h1><p>' . htmlspecialchars($message) . '</p></body></html>';
        exit;
    }
}
