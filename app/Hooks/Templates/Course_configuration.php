<?php
namespace App\Hooks\Templates;

use App\Hooks\Contracts\Template;

/**
 * CSV template for Course_configuration uploads.
 * - columns(): header names (order shown will be exported in HTML/CSV)
 * - sampleRows(): at least one sample row aligned to columns()
 * - filenamePrefix(): used by your download helper
 */
final class Course_configuration implements Template
{
    /** @return string[] */
    public static function columns(): array
    {
        return [
            'programme',
            'semester',
            'level',
            'entry_mode',
            'min_unit',
            'max_unit'
        ];
    }

    /** @return array<int,array<string,string>> */
    public static function sampleRows(): array
    {
        return [
            [
                'programme' => 'BACHELOR OF ARTS (ENGLISH)',
                'semester' => '(first or first semester) or (second or second semester)',
                'level' => '100',
                'entry_mode' => 'O\' Level | Direct Entry | Fast Track',
                'min_unit' => '12',
                'max_unit' => '30'
            ],
        ];
    }

    public static function filenamePrefix(): string
    {
        return "course-configuration_upload_template";
    }
}
