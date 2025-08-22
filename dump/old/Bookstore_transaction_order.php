<?php

require_once 'application/models/Crud.php';
require_once APPPATH . 'constants/BookstoreStatus.php';

class Bookstore_transaction_order extends Crud
{

	/**
	 * @param mixed $filterList
	 * @param mixed $queryString
	 * @param mixed $start
	 * @param mixed $len
	 * @param mixed $orderBy
	 * @param bool $export
	 * @return array
	 */
	public function APIList($filterList, $queryString, $start, $len, $orderBy, $export = false): array
	{
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];
		$from = $this->input->get('start_date', true) ?? null;
		$to = $this->input->get('end_date', true) ?? null;
		$paymentStatus = $this->input->get('payment_status', true) ?? null;
		$bookStatus = $this->input->get('book_status', true) ?? null;

		if ($paymentStatus == 'paid') {
			$filterQuery .= ($filterQuery ? ' and ' : ' where ') . " b.payment_status in ('00', '01') ";
		} else if ($paymentStatus == 'pending') {
			$filterQuery .= ($filterQuery ? ' and ' : ' where ') . " b.payment_status not in ('00', '01') ";
		}

		if ($bookStatus === BookstoreStatus::COMPLETED) {
			$status = BookstoreStatus::COMPLETED;
			$filterQuery .= ($filterQuery ? ' and ' : ' where ') . " a.book_status = '$status'  ";
		} else if ($bookStatus === BookstoreStatus::PENDING) {
			$status = BookstoreStatus::PENDING;
			$filterQuery .= ($filterQuery ? ' and ' : ' where ') . " a.book_status = '$status'  ";
		} else if ($bookStatus === BookstoreStatus::CANCELLED) {
			$status = BookstoreStatus::CANCELLED;
			$filterQuery .= ($filterQuery ? ' and ' : ' where ') . " a.book_status = '$status'  ";
		}

		if ($from && $to) {
			$from = ($this->db->escape_str($from));
			$to = ($this->db->escape_str($to));
			$filterQuery .= ($filterQuery ? " and " : " where ") . " date(e.created_at) between '$from' and '$to' ";
		} else if ($from) {
			$from = ($this->db->escape_str($from));
			$filterQuery .= ($filterQuery ? " and " : " where ") . " date(e.created_at) = '$from' ";
		}

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			if ($export) {
				$filterQuery .= " order by d.matric_number asc ";
			} else {
				$filterQuery .= " order by e.created_at desc ";
			}
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->escape_str($start);
			$len = $this->db->escape_str($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}

		$query = "SELECT SQL_CALC_FOUND_ROWS a.id, a.order_id, a.transaction_ref,e.title,e.quantity,e.price,e.amount,
       		a.book_status, b.rrr_code, c.firstname,c.lastname,
       		c.othernames,d.matric_number,b.date_performed, e.created_at as date_ordered,a.student_id,e.book_type
       		
			from student_payment_bookstore_items e 
			join student_payment_bookstore a on a.id = e.student_payment_bookstore_id 
			join transaction b on b.transaction_ref = a.transaction_ref 
			join students c on c.id = a.student_id 
			join academic_record d on d.student_id = c.id $filterQuery";

		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->result_array();
		$res2 = $this->db->query($query2);
		$res2 = $res2->result_array();
		if ($export) {
			return [$res, $res2];
		}
		return [$res, $res2];
	}
}
