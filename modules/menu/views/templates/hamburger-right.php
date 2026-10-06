<?php
/**
 * قالب نمایش منو: همبرگری سمت راست (کشویی)
 *
 * کامپوننت مستقل و قابل‌استفاده مجدد — هرجا آیتم‌های منو لازم باشد
 * کافی است قبل از include این متغیرها ست شده باشند:
 *   $menu       آرایه آیتم‌ها (title, url, icon)
 *   $currentUrl آدرس فعلی بدون اسلش اول (برای هایلایت آیتم فعال)
 *   $me         اطلاعات کاربر جاری (username, full_name)
 *   $brand      عنوان بالای منو (اختیاری، پیش‌فرض: پنل مدیریت)
 */
$brand = $brand ?? 'پنل مدیریت';
$displayName = !empty($me['full_name']) ? $me['full_name'] : ($me['username'] ?? '');
$initial = mb_substr($displayName !== '' ? $displayName : '?', 0, 1);
?>
<style>
.hb-toggle { position: fixed; top: 16px; right: 16px; z-index: 1001; width: 48px; height: 48px; border-radius: 12px; border: none; background: #1a1a2e; color: #e8b923; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 16px rgba(0,0,0,.25); }
.hb-toggle:hover { background: #25254a; }
.hb-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); z-index: 1002; opacity: 0; visibility: hidden; transition: opacity .3s ease, visibility .3s; }
.hb-overlay.open { opacity: 1; visibility: visible; }
.hb-drawer { position: fixed; top: 0; right: 0; bottom: 0; width: 280px; max-width: 85vw; background: #1a1a2e; color: #eee; z-index: 1003; transform: translateX(105%); transition: transform .3s ease; display: flex; flex-direction: column; box-shadow: -4px 0 24px rgba(0,0,0,.3); }
.hb-drawer.open { transform: translateX(0); }
.hb-head { display: flex; align-items: center; justify-content: space-between; padding: 18px 20px; border-bottom: 1px solid #2a2a4a; }
.hb-head h2 { color: #e8b923; font-size: 17px; }
.hb-close { background: none; border: none; color: #888; cursor: pointer; padding: 6px; display: flex; }
.hb-close:hover { color: #fff; }
.hb-nav { flex: 1; overflow-y: auto; padding: 12px 0; }
.hb-nav a { display: flex; align-items: center; gap: 12px; padding: 13px 20px; color: #aaa; text-decoration: none; font-size: 14px; }
.hb-nav a:hover { background: #16213e; color: #eee; }
.hb-nav a.active { background: #16213e; color: #e8b923; border-right: 3px solid #e8b923; padding-right: 17px; }
.hb-nav a svg { flex-shrink: 0; }
.hb-user { padding: 16px 20px; border-top: 1px solid #2a2a4a; font-size: 13px; color: #888; display: flex; align-items: center; gap: 10px; }
.hb-avatar { width: 36px; height: 36px; border-radius: 50%; background: #e8b923; color: #1a1a2e; display: inline-flex; align-items: center; justify-content: center; font-weight: bold; flex-shrink: 0; }
.hb-user a { color: #888; text-decoration: none; font-size: 12px; }
.hb-user a:hover { color: #fff; }
body.hb-locked { overflow: hidden; }
</style>

<button class="hb-toggle" id="hbToggle" aria-label="باز کردن منو">
    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
</button>
<div class="hb-overlay" id="hbOverlay"></div>
<aside class="hb-drawer" id="hbDrawer" aria-label="منو">
    <div class="hb-head">
        <h2><?= htmlspecialchars($brand) ?></h2>
        <button class="hb-close" id="hbClose" aria-label="بستن منو">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </button>
    </div>
    <nav class="hb-nav">
        <?php foreach ($menu as $item): ?>
            <a href="<?= htmlspecialchars($item['url']) ?>" class="<?= $currentUrl === trim((string) $item['url'], '/') ? 'active' : '' ?>">
                <?= $item['icon'] ?><span><?= htmlspecialchars($item['title']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="hb-user">
        <span class="hb-avatar"><?= htmlspecialchars($initial) ?></span>
        <span><?= htmlspecialchars($displayName) ?><br><a href="user/logout">خروج</a></span>
    </div>
</aside>
<script>
(function() {
    var drawer = document.getElementById('hbDrawer');
    var overlay = document.getElementById('hbOverlay');
    function openMenu() { drawer.classList.add('open'); overlay.classList.add('open'); document.body.classList.add('hb-locked'); }
    function closeMenu() { drawer.classList.remove('open'); overlay.classList.remove('open'); document.body.classList.remove('hb-locked'); }
    document.getElementById('hbToggle').addEventListener('click', openMenu);
    document.getElementById('hbClose').addEventListener('click', closeMenu);
    overlay.addEventListener('click', closeMenu);
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeMenu(); });
})();
</script>
