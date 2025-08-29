<?php
namespace App\Hooks\Observers;

use App\Hooks\Contracts\Observer; 

/**
 * Observer hooks for Document_templates.
 * - $data is passed by reference on mutating hooks (beforeCreating/handleUploads/beforeUpdating).
 * - $extra is passed by value; contains context like $extra['auth'] (user) etc.
 * - Keep logic fast and deterministic; avoid heavy I/O where possible.
 */
final class Document_templates implements Observer
{
    // ---- INSERT FLOW ----
    public function beforeCreating(array &$data, array $extra): void {
        $data['date_added'] = date('Y-m-d H:i:s');
        $data['active'] = 1;

        if (($data['category'] ?? null) !== 'general') {
            $data['printable'] = '';
            $data['session']   = 0;
        }
        $data['content'] = base64_encode($data['content']);
    }
    public function afterCreated(int $id, array &$data, array $extra): void {
        logAction('create_document_template', $extra['current_user']->user_login, null, null, json_encode($data));
    }
    
    public function handleUploads(array &$data, array $files, array $extra): void {}
    public function cleanupUploads(array $data, array $extra): void {}

    // ---- UPDATE FLOW ----
    public function beforeUpdating(int $id, array &$data, array $extra): void {
        if (($data['category'] ?? null) !== 'general') {
            $data['printable'] = '';
            $data['session']   = 0;
        }
        $data['content'] = base64_encode($data['content']);
    }
    public function afterUpdated(int $id, array &$data, array $extra): void {
        logAction('edit_document_template', $extra['current_user']->user_login, null, null, json_encode($data));
    }

    // ---- DELETE FLOW ----
    public function beforeDeleting(int $id, array $extra): void {}
    public function afterDeleted(int $id, array $extra): void {
        $record = json_encode($extra['__record__'] ?? []);
        logAction('delete_document_template', $extra['current_user']->user_login, $id, $record);
    }
}
