<?php

namespace App\Enums;

enum ReportEnum: string
{
    case TRANSFER_JOURNAL = 'transfer_journal';

    case ANALYSIS_EXPENDITURES = 'analysis_expenditures';

    case CASH_ADVANCE = 'cash_advance';

    case CASH_ADVANCE_CLEARED = 'cash_advance_cleared';

    case CASH_ADVANCE_UNCLEARED = 'cash_advance_uncleared';

    case EXPENSES_MORE_THAN_500K = 'expenses_more_than_500K';

    case REGISTERED_STUDENT = 'registered_student';

    case ACCEPTANCE_FEE_JOURNAL = 'acceptance_fee_journal';
}
