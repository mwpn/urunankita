<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Billing routes (protected by auth)
$routes->group('billing', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->get('invoices', 'Modules\\Billing\\Controllers\\BillingController::getInvoices');
    $routes->get('invoice/(:num)', 'Modules\\Billing\\Controllers\\BillingController::invoice/$1');
    $routes->post('invoice/(:num)/pay', 'Modules\\Billing\\Controllers\\BillingController::pay/$1');
});

// Revenue stats (superadmin only)
$routes->group('billing', ['filter' => 'auth|role:superadmin'], static function (RouteCollection $routes) {
    $routes->get('revenue', 'Modules\\Billing\\Controllers\\BillingController::revenue');
});

