<?php
	
require_once('application/models/Crud.php');

/**
 * This is a custom entity class different from the generated one that are mapped to the database table
 */
class Admissions_programme_list extends Crud
{
	protected static $tablename = 'Programme';

	public function APIList($filterList, $queryString,$start,$len,$orderBy)
	{
		$verifyStatus = false;
		$tempPaymentStatus = [];
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];
		$session = $this->input->get('session', true) ?? null;

		if(!$session){
			return [];
		}

		if(isset($_GET['sortBy']) && $orderBy){
			$filterQuery .= " order by $orderBy ";
		}else{
			$filterQuery .= " order by id asc";
		}

		if ($len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$filterQuery.=" limit $start, $len";
		}

		if (!$filterValues) {
			$filterValues = [];
		}

		$query = "SELECT distinct SQL_CALC_FOUND_ROWS programme.* from programme $filterQuery";
		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query,$filterValues);
		$res = $res->result_array();
		$res2  = $this->db->query($query2);
		$res2 = $res2->result_array();
		$res = $this->processList($res, $session);

		return [$res,$res2];
	}

	private function processList($items, $session){
		$contents = [];
		for ($i = 0; $i < count($items); $i++) {
			$contents[] = $this->loadExtras($items[$i],$session);
		}
		return $contents;
	}

	private function loadExtras($item, $session)
	{
		$result = [
			'check_exist' => $this->checkProgrammeExistence($item['id'], $session),
			'data' => $item
		];
		return $result;
	}

	private function checkProgrammeExistence($programme, $session){
		$query = "SELECT id, programme_id, session,admission_id,alevel_requirements,olevel_requirements,other_requirements, active from admission_programme_requirements where programme_id = ? and session = ?";
		$result = $this->query($query, [$programme, $session]);
		if(!$result){
			return null;
		}
		return $result[0];
	}

}