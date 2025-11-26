<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Beneficiary routes (protected by auth & tenant isolation)
$routes->group('beneficiary', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->get('list', 'Modules\\Beneficiary\\Controllers\\BeneficiaryController::list');
    $routes->get('show/(:num)', 'Modules\\Beneficiary\\Controllers\\BeneficiaryController::show/$1');
    $routes->post('create', 'Modules\\Beneficiary\\Controllers\\BeneficiaryController::create');
    $routes->post('update/(:num)', 'Modules\\Beneficiary\\Controllers\\BeneficiaryController::update/$1');
    $routes->delete('delete/(:num)', 'Modules\\Beneficiary\\Controllers\\BeneficiaryController::delete/$1');
});

