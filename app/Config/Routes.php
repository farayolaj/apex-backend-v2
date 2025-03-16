<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

/**
 * DISCLAIMER: Do not change the order of this routes file
 * since each route file is loaded by precedence, top-down
 */

// import all the routes from Routes folder only
require_once __DIR__ . '/Routes/frontend.php';
require_once __DIR__ . '/Routes/misc.php';
require_once __DIR__ . '/Routes/stats.php';
require_once __DIR__ . '/Routes/result_manager.php';
require_once __DIR__ . '/Routes/download.php';
require_once __DIR__ . '/Routes/applicant.php';
require_once __DIR__ . '/Routes/export.php';
require_once __DIR__ . '/Routes/import.php';

require_once __DIR__ . '/Routes/admin.php'; // this handle the web version
require_once __DIR__ . '/Routes/finance.php'; // this handle the payment[finance] outflow
require_once __DIR__ . '/Routes/api.php'; // this handle the student portal

//require_once __DIR__ . '/Routes/web.php';



