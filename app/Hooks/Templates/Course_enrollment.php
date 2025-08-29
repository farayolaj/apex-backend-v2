<?php
namespace App\Hooks\Templates;

use App\Hooks\Contracts\Template;

/**
 * CSV template for Course_enrollment uploads.
 * - columns(): header names (order shown will be exported in HTML/CSV)
 * - sampleRows(): at least one sample row aligned to columns()
 * - filenamePrefix(): used by your download helper
 */
final class Course_enrollment implements Template
{
    /** @return string[] */
    public static function columns(): array
    {
        return [
            'matric_number',
            'course_code',
            'course_unit',
            'course_status',
            'session',
            'course_semester',
            'level'
        ];
    }

    /** @return array<int,array<string,string>> */
    public static function sampleRows(): array
    {
        return [
            [
                'matric_number' => 'E012345',
                'course_code' => 'BUS101',
                'course_unit' => '3',
                'course_status' => 'C',
                'session' => '23',
                'course_semester' => 'First or Second',
                'level' => ''
            ],
        ];
    }

    public static function filenamePrefix(): string
    {
        return "course_enrollment_upload_template";
    }
}
