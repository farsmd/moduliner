<?php
/**
 * لیست آیتم‌های منو
 * متغیرها: $items, $icons, $flash, $csrf, ...
 */
$roleFa = ['owner' => 'مالک', 'admin' => 'مدیر', 'user' => 'کاربر'];
ob_start();
?>
<?php if ($flash !== null): ?>
    <div style="background:#e8f5e9;color:#2e7d32;padding:12px;border-radius:8px;margin-bottom:16px;font-size:14px;"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<div class="card">
    <div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
        <a class="btn btn-gold" href="admin/menus/add">آیتم جدید</a>
        <form class="inline" method="post" action="admin/menus/sync">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
            <button type="submit" class="btn btn-ghost">همگام‌سازی از ماژول‌ها</button>
        </form>
    </div>
    <table>
        <tr><th></th><th>عنوان</th><th>آدرس</th><th>ماژول</th><th>دسترسی</th><th>وضعیت</th><th>عملیات</th></tr>
        <?php foreach ($items as $i => $it): ?>
            <?php $roles = json_decode((string) $it['roles'], true) ?: []; ?>
            <tr>
                <td style="color:#e8b923;"><?= $this->iconSvg((string) $it['icon']) ?></td>
                <td><?= htmlspecialchars($it['title']) ?></td>
                <td dir="ltr" style="font-size:13px;"><?= htmlspecialchars($it['url']) ?></td>
                <td dir="ltr" style="font-size:13px;color:#888;"><?= htmlspecialchars($it['module']) ?></td>
                <td style="font-size:12px;"><?= htmlspecialchars(implode('، ', array_map(fn($r) => $roleFa[$r] ?? $r, $roles))) ?></td>
                <td><?= (bool) $it['is_active'] ? '<span class="badge badge-on">فعال</span>' : '<span class="badge badge-off">غیرفعال</span>' ?></td>
                <td style="white-space:nowrap;">
                    <form class="inline" method="post" action="admin/menus/<?= (int) $it['id'] ?>/move">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="dir" value="up">
                        <button type="submit" class="btn btn-ghost" title="بالا" <?= $i === 0 ? 'disabled' : '' ?>><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 5l7 9H5z"/></svg></button>
                    </form>
                    <form class="inline" method="post" action="admin/menus/<?= (int) $it['id'] ?>/move">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                        <input type="hidden" name="dir" value="down">
                        <button type="submit" class="btn btn-ghost" title="پایین" <?= $i === count($items) - 1 ? 'disabled' : '' ?>><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 19l-7-9h14z"/></svg></button>
                    </form>
                    <a class="btn btn-ghost" href="admin/menus/<?= (int) $it['id'] ?>">ویرایش</a>
                    <form class="inline" method="post" action="admin/menus/<?= (int) $it['id'] ?>/toggle">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                        <button type="submit" class="btn <?= (bool) $it['is_active'] ? 'btn-danger' : 'btn-ok' ?>">
                            <?= (bool) $it['is_active'] ? 'غیرفعال' : 'فعال' ?>
                        </button>
                    </form>
                    <form class="inline" method="post" action="admin/menus/<?= (int) $it['id'] ?>/delete" onsubmit="return confirm('این آیتم حذف شود؟')">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                        <button type="submit" class="btn btn-danger">حذف</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<div class="card" style="font-size:13px;color:#888;">
    آیتم‌های ماژول‌ها (داشبورد، کاربران، …) از فایل <code dir="ltr">menu.php</code> هر ماژول می‌آیند؛ با «همگام‌سازی» موارد جدید اضافه می‌شوند. تغییرات دستی شما دست نمی‌خورد.
</div>
<?php
$content = ob_get_clean();
require MODULES_PATH . '/admin/views/layout.php';
