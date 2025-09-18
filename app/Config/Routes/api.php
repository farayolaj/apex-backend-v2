<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('v1/api/', [
    'filter' => ['apiValidation:student'],
    'namespace' => 'App\Controllers\Student\V1'
], function ($routes) {
    // webinars
    $routes->get('courses/(:num)/(:num)/webinars', 'WebinarController::listWebinars/$1/$2');
    $routes->get('webinars/(:num)', 'WebinarController::getWebinar/$1');
    $routes->get('webinars/(:num)/join_url', 'WebinarController::getJoinUrl/$1');

    // webinar logs
    $routes->post('webinars/(:num)/log_playback', 'WebinarController::logPlayback/$1');

    // webinar comments
    $routes->get('webinars/(:num)/comments', 'WebinarCommentController::getComments/$1');
    $routes->post('webinars/(:num)/comments', 'WebinarCommentController::newComment/$1');
    $routes->delete('webinars/comments/(:num)', 'WebinarCommentController::deleteComment/$1');

    // notifications
    $routes->get('notifications', 'NotificationController::getNotifications');
    $routes->get('notifications/count', 'NotificationController::getNotificationCount');
    $routes->post('notifications/read', 'NotificationController::markAsRead');

    // profile
    $routes->get('student/profile', 'ProfileController::index');
    $routes->patch('student/profile', 'ProfileController::update');
    $routes->get('student/dashboard', 'ProfileController::dashboard');
    $routes->patch('student/password', 'ProfileController::updatePassword');

    // courses
    $routes->get('student/course/details/(:num)', 'CoursesController::courseDetails/$1');
    $routes->get('course/enrollment/(:segment)/(:segment)', 'CoursesController::enrollment/$1/$2');
    $routes->get('course/stats', 'CoursesController::stats');
    $routes->get('course/config', 'CoursesController::config');
    $routes->get('course/preload/(:segment)', 'CoursesController::preload/$1');
    $routes->get('course/search', 'CoursesController::search');
    $routes->get('course/is_registration_open', 'CoursesController::isOpen');
    $routes->get('course/is_registration_delete_open', 'CoursesController::isDeleteOpen');
    $routes->post('course/registration', 'CoursesController::register');
    $routes->post('course/unregister', 'CoursesController::unregister');
    $routes->get('student/sessions', 'CoursesController::sessions');

    // tour
    $routes->get('tour/settings', 'TourController::getTourSettings');

    // handles the cors
    $routes->options('(:any)', static function () {});
    $routes->options('(:any)/(:num)', static function () {});
    $routes->options('(:any)/(:any)/(:any)', static function () {});
});

// this is the api for finance sync between server[UI Admission]
//$routes->get('api/integrations/finance/v1/transaction', 'FinanceIntegration::getTransactionData');

$routes->group('v1/api/', ['filter' => 'apiValidation:student'], static function (RouteCollection $routes): void {
    $routes->post('authenticate', 'Auth::student');
    $routes->post('validate_student', 'Auth::validate_student');
    $routes->post('logout', 'Auth::logout');

    $routes->options('(:any)', static function () {});
});

$routes->group('api', ['filter' => ['cors', 'apiValidation:student']], function ($routes) {
    $routes->add('(:any)', 'Api::frontApi/$1');
    $routes->add('(:any)/(:any)', 'Api::frontApi/$1/$2');
    $routes->add('(:any)/(:any)/(:any)', 'Api::frontApi/$1/$2/$3');

    $routes->options('(:any)', static function () {});
    $routes->options('(:any)/(:any)', static function () {});
    $routes->options('(:any)/(:any)/(:any)', static function () {});
});
