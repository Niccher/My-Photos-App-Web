<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Photos::index');
$routes->get('scan', 'Photos::scan');
$routes->post('upload', 'Photos::upload');
$routes->get('explore', 'Photos::explore');
$routes->get('sharing', 'Photos::sharing');
$routes->get('archive', 'Photos::archive');
$routes->get('trash', 'Photos::trash');

// API routes for photo actions
$routes->post('photos/archive/(:num)', 'Photos::archivePhoto/$1');
$routes->post('photos/delete/(:num)', 'Photos::deletePhoto/$1');
$routes->post('photos/restore/(:num)', 'Photos::restorePhoto/$1');
