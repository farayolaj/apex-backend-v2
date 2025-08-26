<?php
namespace App\Validation\Entities\Course_mapping;

use App\Exceptions\ValidationFailedException;
use App\Validation\Support\Contracts\RulesProvider;
/**
 * Validation rules for Course_mapping (delete).
 * Methods are static to work with your ValidationAuto runner.
 * Keep authorize fast (no heavy I/O); use precheck for DB lookups.
 */
final class DeleteRules implements RulesProvider
{
    /** Gate the action using roles/permissions/tenant context from $ctx. */
    public static function authorize(array $data, array $ctx): bool
    {
        return permissionAuthorize($ctx['__authorize__'] ?? 'course_delete');
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
    public static function precheck(array $data): void{}

    /** CodeIgniter rules array. Keep it minimal and explicit. */
    public static function rules(): array
    {
        return ['id' => 'required|integer'];
    }

    /** Optional custom messages per rule. */
    public static function messages(): array
    {
        return ['id' => ['required' => 'A valid ID is required for delete.']];
    }
}
