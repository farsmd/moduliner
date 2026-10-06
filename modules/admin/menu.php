<?php
/**
 * آیتم‌های منوی ماژول admin
 * فرمت: title, url, icon (SVG), roles
 */
$iconGrid = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>';
$iconUsers = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>';

return [
    ['title' => 'داشبورد', 'url' => 'admin', 'icon' => $iconGrid, 'roles' => ['owner', 'admin']],
    ['title' => 'کاربران', 'url' => 'admin/users', 'icon' => $iconUsers, 'roles' => ['owner', 'admin']],
];
