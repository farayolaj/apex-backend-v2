<?php

namespace App\Enums;

enum RequestTypeEnum: string
{
    case SALARY_ADVANCE = 'SAD';

    case IMPREST = 'IMP';

    case CLAIM = 'CLA';

    case HONORARIUM = 'HON';

    case RETIRE_SALARY_ADVANCE = 'RSA';

    case INVOICE = 'INV';

    case DIEM = 'DIE';
}
