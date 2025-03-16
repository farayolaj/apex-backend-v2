<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->post('web/bulk_voters_upload', 'Import::bulkStudentImport', ['filter' => 'apiValidation:admin']);
$routes->post('web/bulk_voter_email', 'Import::sendBulkMail', ['filter' => 'apiValidation:admin']);
$routes->post('web/bulk_voter_password', 'Import::bulkStudentUpdatePassword', ['filter' => 'apiValidation:admin']);