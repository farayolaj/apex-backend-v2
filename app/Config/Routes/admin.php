<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('web', ['filter' => 'apiValidation:admin'], static function (RouteCollection $routes): void {
    $routes->post('authenticate', 'Auth::web');
    $routes->post('logout', 'Auth::logout');

    $routes->options('(:any)', static function () {});
});

$routes->group('web', ['filter' => ['apiValidation:admin']], function ($routes) {
    $routes->add('(:any)', 'Api::webApi/$1');
    $routes->add('(:any)/(:any)', 'Api::webApi/$1/$2');
    $routes->add('(:any)/(:any)/(:any)', 'Api::webApi/$1/$2/$3');

    $routes->options('(:any)', static function () {});
    $routes->options('(:any)/(:any)', static function () {});
    $routes->options('(:any)/(:any)/(:any)', static function () {});
});

$routes->post('department/authenticate', 'Auth::web', ['filter' =>  ['cors', 'apiValidation:admin']]);
$routes->group('department', ['filter' => ['cors', 'apiValidation:admin']], function ($routes) {
    $routes->add('(:any)', 'Api::departmentapi/$1');
    $routes->add('(:any)/(:any)', 'Api::departmentapi/$1/$2');
    $routes->add('(:any)/(:any)/(:any)', 'Api::departmentapi/$1/$2/$3');

    $routes->options('(:any)', static function () {});
    $routes->options('(:any)/(:any)', static function () {});
    $routes->options('(:any)/(:any)/(:any)', static function () {});
});
