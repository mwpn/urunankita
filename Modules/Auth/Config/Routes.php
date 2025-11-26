<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->group('', static function (RouteCollection $routes) {
    // Login routes are handled in app/Config/Routes.php
    // Register tenant routes disabled - redirect to homepage
    $routes->get('/auth/register-tenant', function() {
        return redirect()->to('/');
    });
    $routes->post('/auth/register-tenant', function() {
        return redirect()->to('/');
    });
    $routes->get('/logout', 'Modules\\Auth\\Controllers\\AuthController::logout');
});


