<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->group('web/v1/courses', ['filter' => ['cors', 'apiValidation:admin']], function ($routes) {
    $routes->add('(:any)', '\App\Controllers\Admin\v1\Courses::index/$1');

    $routes->options('(:any)', static function () {});
    $routes->options('(:any)/(:any)', static function () {});
    $routes->options('(:any)/(:any)/(:any)', static function () {});
});
