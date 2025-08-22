<?php

namespace App\Hooks\Observers;

use App\Hooks\Contracts\Observer;

class Courses implements Observer
{

    public function beforeCreating(array &$data, array $extra): void
    {
        if (isset($data['code'])) $data['code'] = strtoupper(trim((string)$data['code']));
        if (!isset($data['active'])) $data['active'] = 1;
    }

    public function afterCreated(int $id, array &$data, array $extra): void
    {
        logAction('course_create', $extra['current_user']->user_login, null, null, json_encode($data));
    }

    public function handleUploads(array &$data, array $files, array $extra): void
    {
        // TODO: Implement handleUploads() method.
    }

    public function cleanupUploads(array $data, array $extra): void
    {
        // TODO: Implement cleanupUploads() method.
    }
}