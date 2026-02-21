<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// All app routes require an authenticated session
$routes->group('', ['filter' => 'session'], function ($routes) {
    $routes->get('/', 'Photos::index');
    $routes->get('scan', 'Photos::scan');
    $routes->post('upload', 'Photos::upload');
    $routes->get('explore', 'Photos::explore');
    $routes->get('sharing', 'Photos::sharing');
    $routes->get('archive', 'Photos::archive');
    $routes->get('trash', 'Photos::trash');

    // Photo action API
    $routes->post('photos/archive/(:num)', 'Photos::archivePhoto/$1');
    $routes->post('photos/delete/(:num)',  'Photos::deletePhoto/$1');
    $routes->post('photos/restore/(:num)', 'Photos::restorePhoto/$1');

    // Sharing API
    $routes->post('photos/share/(:num)',   'Photos::sharePhoto/$1');
    $routes->post('photos/unshare/(:num)', 'Photos::unsharePhoto/$1');
    $routes->get('users/search',           'Photos::searchUsers');
});

// Shield's auth routes (login, register, magic-link, etc.)
service('auth')->routes($routes);
