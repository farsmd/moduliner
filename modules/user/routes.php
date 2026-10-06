<?php
/**
 * مسیرهای ماژول user
 * متغیرهای $router و $moduleName از App در دسترس‌اند
 */

$router->get('/', 'user', 'Controller', 'home');
$router->get('user', 'user', 'Controller', 'home');
$router->get('user/login', 'user', 'Controller', 'login');
$router->post('user/login', 'user', 'Controller', 'login');
$router->get('user/logout', 'user', 'Controller', 'logout');
$router->get('user/dashboard', 'user', 'Controller', 'dashboard');
