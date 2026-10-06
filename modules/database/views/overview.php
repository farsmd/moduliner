<?php
/**
 * نمای کلی مدیریت دیتابیس
 * متغیرها: $conn, $stats, $databases, $backups, $flash, $csrf, ...
 */
$fmtDate = fn($ts) => date('Y/m/d H:i', (int) $ts);
ob_start();
?>

<?php if ($flash !== null): ?>
    <div class="<?= $flash['error'] ? 'error' : '' ?>" style="<?= $flash['error'] ? '' : 'background:#e8f5e9;color:#2e7d32;padding:12px;border-radius:8px;margin-bottom:16px;font-size:14px;' ?>">
        <?= htmlspecialchars($flash['msg']) ?>
    </div>
<?php endif; ?>

<div class="card">
    <h3 style="margin-bottom:12px;font-size:16px;">اتصال فعلی</h3>
    <table>
        <tr><th>فایل</th><td dir="ltr"><?= htmlspecialchars($conn['file']) ?></td></tr>
        <tr><th>مسیر</th><td dir="ltr" style="font-size:12px;"><?= htmlspecialchars($conn['path']) ?></td></tr>
        <tr><th>حجم</th><td dir="ltr"><?= htmlspecialchars($this->fmtSize($conn['size'])) ?></td></tr>
        <tr><th>وضعیت</th><td>
            <?php if ($conn['exists'] && $conn['writable']): ?><span class="badge badge-on">متصل — قابل‌نوشتن</span>
            <?php elseif ($conn['exists']): ?><span class="badge badge-user">متصل — فقط‌خواندنی</span>
            <?php else: ?><span class="badge badge-off">فایل وجود ندارد</span><?php endif; ?>
        </td></tr>
        <tr><th>نسخه SQLite</th><td dir="ltr"><?= htmlspecialchars($conn['sqlite_version']) ?></td></tr>
    </table>
</div>

<div class="card">
    <h3 style="margin-bottom:12px;font-size:16px;">آمار</h3>
    <div class="stats">
        <div class="stat"><div class="num"><?= (int) $stats['tableCount'] ?></div><div class="lbl">جدول‌ها</div></div>
        <div class="stat"><div class="num"><?= (int) $stats['columnCount'] ?></div><div class="lbl">ستون‌ها</div></div>
        <div class="stat"><div class="num"><?= (int) $stats['rowCount'] ?></div><div class="lbl">ردیف‌ها</div></div>
    </div>
    <table style="margin-top:12px;">
        <tr><th>جدول</th><th>ستون‌ها</th><th>ردیف‌ها</th></tr>
        <?php foreach ($stats['tables'] as $t): ?>
            <tr><td dir="ltr"><?= htmlspecialchars($t['name']) ?></td><td><?= (int) $t['columns'] ?></td><td><?= (int) $t['rows'] ?></td></tr>
        <?php endforeach; ?>
    </table>
</div>

<div class="card">
    <h3 style="margin-bottom:12px;font-size:16px;">دیتابیس‌ها</h3>
    <table>
        <tr><th>فایل</th><th>حجم</th><th>تغییر</th><th>وضعیت</th><th>عملیات</th></tr>
        <?php foreach ($databases as $d): ?>
            <tr>
                <td dir="ltr"><?= htmlspecialchars($d['name']) ?></td>
                <td dir="ltr"><?= htmlspecialchars($this->fmtSize($d['size'])) ?></td>
                <td style="font-size:12px;"><?= $fmtDate($d['mtime']) ?></td>
                <td><?= $d['active'] ? '<span class="badge badge-owner">فعال</span>' : '<span class="badge badge-user">—</span>' ?></td>
                <td>
                    <?php if (!$d['active']): ?>
                        <form class="inline" method="post" action="admin/database/switch">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                            <input type="hidden" name="file" value="<?= htmlspecialchars($d['name']) ?>">
                            <button type="submit" class="btn btn-ghost" onclick="return confirm('دیتابیس فعال به این فایل تغییر کند؟')">فعال‌سازی</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <form method="post" action="admin/database/create" style="margin-top:16px;display:flex;gap:8px;flex-wrap:wrap;">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        <input type="text" name="name" placeholder="نام دیتابیس جدید (مثل shop)" dir="ltr" style="flex:1;min-width:180px;">
        <button type="submit" class="btn btn-gold">ساخت دیتابیس جدید</button>
    </form>
</div>

<div class="card">
    <h3 style="margin-bottom:12px;font-size:16px;">بکاپ‌ها</h3>
    <form method="post" action="admin/database/backup" style="margin-bottom:16px;">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
        <button type="submit" class="btn btn-gold">گرفتن بکاپ از دیتابیس فعلی</button>
    </form>
    <?php if (empty($backups)): ?>
        <p style="font-size:13px;color:#888;">هنوز بکاپی گرفته نشده است.</p>
    <?php else: ?>
        <table>
            <tr><th>فایل</th><th>حجم</th><th>تاریخ</th><th>عملیات</th></tr>
            <?php foreach ($backups as $b): ?>
                <tr>
                    <td dir="ltr" style="font-size:13px;"><?= htmlspecialchars($b['name']) ?></td>
                    <td dir="ltr"><?= htmlspecialchars($this->fmtSize($b['size'])) ?></td>
                    <td style="font-size:12px;"><?= $fmtDate($b['mtime']) ?></td>
                    <td>
                        <form class="inline" method="post" action="admin/database/restore" onsubmit="return confirm('هشدار: دیتابیس فعلی با این بکاپ جایگزین می‌شود! (از وضعیت فعلی بکاپ خودکار گرفته می‌شود) ادامه می‌دهید؟')">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                            <input type="hidden" name="file" value="<?= htmlspecialchars($b['name']) ?>">
                            <button type="submit" class="btn btn-ghost">بازگردانی</button>
                        </form>
                        <a class="btn btn-ghost" href="admin/database/download/<?= htmlspecialchars($b['name']) ?>">دانلود</a>
                        <form class="inline" method="post" action="admin/database/delete-backup" onsubmit="return confirm('این بکاپ حذف شود؟')">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($csrf) ?>">
                            <input type="hidden" name="file" value="<?= htmlspecialchars($b['name']) ?>">
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
// استفاده از لی‌آوت پنل ادمین
require MODULES_PATH . '/admin/views/layout.php';
