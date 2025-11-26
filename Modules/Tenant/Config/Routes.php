<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Tenant management routes (superadmin only)
$routes->group('tenant', ['filter' => 'auth|role:superadmin'], static function (RouteCollection $routes) {
    $routes->get('list', 'Modules\\Tenant\\Controllers\\TenantController::list');
    $routes->get('show/(:num)', 'Modules\\Tenant\\Controllers\\TenantController::show/$1');
    $routes->post('create', 'Modules\\Tenant\\Controllers\\TenantController::create');
    $routes->post('update/(:num)', 'Modules\\Tenant\\Controllers\\TenantController::update/$1');
    $routes->delete('delete/(:num)', 'Modules\\Tenant\\Controllers\\TenantController::delete/$1');
});

