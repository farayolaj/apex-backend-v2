<?php

require_once 'application/models/Crud.php';

/**
 * This class queries those who have paid for both RuS and SuS
 */
class Mandate_requests extends Crud
{
	protected static $tablename = '';

	static $apiSelectClause = [];

	private function getTransactionUserRequest($userID)
	{
		$query = "SELECT batch_ref from transaction_request group by batch_ref";
		$result = $this->query($query);
		$payload = [];
		if($result){
			$result = useGenerators($result);
			foreach($result as $item){
				$payload[] = [
					'batch_ref' => $item['batch_ref'],
					'user_requests' => $item
				];
			}
		}

		return $payload;
	}

	private function getUserRequestOnAssignee($batchRef,$userID){
		$query = "SELECT a.* from user_requests a join user_request_assignee b on b.user_request_id = a.id where b.user_request_id = ? and assign_to = ?";
		$result = $this->query($query, [$requestID, $userID]);
		return $result;
	}

	/**
	 * @param mixed $filterList
	 * @param mixed $queryString
	 * @param mixed $start
	 * @param mixed $len
	 * @param mixed $orderBy
	 * @return array
	 */
	public function APIList($filterList, $queryString, $start, $len, $orderBy=null, $type=null): array
	{
		$q = $this->input->get('q', true) ?: false;
		if ($q) {
			$searchArr = ['a.admon_reference', 'a.rrr_code', 'a.destination_account_number', 'a.destination_account_name'];
			$queryString = buildCustomSearchString($searchArr, $q);
		}
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		if($type === 'db-staff'){
			$currentUser = $this->webSessionManager->currentAPIUser();
			$filterQuery .= ($filterQuery ? " and " : " where ") . "b.assign_to = '$currentUser->id' and b.request_type = 'mandate' ";	
		}

		if (!$filterValues) {
			$filterValues = [];
		}

		if($type === 'db-staff'){
			$query = "SELECT SQL_CALC_FOUND_ROWS batch_ref,rrr_code,date(a.created_at) as created_at from transaction_request a join user_request_assignee b on b.user_request_id = a.user_request_id $filterQuery";
		}else{
			$query = "SELECT SQL_CALC_FOUND_ROWS batch_ref,rrr_code,date(a.created_at) as created_at from transaction_request a group by batch_ref,rrr_code,date(a.created_at) $filterQuery";
		}

		if($type === 'db-staff'){
			$query .= "group by batch_ref,rrr_code,date(a.created_at)";
		}

		if (isset($_GET['sortBy']) && $orderBy) {
			$query .= " order by $orderBy ";
		} else {
			$query .= " order by created_at desc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$query .= " limit $start, $len";
		}
		
		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->result_array();
		$res2 = $this->db->query($query2);
		$res2 = $res2->result_array();
		return [$res, $res2];
	}

}
