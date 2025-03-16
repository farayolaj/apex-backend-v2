<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// overriding routes for printing since higher routes takes precedence
// this handles student portal routes for printing
$routes->get('api/courseregistrationprint', 'Frontend::courseregistrationprint');
$routes->get('api/coursesummaryprint', 'Frontend::coursesummaryprint');
$routes->get('api/examdocket/(:any)/(:any)/(:any)', 'Frontend::examdocket/$1/$2/$3');
$routes->get('api/documentprint/(:any)/(:any)/(:any)', 'Frontend::document/$1/$2/$3');
$routes->get('api/admission_letter/(:any)/(:any)', 'Frontend::admissionLetter/$1/$2');
$routes->get('api/notification_admission_letter/(:any)/(:any)', 'Frontend::notificationAdmissionLetter/$1');
$routes->get('api/mainreceipt', 'Frontend::receipt_main');

// this handles apex routes for printing
$routes->get('web/non_student_receipt', 'Frontend::receipt_non_student');
$routes->get('web/bulk_check_transaction', 'Frontend::bulkTransaction');
$routes->get('web/courseregistrationAll/(:any)/(:any)/(:any)', 'Frontend::courseregistrationprintAll/$1/$2/$3');
$routes->get('web/student_verification_print/(:any)', 'Frontend::studentApplicationForm/$1');
$routes->get('web/student_application_cover_print/(:any)', 'Frontend::studentApplicationCover/$1');
$routes->get('web/student_application_inner_print/(:any)', 'Frontend::studentApplicationInner/$1');
$routes->get('web/student_application_inner2_print/(:any)', 'Frontend::studentApplicationInner2/$1');
$routes->get('web/student_application_document_print/(:any)', 'Frontend::studentApplicationDocument/$1');

// this was a diversion for events uploads
$routes->post('api/uploadevents', 'Frontend::upload_events');
$routes->post('api/uploadstudentevent', 'Frontend::upload_student_event');
$routes->post('api/uploadstudentwebinarschedule', 'Frontend::upload_student_webinar_schedule');

