<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

// Webhook Route (public, no auth)
$routes->post('webhook/(:segment)', 'WebhookController::receive/$1');

// Auth Routes override
$routes->group('', ['namespace' => 'App\Controllers\Auth'], static function ($routes) {
    $routes->get('login', 'LoginController::loginView');
    $routes->post('login', 'LoginController::loginAction');
    $routes->get('logout', 'LoginController::logout');
});

// Super Admin Routes
$routes->group('superadmin', ['filter' => ['session', 'permission:tenants.read']], static function ($routes) {
    $routes->get('dashboard', 'SuperAdmin\DashboardController::index');

    // Tenant Management
    $routes->get('tenant', 'SuperAdmin\TenantController::index');
    $routes->get('tenant/create', 'SuperAdmin\TenantController::create');
    $routes->post('tenant/store', 'SuperAdmin\TenantController::store');
    $routes->get('tenant/edit/(:num)', 'SuperAdmin\TenantController::edit/$1');
    $routes->post('tenant/update/(:num)', 'SuperAdmin\TenantController::update/$1');
    $routes->post('tenant/toggle-active/(:num)', 'SuperAdmin\TenantController::toggleActive/$1');
});

// Tenant Routes
$routes->group('', ['filter' => ['session', 'tenant']], static function ($routes) {
    $routes->get('dashboard', 'Tenant\DashboardController::index');

    // WA Channels
    $routes->get('channels', 'Tenant\ChannelController::index');
    $routes->get('channels/create', 'Tenant\ChannelController::create');
    $routes->post('channels/store', 'Tenant\ChannelController::store');
    $routes->get('channels/edit/(:num)', 'Tenant\ChannelController::edit/$1');
    $routes->post('channels/update/(:num)', 'Tenant\ChannelController::update/$1');
    $routes->get('channels/delete/(:num)', 'Tenant\ChannelController::delete/$1');

    // AI Configuration
    $routes->get('config', 'Tenant\ConfigController::index');
    $routes->post('config/update', 'Tenant\ConfigController::update');

    // API Keys
    $routes->get('api-keys', 'Tenant\ApiKeyController::index');
    $routes->post('api-keys/store', 'Tenant\ApiKeyController::store');
    $routes->post('api-keys/update/(:num)', 'Tenant\ApiKeyController::update/$1');
    $routes->get('api-keys/delete/(:num)', 'Tenant\ApiKeyController::delete/$1');
    $routes->post('api-keys/update-priority', 'Tenant\ApiKeyController::updatePriority');

    // Knowledge Base
    $routes->get('kb', 'Tenant\KnowledgeBaseController::index');
    $routes->get('kb/create', 'Tenant\KnowledgeBaseController::create');
    $routes->post('kb/store', 'Tenant\KnowledgeBaseController::store');
    $routes->get('kb/edit/(:num)', 'Tenant\KnowledgeBaseController::edit/$1');
    $routes->post('kb/update/(:num)', 'Tenant\KnowledgeBaseController::update/$1');
    $routes->get('kb/delete/(:num)', 'Tenant\KnowledgeBaseController::delete/$1');
    $routes->get('kb/toggle/(:num)', 'Tenant\KnowledgeBaseController::toggleActive/$1');

    // Handover Keywords
    $routes->get('keywords', 'Tenant\KeywordController::index');
    $routes->post('keywords/store', 'Tenant\KeywordController::store');
    $routes->get('keywords/delete/(:num)', 'Tenant\KeywordController::delete/$1');
    $routes->get('keywords/toggle/(:num)', 'Tenant\KeywordController::toggleActive/$1');
});
