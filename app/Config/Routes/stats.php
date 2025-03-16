<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// this handle the statistics part of the web
$routes->get('web/student_pwd_distribution', 'ApiStats::student_pwd_distribution');
$routes->get('web/dasboard_top_card', 'ApiStats::dasboard_top_card');
$routes->get('web/dasboard_student_distribution', 'ApiStats::dasboard_student_distribution');
$routes->get('web/dasboard_enrollment_attrition', 'ApiStats::dasboard_enrollment_attrition');
$routes->get('web/dasboard_level_chart', 'ApiStats::dasboard_level_chart');
$routes->get('web/dasboard_level_chart_new', 'ApiStats::dasboard_level_chart_new');
$routes->get('web/dashboard_transaction_distrix', 'ApiStats::dashboard_transaction_distrix');
$routes->get('web/dashboard_transaction_distrix_latest', 'ApiStats::dashboard_transaction_distrix_latest');
$routes->get('web/dashboard_latest_transaction', 'ApiStats::dashboard_latest_transaction');
$routes->get('web/dashboard_payment_distrix', 'ApiStats::dashboard_payment_distrix');
$routes->get('web/dashboard_payment_distrix_latest', 'ApiStats::dashboard_payment_distrix_latest');
$routes->get('web/dashboard_finance_distrix', 'ApiStats::dashboard_finance_distrix');
$routes->get('web/dashboard_transaction_annual_summary', 'ApiStats::dashboard_transaction_annual_summary');
$routes->get('web/dashboard_transaction_summary_year', 'ApiStats::dashboard_transaction_summary_year');
$routes->get('web/dashboard_transaction_summary_month', 'ApiStats::dashboard_transaction_summary_month');
$routes->get('web/dashboard_transaction_summary_day', 'ApiStats::dashboard_transaction_summary_day');
$routes->get('web/dashboard_transaction_summary_per_month', 'ApiStats::dashboard_transaction_summary_per_month');
$routes->get('web/dashboard_student_entry_distribtion', 'ApiStats::dashboard_student_entry_distribtion');
$routes->get('web/dashboard_student_level_entry_distribtion', 'ApiStats::dashboard_student_level_entry_distribtion');
$routes->get('web/dashboard_student_level_session_distribtion', 'ApiStats::dashboard_student_level_session_distribtion');
$routes->get('web/dashboard_department_level_distrix', 'ApiStats::dashboard_department_level_distrix');
$routes->get('web/application_report', 'ApiStats::application_report');
$routes->get('web/application_entrymode', 'ApiStats::application_entrymode');
$routes->get('web/application_gender', 'ApiStats::application_gender');
$routes->get('web/application_age', 'ApiStats::application_age');
$routes->get('web/applicant_programme_status', 'ApiStats::applicant_programme_status');
$routes->get('web/dashboard_applicant_clustered', 'ApiStats::dashboard_applicant_clustered');
$routes->get('web/dashboard_students_result', 'ApiStats::dashboard_students_result');
$routes->get('web/dashboard_course_result', 'ApiStats::dashboard_course_result');
$routes->get('web/dashboard_course_without_result', 'ApiStats::dashboard_course_without_result');
$routes->get('web/course_enrollment_stats', 'ApiStats::course_enrollment_stats');
$routes->get('web/course_dashboard', 'ApiStats::course_dashboard');
$routes->get('web/sundry_rsos_gender_distribution', 'ApiStats::sundry_rsos_gender_distribution');
$routes->get('web/sundry_rsos_age_distribution', 'ApiStats::sundry_rsos_age_distribution');
$routes->get('web/finance_custom_card', 'ApiStats::finance_custom_card');
$routes->get('web/finance_transaction_custom_graph', 'ApiStats::finance_transaction_custom_graph');
$routes->get('web/course_gender_distribution', 'ApiStats::course_gender_distribution');
$routes->get('web/course_age_distribution', 'ApiStats::course_age_distribution');
$routes->get('web/course_pwd_distribution', 'ApiStats::course_pwd_distribution');
$routes->get('web/course_score_analysis', 'ApiStats::course_score_analysis');
$routes->get('web/course_score_distribution', 'ApiStats::course_score_distribution');
$routes->get('web/course_avg_score_trend', 'ApiStats::course_avg_score_trend');
$routes->get('web/course_enrollment_trend', 'ApiStats::course_enrollment_trend');
$routes->get('web/student_gender_distrix', 'ApiStats::student_gender_distrix');
$routes->get('web/student_orientation_stats', 'ApiStats::student_orientation_stats');

// I don't think this is in used again, moved to finance routes
$routes->get('web/course_predictive_analysis', 'ResultManager::course_predictive_analysis');

