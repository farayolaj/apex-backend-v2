<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\Admin\v1\EmailBuilderController;

/**
 * @var RouteCollection $routes
 */

// this routes handle things that their content-type shouldn't be application/json
$routes->group('v1/web/', [
    'filter' => ['apiValidation:admin,no-json'],
    'namespace' => 'App\Controllers\Admin\v1'
], function ($routes) {

    $routes->get('samples/(:segment)', 'SamplesController::download/$1');

    // handle CORS preflight requests
    $routes->options('(:any)', static function () {});
    $routes->options('(:any)/(:num)', static function () {});
});

$routes->group('v1/web/', [
    'filter' => ['apiValidation:admin'],
    'namespace' => 'App\Controllers\Admin\v1'
], function ($routes) {
    // course management
    $routes->get('courses', 'CoursesController::index');
    $routes->get('courses/(:num)', 'CoursesController::show/$1');
    $routes->post('courses', 'CoursesController::store');
    $routes->patch('courses/(:num)', 'CoursesController::update/$1');
    $routes->delete('courses/delete/(:num)', 'CoursesController::delete/$1');
    $routes->post('courses/bulk_course_upload', 'CoursesController::import');

    // webinar management
    $routes->get('courses/(:num)/webinars', 'Webinars::index/$1');
    $routes->get('webinars/(:num)/recordings', 'Webinars::getRecordings/$1');
    $routes->post('webinars', 'Webinars::create');
    $routes->patch('webinars/(:num)', 'Webinars::update/$1');
    $routes->delete('webinars/(:num)', 'Webinars::delete/$1');

    // handle CORS preflight requests
    $routes->options('(:any)', static function () {});
    $routes->options('(:any)/(:num)', static function () {});
    $routes->options('(:any)/(:any)/(:any)', static function () {});
});

$routes->get('v1/web/webinars/presentations/(:any)', '\App\Controllers\Admin\v1\Webinars::getPresentation/$1');

$routes->group('web/email_builder', ['filter' => ['apiValidation:admin']], function ($routes) {
    $routes->get('logs/(:alphanum)', [[EmailBuilderController::class, 'show'], '$1']);
    $routes->get('clean_stale_email', [EmailBuilderController::class, 'cleanupStaleEmail']);
    $routes->post('applicants', [EmailBuilderController::class, 'storeApplicant']);
    $routes->post('students', [EmailBuilderController::class, 'storeStudent']);

    $routes->options('(:any)', static function () {});
    $routes->options('(:any)/(:any)', static function () {});
});
