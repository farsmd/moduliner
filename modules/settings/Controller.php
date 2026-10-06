<?php
declare(strict_types=1);

namespace Modules\settings;

use Core\Controller as BaseController;
use Modules\admin\Models\AdminModel;
use Modules\user\Controller as UserController;
use Modules\user\Models\UserModel;
use Modules\settings\Models\SettingModel;

/**
 * کنترلر ماژول تنظیمات — مدیریت در پنل ادمین
 */
class Controller extends BaseController
{
    private SettingModel $settings;

    public function __construct()
    {
        $this->settings = new SettingModel();
    }

    public function index(): void
    {
        $me = $this->requireAdmin();
        $this->adminView('تنظیمات', 'form', [
            'me' => $me,
            'items' => $this->settings->all(),
            'flash' => $this->takeFlash(),
        ]);
    }

    public function save(): void
    {
        $this->requireAdmin();
        $this->checkCsrf();
        $values = $_POST['s'] ?? [];
        $this->settings->saveAll(is_array($values) ? $values : []);
        $this->flash('تنظیمات ذخیره شد.');
        $this->redirect('admin/settings');
    }

    // ─── ابزارهای داخلی ───

    private function requireAdmin(): array
    {
        if (!UserController::hasRole('admin')) {
            $id = $_SESSION['user_id'] ?? null;
            if ($id === null) { $this->redirect('user/login'); }
            http_response_code(403);
            echo 'دسترسی غیرمجاز.';
            exit;
        }
        $user = (new UserModel())->findById((int) ($_SESSION['user_id'] ?? 0));
        return $user ?? ['username' => '?', 'full_name' => '', 'role' => 'admin'];
    }

    private function flash(string $msg): void
    {
        $_SESSION['settings_flash'] = $msg;
    }

    private function takeFlash(): ?string
    {
        $f = $_SESSION['settings_flash'] ?? null;
        unset($_SESSION['settings_flash']);
        return $f;
    }

    private function csrfToken(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    private function checkCsrf(): void
    {
        if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))) {
            http_response_code(403);
            echo 'توکن امنیتی نامعتبر است.';
            exit;
        }
    }

    private function adminView(string $title, string $view, array $data = []): void
    {
        $me = $data['me'];
        $data['pageTitle'] = $title;
        $data['menu'] = (new AdminModel())->menuItems((string) $me['role']);
        $data['csrf'] = $this->csrfToken();
        $data['currentUrl'] = 'admin/settings';
        $this->view('settings', $view, $data);
    }
}
