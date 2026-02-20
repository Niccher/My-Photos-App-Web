<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Photos::index');
$routes->get('scan', 'Photos::scan');
$routes->post('upload', 'Photos::upload');
