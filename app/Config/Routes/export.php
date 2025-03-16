<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// overriding web apis routes for export since higher routes takes precedence
$routes->get('web/export_finance_transaction', 'Export::exportFinanceTransaction');
$routes->get('web/export_sundry_defer_finance', 'Export::exportSundryDeferTransaction');
$routes->post('web/export_student_lists', 'Export::export_student_lists');
$routes->get('web/export_pwd_student', 'Export::exportPwdStudent');
$routes->get('web/export_student_orientation', 'Export::exportStudentOrientation');
$routes->get('web/export_student_telco', 'Export::exportStudentTelco');
$routes->post('web/export_course_registration', 'Export::export_course_registration');
$routes->post('web/export_applicants', 'Export::export_applicants');

$routes->get('web/bulk_passport_download', 'Export::download_student_passport');
$routes->post('web/export_remote_enrolment', 'Export::exportRemoteEnrolment');
$routes->get('web/export_practicum_form', 'Export::exportPracticumForm');
$routes->get('web/export_student_custom_email', 'Export::exportCustomStudentEmail');

// this routes handles the export on finance outflow
$routes->get('webtranx/export_transfer_journal','Export::exportTransferJournal');
$routes->get('webtranx/export_analysis_expenditures', 'Export::exportAnalysisExpenditure');
$routes->get('webtranx/export_cash_advance', 'Export::exportCashAdvance');
$routes->get('webtranx/export_cash_advance_cleared', 'Export::exportCashAdvanceCleared');
$routes->get('webtranx/export_cash_advance_uncleared', 'Export::exportCashAdvanceUncleared');
$routes->get('webtranx/export_expenses_more_than_500k', 'Export::exportExpensesMoreThan500k');
$routes->get('webtranx/export_registered_student', 'Export::exportRegisteredStudent');
$routes->get('webtranx/export_acceptance_fee_journal', 'Export::exportAcceptanceFeeJournal');

