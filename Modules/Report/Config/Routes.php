<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Public routes - Laporan transparansi publik
$routes->get('report/public', 'Modules\\Report\\Controllers\\ReportController::publicReports');
$routes->get('report/campaign/(:num)', 'Modules\\Report\\Controllers\\ReportController::campaign');

// Authenticated routes - Generate & manage reports
$routes->group('report', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->get('financial', 'Modules\\Report\\Controllers\\ReportController::financial');
    $routes->get('campaign/(:num)', 'Modules\\Report\\Controllers\\ReportController::campaign/$1');
    $routes->get('list', 'Modules\\Report\\Controllers\\ReportController::list');
    $routes->post('save', 'Modules\\Report\\Controllers\\ReportController::save');
});

