<?php
/**
 * مسیرهای ماژول database — فقط owner (گارد داخل کنترلر)
 */
$router->get('admin/database', 'database', 'Controller', 'overview');
$router->post('admin/database/backup', 'database', 'Controller', 'backup');
$router->post('admin/database/restore', 'database', 'Controller', 'restore');
$router->post('admin/database/switch', 'database', 'Controller', 'switchDb');
$router->post('admin/database/create', 'database', 'Controller', 'create');
$router->post('admin/database/delete-backup', 'database', 'Controller', 'deleteBackup');
$router->get('admin/database/download/{file}', 'database', 'Controller', 'download');
