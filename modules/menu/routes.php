<?php
/**
 * مسیرهای ماژول menu — نقش admin و owner (گارد داخل کنترلر)
 */
$router->get('admin/menus', 'menu', 'Controller', 'list');
$router->get('admin/menus/add', 'menu', 'Controller', 'addForm');
$router->post('admin/menus/add', 'menu', 'Controller', 'add');
$router->post('admin/menus/sync', 'menu', 'Controller', 'sync');
$router->get('admin/menus/{id}', 'menu', 'Controller', 'editForm');
$router->post('admin/menus/{id}', 'menu', 'Controller', 'edit');
$router->post('admin/menus/{id}/delete', 'menu', 'Controller', 'delete');
$router->post('admin/menus/{id}/toggle', 'menu', 'Controller', 'toggle');
$router->post('admin/menus/{id}/move', 'menu', 'Controller', 'move');
