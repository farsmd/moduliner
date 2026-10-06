<?php
/**
 * فرم افزودن/ویرایش آیتم منو
 * متغیرها: $item (null برای جدید), $icons, $roles, $iconKey?, $csrf, $error?
 */
$isEdit = $item !== null;
$roleFa = ['owner' => 'مالک', 'admin' => 'مدیر', 'user' => 'کاربر'];
$selectedRoles = $isEdit ? (json_decode((string) $item['roles'], true) ?: []) : ['owner', 'admin'];
$currentIconKey = $iconKey ?? 'grid';
ob_start();
?>
<?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<div class="card">
    <form method="post" action="<?= $isEdit ? 'admin/menus/' . (int) $item['id'] : 'admin/menus/add' ?>">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        <div class="field">
            <label>عنوان</label>
            <input type="text" name="title" required style="width:100%;" value="<?= htmlspecialchars($isEdit ? $item['title'] : '') ?>">
        </div>
        <div class="field">
            <label>آدرس (نسبی، مثل <code dir="ltr">admin/users</code>)</label>
            <input type="text" name="url" required dir="ltr" style="width:100%;text-align:left;" value="<?= htmlspecialchars($isEdit ? $item['url'] : '') ?>">
        </div>
        <div class="field">
            <label>آیکون</label>
            <select name="icon" style="width:100%;">
                <?php foreach ($icons as $key => $svg): ?>
                    <option value="<?= htmlspecialchars($key) ?>" <?= $currentIconKey === $key ? 'selected' : '' ?>><?= htmlspecialchars($key) ?></option>
                <?php endforeach; ?>
                <?php if ($currentIconKey === 'custom'): ?>
                    <option value="__keep__" selected>آیکون سفارشی ماژول (حفظ شود)</option>
                <?php endif; ?>
            </select>
        </div>
        <div class="field">
            <label>نقش‌های مجاز</label>
            <div style="display:flex;gap:16px;">
                <?php foreach ($roles as $r): ?>
                    <label style="display:flex;align-items:center;gap:6px;font-size:14px;color:#333;">
                        <input type="checkbox" name="roles[]" value="<?= htmlspecialchars($r) ?>" <?= in_array($r, $selectedRoles, true) ? 'checked' : '' ?> style="width:auto;margin:0;">
                        <?= htmlspecialchars($roleFa[$r] ?? $r) ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="field">
            <label style="display:flex;align-items:center;gap:8px;font-size:14px;color:#333;">
                <input type="checkbox" name="is_active" value="1" <?= !$isEdit || (bool) $item['is_active'] ? 'checked' : '' ?> style="width:auto;margin:0;">
                فعال
            </label>
        </div>
        <button type="submit" class="btn btn-gold">ذخیره</button>
        <a class="btn btn-ghost" href="admin/menus">انصراف</a>
    </form>
</div>
<?php
$content = ob_get_clean();
require MODULES_PATH . '/admin/views/layout.php';
