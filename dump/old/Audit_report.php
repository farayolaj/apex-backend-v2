<?php

require_once 'application/models/Crud.php';
require_once APPPATH . 'constants/ReportSlug.php';
require_once APPPATH . "constants/OutflowStatus.php";
require_once APPPATH . "constants/RequestTypeSlug.php";
require_once APPPATH . "constants/PaymentFeeDescription.php";

/**
 * This class queries those who have paid for both RuS and SuS
 */
class Audit_report extends Crud
{
	protected static $tablename = '';

	private function apiTransferJournal(?string $from, ?string $to): string
	{
		$whereString = '';
		if ($from && $to) {
			$from = ($this->db->escape_str($from));
			$to = ($this->db->escape_str($to));
			$whereString .= ($whereString ? " and " : " where ") . " date(a.date_performed) between date('$from') and date('$to') ";
		} else if ($from) {
			$from = ($this->db->escape_str($from));
			$whereString .= ($whereString ? " and " : " where ") . " date(a.date_performed) = date('$from') ";
		}

		$whereString .= ($whereString ? ' and ' : ' where ') . " a.payment_status in ('00', '01') ";

		$query = "SELECT a.id as id, concat(firstname, ' ',lastname) as fullname, 
				payment_description as descrip,date_performed,a.mainaccount_amount as ui_amount,
				a.subaccount_amount as dlc_amount,(a.mainaccount_amount+a.subaccount_amount) as total_amount,
				a.service_charge as debit_note,unix_timestamp(date_performed) as orderBy,'student_trans' as trans_type from transaction a join students b on b.id = a.student_id 
				join academic_record d on d.student_id = b.id 
				{$whereString} ";

		$query .= " UNION
		 		(SELECT a.id as id, concat(firstname, ' ',lastname) as fullname, payment_description as descrip,
		 			date_performed,a.mainaccount_amount as ui_amount, a.subaccount_amount as dlc_amount,
		 			(a.mainaccount_amount+a.subaccount_amount) as total_amount,
		 			a.service_charge as debit_note,unix_timestamp(date_performed) as orderBy,'admission_trans' as trans_type 
		 			from applicant_transaction a join applicants b on b.id = a.applicant_id 
		 			{$whereString} ) ";

		$query .= " UNION
		 		(SELECT a.id as id, name as fullname, payment_description as descrip,date_performed,a.mainaccount_amount as ui_amount,
		 		a.subaccount_amount as dlc_amount, (a.mainaccount_amount+a.subaccount_amount) as total_amount,
		 		a.service_charge as debit_note,unix_timestamp(date_performed) as orderBy, 'custom_trans' as trans_type 
		 		from transaction_custom a join users_custom b on b.id = a.custom_users_id join fee_description c 
		 		on c.id = a.payment_id {$whereString} )";

		return "SELECT SQL_CALC_FOUND_ROWS id, fullname, descrip, date_performed, ui_amount, dlc_amount, total_amount,
				debit_note, orderBy, trans_type from ($query) as a ";
	}

	private function apiExpenditures(?string $from, ?string $to): string
	{
		$whereString = '';
		if ($from && $to) {
			$from = ($this->db->escape_str($from));
			$to = ($this->db->escape_str($to));
			$whereString .= ($whereString ? " and " : " where ") . " date(a.created_at) between date('$from') and date('$to') ";
		} else if ($from) {
			$from = ($this->db->escape_str($from));
			$whereString .= ($whereString ? " and " : " where ") . " date(a.created_at) = date('$from') ";
		}

		$successStatus = OutflowStatus::SUCCESSFUL;
		$whereString .= ($whereString ? ' and ' : ' where ') . " a.payment_status_description = '$successStatus' ";

		$query = "SELECT user_id, payment_description as descrip,created_at as date_performed,
       		a.total_amount, unix_timestamp(a.transaction_date) as orderBy,date_paid as paid_date from transaction_request a left join users_new b
    		on b.id = a.user_id {$whereString}";

		return $query;
	}

