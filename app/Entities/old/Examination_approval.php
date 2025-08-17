<?php

require_once 'application/models/Crud.php';

/**
 * This class queries those who have paid for both RuS and SuS
 */
class Examination_approval extends Crud
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
		$filter = $this->input->get('category', true) ?: 'all';
		$session = $this->input->get('session', true);
		$q = $this->input->get('q', true);

		if ($session) {
			$filterQuery = " where c.id='$session' ";
		}

		if ($q) {
			$qValue = " '%$q%' ";
			$filterQuery .= ($filterQuery ? ' AND ' : ' WHERE ') . "(b.code LIKE {$qValue} OR title LIKE {$qValue})";
		}

		if (!$filterValues) {
			$filterValues = [];
		}

		$query = null;
		$query2 = null;
		if ($filter == 'approved') {
			$queryString = $this->getApprovedQuery($filterQuery);
			$query = $queryString[0];
			$query2 = $queryString[1];
		} else if ($filter == 'disapproved') {
			$queryString = $this->getDisapprovedQuery($filterQuery);
			$query = $queryString[0];
			$query2 = $queryString[1];
		} else {
			$queryString = $this->getAllQuery($filterQuery);
			$query = $queryString[0];
			$query2 = $queryString[1];
		}

		if ($filter == 'all') {
			$query .= " GROUP BY b.id, b.code, c.date, c.id, d.course_id, b.title";
		} else {
			$query .= " GROUP BY b.id, b.code, c.date, c.id, b.title";
		}

		if (isset($_GET['sortBy']) && $orderBy) {
			$query .= " order by $orderBy ";
		} else {
			$query .= " order by c.date desc, b.code asc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->escape_str($start);
			$len = $this->db->escape_str($len);
			$query .= " limit $start, $len";
		}

		$res = $this->db->query($query, $filterValues);
		$res = $res->result_array();
		$res2 = $this->db->query($query2);
		$res2 = $res2->result_array()[0]['total'];
		return [$res, $res2];
	}

	private function getAllQuery(?string $filterQuery): array
	{
		$query = "SELECT b.id, b.code as course_code, c.date as session, c.id as session_id, b.title,
			CASE WHEN d.course_id IS NOT NULL THEN '1' ELSE '0' END as status,
    		COUNT(DISTINCT a.student_id) as registered,
    		COUNT(DISTINCT CASE WHEN a.total_score IS NOT NULL THEN a.student_id END) as with_score
    		FROM course_enrollment a 
			JOIN courses b ON b.id = a.course_id 
    		JOIN sessions c ON c.id = a.session_id 
    		LEFT JOIN approved_courses d ON d.course_id = a.course_id AND d.session_id = a.session_id 
			$filterQuery ";

		$query1 = "SELECT COUNT(DISTINCT CONCAT(b.id, '-', c.id)) AS total FROM 
    		course_enrollment a
    		JOIN courses b ON b.id = a.course_id 
    		JOIN sessions c ON c.id = a.session_id 
    		LEFT JOIN approved_courses d ON d.course_id = a.course_id AND d.session_id = a.session_id 
			$filterQuery ";

		return [$query, $query1];
	}

	public function getApprovedQuery($filterQuery): array
	{
		$query = "SELECT b.id, code AS course_code, c.date AS session, '1' AS status, b.title,
    		COUNT(DISTINCT a.student_id) AS registered, c.id as session_id,
    		COUNT(DISTINCT CASE WHEN a.total_score IS NOT NULL THEN a.student_id END) AS with_score
			FROM course_enrollment a
    		JOIN courses b ON b.id = a.course_id 
    		JOIN sessions c ON c.id = a.session_id 
    		JOIN approved_courses d ON d.course_id = a.course_id AND d.session_id = a.session_id $filterQuery";

		$query2 = "SELECT COUNT(DISTINCT CONCAT(b.id, '-', c.id)) AS total
			FROM course_enrollment a
    		JOIN courses b ON b.id = a.course_id 
    		JOIN sessions c ON c.id = a.session_id 
    		JOIN approved_courses d ON d.course_id = a.course_id AND d.session_id = a.session_id $filterQuery";

		return [$query, $query2];
	}

	public function getDisapprovedQuery($filterQuery): array
	{
		$filterQuery .= ($filterQuery ? ' and ' : ' where ') . ' d.course_id is null ';

		$query = "SELECT b.id,code as course_code,c.date as session, '0' as status, b.title,
			count(DISTINCT a.student_id) as registered, c.id as session_id,
			count(DISTINCT CASE WHEN a.total_score IS NOT NULL THEN a.student_id END) as with_score
			 FROM course_enrollment a
			join courses b on b.id=a.course_id 
			join sessions c on c.id=a.session_id  
			left join approved_courses d on d.course_id=a.course_id and d.session_id=a.session_id $filterQuery";

		$query2 = "SELECT COUNT(*) AS total FROM 
			(
    			SELECT b.id, c.date FROM sessions c
        		JOIN course_enrollment a ON c.id = a.session_id
        		JOIN courses b ON b.id = a.course_id
        		LEFT JOIN approved_courses d ON d.course_id = a.course_id AND d.session_id = a.session_id
    			$filterQuery GROUP BY b.id, c.date
			) AS subquery";

		return [$query, $query2];
	}

	public function processSingleApproval(string $courseId, string $sessionId, string $flag): bool
	{
		if ($flag === 'disapprove') {
			$query = "DELETE from approved_courses where course_id=? and session_id=?";
			if (!$this->db->query($query, [$courseId, $sessionId])) {
				return false;
			}
			return true;
		}

		$query = "INSERT ignore into approved_courses(session_id,course_id) 
	   		values(?,?)";
		if (!$this->db->query($query, [$sessionId, $courseId])) {
			return false;
		}
		return true;
	}

	public function processBulkApproval(string $flag): bool
	{
		$session = @$this->input->post('session', true) ?: null;
		$course = trim(@$this->input->post('q', true));
		$where = '';
		$session = $this->db->escape_str($session);
		$course = $this->db->escape_str($course);
		if ($session) {
			$where = " where course_enrollment.session_id='$session' ";
		}

		if ($course) {
			$where .= ($where ? ' and ' : ' where ') . " course_enrollment.course_id in (
				SELECT id from courses where code like '$course%'
			) ";
		}

		if ($flag === 'approve') {
			return $this->approvalActionQuery($where);
		} else {
			return $this->disapproveActionQuery($session, $course);
		}
	}

	private function approvalActionQuery(string $where): bool
	{
		$query = "INSERT ignore into approved_courses(session_id,course_id) 
			SELECT distinct session_id,course_id from course_enrollment $where ";

		if (!$this->db->query($query)) {
			return false;
		}
		return true;
	}

	private function disapproveActionQuery($session, $course = null): bool
	{
		$query = "DELETE ac FROM approved_courses ac where ac.session_id = '$session'  ";
		if ($course) {
			$query .= " AND ac.course_id IN (
				SELECT DISTINCT ce.course_id FROM course_enrollment ce JOIN courses c ON ce.course_id = c.id
   				WHERE ce.session_id = '$session' AND c.code LIKE '$course%' ) ";
		}

		if (!$this->db->query($query)) {
			return false;
		}
		return true;
	}


}
