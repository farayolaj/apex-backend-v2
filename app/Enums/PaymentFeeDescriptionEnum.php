<?php

namespace App\Enums;

enum PaymentFeeDescriptionEnum: int
{
    case ACCEPTANCE_FEE = 16;

    case SCH_FEE_FIRST = 1;

    case SCH_FEE_SECOND = 2;

    case OUTSTANDING_22 = 65;

    case TOPUP_FEE_22 = 68;

    case TOPUP_FEE_21 = 78;

    case PART_FIRST_SCH_FEE = 75;

    case PART_SECOND_SCH_FEE = 76;

    case LAGOS_CENTRE_SECOND_SEM = 57;

    case LAGOS_CENTRE_FIRST_ONLY_SEM = 6;

    case OUTSTANDING_PART_SESSION = 23;

    case VERIFICATION_ONE = 25;

    case VERIFICATION_TWO = 48;
}
