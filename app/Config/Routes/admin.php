<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->post('web/authenticate', 'Auth::login', ['filter' => 'apiValidation:admin']);
$routes->post('web/logout', 'Auth::logout', ['filter' => 'apiValidation:admin']);

$routes->group('web', ['filter' => 'apiValidation:admin'], function ($routes) {
    $routes->add('(:any)', 'Api::webApi/$1');
    $routes->add('(:any)/(:any)', 'Api::webApi/$1/$2');
    $routes->add('(:any)/(:any)', 'Api::webApi/$1/$2/$3');
});