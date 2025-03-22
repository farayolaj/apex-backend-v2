<?php

require_once 'application/models/Crud.php';
require_once APPPATH . 'constants/CommonSlug.php';

/**
 * This class queries those who have paid for both RuS and SuS
 */
class Student_telco_list extends Crud
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

		$filterQuery .= ($filterQuery ? " and " : " where ") . " telco_number <> '' ";

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
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}
		$query = "SELECT SQL_CALC_FOUND_ROWS a.id,a.firstname,a.lastname,a.othernames,a.gender,b.matric_number,a.telco_number,b.current_level as level,d.name as faculty from students a join academic_record b on b.student_id = a.id join programme c on c.id = b.programme_id left join faculty d on d.id = c.faculty_id $filterQuery";
		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->result_array();
		$res2 = $this->db->query($query2);
		$res2 = $res2->result_array();
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
		if ($item['level']) {
			$item['level'] = formatStudentLevel($item['level']);
		}

		if ($item['telco_number']) {
			$item['telco_number'] = removeIntlOnPhoneNumber($item['telco_number']);
		}

		return $item;
	}

}
