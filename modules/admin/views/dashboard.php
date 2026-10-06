<?php
/** داشبورد ادمین — متغیرها: $stats, $pageTitle, $me, $menu, $currentUrl */
ob_start();
?>
<div class="stats">
    <div class="stat"><div class="num"><?= (int) $stats['users'] ?></div><div class="lbl">کاربران</div></div>
    <div class="stat"><div class="num"><?= (int) $stats['modules'] ?></div><div class="lbl">ماژول‌ها</div></div>
    <div class="stat"><div class="num" dir="ltr"><?= htmlspecialchars($stats['version']) ?></div><div class="lbl">نسخه مودولاینر</div></div>
    <div class="stat"><div class="num" style="font-size:20px;" dir="ltr"><?= htmlspecialchars($stats['db_size']) ?></div><div class="lbl">حجم دیتابیس</div></div>
</div>

<div class="card">
    <h3 style="margin-bottom:12px;font-size:16px;">آیتم‌های منوی ماژولار</h3>
    <table>
        <tr><th>ماژول</th><th>عنوان منو</th><th>دسترسی</th></tr>
        <?php foreach ($menu as $item): ?>
            <tr>
                <td dir="ltr"><?= htmlspecialchars($item['module']) ?></td>
                <td><?= htmlspecialchars($item['title']) ?></td>
                <td><?= htmlspecialchars(implode('، ', $item['roles'])) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="card" style="font-size:13px;color:#888;">
    PHP <?= htmlspecialchars($stats['php']) ?> — هر ماژول با فایل <code dir="ltr">menu.php</code> آیتم خودش را به این منو اضافه می‌کند.
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
