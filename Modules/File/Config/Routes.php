<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// File routes (protected by auth & tenant filter)
$routes->group('file', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->post('upload', 'Modules\\File\\Controllers\\FileController::upload');
    $routes->get('download/(:segment)', 'Modules\\File\\Controllers\\FileController::download/$1');
    $routes->delete('delete/(:segment)', 'Modules\\File\\Controllers\\FileController::delete/$1');
    $routes->get('list', 'Modules\\File\\Controllers\\FileController::list');
    $routes->get('info/(:segment)', 'Modules\\File\\Controllers\\FileController::info/$1');
});

