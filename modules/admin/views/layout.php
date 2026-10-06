<?php
/**
 * لی‌آوت پنل مدیریت
 * متغیرها: $pageTitle, $me, $menu, $content (از بافر)
 */
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
.main { padding: 24px 16px; padding-top: 84px; max-width: 1100px; margin: 0 auto; min-height: 100vh; }
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
@media (max-width: 768px) {
  .main { padding: 16px; padding-top: 80px; }
  table { font-size: 13px; }
  th, td { padding: 8px 4px; }
}
</style>
</head>
<body>
<?php require MODULES_PATH . '/menu/views/templates/hamburger-right.php'; ?>
<main class="main">
    <h2 class="page-title"><?= htmlspecialchars($pageTitle) ?></h2>
    <?= $content ?>
</main>
</body>
</html>
