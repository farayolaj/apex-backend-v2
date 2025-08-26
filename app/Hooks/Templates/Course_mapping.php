<?php
namespace App\Hooks\Templates;

use App\Hooks\Contracts\Template;

/**
 * CSV template for Course_mapping uploads.
 * - columns(): header names (order shown will be exported in HTML/CSV)
 * - sampleRows(): at least one sample row aligned to columns()
 * - filenamePrefix(): used by your download helper
 */
final class Course_mapping implements Template
{
    /** @return string[] */
    public static function columns(): array
    {
        return [
            'course_code',
            'programme',
            'semester',
            'course_unit',
            'course_status',
            'passing_score',
            'level',
            'entry_mode',
            'preselect'
        ];
    }

    /** @return array<int,array<string,string>> */
    public static function sampleRows(): array
    {
        return [
            [
                'course_code' => 'BUS101',
                'programme' => 'BACHELOR OF ARTS (ENGLISH)',
                'semester' => '(first or first semester) or (second or second semester)',
                'course_unit' => '3',
                'course_status' => 'C or E or R',
                'passing_score' => '30',
                'level' => '4',
                'entry_mode' => 'O\' Level',
                'preselect' => '0'
            ],
        ];
    }

    public static function filenamePrefix(): string
    {
        return "course_mapping_upload_template_";
    }
}
