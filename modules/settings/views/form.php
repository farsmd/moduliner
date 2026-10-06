<?php
/**
 * فرم تنظیمات در پنل ادمین
 * متغیرها: $items, $flash, $csrf, ...
 */
ob_start();
?>
<?php if ($flash !== null): ?>
    <div style="background:#e8f5e9;color:#2e7d32;padding:12px;border-radius:8px;margin-bottom:16px;font-size:14px;"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>
<div class="card">
    <form method="post" action="admin/settings">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        <?php foreach ($items as $it): ?>
            <div class="field">
                <label><?= htmlspecialchars($it['label'] !== '' ? $it['label'] : $it['key']) ?></label>
                <?php if ($it['type'] === 'textarea'): ?>
                    <textarea name="s[<?= htmlspecialchars($it['key']) ?>]" rows="3" style="width:100%;padding:10px 12px;border:1px solid #ddd;border-radius:8px;font-family:inherit;font-size:14px;"><?= htmlspecialchars($it['value']) ?></textarea>
                <?php else: ?>
                    <input type="text" name="s[<?= htmlspecialchars($it['key']) ?>]" style="width:100%;" value="<?= htmlspecialchars($it['value']) ?>">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <button type="submit" class="btn btn-gold">ذخیره تنظیمات</button>
    </form>
</div>
<?php
$content = ob_get_clean();
require MODULES_PATH . '/admin/views/layout.php';
