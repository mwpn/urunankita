<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Notification routes (protected by auth)
$routes->group('notification', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->post('whatsapp/send', 'Modules\\Notification\\Controllers\\NotificationController::sendWhatsApp');
    $routes->get('logs', 'Modules\\Notification\\Controllers\\NotificationController::getLogs');
});

