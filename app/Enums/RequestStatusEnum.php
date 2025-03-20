<?php

namespace App\Enums;

enum RequestStatusEnum: string
{
    case APPROVED = 'approved';

    case PENDING = 'pending';

    case REJECTED = 'rejected';

    case PAID = 'paid';

    case ADVANCE_RETURN_PENDING = 'advance_return_pending';

    case ADVANCE_RETURN_CONFIRMED = 'advance_return_confirmed';

    case STAGE_PAYMENT_VOUCHER = 'payment_voucher';

    case STAGE_AUDITOR = 'auditor';

    case STAGE_MANDATE = 'mandate';

    case STAGE_PAYMENT = 'payment';

    case STAGE_RETIRE_SALARY_ADVANCE = 'retire_salary_advance';
}
