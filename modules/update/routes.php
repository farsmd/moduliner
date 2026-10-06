<?php
/**
 * مسیرهای ماژول update — فقط owner
 */
$router->get('update', 'update', 'Controller', 'index');
$router->post('update', 'update', 'Controller', 'index');
