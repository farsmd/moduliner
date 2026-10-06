<?php
/**
 * مسیرهای ماژول pages
 * بخش عمومی سایت + مدیریت در پنل ادمین
 */
$router->get('/', 'pages', 'Controller', 'home');
$router->get('page/{slug}', 'pages', 'Controller', 'show');

$router->get('admin/pages', 'pages', 'Controller', 'list');
$router->get('admin/pages/add', 'pages', 'Controller', 'addForm');
$router->post('admin/pages/add', 'pages', 'Controller', 'add');
$router->get('admin/pages/{id}', 'pages', 'Controller', 'editForm');
$router->post('admin/pages/{id}', 'pages', 'Controller', 'edit');
$router->post('admin/pages/{id}/delete', 'pages', 'Controller', 'delete');
$router->post('admin/pages/{id}/toggle', 'pages', 'Controller', 'toggle');
