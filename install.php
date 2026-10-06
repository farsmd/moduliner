<?php
/**
 * Moduliner — نصاب خودکار
 *
 * این فایل فقط وقتی اجرا می‌شود که سیستم نصب نشده باشد.
 * بعد از نصب موفق، قفل می‌شود (فایل database/installed.lock).
 */

declare(strict_types=1);

define('BASE_PATH', __DIR__);
define('DB_PATH', BASE_PATH . '/database/app.sqlite');
define('LOCK_FILE', BASE_PATH . '/database/installed.lock');

// اگر قبلاً نصب شده → هدایت به صفحه اصلی
if (is_file(LOCK_FILE)) {
    header('Location: index.php');
    exit;
}

$errors = [];
$checks = [];

// بررسی ۱: نسخه PHP
$phpOk = version_compare(PHP_VERSION, '8.0.0', '>=');
$checks[] = ['PHP 8.0+', $phpOk, PHP_VERSION];
if (!$phpOk) { $errors[] = 'نسخه PHP باید ۸٫۰ یا بالاتر باشد.'; }

// بررسی ۲: افزونه SQLite
$sqliteOk = extension_loaded('pdo_sqlite');
$checks[] = ['PDO SQLite', $sqliteOk, $sqliteOk ? 'فعال' : 'غیرفعال'];
if (!$sqliteOk) { $errors[] = 'افزونه pdo_sqlite فعال نیست.'; }

// بررسی ۳: قابل‌نوشتن بودن پوشه database
$dbDir = BASE_PATH . '/database';
if (!is_dir($dbDir)) { @mkdir($dbDir, 0755, true); }
$writableOk = is_dir($dbDir) && is_writable($dbDir);
$checks[] = ['پوشه database قابل‌نوشتن', $writableOk, $writableOk ? 'بله' : 'خیر'];
if (!$writableOk) { $errors[] = 'پوشه database قابل‌نوشتن نیست.'; }

// بررسی ۴: OpenSSL (برای VAPID و هش امن — اختیاری ولی پیشنهادی)
$sslOk = extension_loaded('openssl');
$checks[] = ['OpenSSL (پیشنهادی)', $sslOk, $sslOk ? 'فعال' : 'غیرفعال'];

$installError = null;
$success = false;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && empty($errors)) {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $password2 = (string) ($_POST['password2'] ?? '');
    $fullName = trim((string) ($_POST['full_name'] ?? ''));

    if (strlen($username) < 3) {
        $installError = 'نام کاربری باید حداقل ۳ کاراکتر باشد.';
    } elseif (strlen($password) < 6) {
        $installError = 'رمز عبور باید حداقل ۶ کاراکتر باشد.';
    } elseif ($password !== $password2) {
        $installError = 'تکرار رمز عبور مطابقت ندارد.';
    } else {
        try {
            $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
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
            // اگر کاربری وجود ندارد، مدیر اول را بساز
            $count = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
            if ($count === 0) {
                $st = $pdo->prepare('INSERT INTO users (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)');
                $st->execute([$username, password_hash($password, PASSWORD_DEFAULT), $fullName, 'owner']);
            }
            // قفل نصب
            file_put_contents(LOCK_FILE, date('Y-m-d H:i:s'));
            $success = true;
        } catch (Throwable $e) {
            $installError = 'خطا در نصب: ' . $e->getMessage();
        }
    }
}
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
h1 { color: #e8b923; font-size: 24px; margin-bottom: 6px; }
.sub { color: #888; font-size: 13px; margin-bottom: 24px; }
.card { background: #16213e; border-radius: 12px; padding: 24px; margin-bottom: 16px; }
.card h2 { font-size: 16px; margin-bottom: 16px; }
.check { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #222; font-size: 14px; }
.check:last-child { border: none; }
.ok { color: #4caf50; } .fail { color: #f44336; } .warn { color: #ff9800; }
label { display: block; font-size: 13px; margin-bottom: 6px; color: #aaa; }
input { width: 100%; padding: 12px; margin-bottom: 16px; border: 1px solid #333; border-radius: 8px; background: #0f0f1a; color: #eee; font-size: 15px; font-family: inherit; }
button { width: 100%; padding: 14px; border: none; border-radius: 8px; background: #e8b923; color: #1a1a2e; font-size: 16px; font-weight: bold; cursor: pointer; font-family: inherit; }
button:hover { background: #f5c842; }
button:disabled { background: #555; color: #999; cursor: not-allowed; }
.error { background: #4a1515; color: #ff8a8a; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
.success { background: #1b3a1b; color: #8fdb8f; padding: 16px; border-radius: 8px; text-align: center; }
.success a { color: #e8b923; }
</style>
</head>
<body>
<div class="wrap">
    <h1>مودولاینر</h1>
    <p class="sub">نصاب خودکار — نسخه <?= '0.0.2' ?></p>

    <?php if ($success): ?>
        <div class="card"><div class="success">
            <h2>نصب با موفقیت انجام شد!</h2>
            <p style="margin-top:8px;"><a href="index.php">ورود به سیستم</a></p>
        </div></div>
    <?php else: ?>
        <div class="card">
            <h2>بررسی پیش‌نیازها</h2>
            <?php foreach ($checks as [$label, $ok, $detail]): ?>
                <div class="check">
                    <span><?= htmlspecialchars($label) ?></span>
                    <span class="<?= $ok ? 'ok' : 'fail' ?>"><?= htmlspecialchars($detail) ?> <?= $ok ? '✓' : '✗' ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="card"><div class="error">
                <?php foreach ($errors as $e): ?><p><?= htmlspecialchars($e) ?></p><?php endforeach; ?>
                <p style="margin-top:8px;">لطفاً مشکلات را رفع کنید و صفحه را رفرش کنید.</p>
            </div></div>
        <?php else: ?>
            <div class="card">
                <h2>ساخت حساب مدیر</h2>
                <?php if ($installError): ?><div class="error"><?= htmlspecialchars($installError) ?></div><?php endif; ?>
                <form method="post">
                    <label>نام و نام خانوادگی</label>
                    <input type="text" name="full_name" autocomplete="name">
                    <label>نام کاربری</label>
                    <input type="text" name="username" required autocomplete="username">
                    <label>رمز عبور (حداقل ۶ کاراکتر)</label>
                    <input type="password" name="password" required autocomplete="new-password">
                    <label>تکرار رمز عبور</label>
                    <input type="password" name="password2" required autocomplete="new-password">
                    <button type="submit">نصب و ساخت حساب</button>
                </form>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
