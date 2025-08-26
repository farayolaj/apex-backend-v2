<?php
namespace App\Hooks\Observers;

use App\Hooks\Contracts\Observer; 

/**
 * Observer hooks for Course_mapping.
 * - $data is passed by reference on mutating hooks (beforeCreating/handleUploads/beforeUpdating).
 * - $extra is passed by value; contains context like $extra['auth'] (user) etc.
 * - Keep logic fast and deterministic; avoid heavy I/O where possible.
 */
final class Course_mapping implements Observer
{
    // ---- INSERT FLOW ----
    public function beforeCreating(array &$data, array $extra): void {
        $data['pre_select'] = $data['pre_select'] ?? 0;
        $data['level'] = json_encode($data['level'] ?? []);
    }
    public function afterCreated(int $id, array &$data, array $extra): void {
        logAction('course_mapping_create', $extra['current_user']->user_login, null, null, json_encode($data));
    }
    
    public function handleUploads(array &$data, array $files, array $extra): void {}
    public function cleanupUploads(array $data, array $extra): void {}

    // ---- UPDATE FLOW ----
    public function beforeUpdating(int $id, array &$data, array $extra): void {
        $data['pre_select'] = $data['pre_select'] ?? 0;
        $data['level'] = json_encode($data['level'] ?? []);
    }
    public function afterUpdated(int $id, array &$data, array $extra): void {
        logAction('course_mapping_edit', $extra['current_user']->user_login, $id, null, json_encode($data));
    }

    // ---- DELETE FLOW ----
    public function beforeDeleting(int $id, array $extra): void {}
    public function afterDeleted(int $id, array $extra): void {
        $record = json_encode($extra['__record__'] ?? []);
        logAction('course_mapping_delete', $extra['current_user']->user_login, $id, $record);
    }
}
