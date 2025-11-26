<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Public routes - Get categories
$routes->get('helpdesk/categories', 'Modules\\Helpdesk\\Controllers\\HelpdeskController::categories');

// Tenant routes - Create & manage tickets
$routes->group('helpdesk', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->get('tickets', 'Modules\\Helpdesk\\Controllers\\HelpdeskController::tickets');
    $routes->get('ticket/(:alphanumeric)', 'Modules\\Helpdesk\\Controllers\\HelpdeskController::ticket/$1');
    $routes->post('ticket/create', 'Modules\\Helpdesk\\Controllers\\HelpdeskController::createTicket');
    $routes->post('ticket/(:num)/reply', 'Modules\\Helpdesk\\Controllers\\HelpdeskController::addReply/$1');
});

// Admin routes - Manage all tickets
$routes->group('helpdesk', ['filter' => 'auth|role:superadmin'], static function (RouteCollection $routes) {
    $routes->get('admin/tickets', 'Modules\\Helpdesk\\Controllers\\HelpdeskController::adminTickets');
    $routes->post('ticket/(:num)/status', 'Modules\\Helpdesk\\Controllers\\HelpdeskController::updateStatus/$1');
});

