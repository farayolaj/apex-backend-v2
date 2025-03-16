<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// this is for result
$routes->get('web/session_with_enrollment', 'ResultManager::session_with_enrollment');
$routes->get('web/examination_scores_list/(:any)/(:any)/(:any)/(:any)', 'ResultManager::examination_scores_list/$1/$2/$3/$4');
$routes->post('web/examination_scores/(:any)/(:any)/(:any)/(:any)', 'ResultManager::examination_scores/$1/$2/$3/$4');
$routes->get('web/download_result_sample/(:any)/(:any)/(:any)/(:any)', 'Export::download_result_sample/$1/$2/$3/$4');
$routes->post('web/examination_result_export', 'ResultManager::examination_result_export');
$routes->get('web/assigned_course_list', 'ResultManager::assigned_course_list');
$routes->get('web/preview_request_claim', 'ResultManager::preview_request_claim');
$routes->post('web/submit_request_claim', 'ResultManager::submit_request_claim');
$routes->post('web/examination_approval_action', 'ResultManager::examination_approval_action');
$routes->get('web/assigned_list_courses', 'ResultManager::assigned_list_courses');
$routes->get('web/result_approval_stats', 'ResultManager::result_approval_stats');
