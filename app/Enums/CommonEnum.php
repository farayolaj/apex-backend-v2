<?php

namespace App\Enums;

enum CommonEnum: string
{
    case O_LEVEL = 'O\' Level';

    case O_LEVEL_PUTME = 'O\' Level Putme';

    case DIRECT_ENTRY = 'Direct Entry';

    case FAST_TRACK = 'Fast Track';

    case APPLICANT = 'app';

    case APPLICANT_PUTME = 'apu';

    case BULK_PRINT_STUDENT_COVER = 'bulk_print_student_cover';
}
