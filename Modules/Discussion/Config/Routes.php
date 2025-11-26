<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Public routes - Lihat & tambah komentar
$routes->get('discussion/campaign/(:num)', 'Modules\\Discussion\\Controllers\\DiscussionController::getComments/$1');
$routes->post('discussion/comment', 'Modules\\Discussion\\Controllers\\DiscussionController::addComment');
$routes->post('discussion/comment/(:num)/like', 'Modules\\Discussion\\Controllers\\DiscussionController::like/$1');
$routes->post('discussion/comment/(:num)/unlike', 'Modules\\Discussion\\Controllers\\DiscussionController::unlike/$1');

// Authenticated routes - Manage comments (Tenant/Admin)
$routes->group('discussion', ['filter' => 'auth'], static function (RouteCollection $routes) {
    $routes->delete('comment/(:num)', 'Modules\\Discussion\\Controllers\\DiscussionController::delete/$1');
    $routes->post('comment/(:num)/moderate', 'Modules\\Discussion\\Controllers\\DiscussionController::moderate/$1');
    $routes->post('comment/(:num)/pin', 'Modules\\Discussion\\Controllers\\DiscussionController::pin/$1');
});

