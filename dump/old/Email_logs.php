<?php

require_once 'application/models/Crud.php';
require_once APPPATH . 'constants/CommonSlug.php';

/**
 * This class queries those who have paid for both RuS and SuS
 */
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
		$applicantType = CommonSlug::EMAIL_BUILDER_APPLICANT;
		$studentType = CommonSlug::EMAIL_BUILDER_STUDENT;

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by a.created_at desc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->escape_str($start);
			$len = $this->db->escape_str($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}

		$query = "SELECT SQL_CALC_FOUND_ROWS concat(a.type,':',a.email_ref) as id, sender as sent_by,
			CASE 
			WHEN type = '$applicantType' THEN 'Applicant'
			WHEN type = '$studentType' THEN 'Student'
			ELSE 'N/A' 
			END as sent_to, a.created_at as date_performed, a.subject, 
			IF(
				EXISTS (
					SELECT 1 from email_logs b where b.email_ref = a.email_ref LIMIT 1  
            	), 1, 0
            ) as is_sent
			from email_batches a $filterQuery";

		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->result_array();
		$res2 = $this->db->query($query2);
		$res2 = $res2->result_array();
		return [$res, $res2];
	}

	public function APIListLog($filterList, $queryString, $start, $len, $orderBy = null): array
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
			$start = $this->db->escape_str($start);
			$len = $this->db->escape_str($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}

		$query = "SELECT SQL_CALC_FOUND_ROWS a.id, a.to_email as sent_to, a.attempts, a.sent_at, a.created_at, a.type, identifier from 
			email_logs a join email_batches b on b.email_ref = a.email_ref and b.type = a.type $filterQuery";

		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->result_array();
		$res2 = $this->db->query($query2);
		$res2 = $res2->result_array();
		return [$this->processList($res), $res2];
	}

	private function processList($items): array
	{
		$generator = useGenerators($items);
		$payload = [];
		loadClass($this->load, 'students');
		foreach ($generator as $item) {
			$payload[] = $this->loadExtras($item);
		}
		return $payload;
	}

	public function loadExtras($item)
	{
		if (isset($item['sent_to'])) {
			$item['sent_to'] = maskEmail($item['sent_to']);
		}

		if ($item['identifier']) {
			$fullname = null;
			if ($item['type'] == 'email_builder_applicant') {
				$user = fetchSingle($this, 'applicants', 'applicant_id', $item['identifier']);
				if ($user) {
					$fullname = strtoupper($user['lastname']) . ', ' . ucfirst($user['firstname']);
				}
			} else if ($item['type'] == 'email_builder_student') {
				$user = $this->students->getStudentRecordOnly($item['identifier']);
				if ($user) {
					$fullname = strtoupper($user['lastname']) . ', ' . ucfirst($user['firstname']);
				}
			}
			$item['fullname'] = $fullname;
		}

		return $item;
	}

}
