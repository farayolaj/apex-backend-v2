<?php
namespace App\Hooks\Observers;

use App\Hooks\Contracts\Observer; 

/**
 * Observer hooks for Course_configuration.
 * - $data is passed by reference on mutating hooks (beforeCreating/handleUploads/beforeUpdating).
 * - $extra is passed by value; contains context like $extra['auth'] (user) etc.
 * - Keep logic fast and deterministic; avoid heavy I/O where possible.
 */
final class Course_configuration implements Observer
{
    // ---- INSERT FLOW ----
    public function beforeCreating(array &$data, array $extra): void {}
    public function afterCreated(int $id, array &$data, array $extra): void {
        logAction('create_course_config', $extra['current_user']->user_login, $id, null, json_encode($data));
    }
    
    public function handleUploads(array &$data, array $files, array $extra): void {}
    public function cleanupUploads(array $data, array $extra): void {}

    // ---- UPDATE FLOW ----
    public function beforeUpdating(int $id, array &$data, array $extra): void {}
    public function afterUpdated(int $id, array &$data, array $extra): void {
        logAction('edit_course_config', $extra['current_user']->user_login, $id, null, json_encode($data));
    }

    // ---- DELETE FLOW ----
    public function beforeDeleting(int $id, array $extra): void {}
    public function afterDeleted(int $id, array $extra): void {
        $record = json_encode($extra['__record__'] ?? []);
        logAction('course_config_delete', $extra['current_user']->user_login, $id, $record);
    }
}
