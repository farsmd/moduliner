<?php
declare(strict_types=1);

namespace Modules\pages;

use Core\Controller as BaseController;
use Modules\admin\Models\AdminModel;
use Modules\user\Controller as UserController;
use Modules\user\Models\UserModel;
use Modules\pages\Models\PageModel;

/**
 * کنترلر ماژول صفحات — بخش عمومی سایت + مدیریت در پنل ادمین
 */
class Controller extends BaseController
{
    private PageModel $pages;

    public function __construct()
    {
        $this->pages = new PageModel();
    }

    // ─── بخش عمومی (بدون نیاز به لاگین) ───

    /** صفحه اصلی سایت */
    public function home(): void
    {
        $this->pages->ensureDefaultPage();
        $page = $this->pages->homepage();
        if ($page === null) {
            $this->notFound('صفحه‌ای منتشر نشده است.');
            return;
        }
        $this->siteView($page['title'], 'home', [
            'page' => $page,
            'pages' => $this->pages->published(),
        ]);
    }

    /** نمایش یک صفحه */
    public function show(string $slug): void
    {
        $page = $this->pages->findBySlug($slug);
        if ($page === null || $page['status'] !== 'published') {
            $this->notFound('صفحه پیدا نشد.');
            return;
        }
        $this->siteView($page['title'], 'show', [
            'page' => $page,
            'pages' => $this->pages->published(),
        ]);
    }

    // ─── مدیریت در پنل ادمین (admin و owner) ───

    public function list(): void
    {
        $me = $this->requireAdmin();
        $this->adminView('صفحات سایت', 'list', [
            'me' => $me,
            'items' => $this->pages->all(),
            'flash' => $this->takeFlash(),
        ]);
    }

    public function addForm(): void
    {
        $me = $this->requireAdmin();
        $this->adminView('صفحه جدید', 'form', ['me' => $me, 'item' => null]);
    }

    public function add(): void
    {
        $this->requireAdmin();
        $this->checkCsrf();
        $data = $this->readForm();
        if ($data === null) {
            $me = $this->requireAdmin();
            $this->adminView('صفحه جدید', 'form', [
                'me' => $me, 'item' => null, 'error' => 'عنوان و نامک (slug) معتبر الزامی‌اند.',
            ]);
            return;
        }
        if ($this->pages->slugExists($data['slug'])) {
            $me = $this->requireAdmin();
            $this->adminView('صفحه جدید', 'form', [
                'me' => $me, 'item' => null, 'error' => 'این نامک قبلاً استفاده شده است.',
            ]);
            return;
        }
        $this->pages->create($data);
        $this->flash('صفحه ساخته شد.');
        $this->redirect('admin/pages');
    }

    public function editForm(string $id): void
    {
        $me = $this->requireAdmin();
        $item = $this->pages->find((int) $id);
        if ($item === null) { $this->notFound('صفحه پیدا نشد.'); return; }
        $this->adminView('ویرایش صفحه', 'form', ['me' => $me, 'item' => $item]);
    }

    public function edit(string $id): void
    {
        $this->requireAdmin();
        $this->checkCsrf();
        $item = $this->pages->find((int) $id);
        if ($item === null) { $this->notFound('صفحه پیدا نشد.'); return; }
        $data = $this->readForm();
        if ($data === null || $this->pages->slugExists($data['slug'], (int) $id)) {
            $me = $this->requireAdmin();
            $this->adminView('ویرایش صفحه', 'form', [
                'me' => $me, 'item' => $item,
                'error' => $data === null ? 'عنوان و نامک (slug) معتبر الزامی‌اند.' : 'این نامک قبلاً استفاده شده است.',
            ]);
            return;
        }
        $this->pages->update((int) $id, $data);
        $this->flash('تغییرات ذخیره شد.');
        $this->redirect('admin/pages');
    }

    public function delete(string $id): void
    {
        $this->requireAdmin();
        $this->checkCsrf();
        $this->pages->delete((int) $id);
        $this->flash('صفحه حذف شد.');
        $this->redirect('admin/pages');
    }

    public function toggle(string $id): void
    {
        $this->requireAdmin();
        $this->checkCsrf();
        $this->pages->toggleStatus((int) $id);
        $this->redirect('admin/pages');
    }

    // ─── ابزارهای داخلی ───

    private function readForm(): ?array
    {
        $title = trim((string) ($_POST['title'] ?? ''));
        $slug = strtolower(trim((string) ($_POST['slug'] ?? '')));
        $content = (string) ($_POST['content'] ?? '');
        if ($title === '' || !preg_match('#^[a-z0-9-]+$#', $slug)) { return null; }
        $status = (string) ($_POST['status'] ?? 'draft') === 'published' ? 'published' : 'draft';
        return [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'status' => $status,
            'is_home' => !empty($_POST['is_home']),
        ];
    }

    /** گارد پنل: لاگین + نقش admin/owner — برمی‌گرداند کاربر جاری */
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
        $_SESSION['pages_flash'] = $msg;
    }

    private function takeFlash(): ?string
    {
        $f = $_SESSION['pages_flash'] ?? null;
        unset($_SESSION['pages_flash']);
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

    /** رندر ویوی عمومی سایت */
    private function siteView(string $title, string $view, array $data = []): void
    {
        $data['pageTitle'] = $title;
        $this->view('pages', $view, $data);
    }

    /** رندر ویو داخل لی‌آوت پنل ادمین */
    private function adminView(string $title, string $view, array $data = []): void
    {
        $me = $data['me'];
        $data['pageTitle'] = $title;
        $data['menu'] = (new AdminModel())->menuItems((string) $me['role']);
        $data['csrf'] = $this->csrfToken();
        $data['currentUrl'] = 'admin/pages';
        $this->view('pages', $view, $data);
    }
}
