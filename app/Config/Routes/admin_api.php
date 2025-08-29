<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\Admin\V1\EmailBuilderController;

/**
 * @var RouteCollection $routes
 */

// this routes handle things that their content-type shouldn't be application/json
$routes->group('v1/web/', [
    'filter' => ['apiValidation:admin,no-json'],
    'namespace' => 'App\Controllers\Admin\V1'
], function ($routes) {

    $routes->get('samples/(:segment)', 'SamplesController::download/$1');

    // handle CORS preflight requests
    $routes->options('(:any)', static function () {});
    $routes->options('(:any)/(:num)', static function () {});
});

$routes->group('v1/web/', [
    'filter' => ['apiValidation:admin'],
    'namespace' => 'App\Controllers\Admin\V1'
], function ($routes) {
    // course management
    $routes->get('courses', 'CoursesController::index');
    $routes->get('courses/(:num)', 'CoursesController::show/$1');
    $routes->post('courses', 'CoursesController::store');
    $routes->patch('courses/(:num)', 'CoursesController::update/$1');
    $routes->delete('courses/(:num)', 'CoursesController::delete/$1');
    $routes->post('courses/bulk_course_upload', 'CoursesController::import');
    $routes->post('courses/bulk_course_enrollment_upload', 'CoursesController::importCourseEnrollment');

    $routes->resource('course_mapping', [
        'controller' => 'CourseMappingController',
        'only' => ['index', 'show', 'create', 'update', 'delete']
    ]);
    $routes->post('courses_mapping/bulk_course_mapping_upload', 'CourseMappingController::import');

    $routes->resource('course_config', [
        'controller' => 'CourseConfigController',
        'only' => ['index', 'show', 'create', 'update', 'delete']
    ]);
    $routes->post('course_configuration/bulk_course_config_upload', 'CourseConfigController::import');

    // document management
    $routes->resource('document_templates', [
        'controller' => 'DocumentTemplatesController',
        'only' => ['index', 'show', 'create', 'update', 'delete']
    ]);
    $routes->resource('templates', [
        'controller' => 'TemplatesController',
        'only' => ['index', 'show', 'create', 'update', 'delete']
    ]);

    $routes::get('common/prerequisites_fee', 'PaymentController::prerequisitesFee');

    // webinar management
    $routes->get('courses/(:num)/(:num)/webinars', 'WebinarController::index/$1/$2');
    $routes->get('webinars/(:num)/recordings', 'WebinarController::getRecordings/$1');
    $routes->post('webinars', 'WebinarController::create');
    $routes->patch('webinars/(:num)', 'WebinarController::update/$1');
    $routes->delete('webinars/(:num)', 'WebinarController::delete/$1');
    $routes->get('webinars/(:num)/join_url', 'WebinarController::getJoinUrl/$1');
    $routes->delete('webinars/(:num)/recordings', 'WebinarController::deleteRecordings/$1');

    // webinar comments
    $routes->get('webinars/(:num)/comments', 'WebinarCommentController::getComments/$1');
    $routes->post('webinars/(:num)/comments', 'WebinarCommentController::newComment/$1');
    $routes->delete('webinars/comments/(:num)', 'WebinarCommentController::deleteComment/$1');

    // handle CORS preflight requests
    $routes->options('(:any)', static function () {});
    $routes->options('(:any)/(:num)', static function () {});
    $routes->options('(:any)/(:any)/(:any)', static function () {});
});

$routes->get('v1/webinars/(:any)/presentations', '\App\Controllers\Admin\V1\WebinarController::getPresentation/$1');

$routes->group('v1/web/email_builder', [
    'filter' => ['apiValidation:admin'],
], function ($routes) {
    $routes->get('logs/(:alphanum)', [[EmailBuilderController::class, 'show'], '$1']);
    $routes->get('clean_stale_email', [EmailBuilderController::class, 'cleanupStaleEmail']);
    $routes->post('applicants', [EmailBuilderController::class, 'storeApplicant']);
    $routes->post('students', [EmailBuilderController::class, 'storeStudent']);

    $routes->options('(:any)', static function () {});
    $routes->options('(:any)/(:any)', static function () {});
});
