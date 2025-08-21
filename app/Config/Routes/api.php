<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('v1/api/', [
    'filter' => ['apiValidation:student'],
    'namespace' => 'App\Controllers\Student\v1'
], function ($routes) {
    $routes->get('courses/(:num)/webinars', 'Webinars::listWebinars/$1');
    $routes->get('webinars/(:num)', 'Webinars::getWebinar/$1');
});

// this is the api for finance sync between server[UI Admission]
//$routes->get('api/integrations/finance/v1/transaction', 'FinanceIntegration::getTransactionData');

$routes->post('api/authenticate', 'Auth::student', ['filter' => 'apiValidation:student']);
$routes->post('api/validate_student', 'Auth::validate_student', ['filter' => 'apiValidation:student']);
$routes->get('api/baseUrl', 'AjaxData::baseUrl');
$routes->post('api/logout', 'Auth::logout', ['filter' => 'apiValidation:student']);
$routes->group('', ['filter' => 'cors'], static function (RouteCollection $routes): void {
    $routes->options('api/(:any)', static function () {});
});

$routes->group('api', ['filter' => ['cors', 'apiValidation:student']], function ($routes) {
    $routes->add('(:any)', 'Api::frontApi/$1');
    $routes->add('(:any)/(:any)', 'Api::frontApi/$1/$2');
    $routes->add('(:any)/(:any)/(:any)', 'Api::frontApi/$1/$2/$3');

    $routes->options('(:any)', static function () {});
    $routes->options('(:any)/(:any)', static function () {});
    $routes->options('(:any)/(:any)/(:any)', static function () {});
});

// this is for apex mobile API
$routes->post('Apex_mobile/authenticate', 'Auth::apexAuth', ['filter' => 'apiValidation:apex']);
$routes->group('', ['filter' => 'cors'], static function (RouteCollection $routes): void {
    $routes->options('Apex_mobile/(:any)', static function () {});
});
$routes->group('Apex_mobile', ['filter' => ['cors', 'apiValidation:apex']], function ($routes) {
    $routes->add('(:any)', 'Api::apexapi/$1');
    $routes->add('(:any)/(:any)', 'Api::apexapi/$1/$2');
    $routes->add('(:any)/(:any)/(:any)', 'Api::apexapi/$1/$2/$3');

    $routes->options('(:any)', static function () {});
    $routes->options('(:any)/(:any)', static function () {});
    $routes->options('(:any)/(:any)/(:any)', static function () {});
});
