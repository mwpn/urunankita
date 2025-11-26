<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Setting routes (protected by auth)
$routes->group('setting', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->get('get/(:segment)', 'Modules\\Setting\\Controllers\\SettingController::get/$1');
    $routes->post('set', 'Modules\\Setting\\Controllers\\SettingController::set');
    $routes->get('all', 'Modules\\Setting\\Controllers\\SettingController::all');
    $routes->delete('delete/(:segment)', 'Modules\\Setting\\Controllers\\SettingController::delete/$1');
    
    // Tenant-specific settings
    $routes->get('tenant/(:segment)', 'Modules\\Setting\\Controllers\\SettingController::getTenant/$1');
    $routes->post('tenant/set', 'Modules\\Setting\\Controllers\\SettingController::setTenant');
});

