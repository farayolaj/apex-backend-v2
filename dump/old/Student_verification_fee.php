<?php

require_once 'application/models/Crud.php';
require_once APPPATH . 'constants/FeeDescriptionCode.php';

/**
 * This is a custom entity class different from the generated one that are mapped to the database table
 */
class Student_verification_fee extends Crud
{
	protected static $tablename = 'Student_verification_fee';

	static $apiSelectClause = ['id', 'firstname', 'lastname', 'othernames', 'user_login'];

	public function APIList($filterList, $queryString, $start, $len, $orderBy)
	{
		$verifyStatus = false;
		$tempPaymentStatus = [];
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		$filterQuery .= ($filterQuery ? ' and ' : ' where ') . " ((fee_description.code = ? or fee_description.code = ? ) 
			and payment_status in ('00', '01')) ";
		$filterValues[] = [FeeDescriptionCode::VERIFICATION_ONE];
		$filterValues[] = [FeeDescriptionCode::VERIFICATION_TWO];

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by vid desc ";
		}

		if ($len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$filterQuery .= " limit $start, $len";
		}

		if (!$filterValues) {
			$filterValues = [];
		}

		$query = "SELECT distinct SQL_CALC_FOUND_ROWS transaction.ID as vid,students.id, CONCAT(students.lastname, ' ', students.firstname, ' ', students.othernames) AS fullname,
			passport, academic_record.matric_number,academic_record.current_level as level, sessions.date as year_of_entry,
			department.name as department, faculty.name as faculty, programme.name as programme,document_verification,students.gender,academic_record.applicant_type 
			from transaction join fee_description on fee_description.id = transaction.payment_id join students on students.id = transaction.student_id 
			join academic_record  on academic_record.student_id = students.id join sessions on sessions.id = academic_record.year_of_entry 
			join programme on programme.id = academic_record.programme_id left join department on department.id = programme.department_id left join 
			faculty on faculty.id = programme.faculty_id $filterQuery";

		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->result_array();
		$res2 = $this->db->query($query2);
		$res2 = $res2->result_array();
		return [$res, $res2];
	}

	public function getStudentUploadDocumentComplete(bool $logParam = false, bool $session = false)
	{
		$data[] = [FeeDescriptionCode::VERIFICATION_ONE];
		$data[] = [FeeDescriptionCode::VERIFICATION_TWO];
		$from = $this->input->get('start_date', true) ?? null;
		$to = $this->input->get('end_date', true) ?? null;

		$whereFrom = '';
		if ($from && $to) {
			$from = $this->db->escape_str($from);
			$to = $this->db->escape_str($to);
			$whereFrom = " and student_verification_documents.date_created between '$from' and '$to' ";
		} else if ($from) {
			$from = ($this->db->escape_str($from));
			$whereFrom = " and student_verification_documents.date_created >= '$from' ";
		}

		$query = "SELECT distinct SQL_CALC_FOUND_ROWS transaction.ID as vid,students.id, CONCAT(students.lastname, ' ', students.firstname, ' ', students.othernames) AS fullname,
			passport, academic_record.matric_number,academic_record.current_level as level, sessions.date as year_of_entry, 
			department.name as department, faculty.name as faculty, programme.name as programme,document_verification,
			students.gender,academic_record.application_number,academic_record.olevel_details from transaction join 
			fee_description on fee_description.id = transaction.payment_id join students on students.id = transaction.student_id 
			join academic_record  on academic_record.student_id = students.id join sessions on sessions.id = academic_record.year_of_entry 
			join programme on programme.id = academic_record.programme_id join department on department.id = programme.department_id 
			join faculty on faculty.id = programme.faculty_id join student_verification_documents on student_verification_documents.students_id = students.id 
			where fee_description.code in (?, ?) and payment_status in ('00', '01') and document_verification = 'Pending' $whereFrom";

		if ($session) {
			$activeSession = get_setting('active_session_student_portal');
			$query .= "and transaction.session= '$activeSession'";
		}

		$result = $this->query($query, $data);
		if (!$result) {
			return [];
		}

		if ($logParam) {
			$currentUser = $this->webSessionManager->currentAPIUser();
			$logData = [
				'start_from' => $from,
				'end_to' => $to,
				'print_datetime' => date('Y-m-d H:i:s')
			];
			$logData = json_encode($logData);
			logAction($this, 'bulk_print_student_cover', $currentUser->user_login, null, null, $logData);
		}

		return $result;

	}

	public function hasStudentPaidOlevelVerification($studentID){
		$query = "SELECT transaction.ID as vid,student_id from transaction join fee_description 
		on fee_description.id = transaction.payment_id where (fee_description.code = ? or fee_description.code = ? ) and payment_status in ('00', '01') and student_id = ?";
		$data = [FeeDescriptionCode::VERIFICATION_ONE, FeeDescriptionCode::VERIFICATION_TWO, $studentID];
		$result = $this->query($query, $data);
		return (bool)$result;
	}

}
