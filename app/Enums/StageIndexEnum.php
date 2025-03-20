<?php

namespace App\Enums;

enum StageIndexEnum: int
{
    public const PAYMENT_VOUCHER = 1;

    public const MANDATE = 2;

    public const PAYMENT = 3;

    public const AUDITOR = 4;

    public const RETIRE_SALARY_ADVANCE = 5;

}
