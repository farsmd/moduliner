<?php
/**
 * آیتم منوی ماژول menu
 */
$iconMenu = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>';

return [
    ['title' => 'منوها', 'url' => 'admin/menus', 'icon' => $iconMenu, 'roles' => ['owner', 'admin']],
];
