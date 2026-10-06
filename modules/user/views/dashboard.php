<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>داشبورد — مودولاینر</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Tahoma, sans-serif; background: #f0f2f5; color: #333; }
header { background: #1a1a2e; color: #eee; padding: 16px 24px; display: flex; justify-content: space-between; align-items: center; }
header h1 { font-size: 18px; color: #e8b923; }
header a { color: #aaa; text-decoration: none; font-size: 14px; }
header a:hover { color: #fff; }
main { max-width: 800px; margin: 32px auto; padding: 0 16px; }
.card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,.08); margin-bottom: 16px; }
.badge { display: inline-block; background: #e8b923; color: #1a1a2e; font-size: 12px; font-weight: bold; padding: 4px 12px; border-radius: 20px; }
.meta { color: #888; font-size: 13px; margin-top: 8px; }
</style>
</head>
<body>
<header>
    <h1>مودولاینر</h1>
    <a href="user/logout">خروج</a>
</header>
<main>
    <div class="card">
        <h2>خوش آمدی، <?= htmlspecialchars($user['full_name'] ?: $user['username']) ?>!</h2>
        <p style="margin-top:8px;"><span class="badge"><?= htmlspecialchars($user['role']) ?></span></p>
        <p class="meta">ورود موفق — هسته و ماژول کاربر سالم کار می‌کنند.</p>
    </div>
    <div class="card">
        <h3>ماژول‌های بعدی</h3>
        <p class="meta">ساختار آماده است — ماژول جدید را در <code dir="ltr">modules/</code> بساز و فایل <code dir="ltr">routes.php</code> را اضافه کن.</p>
    </div>
</main>
</body>
</html>
