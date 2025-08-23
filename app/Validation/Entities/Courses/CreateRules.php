<?php

namespace App\Validation\Entities\Courses;

use App\Exceptions\ValidationFailedException;
use App\Validation\Support\Contracts\RulesProvider;

/**
 * All-in-one:
 *  - authorize(): gate the action early (return bool)
 *  - precheck(): DB lookups for nicer messages (e.g., unique with details)
 *  - rules()/messages(): normal CI4 rules
 */
final class CreateRules implements RulesProvider
{

    /**
     * Optional: called first; return false to stop with 403
     */
    public static function authorize(array $data, array $ctx): bool
    {
        return permissionAuthorize('course_create');
    }

    /**
     * Optional: custom DB checks before CI rules (throw for custom messages)
     */
    public static function precheck(array $data): void
    {
        if (!empty($data['code'])) {
            $db  = db_connect();
            $row = $db->table('courses')
                ->select('id, title')
                ->where('code', (string) $data['code'])
                ->get()
                ->getRowArray();

            if ($row) {
                $code  = (string) $data['code'];
                $id    = (int) $row['id'];
                $title = (string) ($row['title'] ?? '');
                throw new ValidationFailedException(
                    "Course '{$code}' already exists."
                );
            }
        }
    }

    public static function rules(): array
    {
        return [
            'code'             => 'required|min_length[2]|max_length[7]',
            'title'            => 'required',
            'department_id'    => 'required|integer',
            'type'             => 'required|in_list[cbt,written]',
            'active'           => 'permit_empty|in_list[0,1]',
            'description'      => 'permit_empty',
            'course_guide_url' => 'permit_empty',
        ];
    }

    public static function messages(): array
    {
        return [
            'code'  => [
                'required' => 'Course code is required.',
                'min_length' => 'Course code must be at least 2 characters long.',
                'is_unique' => 'Course code already exists.'
            ],
            'title' => ['required' => 'Title is required.'],
        ];
    }

    /**
     * Optional: customize reason 403 when authorize() returns false
     */
    public static function denyMessage(): string
    {
        return 'You do not have permission to create courses.';
    }
}