	private function apiExpendituresMoreThan500K(?string $from, ?string $to): string
	{
		$whereString = '';
		if ($from && $to) {
			$from = ($this->db->escape_str($from));
			$to = ($this->db->escape_str($to));
			$whereString .= ($whereString ? " and " : " where ") . " date(a.created_at) between date('$from') and date('$to') ";
		} else if ($from) {
			$from = ($this->db->escape_str($from));
			$whereString .= ($whereString ? " and " : " where ") . " date(a.created_at) = date('$from') ";
		}

		$successStatus = OutflowStatus::SUCCESSFUL;
		$whereString .= ($whereString ? ' and ' : ' where ') . " a.payment_status_description = '$successStatus' and a.total_amount > 500000 ";

		$query = "SELECT user_id, payment_description as descrip,created_at as date_performed,
       		a.total_amount, unix_timestamp(a.transaction_date) as orderBy,date_paid as paid_date from transaction_request a left join users_new b
    		on b.id = a.user_id {$whereString}";

		return $query;
	}

	private function apiCashAdvance(?string $from, ?string $to, string $type): string
	{
		$whereString = '';
		if ($from && $to) {
			$from = ($this->db->escape_str($from));
			$to = ($this->db->escape_str($to));
			$whereString .= ($whereString ? " and " : " where ") . " date(a.created_at) between date('$from') and date('$to') ";
		} else if ($from) {
			$from = ($this->db->escape_str($from));
			$whereString .= ($whereString ? " and " : " where ") . " date(a.created_at) = date('$from') ";
		}

		$successStatus = OutflowStatus::SUCCESSFUL;
		$requestType = RequestTypeSlug::SALARY_ADVANCE;
		$requestType2 = RequestTypeSlug::RETIRE_SALARY_ADVANCE;

		if ($type == 'cleared') {
			$whereString .= ($whereString ? ' and ' : ' where ') . " a.payment_status_description = '$successStatus' ";
		} else if ($type == 'uncleared') {
			$whereString .= ($whereString ? ' and ' : ' where ') . " a.payment_status_description != '$successStatus' ";
		}

		$whereString .= ($whereString ? ' and ' : ' where ') . " b.slug in ('$requestType', '$requestType2') ";

		$query = "SELECT user_id, payment_description as descrip,a.created_at as date_performed,
       		a.total_amount, unix_timestamp(a.transaction_date) as orderBy,date_paid as paid_date from transaction_request a 
       		join request_type b on b.id = a.request_type_id {$whereString}";

		return $query;
	}

	private function apiRegisteredStudent($from, $to): string
	{
		$whereString = '';
		if ($from && $to) {
			$from = ($this->db->escape_str($from));
			$to = ($this->db->escape_str($to));
			$whereString .= ($whereString ? " and " : " where ") . " date(a.date_performed) between date('$from') and date('$to') ";
		} else if ($from) {
			$from = ($this->db->escape_str($from));
			$whereString .= ($whereString ? " and " : " where ") . " date(a.date_performed) = date('$from') ";
		}

		$schFee = PaymentFeeDescription::SCH_FEE_FIRST;
		$whereString .= ($whereString ? ' and ' : ' where ') . " a.payment_status in ('00', '01') and a.payment_id = '$schFee' ";

		return "SELECT a.id, concat(firstname, ' ',lastname) as fullname, matric_number,e.name as department, 
       		f.name as faculty,unix_timestamp(date_performed) as orderBy from transaction a join students b on b.id = a.student_id join academic_record c 
       		on c.student_id = b.id join programme d on d.id = a.programme_id join department e on e.id = d.department_id 
			join faculty f on f.id = d.faculty_id {$whereString}";
	}

