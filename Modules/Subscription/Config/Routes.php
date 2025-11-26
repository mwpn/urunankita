<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Subscription routes (protected by auth)
$routes->group('subscription', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->get('active', 'Modules\\Subscription\\Controllers\\SubscriptionController::active');
    $routes->post('create', 'Modules\\Subscription\\Controllers\\SubscriptionController::create');
    $routes->post('cancel/(:num)', 'Modules\\Subscription\\Controllers\\SubscriptionController::cancel/$1');
    $routes->post('renew/(:num)', 'Modules\\Subscription\\Controllers\\SubscriptionController::renew/$1');
});

