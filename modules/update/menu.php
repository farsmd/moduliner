<?php
/**
 * آیتم منوی ماژول update — فقط مالک
 */
$iconUpdate = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-2.64-6.36"/><polyline points="21 3 21 9 15 9"/></svg>';

return [
    ['title' => 'به‌روزرسانی', 'url' => 'update', 'icon' => $iconUpdate, 'roles' => ['owner']],
];
