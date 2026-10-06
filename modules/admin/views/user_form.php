<?php
/** فرم ویرایش کاربر — متغیرها: $user, $roles, $csrf, $error?, ... */
$roleFa = ['owner' => 'مالک', 'admin' => 'مدیر', 'user' => 'کاربر'];
ob_start();
?>
<?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<div class="card">
    <form method="post" action="admin/users/<?= (int) $user['id'] ?>">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        <div class="field">
            <label>نام کاربری</label>
            <input type="text" value="<?= htmlspecialchars($user['username']) ?>" disabled dir="ltr" style="width:100%;background:#f5f5f5;">
        </div>
        <div class="field">
            <label>نام و نام خانوادگی</label>
            <input type="text" value="<?= htmlspecialchars($user['full_name']) ?>" disabled style="width:100%;background:#f5f5f5;">
        </div>
        <div class="field">
            <label>نقش</label>
            <select name="role" style="width:100%;">
                <?php foreach ($roles as $r): ?>
                    <option value="<?= htmlspecialchars($r) ?>" <?= $user['role'] === $r ? 'selected' : '' ?>>
                        <?= htmlspecialchars($roleFa[$r] ?? $r) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button type="submit" class="btn btn-gold">ذخیره</button>
        <a class="btn btn-ghost" href="admin/users">انصراف</a>
    </form>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
