<?php

namespace App\Services\Admin;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;

class PhotoService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    public function getPhotos(array $f, int $offset = 0, int $limit = 50, bool $withPaths = true): array
    {
        $target = strtolower((string) ($f['target'] ?? 'student'));
        return $target === 'applicant'
            ? $this->getApplicantPhotos($f, $offset, $limit, $withPaths)
            : $this->getStudentPhotos($f, $offset, $limit, $withPaths);
    }

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
                ->join('department f', 'f.id = d.department_id');

            if($course){
                $base->join('course_enrollment c', 'c.student_id = a.id', 'inner')
                    ->where('c.course_id', $course);
            }
            $base->where('e.payment_id', 1)
                ->whereIn('e.payment_status', ['00', '01'])
                ->where('e.session', $session);

            if ($level)   { $base->where('e.level', $level); }
            if ($program) { $base->where('e.programme_id', $program); }
        } else {
            $base = $db->table('students a')
                ->join('academic_record b', 'b.student_id = a.id')
                ->join('programme d', 'd.id = b.programme_id', 'left')
                ->join('department f', 'f.id = d.department_id');

            if ($course) {
                $base->join('course_enrollment c', 'c.student_id = a.id', 'inner')
                    ->where('c.course_id', $course);
            }

            if ($level)   { $base->where('b.current_level', $level); }
            if ($program) { $base->where('b.programme_id', $program); }
        }

        if ($entry)  { $base->where('b.session_of_admission', $entry); }
        if ($dept)   { $base->where('f.id', $dept); }

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

        $idsSQL = $countBuilder->select('DISTINCT a.id', false)->getCompiledSelect(false);
        $total  = (int) $this->db->table("({$idsSQL}) t")->countAllResults(false);

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
            $rows = $this->attachPassportPaths($rows, 'student');;
        }

        return ['total' => $total, 'data' => $rows];
    }

    private function baseApplicantQuery(array $f): BaseBuilder
    {
        $db      = $this->db;
        $session = $f['session']    ?? null;
        $level   = $f['level']      ?? null;
        $dept    = $f['department'] ?? null;
        $program = $f['programme']  ?? null;
        $search  = $f['q']          ?? null;

        $base = $db->table('applicants ap');

        if ($dept) {
            $base->join('programme d', 'd.id = ap.programme_id', 'left')
                ->join('department f', 'f.id = d.department_id', 'left')
                ->where('f.id', $dept);
        }

        if ($program) { $base->where('ap.programme_id', $program); }
        if ($session) { $base->where('ap.session_id', $session); }
        if ($level)   { $base->where('ap.admitted_level', $level); }

        if ($search) {
            $base->groupStart()
                ->like('ap.firstname', $search)
                ->orLike('ap.othernames', $search)
                ->orLike('ap.lastname', $search)
                ->orLike('ap.applicant_id', $search)
                ->orLike('ap.email', $search)
                ->groupEnd();
        }

        return $base;
    }

    public function getApplicantPhotos(array $f, int $offset = 0, int $limit = 50, bool $withPaths = true): array
    {
        $base         = $this->baseApplicantQuery($f);
        $countBuilder = clone $base;
        $dataBuilder  = clone $base;

        $idsSQL = $countBuilder->select('DISTINCT ap.id', false)->getCompiledSelect(false);
        $total  = (int) $this->db->table("({$idsSQL}) t")->countAllResults(false);

        $rows = $dataBuilder
            ->distinct()
            ->select([
                'ap.id AS student_id',
                'ap.applicant_id AS matric_number',
                'ap.passport',
                'ap.email',
                'ap.admitted_level AS level',
            ])
            ->select("CONCAT_WS(' ', ap.firstname, ap.othernames, ap.lastname) AS fullname", false)
            ->orderBy('ap.applicant_id', 'ASC')
            ->limit(max(1, $limit), max(0, $offset))
            ->get()->getResultArray();

        if ($withPaths) {
            $rows = $this->attachPassportPaths($rows, 'applicant');
        }

        return ['total' => $total, 'data' => $rows];
    }

    /**
     * NEW: return just matric_number + passport for explicit selections.
     */
    public function listFilesByMatric(array $matrics, string $target = 'student'): array
    {
        if (empty($matrics)) return [];

        if (strtolower($target) === 'applicant') {
            return $this->db->table('applicants ap')
                ->distinct()
                ->select(['ap.applicant_id AS matric_number', 'ap.passport'])
                ->whereIn('ap.applicant_id', $matrics)
                ->get()->getResultArray();
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
        $target = strtolower((string) ($f['target'] ?? 'student'));
        if ($target === 'applicant') {
            return $this->baseApplicantQuery($f)
                ->distinct()
                ->select(['ap.applicant_id AS matric_number', 'ap.passport'])
                ->get()->getResultArray();
        }

        return $this->baseQueryForFilters($f)
            ->distinct()
            ->select(['b.matric_number', 'a.passport'])
            ->get()->getResultArray();
    }

    private function attachPassportPaths(array $rows, string $target): array
    {
        foreach ($rows as &$r) {
            $filename = trim((string) ($r['passport'] ?? ''));
            if ($filename === '') {
                $r['passport'] = '';
                continue;
            }
            $imagePath = $target === 'applicant' ? applicantImagePathDirectory($filename) : studentImagePathDirectory($filename);
            if (!is_file($imagePath)) {
                $r['passport'] = '';
                continue;
            }
            $r['passport'] = $target === 'applicant' ? applicantImagePath($filename) : studentImagePath($filename);
        }
        unset($r);
        return $rows;
    }
}