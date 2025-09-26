<?php

namespace App\Entities;

use App\Models\Crud;

/**
 * This class represents the model of the course_settings table
 */
class Course_settings extends Crud
{
    /**
     * This is the entity name equivalent to the table name
     * @var string
     */
    protected static $tablename = "course_settings";

    /**
     * Get course settings by course and session
     * @return array{overview: string|null, mission: string|null, objectives: string|null, course_guide_id: string|null}|null
     */
    public function getByCourseAndSession(int $courseId, int $sessionId): ?array
    {
        $builder = $this->db->table(self::$tablename);
        $result = $builder->select('overview, mission, objectives, course_guide_id')
            ->where('course_id', $courseId)
            ->where('session_id', $sessionId)
            ->get()
            ->getRowArray();

        return $result ?: [
            'overview' => null,
            'mission' => null,
            'objectives' => null,
            'course_guide_id' => null
        ];
    }

    /**
     * Create or update course settings
     * @param array{overview?: string|null, mission?: string|null, objectives?: string|null, course_guide_id?: string|null} $data
     */
    public function upsertSettings(int $courseId, int $sessionId, array $data): bool
    {
        $result = $this->db->table(self::$tablename)->upsert([
            array_merge($data, [
                'course_id' => $courseId,
                'session_id' => $sessionId
            ])
        ]);

        return is_bool($result) ? $result : $result > 0;
    }
}
