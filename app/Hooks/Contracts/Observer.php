<?php
namespace App\Hooks\Contracts;

interface Observer
{
    public function beforeCreating(array &$data, array $extra): void;
    public function afterCreated(int $id, array &$data, array $extra): void;

    public function beforeUpdating(int $id, array &$data, array $extra): void;
    public function afterUpdated(int $id, array &$data, array $extra): void;

    public function handleUploads(array &$data, array $files, array $extra): void;
    public function cleanupUploads(array $data, array $extra): void;
}