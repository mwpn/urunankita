<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Admin (superadmin) dashboard
$routes->group('admin', ['filter' => 'auth|role:superadmin'], static function (RouteCollection $routes) {
    $routes->get('/', 'Modules\\Dashboard\\Controllers\\AdminController::index');
});

// Tenant dashboard: /tenant/{slug}/dashboard
$routes->group('tenant', static function (RouteCollection $routes) {
    $routes->group('(:segment)', ['filter' => 'tenant:/$1|auth'], static function (RouteCollection $routes) {
        $routes->get('dashboard', 'Modules\\Dashboard\\Controllers\\TenantController::index/$1');
    });
});


