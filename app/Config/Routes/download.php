<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// this is strictly for downloading files
$routes->group('v1/web/', [
    'namespace' => 'App\Controllers\Admin\V1'
], function ($routes) {

    $routes->get('web/download_document_complete', 'Download::export_document_complete');
    $routes->get('download/direct_link', 'Download::directDownloadLink');
    $routes->get('download/direct_link_logs', 'Download::directDownloadLinkLogs');
    $routes->get('download/direct_link_passport', 'Download::directDownloadLinkPassport');

    $routes->options('(:segment)', static function () {});
    $routes->options('(:segment)/(:segment)', static function () {});
});
