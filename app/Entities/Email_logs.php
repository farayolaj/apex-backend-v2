<?php

namespace App\Entities;

use App\Models\Crud;
use App\Enums\CommonEnum as CommonSlug;

class Email_logs extends Crud
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
    public function APIList($filterList, $queryString, $start, $len, $orderBy): array
    {
        $temp = getFilterQueryFromDict($filterList);
        $filterQuery = buildCustomWhereString($temp[0], $queryString, false);
        $filterValues = $temp[1];
        $applicantType = CommonSlug::EMAIL_BUILDER_APPLICANT->value;
        $studentType = CommonSlug::EMAIL_BUILDER_STUDENT->value;

        $filterQuery .= ($filterQuery ? " and " : " where "). "action_performed in ('$applicantType', '$studentType') ";

        if (isset($_GET['sortBy']) && $orderBy) {
            $filterQuery .= " order by $orderBy ";
        } else {
            $filterQuery .= " order by date_performed desc ";
        }

        if (isset($_GET['start']) && $len) {
            $start = $this->db->escapeString($start);
            $len = $this->db->escapeString($len);
            $filterQuery .= " limit $start, $len";
        }
        if (!$filterValues) {
            $filterValues = [];
        }

        $query = "SELECT SQL_CALC_FOUND_ROWS a.student_id as id, username as sent_by,
			CASE 
			WHEN action_performed = '$applicantType' THEN 'Applicant'
			WHEN action_performed = '$studentType' THEN 'Student'
			ELSE 'N/A' 
			END as sent_to,date_performed, description as subject, 
			IF(
				EXISTS (
					SELECT 1 from email_logs b where b.email_ref = a.student_id LIMIT 1  
            	), 1, 0
            ) as is_sent
			from users_log a $filterQuery";

        $query2 = "SELECT FOUND_ROWS() as totalCount";
        $res = $this->db->query($query, $filterValues);
        $res = $res->getResultArray();
        $res2 = $this->db->query($query2);
        $res2 = $res2->getResultArray();
        return [$res, $res2];
    }

    public function APIListLog($filterList, $queryString, $start, $len, $orderBy=null): array
    {
        $temp = getFilterQueryFromDict($filterList);
        $filterQuery = buildCustomWhereString($temp[0], $queryString, false);
        $filterValues = $temp[1];

        if (isset($_GET['sortBy']) && $orderBy) {
            $filterQuery .= " order by $orderBy ";
        } else {
            $filterQuery .= " order by sent_at desc ";
        }

        if (isset($_GET['start']) && $len) {
            $start = $this->db->escapeString($start);
            $len = $this->db->escapeString($len);
            $filterQuery .= " limit $start, $len";
        }
        if (!$filterValues) {
            $filterValues = [];
        }

        $query = "SELECT SQL_CALC_FOUND_ROWS a.id, subject, to_email as sent_to, attempts, sent_at, created_at from 
                email_logs a $filterQuery";

        $query2 = "SELECT FOUND_ROWS() as totalCount";
        $res = $this->db->query($query, $filterValues);
        $res = $res->getResultArray();
        $res2 = $this->db->query($query2);
        $res2 = $res2->getResultArray();
        return [$res, $res2];
    }

}