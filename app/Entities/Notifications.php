<?php

namespace App\Entities;

use App\Models\Crud;

class Notifications extends Crud
{
    protected $table = 'notifications';
    static $apiSelectClause = [
        'id',
        'recipient_table',
        'recipient_id',
        'type',
        'title',
        'message',
        'data',
        'created_at'
    ];

    public function createMany(array $notifications): bool
    {
        return $this->db->table($this->table)->insertBatch($notifications);
    }

    public function getUnreadNotifications(string $recipientTable, int $recipientId, int $limit = 20, int $offset = 0): array
    {
        return $this->db->table($this->table)
            ->select(self::$apiSelectClause)
            ->where('recipient_table', $recipientTable)
            ->where('recipient_id', $recipientId)
            ->where('is_read', false)
            ->orderBy('created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
    }

    public function deleteMany(array $ids): bool
    {
        return $this->db->table($this->table)
            ->whereIn('id', $ids)
            ->delete();
    }

    public function countUnreadNotifications(string $recipientTable, int $recipientId): int
    {
        return $this->db->table($this->table)
            ->where('recipient_table', $recipientTable)
            ->where('recipient_id', $recipientId)
            ->where('is_read', false)
            ->countAllResults();
    }

    public function markAsRead(string $recipientTable, int $recipientId, array $ids): bool
    {
        return $this->db->table($this->table)
            ->where('recipient_table', $recipientTable)
            ->where('recipient_id', $recipientId)
            ->whereIn('id', $ids)
            ->update(['is_read' => true]);
    }
}
