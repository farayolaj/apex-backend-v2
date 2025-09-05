<?php

namespace App\Models\Admin;

use CodeIgniter\Model;
use CodeIgniter\Database\BaseBuilder;

class PhotoModel extends Model
{
    private function baseQueryForFilters(array $f): BaseBuilder
    {
        $db      = $this->db;
        $session = $f['session']    ?? null;
        $entry   = $f['entry_year'] ?? null;
        $level   = $f['level']      ?? ($f['levels'] ?? null);
        $dept    = $f['department'] ?? null;
        $program = $f['programme']  ?? null;
        $course  = $f['course']     ?? null;
        $search  = $f['q']          ?? ($f['searchPhotos'] ?? null);

        if ($session) {
            $base = $db->table('transaction e')
                ->join('students a', 'e.student_id = a.id')
                ->join('academic_record b', 'b.student_id = a.id')
                ->join('programme d', 'd.id = e.programme_id')
                ->join('department f', 'f.id = d.department_id')
                ->join('course_enrollment c', 'c.student_id = a.id', 'left')
                ->where('e.payment_id', 1)
                ->whereIn('e.payment_status', ['00', '01'])
                ->where('e.session', $session);

            if ($level)   { $base->where('e.level', $level); }
            if ($program) { $base->where('e.programme_id', $program); }
        } else {
            $base = $db->table('students a')
                ->join('academic_record b', 'b.student_id = a.id')
                ->join('programme d', 'd.id = b.programme_id', 'left')
                ->join('department f', 'f.id = d.department_id')
                ->join('course_enrollment c', 'c.student_id = a.id', 'left');

            if ($level)   { $base->where('b.current_level', $level); }
            if ($program) { $base->where('b.programme_id', $program); }
        }

        if ($entry)  { $base->where('b.session_of_admission', $entry); }
        if ($dept)   { $base->where('f.id', $dept); }
        if ($course) { $base->where('c.course_id', $course); }

        if ($search) {
            $base->groupStart()
                ->like('a.firstname', $search)
                ->orLike('a.othernames', $search)
                ->orLike('a.lastname', $search)
                ->orLike('b.matric_number', $search)
                ->orLike('a.user_login', $search)
                ->groupEnd();
        }

        return $base;
    }

    /**
     * @param array $f
     * @param int $offset
     * @param int $limit
     * @param bool $withPaths
     * @return array
     */
    public function getStudentPhotos(array $f, int $offset = 0, int $limit = 50, bool $withPaths = true): array
    {
        $base         = $this->baseQueryForFilters($f);
        $countBuilder = clone $base;
        $dataBuilder  = clone $base;

        $total = (int) $countBuilder
            ->select('COUNT(DISTINCT a.id) AS total', false)
            ->get()->getRow('total');

        $levelExpr = ($f['session'] ?? null) ? 'e.level' : 'b.current_level';

        $rows = $dataBuilder
            ->distinct()
            ->select([
                'a.id AS student_id',
                'b.matric_number',
                'a.passport',
                'a.user_login AS email',
            ])
            ->select("CONCAT_WS(' ', a.firstname, a.othernames, a.lastname) AS fullname", false)
            ->select("$levelExpr AS level", false)
            ->orderBy('b.matric_number', 'ASC')
            ->limit(max(1, $limit), max(0, $offset))
            ->get()->getResultArray();

        if ($withPaths) {
            $rows = $this->attachPassportPaths($rows);
        }

        return ['total' => $total, 'data' => $rows];
    }

    /**
     * NEW: return just matric_number + passport for explicit selections.
     */
    public function listFilesByMatric(array $matrics): array
    {
        if (empty($matrics)) {
            return [];
        }
        return $this->db->table('students a')
            ->distinct()
            ->select(['b.matric_number', 'a.passport'])
            ->join('academic_record b', 'b.student_id = a.id')
            ->whereIn('b.matric_number', $matrics)
            ->get()->getResultArray();
    }

    /**
     * NEW: return just matric_number + passport for a filter set
     * (used by the ZIP download path; no pagination).
     */
    public function listFilesByFilter(array $f): array
    {
        return $this->baseQueryForFilters($f)
            ->distinct()
            ->select(['b.matric_number', 'a.passport'])
            ->get()->getResultArray();
    }

    private function attachPassportPaths(array $rows): array
    {
        foreach ($rows as &$r) {
            $filename = trim((string) ($r['passport'] ?? ''));
            if ($filename === '') {
                $r['passport'] = '';
                continue;
            }
            $imagePath = studentImagePathDirectory($filename);
            if (!is_file($imagePath)) {
                $r['passport'] = '';
                continue;
            }
            $r['passport'] = studentImagePath($filename);
        }
        unset($r);
        return $rows;
    }
}