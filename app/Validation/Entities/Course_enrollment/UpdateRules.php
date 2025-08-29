<?php
namespace App\Validation\Entities\Course_enrollment;

use App\Validation\Support\Contracts\RulesProvider;
/**
 * Validation rules for Course_enrollment (update).
 * Methods are static to work with your ValidationAuto runner.
 * Keep authorize fast (no heavy I/O); use precheck for DB lookups.
 */
final class UpdateRules implements RulesProvider
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
            'course_unit' => 'permit_empty|required|numeric',
            'course_status' => 'permit_empty|required|string',
            'semester' => 'permit_empty|required|numeric',
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
