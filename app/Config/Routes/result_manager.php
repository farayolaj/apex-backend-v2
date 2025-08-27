<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('v1/web/', [
    'filter' => ['apiValidation:admin'],
    'namespace' => 'App\Controllers\Admin\V1'
], function ($routes) {

    $routes->get('result_manager/session_with_enrollment', 'ResultManagerController::sessionWithEnrollment');
    $routes->get('result_manager/examination_courses', 'ResultManagerController::examinationCourses');
    $routes->get('result_manager/assigned_list_courses', 'ResultManagerController::assignedListCourses');
    $routes->get('result_manager/examination_scores_list/(:segment)/(:segment)/(:segment)', 'ResultManagerController::examinationScoresList/$1/$2/$3');
    $routes->post('result_manager/examination_scores/(:any)/(:any)/(:any)/(:any)', 'ResultManagerController::examinationScores/$1/$2/$3/$4');


    // handle CORS preflight requests
    $routes->options('(:any)', static function () {});
    $routes->options('(:any)/(:num)', static function () {});
});

// this is for result
$routes->get('web/download_result_sample/(:any)/(:any)/(:any)/(:any)', 'Export::download_result_sample/$1/$2/$3/$4');
$routes->post('web/examination_result_export', 'ResultManager::examination_result_export');
$routes->get('web/assigned_course_list', 'ResultManager::assigned_course_list');
$routes->get('web/preview_request_claim', 'ResultManager::preview_request_claim');
$routes->post('web/submit_request_claim', 'ResultManager::submit_request_claim');
$routes->post('web/examination_approval_action', 'ResultManager::examination_approval_action');
$routes->get('web/result_approval_stats', 'ResultManager::result_approval_stats');
