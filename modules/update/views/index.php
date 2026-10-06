<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
<meta charset="utf-8">
<base href="<?= htmlspecialchars(BASE_URL . '/') ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>به‌روزرسانی — مودولاینر</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Tahoma, sans-serif; background: #f0f2f5; color: #333; }
header { background: #1a1a2e; color: #eee; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; }
header h1 { font-size: 18px; color: #e8b923; }
header a { color: #aaa; text-decoration: none; font-size: 14px; margin-right: 16px; }
main { max-width: 640px; margin: 32px auto; padding: 0 16px; }
.card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,.08); margin-bottom: 16px; }
.ver { font-size: 14px; color: #666; margin: 8px 0; }
.ver strong { color: #1a1a2e; font-size: 20px; }
.badge-new { display: inline-block; background: #4caf50; color: #fff; font-size: 12px; padding: 4px 12px; border-radius: 20px; margin-right: 8px; }
button { padding: 12px 32px; border: none; border-radius: 8px; background: #e8b923; color: #1a1a2e; font-size: 15px; font-weight: bold; cursor: pointer; font-family: inherit; }
button:hover { background: #f5c842; }
button:disabled { background: #ccc; cursor: not-allowed; }
.ok { background: #e8f5e9; color: #2e7d32; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
.err { background: #ffebee; color: #c62828; padding: 12px; border-radius: 8px; margin-bottom: 16px; }
.notes { background: #f9f9f9; border-radius: 8px; padding: 16px; margin-top: 16px; font-size: 13px; white-space: pre-wrap; }
.muted { color: #888; font-size: 13px; }
.protected { margin-top: 16px; font-size: 12px; color: #888; }
</style>
</head>
<body>
<header>
    <h1>مودولاینر — به‌روزرسانی</h1>
    <div><a href="user/dashboard">داشبورد</a><a href="user/logout">خروج</a></div>
</header>
<main>
    <?php if ($result !== null): ?>
        <div class="<?= $result['ok'] ? 'ok' : 'err' ?>"><?= htmlspecialchars($result['message']) ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="ver">نسخه فعلی: <strong dir="ltr"><?= htmlspecialchars($current) ?></strong></div>
        <?php if ($latest === null): ?>
            <p class="muted">ارتباط با گیت‌هاب برقرار نشد. اتصال اینترنت را بررسی کنید.</p>
        <?php elseif ($hasUpdate): ?>
            <div class="ver">نسخه جدید: <strong dir="ltr"><?= htmlspecialchars($latest['version']) ?></strong> <span class="badge-new">جدید</span></div>
            <?php if (!empty($latest['notes'])): ?>
                <div class="notes"><?= htmlspecialchars($latest['notes']) ?></div>
            <?php endif; ?>
            <form method="post" style="margin-top:16px;" onsubmit="return confirm('آپدیت انجام شود؟ قبل از آپدیت بکاپ خودکار گرفته می‌شود.');">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                <button type="submit" name="do_update" value="1">نصب آپدیت</button>
            </form>
            <p class="protected">محافظت‌شده: پوشه <code dir="ltr">database/</code> و فایل <code dir="ltr">config.php</code> هرگز بازنویسی نمی‌شوند.</p>
        <?php else: ?>
            <p class="muted">سیستم به‌روز است. ✓</p>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
