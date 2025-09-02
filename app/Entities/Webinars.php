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
        'w.id',
        'w.course_id',
        'w.session_id',
        'w.room_id',
        'w.title',
        'w.description',
        'w.scheduled_for',
        'w.start_time',
        'w.end_time',
        'w.presentation_id',
        'w.presentation_name',
        'w.enable_comments',
        'w.send_notifications',
        'w.join_count',
        'w.playback_count',
        'w.recording_id',
        'w.recording_url',
        'w.updated_at',
        'w.created_at'
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
            return $this->db->table('webinars w')->select(self::$apiSelectClause)
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

    public function listWithCommentCount(int $sessionId, int $courseId)
    {
        try {
            return $this->db->table('webinars w')
                ->select(array_merge(self::$apiSelectClause, ['COUNT(wc.id) AS comment_count']))
                ->join('webinar_comments wc', 'w.id = wc.webinar_id', 'left')
                ->where('w.session_id', $sessionId)
                ->where('w.course_id', $courseId)
                ->groupBy('w.id')
                ->orderBy('w.scheduled_for', 'DESC')
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'Error Listing Webinars with Comments: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    public function getDetails(int $webinarId)
    {
        try {
            return $this->db->table('webinars w')->select(self::$apiSelectClause)
                ->where('w.id', $webinarId)
                ->get()
                ->getRowArray();
        } catch (\Exception $e) {
            log_message('error', 'Error Getting Webinar Details: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    public function getDetailsByRoomId(string $roomId)
    {
        try {
            return $this->db->table('webinars w')->select(self::$apiSelectClause)
                ->where('w.room_id', $roomId)
                ->get()
                ->getRowArray();
        } catch (\Exception $e) {
            log_message('error', 'Error Getting Webinar Details by Room ID: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);
            return null;
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
