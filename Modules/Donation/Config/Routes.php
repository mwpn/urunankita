<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Public routes - Donasi
$routes->post('donation/create', 'Modules\\Donation\\Controllers\\DonationController::create');
$routes->get('donation/campaign/(:num)', 'Modules\\Donation\\Controllers\\DonationController::getByCampaign/$1');
$routes->get('donation/bank-accounts/(:num)', 'Modules\\Donation\\Controllers\\DonationController::getBankAccounts/$1');

// Authenticated routes - Konfirmasi pembayaran
$routes->group('donation', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->post('pay/(:num)', 'Modules\\Donation\\Controllers\\DonationController::pay/$1');
    $routes->post('confirm/(:num)', 'Modules\\Donation\\Controllers\\DonationController::confirm/$1');
});

