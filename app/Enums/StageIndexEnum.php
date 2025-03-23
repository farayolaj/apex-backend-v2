<?php

namespace App\Enums;

enum StageIndexEnum: int
{
    case PAYMENT_VOUCHER = 1;

    case MANDATE = 2;

    case PAYMENT = 3;

    case AUDITOR = 4;

    case RETIRE_SALARY_ADVANCE = 5;

}
