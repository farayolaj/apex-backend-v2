<?php

namespace App\Enums;

enum OutflowStatusEnum: string
{
    case PENDING_START = 'PENDING_START'; // means it's a pending tranx and is the default status

    case SUCCESSFUL = 'CREDITED'; // means it's successful

    case DEBITED = 'DEBITED'; // means it a debit transaction and doesn't likely to occur often

    case FAILED = 'FAILED'; // means it failed

    case REVERSED = 'REVERSED'; // means it's been reversed

    case PENDING_CREDIT = 'PENDING_CREDIT'; // means it's being processed

    case PENDING_DEBIT = 'PENDING_DEBIT';
}
