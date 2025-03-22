<?php
namespace App\Entities;

use App\Models\Crud;
use App\Libraries\EntityLoader;

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
		$filter = $this->input->get('category', true);
		$session = $this->input->get('session', true);
		$q = $this->input->get('q', true);
		if (!$filter) {
			$filter = 'all';
		}

		if ($session) {
			$filterQuery = (!$filter || $filter == 'all') ? " where session_id='$session' " : " where sessions.id='$session' ";
		}

		if ($q) {
			if ($filterQuery) {
				$filterQuery .= ($filter == 'all') ? " and course_code like '%$q%' or title like '%$q%' " : " and courses.code like '%$q%' or title like '%$q%' ";
			} else {
				$filterQuery .= ($filter == 'all') ? " where course_code like '%$q%' or title like '%$q%' " : " where courses.code like '%$q%' or title like '%$q%' ";
			}
		}

		if (!$filterValues) {
			$filterValues = [];
		}

		if ($filter == 'approved') {
			$query = $this->getApprovedQuery($filterQuery);
		} else if ($filter == 'disapproved') {
			$query = $this->getDisapprovedQuery($filterQuery);
		} else {
			$query = $this->getAllQuery($filterQuery);
		}

		if (isset($_GET['sortBy']) && $orderBy) {
			$query .= " order by $orderBy ";
		} else {
			if ($filter == 'all') {
				$query .= " order by session desc,course_code asc ";
			} else if ($filter == 'approved') {
				$query .= " order by session desc,course_code asc ";
			} else {
				$query .= " order by session desc,course_code asc ";
			}
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->escape($start);
			$len = $this->db->escape($len);
			$query .= " limit $start, $len";
		}

		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->getResultArray();
		$res2 = $this->db->query($query2);
		$res2 = $res2->getResultArray();
		return [$res, $res2];
	}

	private function getAllQuery($filterQuery): string
	{
		$query = "SELECT SQL_CALC_FOUND_ROWS * from (
			(SELECT courses.id,code as course_code,sessions.date as session,ANY_VALUE(sessions.id) as session_id,
				ANY_VALUE(1) as status,count(*) as registered,
				count(total_score) as with_score,courses.title FROM course_enrollment join courses on courses.id=course_enrollment.course_id join 
				sessions on sessions.id=course_enrollment.session_id join approved_courses on course_enrollment.course_id=approved_courses.course_id 
				and course_enrollment.session_id=approved_courses.session_id group by course_code,session 
			)
			UNION
			(
				SELECT courses.id, code as course_code,sessions.date as session,ANY_VALUE(sessions.id) as session_id,
				ANY_VALUE(0) as status,count(*) as registered,count(total_score) as with_score,
				courses.title FROM course_enrollment join courses on courses.id=course_enrollment.course_id join sessions on sessions.id=course_enrollment.
				session_id  left join approved_courses on course_enrollment.course_id=approved_courses.course_id and 
				course_enrollment.session_id=approved_courses.session_id where approved_courses.course_id is null
				group by course_code,session)
			) all_courses $filterQuery";
//		$query1 = "SELECT COUNT(*) as totalCount FROM (SELECT courses.id,sessions.id as session_id
//    			FROM course_enrollment JOIN courses ON courses.id = course_enrollment.course_id JOIN sessions ON sessions.id = course_enrollment.session_id
//    			JOIN approved_courses ON course_enrollment.course_id = approved_courses.course_id AND
//    			course_enrollment.session_id = approved_courses.session_id
//    			GROUP BY courses.id, courses.code, sessions.date) AS all_courses $filterQuery ";
		return $query;
	}

	public function getApprovedQuery($filterQuery): string
	{
		$query = "SELECT SQL_CALC_FOUND_ROWS courses.id,code as course_code,sessions.date as session,ANY_VALUE(1) as status,
		count(*) as registered,count(total_score) as with_score,courses.title FROM course_enrollment join courses on courses.id=course_enrollment.course_id 
		join sessions on sessions.id=course_enrollment.session_id join approved_courses on course_enrollment.course_id=approved_courses.course_id 
		and course_enrollment.session_id=approved_courses.session_id $filterQuery group by course_code,session ";
		return $query;
	}

	function getDisapprovedQuery($filterQuery): string
	{
		if ($filterQuery) {
			$filterQuery .= ' and approved_courses.course_id is null ';
		} else {
			$filterQuery .= ' where approved_courses.course_id is null ';
		}
		$query = "SELECT SQL_CALC_FOUND_ROWS courses.id,code as course_code,sessions.date as session,ANY_VALUE(0) as status,
			count(*) as registered,count(total_score) as with_score,courses.title FROM course_enrollment join courses on courses.id=course_enrollment.course_id 
			join sessions on sessions.id=course_enrollment.session_id  left join approved_courses on course_enrollment.course_id=approved_courses.course_id 
			and course_enrollment.session_id=approved_courses.session_id $filterQuery group by course_code,session ";
		return $query;
	}

	private function processList($items): array
	{
		EntityLoader::loadClass($this, 'users_new');
		$currentUser = WebSessionManager::currentAPIUser();
		for ($i = 0; $i < count($items); $i++) {
			$items[$i] = $this->loadExtras($items[$i], $currentUser);
		}
		return $items;
	}

}
