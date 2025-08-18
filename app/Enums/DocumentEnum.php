<?php

namespace App\Enums;

enum DocumentEnum: string
{
    case ADMISSION_LETTER_22_23 = '2022-2023-admission-letter';

    case NOTIFICATION_ADMISSION_LETTER_22_23 = '2022-2023-notification-of-admission-letter';

    case PRACTICUM_LETTER = 'practicum-letter';

    case TEACHING_PRACTICE = 'teaching-practice-letter';

    case TEACHING_OBSERVATION = 'teaching-observation-letter';
}
