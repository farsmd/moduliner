<?php
/**
 * Moduliner — نصب‌کننده تک‌فایل
 *
 * نحوه استفاده:
 * ۱. فقط همین یک فایل را در هاست خالی آپلود کنید
 * ۲. در مرورگر باز کنید: https://yoursite.com/moduliner-installer.php
 * ۳. مراحل را دنبال کنید — خودش آخرین نسخه را از گیت‌هاب می‌گیرد و نصب می‌کند
 * ۴. بعد از نصب، این فایل را پاک کنید
 *
 * نسخه: ۰٫۰٫۳
 */

declare(strict_types=1);

define('GITHUB_REPO', 'farsmd/moduliner');
define('INSTALLER_VERSION', '0.0.4');

session_start();

// گام‌ها: check → download → setup → done
$step = $_GET['step'] ?? 'check';
$errors = [];

// ─── گام ۱: بررسی پیش‌نیازها ───
function checkRequirements(): array
{
    $checks = [];
    $ok = true;

    $phpOk = version_compare(PHP_VERSION, '8.0.0', '>=');
    $checks[] = ['نسخه PHP (۸٫۰+)', $phpOk, PHP_VERSION];
    $ok = $ok && $phpOk;

    $sqliteOk = extension_loaded('pdo_sqlite');
    $checks[] = ['PDO SQLite', $sqliteOk, $sqliteOk ? 'فعال' : 'غیرفعال'];
    $ok = $ok && $sqliteOk;

    $zipOk = class_exists('ZipArchive');
    $checks[] = ['ZipArchive (استخراج)', $zipOk, $zipOk ? 'فعال' : 'غیرفعال'];
    $ok = $ok && $zipOk;

    $writableOk = is_writable(__DIR__);
    $checks[] = ['پوشه قابل‌نوشتن', $writableOk, $writableOk ? 'بله' : 'خیر'];
    $ok = $ok && $writableOk;

    $urlOk = ini_get('allow_url_fopen') == '1';
    $checks[] = ['دانلود از اینترنت', $urlOk, $urlOk ? 'فعال' : 'غیرفعال'];
    $ok = $ok && $urlOk;

    return [$checks, $ok];
}

