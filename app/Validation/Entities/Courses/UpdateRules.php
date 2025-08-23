<?php

namespace App\Validation\Entities\Courses;

use App\Exceptions\ValidationFailedException;
use App\Validation\Support\Contracts\RulesProvider;

final class UpdateRules implements RulesProvider
{
    public static function authorize(array $data, array $ctx): bool
    {
        return permissionAuthorize('course_edit');
    }
    public static function denyMessage(): string { return 'You do not have permission to update courses.'; }

    public static function precheck(array $data): void
    {
        if (!empty($data['code']) && !empty($data['id'])) {
            $row = db_connect()->table('courses')->select('id,title')
                ->where('code', (string)$data['code'])
                ->where('id !=', (int)$data['id'])
                ->get()->getRowArray();
            if ($row) {
                throw new ValidationFailedException(
                    "Course '{$data['code']}' already exists."
                );
            }
        }
    }

    public static function rules(): array
    {
        // On update, usually fields are optional; validate only if present
        return [
            'code'          => 'required|max_length[7]',
            'title'         => 'permit_empty|min_length[3]',
            'department_id' => 'permit_empty|integer',
            'active'        => 'permit_empty|in_list[0,1]',
            'type'          => 'permit_empty|required|in_list[cbt,written]',
            'description'   => 'permit_empty',
            'course_guide_url' => 'permit_empty',
        ];
    }

    public static function messages(): array
    {
        return [];
    }
}