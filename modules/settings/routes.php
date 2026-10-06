<?php
/**
 * مسیرهای ماژول settings
 */
$router->get('admin/settings', 'settings', 'Controller', 'index');
$router->post('admin/settings', 'settings', 'Controller', 'save');
