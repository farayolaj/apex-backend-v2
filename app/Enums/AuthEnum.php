<?php

namespace App\Enums;

enum AuthEnum: string
{
    case ADMIN = 'admin';

    case FINANCE_OUTFLOW = 'web-finance';

    case STUDENT = 'student';

    case CONTRACTOR = 'contractor';

    case APEX = 'apex';

    case APPLICANT = 'applicant';
}