	private function apiAcceptanceJournal($from, $to): string
	{
		$whereString = '';
		if ($from && $to) {
			$from = ($this->db->escape_str($from));
			$to = ($this->db->escape_str($to));
			$whereString .= ($whereString ? " and " : " where ") . " date(a.date_performed) between date('$from') and date('$to') ";
		} else if ($from) {
			$from = ($this->db->escape_str($from));
			$whereString .= ($whereString ? " and " : " where ") . " date(a.date_performed) = date('$from') ";
		}

		$acceptance = PaymentFeeDescription::ACCEPTANCE_FEE;
		$whereString .= ($whereString ? ' and ' : ' where ') . " a.payment_status in ('00', '01') and a.payment_id = '$acceptance' ";

		return "SELECT a.id, concat(firstname, ' ',lastname) as fullname, matric_number,e.name as department, 
       		f.name as faculty,d.name as course, unix_timestamp(date_performed) as orderBy from transaction a join students b on b.id = a.student_id join academic_record c 
       		on c.student_id = b.id join programme d on d.id = a.programme_id join department e on e.id = d.department_id 
			join faculty f on f.id = d.faculty_id {$whereString}";
	}

	/**
	 * @param mixed $filterList
	 * @param mixed $queryString
	 * @param mixed $start
	 * @param mixed $len
	 * @param mixed $orderBy
	 * @param string $type
	 * @param bool $export
	 * @return array
	 */
	public function APIList(?array  $filterList, ?string $queryString, ?string $start, ?string $len,
							?string $orderBy, string $type, bool $export = false): array
	{
		$from = $this->input->get('start_date', true) ?? null;
		$to = $this->input->get('end_date', true) ?? null;
		$limit = '';

		if (isset($_GET['start']) && $len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$limit = " limit $start, $len";
		}

		$query = null;

		if ($type == ReportSlug::TRANSFER_JOURNAL) {
			$query = $this->apiTransferJournal($from, $to);
		}

		if ($type == ReportSlug::ANALYSIS_EXPENDITURES) {
			$query = $this->apiExpenditures($from, $to);
		}


		if ($type == ReportSlug::CASH_ADVANCE) {
			$query = $this->apiCashAdvance($from, $to, 'all');
		}

		if ($type == ReportSlug::CASH_ADVANCE_CLEARED) {
			$query = $this->apiCashAdvance($from, $to, 'cleared');
		}

		if ($type == ReportSlug::CASH_ADVANCE_UNCLEARED) {
			$query = $this->apiCashAdvance($from, $to, 'uncleared');
		}

		if ($type == ReportSlug::EXPENSES_MORE_THAN_500K) {
			$query = $this->apiExpendituresMoreThan500K($from, $to);
		}

		if ($type == ReportSlug::REGISTERED_STUDENT) {
			$query = $this->apiRegisteredStudent($from, $to);
		}

		if ($type == ReportSlug::ACCEPTANCE_FEE_JOURNAL) {
			$query = $this->apiAcceptanceJournal($from, $to);
		}

		if (isset($_GET['sortBy']) && $orderBy) {
			$query .= " order by $orderBy {$limit}";
		} else {
			$query .= " order by orderBy desc {$limit}";
		}

		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query);
		$res = $res->result_array();
		$res2 = $this->db->query($query2);
		$res2 = $res2->result_array();

		$processList = [
			ReportSlug::ANALYSIS_EXPENDITURES,
			ReportSlug::CASH_ADVANCE,
			ReportSlug::CASH_ADVANCE_CLEARED,
			ReportSlug::CASH_ADVANCE_UNCLEARED,
			ReportSlug::EXPENSES_MORE_THAN_500K
		];
		if (in_array($type, $processList)) {
			$res = $this->processList($res);
		}

		if ($export) {
			return [$res, $res2];
		}
		return [$res, $res2];
	}

	private function processList($items): array
	{
		loadClass($this->load, 'users_new');

		$generator = useGenerators($items);
		$payload = [];
		foreach ($generator as $item) {
			$payload[] = $this->loadExtras($item);
		}
		return $payload;
	}

	public function loadExtras($item)
	{
		if (isset($item['user_id'])) {
			$userInfo = $this->users_new->getRequestUserInfo($item['user_id']);
			if ($userInfo) {
				$userInfo = $userInfo[0];
				$name = $userInfo['firstname'] . ' ' . $userInfo['lastname'];
				$item['fullname'] = $name;
			} else {
				$item['fullname'] = null;
			}
		}

		return $item;
	}

}
