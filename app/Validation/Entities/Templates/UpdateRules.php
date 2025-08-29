<?php
namespace App\Validation\Entities\Templates;

use App\Exceptions\ValidationFailedException;
use App\Validation\Support\Contracts\RulesProvider;
/**
 * Validation rules for Templates (update).
 * Methods are static to work with your ValidationAuto runner.
 * Keep authorize fast (no heavy I/O); use precheck for DB lookups.
 */
final class UpdateRules implements RulesProvider
{
    /** Gate the action using roles/permissions/tenant context from $ctx. */
    public static function authorize(array $data, array $ctx): bool
    {
        return permissionAuthorize($ctx['__authorize__'] ?? 'message_template_edit');
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
        $db = db_connect();
        $requestSession = $db->table('templates')->select('slug')
            ->where('id', $data['id'])->get()->getRow('slug');
        $uniqueSession = !$data['slug'] || $data['slug'] == $requestSession;
        if(!$uniqueSession){
            throw new ValidationFailedException('Template is already in use');
        }
    }

    /** CodeIgniter rules array. Keep it minimal and explicit. */
    public static function rules(): array
    {
        return [
            'id'        => ['label' => 'id', 'rules' => 'required'],
            'name'     => ['label' => 'name',        'rules' => 'required'],
            'slug'     => ['label' => 'slug',        'rules' => 'required|is_unique[templates.slug,id,{id}]'],
            'content' => ['label' => 'content',    'rules' => 'required'],
            'type' => ['label' => 'type',    'rules' => 'required'],
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
