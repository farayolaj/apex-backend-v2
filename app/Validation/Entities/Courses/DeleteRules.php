<?php
namespace App\Validation\Entities\Courses;

use App\Exceptions\ValidationFailedException;
use App\Validation\Support\Contracts\RulesProvider;

final class DeleteRules implements RulesProvider
{
    public static function authorize(array $data, array $ctx): bool
    {
        return permissionAuthorize('course_delete');
    }
    public static function denyMessage(): string { return 'You do not have permission to delete courses.'; }

    public static function precheck(array $data): void
    {
        // optional: ensure row exists
        if (!empty($data['id'])) {
            $entity = $data['__entity__'];
            $exists = (bool) db_connect()->table($entity)
                ->select('id')
                ->where('id', (int)$data['id'])
                ->get()->getFirstRow();
            if (!$exists) {
                throw new ValidationFailedException("Course not found");
            }
        }
    }

    public static function rules(): array
    {
        return ['id' => 'required|integer'];
    }

    public static function messages(): array
    {
        return ['id' => ['required' => 'A valid ID is required for delete.']];
    }
}
