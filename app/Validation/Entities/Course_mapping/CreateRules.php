<?php
namespace App\Validation\Entities\Course_mapping;

use App\Validation\Support\Contracts\RulesProvider;
/**
 * Validation rules for Course_mapping (create).
 * Methods are static to work with your ValidationAuto runner.
 * Keep authorize fast (no heavy I/O); use precheck for DB lookups.
 */
final class CreateRules implements RulesProvider
{
    /** Gate the action using roles/permissions/tenant context from $ctx. */
    public static function authorize(array $data, array $ctx): bool
    {
        return permissionAuthorize($ctx['__authorize__'] ?? 'course_create');
    }

    /** Message returned if authorize() returns false. */
    public static function denyMessage(): string
    {
        return 'You are not allowed to perform this action.';
    }

    /**
     * Optional: perform lightweight DB checks; throw your ApiValidationException::field(...)
     * to produce friendly per-field messages.
     */
    public static function precheck(array $data): void
    {
        // e.g., ensure foreign keys exist, or ownership checks
        // throw new \App\Exceptions\ValidationFailedException('Reason');
    }

    /** CodeIgniter rules array. Keep it minimal and explicit. */
    public static function rules(): array
    {
        return [
            'course_id'     => ['label' => 'course',        'rules' => 'required'],
            'programme_id'  => ['label' => 'programme',     'rules' => 'required'],
            'semester'      => ['label' => 'semester',      'rules' => 'required|in_list[1,2]'],
            'course_unit'   => ['label' => 'course unit',   'rules' => 'required'],
            'course_status' => ['label' => 'course status', 'rules' => 'required'],
            'level'         => ['label' => 'level',         'rules' => 'required'],
            'mode_of_entry' => ['label' => 'mode of entry', 'rules' => 'required'],
            'pass_score'    => ['label' => 'pass score',    'rules' => 'required|is_natural_no_zero|less_than_equal_to[100]'],
        ];
    }

    /** Optional custom messages per rule. */
    public static function messages(): array
    {
        return [
            // 'code.required' => 'Code is required.',
        ];
    }
}
