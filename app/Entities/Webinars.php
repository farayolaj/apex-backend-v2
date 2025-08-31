<?php

namespace App\Entities;

use App\Models\Crud;
use CodeIgniter\Database\RawSql;

/**
 * This class queries those who have paid for both RuS and SuS
 */
class Webinars extends Crud
{
    protected static $tablename = 'webinars';

    static $apiSelectClause = [
        'id',
        'course_id',
        'room_id',
        'title',
        'description',
        'scheduled_for',
        'presentation_id',
        'presentation_name',
        'enable_comments',
        'send_notifications',
        'join_count',
        'playback_count',
        'updated_at',
        'created_at'
    ];

    public function webinarExists(int $webinarId): bool
    {
        try {
            return $this->db->table('webinars')->where('id', $webinarId)->countAllResults() === 1;
        } catch (\Exception $e) {
            log_message('error', 'Error Checking Webinar Existence: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function list(int $sessionId, int $courseId)
    {
        try {
            return $this->db->table('webinars')->select(self::$apiSelectClause)
                ->where('session_id', $sessionId)
                ->where('course_id', $courseId)
                ->orderBy('scheduled_for', 'DESC')
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'Error Listing Webinars: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    public function getDetails(int $webinarId)
    {
        try {
            return $this->db->table('webinars')->select(self::$apiSelectClause)
                ->where('id', $webinarId)
                ->get()
                ->getRowArray();
        } catch (\Exception $e) {
            log_message('error', 'Error Getting Webinar Details: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    public function create(array $data): int
    {
        try {
            $this->db->table('webinars')->insert($data);
            return $this->db->insertID();
        } catch (\Exception $e) {
            log_message('error', 'Error Creating Webinar: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);
            return 0;
        }
    }

    public function updateWebinar(int $webinarId, array $data): bool
    {
        try {
            $this->db->table('webinars')->where('id', $webinarId)->update($data);
            return $this->db->affectedRows() > 0;
        } catch (\Exception $e) {
            log_message('error', 'Error Updating Webinar: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function incrementJoinCount(int $webinarId): bool
    {
        try {
            return $this->db->table('webinars')->where('id', $webinarId)->update(['join_count' => new RawSql('join_count + 1')]) > 0;
        } catch (\Exception $e) {
            log_message('error', 'Error Incrementing Join Count: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function incrementPlaybackCount(int $webinarId): bool
    {
        try {
            return $this->db->table('webinars')->where('id', $webinarId)->update(['playback_count' => new RawSql('playback_count + 1')]) > 0;
        } catch (\Exception $e) {
            log_message('error', 'Error Incrementing Playback Count: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
