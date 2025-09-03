<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// $routes->post('web/bulk_voters_upload', 'Import::bulkStudentImport', ['filter' => 'apiValidation:admin']);

// this is test controller

$routes->post('v1/web/tester','Tester::test', ['filter' => ['cors', 'apiValidation:admin']]);

// this routes is for reporting controller
$routes->post('web/senate_export_html/(:any)/(:any)/(:any)/(:any)/(:any)', 'Report::senateResponseHtml/$1/$2/$3/$4/$5');
$routes->post('web/senate_gradesheet_export_html/(:any)/(:any)/(:any)/(:any)', 'Report::senateGradeSheetResponseHtml/$1/$2/$3/$4');
$routes->post('web/senate_cover_export_html/(:any)/(:any)/(:any)/(:any)', 'Report::senateCoverResponseHtml/$1/$2/$3/$4');
$routes->post('web/ui_result_course_export_html/(:any)/(:any)/(:any)/(:any)', 'Report::resultCourseResponseHtml/$1/$2/$3/$4');

// not sure if this is in used again
$routes->post('web/claims_request_html/(:any)/(:any)', 'Report::claimRequestResponseHtml/$1/$2');