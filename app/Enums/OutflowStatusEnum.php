<?php

namespace App\Enums;

enum OutflowStatusEnum: string
{
    public const PENDING_START = 'PENDING_START'; // means it's a pending tranx and is the default status

    public const SUCCESSFUL = 'CREDITED'; // means it's successful

    public const DEBITED = 'DEBITED'; // means it a debit transaction and doesn't likely to occur often

    public const FAILED = 'FAILED'; // means it failed

    public const REVERSED = 'REVERSED'; // means it's been reversed

    public const PENDING_CREDIT = 'PENDING_CREDIT'; // means it's being processed

    public const PENDING_DEBIT = 'PENDING_DEBIT';
}
