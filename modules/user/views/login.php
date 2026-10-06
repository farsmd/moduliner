<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>ورود — مودولاینر</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Tahoma, sans-serif; background: #1a1a2e; color: #eee; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
.card { background: #16213e; border-radius: 12px; padding: 32px; width: 100%; max-width: 360px; box-shadow: 0 8px 32px rgba(0,0,0,.4); }
h1 { font-size: 22px; margin-bottom: 6px; color: #e8b923; }
.sub { font-size: 13px; color: #888; margin-bottom: 24px; }
label { display: block; font-size: 13px; margin-bottom: 6px; color: #aaa; }
input { width: 100%; padding: 12px; margin-bottom: 16px; border: 1px solid #333; border-radius: 8px; background: #0f0f1a; color: #eee; font-size: 15px; font-family: inherit; }
input:focus { outline: none; border-color: #e8b923; }
button { width: 100%; padding: 12px; border: none; border-radius: 8px; background: #e8b923; color: #1a1a2e; font-size: 16px; font-weight: bold; cursor: pointer; font-family: inherit; }
button:hover { background: #f5c842; }
.error { background: #4a1515; color: #ff8a8a; padding: 10px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; }
.hint { margin-top: 16px; font-size: 12px; color: #666; text-align: center; }
</style>
</head>
<body>
<div class="card">
    <h1>مودولاینر</h1>
    <p class="sub">ورود به سیستم</p>
    <?php if (!empty($error)): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="post">
        <label>نام کاربری</label>
        <input type="text" name="username" required autocomplete="username" autofocus>
        <label>رمز عبور</label>
        <input type="password" name="password" required autocomplete="current-password">
        <button type="submit">ورود</button>
    </form>
    <p class="hint">کاربر پیش‌فرض: admin / admin123</p>
</div>
</body>
</html>
