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

    case PROF_RANK = 'prof_rank';

    case LECTURER_RANK = 'lecturer_rank';

    case APPLICANT_PUTME_ENTITY = "applicant_post_utme";

    case GES_BOOK = 'ges';

    case DLC_BOOK = 'dlc';

    case COURSE_PACK = 'course_pack';

    case EMAIL_BUILDER_APPLICANT = 'email_builder_applicant';

    case EMAIL_BUILDER_STUDENT = 'email_builder_student';

    case API_SERVICE_FINANCE = 'admon_finance';

    case API_SERVICE_RESULT = 'result';

    case ETUTOR = 'eTutor';
}
