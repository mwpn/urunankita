<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Public routes - Lihat Laporan Kabar Terbaru
$routes->get('campaign-update/campaign/(:num)', 'Modules\\CampaignUpdate\\Controllers\\CampaignUpdateController::getByCampaign/$1');

// Authenticated routes - Buat & Kelola Laporan
$routes->group('campaign-update', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->post('create', 'Modules\\CampaignUpdate\\Controllers\\CampaignUpdateController::create');
    $routes->post('update/(:num)', 'Modules\\CampaignUpdate\\Controllers\\CampaignUpdateController::update/$1');
    $routes->delete('delete/(:num)', 'Modules\\CampaignUpdate\\Controllers\\CampaignUpdateController::delete/$1');
});

