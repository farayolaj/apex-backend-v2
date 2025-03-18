<?php

namespace App\Libraries;

/**
 * Class RouteURI
 *
 * This class contains a list of route URIs that are exposed to finance outflow.
 */
class RouteURI
{
    /**
     * List of finance-related route URIs.
     */
    public const FINANCE_STATS = [
        'finance_outflow_payment',
        'dashboard_finance_distrix_latest',
        'dashboard_finance_distrix',
        'dashboard_payment_distrix',
        'dashboard_transaction_annual_summary',
        'dashboard_transaction_summary_year',
        'dashboard_transaction_summary_month',
        'dashboard_transaction_summary_day',
        'dashboard_transaction_summary_per_month',
        'dashboard_latest_transaction',
        'dashboard_latest_transaction_debit',
        'dashboard_latest_finance_transaction',
        'dashboard_finance_current_session',
        'course_predictive_analysis',

        'export_transfer_journal',
        'export_analysis_expenditures',
        'export_cash_advance',
        'export_cash_advance_cleared',
        'export_cash_advance_uncleared',
        'export_expenses_more_than_500k',
        'export_registered_student',
        'export_acceptance_fee_journal',
    ];
}