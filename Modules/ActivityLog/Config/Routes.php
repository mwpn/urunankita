<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Activity Log routes (protected by auth & tenant isolation)
$routes->group('activity-log', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->get('list', 'Modules\\ActivityLog\\Controllers\\ActivityLogController::list');
    $routes->get('summary', 'Modules\\ActivityLog\\Controllers\\ActivityLogController::summary');
    $routes->get('entity/(:segment)/(:segment)', 'Modules\\ActivityLog\\Controllers\\ActivityLogController::entityLogs/$1/$2');
});

