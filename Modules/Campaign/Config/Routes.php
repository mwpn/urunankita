<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Public routes - Lihat Urunan
$routes->get('campaign/list', 'Modules\\Campaign\\Controllers\\CampaignController::list');
$routes->get('campaign/(:segment)', 'Modules\\Campaign\\Controllers\\CampaignController::show/$1');

// Authenticated routes - Buat Urunan & Kelola
$routes->group('campaign', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->get('my-campaigns', 'Modules\\Campaign\\Controllers\\CampaignController::myCampaigns');
    $routes->post('create', 'Modules\\Campaign\\Controllers\\CampaignController::create');
    $routes->post('update/(:num)', 'Modules\\Campaign\\Controllers\\CampaignController::update/$1');
    $routes->post('submit/(:num)', 'Modules\\Campaign\\Controllers\\CampaignController::submit/$1');
});

// Admin routes - Verifikasi (Tim UrunanKita)
$routes->group('campaign', ['filter' => 'auth|role:superadmin'], static function (RouteCollection $routes) {
    $routes->post('verify/(:num)', 'Modules\\Campaign\\Controllers\\CampaignController::verify/$1');
});

