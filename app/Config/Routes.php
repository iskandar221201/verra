<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

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
});
