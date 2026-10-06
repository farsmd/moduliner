<?php
/**
 * لی‌آوت پنل مدیریت
 * متغیرها: $pageTitle, $me, $menu, $content (از بافر)
 */
$initial = mb_substr($me['full_name'] !== '' ? $me['full_name'] : $me['username'], 0, 1);
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
<meta charset="utf-8">
<base href="<?= htmlspecialchars(BASE_URL . '/') ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= htmlspecialchars($pageTitle) ?> — مدیریت مودولاینر</title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Tahoma, sans-serif; background: #f0f2f5; color: #333; }
.admin { display: flex; min-height: 100vh; }
.sidebar { width: 240px; background: #1a1a2e; color: #eee; padding: 20px 0; position: fixed; top: 0; bottom: 0; right: 0; overflow-y: auto; }
.brand { padding: 0 20px 20px; border-bottom: 1px solid #2a2a4a; margin-bottom: 16px; }
.brand h1 { color: #e8b923; font-size: 18px; }
.brand small { color: #888; font-size: 12px; }
.nav a { display: flex; align-items: center; gap: 12px; padding: 12px 20px; color: #aaa; text-decoration: none; font-size: 14px; }
.nav a:hover { background: #16213e; color: #eee; }
.nav a.active { background: #16213e; color: #e8b923; border-right: 3px solid #e8b923; }
.nav svg { flex-shrink: 0; }
.side-user { padding: 16px 20px; border-top: 1px solid #2a2a4a; margin-top: 16px; font-size: 13px; color: #888; }
.main { flex: 1; margin-right: 240px; padding: 24px; max-width: 1100px; }
.topbar { display: none; }
.page-title { font-size: 22px; margin-bottom: 20px; color: #1a1a2e; }
.card { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,.06); margin-bottom: 16px; }
table { width: 100%; border-collapse: collapse; font-size: 14px; }
th, td { padding: 12px 8px; text-align: right; border-bottom: 1px solid #eee; }
th { color: #888; font-weight: normal; font-size: 13px; }
.badge { display: inline-block; font-size: 12px; padding: 3px 10px; border-radius: 20px; }
.badge-owner { background: #e8b923; color: #1a1a2e; }
.badge-admin { background: #e3f2fd; color: #1565c0; }
.badge-user { background: #f0f0f0; color: #666; }
.badge-on { background: #e8f5e9; color: #2e7d32; }
.badge-off { background: #ffebee; color: #c62828; }
.btn { display: inline-block; padding: 8px 18px; border-radius: 8px; border: none; font-family: inherit; font-size: 13px; cursor: pointer; text-decoration: none; }
.btn-gold { background: #e8b923; color: #1a1a2e; font-weight: bold; }
.btn-ghost { background: #f0f0f0; color: #333; }
.btn-danger { background: #ffebee; color: #c62828; }
.btn-ok { background: #e8f5e9; color: #2e7d32; }
form.inline { display: inline; }
.stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; }
.stat { background: #fff; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
.stat .num { font-size: 28px; font-weight: bold; color: #1a1a2e; }
.stat .lbl { font-size: 13px; color: #888; margin-top: 4px; }
input, select { padding: 10px 12px; border: 1px solid #ddd; border-radius: 8px; font-family: inherit; font-size: 14px; }
label { display: block; font-size: 13px; color: #666; margin-bottom: 6px; }
.field { margin-bottom: 16px; }
.error { background: #ffebee; color: #c62828; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 14px; }
.logout { color: #888; font-size: 13px; text-decoration: none; }
.avatar { width: 36px; height: 36px; border-radius: 50%; background: #e8b923; color: #1a1a2e; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; vertical-align: middle; margin-left: 8px; }
@media (max-width: 768px) {
  .sidebar { display: none; }
  .main { margin-right: 0; padding: 16px; padding-top: 72px; }
  .topbar { display: flex; position: fixed; top: 0; right: 0; left: 0; background: #1a1a2e; color: #eee; padding: 12px 16px; align-items: center; justify-content: space-between; z-index: 10; }
  .topbar .brandline { color: #e8b923; font-weight: bold; }
  .topbar nav { display: flex; gap: 4px; }
  .topbar nav a { color: #aaa; text-decoration: none; font-size: 13px; padding: 8px 10px; border-radius: 8px; }
  .topbar nav a.active { color: #e8b923; background: #16213e; }
  table { font-size: 13px; }
  th, td { padding: 8px 4px; }
}
</style>
</head>
<body>
<div class="topbar">
    <span class="brandline">مدیریت</span>
    <nav>
        <?php foreach ($menu as $item): ?>
            <a href="<?= htmlspecialchars($item['url']) ?>" class="<?= $currentUrl === trim($item['url'], '/') ? 'active' : '' ?>"><?= htmlspecialchars($item['title']) ?></a>
        <?php endforeach; ?>
    </nav>
</div>
<div class="admin">
    <aside class="sidebar">
        <div class="brand"><h1>مودولاینر</h1><small>پنل مدیریت</small></div>
        <nav class="nav">
            <?php foreach ($menu as $item): ?>
                <a href="<?= htmlspecialchars($item['url']) ?>" class="<?= $currentUrl === trim($item['url'], '/') ? 'active' : '' ?>">
                    <?= $item['icon'] ?><span><?= htmlspecialchars($item['title']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="side-user">
            <span class="avatar"><?= htmlspecialchars($initial) ?></span>
            <?= htmlspecialchars($me['full_name'] !== '' ? $me['full_name'] : $me['username']) ?>
            <div style="margin-top:8px;"><a class="logout" href="user/logout">خروج</a></div>
        </div>
    </aside>
    <main class="main">
        <h2 class="page-title"><?= htmlspecialchars($pageTitle) ?></h2>
        <?= $content ?>
    </main>
</div>
</body>
</html>
