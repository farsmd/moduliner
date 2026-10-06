<?php
declare(strict_types=1);

namespace Modules\update;

use Core\Controller as BaseController;
use Modules\update\Models\UpdateModel;
use Modules\user\Controller as UserController;

/**
 * کنترلر ماژول آپدیت — فقط برای owner
 */
class Controller extends BaseController
{
    private UpdateModel $updater;

    public function __construct()
    {
        $this->updater = new UpdateModel();
    }

    public function index(): void
    {
        if (!UserController::hasRole('owner')) {
            $this->notFound('دسترسی غیرمجاز.');
        }

        $current = $this->updater->currentVersion();
        $latest = $this->updater->latestRelease();
        $hasUpdate = $latest !== null && version_compare($latest['version'], $current, '>');

        $result = null;
        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['do_update'])) {
            // محافظت CSRF ساده
            if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))) {
                $result = ['ok' => false, 'message' => 'توکن امنیتی نامعتبر است.'];
            } elseif ($latest !== null && !empty($latest['url'])) {
                $result = $this->updater->applyUpdate($latest['url']);
                // رفرش اطلاعات بعد از آپدیت
                $latest = $this->updater->latestRelease();
                $hasUpdate = $latest !== null && version_compare($latest['version'], $current, '>');
            }
        }

        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }

        $this->view('update', 'index', [
            'current' => $current,
            'latest' => $latest,
            'hasUpdate' => $hasUpdate,
            'result' => $result,
            'csrf' => $_SESSION['csrf'],
        ]);
    }
}
