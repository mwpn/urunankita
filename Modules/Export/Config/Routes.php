<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Export routes (protected by auth)
$routes->group('export', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->get('donations', 'Modules\\Export\\Controllers\\ExportController::donations');
    $routes->get('campaigns', 'Modules\\Export\\Controllers\\ExportController::campaigns');
    $routes->get('withdrawals', 'Modules\\Export\\Controllers\\ExportController::withdrawals');
});

