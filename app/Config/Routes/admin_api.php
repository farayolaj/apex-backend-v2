<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\Admin\v1\EmailBuilderController;

/**
 * @var RouteCollection $routes
 */

$routes->group('web/v1/courses', ['filter' => ['cors', 'apiValidation:admin']], function ($routes) {
    $routes->add('(:any)', '\App\Controllers\Admin\v1\Courses::index/$1');

    $routes->options('(:any)', static function () {});
    $routes->options('(:any)/(:any)', static function () {});
    $routes->options('(:any)/(:any)/(:any)', static function () {});
});

$routes->group('web/email_builder', ['filter' => ['apiValidation:admin']], function ($routes) {
    $routes->get('logs/(:alphanum)', [[EmailBuilderController::class, 'show'], '$1']);
    $routes->get('clean_stale_email', [EmailBuilderController::class, 'cleanupStaleEmail']);
    $routes->post('applicants', [EmailBuilderController::class, 'storeApplicant']);
    $routes->post('students', [EmailBuilderController::class, 'storeStudent']);

    $routes->options('(:any)', static function () {});
});
