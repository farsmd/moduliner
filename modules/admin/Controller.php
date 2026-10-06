<?php
declare(strict_types=1);

namespace Modules\admin;

use Core\Controller as BaseController;
use Modules\admin\Models\AdminModel;
use Modules\user\Controller as UserController;
use Modules\user\Models\UserModel;

/**
 * کنترلر پنل مدیریت
 * همه روت‌ها: لاگین + نقش admin/owner
 * تغییر نقش و فعال/غیرفعال‌سازی: فقط owner
 */
class Controller extends BaseController
{
    private AdminModel $admin;
    private UserModel $users;
    private array $me;

    public function __construct()
    {
        $this->admin = new AdminModel();
        $this->users = new UserModel();
        $this->me = $this->requireAdmin();
    }

    /** داشبورد */
    public function dashboard(): void
    {
        $stats = [
            'users' => $this->users->count(),
            'modules' => $this->admin->moduleCount(),
            'version' => defined('MODULINER_VERSION') ? MODULINER_VERSION : '?',
            'db_size' => $this->admin->databaseSize(),
            'php' => PHP_VERSION,
        ];
        $this->render('داشبورد', 'dashboard', ['stats' => $stats]);
    }

    /** لیست کاربران */
    public function users(): void
    {
        $this->render('کاربران', 'users', ['users' => $this->users->all()]);
    }

    /** فرم ویرایش کاربر (فقط owner) */
    public function editUser(string $id): void
    {
        $this->requireOwner();
        $user = $this->users->findById((int) $id);
        if ($user === null) { $this->notFound('کاربر پیدا نشد.'); }
        $this->render('ویرایش کاربر', 'user_form', [
            'user' => $user,
            'roles' => $this->availableRoles(),
            'csrf' => $this->csrfToken(),
        ]);
    }

    /** ذخیره ویرایش کاربر (فقط owner) */
    public function saveUser(string $id): void
    {
        $this->requireOwner();
        $user = $this->users->findById((int) $id);
        if ($user === null) { $this->notFound('کاربر پیدا نشد.'); }
        $this->checkCsrf();

        $role = (string) ($_POST['role'] ?? 'user');
        if (!in_array($role, $this->availableRoles(), true)) {
            $this->render('ویرایش کاربر', 'user_form', [
                'user' => $user, 'roles' => $this->availableRoles(),
                'csrf' => $this->csrfToken(), 'error' => 'نقش نامعتبر است.',
            ]);
            return;
        }
        // owner نمی‌تواند نقش خودش را تنزل دهد (قفل‌شدن بیرون از سیستم)
        if ((int) $user['id'] === (int) $this->me['id'] && $role !== 'owner') {
            $this->render('ویرایش کاربر', 'user_form', [
                'user' => $user, 'roles' => $this->availableRoles(),
                'csrf' => $this->csrfToken(), 'error' => 'نمی‌توانید نقش خودتان را تغییر دهید.',
            ]);
            return;
        }
        $this->users->updateRole((int) $user['id'], $role);
        $this->redirect('admin/users');
    }

    /** فعال / غیرفعال کردن کاربر (فقط owner) */
    public function toggleUser(string $id): void
    {
        $this->requireOwner();
        $user = $this->users->findById((int) $id);
        if ($user === null) { $this->notFound('کاربر پیدا نشد.'); }
        $this->checkCsrf();
        // خودش را غیرفعال نکند
        if ((int) $user['id'] === (int) $this->me['id']) {
            $this->redirect('admin/users');
            return;
        }
        $this->users->setActive((int) $user['id'], !(bool) $user['is_active']);
        $this->redirect('admin/users');
    }

    // ─── ابزارهای داخلی ───

    /** نقش‌های قابل‌تخصیص — قابل‌توسعه */
    private function availableRoles(): array
    {
        return ['owner', 'admin', 'user'];
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

    /** گارد: لاگین + نقش admin/owner */
    private function requireAdmin(): array
    {
        $id = $_SESSION['user_id'] ?? null;
        if ($id === null) { $this->redirect('user/login'); }
        $user = $this->users->findById((int) $id);
        if ($user === null || !(bool) $user['is_active']) {
            unset($_SESSION['user_id']);
            $this->redirect('user/login');
        }
        if ($user['role'] !== 'owner' && $user['role'] !== 'admin') {
            http_response_code(403);
            echo '<!DOCTYPE html><html dir="rtl" lang="fa"><head><meta charset="utf-8"><title>۴۰۳</title></head><body style="font-family:Tahoma;text-align:center;padding:60px;"><h1>۴۰۳</h1><p>دسترسی غیرمجاز.</p></body></html>';
            exit;
        }
        return $user;
    }

    /** گارد: فقط owner */
    private function requireOwner(): void
    {
        if (($this->me['role'] ?? '') !== 'owner') {
            http_response_code(403);
            echo 'فقط owner مجاز است.';
            exit;
        }
    }

    /** رندر ویو داخل لی‌آوت ادمین */
    private function render(string $title, string $view, array $data = []): void
    {
        $data['pageTitle'] = $title;
        $data['me'] = $this->me;
        $data['menu'] = $this->admin->menuItems((string) $this->me['role']);
        $data['csrf'] = $this->csrfToken();
        $data['currentUrl'] = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', '/');
        // حذف base path و index.php مثل روتر
        $data['currentUrl'] = preg_replace('#(^|/)index\.php$#', '', $data['currentUrl']);
        $data['currentUrl'] = trim((string) $data['currentUrl'], '/');
        $this->view('admin', $view, $data);
    }
}
