<?php

namespace App\Enums;

enum BookstoreStatusEnum: string
{
    case PENDING   = 'pending';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
}
