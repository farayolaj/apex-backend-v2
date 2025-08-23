<?php
namespace App\Hooks\Templates;

use App\Hooks\Contracts\Template;

final class Courses implements Template
{
    public static function columns(): array
    {
        return [
            'course_code',
            'course_title',
            'course_description',
            'course_guide_url',
            'course_type',
            'department_code',
        ];
    }

    public static function sampleRows(): array
    {
        return [[
            'course_code' => 'BUS101',
            'course_title' => 'Business Intelligence',
            'course_description' => 'Business Intelligence',
            'course_guide_url' => 'https://example.com/path/to/file',
            'course_type' => 'cbt or written',
            'department_code' => 'ECO',
        ]];
    }

    public static function filenamePrefix(): string
    {
        return 'course_upload_template';
    }

}