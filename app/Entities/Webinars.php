<?php

namespace App\Entities;

use App\Models\Crud;

/**
 * This class queries those who have paid for both RuS and SuS
 */
class Webinars extends Crud
{
    protected static $tablename = 'webinars';

    static $apiSelectClause = [
        "id",
        "course_id",
        "room_id",
        "title",
        "description",
        "scheduled_for",
        "presentation_id",
        "presentation_name",
        "updated_at",
        "created_at"
    ];

    public function webinarExists(int $webinarId): bool
    {
        return $this->db->table('webinars')->where('id', $webinarId)->countAllResults() === 1;
    }

    public function list(int $sessionId, int $courseId)
    {
        $query = "SELECT " . implode(", ", self::$apiSelectClause) . " FROM webinars WHERE session_id = $sessionId AND course_id = $courseId ORDER BY scheduled_for DESC";
        $res = $this->db->query($query);
        return $res->getResultArray();
    }

    public function getDetails(int $webinarId)
    {
        $query = "SELECT " . implode(", ", self::$apiSelectClause) . " FROM webinars WHERE id = $webinarId";
        $res = $this->db->query($query);
        return $res->getRowArray();
    }

    public function create(array $data): int
    {
        $escapedValues = array_map(function ($value) {
            return "'" . $this->db->escapeString($value) . "'";
        }, array_values($data));
        $query = "INSERT INTO webinars (" . implode(", ", array_keys($data)) . ") VALUES (" . implode(", ", $escapedValues) . ")";
        $this->db->query($query);
        return $this->db->insertID();
    }

    public function updateWebinar(int $webinarId, array $data): bool
    {
        $setClause = [];
        foreach ($data as $key => $value) {
            $setClause[] = "$key = " . "'" . $this->db->escapeString($value) . "'";
        }
        $query = "UPDATE webinars SET " . implode(", ", $setClause) . " WHERE id = $webinarId";
        $this->db->query($query);
        return $this->db->affectedRows() > 0;
    }
}
