<?php

namespace App\Entities;

use App\Enums\CommonEnum as CommonSlug;
use App\Models\Crud;

/**
 * This class queries those who have paid for both RuS and SuS
 */
class Student_orientation_list extends Crud
{
    protected static $tablename = '';

    static $apiSelectClause = [];

    /**
     * @param mixed $filterList
     * @param mixed $queryString
     * @param mixed $start
     * @param mixed $len
     * @param mixed $orderBy
     * @return array
     */
    public function APIList($filterList, $queryString, $start, $len, $orderBy, $export = false): array
    {
        $temp = getFilterQueryFromDict($filterList);
        $filterQuery = buildCustomWhereString($temp[0], $queryString, false);
        $filterValues = $temp[1];
        $currentAdmissionSession = get_setting('admission_session_update');

        // $filterQuery .= ($filterQuery ? " and " : " where ") . " orientation_attendance <> ''
        // and b.session_of_admission = '$currentAdmissionSession' ";

        $directEntry = $this->db->escapeString(CommonSlug::DIRECT_ENTRY->value);
        $olevel = $this->db->escapeString(CommonSlug::O_LEVEL->value);
        $olevelPutme = $this->db->escapeString(CommonSlug::O_LEVEL_PUTME->value);
        $fastTrack = $this->db->escapeString(CommonSlug::FAST_TRACK->value);

        $filterQuery .= ($filterQuery ? " and " : " where ") . " orientation_attendance <> '' and (
			(b.entry_mode = '$directEntry' and b.current_level = '2') ||
			(b.entry_mode = '$fastTrack' and b.current_level = '2') ||
			(b.entry_mode = '$olevel' and b.current_level = '1') ||
            (b.entry_mode = '$olevelPutme' and b.current_level = '1') ||
			(b.entry_mode = '$directEntry' and b.current_level = '1') ||
			(b.entry_mode = '$fastTrack' and b.current_level = '1')
		) ";

        if (isset($_GET['sortBy']) && $orderBy) {
            $filterQuery .= " order by $orderBy ";
        } else {
            if ($export) {
                $filterQuery .= " order by faculty asc, matric_number asc ";
            } else {
                $filterQuery .= " order by entry_year desc ";
            }
        }

        if (isset($_GET['start']) && $len) {
            $start = $this->db->escapeString($start);
            $len = $this->db->escapeString($len);
            $filterQuery .= " limit $start, $len";
        }
        if (!$filterValues) {
            $filterValues = [];
        }
        $query = "SELECT SQL_CALC_FOUND_ROWS a.id,a.firstname,a.lastname,a.othernames,a.gender,b.matric_number,a.orientation_attendance,
       		a.passport,b.current_level as level,(select sessions.date from sessions where sessions.id = b.session_of_admission) as entry_year,d.name as faculty,d.slug,a.orientation_seat_no
			from students a join academic_record b on b.student_id = a.id join programme c on c.id = b.programme_id left join faculty d on d.id = c.faculty_id $filterQuery";
        $query2 = "SELECT FOUND_ROWS() as totalCount";
        $res = $this->db->query($query, $filterValues);
        $res = $res->getResultArray();
        $res2 = $this->db->query($query2);
        $res2 = $res2->getResultArray();
        if ($export) {
            return [$res, $res2];
        }
        $res = $this->processList($res);
        return [$res, $res2];
    }

    private function processList($items): array
    {
        $generator = useGenerators($items);
        $payload = [];
        foreach ($generator as $item) {
            $payload[] = $this->loadExtras($item);
        }
        return $payload;
    }

    public function loadExtras($item)
    {
        if ($item['passport']) {
            $item['passport'] = studentImagePath($item['passport'], $this);
        }

        if ($item['level']) {
            $item['level'] = formatStudentLevel($item['level']);
        }

        if ($item['orientation_attendance']) {
            $item['orientation_attendance'] = ucfirst($item['orientation_attendance']);
        }

        return $item;
    }

}
