<?php

namespace App\Enums;

enum CacheEnum: string
{
    case SESSION_WITH_RESULT = 'sessions_with_result';
    case STUDENT_ENROLLMENT = "student_enrollment";
    case STUDENT_STATS = "student_stats";
    case STUDENT_CONFIG = "student_config";
    case STUDENT_PRELOAD_LISTING = "student_course_preload";
    case STUDENT_COURSE_SEARCH = "student_course_search";
}
