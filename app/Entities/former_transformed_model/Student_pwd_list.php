<?php
namespace App\Entities;

use App\Models\Crud;

/**
 * This class queries those who have paid for both RuS and SuS
 */
class Student_pwd_list extends Crud
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

		$filterQuery .= ($filterQuery ? " and " : " where ") . " disabilities is not null and disabilities <> '' and disabilities <> 'No' ";

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by entry_year desc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->escapeString($start);
			$len = $this->db->escapeString($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}
		$query = "SELECT SQL_CALC_FOUND_ROWS a.id,a.firstname,a.lastname,a.othernames,a.gender,b.matric_number,c.disabilities,
       	a.passport,b.current_level as level,(select sessions.date from sessions where sessions.id = b.session_of_admission) as entry_year 
		from students a join academic_record b on b.student_id = a.id join medical_record c on a.id = c.student_id $filterQuery";
		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->getResultArray();
		$res2 = $this->db->query($query2);
		$res2 = $res2->getResultArray();
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
		if (isset($item['disabilities'])) {
			$item['disabilities'] = $item['disabilities'] ?: null;
		}

		if ($item['passport']) {
			$item['passport'] = studentImagePath($item['passport']);
		}

		if ($item['level']) {
			$item['level'] = formatStudentLevel($item['level']);
		}

		return $item;
	}

}
