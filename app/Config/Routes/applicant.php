<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// this is the api for mode of study sync between server[UI Admission]
$routes->group('api/v1', ['filter' => 'apiValidation:student'], function ($routes) {
    $routes->post('transfer_applicant', 'ApplicantMigration::createApplicant');
    $routes->get('applicant_details', 'ApplicantMigration::getApplicantDetails');
    $routes->get('applicant_admission_status', 'ApplicantMigration::getApplicantAdmissionStatus');
    $routes->post('applicant_update_olevel', 'ApplicantMigration::applicantOlevelUpdate');
    $routes->post('applicant_update_biodata', 'ApplicantMigration::applicantBiodataUpdate');
    $routes->get('applicant_all_programme', 'ApplicantMigration::getAllProgramme');
});