// ─── گام ۲: دانلود آخرین ریلیز ───
function downloadLatest(): array
{
    $ctx = stream_context_create([
        'http' => ['timeout' => 30, 'header' => "User-Agent: Moduliner-Installer\r\n"],
    ]);

    // آخرین ریلیز
    $json = @file_get_contents('https://api.github.com/repos/' . GITHUB_REPO . '/releases/latest', false, $ctx);
    if ($json === false) {
        return ['ok' => false, 'message' => 'ارتباط با گیت‌هاب برقرار نشد.'];
    }
    $rel = json_decode($json, true);
    if (empty($rel['zipball_url'])) {
        return ['ok' => false, 'message' => 'ریلیزی پیدا نشد.'];
    }

    $version = ltrim((string) ($rel['tag_name'] ?? ''), 'v');
    $zipUrl = (string) $rel['zipball_url'];

    // دانلود ZIP
    $zipData = @file_get_contents($zipUrl, false, stream_context_create([
        'http' => ['timeout' => 120, 'header' => "User-Agent: Moduliner-Installer\r\n"],
    ]));
    if ($zipData === false) {
        return ['ok' => false, 'message' => 'دانلود فایل نصب ناموفق بود.'];
    }

    $tmpZip = sys_get_temp_dir() . '/moduliner_' . time() . '.zip';
    file_put_contents($tmpZip, $zipData);

    // استخراج
    $zip = new ZipArchive();
    if ($zip->open($tmpZip) !== true) {
        @unlink($tmpZip);
        return ['ok' => false, 'message' => 'فایل دانلودشده خراب است.'];
    }

    $tmpDir = sys_get_temp_dir() . '/moduliner_' . time();
    mkdir($tmpDir, 0755, true);
    $zip->extractTo($tmpDir);
    $zip->close();
    @unlink($tmpZip);

    // پوشه اصلی داخل ZIP
    $srcDir = null;
    foreach (scandir($tmpDir) as $e) {
        if ($e !== '.' && $e !== '..' && is_dir("$tmpDir/$e")) { $srcDir = "$tmpDir/$e"; break; }
    }
    if ($srcDir === null) {
        return ['ok' => false, 'message' => 'ساختار فایل نصب نامعتبر است.'];
    }

    // کپی به روت (به‌جز خود نصب‌کننده)
    $copied = 0;
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($srcDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($iterator as $item) {
        $relPath = substr($item->getPathname(), strlen($srcDir) + 1);
        // خود نصب‌کننده را بازنویسی نکن
        if ($relPath === 'moduliner-installer.php') { continue; }
        // database/ را بازنویسی نکن اگر وجود دارد
        if (str_starts_with($relPath, 'database/') && is_file(__DIR__ . '/' . $relPath)) { continue; }

        $dest = __DIR__ . '/' . $relPath;
        if ($item->isDir()) {
            if (!is_dir($dest)) { mkdir($dest, 0755, true); }
        } else {
            $destDir = dirname($dest);
            if (!is_dir($destDir)) { mkdir($destDir, 0755, true); }
            if (@copy($item->getPathname(), $dest)) { $copied++; }
        }
    }

    // پاک‌سازی موقت
    $it = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($tmpDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($it as $item) {
        $item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
    }
    @rmdir($tmpDir);

    $_SESSION['installed_version'] = $version;
    return ['ok' => true, 'message' => "نسخه {$version} دانلود و استخراج شد ({$copied} فایل)."];
}

// ─── گام ۳: ساخت حساب مدیر ───
function setupAdmin(string $username, string $password, string $fullName): array
{
    if (strlen($username) < 3) { return ['ok' => false, 'message' => 'نام کاربری حداقل ۳ کاراکتر.']; }
    if (strlen($password) < 6) { return ['ok' => false, 'message' => 'رمز عبور حداقل ۶ کاراکتر.']; }

    try {
        $dbPath = __DIR__ . '/database/app.sqlite';
        $dbDir = dirname($dbPath);
        if (!is_dir($dbDir)) { mkdir($dbDir, 0755, true); }

        $pdo = new PDO('sqlite:' . $dbPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                password_hash TEXT NOT NULL,
                full_name TEXT DEFAULT '',
                role TEXT NOT NULL DEFAULT 'user',
                is_active INTEGER NOT NULL DEFAULT 1,
                created_at TEXT NOT NULL DEFAULT (datetime('now'))
            )
        ");
        $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
        if ($count === 0) {
            $st = $pdo->prepare('INSERT INTO users (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)');
            $st->execute([$username, password_hash($password, PASSWORD_DEFAULT), $fullName, 'owner']);
        }
        file_put_contents($dbDir . '/installed.lock', date('Y-m-d H:i:s'));
        return ['ok' => true, 'message' => 'حساب مدیر ساخته شد.'];
    } catch (Throwable $e) {
        return ['ok' => false, 'message' => 'خطا: ' . $e->getMessage()];
    }
}

// ─── پردازش ───
$result = null;
if ($step === 'download' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $result = downloadLatest();
    if ($result['ok']) { $step = 'setup'; }
} elseif ($step === 'setup' && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $pw = (string) ($_POST['password'] ?? '');
    $pw2 = (string) ($_POST['password2'] ?? '');
    if ($pw !== $pw2) {
        $result = ['ok' => false, 'message' => 'تکرار رمز عبور مطابقت ندارد.'];
    } else {
        $result = setupAdmin(
            trim((string) ($_POST['username'] ?? '')),
            $pw,
            trim((string) ($_POST['full_name'] ?? ''))
        );
        if ($result['ok']) { $step = 'done'; }
    }
}

[$checks, $allOk] = checkRequirements();
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>نصب مودولاینر</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Tahoma, sans-serif; background: #1a1a2e; color: #eee; min-height: 100vh; padding: 32px 16px; }
.wrap { max-width: 520px; margin: 0 auto; }
h1 { color: #e8b923; font-size: 24px; }
.sub { color: #888; font-size: 13px; margin: 6px 0 24px; }
.card { background: #16213e; border-radius: 12px; padding: 24px; margin-bottom: 16px; }
.card h2 { font-size: 16px; margin-bottom: 16px; }
.row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #222; font-size: 14px; }
.row:last-child { border: none; }
.ok { color: #4caf50; } .fail { color: #f44336; }
label { display: block; font-size: 13px; margin-bottom: 6px; color: #aaa; }
input { width: 100%; padding: 12px; margin-bottom: 16px; border: 1px solid #333; border-radius: 8px; background: #0f0f1a; color: #eee; font-size: 15px; font-family: inherit; }
button { width: 100%; padding: 14px; border: none; border-radius: 8px; background: #e8b923; color: #1a1a2e; font-size: 16px; font-weight: bold; cursor: pointer; font-family: inherit; }
button:hover { background: #f5c842; }
button:disabled { background: #555; color: #999; cursor: not-allowed; }
.msg-err { background: #4a1515; color: #ff8a8a; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
.msg-ok { background: #1b3a1b; color: #8fdb8f; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
.steps { display: flex; gap: 8px; margin-bottom: 24px; }
.step { flex: 1; text-align: center; font-size: 12px; color: #666; padding: 8px; background: #16213e; border-radius: 8px; }
.step.active { color: #e8b923; border: 1px solid #e8b923; }
.step.done { color: #4caf50; }
a { color: #e8b923; }
code { background: #0f0f1a; padding: 2px 8px; border-radius: 4px; font-size: 13px; }
</style>
</head>
<body>
<div class="wrap">
    <h1>مودولاینر</h1>
    <p class="sub">نصب‌کننده تک‌فایل — نسخه <?= INSTALLER_VERSION ?></p>

    <div class="steps">
        <div class="step <?= $step === 'check' ? 'active' : 'done' ?>">۱. بررسی</div>
        <div class="step <?= $step === 'download' ? 'active' : ($step === 'setup' || $step === 'done' ? 'done' : '') ?>">۲. دانلود</div>
        <div class="step <?= $step === 'setup' ? 'active' : ($step === 'done' ? 'done' : '') ?>">۳. حساب مدیر</div>
        <div class="step <?= $step === 'done' ? 'active' : '' ?>">۴. پایان</div>
    </div>

    <?php if ($step === 'check'): ?>
        <div class="card">
            <h2>بررسی پیش‌نیازها</h2>
            <?php foreach ($checks as [$label, $ok, $detail]): ?>
                <div class="row"><span><?= htmlspecialchars($label) ?></span><span class="<?= $ok ? 'ok' : 'fail' ?>"><?= htmlspecialchars($detail) ?> <?= $ok ? '✓' : '✗' ?></span></div>
            <?php endforeach; ?>
        </div>
        <?php if ($allOk): ?>
            <form method="post" action="?step=download"><button type="submit">دانلود و نصب آخرین نسخه از گیت‌هاب</button></form>
        <?php else: ?>
            <div class="card"><p style="color:#ff8a8a;">لطفاً موارد قرمز را رفع کنید و صفحه را رفرش کنید.</p></div>
        <?php endif; ?>

    <?php elseif ($step === 'download'): ?>
        <?php if ($result && !$result['ok']): ?><div class="msg-err"><?= htmlspecialchars($result['message']) ?></div><?php endif; ?>
        <div class="card">
            <h2>دانلود از گیت‌هاب</h2>
            <p style="font-size:14px;color:#aaa;margin-bottom:16px;">آخرین نسخه از مخزن <code dir="ltr"><?= GITHUB_REPO ?></code> دانلود و استخراج می‌شود.</p>
            <form method="post"><button type="submit">شروع دانلود</button></form>
        </div>

    <?php elseif ($step === 'setup'): ?>
        <?php if ($result && $result['ok']): ?><div class="msg-ok"><?= htmlspecialchars($result['message']) ?></div><?php endif; ?>
        <?php if ($result && !$result['ok']): ?><div class="msg-err"><?= htmlspecialchars($result['message']) ?></div><?php endif; ?>
        <div class="card">
            <h2>ساخت حساب مدیر</h2>
            <form method="post">
                <label>نام و نام خانوادگی</label>
                <input type="text" name="full_name" autocomplete="name">
                <label>نام کاربری</label>
                <input type="text" name="username" required autocomplete="username">
                <label>رمز عبور (حداقل ۶ کاراکتر)</label>
                <input type="password" name="password" required autocomplete="new-password">
                <label>تکرار رمز عبور</label>
                <input type="password" name="password2" required autocomplete="new-password">
                <button type="submit">نصب نهایی</button>
            </form>
        </div>

    <?php elseif ($step === 'done'):
        // حذف خودکار نصب‌کننده بعد از نصب موفق
        $selfDeleted = @unlink(__FILE__);
    ?>
        <div class="card">
            <div class="msg-ok" style="text-align:center;">
                <h2>نصب کامل شد!</h2>
                <p style="margin-top:8px;">نسخه <?= htmlspecialchars($_SESSION['installed_version'] ?? '') ?> با موفقیت نصب شد.</p>
                <?php if ($selfDeleted): ?>
                    <p style="margin-top:8px;">فایل نصب‌کننده به‌صورت خودکار حذف شد.</p>
                <?php else: ?>
                    <p style="margin-top:8px;color:#ff8a8a;">فایل <code dir="ltr">moduliner-installer.php</code> را دستی از هاست پاک کنید.</p>
                <?php endif; ?>
            </div>
            <p style="text-align:center;margin-top:16px;"><a href="index.php" style="font-size:16px;">ورود به سیستم</a></p>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
