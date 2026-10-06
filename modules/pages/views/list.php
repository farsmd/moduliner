<?php
/**
 * لیست صفحات در پنل ادمین
 * متغیرها: $items, $flash, $csrf, ...
 */
ob_start();
?>
<?php if ($flash !== null): ?>
    <div style="background:#e8f5e9;color:#2e7d32;padding:12px;border-radius:8px;margin-bottom:16px;font-size:14px;"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<div class="card">
    <div style="margin-bottom:16px;">
        <a class="btn btn-gold" href="admin/pages/add">صفحه جدید</a>
        <a class="btn btn-ghost" href="" target="_blank">مشاهده سایت</a>
    </div>
    <?php if (empty($items)): ?>
        <p style="font-size:13px;color:#888;">هنوز صفحه‌ای ساخته نشده است.</p>
    <?php else: ?>
        <table>
            <tr><th>عنوان</th><th>نامک</th><th>وضعیت</th><th>عملیات</th></tr>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td>
                        <?= htmlspecialchars($it['title']) ?>
                        <?php if ((bool) $it['is_home']): ?><span class="badge badge-owner">صفحه اصلی</span><?php endif; ?>
                    </td>
                    <td dir="ltr" style="font-size:13px;"><?= htmlspecialchars($it['slug']) ?></td>
                    <td><?= $it['status'] === 'published' ? '<span class="badge badge-on">منتشرشده</span>' : '<span class="badge badge-user">پیش‌نویس</span>' ?></td>
                    <td style="white-space:nowrap;">
                        <a class="btn btn-ghost" href="page/<?= htmlspecialchars($it['slug']) ?>" target="_blank">نمایش</a>
                        <a class="btn btn-ghost" href="admin/pages/<?= (int) $it['id'] ?>">ویرایش</a>
                        <form class="inline" method="post" action="admin/pages/<?= (int) $it['id'] ?>/toggle">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                            <button type="submit" class="btn <?= $it['status'] === 'published' ? 'btn-danger' : 'btn-ok' ?>">
                                <?= $it['status'] === 'published' ? 'پیش‌نویس' : 'انتشار' ?>
                            </button>
                        </form>
                        <form class="inline" method="post" action="admin/pages/<?= (int) $it['id'] ?>/delete" onsubmit="return confirm('این صفحه حذف شود؟')">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                            <button type="submit" class="btn btn-danger">حذف</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>
<?php
$content = ob_get_clean();
require MODULES_PATH . '/admin/views/layout.php';
