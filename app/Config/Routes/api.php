<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->post('api/authenticate', 'Auth::voterLogin', ['filter' => 'apiValidation:voter']);
$routes->post('api/logout', 'Auth::logout', ['filter' => 'apiValidation:voter']);

$routes->group('api', ['filter' => 'apiValidation:voter'], function ($routes) {
    $routes->add('(:any)', 'Api::frontApi/$1');
    $routes->add('(:any)/(:any)', 'Api::frontApi/$1/$2');
    $routes->add('(:any)/(:any)', 'Api::frontApi/$1/$2/$3');
});
