<?php

namespace App\Entities;

use App\Models\Crud;
use CodeIgniter\Database\RawSql;

/**
 * Class Webinars
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
        'w.planned_duration',
        'w.start_time',
        'w.end_time',
        'w.presentation_id',
        'w.presentation_name',
        'w.enable_comments',
        'w.send_notifications',
        'w.join_count',
        'w.playback_count',
        'w.recordings',
        'w.updated_at',
        'w.created_at'
    ];

    private function process(?array $webinar)
    {
        if ($webinar) {
            $webinar['recordings'] = json_decode($webinar['recordings'], true) ?: [];
        }

        return $webinar;
    }

    public function webinarExists(int $webinarId): bool
    {
        try {
            return $this->db->table('webinars')
                ->where('id', $webinarId)
                ->where('deletion_date IS NULL')
                ->countAllResults() === 1;
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
            $webinars = $this->db->table('webinars w')->select(self::$apiSelectClause)
                ->where('session_id', $sessionId)
                ->where('course_id', $courseId)
                ->where('deletion_date IS NULL')
                ->orderBy('scheduled_for', 'DESC')
                ->get()
                ->getResultArray();
            return array_map([$this, 'process'], $webinars);
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
            $webinars = $this->db->table('webinars w')
                ->select(array_merge(self::$apiSelectClause, ['COUNT(wc.id) AS comment_count']))
                ->join('webinar_comments wc', 'w.id = wc.webinar_id', 'left')
                ->where('w.session_id', $sessionId)
                ->where('w.course_id', $courseId)
                ->where('w.deletion_date IS NULL')
                ->groupBy('w.id')
                ->orderBy('w.scheduled_for', 'DESC')
                ->get()
                ->getResultArray();
            return array_map([$this, 'process'], $webinars);
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
            $webinar = $this->db->table('webinars w')->select(self::$apiSelectClause)
                ->where('w.id', $webinarId)
                ->where('w.deletion_date IS NULL')
                ->get()
                ->getRowArray();
            return $this->process($webinar);
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
            $webinar = $this->db->table('webinars w')->select(self::$apiSelectClause)
                ->where('w.room_id', $roomId)
                ->where('w.deletion_date IS NULL')
                ->get()
                ->getRowArray();
            return $this->process($webinar);
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
            if (isset($data['recordings'])) {
                $data['recordings'] = json_encode($data['recordings']);
            }

            $this->db->table('webinars')
                ->where('id', $webinarId)
                ->where('deletion_date IS NULL')
                ->update($data);
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
            return $this->db->table('webinars')
                ->where('id', $webinarId)
                ->where('deletion_date IS NULL')
                ->update(['join_count' => new RawSql('join_count + 1')]) > 0;
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
            return $this->db->table('webinars')
                ->where('id', $webinarId)
                ->where('deletion_date IS NULL')
                ->update(['playback_count' => new RawSql('playback_count + 1')]) > 0;
        } catch (\Exception $e) {
            log_message('error', 'Error Incrementing Playback Count: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function markAsDeleted(int $webinarId, int $deletedBy): bool
    {
        try {
            $data = [
                'deleted_by' => $deletedBy,
                'deletion_date' => new RawSql('CURRENT_TIMESTAMP')
            ];
            $this->db->table('webinars')
                ->where('id', $webinarId)
                ->where('deletion_date IS NULL')
                ->update($data);
            return $this->db->affectedRows() > 0;
        } catch (\Exception $e) {
            log_message('error', 'Error Marking Webinar as Deleted: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}
