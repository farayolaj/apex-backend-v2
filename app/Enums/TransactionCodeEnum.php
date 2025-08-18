<?php

namespace App\Enums;

enum TransactionCodeEnum: string
{
    case PENDING = '021';

    case SUCCESS = '00';

    case SUCCESS_II = '01';
}
