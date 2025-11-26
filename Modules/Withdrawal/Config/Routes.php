<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Withdrawal routes (protected by auth)
$routes->group('withdrawal', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->post('request', 'Modules\\Withdrawal\\Controllers\\WithdrawalController::request');
    $routes->get('list', 'Modules\\Withdrawal\\Controllers\\WithdrawalController::list');
    $routes->post('complete/(:num)', 'Modules\\Withdrawal\\Controllers\\WithdrawalController::complete/$1');
});

// Admin routes - Approve/Reject (Tim UrunanKita)
$routes->group('withdrawal', ['filter' => 'auth|role:superadmin'], static function (RouteCollection $routes) {
    $routes->post('approve/(:num)', 'Modules\\Withdrawal\\Controllers\\WithdrawalController::approve/$1');
    $routes->post('reject/(:num)', 'Modules\\Withdrawal\\Controllers\\WithdrawalController::reject/$1');
});

