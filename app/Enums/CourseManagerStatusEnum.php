<?php

namespace App\Enums;

enum CourseManagerStatusEnum: string
{
    case WAIVER = 'waiver';

    case APPROVED = 'approved';

    case ACCEPTED = '1';

    case UNQUALIFIED = 'unqualified';
}
