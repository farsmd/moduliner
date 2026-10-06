<?php
/**
 * لی‌آوت عمومی سایت
 * متغیرها: $pageTitle, $content, $pages (صفحات منتشرشده برای ناوبری)
 */
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<base href="<?= htmlspecialchars(BASE_URL . '/') ?>">
<title><?= htmlspecialchars($pageTitle) ?></title>
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: Tahoma, sans-serif; background: #fafafa; color: #333; line-height: 2; }
header { background: #1a1a2e; color: #eee; }
.navbar { max-width: 960px; margin: 0 auto; padding: 16px 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; }
.brand { color: #e8b923; font-weight: bold; font-size: 18px; text-decoration: none; }
nav.links { display: flex; gap: 4px; flex-wrap: wrap; }
nav.links a { color: #aaa; text-decoration: none; font-size: 14px; padding: 8px 14px; border-radius: 8px; }
nav.links a:hover { color: #fff; background: #16213e; }
main { max-width: 960px; margin: 0 auto; padding: 48px 20px; min-height: 60vh; }
article { background: #fff; border-radius: 16px; padding: 40px; box-shadow: 0 2px 12px rgba(0,0,0,.05); }
article h1 { font-size: 28px; color: #1a1a2e; margin-bottom: 16px; line-height: 1.6; }
article .body { font-size: 16px; color: #444; }
footer { text-align: center; padding: 32px 16px; color: #999; font-size: 13px; }
footer a { color: #888; }
@media (max-width: 640px) {
  article { padding: 24px 20px; }
  article h1 { font-size: 22px; }
  main { padding: 24px 12px; }
}
</style>
</head>
<body>
<header>
    <div class="navbar">
        <a class="brand" href="">خانه</a>
        <nav class="links">
            <?php foreach ($pages as $p): ?>
                <?php if (!empty($p['is_home'])) continue; ?>
                <a href="page/<?= htmlspecialchars($p['slug']) ?>"><?= htmlspecialchars($p['title']) ?></a>
            <?php endforeach; ?>
        </nav>
    </div>
</header>
<main>
    <?= $content ?>
</main>
<footer>ساخته شده با مودولاینر</footer>
</body>
</html>
