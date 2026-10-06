<?php
declare(strict_types=1);

namespace Modules\database;

use Core\Controller as BaseController;
use Modules\admin\Models\AdminModel;
use Modules\user\Controller as UserController;
use Modules\user\Models\UserModel;
use Modules\database\Models\DatabaseModel;

/**
 * کنترلر مدیریت دیتابیس — فقط owner
 * از لی‌آوت پنل ادمین استفاده می‌کند
 */
class Controller extends BaseController
{
    private DatabaseModel $dbm;
    private AdminModel $admin;
    private UserModel $users;
    private array $me;

    public function __construct()
    {
        if (!UserController::hasRole('owner')) {
            http_response_code(403);
            echo 'فقط owner مجاز است.';
            exit;
        }
        $this->dbm = new DatabaseModel();
        $this->admin = new AdminModel();
        $this->users = new UserModel();
        $id = (int) ($_SESSION['user_id'] ?? 0);
        $this->me = $this->users->findById($id) ?? ['username' => '?', 'full_name' => '', 'role' => 'owner'];
    }

    /** نمای کلی: اتصال، آمار، دیتابیس‌ها، بکاپ‌ها */
    public function overview(): void
    {
        $this->render('مدیریت دیتابیس', 'overview', [
            'conn' => $this->dbm->connectionInfo(),
            'stats' => $this->dbm->tableStats(),
            'databases' => $this->dbm->listDatabases(),
            'backups' => $this->dbm->listBackups(),
            'flash' => $this->takeFlash(),
        ]);
    }

    /** گرفتن بکاپ */
    public function backup(): void
    {
        $this->checkCsrf();
        try {
            $name = $this->dbm->makeBackup();
            $this->flash('بکاپ «' . $name . '» ساخته شد.');
        } catch (\Throwable $e) {
            $this->flash($e->getMessage(), true);
        }
        $this->redirect('admin/database');
    }

    /** بازگردانی بکاپ */
    public function restore(): void
    {
        $this->checkCsrf();
        try {
            $this->dbm->restoreBackup((string) ($_POST['file'] ?? ''));
            // دیتابیس عوض شد — سشن کاربر فعلی ممکن است معتبر نباشد
            $this->logoutAndGoLogin('بازگردانی انجام شد (از وضعیت قبلی بکاپ خودکار گرفته شد). لطفاً دوباره وارد شوید.');
        } catch (\Throwable $e) {
            $this->flash($e->getMessage(), true);
        }
        $this->redirect('admin/database');
    }

    /** تعویض دیتابیس فعال */
    public function switchDb(): void
    {
        $this->checkCsrf();
        try {
            $this->dbm->switchDatabase((string) ($_POST['file'] ?? ''));
            // کاربر فعلی ممکن است در دیتابیس جدید وجود نداشته باشد
            $this->logoutAndGoLogin('دیتابیس فعال تغییر کرد. لطفاً دوباره وارد شوید.');
        } catch (\Throwable $e) {
            $this->flash($e->getMessage(), true);
        }
        $this->redirect('admin/database');
    }

    /** ساخت دیتابیس جدید */
    public function create(): void
    {
        $this->checkCsrf();
        try {
            $name = trim((string) ($_POST['name'] ?? ''));
            if (!str_ends_with(strtolower($name), '.sqlite')) { $name .= '.sqlite'; }
            $this->dbm->createDatabase($name);
            $this->flash('دیتابیس «' . $name . '» ساخته شد.');
        } catch (\Throwable $e) {
            $this->flash($e->getMessage(), true);
        }
        $this->redirect('admin/database');
    }

    /** حذف بکاپ */
    public function deleteBackup(): void
    {
        $this->checkCsrf();
        try {
            $this->dbm->deleteBackup((string) ($_POST['file'] ?? ''));
            $this->flash('بکاپ حذف شد.');
        } catch (\Throwable $e) {
            $this->flash($e->getMessage(), true);
        }
        $this->redirect('admin/database');
    }

    /** دانلود بکاپ */
    public function download(string $file): void
    {
        if (!$this->dbm->validName($file)) { $this->notFound('نام نامعتبر است.'); }
        $path = $this->dbm->backupPath($file);
        if (!is_file($path)) { $this->notFound('فایل پیدا نشد.'); }
        header('Content-Type: application/x-sqlite3');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    // ─── ابزارهای داخلی ───

    /** قالب‌بندی حجم — برای استفاده در ویو */
    public function fmtSize(int $bytes): string
    {
        return $this->dbm->formatSize($bytes);
    }

    /** خروج امن بعد از تغییر دیتابیس + هدایت به لاگین با پیام */
    private function logoutAndGoLogin(string $msg): void
    {
        unset($_SESSION['user_id']);
        session_regenerate_id(true);
        $_SESSION['login_notice'] = $msg;
        $this->redirect('user/login');
    }

    private function flash(string $msg, bool $isError = false): void
    {
        $_SESSION['db_flash'] = ['msg' => $msg, 'error' => $isError];
    }

    private function takeFlash(): ?array
    {
        $f = $_SESSION['db_flash'] ?? null;
        unset($_SESSION['db_flash']);
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

    private function render(string $title, string $view, array $data = []): void
    {
        $data['pageTitle'] = $title;
        $data['me'] = $this->me;
        $data['menu'] = $this->admin->menuItems('owner');
        $data['csrf'] = $this->csrfToken();
        $data['currentUrl'] = 'admin/database';
        $this->view('database', $view, $data);
    }
}
