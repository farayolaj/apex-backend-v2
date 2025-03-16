<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// this routes handles the api for audit report
$routes->get('webtranx/finance_audit_report', 'AuditReport::processAuditReport');

$routes->get('webtranx/finance_outflow_payment', 'ApiStats::finance_outflow_payment');
$routes->get('webtranx/dashboard_finance_distrix', 'ApiStats::dashboard_finance_distrix');
$routes->get('webtranx/dashboard_payment_distrix', 'ApiStats::dashboard_payment_distrix');
$routes->get('webtranx/dashboard_finance_distrix_latest', 'ApiStats::dashboard_finance_distrix_latest');
$routes->get('webtranx/dashboard_transaction_annual_summary', 'ApiStats::dashboard_transaction_annual_summary');
$routes->get('webtranx/dashboard_transaction_summary_year', 'ApiStats::dashboard_transaction_summary_year');
$routes->get('webtranx/dashboard_transaction_summary_month', 'ApiStats::dashboard_transaction_summary_month');
$routes->get('webtranx/dashboard_transaction_summary_day', 'ApiStats::dashboard_transaction_summary_day');
$routes->get('webtranx/dashboard_transaction_summary_per_month', 'ApiStats::dashboard_transaction_summary_per_month');
$routes->get('webtranx/dashboard_latest_transaction', 'ApiStats::dashboard_latest_transaction');
$routes->get('webtranx/dashboard_latest_transaction_debit', 'ApiStats::dashboard_latest_transaction_debit');
$routes->get('webtranx/dashboard_latest_finance_transaction', 'ApiStats::dashboard_latest_finance_transaction');
$routes->get('webtranx/dashboard_finance_current_session', 'ApiStats::dashboard_finance_current_session');

$routes->get('webtranx/course_predictive_analysis', 'ResultManager::course_predictive_analysis');

// this is for finance routes
$routes->post('webtranx/authenticate', 'Auth::financeAuth', ['filter' => 'apiValidation:web-finance']);
$routes->group('webtranx', ['filter' => 'apiValidation:web-finance'], function ($routes) {
    $routes->add('(:any)', 'Api::financeapi/$1');
    $routes->add('(:any)/(:any)', 'Api::financeapi/$1/$2');
    $routes->add('(:any)/(:any)/(:any)', 'Api::financeapi/$1/$2/$3');
    $routes->add('(:any)/(:any)/(:any)/(:any)', 'Api::financeapi/$1/$2/$3/$4');
});
