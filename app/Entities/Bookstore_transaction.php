<?php

namespace App\Entities;

use App\Enums\BookstoreStatusEnum as BookstoreStatus;
use App\Models\Crud;

class Bookstore_transaction extends Crud
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
        $from = request()->getGet('start_date') ?? null;
        $to = request()->getGet('end_date') ?? null;
        $paymentStatus = request()->getGet('payment_status') ?? null;
        $bookStatus = request()->getGet('book_status') ?? null;

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
            $filterQuery .= ($filterQuery ? " and " : " where ") . " (b.transaction_ref is NULL OR 
			(date(b.date_performed) between '$from' and '$to' )) ";
        } else if ($from) {
            $from = ($this->db->escape_str($from));
            $filterQuery .= ($filterQuery ? " and " : " where ") . " (b.transaction_ref IS NULL OR 
				(date(b.date_performed) = '$from' )) ";
        }

        if (isset($_GET['sortBy']) && $orderBy) {
            $filterQuery .= " order by $orderBy ";
        } else {
            if ($export) {
                $filterQuery .= " order by d.matric_number asc ";
            } else {
                $filterQuery .= " order by a.created_at desc ";
            }
        }

        if (isset($_GET['start']) && $len) {
            $start = $this->db->escapeString($start);
            $len = $this->db->escapeString($len);
            $filterQuery .= " limit $start, $len";
        }
        if (!$filterValues) {
            $filterValues = [];
        }
        $query = "SELECT SQL_CALC_FOUND_ROWS a.id, a.order_id, a.total_amount, a.transaction_ref, a.book_status, 
       		 COALESCE(b.rrr_code, 'N/A') as rrr_code, b.payment_description, b.payment_status, b.amount_paid,
       		 c.firstname,c.lastname,c.othernames,d.matric_number,
       		 COALESCE(b.date_performed, a.created_at) as date_performed,a.created_at,a.student_id
			from student_payment_bookstore a 
			left join transaction b on b.transaction_ref = a.transaction_ref 
			join students c on c.id = a.student_id 
			join academic_record d on d.student_id = c.id $filterQuery";

        $query2 = "SELECT FOUND_ROWS() as totalCount";
        $res = $this->db->query($query, $filterValues);
        $res = $res->getResultArray();
        $res2 = $this->db->query($query2);
        $res2 = $res2->getResultArray();
        if ($export) {
            return [$res, $res2];
        }
        return [$res, $res2];
    }

    public function getBookstoreTransaction(): array
    {
        $status = BookstoreStatus::PENDING;
        $query = "SELECT a.id,a.order_id, a.transaction_ref,a.reserved_until, b.rrr_code,a.book_status, b.payment_status,
		 	b.amount_paid,b.date_performed
			from student_payment_bookstore a 
			left join transaction b on b.transaction_ref = a.transaction_ref where a.book_status = '$status' ";
        $res = $this->db->query($query);
        return $res->getResultArray();
    }
}
