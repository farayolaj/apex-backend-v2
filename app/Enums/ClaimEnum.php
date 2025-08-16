<?php

namespace App\Enums;

enum ClaimEnum: string
{
    case SCRIPT = 'exam_facilitation';

    case FACILITATION = 'physical_facilitation';

    case INTERACTION = 'online_facilitation';

    case EXAM_PAPER = 'written';

    case EXAM_CBT = 'cbt';

    case NON_EXAM = 'non-exam';
}
