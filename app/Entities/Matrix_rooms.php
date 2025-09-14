<?php

namespace App\Entities;

use App\Models\Crud;

class Matrix_rooms extends Crud
{
    protected static string $tablename = 'matrix_rooms';

    /**
     * Create a new Matrix room entry.
     * @param string $roomId The Matrix room ID.
     * @param string $roomType The type of room ('course', 'general', 'department').
     * @param int $entityId The associated entity ID (course ID or department ID).
     * @return bool True on success, false on failure.
     */
    public function create(string $roomId, string $roomType, int $entityId): bool
    {
        try {
            $this->db->table(static::$tablename)->insert([
                'room_id' => $roomId,
                'room_type' => $roomType,
                'entity_id' => $entityId,
            ]);
            return true;
        } catch (\Exception $e) {
            log_message('error', $e->getMessage(), $e->getTrace());
            return false;
        }
    }

    /**
     * Create multiple Matrix room entries in a single batch operation.
     * @param list<array{room_id: string, room_type: string, entity_id: int}> $rooms An array of associative arrays, each containing 'room_id', 'room_type', and 'entity_id'.
     */
    public function createMultiple(array $rooms): int
    {
        $res = $this->db->table(static::$tablename)->insertBatch($rooms);
        return is_bool($res) ? 0 : $res;
    }

    /**
     * @return array{room_id: string, room_type: string, entity_id: int}|null The room record as an associative array, or null if not found.
     */
    public function getByRoomId(string $roomId): ?array
    {
        return $this->db->table(static::$tablename)
            ->where('room_id', $roomId)
            ->get()
            ->getRowArray();
    }

    /**
     * Get a Matrix room by its associated entity ID and optional room type.
     * @param int $entityId The associated entity ID (course ID or department ID).
     * @param string|null $roomType The type of room ('course', 'general', 'department'), or null to ignore type.
     * @return array{room_id: string, room_type: string, entity_id: int}|null The room record as an associative array, or null if not found.
     */
    public function getByEntityId(int $entityId, string $roomType = "course"): ?array
    {
        return $this->db->table(static::$tablename)
            ->where('entity_id', $entityId)
            ->where('room_type', $roomType)
            ->get()
            ->getRowArray();
    }

    /**
     * Get all Matrix rooms of type 'course'.
     * @return list<array{room_id: string, room_type: string, entity_id: int}> An array of room records.
     */
    public function getGeneralRooms(): array
    {
        return $this->db->table(static::$tablename)
            ->where('room_type', 'general')
            ->get()
            ->getResultArray();
    }
}
