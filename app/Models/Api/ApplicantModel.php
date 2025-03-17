<?php
if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

require_once APPPATH . 'constants/PaymentFeeDescription.php';
require_once APPPATH . 'traits/AdminModelTrait.php';

/**
 *
 */
class ApplicantModel extends CI_Model
{

	/**
	 * @return array
	 */
	private function loadTransactionApplicationSession(): array
	{
		$query = "SELECT a.id,a.date from sessions a join transaction b on b.session = a.id group by id, a.date order by a.date asc";
		$query = $this->db->query($query);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		$content = $query->result_array();
		$currentAdmissionSession = get_setting('active_admission_session');
		loadClass($this->load, 'sessions');
		$session = $this->sessions->getSessionById($currentAdmissionSession);
		$result = [];
		$sessionPresent = false;
		foreach ($content as $ses) {
			$sessionLatter = explode('/', $ses['date']);
			if (isset($sessionLatter[0]) && $sessionLatter[0] >= '2018') {
				if ($ses['id'] == $currentAdmissionSession) {
					$sessionPresent = true;
					$result[] = $ses;
				} else {
					$result[] = $ses;
				}
			}
		}
		if (!$sessionPresent) {
			$result = array_merge($session, $result);
		}

		asort($result);
		return $result;
	}

	/**
	 * @param mixed $session
	 * @return int|<missing>
	 */
	public function getApplicantStatsData(string $type, $session = null)
	{
		if ($type == 'admitted') {
			$query = "SELECT sum(total) as total from (
                SELECT distinct count(*) as total from applicants a where a.is_admitted = '1' and a.session_id = ?
                UNION
                SELECT distinct count(*) as total from applicant_post_utme c where c.is_admitted = '1' and c.session_id = ? 
                                                                             and c.programme_id <> ''
            ) x ";
			$query = $this->db->query($query, [$session, $session]);
		} else if ($type == 'accepted') {
			$acceptance = PaymentFeeDescription::ACCEPTANCE_FEE;
			$query = "SELECT distinct count(*) as total from transaction a where payment_id = '$acceptance' and payment_status in ('00', '01') and a.session = ?";
			$query = $this->db->query($query, [$session]);
		} else if ($type == 'application') {
			$query = "SELECT sum(total) as total from (
                SELECT distinct count(*) as total from applicant_transaction a join applicants b on b.id = a.applicant_id  where a.payment_status in ('00', '01') and a.session = ?
                UNION
                    SELECT distinct count(*) as total from applicant_post_utme d where d.session_id = ? and d.programme_id <> ''
            ) x ";
			$query = $this->db->query($query, [$session, $session]);
		} else if ($type == 'registered') {
			$query = "SELECT count(distinct a.student_id) as total from transaction a join academic_record c on c.student_id = a.student_id where a.payment_id = '1' and a.payment_status in ('01', '00') and a.session = ? and c.year_of_entry = ?";
			$query = $this->db->query($query, [$session, $session]);
		}

		$result = 0;
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array()[0]['total'];
	}

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public function loadApplicantStats(string $type = 'admitted'): array
	{
		$sessions = $this->loadTransactionApplicationSession();
		$result = [];
		foreach ($sessions as $session) {
			$total = $this->getApplicantStatsData($type, $session['id']);
			$payload = [
				'label' => $session['date'],
				'total' => $total ?: 0,
			];
			$result[] = $payload;
		}

		return $result;
	}

	/**
	 * @param mixed $session
	 * @param mixed $type
	 * @return array|<missing>
	 */
	public function loadStudetProgrammeStatus($session, $type = 'verified')
	{
		if ($type == 'verified') {
			$query = "SELECT distinct programme.name, count(distinct transaction.student_id) as total from transaction join students on transaction.student_id = students.id join academic_record on academic_record.student_id = students.id join programme on programme.id = transaction.programme_id where transaction.session = ? and  academic_record.session_of_admission = ? and students.is_verified = ? and payment_status in ('00', '01') and payment_id = '16' group by programme.name order by programme.name asc";

			$query = $this->db->query($query, [$session, $session, '1']);
		} else if ($type == 'admitted') {
			$query = "SELECT name, sum(total) as total from (
                SELECT distinct b.name, count(*) as total from applicants a join programme b on b.id = a.programme_id where is_admitted = '1' and session_id = ? group by b.name
                UNION
                SELECT distinct d.name, count(*) as total from applicant_post_utme c join programme d on d.id = c.programme_id where is_admitted = '1' and session_id = ? group by d.name
            ) x group by name order by name asc";
			$query = $this->db->query($query, [$session, $session]);
		} else if ($type == 'accepted') {
			$acceptance = PaymentFeeDescription::ACCEPTANCE_FEE;
			$query = "SELECT distinct programme.name, count(*) as total from transaction join programme on programme.id = transaction.programme_id where payment_id = '$acceptance' and payment_status in ('00', '01') and session = ? group by programme.name order by programme.name asc";
			$query = $this->db->query($query, [$session]);
		} else if ($type == 'application') {
			$query = "SELECT name, sum(total) as total from (
            SELECT distinct c.name, count(*) as total from applicant_transaction a join applicants b on b.id = a.applicant_id join programme c on c.id = b.programme_id where payment_status in ('00', '01') and a.session = ? group by c.name
            UNION
                SELECT distinct e.name, count(*) as total from applicant_post_utme d join programme e on e.id = d.programme_id where d.session_id = ? group by e.name
            ) x group by name order by name asc ";
			$query = $this->db->query($query, [$session, $session]);
		} else if ($type == 'registered') {
			$query = null;
			$paymentValidation = get_setting('session_semester_payment_start');
			$semester = ($session >= $paymentValidation) ? true : false;

			if($semester){
				$query = "SELECT distinct d.name, count(distinct a.student_id) as total from transaction a  join academic_record c on c.student_id = a.student_id join programme d on d.id = a.programme_id where a.payment_id in ('1','2') and a.payment_status in ('01', '00') and a.session = ? and c.year_of_entry = ? group by d.name order by d.name asc";
			}else{
				$query = "SELECT distinct d.name, count(distinct a.student_id) as total from transaction a  join academic_record c on c.student_id = a.student_id join programme d on d.id = a.programme_id where a.payment_id = '1' and a.payment_status in ('01', '00') and a.session = ? and c.year_of_entry = ? group by d.name order by d.name asc";
			}
			$query = $this->db->query($query, [$session, $session]);
		}

		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array();
	}

	/**
	 * @param mixed $session
	 * @param mixed $type
	 * @return array|<missing>
	 */
	public function loadApplicantAge($session, $type = 'general'): array
	{

		if ($type == 'general') {
			$query = "SELECT age, sum(total) as total from (
                SELECT distinct count(*) as total, TIMESTAMPDIFF(YEAR, dob, CURDATE()) AS age from applicants a join applicant_transaction b on b.applicant_id = a.id where b.payment_status in ('00','01') and dob is not NULL and dob != '' and b.session = ? group by age having age is not null
                UNION
                SELECT distinct count(*) as total, TIMESTAMPDIFF(YEAR, dob, CURDATE()) AS age from applicant_post_utme c where dob is not NULL and dob != '' and c.session_id = ? group by age having age is not null
            ) x group by age order by total
            ";
			$query = $this->db->query($query, [$session, $session]);
		} else {
			$query = "SELECT name, age, ANY_VALUE(total) as total from (
            SELECT distinct c.name as name, TIMESTAMPDIFF(YEAR, dob, CURDATE()) as age, count(*) as total from applicants a join applicant_transaction b on b.applicant_id = a.id join programme c on c.id = a.programme_id where b.payment_status in ('00','01') and dob is not NULL and dob != '' and b.session = ? group by name, age having age is not null
            UNION
            SELECT distinct e.name as name, TIMESTAMPDIFF(YEAR, dob, CURDATE()) as age, count(*) as total from applicant_post_utme d join programme e on e.id = d.programme_id where dob is not NULL and dob != '' and d.session_id = ? group by name, age having age is not null
            ) x group by name, age order by name";
			$query = $this->db->query($query, [$session, $session]);
		}

		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array();
	}

	/**
	 * @param mixed $session
	 * @return array<int,mixed>
	 */
	public function getAgeByProgramme($session): array
	{
		$result = $this->loadApplicantAge($session, 'programme');

		$content = [];
		$programmeAge = AdminModelTrait::groupRelatedDataToAssoc($result);
		$programmeName = AdminModelTrait::getUniqueNameFromAssoc($result);
		$content1 = $programmeName;

		if (!empty($programmeAge)) {
			foreach ($programmeName as $name) {
				$content[] = [
					'name' => $name,
					'value' => AdminModelTrait::groupDataByAge($programmeAge[$name]),
				];
			}
		}
		return [$content, $content1];
	}

	/**
	 * @param mixed $session
	 * @param mixed $type
	 * @return array|<missing>
	 */
	public function loadApplicationGender($session, $type = 'general'): array
	{
		if ($type == 'general') {
			$query = "SELECT name, sum(total) as total from (
                SELECT distinct CASE
                    WHEN lower(gender) = 'male' THEN 'Male'
                    WHEN lower(gender) = 'female' THEN 'Female'
                    ELSE 'Null' END as name, count(*) as total from applicants a join
                applicant_transaction b on b.applicant_id = a.id where
                b.payment_status in ('00','01') and b.session = ? group by name
                UNION
                SELECT distinct CASE
                    WHEN lower(gender) = 'male' THEN 'Male'
                    WHEN lower(gender) = 'female' THEN 'Female'
                    ELSE 'Null' END as name, count(*) as total from applicant_post_utme c where c.session_id = ? and c.programme_id <> ''
                    group by name
            ) x group by name order by name ";
			$query = $this->db->query($query, [$session, $session]);
		} else {
			$query = "SELECT distinct name, gender, sum(total) as total from (
                SELECT distinct c.name as name, CASE
                    WHEN lower(gender) = 'male' THEN 'Male'
                    WHEN lower(gender) = 'female' THEN 'Female'
                    ELSE 'Null' END as gender, count(*) as total
                from applicants a join applicant_transaction b on b.applicant_id = a.id join programme c on
                c.id = a.programme_id where b.payment_status in ('00','01') and
                b.session = ? group by name, gender
                UNION
                SELECT distinct e.name as name, CASE
                    WHEN lower(gender) = 'male' THEN 'Male'
                    WHEN lower(gender) = 'female' THEN 'Female'
                    ELSE 'Null' END as gender, count(*) as total
                from applicant_post_utme d join programme e on
                e.id = d.programme_id where d.session_id = ? group by name, gender
            ) x group by name, gender order by name";
			$query = $this->db->query($query, [$session, $session]);
		}

		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array();
	}

	/**
	 * @param mixed $session
	 * @return array<int,array>
	 */
	public function applicationGenderProgramme($session): array
	{
		$response = $this->loadApplicationGender($session, 'programme');
		$content = [];
		$content1 = [];
		if (count($response) > 0) {
			foreach ($response as $cont) {
				$tempProg = AdminModelTrait::removeSingleProgrammePrefix($cont['name']);
				if (in_array($tempProg, $content1) === false) {
					$content1[] = $tempProg;
				}

				$payload = [
					'name' => $cont['name'],
					'gender' => strtolower($cont['gender']),
					'total' => $cont['total'],
				];
				$content[] = $payload;
			}
		}
		return [$content, $content1];
	}

	/**
	 * @param mixed $session
	 * @return array|<missing>
	 */
	public function loadApplicationEntryMode($session): array
	{
		$query = " SELECT name, sum(total) as total from (
            SELECT distinct if(entry_mode = '', 'Others', entry_mode) as name,
            count(distinct a.applicant_id) as total from applicant_transaction a join applicants b on
            a.applicant_id = b.id where a.session = ? and a.payment_status in ('00', '01') group by name
            UNION
            SELECT distinct if(entry_mode = '', 'Others', entry_mode) as name,
            count(distinct c.applicant_id) as total from applicant_post_utme c  where c.session_id = ? and c.programme_id <> '' group by name
            ) x group by name order by name asc";
		$query = $this->db->query($query, [$session, $session]);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array();
	}

	/**
	 * @param mixed $session
	 * @return array|<missing>
	 */
	public function loadApplicationReport($session): array
	{
		$query = "SELECT name, sum(total) as total from (
            SELECT distinct d.name as name, count(distinct a.applicant_id) as total from applicant_transaction a join applicants b on a.applicant_id = b.id join programme c on c.id = b.programme_id join faculty d on d.id = c.faculty_id where a.session = ? and a.payment_status in ('00', '01') group by d.name
            UNION
            SELECT distinct g.name as name, count(distinct e.applicant_id) as total from applicant_post_utme e join programme f on f.id = e.programme_id join faculty g on g.id = f.faculty_id where e.session_id = ? group by g.name
        ) x group by name order by name asc";
		$query = $this->db->query($query, [$session, $session]);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array();
	}

}
