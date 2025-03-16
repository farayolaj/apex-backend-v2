<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// overriding web apis routes for import since higher routes takes precedence
$routes->post('web/bulk_student_upload_action', 'Import::bulkStudentUploadAction');
$routes->post('web/bulk_passport_upload', 'Import::bulkPassportUpload');
$routes->post('web/bulk_applicant_passport_upload', 'Import::bulkApplicantPassportUpload');
$routes->post('web/bulk_applicant_post_utme', 'Import::bulkApplicantPostUtme');
$routes->post('web/bulk_practicum_form', 'Import::bulkPracticumForm');
$routes->post('web/bulk_courses_type', 'Import::bulkCourseType');
$routes->post('web/bulk_update_student_email', 'Import::bulkUpdateStudentEmail');
$routes->post('web/bulk_update_student_matric', 'Import::bulkUpdateStudentMatric');
$routes->post('web/bulk_assign_card_pins', 'Import::bulkAssignCardPins');

$routes->post('web/bulk_payment_code_upload', 'OneTime::bulkUpdatePaymentCode');
$routes->post('web/bulk_outstanding_student', 'OneTime::bulkOutstandingStudent');
$routes->post('web/bulk_topup_student', 'OneTime::bulkTopupStudent');
$routes->post('web/bulk_copy_student_passport', 'OneTime::bulkCopyStudentPassport');
$routes->post('web/bulk_transaction_ref', 'OneTime::bulkupdateTransactionref');
$routes->post('web/bulk_distribute_user', 'OneTime::bulkUserData');
$routes->post('web/bulk_update_staff_dob', 'OneTime::bulkUpdateStaffDOb');
$routes->post('web/bulk_deduction_amount', 'OneTime::bulkUpdateDeductionAmount');
$routes->post('web/bulk_transform_phone', 'OneTime::bulkTransformPhone');
$routes->post('web/bulk_move_student_session', 'OneTime::bulkMoveStudentToAnotherSession');
$routes->post('web/bulk_duplicate_payments', 'OneTime::bulkDuplicatePayments');
$routes->get('web/bulk_clear_student_outstanding', 'OneTime::bulkClearOutstanding');
$routes->post('web/bulk_payment_prerequisites_equiv', 'OneTime::buildPaymentPrerequisiteEquiv');
