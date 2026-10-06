<?php
declare(strict_types=1);

namespace Modules\user;

use Core\Controller as BaseController;
use Modules\user\Models\UserModel;

/**
 * کنترلر ماژول کاربر — لاگین، خروج، داشبورد
 */
class Controller extends BaseController
{
    private UserModel $users;

    public function __construct()
    {
        $this->users = new UserModel();
        $this->users->ensureOwnerExists();
    }

    /** صفحه اصلی — هدایت به لاگین یا داشبورد */
    public function home(): void
    {
        if ($this->currentUser() !== null) {
            $this->redirect('user/dashboard');
        } else {
            $this->redirect('user/login');
        }
    }

    public function login(): void
    {
        if ($this->currentUser() !== null) {
            $this->redirect('user/dashboard');
        }
        $error = null;
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            $username = trim((string) ($_POST['username'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');
            $user = $this->users->findByUsername($username);
            if ($user !== null && $this->users->verifyPassword($user, $password)) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['id'];
                $this->redirect('user/dashboard');
            }
            $error = 'نام کاربری یا رمز عبور اشتباه است.';
        }
        $this->view('user', 'login', ['error' => $error]);
    }

    public function logout(): void
    {
        unset($_SESSION['user_id']);
        session_regenerate_id(true);
        $this->redirect('user/login');
    }

    public function dashboard(): void
    {
        $user = $this->requireLogin();
        $this->view('user', 'dashboard', ['user' => $user]);
    }

    /** کاربر جاری یا null */
    private function currentUser(): ?array
    {
        $id = $_SESSION['user_id'] ?? null;
        if ($id === null) { return null; }
        return $this->users->findById((int) $id);
    }

    /** اگر لاگین نباشد → هدایت به لاگین */
    private function requireLogin(): array
    {
        $user = $this->currentUser();
        if ($user === null) {
            $this->redirect('user/login');
        }
        return $user;
    }

    /** بررسی نقش — برای استفاده ماژول‌های دیگر */
    public static function hasRole(string ...$roles): bool
    {
        $id = $_SESSION['user_id'] ?? null;
        if ($id === null) { return false; }
        $user = (new UserModel())->findById((int) $id);
        if ($user === null) { return false; }
        // owner به همه‌چیز دسترسی دارد
        if ($user['role'] === 'owner') { return true; }
        return in_array($user['role'], $roles, true);
    }
}
