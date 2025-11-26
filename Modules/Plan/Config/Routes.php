<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Public routes - list plans
$routes->get('plan/list', 'Modules\\Plan\\Controllers\\PlanController::list');
$routes->get('plan/show/(:num)', 'Modules\\Plan\\Controllers\\PlanController::show/$1');

// Admin routes - CRUD plans (superadmin only)
$routes->group('plan', ['filter' => 'auth|role:superadmin'], static function (RouteCollection $routes) {
    $routes->post('create', 'Modules\\Plan\\Controllers\\PlanController::create');
    $routes->post('update/(:num)', 'Modules\\Plan\\Controllers\\PlanController::update/$1');
    $routes->delete('delete/(:num)', 'Modules\\Plan\\Controllers\\PlanController::delete/$1');
});

