<?php

namespace App\Enums;

enum FeeDescriptionCodeEnum: string
{
    case OUTSTANDING_FEE = 'OUT';

    case SCHOOL_FEE = 'SCF';

    case VERIFICATION_ONE = 'VEF-One';

    case VERIFICATION_TWO = 'VEF-Two';

    case TOPUP_FEE = 'TU';

    case TOPUP_FEE_BAL = 'TUB';
}
