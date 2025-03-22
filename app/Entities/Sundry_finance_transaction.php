<?php
namespace App\Entities;

use App\Models\Crud;

/**
 * This class queries those who have paid for both RuS and SuS
 */
class Sundry_finance_transaction extends Crud
{
	protected static $tablename = 'Sundry_finance_transaction';

	static $apiSelectClause = [];

	/**
	 * @param mixed $filterList
	 * @param mixed $queryString
	 * @param mixed $start
	 * @param mixed $len
	 * @param mixed $orderBy
	 * @return array
	 */
	public function APIList($filterList, $queryString, $start, $len, $orderBy = null, $exportApi = null)
	{
		$paymentStatus = false;
		$tempPaymentStatus = [];
		$tempCode = null;
		$from = $this->input->get('start_date', true) ?? null;
		$to = $this->input->get('end_date', true) ?? null;

		if (isset($filterList['payment_status']) && $filterList['payment_status']) {
			$paymentStatus = true;
			$tempPaymentStatus['payment_status'] = $filterList['payment_status'];
			unset($filterList['payment_status']);
		} else {
			// using this to still enforce a paid payment_status since FE is not sending any
			$paymentStatus = true;
			$tempPaymentStatus['payment_status'] = 'paid';
		}

		if (isset($filterList['b.code']) && $filterList['b.code']) {
			$tempCode = $filterList['b.code'];
			unset($filterList['b.code']);
		}

		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		if ($paymentStatus) {
			if ($tempCode) {
				$filterQuery .= ($filterQuery ? " and " : " where ") . " a.payment_status in ('00', '01') ";
			} else {
				$filterQuery .= ($filterQuery ? " and " : " where ") . " (a.payment_status in ('00', '01') and b.code in ('SuS', 'RoS') )";
			}
		}

		if ($tempCode) {
			$code = $tempCode == 'suspension' ? 'SuS' : 'RoS';
			$filterQuery .= ($filterQuery ? " and " : " where ") . " b.code='$code'";
		}

		if ($from && $to) {
			$from = ($this->db->escape_str($from));
			$to = ($this->db->escape_str($to));
			$filterQuery .= ($filterQuery ? " and " : " where ") . " date(a.date_performed) between date('$from') and date('$to') ";
		} else if ($from) {
			$from = ($this->db->escape_str($from));
			$filterQuery .= ($filterQuery ? " and " : " where ") . " date(a.date_performed) = date('$from') ";
		}

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy";
		} else {
			if ($exportApi) {
				$filterQuery .= " order by g.name asc, d.matric_number asc";
			} else {
				$filterQuery .= " order by a.date_performed desc ";
			}
		}

		if ($len) {
			$start = $this->db->escape($start);
			$len = $this->db->escape($len);
			$filterQuery .= " limit $start, $len";
		}

		if (!$filterValues) {
			$filterValues = [];
		}

		if ($exportApi) {
			$query = "SELECT SQL_CALC_FOUND_ROWS a.student_id,c.lastname, c.firstname, c.othernames,d.matric_number,a.payment_status,
       		a.date_performed as paid_date,a.payment_description,e.date as year_of_entry,d.entry_mode,g.name as department from 
        	transaction a join fee_description b on b.id = a.payment_id join students c on c.id = a.student_id join academic_record d 
        	on d.student_id = c.id join sessions e on e.id = d.year_of_entry join programme f on f.id = d.programme_id join 
        	department g on g.id = f.department_id $filterQuery";
		} else {
			$query = "SELECT SQL_CALC_FOUND_ROWS a.student_id,c.lastname, c.firstname, c.othernames,d.matric_number,a.payment_status,
       		a.date_performed as paid_date,a.payment_description,e.date as year_of_entry,d.entry_mode from transaction a join 
        	fee_description b on b.id = a.payment_id join students c on c.id = a.student_id join academic_record d on 
        	d.student_id = c.id join sessions e on e.id = d.year_of_entry $filterQuery";
		}
		
		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->getResultArray();
		$res2 = $this->db->query($query2);
		$res2 = $res2->getResultArray();

		return [$res, $res2];
	}

}
