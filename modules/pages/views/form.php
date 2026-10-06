<?php
/**
 * فرم افزودن/ویرایش صفحه
 * متغیرها: $item (null برای جدید), $csrf, $error?
 */
$isEdit = $item !== null;
ob_start();
?>
<?php if (!empty($error)): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<div class="card">
    <form method="post" action="<?= $isEdit ? 'admin/pages/' . (int) $item['id'] : 'admin/pages/add' ?>">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        <div class="field">
            <label>عنوان</label>
            <input type="text" name="title" required style="width:100%;" value="<?= htmlspecialchars($isEdit ? $item['title'] : '') ?>">
        </div>
        <div class="field">
            <label>نامک (slug) — حروف انگلیسی کوچک، عدد و خط تیره</label>
            <input type="text" name="slug" required dir="ltr" style="width:100%;text-align:left;" value="<?= htmlspecialchars($isEdit ? $item['slug'] : '') ?>" placeholder="about-us">
        </div>
        <div class="field">
            <label>محتوا</label>
            <textarea name="content" rows="10" style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-family:inherit;font-size:14px;line-height:1.9;"><?= htmlspecialchars($isEdit ? $item['content'] : '') ?></textarea>
        </div>
        <div class="field">
            <label>وضعیت</label>
            <select name="status" style="width:100%;">
                <option value="published" <?= $isEdit && $item['status'] === 'published' ? 'selected' : '' ?>>منتشرشده</option>
                <option value="draft" <?= !$isEdit || $item['status'] !== 'published' ? 'selected' : '' ?>>پیش‌نویس</option>
            </select>
        </div>
        <div class="field">
            <label style="display:flex;align-items:center;gap:8px;font-size:14px;color:#333;">
                <input type="checkbox" name="is_home" value="1" <?= $isEdit && (bool) $item['is_home'] ? 'checked' : '' ?> style="width:auto;margin:0;">
                صفحه اصلی سایت
            </label>
        </div>
        <button type="submit" class="btn btn-gold">ذخیره</button>
        <a class="btn btn-ghost" href="admin/pages">انصراف</a>
    </form>
</div>
<?php
$content = ob_get_clean();
require MODULES_PATH . '/admin/views/layout.php';
