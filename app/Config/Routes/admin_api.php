<?php

use CodeIgniter\Router\RouteCollection;
use App\Controllers\Admin\V1\EmailBuilderController;

/**
 * @var RouteCollection $routes
 */

// no filter routes
$routes->group('v1/web/', [
    'namespace' => 'App\Controllers\Admin\V1'
], function ($routes) {

    $routes->post('_relayq/run', '\Alatise\RelayQ\Controllers\RunController::runOne');

    $routes->options('(:segment)', static function () {});
    $routes->options('(:segment)/(:segment)', static function () {});
});

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

$routes->get('v1/webinars/(:any)/presentations', '\App\Controllers\Admin\V1\WebinarController::getPresentation/$1');
$routes->get('v1/webinars/(:any)/end', '\App\Controllers\Admin\V1\WebinarController::endWebinar/$1');
$routes->post('v1/webinars/recordings', '\App\Controllers\Admin\V1\WebinarController::recordingReadyCallback');

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
    $routes->get('courses/stats', 'CoursesController::stats');

    $routes->post('courses/(:num)/course_guide', 'CoursesController::uploadCourseGuide/$1');
    $routes->delete('courses/(:num)/course_guide', 'CoursesController::deleteCourseGuide/$1');

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

    // student management
    $routes->get('students/photos', 'PhotoManagerController::photos');
    $routes->post('students/photo/download', 'PhotoManagerController::photoDownload');
    $routes->get('students', 'StudentsController::index');
    $routes->get('students/(:segment)', 'StudentsController::show/$1');
    $routes->post('students/create', 'StudentsController::store', [
        'filter' => 'authorize:student_create'
    ]);
    $routes->patch('students/update/(:num)', 'StudentsController::update/$1', [
        'filter' => 'authorize:student_edit'
    ]);
    $routes->get('students/course/registered', 'StudentsController::studentAllRegistered', [
        'filter' => 'authorize:student_edit'
    ]);
    $routes->get('students/course/registration', 'StudentsController::registrationCourses', [
        'filter' => 'authorize:student_edit'
    ]);
    $routes->post('students/course/registration', 'StudentsController::registerForStudent', [
        'filter' => 'authorize:student_course_reg'
    ]);
    $routes->delete('students/course/registration', 'StudentsController::deleteStudentRegistered', [
        'filter' => 'authorize:student_delete_course_registration'
    ]);

    // applicants management
    $routes::post('admissions/single/admission', 'AdmissionController::singleAdmission');
    $routes::post('admissions/upload/admission', 'AdmissionController::uploadAdmission');

    $routes::get('common/prerequisites_fee', 'PaymentController::prerequisitesFee');

    // webinar management
    $routes->get('courses/(:num)/(:num)/webinars', 'WebinarController::index/$1/$2');
    $routes->post('webinars', 'WebinarController::create');
    $routes->patch('webinars/(:num)', 'WebinarController::update/$1');
    $routes->delete('webinars/(:num)', 'WebinarController::delete/$1');
    $routes->get('webinars/(:num)/join_url', 'WebinarController::getJoinUrl/$1');
    $routes->post('webinars/(:num)/presentation', 'WebinarController::updatePresentation/$1');
    $routes->delete('webinars/(:num)/presentation', 'WebinarController::deletePresentation/$1');

    // webinar comments
    $routes->get('webinars/(:num)/comments', 'WebinarCommentController::getComments/$1');
    $routes->post('webinars/(:num)/comments', 'WebinarCommentController::newComment/$1');
    $routes->delete('webinars/comments/(:num)', 'WebinarCommentController::deleteComment/$1');

    // notifications
    $routes->get('notifications', 'NotificationController::getNotifications');
    $routes->get('notifications/count', 'NotificationController::getNotificationCount');
    $routes->post('notifications/read', 'NotificationController::markAsRead');

    // handle CORS preflight requests
    $routes->options('(:any)', static function () {});
    $routes->options('(:any)/(:num)', static function () {});
    $routes->options('(:any)/(:any)/(:any)', static function () {});
});

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
