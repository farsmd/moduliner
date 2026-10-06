<?php
/** لیست کاربران — متغیرها: $users, $me, ... */
$isOwner = ($me['role'] ?? '') === 'owner';
$roleFa = ['owner' => 'مالک', 'admin' => 'مدیر', 'user' => 'کاربر'];
ob_start();
?>
<div class="card">
    <table>
        <tr><th>کاربر</th><th>نام کاربری</th><th>نقش</th><th>وضعیت</th><?php if ($isOwner): ?><th>عملیات</th><?php endif; ?></tr>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= htmlspecialchars($u['full_name'] !== '' ? $u['full_name'] : '—') ?></td>
                <td dir="ltr"><?= htmlspecialchars($u['username']) ?></td>
                <td><span class="badge badge-<?= htmlspecialchars($u['role']) ?>"><?= htmlspecialchars($roleFa[$u['role']] ?? $u['role']) ?></span></td>
                <td>
                    <?php if ((bool) $u['is_active']): ?><span class="badge badge-on">فعال</span>
                    <?php else: ?><span class="badge badge-off">غیرفعال</span><?php endif; ?>
                </td>
                <?php if ($isOwner): ?>
                    <td>
                        <a class="btn btn-ghost" href="admin/users/<?= (int) $u['id'] ?>">ویرایش</a>
                        <?php if ((int) $u['id'] !== (int) $me['id']): ?>
                            <form class="inline" method="post" action="admin/users/<?= (int) $u['id'] ?>/toggle">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                                <button type="submit" class="btn <?= (bool) $u['is_active'] ? 'btn-danger' : 'btn-ok' ?>">
                                    <?= (bool) $u['is_active'] ? 'غیرفعال' : 'فعال' ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php if (!$isOwner): ?>
    <div class="card" style="font-size:13px;color:#888;">تغییر نقش و فعال/غیرفعال‌سازی فقط برای مالک (owner) مجاز است.</div>
<?php endif; ?>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
