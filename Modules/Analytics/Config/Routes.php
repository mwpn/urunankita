<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Analytics routes (protected by auth)
$routes->group('analytics', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->get('dashboard', 'Modules\\Analytics\\Controllers\\AnalyticsController::dashboard');
    $routes->get('top-campaigns', 'Modules\\Analytics\\Controllers\\AnalyticsController::topCampaigns');
    $routes->get('trends', 'Modules\\Analytics\\Controllers\\AnalyticsController::trends');
    $routes->get('campaign/(:num)', 'Modules\\Analytics\\Controllers\\AnalyticsController::campaignPerformance/$1');
});

// Admin routes - Platform stats
$routes->group('analytics', ['filter' => 'auth|role:superadmin'], static function (RouteCollection $routes) {
    $routes->get('platform', 'Modules\\Analytics\\Controllers\\AnalyticsController::platform');
});

