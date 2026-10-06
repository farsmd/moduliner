<?php
/**
 * مسیرهای ماژول admin
 * همه روت‌ها داخل کنترلر گارد نقش دارند
 */
$router->get('admin', 'admin', 'Controller', 'dashboard');
$router->get('admin/users', 'admin', 'Controller', 'users');
$router->get('admin/users/{id}', 'admin', 'Controller', 'editUser');
$router->post('admin/users/{id}', 'admin', 'Controller', 'saveUser');
$router->post('admin/users/{id}/toggle', 'admin', 'Controller', 'toggleUser');
