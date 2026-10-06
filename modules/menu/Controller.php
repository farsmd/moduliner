<?php
declare(strict_types=1);

namespace Modules\menu;

use Core\Controller as BaseController;
use Modules\admin\Models\AdminModel;
use Modules\user\Controller as UserController;
use Modules\user\Models\UserModel;
use Modules\menu\Models\MenuModel;

/**
 * کنترلر مدیریت منو — نقش admin و owner
 * از لی‌آوت پنل ادمین استفاده می‌کند
 */
class Controller extends BaseController
{
    private MenuModel $menus;
    private AdminModel $admin;
    private UserModel $users;
    private array $me;

    public function __construct()
    {
        if (!UserController::hasRole('admin')) {
            http_response_code(403);
            echo 'دسترسی غیرمجاز.';
            exit;
        }
        $this->menus = new MenuModel();
        $this->admin = new AdminModel();
        $this->users = new UserModel();
        $id = (int) ($_SESSION['user_id'] ?? 0);
        $this->me = $this->users->findById($id) ?? ['username' => '?', 'full_name' => '', 'role' => 'admin'];
    }

    /** لیست آیتم‌ها */
    public function list(): void
    {
        $this->render('مدیریت منو', 'list', [
            'items' => $this->menus->all(),
            'icons' => $this->menus->icons(),
            'flash' => $this->takeFlash(),
        ]);
    }

    /** فرم افزودن */
    public function addForm(): void
    {
        $this->render('آیتم جدید', 'form', [
            'item' => null,
            'icons' => $this->menus->icons(),
            'roles' => $this->allRoles(),
        ]);
    }

    /** ذخیره آیتم جدید */
    public function add(): void
    {
        $this->checkCsrf();
        $data = $this->readForm();
        if ($data === null) {
            $this->render('آیتم جدید', 'form', [
                'item' => null, 'icons' => $this->menus->icons(),
                'roles' => $this->allRoles(), 'error' => 'عنوان و آدرس الزامی‌اند.',
            ]);
            return;
        }
        $this->menus->create($data);
        $this->flash('آیتم منو اضافه شد.');
        $this->redirect('admin/menus');
    }

    /** فرم ویرایش */
    public function editForm(string $id): void
    {
        $item = $this->menus->find((int) $id);
        if ($item === null) { $this->notFound('آیتم پیدا نشد.'); }
        $this->render('ویرایش آیتم منو', 'form', [
            'item' => $item,
            'icons' => $this->menus->icons(),
            'roles' => $this->allRoles(),
            'iconKey' => $this->menus->iconKey((string) $item['icon']),
        ]);
    }

    /** ذخیره ویرایش */
    public function edit(string $id): void
    {
        $this->checkCsrf();
        $item = $this->menus->find((int) $id);
        if ($item === null) { $this->notFound('آیتم پیدا نشد.'); }
        $data = $this->readForm();
        if ($data === null) {
            $this->render('ویرایش آیتم منو', 'form', [
                'item' => $item, 'icons' => $this->menus->icons(),
                'roles' => $this->allRoles(),
                'iconKey' => $this->menus->iconKey((string) $item['icon']),
                'error' => 'عنوان و آدرس الزامی‌اند.',
            ]);
            return;
        }
        // اگر آیکون سفارشی ماژول بود و کاربر آیکون جدید انتخاب نکرد، همان بماند
        if ($data['icon'] === '__keep__') {
            $data['icon'] = (string) $item['icon'];
        }
        $this->menus->update((int) $id, $data);
        $this->flash('تغییرات ذخیره شد.');
        $this->redirect('admin/menus');
    }

    /** حذف آیتم */
    public function delete(string $id): void
    {
        $this->checkCsrf();
        $this->menus->delete((int) $id);
        $this->flash('آیتم حذف شد.');
        $this->redirect('admin/menus');
    }

    /** فعال / غیرفعال */
    public function toggle(string $id): void
    {
        $this->checkCsrf();
        $this->menus->toggle((int) $id);
        $this->redirect('admin/menus');
    }

    /** جابه‌جایی بالا/پایین */
    public function move(string $id): void
    {
        $this->checkCsrf();
        $dir = (string) ($_POST['dir'] ?? 'up') === 'down' ? 'down' : 'up';
        $this->menus->move((int) $id, $dir);
        $this->redirect('admin/menus');
    }

    /** همگام‌سازی از menu.php ماژول‌ها */
    public function sync(): void
    {
        $this->checkCsrf();
        $added = $this->menus->syncFromModules();
        $this->flash($added > 0 ? "{$added} آیتم جدید از ماژول‌ها اضافه شد." : 'آیتم جدیدی پیدا نشد.');
        $this->redirect('admin/menus');
    }

    // ─── ابزارهای داخلی ───

    private function allRoles(): array
    {
        return ['owner', 'admin', 'user'];
    }

    /** خواندن و اعتبارسنجی فرم */
    private function readForm(): ?array
    {
        $title = trim((string) ($_POST['title'] ?? ''));
        $url = trim((string) ($_POST['url'] ?? ''), '/');
        if ($title === '' || $url === '') { return null; }
        // آدرس: فقط حروف، عدد، خط تیره، زیرخط و اسلش
        if (!preg_match('#^[a-zA-Z0-9_/-]+$#', $url)) { return null; }
        $icon = (string) ($_POST['icon'] ?? 'grid');
        $icons = $this->menus->icons();
        if ($icon !== '__keep__' && !isset($icons[$icon])) { $icon = 'grid'; }
        $roles = array_intersect((array) ($_POST['roles'] ?? []), $this->allRoles());
        if (empty($roles)) { $roles = ['owner', 'admin']; }
        return [
            'title' => $title,
            'url' => $url,
            'icon' => $icon,
            'roles' => array_values($roles),
            'is_active' => !empty($_POST['is_active']),
        ];
    }

    private function flash(string $msg): void
    {
        $_SESSION['menu_flash'] = $msg;
    }

    private function takeFlash(): ?string
    {
        $f = $_SESSION['menu_flash'] ?? null;
        unset($_SESSION['menu_flash']);
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
        $data['menu'] = $this->admin->menuItems((string) $this->me['role']);
        $data['csrf'] = $this->csrfToken();
        $data['currentUrl'] = 'admin/menus';
        $this->view('menu', $view, $data);
    }

    /** قالب‌بندی برای ویو */
    public function iconSvg(string $value): string
    {
        return $this->menus->resolveIcon($value);
    }
}
