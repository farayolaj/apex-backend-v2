<?php
if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

require_once APPPATH . 'constants/OutflowStatus.php';
require_once APPPATH . 'constants/PaymentFeeDescription.php';
require_once APPPATH . 'traits/AdminModelTrait.php';

/**
 *
 */
class AdminModel extends CI_Model
{
	private $previousImagePath;
	private $httpURL;

	public function __construct()
	{
		parent::__construct();
		$this->previousImagePath = $_SERVER['HTTP_HOST'] == 'localhost' ? '../stagingui/assets/images/student/passports/' : '../assets/images/student/passports/';
		$this->httpURL = $_SERVER['HTTP_HOST'] == 'localhost' ? 'http://localhost/stagingui/assets/images/student/passports/' : 'https://dlcoffice.ui.edu.ng/assets/images/student/passports/';
	}

	public function downloadPhotos()
	{
		$this->load->library('zip');
		$filters = isset($_POST['filter']) ? $_POST['filter'] : null;
		$selected = isset($_POST['selected']) ? $_POST['selected'] : null;
		$paths = [];
		if ($selected && count($selected) > 0) {
			$paths = $this->getFileListByMatric($selected);
		} else if ($filters) {
			$paths = $this->getFileListByFilter($filters);
		}

		$destPath = FCPATH . "temp/download_passport";
		// cleanup the files first
		delete_files($destPath . "/");
		$currentUser = $this->webSessionManager->currentAPIUser();
		$filename = $currentUser->firstname . '_' . $currentUser->lastname . '_students_passports.zip';
		$name = $destPath . '/' . $filename;
		$zip = new ZipArchive();
		$status = $zip->open($name, ZipArchive::OVERWRITE);
		if ($status !== true) {
			$zip->open($name, ZipArchive::CREATE);
		}

		$totalCount = 0;
		foreach (useGenerators($paths) as $path) {
			if (!trim($path['passport'])) {
				continue;
			}

			$fullpath = studentImagePathDirectory($path['passport']);
			if (!file_exists($fullpath)) {
				continue;
			}
			$ext = pathinfo($fullpath, PATHINFO_EXTENSION);
			$zip->addFile($fullpath, $path['matric_number'] . '.' . $ext);
			$totalCount++;
		}

		$zip->close();
		if ($totalCount <= 0) {
			return false;
		}
		return generateDownloadLink($name, 'temp/download_passport', 'direct_link_passport');
	}

	public function getStudentPhotos(): array
	{
		$pageSize = (isset($_GET['len']) && $_GET['len'] && $_GET['len'] > 0) ? $_GET['len'] : 50;
		$page = (isset($_GET['start']) && $_GET['start']) ? $_GET['start'] : 1;

		$session = isset($_GET['session']) ? $_GET['session'] : null;
		$level = isset($_GET['level']) ? $_GET['level'] : false;
		$program = isset($_GET['programme']) ? $_GET['programme'] : false;
		$course = isset($_GET['course']) ? $_GET['course'] : false;
		$entry = isset($_GET['entry_year']) ? $_GET['entry_year'] : false;
		$dept = isset($_GET['department']) ? $_GET['department'] : false;
		$search = isset($_GET['q']) ? $_GET['q'] : false;

		return $this->loadPhotosByFilter($search, $session, $entry, $level, $dept, $program, $course, $page, $pageSize);
	}

	/**
	 * @param mixed $selected
	 * @return array|<missing>
	 */
	private function getFileListByMatric($selected): array
	{
		if (!$selected || count($selected) == 0) {
			return [];
		}

		$selected = array_map(function ($item) {
			return "'$item'";
		}, $selected);
		$inQuery = implode(',', $selected);
		$query = "select distinct matric_number,passport from students join academic_record on academic_record.student_id=students.id where matric_number in ($inQuery)";
		$result = $this->db->query($query);
		return $result->result_array();
	}

	/**
	 * @param mixed $filters
	 */
	private function getFileListByFilter($filters): array
	{
		$pageSize = (isset($_GET['len']) && $_GET['len'] && $_GET['len'] > 0) ? $_GET['len'] : null;
		$page = (isset($_GET['start']) && $_GET['start']) ? $_GET['start'] : 1;

		$session = isset($filters['session']) ? $filters['session'] : null;
		$level = isset($filters['levels']) ? $filters['levels'] : false;
		$program = isset($filters['programme']) ? $filters['programme'] : false;
		$course = isset($filters['course']) ? $filters['course'] : false;
		$entry = isset($filters['entry_year']) ? $filters['entry_year'] : false;
		$dept = isset($filters['department']) ? $filters['department'] : false;
		$search = isset($filters['searchPhotos']) ? $filters['searchPhotos'] : false;

		$result = $this->loadPhotosByFilter($search, $session, $entry, $level, $dept, $program, $course, false, false, false);
		return $result['table_data'];
	}

	private function loadPhotosByFilter($search, $session, $entry, $level, $dept, $program, $course, $page = false, $pageSize = false, $returnPath = true): array
	{
		$where = '';
		$param = [];
		if ($session) {
			$where .= ($where ? ' and ' : ' where ') . " e.payment_id=1 and e.payment_status in ('00', '01') and e.session=?";
			$param[] = $this->db->conn_id->escape_string($session);
		}
		if ($entry) {
			$where .= ($where ? ' and ' : ' where ') . " b.session_of_admission=?";
			$param[] = $this->db->conn_id->escape_string($entry);
		}
		if ($level) {
			if ($session) {
				$where .= ($where ? ' and ' : ' where ') . " e.level=?";
			} else {
				$where .= ($where ? ' and ' : ' where ') . " b.current_level=?";
			}
			$param[] = $this->db->conn_id->escape_string($level);
		}
		if ($dept) {
			$where .= ($where ? ' and ' : ' where ') . " f.id=?";
			$param[] = $this->db->conn_id->escape_string($dept);
		}
		if ($program) {
			if ($session) {
				$where .= ($where ? ' and ' : ' where ') . " e.programme_id=?";
			} else {
				$where .= ($where ? ' and ' : ' where ') . " b.programme_id=?";
			}
			$param[] = $this->db->conn_id->escape_string($program);
		}
		if ($course) {
			$where .= ($where ? ' and ' : ' where ') . " c.course_id=?";
			$param[] = $this->db->conn_id->escape_string($course);
		}
		if ($search) {
			$search = $this->db->conn_id->escape_string($search);
			$where .= ($where ? ' and ' : ' where ') . " concat_ws(' ',firstname,othernames,lastname) like '%$search%'
			or matric_number like '%$search%' or user_login like '%$search%'";
		}

		$limit = '';
		if ($page > 1) {
			$limit = " limit $page, $pageSize";
		} else if ($pageSize) {
			$limit = " limit $pageSize";
		}

		if ($session) {
			$query = "SELECT distinct SQL_CALC_FOUND_ROWS a.id as student_id,matric_number,passport,
			concat_ws(' ',firstname,othernames,lastname) as fullname,e.level,
			user_login as email from transaction e join students a on e.student_id=a.id join academic_record b on b.student_id= a.id left join course_enrollment c on
			c.student_id=a.id join programme d on d.id=e.programme_id join department f on f.id = d.department_id $where order by matric_number $limit";
		} else {
			$query = "SELECT distinct SQL_CALC_FOUND_ROWS a.id as student_id,matric_number,passport,concat_ws(' ',firstname,othernames,lastname) as fullname,
			user_login as email,b.current_level as level from students a join academic_record b on b.student_id= a.id left join course_enrollment c on
			c.student_id=a.id left join programme d on d.id=b.programme_id join department f on f.id = d.department_id
			$where order by matric_number $limit";
		}

		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$result = $this->db->query($query, $param);
		$result = $result->result_array();

		if ($returnPath) {
			$result = $this->filterAvailablePassports($result);
		}
		$rows = $this->db->query($query2);
		$rows = $rows->result_array();
		$totalCount = $rows[0]['totalCount'];
		return ['paging' => $totalCount, 'table_data' => $result];
	}

	/**
	 * @param mixed $results
	 * @return mixed
	 */
	private function filterAvailablePassports($results)
	{
		for ($i = 0; $i < count($results); $i++) {
			$current = $results[$i];
			if (!trim($current['passport'])) {
				$results[$i]['passport'] = '';
				continue;
			}

			$imagePath = studentImagePathDirectory($current['passport']);
			if (!file_exists($imagePath) || is_dir($imagePath)) {
				$results[$i]['passport'] = '';
				continue;
			}
			$results[$i]['passport'] = studentImagePath($current['passport']);
		}
		return $results;
	}

	/**
	 * @return array
	 */
	public function getCourseDashboardInfo(): array
	{
		$result = [];
		$session = isset($_GET['ses']) ? $_GET['ses'] : getCurrentSession($this->db);
		$level = isset($_GET['lv']) ? $_GET['lv'] : false;
		$program = isset($_GET['pg']) ? $_GET['pg'] : false;
		if ($session && !is_numeric($session)) {
			$session = getIDByName($this, 'sessions', 'date', $session);
		}
		if ($level) {
			$level = $this->findLevelKey($level);
		}
		if ($program && !is_numeric($program)) {
			$program = getIDByName($this, 'programme', 'name', $program);
		}
		// $result['all_sessions']=$this->loadSessions();
		// $result['all_programs']=$this->loadPrograms();
		// $result['all_levels']=$this->loadLevels();
		$userType = $this->getUserType($session);
		$result['courses_info'] = $this->getCourses($userType, $session, $level, $program);
		return $result;
	}

	/**
	 * @param mixed $session
	 * @return string
	 */
	private function getUserType($session): string
	{
		//the data to provide this functionality is not available yet
		return 'admin';
	}

	/**
	 * @param mixed $userType
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $program
	 * @return array|<missing>
	 */
	private function getCourses($userType, $session, $level, $program): array
	{
		//this query will be editted later though
		$query = "select * from courses";
		$courses = $this->db->query($query);
		$courses = $courses->result_array();
		if (!$courses) {
			return [];
		}
		for ($i = 0; $i < count($courses); $i++) {
			$courses[$i]['more_info'] = $this->getCourseInfo($courses[$i], $session, $level, $program);
		}
		return $courses;
	}

	/**
	 * @param mixed $course
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $program
	 * @return array
	 */
	private function getCourseInfo($course, $session, $level, $program): array
	{
		$result = [];
		$result['personnel'] = $this->getCoursePersonnel($course['id'], $session);
		$result['course_content'] = $this->getCourseContentInfo($course, $session);
		$result['registration_info'] = $this->getCourseRegistrationInfo($course['id'], $session, $level, $program);
		$result['result_info'] = $this->getCourseResultInfo($course['id'], $session, $level, $program);
		// print_r($course);exit;
		return $result;
	}

	/**
	 * @param mixed $course
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $program
	 */
	private function getCourseResultInfo($course, $session, $level, $program)
	{
		$extra = " where course_enrollment.course_id=? ";
		$param = [$course];
		if ($session) {
			$extra .= " and  course_enrollment.session_id=?";
			$param[] = $session;
		}
		if ($level) {
			$extra .= $extra ? ' and course_enrollment.student_level=?' : " where exam_record.student_level=? ";
			$param[] = $level;
		}
		if ($program) {
			$extra .= $extra ? ' and academic_record.programme_id=?' : " where academic_record.programme_id =? ";
			$param[] = $program;
		}
		$query = "select count(total_score) as scored,count(if(total_score=null,1,null)) as unscored,min(total_score) as min_score,max(total_score) as max_score, avg(total_score) as avg_score from course_enrollment join academic_record on academic_record.student_id=course_enrollment.student_id $extra ";
		$result = $this->db->query($query, $param);
		$result = $result->result_array();
		return $result[0];
	}

	/**
	 * @param mixed $course
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $program
	 */
	private function getCourseRegistrationInfo($course, $session, $level, $program)
	{
		$extra = " where course_enrollment.course_id=? ";
		$param = [$course];
		if ($session) {
			$extra .= " and  course_enrollment.session_id=?";
			$param[] = $session;
		}
		if ($level) {
			$extra .= $extra ? ' and course_enrollment.student_level=?' : " where exam_record.student_level=? ";
			$param[] = $level;
		}
		if ($program) {
			$extra .= $extra ? ' and academic_record.programme_id=?' : " where academic_record.programme_id =? ";
			$param[] = $program;
		}
		$partial = "(YEAR(NOW())-
		EXTRACT(YEAR FROM IF(STR_TO_DATE(dob,'%d/%m/%Y'),STR_TO_DATE(dob,'%d/%m/%Y'),str_to_date(dob,'%d-%m-Y'))))";
		$query = "select count(*) as total_registered,count(if(trim(lower(gender))='male',1,null)) as male_count,count(if(trim(lower(gender))='female',1,null)) as female_count ,min($partial) as min_age,max($partial) max_age,avg($partial) as avg_age from  course_enrollment join students on students.id=course_enrollment.student_id join academic_record on students.id=academic_record.student_id $extra";
		$result = $this->db->query($query, $param);
		$result = $result->result_array();
		return $result[0];
	}

	/**
	 * @param mixed $course
	 * @return array<string,mixed>
	 */
	private function getCourseContentInfo($course)
	{
		$result = [];
		$result['course_guide'] = $course['course_guide_url'];
		$result['course_manual'] = false;
		$result['videos'] = false;
		$result['audio'] = false;
		return $result;
	}

	/**
	 * @param mixed $course
	 * @param mixed $session
	 * @return array<string,mixed>
	 */
	private function getCoursePersonnel($course, $session)
	{
		$session = 1;
		$result = ['course_manager' => false, 'lecturers' => [], 'e-tutor' => false];
		$query = "select * from course_manager where course_id=? and session_id=? limit 1";
		$temp = $this->db->query($query, [$course, $session]);
		$temp = $temp->result_array();
		if (!$temp) {
			return $result;
		}
		$temp = $temp[0];
		$result['course_manager'] = $this->loadCourseManager($temp['course_manager_id']);
		$result['lecturers'] = $this->loadCourseLecturers($temp['course_lecturer_id']);
		$result['e-tutors'] = $this->loadCourseTutors($temp);
		return $result;
	}

	/**
	 * @param mixed $id
	 * @return bool|<missing>
	 */
	private function loadCourseManager($id)
	{
		$query = "select * from users_new where id=?";
		$result = $this->db->query($query, [$id]);
		$result = $result->result_array();
		if (!$result) {
			return false;
		}
		return $result[0];
	}

	/**
	 * @param mixed $lecturers
	 * @return array|<missing>
	 */
	private function loadCourseLecturers($lecturers)
	{
		$converted = json_decode($lecturers);
		if (!$converted) {
			return [];
		}
		$str = str_replace('[', '(', $lecturers);
		$str = str_replace(']', ')', $str);
		$query = "select id,title,user_rank,user_email,user_phone,concat_ws(' ',firstname,othernames,lastname) as fullname,avatar from users where id in $str";
		$result = $this->db->query($query);
		$result = $result->result_array();
		return $result;
	}

	/**
	 * @param mixed $tutors
	 * @return array
	 */
	private function loadCourseTutors($tutors)
	{
		//there is not database information about tutors for now so just retur empty array for now
		return [];
	}

	/**
	 * @param mixed $user
	 * @return array<string,mixed>
	 */
	public function getDashboardInfo($user)
	{
		$result = [];
		$session = isset($_GET['ses']) ? $_GET['ses'] : false;
		$level = isset($_GET['lv']) ? $_GET['lv'] : false;
		$program = isset($_GET['pg']) ? $_GET['pg'] : false;
		if ($session && !is_numeric($session)) {
			$session = getIDByName($this, 'sessions', 'date', $session);
		}
		if ($level) {
			$level = $this->findLevelKey($level);
		}
		if ($program && !is_numeric($program)) {
			$program = getIDByName($this, 'programme', 'name', $program);
		}
		$result['fullname'] = $user->fullname;
		$result['abbr'] = $user->abbr;
		$tempUser = ['fullname' => $user->fullname, 'abbr' => $user->abbr, 'photo' => $user->avatar];
		$result['user'] = $tempUser;
		//get payment information here
		$transaction = $this->getTransaction($session, $level, $program);
		$transaction['total_amount'] = number_format($transaction['total_amount'], 2);
		$result['transaction'] = $transaction;
		$result['users'] = [];
		$result['users']['student_count'] = $this->getActiveStudentCount($session, $level, $program);
		$result['users']['male_count'] = $this->getActiveMaleStudentCount($session, $level, $program);
		$result['users']['female_count'] = $this->getActiveFemaleStudentCount($session, $level, $program);
		$result['users']['total_lecturer'] = $this->getTotalLecturer($session, $level, $program);
		$result['courses'] = $this->getCourseInformation($session, $level, $program);
		// $result['all_sessions']=$this->loadSessions();
		// $result['all_programs']=$this->loadPrograms();
		// $result['all_levels']=$this->loadLevels();
		$result['users']['student_distribution'] = $this->loadStudentDistribution($session, $level, $program);
		$result['admissions'] = $this->getAdmissionInformation($session, $level, $program);
		return $result;
	}

	/**
	 * @param mixed $val
	 * @return mixed|string
	 */
	private function findLevelKey($val)
	{
		$levels = $this->loadLevels();
		foreach ($levels as $key => $value) {
			if ($value == $val) {
				return $key;
			}
		}
		return '';
	}

	/**
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $program
	 * @return array<string,mixed>
	 */
	private function getCourseInformation($session, $level, $program)
	{
		$result = [];
		$result['count'] = $this->getCourseCount($session, $level, $program);
		$result['num_with_registration'] = $this->getCourseWithRegistration($session, $level, $program);
		$result['num_with_score'] = $this->getNumberOfCourseWithScore($session, $level, $program);
		$result['total_courses_registered'] = $this->getTotalCourseRegistered($session, $level, $program);
		$result['total_student_registration'] = $this->getTotalStudentRegistration($session, $level, $program);
		$result['total_score_feedback'] = $this->getTotalScoreWithFeedback($session, $level, $program);
		$result['cgpas'] = $this->getHighestCGPA($session, $level, $program);
		$result['course_wide_average'] = 0;
		if ($result['num_with_registration']) {
			$result['course_wide_average'] = number_format($result['num_with_score'] / $result['num_with_registration'], 3) * 100;
		}
		$result['student_level_course_average'] = 0;
		if ($result['total_student_registration']) {
			$result['student_level_course_average'] = number_format($result['num_with_score'] / $result['total_student_registration'], 3) * 100;
		}
		return $result;
	}

	/**
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $program
	 * @return string
	 */
	private function getTotalScoreWithFeedback($session, $level, $program)
	{
		//there is no information about how to provide and produce feedback at this time
		// so return not available and take care of that later
		return "NA";
	}

	/**
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $program
	 * @param mixed $limit
	 */
	private function getHighestCGPA($session, $level, $program, $limit = 10)
	{
		$extra = "";
		$param = [];
		if ($session) {
			$extra = " where  exam_record.session_id=?";
			$param[] = $session;
		}
		if ($level) {
			$extra .= $extra ? ' and exam_record.student_level=?' : " where exam_record.student_level=? ";
			$param[] = $level;
		}
		if ($program) {
			$extra .= $extra ? ' and academic_record.programme_id=?' : " where academic_record.programme_id =? ";
			$param[] = $program;
		}
		$query = "select  concat_ws(' ',firstname,othernames,lastname) as student_name,cgpa from exam_record join academic_record on academic_record.student_id=exam_record.student_id join students on students.id=academic_record.student_id $extra order by cgpa desc limit $limit ";
		$result = $this->db->query($query, $param);
		$result = $result->result_array();
		return $result;
	}

	/*
		 * @param mixed $session	private function getCourseWid($value='')
		{
		# code...
		}* @param mixed $semester
		 * @param mixed $level
		 * @param mixed $program
	*/
	private function getTotalStudentRegistration($session, $semester = false, $level, $program)
	{
		$extra = '';
		$param = [];
		if ($session) {
			$extra = " where  course_enrollment.session_id=?";
			$param[] = $session;
		}
		if ($semester) {
			$extra .= ($extra) ? ' and course_enrollment.semester=?' : ' where course_enrollment.semester=?';
			$param[] = $semester;
		}
		if ($level) {
			$extra .= $extra ? ' and course_enrollment.student_level=?' : " where course_enrollment.student_level=? ";
			$param[] = $level;
		}
		if ($program) {
			$extra .= $extra ? ' and academic_record.programme_id=?' : " where academic_record.programme_id =? ";
			$param[] = $program;
		}
		$query = "select count(*) as  num from course_enrollment join academic_record on academic_record.student_id=course_enrollment.student_id   $extra";
		$result = $this->db->query($query, $param);
		$result = $result->result_array();
		return $result[0]['num'];
	}

	/**
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $program
	 */
	private function getTotalCourseRegistered($session, $level, $program)
	{
		$extra = '';
		$param = [];
		if ($session) {
			$extra = " where  course_enrollment.session_id=?";
			$param[] = $session;
		}
		if ($level) {
			$extra .= $extra ? ' and course_enrollment.student_level=?' : " where course_enrollment.student_level=? ";
			$param[] = $level;
		}
		if ($program) {
			$extra .= $extra ? ' and academic_record.programme_id=?' : " where academic_record.programme_id =? ";
			$param[] = $program;
		}
		$query = "select count(distinct course_id) as  num from course_enrollment join academic_record on academic_record.student_id=course_enrollment.student_id   $extra";
		$result = $this->db->query($query, $param);
		$result = $result->result_array();
		return $result[0]['num'];
	}

	/**
	 * @param mixed $session
	 * @param mixed $semester
	 * @param mixed $level
	 * @param mixed $program
	 */
	private function getNumberOfCourseWithScore($session, $semester = false, $level = false, $program = false)
	{
		$extra = '';
		$param = [];
		if ($session) {
			$extra = " and  a.session_id=?";
			$param[] = $session;
		}
		if ($semester) {
			$extra .= ($extra) ? ' and a.semester=?' : ' where a.semester=?';
			$param[] = $semester;
		}
		if ($level) {
			$extra .= ' and a.student_level=?';
			$param[] = $level;
		}
		if ($program) {
			$extra .= ' and b.programme_id=?';
			$param[] = $program;
		}

		if (isset($_GET['dashboard_department'])) {
			$department = $_GET['dashboard_department'];
			$query = "SELECT count(distinct course_id) as num from course_enrollment a join academic_record b on
			b.student_id=a.student_id right join programme c on c.id = b.programme_id right join department d on d.id = c.department_id
            where total_score is not null and total_score <> '' and d.id = '$department' $extra";
		} else {
			$query = "SELECT count(distinct course_id) as num from course_enrollment a join academic_record b on
			b.student_id=a.student_id where total_score is not null and total_score <> '' $extra";
		}
		$result = $this->db->query($query, $param);
		$result = $result->result_array();
		return $result[0]['num'];
	}

	/**
	 * @param mixed $session
	 * @param mixed $semester
	 * @param mixed $level
	 * @param mixed $program
	 */
	private function getCourseWithRegistration($session, $semester = false, $level = false, $program = false)
	{
		$extra = '';
		$param = [];
		if ($session) {
			$extra = " where course_enrollment.session_id=?";
			$param[] = $session;
		}
		if ($semester) {
			$extra .= $extra ? ' and course_enrollment.semester=?' : " where course_enrollment.semester=?";
			$param[] = $semester;
		}
		if ($level) {
			$extra .= $extra ? ' and course_enrollment.student_level=?' : " where course_enrollment.student_level=?";
			$param[] = $level;
		}
		if ($program) {
			$extra .= $extra ? ' and academic_record.programme_id=?' : " where academic_record.programme_id=?";
			$param[] = $program;
		}
		$query = "select count(distinct course_id) as  num from course_enrollment join academic_record on academic_record.student_id=course_enrollment.student_id $extra";
		$result = $this->db->query($query, $param);
		$result = $result->result_array();
		return $result[0]['num'];
	}

	/**
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $program
	 * @return array<string,mixed>
	 */
	private function getAdmissionInformation($session, $level, $program)
	{
		$session = $session ? $session : getCurrentSession($this->db);
		$result = [];
		//get the number of admitted applicant foirst
		$result['num_admitted'] = $this->getNumberAdmitted($session, $level, $program);
		$result['num_verified'] = $this->getNumberWithBooleanField('is_verified', $session, $level, $program);
		$result['num_screened'] = 0; //$this->getNumberWithBooleanField('is_screened',$session,$level,$program);
		$result['total_program'] = $this->getNumberAdmissionProgram($session, $level);
		return $result;

	}

	/**
	 * @param mixed $field
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $program
	 */
	private function getNumberWithBooleanField($field, $session, $level, $program)
	{
		$extra = '';
		$param = [];
		if ($session) {
			$extra = " and academic_record.year_of_entry=?";
			$param[] = $session;
		}
		if ($level) {
			$extra .= ' and academic_record.level_of_admission=?';
			$param[] = $level;
		}
		if ($program) {
			$extra .= ' and academic_record.programme_id=?';
			$param[] = $program;
		}
		$query = "select count(*) as num from students join academic_record on academic_record.student_id=students.id where students.$field=1 $extra";
		$result = $this->db->query($query, $param);
		$result = $result->result_array();
		return $result[0]['num'];
	}

	/**
	 * @param mixed $session
	 * @param mixed $level
	 */
	private function getNumberAdmissionProgram($session, $level)
	{
		$extra = '';
		$param = [];
		if ($session) {
			$extra = " where academic_record.year_of_entry=?";
			$param[] = $session;
		}
		if ($level) {
			$extra .= ($extra) ? ' and academic_record.level_of_admission=?' : ' where academic_record.level_of_admission=?';
			$param[] = $level;
		}
		$query = "select count(distinct programme_id) as num from students join academic_record on academic_record.student_id=students.id  $extra";
		$result = $this->db->query($query, $param);
		$result = $result->result_array();
		return $result[0]['num'];
	}

	/**
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $program
	 */
	private function getNumberAdmitted($session, $level, $program)
	{
		$extra = '';
		$param = [];
		if ($session) {
			$extra = " where academic_record.year_of_entry=?";
			$param[] = $session;
		}
		if ($level) {
			$extra .= ($extra) ? ' and academic_record.level_of_admission=?' : ' where academic_record.level_of_admission=?';
			$param[] = $level;
		}
		if ($program) {
			$extra .= ($extra) ? ' and academic_record.programme_id=?' : ' where academic_record.programme_id=?';
			$param[] = $program;
		}
		$query = "select count(*) as num from students join academic_record on academic_record.student_id=students.id  $extra";
		$result = $this->db->query($query, $param);
		$result = $result->result_array();
		return $result[0]['num'];
	}

	/**
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $program
	 * @return array
	 */
	private function loadStudentDistribution($session = false, $level = false, $program = false)
	{
		$result = [];
		$result['age_distribution'] = $this->loadStudentAgeDistribution($session, $level, $program);
		$result['state_distribution'] = $this->loadStateDistribution($session, $level, $program);
		$result['department_distribution'] = $this->loadDepartmentDistribution($session, $level);
		$result['faculty_distribution'] = $this->loadFacultyDistribution($session, $level);
		return $result;
	}

	/**
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $program
	 * @return array
	 */
	private function loadStateDistribution($session, $level, $program)
	{
		$extra = '';
		$param = [];
		if ($session) {
			$extra = " where exam_record.session_id=?";
			$param[] = $session;
		}
		if ($level) {
			$extra .= ($extra) ? ' and exam_record.student_level=?' : ' where exam_record.student_level=?';
			$param[] = $level;
		}
		if ($program) {
			$extra .= ($extra) ? ' and academic_record.programme_id=?' : ' where academic_record.programme_id=?';
			$param[] = $program;
		}

		$query = "select count(*) as num,state_of_origin from students join academic_record on academic_record.student_id=students.id join exam_record on exam_record.student_id=students.id $extra group by state_of_origin ";
		$result = $this->db->query($query, $param);
		$result = $result->result_array();
		if (!$result) {
			return [];
		}
		$return = [];
		foreach ($result as $key => $value) {
			$return[$value['state_of_origin']] = $value['num'];
		}
		return $return;
	}

	/**
	 * @param mixed $session
	 * @param mixed $level
	 * @return array
	 */
	private function loadFacultyDistribution($session, $level)
	{
		$extra = '';
		$param = [];
		if ($session) {
			$extra = " where exam_record.session_id=?";
			$param[] = $session;
		}
		if ($level) {
			$extra .= ($extra) ? ' and exam_record.student_level=?' : ' where exam_record.student_level=?';
			$param[] = $level;
		}

		$query = "select count(*) as num,faculty.name from students join academic_record on academic_record.student_id=students.id join exam_record on exam_record.student_id=students.id join programme on academic_record.programme_id=programme.id join department on department.id=programme.department_id join faculty on faculty.id=department.faculty_id $extra group by faculty.name ";
		$result = $this->db->query($query, $param);
		$result = $result->result_array();
		if (!$result) {
			return [];
		}
		$return = [];
		foreach ($result as $key => $value) {
			$return[$value['name']] = $value['num'];
		}
		return $return;
	}

	/**
	 * @param mixed $session
	 * @param mixed $level
	 * @return array
	 */
	private function loadDepartmentDistribution($session, $level)
	{
		$extra = '';
		$param = [];
		if ($session) {
			$extra = " where exam_record.session_id=?";
			$param[] = $session;
		}
		if ($level) {
			$extra .= ($extra) ? ' and exam_record.student_level=?' : ' where exam_record.student_level=?';
			$param[] = $level;
		}

		if (true) {
			$extra .= ($extra ? ' and ' : ' where ') . " department.type = 'academic'";
		}

		$query = "select count(*) as num,department.name from students join academic_record on academic_record.student_id=students.id join exam_record on exam_record.student_id=students.id join programme on academic_record.programme_id=programme.id join department on department.id=programme.department_id $extra group by department_id ";
		$result = $this->db->query($query, $param);
		$result = $result->result_array();
		if (!$result) {
			return [];
		}
		$return = [];
		foreach ($result as $key => $value) {
			$return[$value['name']] = $value['num'];
		}
		return $return;
	}

	/**
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $program
	 */
	private function loadAdmittedStudentCount($session = false, $level = false, $program = false)
	{
		$extra = '';
		$param = [];
		if ($session) {
			$extra = " where academic_record.session_of_admission=?";
			$param[] = $session;
		}
		if ($level) {
			$extra .= ($extra) ? ' and academic_record.level_of_admission=?' : ' where academic_record.level_of_admission=?';
			$param[] = $level;
		}
		if ($program) {
			$extra .= ($extra) ? ' and academic_record.programme_id=?' : ' where academic_record.programme_id=?';
			$param[] = $program;
		}
		$query = "SELECT count(*) as num from students join academic_record on academic_record.student_id=students.id join exam_record on exam_record.student_id=students.id $extra";
		$result = $this->db->query($query, $param);
		$result = $result->result_array();
		return $result[0]['num'];
	}

	/**
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $program
	 */
	private function loadVerifiedStudentCount($session = false, $level = false, $program = false)
	{
		$extra = '';
		$param = [];
		if ($session) {
			$extra = " where academic_record.session_of_admission=?";
			$param[] = $session;
		}
		if ($level) {
			$extra .= ($extra) ? ' and academic_record.level_of_admission=?' : ' where academic_record.level_of_admission=?';
			$param[] = $level;
		}
		if ($program) {
			$extra .= ($extra) ? ' and academic_record.programme_id=?' : ' where academic_record.programme_id=?';
			$param[] = $program;
		}
		$query = "SELECT count(*) as num from students join academic_record on academic_record.student_id=students.id join exam_record on exam_record.student_id=students.id $extra";
		$result = $this->db->query($query, $param);
		$result = $result->result_array();
		return $result[0]['num'];
	}

	/**
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $program
	 * @return array
	 */
	private function loadStudentAgeDistribution($session = false, $level = false, $program = false)
	{
		$extra = "";
		$param = [];
		if ($session) {
			$extra = ' where exam_record.session_id=?';
			$param[] = $session;
		}
		if ($level) {
			$extra .= ($extra) ? ' and exam_record.student_level=?' : ' where exam_record.student_level=?';
			$param[] = $level;
		}
		if ($program) {
			$extra .= ($extra) ? ' and academic_record.programme_id=?' : ' where academic_record.programme_id=?';
			$param[] = $program;
		}
		$query = "SELECT count(*) as num,(YEAR(NOW())-EXTRACT(YEAR FROM IF(STR_TO_DATE(dob,'%d/%m/%Y'),STR_TO_DATE(dob,'%d/%m/%Y'),
		str_to_date(dob,'%d-%m-Y')))) as age from students join exam_record on exam_record.student_id = students.id join
		academic_record on academic_record.student_id=students.id $extra group by age order by num";

		$result = $this->db->query($query, $param);
		$result = $result->result_array();
		//now place the result into category
		$range = [
			[1, 14],
			[15, 20],
			[21, 25],
			[26, 30],
			[31, 40],
			[41, 50],
			[51, 60],
		];
		$return = [];
		foreach ($result as $res) {
			$age = $res['age'];
			$num = $res['num'];
			$key = $this->getAgeKey($age, $range);
			if (!array_key_exists($key, $return)) {
				$return[$key] = 0;
			}
			$return[$key] = $return[$key] + $num;
		}
		ksort($return);
		return $return;
	}

	/**
	 * @param mixed $age
	 * @param mixed $range
	 * @return string
	 */
	private function getAgeKey($age, $range)
	{
		$result = '';
		if (!$age) {
			return 'unknown';
		}
		foreach ($range as $val) {
			$min = $val[0];
			$max = $val[1];
			if ($age >= $min && $age <= $max) {
				$return = implode('-', [$min, $max]);
				return $return;
			}
		}
		return '60 >';
	}

	private function loadSessions()
	{
		$query = "select id,date as name from sessions";
		$result = $this->db->query($query);
		return $result->result_array();
	}

	/**
	 * @param mixed $id
	 */
	private function loadProgramByDepartment($id)
	{
		$query = "select id,name  from programme where department_id=?";
		$result = $this->db->query($query, [$id]);
		$result = $result->result_array();
		return $result;
	}

	/**
	 * @return array
	 */
	private function loadPrograms()
	{
		$result = [];
		$query = "select id, name from department";
		$tempRes = $this->db->query($query);
		$tempRes = $tempRes->result_array();
		foreach ($tempRes as $res) {
			$result[$res['name']] = $this->loadProgramByDepartment($res['id']);
		}
		return $result;
	}

	private function loadCourseCodes()
	{
		$query = "SELECT distinct courses.id,concat_ws(' - ',courses.code,courses.title) as name  from courses where exists (select * from course_enrollment where course_id=courses.id)";
		$result = $this->db->query($query);
		$result = $result->result_array();
		return $result;
	}

	/**
	 * @return array
	 */
	private function loadLevels(): array
	{
		return ['' => '', 1 => 100, 2 => 200, 3 => 300, 4 => 400, 401 => 401, 402 => 402, 5 => 500, 501 => 501,
			502 => 502, 6 => 600, 7 => 700, 8 => 800, 9 => 900];
	}

	public function getTotalLecturer()
	{
		$query = "SELECT count(*) as num from users where is_lecturer=1";
		$result = $this->db->query($query);
		$result = $result->result_array();
		return $result[0]['num'];
	}

	/**
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $program
	 */
	public function getActiveStudentCount($session = false, $level = false, $program)
	{
		$extra = '';
		$param = [];
		if ($session) {
			$extra = " and exam_record.session_id=?";
			$param[] = $session;
		}
		if ($level) {
			$extra .= ' and exam_record.student_level=?';
			$param[] = $level;
		}
		if ($program) {
			$extra .= ' and academic_record.programme_id=?';
			$param[] = $program;
		}
		$query = "SELECT count(distinct students.id) as num from exam_record join students on students.id=exam_record.student_id join academic_record on academic_record.student_id=students.id where exam_record.active=1 $extra";
		$result = $this->db->query($query, $param);
		$result = $result->result_array();
		return $result[0]['num'];

	}

	/**
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $program
	 */
	public function getActiveMaleStudentCount($session, $level, $program)
	{
		$extra = '';
		$param = [];
		if ($session) {
			$extra = " and exam_record.session_id=?";
			$param[] = $session;
		}
		if ($level) {
			$extra .= ' and exam_record.student_level=?';
			$param[] = $level;
		}
		if ($program) {
			$extra .= ' and academic_record.programme_id=?';
			$param[] = $program;
		}
		$query = "SELECT count(distinct students.id) as num from exam_record join students on students.id=exam_record.student_id join academic_record on academic_record.student_id=students.id  where exam_record.active=1 and trim(lower(gender))='male' $extra";
		$result = $this->db->query($query, $param);
		$result = $result->result_array();
		return $result[0]['num'];
	}

	/**
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $program
	 */
	public function getActiveFemaleStudentCount($session, $level, $program)
	{
		$extra = '';
		$param = [];
		if ($session) {
			$extra = " and exam_record.session_id=?";
			$param[] = $session;
		}
		if ($level) {
			$extra .= ' and exam_record.student_level=?';
			$param[] = $level;
		}
		if ($program) {
			$extra .= ' and academic_record.programme_id=?';
			$param[] = $program;
		}
		$query = "SELECT count(distinct students.id) as num from exam_record join  students on students.id=exam_record.student_id join academic_record on academic_record.student_id=students.id where exam_record.active=1 and trim(lower(gender))='female' $extra";
		$result = $this->db->query($query, $param);
		$result = $result->result_array();
		return $result[0]['num'];

	}

	/**
	 * @param mixed $session
	 * @param mixed $semester
	 * @param mixed $level
	 * @param mixed $program
	 */
	public function getCourseCount($session, $semester = false, $level = false, $program = false)
	{
		$extra = '';
		$param = [];
		if ($session) {
			$extra = " where session_id=?";
			$param[] = $session;
		}
		if ($semester) {
			$extra .= ($extra) ? ' and b.semester=?' : ' where b.semester=?';
			$param[] = $semester;
		}
		if ($level) {
			$extra .= ($extra) ? ' and student_level=?' : ' where student_level=?';
			$param[] = $level;
		}
		if ($program) {
			$extra .= ($extra) ? ' and programme_id=?' : ' where programme_id=?';
			$param[] = $program;
		}

		if (isset($_GET['dashboard_department'])) {
			$department = $_GET['dashboard_department'];
			$extra .= ($extra) ? ' and e.id=?' : ' where e.id=?';
			$param[] = $department;
			$query = "SELECT count(distinct a.id) as num from courses a left join course_enrollment b on
			b.course_id=a.id left join academic_record c on c.student_id=b.student_id right join programme d on d.id=c.programme_id
			right join department e on e.id=d.department_id $extra";
		} else {
			$query = "SELECT count(distinct a.id) as num from courses a left join course_enrollment b on
			b.course_id=a.id left join academic_record c on c.student_id=b.student_id $extra";
		}
		$result = $this->db->query($query, $param);
		$result = $result->result_array();
		return $result[0]['num'];
	}

	/**
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $program
	 */
	public function getTransaction($session, $level, $program)
	{
		$extra = "";
		$param = [];
		if ($session) {
			$extra = ' and session=?';
			$param[] = $session;
		}
		if ($level) {
			$extra .= ' and level=?';
			$param[] = $level;
		}
		if ($program) {
			$extra .= ' and programme_id=?';
			$param[] = $program;
		}

		$query = "SELECT sum(total_amount) as total_amount, count(total_amount) as count from transaction where (payment_status='00' or payment_status='01') $extra";
		$result = $this->db->query($query, $param);
		$result = $result->result_array();
		return $result[0];
	}

	/**
	 * [currentSession description]
	 * @return array|<missing>* @param mixed $all
	 */
	public function currentSession($all = false)
	{
		$query = "SELECT a.id,a.date as value from sessions a join transaction b on b.session = a.id where a.active = '1' group by id, value order by value desc limit 1";
		$query = $this->db->query($query);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		$result = $query->result_array();
		if ($all) {
			return $result[0];
		}
		return $result[0]['id'];
	}

	/**
	 * @param mixed $table
	 * @return bool|<missing>
	 */
	public function currentTransactionSession($table = 'transaction')
	{
		$query = "SELECT settings_value as id from settings where settings_name = 'active_session_student_portal'";
		$result = $this->db->query($query);
		$result = $result->result_array();
		if (!$result) {
			return false;
		}
		return $result[0]['id'];
	}

	/**
	 * This is to count total application
	 * @return int|<missing>
	 */
	private function applicantInterested(string $session = null)
	{
		return $this->applicantModel->getApplicantStatsData('application', $session);
	}

	/**
	 * @return int|<missing>
	 */
	private function applicantRegistered(string $session = null)
	{
		return $this->applicantModel->getApplicantStatsData('registered', $session);
	}

	/**
	 * @return int|<missing>
	 */
	private function applicantIsAdmitted(string $session = null)
	{
		$this->load->model('applicantModel');
		return $this->applicantModel->getApplicantStatsData('admitted', $session);
	}

	/**
	 * @return int|<missing>
	 */
	private function applicantAccepted(string $session = null)
	{
		$this->load->model('applicantModel');
		return $this->applicantModel->getApplicantStatsData('accepted', $session);
	}

	/**
	 * This is to calc the active student using their sch fee transaction
	 * @param  [type] $session [description]
	 * @return int|<missing>@param mixed $session
	 */
	private function totalActiveStudent($session = null)
	{
		if (isset($_GET['dashboard_department'])) {
			$department = $_GET['dashboard_department'];
			$query = "SELECT distinct count(distinct b.student_id) as total from transaction b join academic_record c on c.student_id=b.student_id
			right join programme d on d.id = c.programme_id right join department e on e.id = d.department_id where b.session = ?
			and b.payment_id in ('1','2') and b.payment_status in ('00','01') and e.id = '$department'";
		} else {
			$query = "SELECT distinct count(distinct b.student_id) as total from transaction b join academic_record c on
			c.student_id=b.student_id where b.session = ? and b.payment_id in ('1','2') and b.payment_status in ('00','01') ";
		}

		$query = $this->db->query($query, [$session]);
		$result = 0;
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array()[0]['total'];
	}

	private function totalActiveStudent_backup($session = null)
	{
		$query = "SELECT distinct count(distinct b.student_id) as total from transaction b join academic_record c on c.student_id=b.student_id where b.session = ? and (b.payment_id = '1' or b.payment_id = '2') and b.payment_status in ('00','01')";
		$query = $this->db->query($query, [$session]);
		$result = 0;
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array()[0]['total'];
	}

	/**
	 * @param mixed $session
	 * @return int|<missing>
	 */
	private function totalActiveStudent2($session = null)
	{
		if (isset($_GET['dashboard_department'])) {
			$department = $_GET['dashboard_department'];
			$query = "SELECT distinct count(distinct a.student_id) as total from transaction a join academic_record b on
			a.student_id = b.student_id right join programme d on d.id = b.programme_id right join department e on e.id = d.department_id
            where a.session = ? and a.payment_id = '2' and (a.payment_status = '00' or a.payment_status = '01') and e.id = '$department'";
		} else {
			$query = "SELECT distinct count(distinct a.student_id) as total from transaction a join academic_record b on a.student_id = b.student_id
            where a.session = ? and a.payment_id = '2' and (a.payment_status = '00' or a.payment_status = '01')";
		}

		$query = $this->db->query($query, [$session]);
		$result = 0;
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array()[0]['total'];
	}

	/**
	 * This is to calc student that are not active in the sch
	 * @param  [type] $session [description]
	 * @return int@param mixed $session
	 */
	private function totalPassiveStudent($session = null)
	{
		if (isset($_GET['dashboard_department'])) {
			$department = $_GET['dashboard_department'];
			$query = "SELECT count(*) as total from students a join academic_record b on b.student_id = a.id right join programme c
			on c.id = b.programme_id right join department d on d.id = c.department_id where d.id = '$department'";
		} else {
			$query = "SELECT count(*) as total from students";
		}

		$query = $this->db->query($query, [$session]);
		$result = 0;
		if ($query->num_rows() <= 0) {
			return $result;
		}
		$result = $query->result_array()[0]['total'];
		$activeStudent = $this->totalActiveStudent($session);
		return $result - $activeStudent;
	}

	/**
	 * @param mixed $session
	 * @return int
	 */
	private function totalGraduatingStudent($session)
	{
		if (isset($_GET['dashboard_department'])) {
			$department = $_GET['dashboard_department'];
			$query = "SELECT sum(total) as total from
            (
			SELECT count(*) as total from students a join academic_record b on b.student_id = a.id right join
			programme c on c.id = b.programme_id right join department d on d.id = c.department_id where b.current_session = ?
			and d.id = '$department' and b.current_level in ('500','501') UNION SELECT count(*) as total from
			students a join academic_record b on b.student_id = a.id right join programme c on c.id = b.programme_id right join
			department d on d.id = c.department_id where b.current_session = ? and (b.current_level in ('400','401') and b.entry_mode = 'fast track' ) and d.id = '$department'
			) as x ";
		} else {
			$query = "SELECT sum(total) as total from
			(
			SELECT count(*) as total from students a join academic_record b on b.student_id = a.id where
			b.current_session = ? and b.current_level in ('500','501') UNION SELECT count(*) as total from
			students a join academic_record b on b.student_id = a.id where b.current_session = ? and
			(b.current_level in ('400','401') and b.entry_mode = 'fast track' )
			) as x ";
		}
		$query = $this->db->query($query, [$session, $session]);
		$result = 0;
		if ($query->num_rows() <= 0) {
			return $result;
		}
		$result = $query->result_array();
		return $result[0]['total'];
	}

	/**
	 * This is to get total payment distribution by day both from applicant_transaction
	 * and transaction table in the database.
	 * Eventually removed applicant_transaction from the query and left with transaction only
	 * @param mixed $endDate
	 * @return array|<missing>@param mixed $operator
	 */
	public function totalPaymentDistrix($operator = null, $endDate = null): array
	{
		$query = null;
		$date = $this->input->get('filter');
		if ($date === 'monthly') {
			$date = 'yearly';
		} else if ($date === 'yearly') {
			$date = 'monthly';
		}

		$operator = ($operator == 'less') ? '<=' : '>=';
		if ($date == 'monthly') {
			// return in yearly group
			return $this->getTotalAnnualFinance('collections');
			// return it weekly group but not used again in frontend

			// $query = "SELECT date_format(b.date_completed, '%a') as label, cast(sum(b.amount_paid) as decimal) as total from transaction b where (b.payment_status = '01' or b.payment_status = '00') and month(b.date_completed) = month(curdate()) and year(b.date_completed) = year(curdate()) and cast(b.date_performed as date) $operator '$endDate' group by label order by label asc";

			$query = " SELECT label, ANY_VALUE(ord) as ord, sum(total) as total from
			(
				SELECT date_format(a.date_completed, '%a') as label, cast(sum(a.mainaccount_amount + a.subaccount_amount) as decimal) as total,
				ANY_VALUE(UNIX_TIMESTAMP(a.date_completed)) as ord from applicant_transaction a where a.payment_status in ('01','00')
				and month(a.date_completed) = month(curdate()) and year(a.date_completed) = year(curdate()) group by label
				UNION
				SELECT date_format(b.date_completed, '%a') as label, cast(sum(b.mainaccount_amount + b.subaccount_amount) as decimal) as total,
				ANY_VALUE(UNIX_TIMESTAMP(b.date_completed)) as ord from transaction b where b.payment_status in ('01','00') and
				month(b.date_completed) = month(curdate()) and year(b.date_completed) = year(curdate()) group by label
				UNION
				SELECT date_format(c.date_completed, '%a') as label, cast(sum(c.mainaccount_amount + c.subaccount_amount) as decimal) as total,
				ANY_VALUE(UNIX_TIMESTAMP(c.date_completed)) as ord from transaction_custom c where c.payment_status in ('01','00') and
				month(c.date_completed) = month(curdate()) and year(c.date_completed) = year(curdate()) group by label
			) as x group by label ORDER BY ord ASC";
		} else if ($date == 'yearly') {
			// return in monthly group
			$query = "
				SELECT label, ANY_VALUE(ord) as ord, sum(total) as total from
				(
				SELECT date_format(a.date_completed, '%b-%Y') as label, ANY_VALUE(UNIX_TIMESTAMP(a.date_completed)) as ord,
				sum(a.mainaccount_amount + a.subaccount_amount) as total from applicant_transaction a where
				a.payment_status in ('01','00') and timestampdiff(month, a.date_completed, now()) < 12 group by year(a.date_completed), month(a.date_completed), label
				UNION
				SELECT date_format(b.date_completed, '%b-%Y') as label, ANY_VALUE(UNIX_TIMESTAMP(b.date_completed)) as ord,
				sum(b.mainaccount_amount + b.subaccount_amount) as total from transaction b where b.payment_status in ('01','00')
				and timestampdiff(month, b.date_completed, now()) < 12 group by year(b.date_completed), month(b.date_completed), label
				UNION
				SELECT date_format(c.date_completed, '%b-%Y') as label, ANY_VALUE(UNIX_TIMESTAMP(c.date_completed)) as ord,
				sum(c.mainaccount_amount + c.subaccount_amount) as total from transaction_custom c where c.payment_status in ('01','00')
				and timestampdiff(month, c.date_completed, now()) < 12 group by year(c.date_completed), month(c.date_completed), label
				) as x group by label ORDER BY ord ASC";
		} else {
			// return last 7 days group by day
			$query = "
				SELECT label, ANY_VALUE(day) as day, sum(total) as total, ANY_VALUE(ord) as ord from
				(
					SELECT date_format(a.date_completed, '%b %e, %Y') as label, ANY_VALUE(date_format(a.date_completed, '%d')) as day,
				    cast(sum(a.mainaccount_amount + a.subaccount_amount) as decimal) as total, ANY_VALUE(UNIX_TIMESTAMP(a.date_completed)) as ord from
				    applicant_transaction a where a.payment_status in ('01','00') and date(a.date_completed) >= (date(now()) - interval 7 day) group by label
					UNION
					SELECT date_format(b.date_completed, '%b %e, %Y') as label, ANY_VALUE(date_format(b.date_completed, '%d')) as day,
				    cast(sum(b.mainaccount_amount + b.subaccount_amount) as decimal) as total, ANY_VALUE(UNIX_TIMESTAMP(b.date_completed)) as ord from
					transaction b where b.payment_status in ('01','00') and date(b.date_completed) >= (date(now()) - interval 7 day) group by label
					UNION
					SELECT date_format(c.date_completed, '%b %e, %Y') as label, ANY_VALUE(date_format(c.date_completed, '%d')) as day,
				    cast(sum(c.mainaccount_amount + c.subaccount_amount) as decimal) as total, ANY_VALUE(UNIX_TIMESTAMP(c.date_completed)) as ord from
				    transaction_custom c where c.payment_status in ('01','00') and date(c.date_completed) >= (date(now()) - interval 7 day) group by label
				) as x group by label order by ord asc";
		}

		$query = $this->db->query($query);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array();
	}

	/**
	 * @param mixed $session
	 * @return bool|<missing>
	 */
	private function getTotalActiveStudent($session)
	{
		$paymentValidation = get_setting('session_semester_payment_start');
		$semester = ($session >= $paymentValidation) ? true : false;
		$query = null;

		if ($semester) {
			$query = "SELECT e.name, 'active',any_value(e.slug) as slug, count(distinct b.student_id) as total from transaction b join academic_record c on c.student_id = b.student_id join programme d on d.id = c.programme_id right join faculty e on e.id = d.faculty_id where b.session = ? and (b.payment_id = '1' or b.payment_id = '2') and b.payment_status in ('00', '01') group by e.name order by name asc";
		} else {
			$query = "SELECT distinct e.name, 'active',any_value(e.slug) as slug, count(b.student_id) as total from transaction b join academic_record c on c.student_id = b.student_id join programme d on d.id = c.programme_id right join faculty e on e.id = d.faculty_id where b.session = ? and b.payment_id = '1' and b.payment_status in ('00', '01') group by e.name order by name asc";
		}

		$result = [];
		$query = $this->db->query($query, [$session]);
		if ($query->num_rows() <= 0) {
			return false;
		}

		return $query->result_array();
	}

	/**
	 * @param mixed $session
	 * @return array|<missing>
	 */
	private function totalStudentByFaculty($session)
	{
		$result = $this->getTotalActiveStudent($session);
		if (!$result) {
			return [];
		}
		$content = [];
		foreach ($result as $i) {
			$cont[] = [
				'name' => $i['name'],
				'total' => (int)$i['total'],
			];
			$content = $cont;
		}
		return $content;
	}

	/**
	 * [totalActiveStudentByDepartment description]
	 * @return array|<missing>@param mixed $session
	 */
	private function totalActiveStudentByDepartment($session)
	{
		$paymentValidation = get_setting('session_semester_payment_start');
		$semester = ($session >= $paymentValidation) ? true : false;
		$query = null;

		if ($semester) {
			$query = "SELECT e.name, 'active',any_value(e.slug) as slug, count(distinct b.student_id) as total from transaction b right join
			academic_record c on c.student_id = b.student_id right join programme d on d.id = c.programme_id right join department e on e.id = d.department_id
            where b.session = ? and (b.payment_id = '1' or b.payment_id = '2') and b.payment_status in ('00', '01') and e.type='academic' group by e.name order by name asc";
		} else {
			$query = "SELECT distinct e.name, 'active',any_value(e.slug) as slug, count(b.student_id) as total from transaction b right join academic_record c on c.student_id = b.student_id right join programme d on d.id = c.programme_id right join department e on e.id = d.department_id where b.session = ? and b.payment_id = '1' and b.payment_status in ('00', '01') and e.type='academic' group by e.name order by name asc";
		}

		$query1 = "SELECT distinct d.name, 'total_student', any_value(d.slug) as slug, count(*) as total from students a right join academic_record b on b.student_id = a.id right join programme c on c.id = b.programme_id right join department d on d.id = c.department_id where b.current_session = ? and a.active = '1' and d.type='academic' group by d.name";
		$query = $this->db->query($query, [$session]);
		$query1 = $this->db->query($query1, [$session]);
		$result = [];
		if ($query->num_rows() <= 0 || $query1->num_rows() <= 0) {
			return $result;
		}

		$result = $query->result_array();
		$result1 = $query1->result_array();
		$content = [];
		foreach ($result as $i) {
			foreach ($result1 as $j) {
				if (strtolower($i['slug']) == strtolower($j['slug'])) {
					$cont[] = [
						'name' => $i['name'],
						'sub_total' => (int)$i['total'],
						'total' => (int)$j['total'],
					];
					$content = $cont;
				}
			}
		}

		return $content;
	}

	/**
	 * @param mixed $session
	 * @return array|<missing>
	 */
	private function totalActiveStudentByFaculty($session)
	{
		$result = $this->getTotalActiveStudent($session);
		if (!$result) {
			return [];
		}
		$query1 = "SELECT distinct d.name, 'total_student',any_value(d.slug) as slug, count(*) as total from students a join academic_record b on b.student_id = a.id join programme c on c.id = b.programme_id join faculty d on d.id = c.faculty_id where b.current_session = ? and a.active = '1' group by d.name";
		$query1 = $this->db->query($query1, [$session]);
		if ($query1->num_rows() <= 0) {
			return [];
		}
		$result1 = $query1->result_array();
		$content = [];
		foreach ($result as $i) {
			foreach ($result1 as $j) {
				if (strtolower($i['slug']) == strtolower($j['slug'])) {
					$cont[] = [
						'name' => $i['name'],
						'sub_total' => (int)$i['total'],
						'total' => (int)$j['total'],
					];
					$content = $cont;
				}
			}
		}

		return $content;

	}

	public function getActiveStudentTransactionPerLevel($session, $semester): array
	{
		$paymentValidation = get_setting('session_semester_payment_start');
		$semester = ($session >= $paymentValidation) ? $semester : null;
		$query = null;

		$department = $_GET['dashboard_department'];

		if ($semester) {
			if ($semester == 1) {
				$query = "SELECT distinct a.level as name, count(*) as total from transaction a join academic_record b on
				b.student_id = a.student_id right join programme c on c.id = b.programme_id right join department d on d.id = c.department_id
            	where a.payment_id = '1' and d.id = '$department' and a.session = ? and  payment_status in ('00','01') group by name order by name asc";
			} else {
				$query = "SELECT distinct a.level as name, count(*) as total from transaction a join academic_record b on
				b.student_id = a.student_id right join programme c on c.id = b.programme_id right join department d on d.id = c.department_id
            	where a.payment_id = '2' and d.id = '$department' and a.session = ? and  payment_status in ('00','01') group by name order by name asc";
			}

		} else {
			$query = "SELECT distinct b.current_level as name, count(distinct b.student_id) as total from transaction a join academic_record b on
				b.student_id = a.student_id right join programme c on c.id = b.programme_id right join department d on d.id = c.department_id
            	where a.payment_id = '1' and d.id = '$department' and a.session = ? and  payment_status in ('00','01') group by name order by name asc";
		}

		$query = $this->db->query($query, [$session]);
		if ($query->num_rows() <= 0) {
			return [
				'name' => null,
				'active' => 0,
			];
		}
		$result = $query->result_array();
		$payload = [];
		foreach ($result as $res) {
			$item = [
				'name' => formatStudentLevel($res['name']),
				'active' => (int)$res['total'],
			];
			$payload[] = $item;
		}

		return $payload;
	}

	public function getActiveStudentTransactionPerLevelNew($session, $semester): array
	{
//		$query = "SELECT distinct a.level as name, count(distinct a.student_id) as total from transaction a join academic_record b
//			on b.student_id = a.student_id where a.session = ? and a.payment_id = '1' and a.payment_status in ('00', '01')
//			group by name order by name asc";

		$paymentValidation = get_setting('session_semester_payment_start');
		$semester = ($session >= $paymentValidation) ? $semester : null;
		$query = null;

		if ($semester) {
			if ($semester == 1) {
				$query = "SELECT distinct a.level as name, count(*) as total from transaction a join academic_record b on
				b.student_id = a.student_id where a.session = ? and a.payment_id = '1' and a.payment_status in ('00', '01')
				group by name order by name asc";
			} else {
				$query = "SELECT distinct a.level as name, count(*) as total from transaction a join academic_record b on
				b.student_id = a.student_id where a.session = ? and a.payment_id = '2' and a.payment_status in ('00', '01')
				group by name order by name asc";
			}
		} else {
			$query = "SELECT distinct a.level as name, count(*) as total from transaction a join academic_record b on
			b.student_id = a.student_id where a.session = ? and a.payment_id = '1' and a.payment_status in ('00', '01')
			group by name order by name asc";
		}

		$query = $this->db->query($query, [$session]);
		if ($query->num_rows() <= 0) {
			return [
				'name' => null,
				'active' => 0,
			];
		}
		$result = $query->result_array();
		$payload = [];
		foreach ($result as $res) {
			$item = [
				'name' => formatStudentLevel($res['name']),
				'active' => (int)$res['total'],
			];
			$payload[] = $item;
		}

		return $payload;
	}

	/**
	 * @param mixed $session
	 * @param mixed $semester
	 * @return array|array<int,array<string,mixed>>
	 */
	private function getActiveStudentTransactionByLevel($session, $semester): array
	{
		$paymentValidation = get_setting('session_semester_payment_start');
		$semester = ($session >= $paymentValidation) ? $semester : null;
		$query = null;

		if (isset($_GET['dashboard_department'])) {
			$department = $_GET['dashboard_department'];
			if ($semester) {
				if ($semester == 1) {
					$query = "SELECT distinct a.level as name, count(distinct a.student_id) as total from transaction a join academic_record b
					on b.student_id = a.student_id right join programme c on c.id = b.programme_id right join department d on d.id = c.department_id
					where a.session = ? and a.payment_id = '1' and a.payment_status in ('00', '01') and d.id = '$department' group by name order by name asc";
				} else {
					$query = "SELECT distinct a.level as name, count(distinct a.student_id) as total from transaction a join academic_record b
					on b.student_id = a.student_id right join programme c on c.id = b.programme_id right join department d on d.id = c.department_id
					where a.session = ? and a.payment_id = '2' and a.payment_status in ('00', '01') and d.id = '$department' group by name order by name asc";
				}
			} else {
				$query = "SELECT distinct a.level as name, count(distinct a.student_id) as total from transaction a join academic_record b
				on b.student_id = a.student_id right join programme c on c.id = b.programme_id right join department d on d.id = c.department_id
				where a.session = ? and a.payment_id = '1' and a.payment_status in ('00', '01') and d.id = '$department' group by name order by name asc";
			}

			$query1 = "SELECT distinct b.current_level as name, count(*) as total from students a join academic_record b on
			b.student_id = a.id right join programme c on c.id = b.programme_id right join department d on d.id = c.department_id
            where d.id = '$department' and b.current_session = ? group by name order by name asc";
		} else {
			if ($semester) {
				if ($semester == 1) {
					$query = "SELECT distinct a.level as name, count(*) as total from transaction a join academic_record b
						on b.student_id = a.student_id where a.session = ? and a.payment_id = '1' and
						a.payment_status in ('00', '01') group by name order by name asc";
				} else {
					$query = "SELECT distinct a.level as name, count(*) as total from transaction a join academic_record b on
						b.student_id = a.student_id where a.session = ? and a.payment_id = '2' and
						a.payment_status in ('00', '01') group by name order by name asc";
				}
			} else {
				$query = "SELECT distinct a.level as name, count(*) as total from transaction a join academic_record b on
					b.student_id = a.student_id where a.session = ? and a.payment_id = '1' and
					a.payment_status in ('00', '01') group by name order by name asc";
			}
			//			$query = "SELECT distinct a.level as name, count(distinct a.student_id) as total from transaction a join academic_record b
			//			on b.student_id = a.student_id where a.session = ? and a.payment_id = '1' and a.payment_status in ('00', '01')
			//			group by name order by name asc";

			$query1 = "SELECT distinct b.current_level as name, count(*) as total from students a join academic_record b on
			b.student_id = a.id where b.current_session = ? group by name order by name asc";
		}

		$query = $this->db->query($query, [$session]);
		$query1 = $this->db->query($query1, [$session]);

		$result = $query->result_array();
		$result1 = $query1->result_array();
		$payload = [];

		if (!empty($result1)) {
			foreach ($result1 as $res) {
				if (!empty($result)) {
					foreach ($result as $res1) {
						if ($res1['name'] == $res['name']) {
							$item = [
								'name' => formatStudentLevel($res1['name']),
								'passive' => $res['total'] - $res1['total'],
								'active' => (int)$res1['total'],
							];
							$payload[] = $item;
						}
					}
				} else {
					$item = [
						'name' => formatStudentLevel($res['name']),
						'passive' => (int)$res['total'],
						'active' => 0,
					];
					$payload[] = $item;
				}
			}
		} else {
			$item = [
				'name' => null,
				'passive' => 0,
				'active' => 0,
			];
			$payload[] = $item;
		}

		return $payload;
	}

	private function getActiveStudentTransactionByLevel_backup($session, $semester)
	{
		$paymentValidation = get_setting('session_semester_payment_start');
		$semester = ($session >= $paymentValidation) ? $semester : null;
		$query = null;

		if ($semester) {
			if ($semester == 1) {
				$query = "SELECT distinct a.level as name, count(*) as total from transaction a join academic_record b on b.student_id = a.student_id where a.session = ? and a.payment_id = '1' and a.payment_status in ('00', '01') group by name order by name asc";
			} else {
				$query = "SELECT distinct a.level as name, count(*) as total from transaction a join academic_record b on b.student_id = a.student_id where a.session = ? and a.payment_id = '2' and a.payment_status in ('00', '01') group by name order by name asc";
			}
		} else {
			$query = "SELECT distinct a.level as name, count(*) as total from transaction a join academic_record b on b.student_id = a.student_id where a.session = ? and a.payment_id = '1' and a.payment_status in ('00', '01') group by name order by name asc";
		}

		$query1 = "SELECT distinct b.current_level as name, count(*) as total from students a join academic_record b on b.student_id = a.id where b.current_session = ? group by name order by name asc";
		$query = $this->db->query($query, [$session]);
		$query1 = $this->db->query($query1, [$session]);
		if ($query->num_rows() <= 0) {
			return [];
		}
		if ($query1->num_rows() <= 0) {
			return [];
		}
		$result = $query->result_array();
		$result1 = $query1->result_array();
		$payload = [];

		foreach ($result1 as $res) {
			if (!empty($result)) {
				foreach ($result as $res1) {
					// if ($res1['name'] != '501' && $res1['name'] != '401') {
					if ($res1['name'] == $res['name']) {
						$item = [
							'name' => (strlen($res1['name']) < 3) ? $res1['name'] . "00" : $res1['name'],
							'passive' => $res['total'] - $res1['total'],
							'active' => (int)$res1['total'],
						];
						$payload[] = $item;
					}
					// }

				}
			} else {
				$item = [
					'name' => (strlen($res['name']) < 3) ? $res['name'] . "00" : $res['name'],
					'passive' => (int)$res['total'],
					'active' => 0,
				];
				$payload[] = $item;
			}
		}
		return $payload;
	}

	/**
	 * This is to get total payment both from applicant_transaction and transaction
	 * table in the database
	 * @return int@param mixed $session
	 */
	private function totalPayment($session)
	{
		$query = "SELECT sum(total) as total, sum(countTotal) as countTotal from 
		(
			SELECT sum(a.mainaccount_amount + a.subaccount_amount) as total, count(a.id) as countTotal from applicant_transaction a where a.payment_status in ('01','00') and a.session = ?
        	UNION SELECT sum(b.mainaccount_amount + b.subaccount_amount) as total, count(b.id) as countTotal from transaction b where b.payment_status in ('01','00') and b.session = ?
        	UNION SELECT sum(c.mainaccount_amount + c.subaccount_amount) as total, count(c.id) as countTotal from transaction_custom c where c.payment_status in ('01','00') and c.session = ? 
		) x";
		$query = $this->db->query($query, [$session, $session, $session]);
		$result = [];
		if ($query->num_rows() <= 0) {
			return 0;
		}
		$result = $query->result_array();
		$result1 = (!empty($result) && isset($result[0])) ? $result[0]['total'] : 0;
		$result2 = (!empty($result) && isset($result[0])) ? $result[0]['countTotal'] : 0;
		return ['amount' => $result1, 'total' => $result2];
	}

	/**
	 * @param mixed $session
	 * @param mixed $operator
	 * @param mixed $endDate
	 * @return int
	 */
	private function totalPayment2($session, $operator = null, $endDate = null)
	{
		$operator = ($operator == 'less') ? '<=' : '>=';
		$query = "SELECT sum(a.amount_paid) as total from applicant_transaction a where a.payment_status in ('01', '00') and
        	a.session = ? and cast(a.date_performed as date) $operator '$endDate'
        	UNION SELECT sum(b.amount_paid) as total from transaction b where b.payment_status in ('01','00') and b.session = ?
        	and cast(b.date_performed as date) $operator '$endDate'
        	UNION SELECT sum(c.amount_paid) as total from transaction_custom c where c.payment_status in ('01','00') and c.session = ?
            and cast(c.date_performed as date) $operator '$endDate' ";

		$query = $this->db->query($query, [$session, $session, $session]);
		$result = [];
		if ($query->num_rows() <= 0) {
			return 0;
		}
		$result = $query->result_array();
		$result1 = (!empty($result) && isset($result[0])) ? $result[0]['total'] : 0;
		$result2 = (!empty($result) && isset($result[1])) ? $result[1]['total'] : 0;
		$result3 = (!empty($result) && isset($result[2])) ? $result[2]['total'] : 0;
		return $result1 + $result2 + $result3;
	}

	/**
	 * This is to get mainaccount amount
	 * @return int@param mixed $session
	 */
	private function totalTransactionfeeDescription($session)
	{
		$query = "SELECT sum(total) as total, sum(countTotal) as countTotal from (
		SELECT sum(a.mainaccount_amount + a.subaccount_amount) as total,count(a.id) as countTotal from applicant_transaction a where a.session = ? and a.payment_status in ('00', '01')
		UNION SELECT sum(b.mainaccount_amount + b.subaccount_amount) as total,count(b.id) as countTotal from transaction b where b.session = ? and b.payment_status in ('00','01')
		UNION SELECT sum(c.mainaccount_amount + c.subaccount_amount) as total, count(c.id) as countTotal from transaction_custom c where c.session = ? and c.payment_status in ('00','01') 
		) x";
		$query = $this->db->query($query, [$session, $session, $session]);
		$result = [];
		if (!$query) {
			return 0;
		}
		$result = $query->result_array();
		$result1 = (!empty($result) && isset($result[0])) ? $result[0]['total'] : 0;
		$result2 = (!empty($result) && isset($result[0])) ? $result[0]['countTotal'] : 0;
		return ['amount' => $result1, 'total' => $result2];
	}

	/**
	 * This is to get mainaccount amount
	 * @param mixed $operator
	 * @param mixed $endDate
	 * @return int@param mixed $session
	 */
	private function totalTransactionfeeDescription2($session, $operator = null, $endDate = null)
	{
		$operator = ($operator == 'less') ? '<=' : '>=';
		$query = "SELECT sum(a.mainaccount_amount + a.subaccount_amount) as total from applicant_transaction a where a.session = ?
			and (a.payment_status = '00' or a.payment_status = '01') and cast(a.date_performed as date) $operator '$endDate'
        	UNION SELECT sum(b.mainaccount_amount + b.subaccount_amount) as total from transaction b where b.session = ? and
        	(b.payment_status = '00' or b.payment_status = '01') and cast(b.date_performed as date) $operator '$endDate'
        	UNION SELECT sum(c.mainaccount_amount + c.subaccount_amount) as total from transaction_custom c where c.session = ?
        	and c.payment_status in ('00','01') and cast(c.date_performed as date) $operator '$endDate' ";
		$query = $this->db->query($query, [$session, $session, $session]);
		$result = [];
		if (!$query) {
			return 0;
		}
		$result = $query->result_array();
		$result1 = (!empty($result) && isset($result[0])) ? $result[0]['total'] : 0;
		$result2 = (!empty($result) && isset($result[1])) ? $result[1]['total'] : 0;
		$result3 = (!empty($result) && isset($result[2])) ? $result[2]['total'] : 0;
		return $result1 + $result2 + $result3;
	}

	/**
	 * @param mixed $session
	 * @return int
	 */
	private function mainTransactionfeeDescription($session)
	{
		$query = "
		SELECT sum(total) as total, sum(countTotal) as countTotal from
		(
			SELECT sum(a.mainaccount_amount) as total, count(a.id) as countTotal from applicant_transaction a where a.session = ? and a.payment_status in ('00','01')
			UNION
			SELECT sum(b.mainaccount_amount) as total, count(b.id) as countTotal from transaction b where b.session = ? and b.payment_status in ('00','01')
			UNION
			SELECT sum(c.mainaccount_amount) as total, count(c.id) as countTotal from transaction_custom c where c.session = ? and c.payment_status in ('00','01')
		) as x ";
		$query = $this->db->query($query, [$session, $session, $session]);
		$result = [];
		if (!$query) {
			return 0;
		}
		$result = $query->result_array();
		$result1 = (!empty($result) && isset($result[0])) ? $result[0]['total'] : 0;
		$result2 = (!empty($result) && isset($result[0])) ? $result[0]['countTotal'] : 0;
		return ['amount' => $result1, 'total' => $result2];
	}

	/**
	 * @param mixed $session
	 * @param mixed $operator
	 * @param mixed $endDate
	 * @return int
	 */
	private function mainTransactionfeeDescription2($session, $operator = null, $endDate = null)
	{
		$operator = ($operator == 'less') ? '<=' : '>=';
		$query = "SELECT sum(a.mainaccount_amount) as total from applicant_transaction a where a.session = ? and a.payment_status in ('00','01') and
    		cast(a.date_performed as date) $operator '$endDate'
        	UNION SELECT sum(b.mainaccount_amount) as total from transaction b where b.session = ? and b.payment_status in ('00','01')
        	and cast(b.date_performed as date) $operator '$endDate'
        	UNION SELECT sum(c.mainaccount_amount) as total from transaction_custom c where c.session = ? and
        	c.payment_status in ('00','01') and cast(c.date_performed as date) $operator '$endDate' ";
		$query = $this->db->query($query, [$session, $session, $session]);
		$result = [];
		if (!$query) {
			return 0;
		}
		$result = $query->result_array();
		$result1 = (!empty($result) && isset($result[0])) ? $result[0]['total'] : 0;
		$result2 = (!empty($result) && isset($result[1])) ? $result[1]['total'] : 0;
		$result3 = (!empty($result) && isset($result[2])) ? $result[2]['total'] : 0;
		return $result1 + $result2 + $result3;
	}

	/**
	 * @param mixed $session
	 * @return int
	 */
	private function subTransactionfeeDescription($session)
	{
		$query = "SELECT SUM(total) as total, sum(countTotal) as countTotal from (
		SELECT sum(a.subaccount_amount) as total, count(a.id) as countTotal from applicant_transaction a where a.session = ? and a.payment_status in ('00','01')
		UNION SELECT sum(b.subaccount_amount) as total,count(b.id) as countTotal from transaction b where b.session = ? and b.payment_status in ('00','01')
		UNION SELECT sum(c.subaccount_amount) as total, count(c.id) as countTotal from transaction_custom c where c.session = ? and c.payment_status in ('00','01') 
		) x";
		$query = $this->db->query($query, [$session, $session, $session]);
		$result = [];
		if (!$query) {
			return 0;
		}
		$result = $query->result_array();
		$result1 = (!empty($result) && isset($result[0])) ? $result[0]['total'] : 0;
		$result2 = (!empty($result) && isset($result[0])) ? $result[0]['countTotal'] : 0;
		return ['amount' => $result1, 'total' => $result2];
	}

	/**
	 * @param mixed $session
	 * @param mixed $operator
	 * @param mixed $endDate
	 * @return int
	 */
	private function subTransactionfeeDescription2($session, $operator = null, $endDate = null)
	{
		$operator = ($operator == 'less') ? '<=' : '>=';
		$query = "SELECT sum(a.subaccount_amount) as total from applicant_transaction a where a.session = ? and a.payment_status in ('00','01') and
        	cast(a.date_performed as date) $operator '$endDate'
            UNION SELECT sum(b.subaccount_amount) as total from transaction b where b.session = ? and b.payment_status in ('00','01')
            and cast(b.date_performed as date) $operator '$endDate'
            UNION SELECT sum(c.subaccount_amount) as total from transaction_custom c where c.session = ? and c.payment_status in ('00','01')
            and cast(c.date_performed as date) $operator '$endDate' ";
		$query = $this->db->query($query, [$session, $session, $session]);
		$result = [];
		if (!$query) {
			return 0;
		}
		$result = $query->result_array();
		$result1 = (!empty($result) && isset($result[0])) ? $result[0]['total'] : 0;
		$result2 = (!empty($result) && isset($result[1])) ? $result[1]['total'] : 0;
		$result3 = (!empty($result) && isset($result[2])) ? $result[2]['total'] : 0;
		return $result1 + $result2 + $result3;
	}

	/**
	 * @param mixed $session
	 * @param mixed $operator
	 * @param mixed $endDate
	 * @return array<string,mixed>
	 */
	private function getRemittanceInflowLatest($session, $operator, $endDate)
	{
		return [
			'total_payment' => (int)$this->totalPayment2($session, $operator, $endDate),
			'e_collections' => (int)$this->totalTransactionfeeDescription2($session, $operator, $endDate),
			'ui_inflow' => (int)$this->mainTransactionfeeDescription2($session, $operator, $endDate),
			'dlc_inflow' => (int)$this->subTransactionfeeDescription2($session, $operator, $endDate),
		];
	}

	/**
	 * @param mixed $session
	 * @param mixed $operator
	 * @param mixed $endDate
	 * @return array<string,mixed>
	 */
	private function getRemittanceGraph($session, $operator = null, $endDate = null)
	{
		return [
			'total_payment' => $this->totalPayment($session),
			'e_collections' => $this->totalTransactionfeeDescription($session),
			'ui_inflow' => $this->mainTransactionfeeDescription($session),
			'dlc_inflow' => $this->subTransactionfeeDescription($session),
		];
	}

	/**
	 * @param mixed $session
	 * @param mixed $semester
	 */
	public function getStudentWithEnrollment($session, $semester)
	{
		if (isset($_GET['dashboard_department'])) {
			$department = $_GET['dashboard_department'];
			$query = "SELECT count(distinct a.student_id) as total from course_enrollment a join academic_record b on
			b.student_id=a.student_id right join programme c on c.id = b.programme_id
            right join department d on d.id = c.department_id where a.session_id=? and a.semester=? and d.id = '$department'";
		} else {
			$query = "SELECT count(distinct course_enrollment.student_id) as total from course_enrollment join academic_record on
			academic_record.student_id=course_enrollment.student_id where course_enrollment.session_id=? and course_enrollment.semester=?";
		}
		$result = $this->db->query($query, [$session, $semester]);
		return $result->result_array()[0]['total'];
	}

	/**
	 * @param  [type] $student  [description]
	 * @param  [type] $session  [description]
	 * @param  [type] $semester [description]
	 * @param mixed $student
	 * @param mixed $session
	 * @param mixed $semester
	 * @return [type]           [description]
	 * @deprecated - Not used again because I found another efficient query
	 */
	private function getStudentCourseEnrollment($student, $session, $semester)
	{
		$query = "SELECT distinct courses.id as main_course_id, courses.code as course_code, if(total_score is not null and total_score <> '', 'present', 'absent') as remark FROM course_enrollment join courses on courses.id=course_enrollment.course_id where course_enrollment.student_id=? and course_enrollment.session_id = ? and course_enrollment.semester = ?";
		$query .= "order by course_code";
		$result = $this->db->query($query, [$student, $session, $semester]);
		return $result->result_array();
	}

	/**
	 * @param  [type] $student  [description]
	 * @param  [type] $session  [description]
	 * @param  [type] $semester [description]
	 * @param mixed $session
	 * @param mixed $semester
	 * @return int|<missing>@param mixed $student
	 * @deprecated - Not used again because I found another efficient query
	 */
	private function getStudentCourseWithResult($student, $session, $semester)
	{
		$query = "SELECT distinct count(*) as total FROM course_enrollment where total_score is not null and total_score <> ''  and course_enrollment.student_id=? and course_enrollment.session_id = ? and course_enrollment.semester = ? ";
		$result = $this->db->query($query, [$student, $session, $semester]);
		if ($result->num_rows() <= 0) {
			return 0;
		}
		return $result->result_array()[0]['total'];
	}

	/**
	 * @param  [type]  $session  [description]
	 * @param boolean $semester [description]
	 * @param string $type [description]
	 * @return int@param mixed $session
	 * @deprecated - Not used again because I found another efficient query - getStudentWithResult
	 */
	private function getStudentWithResultOld($session, $semester = false, $type = 'complete')
	{

		$query = "SELECT distinct course_enrollment.student_id from course_enrollment join academic_record on academic_record.student_id=course_enrollment.student_id where course_enrollment.session_id = ? ";

		if ($semester) {
			$query .= " and course_enrollment.semester = '$semester'";
		}
		$result = $this->db->query($query, [$session]);
		if ($result->num_rows() <= 0) {
			return 0;
		}
		$result = $result->result_array();
		$count = 0;
		$content = [];
		foreach ($result as $res) {
			$courses = $this->getStudentCourseEnrollment($res['student_id'], $session, $semester);
			$courseScore = $this->getStudentCourseWithResult($res['student_id'], $session, $semester);
			$courses = count($courses);

			if ($type == 'complete') {
				if ($courses == $courseScore) {
					$count++;
				}
			}

			if ($type == 'incomplete') {
				if ($courses != $courseScore) {
					$count++;
				}
			}

		}
		return $count;
	}

	/**
	 * @param mixed $session
	 * @param mixed $semester
	 * @param mixed $type
	 * @return int|int<0, max>
	 */
	private function getStudentWithResult($session, $semester = false, $type = 'complete')
	{
		if (isset($_GET['dashboard_department'])) {
			$department = $_GET['dashboard_department'];
			$query = "SELECT distinct course_enrollment.student_id, count(distinct course_enrollment.course_id) as course_count,
			count(if(total_score is not null and total_score <> '', 1, null)) as score_count from course_enrollment join
			academic_record on academic_record.student_id=course_enrollment.student_id right join programme on
			programme.id = academic_record.programme_id right join department on department.id = programme.department_id where
			course_enrollment.session_id = ? and course_enrollment.semester = '$semester' and department.id = '$department' group by course_enrollment.student_id";
		} else {
			$query = "SELECT distinct course_enrollment.student_id, count(distinct course_enrollment.course_id) as course_count,
			count(if(total_score is not null and total_score <> '', 1, null)) as score_count from course_enrollment join
			academic_record on academic_record.student_id=course_enrollment.student_id where course_enrollment.session_id = ? and
			course_enrollment.semester = '$semester' group by course_enrollment.student_id";
		}
		$result = $this->db->query($query, [$session]);
		if ($result->num_rows() <= 0) {
			return 0;
		}
		$result = $result->result_array();
		$content = [];
		if ($type == 'complete') {
			$content = array_filter($result, function ($v, $k) {
				return $v['course_count'] == $v['score_count'];
			}, ARRAY_FILTER_USE_BOTH);
		}

		if ($type == 'incomplete') {
			$content = array_filter($result, function ($v, $k) {
				return $v['course_count'] != $v['score_count'];
			}, ARRAY_FILTER_USE_BOTH);
		}

		if ($type == 'zero') {
			$content = array_filter($result, function ($v, $k) {
				return $v['score_count'] == 0;
			}, ARRAY_FILTER_USE_BOTH);
		}

		return count($content);
	}

	/**
	 * @param mixed $session
	 * @param mixed $semester
	 * @return bool|<missing>
	 */
	private function getDistinctCourseWithScoreOld($session, $semester)
	{
		$query = "SELECT course_enrollment.course_id, count(*) as total from course_enrollment join academic_record on academic_record.student_id=course_enrollment.student_id where course_enrollment.session_id = ? and course_enrollment.semester = ? and total_score is not null and total_score <> '' group by course_enrollment.course_id";
		$result = $this->db->query($query, [$session, $semester]);
		if ($result->num_rows() <= 0) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param mixed $session
	 * @param mixed $semester
	 * @return bool|<missing>
	 */
	private function getDistinctCourseWithScore($session, $semester)
	{
		if (isset($_GET['dashboard_department'])) {
			$department = $_GET['dashboard_department'];
			$query = "SELECT distinct a.course_id, count(*) as total, count(if(total_score is not null and total_score <> '', 1, null)) as score_count
			from course_enrollment a join academic_record b on b.student_id=a.student_id right join programme c on c.id = b.programme_id
			right join department d on d.id = c.department_id where d.id = '$department' and a.session_id = ? and a.semester = ?
			group by a.course_id";
		} else {
			$query = "SELECT distinct a.course_id, count(*) as total, count(if(total_score is not null and total_score <> '', 1, null)) as score_count
			from course_enrollment a join academic_record b on b.student_id=a.student_id where a.session_id = ? and a.semester = ?
			group by a.course_id";
		}

		$result = $this->db->query($query, [$session, $semester]);
		if ($result->num_rows() <= 0) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param mixed $session
	 * @param mixed $semester
	 * @return bool|<missing>
	 */
	private function getDistinctCourseCodeWithScore($session, $semester)
	{
		if (isset($_GET['dashboard_department'])) {
			$department = $_GET['dashboard_department'];
			$query = "SELECT distinct a.course_id, c.code as course_code, count(*) as total,
    		count(if(total_score is not null and total_score <> '', 1, null)) as score_count from course_enrollment a join
    		academic_record b on b.student_id=a.student_id left join courses c on
    		c.id = a.course_id right join programme d on d.id = b.programme_id right join department e on e.id = d.department_id
    		where e.id = '$department' and a.session_id = ? and a.semester = ? group by a.course_id";
		} else {
			$query = "SELECT distinct a.course_id, c.code as course_code, count(*) as total,
    		count(if(total_score is not null and total_score <> '', 1, null)) as score_count from course_enrollment a join
    		academic_record b on b.student_id=a.student_id left join courses c on
    		c.id = a.course_id where a.session_id = ? and a.semester = ? group by a.course_id";
		}
		$result = $this->db->query($query, [$session, $semester]);
		if ($result->num_rows() <= 0) {
			return false;
		}
		return $result->result_array();
	}

	/**
	 * @param mixed $session
	 * @param mixed $semester
	 * @return bool|<missing>
	 */
	private function getDistinctCourseEnrollment($session, $semester)
	{
		$query = "SELECT distinct course_enrollment.course_id, count(*) as total from course_enrollment join academic_record
		on academic_record.student_id=course_enrollment.student_id where course_enrollment.session_id = ? and
		course_enrollment.semester = ? group by course_enrollment.course_id";

		$result = $this->db->query($query, [$session, $semester]);
		if ($result->num_rows() <= 0) {
			return false;
		}
		return $result->result_array();
	}

	public function getDistinctCourseEnrol()
	{
		$session = $_GET['session'] ?? $this->currentTransactionSession();
		$semester = $_GET['sem'] ?? 1;
		$download = $this->input->get('download', true) ?? null;

		$query = "SELECT count(distinct a.student_id) as total, b.code as course_code from course_enrollment a
    	join courses b on b.id = a.course_id where a.session_id = ? and a.semester = ? group by a.course_id";
		$result = $this->db->query($query, [$session, $semester]);
		if ($result->num_rows() <= 0) {
			return false;
		}

		$result = $result->result_array();
		loadClass($this->load, 'sessions');
		foreach ($result as $res) {
			$item = [
				'total' => $res['total'],
				'course_code' => $res['course_code'],
			];
			if ($download == 'yes') {
				$sessions = $this->sessions->getSessionById($session);
				$item = [
					'course_code' => $res['course_code'],
					'session' => $sessions[0]['date'],
					'semester' => ($semester == '1') ? 'First' : 'Second',
					'count' => $res['total'],
				];
			}
			$contents[] = $item;
		}
		if ($download == 'yes') {
			usort($contents, [AdminModel::class, "cmp_obj_2"]);
		} else {
			rsort($contents);
		}

		if ($download == 'yes') {
			$contents = array2csv($contents);
			$filename = "Courses_enrollment_stats_" . date('Y-m-d') . "_download.csv";
			$header = 'text/csv';
			return sendDownload($contents, $header, $filename);
		}
		return $contents;
	}

	/**
	 * @param mixed $session
	 * @param mixed $semester
	 * @param mixed $nth
	 * @return array
	 */
	private function getTopCourseWithoutResult($session, $semester, $nth = 30)
	{
		$download = $this->input->get('download', true) ?? null;
		$result = $this->getDistinctCourseCodeWithScore($session, $semester);
		if (!$result) {
			return [];
		}

		$contents = [];
		loadClass($this->load, 'sessions');
		foreach ($result as $res) {
			$outStanding = $res['total'] - $res['score_count'];
			if ($outStanding > 0) {
				$item = [
					'outstanding_total' => $outStanding,
					'course_code' => $res['course_code'],
				];
				if ($download == 'yes') {
					$sessions = $this->sessions->getSessionById($session);
					$item = [
						'course_code' => $res['course_code'],
						'session' => $sessions[0]['date'],
						'semester' => ($semester == '1') ? 'First' : 'Second',
						'students' => $outStanding,
					];
				}
				$contents[] = $item;
			}
		}

		if ($download == 'yes') {
			usort($contents, [AdminModel::class, "cmp_obj"]);
		} else {
			rsort($contents);
		}
		$contents = array_slice($contents, 0, $nth);
		if ($download == 'yes') {
			$contents = array2csv($contents);
			$filename = "Courses_without_result_" . date('Y-m-d') . "_download.csv";
			$header = 'text/csv';
			return sendDownload($contents, $header, $filename);
		}
		return $contents;
	}

	/**
	 * @param mixed $a
	 * @param mixed $b
	 * @return int
	 */
	public static function cmp_obj($a, $b)
	{
		if ($a['students'] == $b['students']) {
			return 0;
		}
		return ($a['students'] < $b['students']) ? -1 : 1;
	}

	public static function cmp_obj_2($a, $b)
	{
		return $b['count'] - $a['count'];
	}

	/**
	 * @param $session
	 * @param mixed $semester
	 * @return int mixed $session
	 * @deprecated - Not sure if this code is accurate logically
	 */
	private function getCourseWithoutCompleteResultOld($session, $semester)
	{
		$result = $this->getDistinctCourseEnrollment($session, $semester);
		if (!$result) {
			return 0;
		}

		$result1 = $this->getDistinctCourseWithScoreOld($session, $semester);
		if (!$result1) {
			return 0;
		}

		$count = 0;
		$contents = [];
		foreach ($result as $i) {
			foreach ($result1 as $j) {
				if ($i['course_id'] == $j['course_id']) {
					$item = [
						'course_id' => $i['course_id'],
						'sub_total' => $j['total'],
						'total' => $i['total'],
					];
					$contents[] = $item;
				}
			}
		}

		if (!empty($contents)) {
			foreach ($contents as $content) {
				if ($content['sub_total'] != $content['total']) {
					$count++;
				}
			}
		}
		return $count;
	}

	/**
	 * @param mixed $session
	 * @param mixed $semester
	 * @return int|int<0, max>
	 */
	private function getCourseWithoutCompleteResult($session, $semester)
	{
		$result = $this->getDistinctCourseWithScore($session, $semester);
		if (!$result) {
			return 0;
		}

		$content = array_filter((array)$result, function ($v, $k) {
			return $v['score_count'] != $v['total'];
		}, ARRAY_FILTER_USE_BOTH);
		return count($content);
	}

	/**
	 * @param mixed $session
	 * @param mixed $semester
	 * @return int|int<0, max>
	 */
	private function getCourseWithoutAnyResult($session, $semester)
	{
		$result = $this->getDistinctCourseWithScore($session, $semester);
		if (!$result) {
			return 0;
		}

		$content = array_filter((array)$result, function ($v, $k) {
			return $v['score_count'] == 0;
		}, ARRAY_FILTER_USE_BOTH);
		return count($content);
	}

	/**
	 * This is to get fees_description based on date_completed
	 * @return array|<missing>@param mixed $session
	 */
	private function transactionfeeDescription($session): array
	{
		$query = "SELECT b.id,b.description as descrip ,sum(a.mainaccount_amount + a.subaccount_amount) as total, count(*) as countTotal from transaction a join fee_description b on b.id = a.payment_id where a.session = ? and a.payment_status in ('00', '01') group by b.id,descrip order by descrip asc";
		$query = $this->db->query($query, [$session]);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array();
	}

	/**
	 * This is to get fees_description based on date_completed
	 * @return array|<missing>@param mixed $session
	 */
	private function applicantfeeDescription($session): array
	{
		$query = "SELECT b.id,c.description as descrip ,sum(a.mainaccount_amount + a.subaccount_amount) as total, count(*) as countTotal from applicant_transaction a join applicant_payment b on b.id = a.payment_id join fee_description c on c.id = b.description where a.session = ? and a.payment_status in ('00', '01') group by b.id,descrip order by descrip asc";
		$query = $this->db->query($query, [$session]);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array();
	}

	/**
	 * This is to get fees_description based on date_completed
	 * @return array|<missing>@param mixed $session
	 */
	private function transactionCustomfeeDescription($session): array
	{
		$query = "SELECT b.id,b.description as descrip ,sum(a.mainaccount_amount + a.subaccount_amount) as total,count(*) as countTotal from transaction_custom a join fee_description b on b.id = a.payment_id where a.session = ? and a.payment_status in ('00', '01') group by b.id,descrip order by descrip asc";
		$query = $this->db->query($query, [$session]);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array();
	}

	/**
	 * @param mixed $limit
	 * @return array|<missing>
	 */
	public function getDashboardLatestTransaction($limit = 25): array
	{
		$q = $this->input->get('q', true) ?: null;
		$where = '';
		$where1 = '';
		$where2 = '';

		if ($q) {
			$searchList = ['matric_number', 'rrr_code', 'payment_description', 'transaction_ref', 'amount_paid',
			];
			$searchList1 = [
				'rrr_code', 'payment_description', 'transaction_ref', 'amount_paid',
			];
			$searchList2 = [
				'rrr_code', 'payment_description', 'transaction_ref', 'amount_paid',
			];
			$queryString = buildCustomSearchString($searchList, $q);
			$queryString1 = buildCustomSearchString($searchList1, $q);
			$queryString2 = buildCustomSearchString($searchList2, $q);
			$where = " and ($queryString) ";
			$where1 = " and ($queryString1) ";
			$where2 = " and ($queryString2) ";
		}

		$query = "(SELECT concat(firstname, ' ',lastname) as fullname, payment_description as descrip,transaction_ref,rrr_code,
			date_performed,payment_status,a.mainaccount_amount as ui_amount,a.subaccount_amount as dlc_amount,
			(a.mainaccount_amount+a.subaccount_amount) as total_amount,timestamp(date_completed) as orderBy,date_completed as paid_date,matric_number as application_number from transaction a
			left join students b on b.id = a.student_id join
				academic_record c on c.student_id = b.id where a.payment_status in ('00','01') {$where} )
    	UNION
    	 (SELECT concat(firstname, ' ',lastname) as fullname, payment_description as descrip,transaction_ref,rrr_code,
    	 	date_performed,payment_status,a.mainaccount_amount as ui_amount,a.subaccount_amount as dlc_amount,
    	 	(a.mainaccount_amount+a.subaccount_amount) as total_amount,timestamp(date_completed) as orderBy,date_completed as paid_date,b.applicant_id as application_number from applicant_transaction a
    	 left join applicants b on b.id = a.applicant_id where a.payment_status in ('00','01') {$where1} )
    	 UNION
    	 	(SELECT name as fullname, payment_description as descrip,transaction_ref,rrr_code,date_performed,payment_status,
    	 	a.mainaccount_amount as ui_amount,a.subaccount_amount as dlc_amount,(a.mainaccount_amount+a.subaccount_amount) as total_amount,
    	 	timestamp(date_completed) as orderBy,date_completed as paid_date,b.phone_number as application_number from transaction_custom a left join users_custom b on b.id = a.custom_users_id
    	 	where a.payment_status in ('00','01') {$where2} )
    	  	order by orderBy desc limit {$limit}
    	 ";

		$query = $this->db->query($query);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array();
	}

	/**
	 * This calc the top card stats
	 * @return array<string,int>
	 */
	public function getTopData(): array
	{
		$admissionSession = $_GET['session'] ?? get_setting('active_admission_session');
		$session = $_GET['session'] ?? $this->currentTransactionSession();

		return [
			'interested_applicant' => (int)$this->applicantInterested($admissionSession),
			'admitted_applicant' => (int)$this->applicantIsAdmitted($admissionSession),
			'accepted_applicant' => (int)$this->applicantAccepted($admissionSession),
			'registered_applicant' => (int)$this->applicantRegistered($admissionSession),
			'active_student' => (int)$this->totalActiveStudent($session),
			'active_student_second' => (int)$this->totalActiveStudent2($session),
			'passive_student' => (int)$this->totalPassiveStudent($session),
			'graduating_student' => (int)$this->totalGraduatingStudent($session),
			'graduated_student' => (int)0,
		];
	}

	/**
	 * @return array|<missing>
	 */
	public function getStudentDistribution()
	{
		$result = [];
		$session = $_GET['session'] ?? $this->currentTransactionSession();
		$level = $_GET['lv'] ?? false;
		$program = $_GET['pg'] ?? false;

		return $this->totalStudentByFaculty($session);
	}

	public function getStudentEntryDistribution()
	{
		$result = [];
		$session = $_GET['session'] ?? $this->currentTransactionSession();

		if (isset($_GET['dashboard_department'])) {
			$department = $_GET['dashboard_department'];
			$query = "SELECT distinct if(c.date, c.date, 'Unknown') as name, count(distinct a.student_id) as total from transaction a
			join academic_record b on b.student_id = a.student_id left join sessions c on c.id = b.session_of_admission
        	right join programme d on d.id = b.programme_id right join department e on e.id = d.department_id where e.id = '$department'
        	and a.payment_status in ('00','01') and (a.payment_id = '1') and a.session = ? group by c.date having total > 0 order by name desc";
		} else {
			$query = "SELECT distinct if(c.date, c.date, 'Unknown') as name, count(distinct a.student_id) as total from transaction a
			join academic_record b on b.student_id = a.student_id left join sessions c on c.id = b.session_of_admission
        	where a.payment_status in ('00','01') and (a.payment_id = '1') and a.session = ? group by c.date having total > 0
        	order by name desc";
		}

		$result = [];
		$query = $this->db->query($query, [$session]);
		if ($query->num_rows() <= 0) {
			return [];
		}

		$contents = $query->result_array();
		// foreach ($contents as $content) {
		// 	$sessionPrefix = explode('/', $content['name']);
		// 	if (isset($sessionPrefix[0]) && $sessionPrefix[0] >= '2010') {
		// 		$result[] = $content;
		// 	}
		// }
		return $contents;
	}

	/**
	 * @return array
	 */
	public function getEnrollmentAttrition()
	{
		$option = $this->input->get('filter');
		$session = $this->input->get('session');
		$session = isset($_GET['session']) ? $_GET['session'] : $this->currentTransactionSession();
		$totalDistrix = null;

		if ($option == 'department') {
			$totalDistrix = $this->totalActiveStudentByDepartment($session);
		} else {
			$totalDistrix = $this->totalActiveStudentByFaculty($session);
		}
		return $totalDistrix;
	}

	/**
	 * @return array|array<int,array<string,mixed>>
	 */
	public function getTransactionLevelDistrix(string $type = 'admin'): array
	{
		$session = $_GET['session'] ?? $this->currentTransactionSession();
		$semester = $_GET['sem'] ?? 1;
		if ($type == 'admin') {
			return $this->getActiveStudentTransactionByLevel($session, $semester);
		} else {
			return $this->getActiveStudentTransactionPerLevel($session, $semester);
		}
	}

	public function getTransactionLevelDistrixNew(): array
	{
		$session = $_GET['session'] ?? $this->currentTransactionSession();
		$semester = $_GET['sem'] ?? 1;
		return $this->getActiveStudentTransactionPerLevelNew($session, $semester);
	}

	/**
	 * @return array<string,mixed>
	 */
	public function getTransactionDistrix()
	{
		$session = $_GET['session'] ?? $this->currentTransactionSession();
		// return $this->getRemittanceGraph($session, 'less', '2023-09-08');
		return $this->getRemittanceGraph($session);
	}

	/**
	 * @return array<string,mixed>
	 */
	public function getRemittanceinFlow()
	{
		$session = $_GET['session'] ?? $this->currentTransactionSession();
		return $this->getRemittanceInflowLatest($session, 'greater', '2023-09-09');
	}

	/**
	 * @return array<string,mixed>
	 */
	public function getResultDistrix(): array
	{
		$session = $_GET['session'] ?? $this->currentTransactionSession();
		$semester = $_GET['sem'] ?? 1;

		$activeStudent = $semester == 1 ?
			$this->totalActiveStudent($session) : $this->totalActiveStudent2($session);
		$courseEntrolled = $this->getStudentWithEnrollment($session, $semester);
		$studentWithoutComplete = $this->getStudentWithResult($session, $semester, 'incomplete');
		$studentWithComplete = $this->getStudentWithResult($session, $semester, 'complete');
		$studentWithoutResult = $this->getStudentWithResult($session, $semester, 'zero');
		return [
			'active_student' => $activeStudent,
			'course_enrollment' => $courseEntrolled,
			'student_with_complete' => $studentWithComplete,
			'student_without_complete' => $studentWithoutComplete,
			'student_without_results' => $studentWithoutResult,
		];
	}

	/**
	 * @return array<string,mixed>
	 */
	public function getCourseDistrix(): array
	{
		$session = $_GET['session'] ?? $this->currentTransactionSession();
		$semester = $_GET['sem'] ?? 1;
		return [
			'total_courses_registered' => (int)$this->getCourseCount($session, $semester),
			'course_with_result' => (int)$this->getNumberOfCourseWithScore($session, $semester),
			'course_without_complete' => $this->getCourseWithoutCompleteResult($session, $semester),
			'course_without_result' => $this->getCourseWithoutAnyResult($session, $semester),
		];
	}

	/**
	 * @return int|<missing>
	 */
	public function getCourseWithoutDistrix()
	{
		$session = $_GET['session'] ?? $this->currentTransactionSession();
		$semester = $_GET['sem'] ?? 1;
		$pageSize = $_GET['page_size'] ?? null;
		return $this->getTopCourseWithoutResult($session, $semester, $pageSize);
	}

	/**
	 * @return array<string,mixed>
	 */
	public function getFinanceDistrix(): array
	{
		$session = $_GET['session'] ?? $this->currentTransactionSession();
		$session2 = $_GET['session'] ?? $this->currentSession();

		return [
			'total_payment' => $this->totalPayment($session),
			'payment_desc' => $this->transactionfeeDescription($session),
			'applicant_payment_desc' => $this->applicantfeeDescription($session2),
			'payment_custom_desc' => $this->transactionCustomfeeDescription($session),
			'e_collections' => $this->totalTransactionfeeDescription($session),
			'ui_inflow' => $this->mainTransactionfeeDescription($session),
			'dlc_inflow' => $this->subTransactionfeeDescription($session),
		];
	}

	/**
	 * @param array<int,mixed> $datas
	 * @return array
	 */
	public function removePorgrammePrefix(array $datas = [], string $key = 'name'): array
	{
		$content = [];
		if (!empty($datas)) {
			foreach ($datas as $data) {
				$data[$key] = AdminModelTrait::removeSingleProgrammePrefix($data[$key]);

				$content[] = $data;
			}
		}

		return $content;
	}

	/**
	 * @param array<int,mixed> $datas
	 * @return array
	 */
	public function removeAssocPorgrammePrefix(array $datas = []): array
	{
		$content = [];
		if (!empty($datas)) {
			foreach ($datas as $data) {
				$content[] = AdminModelTrait::removeSingleProgrammePrefix($data);
			}
		}

		return $content;
	}

	private function transactionSession(){
		loadClass($this->load, 'sessions');
		$result = $this->sessions->getCompleteTransactionSession();
		return $result;
	}

	private function getSingleStudentLevelDistribution($filterBy, $session){
		$query = null;
		$paymentValidation = get_setting('session_semester_payment_start');
		$semester = ($session >= $paymentValidation) ? true : false;

		if ($filterBy === 'tranx_session') {
			if ($semester) {
				if (isset($_GET['dashboard_department'])) {
					$department = $_GET['dashboard_department'];
					$query = "SELECT distinct d.date as name,b.level,count(distinct b.student_id) as total from transaction b join
					academic_record c on c.student_id=b.student_id join sessions d on d.id = b.session right join programme e on e.id = c.programme_id
	                right join department f on f.id = e.department_id where b.payment_status in ('00','01') and b.payment_id in ('1', '2')
	        		and f.id = '$department' and b.session = ? group by d.date,b.level order by name asc ";
				} else {
					$query = "SELECT distinct d.date as name,b.level,count(distinct b.student_id) as total from transaction b join
					academic_record c on c.student_id=b.student_id join sessions d on d.id = b.session
	            	where b.payment_status in ('00','01') and b.payment_id in ('1', '2') and b.session = ? group by d.date,b.level order by name desc";
				}
			}else{
				if (isset($_GET['dashboard_department'])) {
					$department = $_GET['dashboard_department'];
					$query = "SELECT distinct d.date as name,b.level,count(distinct b.student_id) as total from transaction b join
					academic_record c on c.student_id=b.student_id join sessions d on d.id = b.session right join programme e on e.id = c.programme_id
	                right join department f on f.id = e.department_id where b.payment_status in ('00','01') and b.payment_id = '1'
	        		and f.id = '$department' and b.session = ? group by d.date,b.level order by name asc ";
				} else {
					$query = "SELECT d.date as name,b.level,count(distinct b.student_id) as total from transaction b join
					academic_record c on c.student_id=b.student_id join sessions d on d.id = b.session
	            	where b.payment_status in ('00','01') and b.payment_id = '1' and b.session = ? group by d.date,b.level order by name desc";
				}
			}
		}

		if (!$query) {
			return [];
		}

		$query = $this->db->query($query, [$session]);
		if ($query->num_rows() <= 0) {
			return [];
		}

		return $query->result_array();
	}

	public function getStudentLevelEntryDistribution()
	{
		$query = null;
		$content = [];
		$content1 = [];
		$filterBy = $this->input->get('filterBy') ?: 'tranx_session';
		$sessions = $this->transactionSession();
		
		foreach($sessions as $session){
			$result = $this->getSingleStudentLevelDistribution($filterBy, $session['id']);
			if($result){
				$content1[] = $session['value'];
				$content[] = [
					'name' => $session['value'],
					'value' => $this->groupDataByLevel($result),
				];
			}
			
		}

		$content = $this->formatGroupLevelCategory($content);
		return [$content, $content1];
	}

	private function processStudentLevelSessionDistribution($session = null)
	{
		$data = null;
		if ($session !== 'none') {
			if (isset($_GET['dashboard_department'])) {
				$department = $_GET['dashboard_department'];
				$query = "SELECT distinct d.date as name,b.level, count(distinct b.student_id) as total from transaction b join
				academic_record c on c.student_id=b.student_id join sessions d on d.id = c.session_of_admission
				right join programme e on e.id = c.programme_id right join department f on f.id = e.department_id where f.id = '$department'
				and b.payment_status in ('00','01') and b.payment_id = '1' and b.session = ? group by d.date,b.level order by name desc";
			} else {
				$query = "SELECT distinct d.date as name,b.level, count(distinct b.student_id) as total from transaction b join
				academic_record c on c.student_id=b.student_id join sessions d on d.id = c.session_of_admission where
				b.payment_status in ('00','01') and b.payment_id = '1' and b.session = ? group by d.date,b.level order by name desc";
			}
			$data[] = $session;
		} else {
			if (isset($_GET['dashboard_department'])) {
				$department = $_GET['dashboard_department'];
				$query = "SELECT distinct d.date as name,c.current_level as level, count(distinct c.student_id) as total from
    			academic_record c join sessions d on d.id = c.session_of_admission right join programme e on e.id = c.programme_id
                right join department f on f.id = e.department_id where f.id = '$department' group by d.date,c.current_level order by name desc";
			} else {
				$query = "SELECT distinct d.date as name,c.current_level as level, count(distinct c.student_id) as total from
    			academic_record c join sessions d on d.id = c.session_of_admission group by d.date,c.current_level order by name desc";
			}
		}

		$query = $this->db->query($query, $data);
		if ($query->num_rows() <= 0) {
			return [];
		}

		return $query->result_array();
	}

	public function getStudentLevelEntrySessionDistribution(): array
	{
		$session = $_GET['session'] ?? $this->currentTransactionSession();
		$result = $this->processStudentLevelSessionDistribution($session);

		$groupData = AdminModelTrait::groupRelatedDataToAssoc($result);
		$uniqueSession = AdminModelTrait::getUniqueNameFromAssoc($result);
		$content = [];
		$content1 = $uniqueSession;

		if (!empty($groupData)) {
			foreach ($uniqueSession as $name) {
				$content[] = [
					'name' => $name,
					'value' => $this->groupDataByLevel($groupData[$name]),
				];
			}
		}

		$content = $this->formatGroupLevelCategory($content);
		return [$content, $content1];
	}

	private function formatGroupLevelCategory(array $data = []): array
	{
		$levelData = $this->levelData();
		$content = [];
		foreach ($levelData as $level) {
			$result = $this->groupByLevelCategory($data, $level);
			if (!empty($result)) {
				$content[] = $result;
			}
		}
		return $content;
	}

	private function groupByLevelCategory(array $data, $level): array
	{
		$content = [];
		if (!empty($data)) {
			foreach ($data as $d) {
				if (array_key_exists($level, $d['value']) !== false) {
					if (array_key_exists($level, $content) !== false) {
						$content[$level][] = $d['value'][$level];
					} else {
						$content[$level][] = $d['value'][$level];
					}
				}
			}
		}

		$newLevel = formatStudentLevel($level);
		if (empty($content[$level])) {
			$content[$level] = [];
		}
		return ['name' => $newLevel, 'data' => $content[$level]];
	}

	private function groupDataByLevel(array $data = []): array
	{
		$return = [];
		foreach ($data as $res) {
			$key = $res['level'];
			$num = $res['total'];
			if (array_key_exists($key, $return) === false) {
				$return[$key] = 0;
			}
			$return[$key] = $return[$key] + $num;
		}

		$result = $this->fillMissingLevelWithZero($return);
		$return = $return + $result; // merging without disrupting the orig form

		ksort($return);
		return $return;
	}

	private function levelData(): array
	{
		return [1, 2, 3, 4, 401, 402, 5, 501, 502];
	}

	private function fillMissingLevelWithZero(array $data = []): array
	{
		$content = [];
		$levelData = $this->levelData();

		if (!empty($data)) {
			$differences = array_diff($levelData, array_keys($data));
			if (!empty($differences)) {
				foreach ($differences as $item) {
					$content[$item] = 0;
				}
			}
		}
		return $content;
	}

	private function processStudentDepartmentLevel($session)
	{
		$paymentValidation = get_setting('session_semester_payment_start');
		$semester = ($session >= $paymentValidation) ? true : false;
		$query = null;

		if ($semester) {
			$query = "SELECT distinct e.name,f.slug as faculty,a.level,e.slug as department, count(distinct a.student_id) as total from transaction a right join academic_record c on c.student_id = a.student_id right join programme d on d.id = c.programme_id right join department e on e.id = d.department_id right join faculty f on f.id = d.faculty_id where a.payment_id in ('1', '2') and a.payment_status in ('01', '00') and a.session = ? and e.type='academic' group by e.name,f.slug,a.level,e.slug order by f.slug, e.name asc";
		} else {
			$query = "SELECT distinct e.name,f.slug as faculty,a.level,e.slug as department, count(a.student_id) as total from transaction a right join academic_record c on c.student_id = a.student_id right join programme d on d.id = c.programme_id right join department e on e.id = d.department_id right join faculty f on f.id = d.faculty_id where a.payment_id = '1' and a.payment_status in ('01', '00') and a.session = ? and e.type='academic' group by e.name,f.slug,a.level,e.slug order by f.slug, e.name asc";
		}
		$query = $this->db->query($query, [$session]);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array();
	}

	public function getStudentDepartmentLevel()
	{
		$session = $this->input->get('session', true) ?: $this->currentSession();
		$result = $this->processStudentDepartmentLevel($session);
		$groupedFaculty = AdminModelTrait::groupRelatedDataToAssoc($result, 'faculty');
		$departmentData = $this->processByDepartmentInFaculty($groupedFaculty);

		return $departmentData;

	}

	private function processByDepartmentInFaculty(array $data): array
	{
		$contents = [];

		foreach ($data as $key => $values) {
			$content = [];
			foreach ($values as $val) {
				$newLevel = strlen((string)$val['level']) < 3 ? $val['level'] . "00" : $val['level'];
				$payload = [
					'level' => $newLevel,
					'total' => $val['total'],
				];

				$content[$val['name']][] = $payload;
			}

			$contents[] = [
				'faculty' => $key,
				'departments' => $this->fillMissingLevelWithNil($content),
			];
		}
		return $contents;
	}

	private function minimallevelData(): array
	{
		return [100, 200, 300, 400, 500, 401, 501];
	}

	private function fillMissingLevelWithNil(array $data = []): array
	{
		$contents = [];
		$levelData = $this->minimallevelData();

		foreach ($data as $key => $values) {
			$items = array_column($values, 'level');
			$differences = array_diff($levelData, $items);

			if (!empty($differences)) {
				$content = [];
				foreach ($differences as $item) {
					$newLevel = strlen($item) < 3 ? $item . "00" : $item;
					$payload = [
						'level' => (string)$newLevel,
						'total' => 'Nil',
					];
					$content[$key][] = $payload;
				}

				$result = array_merge($content[$key], $values);
				usort($result, [AdminModel::class, "cmp_level_obj"]);
				$contents[$key] = $result;
			} else {
				$result = $values;
				usort($result, [AdminModel::class, "cmp_level_obj"]);
				$contents[$key] = $result;
			}
		}
		return $contents;
	}

	public static function cmp_level_obj($a, $b)
	{
		if ($a['level'] == $b['level']) {
			return 0;
		}
		return ($a['level'] < $b['level']) ? -1 : 1;
	}

	/**
	 * @param mixed $student
	 * @return bool|<missing>
	 */
	public function validateStudentPaymentTransaction($student)
	{
		$currentSemester = get_setting('active_semester');
		$currentSession = get_setting('active_session_student_portal');

		$query = "SELECT * from transaction where payment_id = ? and student_id = ? and session = ? and payment_status in ('00', '01')";
		$result = $this->db->query($query, [$currentSemester, $student, $currentSession]);
		if ($result->num_rows() <= 0) {
			return false;
		}
		return $result->result_array()[0];
	}

	/**
	 * @param mixed $student
	 * @param mixed $session
	 * @return bool
	 */
	public function validateStudentProgrammeStatus($student, $session)
	{
		$query = "SELECT * from student_change_of_programme where student_id = ? and session = ?";
		$result = $this->db->query($query, [$student, $session]);
		if ($result->num_rows() <= 0) {
			return false;
		}
		return true;
	}

	/**
	 * @return bool|<missing>
	 */
	public function getAllPayments()
	{
		$query = "SELECT payment.id, fee_description.description, sessions.date as session, payment.fee_category,payment.payment_code from payment left join fee_description on fee_description.id = payment.description left join sessions on sessions.id = payment.session order by id desc";
		$result = $this->db->query($query);
		if ($result->num_rows() <= 0) {
			return false;
		}
		return $result->result_array();
	}

	public function getTotalAnnualFinance(string $type, string $orderBy = 'asc')
	{
		if ($type == 'debit') {
			$query = "SELECT label, sum(total) as total from
			(
			SELECT year(a.date_performed) as label, sum(a.service_charge) as total from applicant_transaction a where a.payment_status in ('00','01') group by label having label >= '2018'
			UNION
			SELECT year(b.date_performed) as label, sum(b.service_charge) as total from transaction b where b.payment_status in ('00','01') group by label having label >= '2018'
			UNION
			SELECT year(c.date_performed) as label, sum(c.service_charge) as total from transaction_custom c where c.payment_status in ('00','01') group by label having label >= '2018'
			)
			as x group by label order by label $orderBy
			";
		} else if ($type == 'collections') {
			$query = "SELECT label, sum(total) as total from
			(
			SELECT year(a.date_performed) as label, sum(a.amount_paid - a.service_charge) as total from applicant_transaction a where a.payment_status in ('00','01') group by label having label >= '2018'
			UNION
			SELECT year(b.date_performed) as label, sum(b.amount_paid - b.service_charge) as total from transaction b where b.payment_status in ('00','01') group by label having label >= '2018'
			UNION
			SELECT year(c.date_performed) as label, sum(c.amount_paid - c.service_charge) as total from transaction_custom c where c.payment_status in ('00','01') group by label having label >= '2018'
			)
			as x group by label order by label $orderBy
			";
		}
		$query = $this->db->query($query);
		return $query->result_array();
	}

	/**
	 * @return array
	 */
	public function getTotalSessionFinance(string $type): array
	{
		if ($type == 'debit') {
			$query = "SELECT label, sum(total) as total from
			(
			SELECT if(b.date is null, 'N/A', b.date) as label, sum(a.service_charge) as total from applicant_transaction a left join sessions b on b.id = a.session where a.payment_status in ('00','01') group by label
			UNION SELECT if(d.date is null, 'N/A', d.date) as label, sum(c.service_charge) as total from transaction c left join sessions d on d.id = c.session where c.payment_status in ('00','01') group by label
			)
			as x group by label order by label asc
			";
		} else if ($type == 'collections') {
			$query = "SELECT label, sum(total) as total from
			(
			SELECT if(b.date is null, 'N/A', b.date) as label, sum(a.amount_paid - a.service_charge) as total from applicant_transaction a left join sessions b on b.id = a.session where a.payment_status in ('00','01') group by label
			UNION SELECT if(d.date is null, 'N/A', d.date) as label, sum(c.amount_paid - c.service_charge) as total from transaction c left join sessions d on d.id = c.session where c.payment_status in ('00','01') group by label
			)
			as x group by label order by label asc
			";
		}
		$query = $this->db->query($query);
		$content = $query->result_array();

		if (count($content) <= 0) {
			return [];
		}

		$result = [];
		foreach ($content as $cont) {
			$sessionLatter = explode('/', $cont['label']);
			if (isset($sessionLatter[0]) && $sessionLatter[0] >= '2018') {
				$result[] = $cont;
			}
		}

		return $result;
	}

	public function getTotalYearFinance(string $type)
	{
		return $this->getTotalAnnualFinance($type, 'desc');
	}

	/**
	 * @param mixed $month
	 * @return array|<missing>
	 */
	private function getMonthFinanceData(string $type, $month, string $year): array
	{
		if ($type == 'debit') {
			$query = "
				SELECT label, sum(total) as total, qty, payment_id from
				(
					SELECT b.description as label, sum(a.service_charge) as total,count(b.description) as qty,a.payment_id from transaction a left join fee_description b on b.id = a.payment_id where a.payment_status in ('00', '01') and year(a.date_performed) = '$year' and month(a.date_performed) = '$month' group by label, a.payment_id having total > 0
					UNION
					SELECT e.description as label, sum(c.service_charge) as total,count(e.description) as qty,e.id as payment_id from applicant_transaction c left join applicant_payment d on d.id = c.payment_id left join fee_description e on e.id = d.description where c.payment_status in ('00', '01') and year(c.date_performed) = '$year' and month(c.date_performed) = '$month' group by label,payment_id having total > 0
					UNION
					SELECT g.description as label, sum(f.service_charge) as total,count(g.description) as qty,f.payment_id from transaction_custom f left join fee_description g on g.id = f.payment_id where f.payment_status in ('00', '01') and year(f.date_performed) = '$year' and month(f.date_performed) = '$month' group by label,f.payment_id having total > 0
				)
				as x group by label, qty, payment_id order by label asc
			";
		} else if ($type == 'collections') {
			$query = "
				SELECT label, sum(total) as total, qty,payment_id from
				(
					SELECT b.description as label, sum(a.amount_paid - a.service_charge) as total,count(b.description) as qty,a.payment_id from transaction a left join fee_description b on b.id = a.payment_id where a.payment_status in ('00', '01') and year(a.date_performed) = '$year' and month(a.date_performed) = '$month' group by label,a.payment_id having total > 0
					UNION
					SELECT e.description as label, sum(c.amount_paid - c.service_charge) as total,count(e.description) as qty,e.id as payment_id from applicant_transaction c left join applicant_payment d on d.id = c.payment_id left join fee_description e on e.id = d.description where c.payment_status in ('00', '01') and year(c.date_performed) = '$year' and month(c.date_performed) = '$month' group by label,payment_id having total > 0
					UNION
					SELECT g.description as label, sum(f.amount_paid - f.service_charge) as total,count(g.description) as qty,f.payment_id from transaction_custom f left join fee_description g on g.id = f.payment_id where f.payment_status in ('00', '01') and year(f.date_performed) = '$year' and month(f.date_performed) = '$month' group by label,f.payment_id having total > 0
				)
				as x group by label, qty, payment_id  order by label asc
			";
		}

		$query = $this->db->query($query);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array();
	}

	public function getDayPerMonthFinanceData(string $type, $month, string $year): array
	{
		return $this->processDayPerMonthFinanceData($type, $month, $year);
	}

	private function processDayPerMonthFinanceData($type, $month, $year): array
	{
		if ($type == 'debit') {
			$query = "
				SELECT dayOfMonth, label, sum(total) as total, qty, payment_id from
				(
					SELECT date_format(a.date_performed,'%d-%M-%Y') as dayOfMonth, b.description as label, sum(a.service_charge) as total,count(b.description) as qty,a.payment_id from transaction a left join fee_description b on b.id = a.payment_id where a.payment_status in ('00', '01') and year(a.date_performed) = '$year' and month(a.date_performed) = '$month' group by dayOfMonth, label, a.payment_id having total > 0
					UNION
					SELECT date_format(c.date_performed,'%d-%M-%Y') as dayOfMonth, e.description as label, sum(c.service_charge) as total,count(e.description) as qty,e.id as payment_id from applicant_transaction c left join applicant_payment d on d.id = c.payment_id left join fee_description e on e.id = d.description where c.payment_status in ('00', '01') and year(c.date_performed) = '$year' and month(c.date_performed) = '$month' group by dayOfMonth, label,payment_id having total > 0
					UNION
					SELECT date_format(f.date_performed,'%d-%M-%Y') as dayOfMonth, g.description as label, sum(f.service_charge) as total,count(g.description) as qty,f.payment_id from transaction_custom f left join fee_description g on g.id = f.payment_id where f.payment_status in ('00', '01') and year(f.date_performed) = '$year' and month(f.date_performed) = '$month' group by dayOfMonth,label,f.payment_id having total > 0
				)
				as x group by dayOfMonth, label, qty, payment_id order by dayOfMonth asc
			";
		} else if ($type == 'collections') {
			$query = "
				SELECT dayOfMonth, label, sum(total) as total, qty,payment_id from
				(
					SELECT date_format(a.date_performed,'%d-%M-%Y') as dayOfMonth, b.description as label, sum(a.amount_paid - a.service_charge) as total,count(b.description) as qty,a.payment_id from transaction a left join fee_description b on b.id = a.payment_id where a.payment_status in ('00', '01') and year(a.date_performed) = '$year' and month(a.date_performed) = '$month' group by dayOfMonth, label,a.payment_id having total > 0
					UNION
					SELECT date_format(c.date_performed,'%d-%M-%Y') as dayOfMonth, e.description as label, sum(c.amount_paid - c.service_charge) as total,count(e.description) as qty,e.id as payment_id from applicant_transaction c left join applicant_payment d on d.id = c.payment_id left join fee_description e on e.id = d.description where c.payment_status in ('00', '01') and year(c.date_performed) = '$year' and month(c.date_performed) = '$month' group by dayOfMonth, label,payment_id having total > 0
					UNION
					SELECT date_format(f.date_performed,'%d-%M-%Y') as dayOfMonth, g.description as label, sum(f.amount_paid - f.service_charge) as total,count(g.description) as qty,f.payment_id from transaction_custom f left join fee_description g on g.id = f.payment_id where f.payment_status in ('00', '01') and year(f.date_performed) = '$year' and month(f.date_performed) = '$month' group by dayOfMonth, label,f.payment_id having total > 0
				)
				as x group by dayOfMonth, label, qty, payment_id  order by dayOfMonth asc
			";
		}

		$query = $this->db->query($query);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		$return = $query->result_array();
		$groupData = AdminModelTrait::groupRelatedDataToAssoc($return, 'dayOfMonth');
		return $this->processGroupDataPerDay($groupData);
	}

	private function processGroupDataPerDay(array $data): array
	{
		$contents = [];
		foreach ($data as $key => $item) {
			$payload = [
				'sch_fees' => $this->filterBySchFee($item),
				'sundry_fees' => $this->filterBySundryFee($item),
				'grandTotal' => [
					'qty' => $this->processMonthCumQty($item),
					'total' => $this->processMonthCumTotal($item),
				],
				// 'data' => $item,
			];
			$contents[] = [
				'label' => $key,
				'data' => $payload,
			];
		}

		return $contents;
	}

	/**
	 * @param mixed $date
	 * @return array|<missing>
	 */
	public function getDayFinanceData(string $type, $date)
	{
		if ($type == 'debit') {
			$query = "
				SELECT label, sum(total) as total, qty, payment_id from
				(
					SELECT b.description as label, sum(a.service_charge) as total,count(b.description) as qty,a.payment_id from transaction a left join fee_description b on b.id = a.payment_id where a.payment_status in ('00', '01') and cast(a.date_performed as date) = '$date' group by label,a.payment_id having total > 0
					UNION
					SELECT e.description as label, sum(c.service_charge) as total,count(e.description) as qty,e.id as payment_id from applicant_transaction c left join applicant_payment d on d.id = c.payment_id left join fee_description e on e.id = d.description where c.payment_status in ('00', '01') and cast(c.date_performed as date) = '$date' group by label,payment_id having total > 0
					UNION
					SELECT g.description as label, sum(f.service_charge) as total,count(g.description) as qty,f.payment_id from transaction_custom f left join fee_description g on g.id = f.payment_id where f.payment_status in ('00', '01') and cast(f.date_performed as date) = '$date' group by label,f.payment_id having total > 0
				)
				as x group by label, qty, payment_id order by label asc
			";
		} else if ($type == 'collections') {
			$query = "
				SELECT label, sum(total) as total, qty, payment_id from
				(
					SELECT b.description as label, sum(a.amount_paid - a.service_charge) as total,count(b.description) as qty,a.payment_id from transaction a left join fee_description b on b.id = a.payment_id where a.payment_status in ('00', '01') and cast(a.date_performed as date) = '$date' group by label,a.payment_id having total > 0
					UNION
					SELECT e.description as label, sum(c.amount_paid - c.service_charge) as total,count(e.description) as qty,e.id as payment_id from applicant_transaction c left join applicant_payment d on d.id = c.payment_id left join fee_description e on e.id = d.description where c.payment_status in ('00', '01') and cast(c.date_performed as date) = '$date' group by label,payment_id having total > 0
					UNION
					SELECT g.description as label, sum(f.amount_paid - f.service_charge) as total,count(g.description) as qty,f.payment_id from transaction_custom f left join fee_description g on g.id = f.payment_id where f.payment_status in ('00', '01') and cast(f.date_performed as date) = '$date' group by label,f.payment_id having total > 0
				)
				as x group by label, qty, payment_id  order by label asc
			";
		}

		$query = $this->db->query($query);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		$result = $query->result_array();
		$payload = [
			'sch_fees' => $this->filterBySchFee($result),
			'sundry_fees' => $this->filterBySundryFee($result),
			'data' => $result,
			'grandTotal' => $this->processMonthCumTotal($result),
		];

		return $payload;
	}

	/**
	 * @param array<int,mixed> $data
	 */
	private function filterBySchFee(array $data)
	{
		$result = array_filter($data, function ($v, $k) {
			return $v['payment_id'] == 1 || $v['payment_id'] == 2;
		}, ARRAY_FILTER_USE_BOTH);

		return $this->calcTotalPaymentType($result);
	}

	/**
	 * @param array<int,mixed> $data
	 */
	private function filterBySundryFee(array $data)
	{
		$result = array_filter($data, function ($v, $k) {
			return $v['payment_id'] != 1 && $v['payment_id'] != 2;
		}, ARRAY_FILTER_USE_BOTH);

		return $this->calcTotalPaymentType($result);
	}

	/**
	 * @param array<int,mixed> $data
	 * @return array<string,int>
	 */
	private function calcTotalPaymentType(array $data)
	{
		$total = 0;
		$totalQty = 0;
		if (count($data) > 0) {
			foreach ($data as $item) {
				$total += $item['total'];
				$totalQty += $item['qty'];
			}
		}

		return ['qty' => $totalQty, 'total' => $total];
	}

	/**
	 * This is for debugging sake on monthly data
	 * @param mixed $month
	 * @return array|<missing>
	 */
	private function totalMonthData(string $type, $month, string $year)
	{
		if ($type == 'debit') {
			$query = "
				SELECT sum(total) as total from
				(
					SELECT sum(a.service_charge) as total from transaction a where a.payment_status in ('00', '01') and year(a.date_performed) = '$year' and month(a.date_performed) = '$month'
					UNION
					SELECT sum(c.service_charge) as total from applicant_transaction c where c.payment_status in ('00', '01') and year(c.date_performed) = '$year' and month(c.date_performed) = '$month'
					UNION
					SELECT sum(f.service_charge) as total from transaction_custom f where f.payment_status in ('00', '01') and year(f.date_performed) = '$year' and month(f.date_performed) = '$month'
				)
				as x
			";
		} else if ($type == 'collections') {
			$query = "
				SELECT sum(total) as total from
				(
					SELECT sum(a.amount_paid - a.service_charge) as total from transaction a where a.payment_status in ('00', '01') and year(a.date_performed) = '$year' and month(a.date_performed) = '$month'
					UNION
					SELECT e.description as label, sum(c.amount_paid - c.service_charge) as total from applicant_transaction c where c.payment_status in ('00', '01') and year(c.date_performed) = '$year' and month(c.date_performed) = '$month'
					UNION
					SELECT g.description as label, sum(f.amount_paid - f.service_charge) as total from transaction_custom f where f.payment_status in ('00', '01') and year(f.date_performed) = '$year' and month(f.date_performed) = '$month'
				)
				as x
			";
		}

		$query = $this->db->query($query);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array()[0]['total'];
	}

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public function getQuarterFinanceData(string $type, string $year)
	{
		$quarterRange = [range(1, 3), range(4, 6), range(7, 9), range(10, 12)];
		$payload = [];
		foreach ($quarterRange as $key => $quarter) {
			$item = [
				'id' => "Q" . ($key + 1),
				'months' => $this->processQuarterData($quarter, $type, $year),
			];
			$payload[] = $item;
		}
		return $payload;
	}

	/**
	 * @param array<int,mixed> $data
	 * @return array<int,array<string,mixed>>
	 */
	private function processQuarterData(array $data, string $type, string $year)
	{
		$result = [];

		foreach ($data as $month) {
			$content = $this->getMonthFinanceData($type, $month, $year);
			$item = [
				'name' => monthNumberToName($month),
				'sch_fees' => $this->filterBySchFee($content),
				'sundry_fees' => $this->filterBySundryFee($content),
				'data' => $content,
				'total' => $this->processMonthCumTotal($content),
				// 'total1' => $this->totalMonthData($type, $month, $year),
			];
			$result[] = $item;
		}
		return $result;
	}

	/**
	 * @param array<int,mixed> $data
	 * @return int|float
	 */
	private function processMonthCumTotal(array $data)
	{
		if (count($data) <= 0) {
			return 0;
		}

		$total = array_map(function ($subArray) {
			return $subArray['total'];
		}, $data);

		return array_sum($total);
	}

	private function processMonthCumQty(array $data)
	{
		if (count($data) <= 0) {
			return 0;
		}

		$total = array_map(function ($subArray) {
			return $subArray['qty'];
		}, $data);

		return array_sum($total);
	}

	public function loadRSsosSundryGenderDistribution()
	{
		$session = isset($_GET['session']) ? $_GET['session'] : $this->currentTransactionSession();
		$type = isset($_GET['sundry_type']) ? $_GET['sundry_type'] : 'suspension';
		$code = ($type === 'suspension') ? 'SuS' : 'RoS';

		$query = "SELECT distinct if(gender = '', 'Null', gender) as name, count(*) as total from transaction a join students b on a.student_id = b.id join fee_description c on c.id = a.payment_id where a.payment_status in ('00', '01') and a.session = ? and c.code = ? group by name order by name";
		$query = $this->db->query($query, [$session, $code]);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array();

	}

	public function loadRSsosSundryAgeDistribution()
	{
		$session = isset($_GET['session']) ? $_GET['session'] : $this->currentTransactionSession();
		$type = isset($_GET['sundry_type']) ? $_GET['sundry_type'] : 'suspension';
		$code = ($type === 'suspension') ? 'SuS' : 'RoS';

		// $query = "SELECT distinct count(*) as total, if(TIMESTAMPDIFF(YEAR, dob, CURDATE()) = '', 'unknown', TIMESTAMPDIFF(YEAR, dob, CURDATE())) AS age from students a join transaction b on b.student_id = a.id join fee_description c on c.id = b.payment_id where b.payment_status in ('00','01') and b.session = ? and c.code = ? group by age order by total";

		$query = "SELECT distinct count(*) as total, CASE
        WHEN dob IS NOT NULL AND dob <> '' THEN
            TIMESTAMPDIFF(YEAR, dob, CURDATE())
        ELSE
            NULL
    	END AS age from students a join transaction b on b.student_id = a.id join fee_description c on c.id = b.payment_id
    	where b.payment_status in ('00','01') and b.session = ? and c.code = ? group by age order by total";

		$query = $this->db->query($query, [$session, $code]);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		$result = $query->result_array();
		$result = AdminModelTrait::groupDataByAge($result, 'Null');
		return AdminModelTrait::formatRangeToStandard($result);
	}

	public function loadStudentPwdDistribution()
	{
		if (isset($_GET['dashboard_department'])) {
			$department = $_GET['dashboard_department'];
			$query = "SELECT a.student_id,b.disabilities from academic_record a join medical_record b on a.student_id = b.student_id right join programme e on e.id = a.programme_id right join department f on f.id = e.department_id where f.id = '$department' and disabilities is not null and disabilities <> '' and disabilities <> 'No' ";
		} else {
			$query = "SELECT a.student_id,b.disabilities from academic_record a join medical_record b on a.student_id = b.student_id where disabilities is not null and disabilities <> '' and disabilities <> 'No' ";
		}

		$result = $this->db->query($query);
		$results = $result->result_array();
		$payload = [];

		if (!empty($results)) {
			$payload = AdminModelTrait::processPWDStats($results);
		}

		return $payload;
	}

	public function transactionCustomStatus(string $type)
	{
		if ($type == 'paid') {
			$query = "SELECT sum(a.amount_paid) as total from transaction_custom a where a.payment_status in ('00', '01')";
		} else {
			$query = "SELECT sum(a.total_amount) as total from transaction_custom a where a.payment_status not in ('00', '01')";
		}
		$query = $this->db->query($query);
		if ($query->num_rows() <= 0) {
			return 0;
		}
		return $query->result_array()[0]['total'];
	}

	public function transactionCustomDistrix(string $type)
	{
		if ($type === 'paid') {
			$query = "SELECT date_format(c.date_completed, '%b-%Y') as label, ANY_VALUE(UNIX_TIMESTAMP(c.date_completed)) as ord,sum(c.amount_paid) as total from transaction_custom c where c.payment_status in ('01','00') and timestampdiff(month, c.date_completed, now()) < 12 group by year(c.date_completed), month(c.date_completed), label ORDER BY ord ASC";
		} else {
			$query = "SELECT date_format(c.date_completed, '%b-%Y') as label, ANY_VALUE(UNIX_TIMESTAMP(c.date_completed)) as ord,sum(c.total_amount) as total from transaction_custom c where c.payment_status not in ('01','00') and timestampdiff(month, c.date_completed, now()) < 12 group by year(c.date_completed), month(c.date_completed), label ORDER BY ord ASC";
		}
		$query = $this->db->query($query);
		if ($query->num_rows() <= 0) {
			return [];
		}
		return $query->result_array();
	}

	public function getFinanceOutflowPayment()
	{
		$session = $_GET['session'] ?? $this->currentTransactionSession();
		$query = "SELECT sum(a.total_amount) as total from transaction_request a where a.payment_status in ('00', '01') and
                	a.payment_status_description = ? and a.session = ?";
		$query = $this->db->query($query, [OutflowStatus::SUCCESSFUL, $session]);
		return $query->result_array()[0]['total'];
	}

	public function getDashboardLatestTransactionDebit($limit = 25): array
	{
		$query = "SELECT user_id, payment_description as descrip,transaction_ref,rrr_code,created_at as date_performed,payment_status_description,
       		a.total_amount, timestamp(a.transaction_date) as orderBy,date_paid as paid_date from transaction_request a left join users_new b
    		on b.id = a.user_id order by orderBy desc limit {$limit}
    	 ";
		$query = $this->db->query($query);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		$result = $query->result_array();
		loadClass($this->load, 'users_new');
		$payload = [];
		foreach ($result as $item) {
			$temp = $item;
			if ($item['user_id']) {
				$fullname = $this->users_new->getRequestUserInfo($item['user_id']);
				if ($fullname) {
					$fullname = $fullname[0];
					$temp['fullname'] = $fullname['firstname'] . ' ' . $fullname['lastname'];
					$temp['user_type'] = $fullname['user_type'];
				} else {
					$temp['fullname'] = '';
					$temp['user_type'] = '';
				}
			}

			if ($item['payment_status_description']) {
				$temp['payment_status'] = $item['payment_status_description'] === OutflowStatus::SUCCESSFUL ? '00' : '021';
			} else {
				$temp['payment_status'] = '021';
			}
			unset($temp['payment_status_description']);

			$payload[] = $temp;
		}

		return $payload;
	}

	public function courseGenderDistribution($session, $course)
	{
		$query = "SELECT distinct CASE
					WHEN lower(gender) = 'male' THEN 'Male'
					WHEN lower(gender) = 'female' THEN 'Female'
					ELSE 'Null'
					END as gender_name,
					count(*) as total  from course_enrollment a join students b on b.id = a.student_id where a.session_id = ?
					and a.course_id = ? group by gender_name order by gender_name";
		$query = $this->db->query($query, [$session, $course]);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array();
	}

	public function loadCourseAgeDistribution($session, $course): array
	{
		$query = "SELECT distinct count(*) as total, CASE
        WHEN dob IS NOT NULL AND dob <> '' THEN
            TIMESTAMPDIFF(YEAR, dob, CURDATE())
        ELSE
            NULL
    	END AS age from course_enrollment a join students b on b.id = a.student_id where a.session_id = ? and a.course_id = ?
    	group by age order by total";

		$query = $this->db->query($query, [$session, $course]);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		$result = $query->result_array();
		$result = AdminModelTrait::groupDataByAge($result, 'Null');
		return AdminModelTrait::formatRangeToStandard($result);
	}

	public function loadCourseAgeDistrixStats($session, $course)
	{
		$query = "SELECT sum(age) as sum_age, count(age) as total, min(age) as min_age, max(age) as max_age from
                    (
                        SELECT CASE
						WHEN dob IS NOT NULL AND dob <> '' AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) > 0 THEN
							TIMESTAMPDIFF(YEAR, dob, CURDATE())
						ELSE
							NULL
						END AS age from course_enrollment a join students b on b.id=a.student_id where a.course_id = ?
						and a.session_id = ?
                    ) as x ";
		$result = $this->db->query($query, [$course, $session]);
		$result = $result->result_array();
		return @$result[0];
	}

	public function loadCoursePWD($session, $course): array
	{
		$query = "SELECT a.student_id,c.disabilities from course_enrollment a join students b on b.id=a.student_id join
			medical_record c on b.id = c.student_id where a.course_id = ? and a.session_id = ? and disabilities is not null
			and disabilities <> '' and disabilities <> 'No' ";

		$result = $this->db->query($query, [$course, $session]);
		$results = $result->result_array();
		$payload = [];

		if (!empty($results)) {
			$payload = AdminModelTrait::processPWDStats($results);
		}

		return $payload;
	}

	public function loadScoreAnalysis($session, $course)
	{
		$query = "select count(total_score) as scored,count(if(total_score=null,1,null)) as unscored,min(total_score) as min_score,
       		max(total_score) as max_score, avg(total_score) as avg_score from course_enrollment a join academic_record b on
       		b.student_id=a.student_id where a.course_id = ? and a.session_id = ?";
		$result = $this->db->query($query, [$course, $session]);
		$result = $result->result_array();
		return $result[0];
	}

	public function loadScoreDistribution($session, $course): array
	{
		$query = "select total_score as label,count(total_score) as total from course_enrollment a join academic_record b on
	   		b.student_id=a.student_id where a.course_id = ? and a.session_id = ? group by total_score order by total_score";
		$result = $this->db->query($query, [$course, $session]);
		$result = $result->result_array();
		$result = AdminModelTrait::groupDataByScore($result, 'Null');
		return AdminModelTrait::formatRangeToStandard($result);
	}

	private function loadAvgCourseScoreBySession($session, $course)
	{
		$query = "SELECT avg(total_score) as total from course_enrollment a join academic_record b on
	   		b.student_id = a.student_id where a.course_id = ? and a.session_id = ?";
		$result = $this->db->query($query, [$course, $session]);
		return $result->result_array()[0]['total'];
	}

	public function loadAvgScoreTrend($session, $course): array
	{
		loadClass($this->load, 'sessions');
		$session = $this->sessions->getSessionById($session);
		if (!$session) {
			return [];
		}
		$session = $session[0]['date'];
		$query = "SELECT a.session_id, b.date as label from course_enrollment a join sessions b on b.id = a.session_id
                    where a.course_id = ? group by label, a.session_id having label <= '$session' order by label asc limit 5";
		$result = $this->db->query($query, [$course]);
		$result = $result->result_array();
		$payload = [];
		if (!empty($result)) {
			foreach ($result as $session) {
				$total = $this->loadAvgCourseScoreBySession($session['session_id'], $course);
				$payload[] = [
					'label' => $session['label'],
					'total' => $total,
				];
			}
		}

		return $payload;
	}

	private function loadCourseEnrollmentBySession($session, $course)
	{
		$query = "select count(*) as total from course_enrollment a join academic_record b on
			b.student_id = a.student_id where a.session_id = ? and a.course_id = ?";
		$result = $this->db->query($query, [$session, $course]);
		$result = $result->result_array();
		return $result[0]['total'];
	}

	public function loadCourseEnrollmentScoreTrend($session, $course): array
	{
		loadClass($this->load, 'sessions');
		$session = $this->sessions->getSessionById($session);
		$activeSession = get_setting('active_session_student_portal');
		$session2 = $this->sessions->getSessionById($activeSession);
		if (!$session) {
			return [];
		}
		$session = $session[0]['date'];
		$session2 = $session2[0]['date'];
		$query = "SELECT a.session_id, b.date as label from course_enrollment a join sessions b on b.id = a.session_id
                    where a.course_id = ? group by label, a.session_id having label >= '$session' and label <= '$session2'
                    order by label asc limit 5";
		$result = $this->db->query($query, [$course]);
		$result = $result->result_array();
		$payload = [];
		if (!empty($result)) {
			foreach ($result as $session) {
				$total = $this->loadCourseEnrollmentBySession($session['session_id'], $course);
				$payload[] = [
					'label' => $session['label'],
					'total' => $total,
				];
			}
		}

		return $payload;
	}

	public function loadStudentGenderData($session): array
	{

		$query = "SELECT distinct CASE
					WHEN lower(gender) = 'male' THEN 'Male'
					WHEN lower(gender) = 'female' THEN 'Female'
					ELSE 'Null'
					END as gender_name,
					count(distinct b.student_id) as total from transaction b join
				students a on b.student_id = a.id where
				b.payment_status in ('00','01') and b.session = ? and b.payment_id = '1' group by gender_name order by gender_name";
		$query = $this->db->query($query, [$session]);

		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array();
	}

	public function getOrientationData(): array
	{
		$admissionSession = $_GET['session'] ?? get_setting('active_admission_session');
		$query = "SELECT lower(orientation_attendance) as name, count(*) as total FROM students a join academic_record b on 
		 b.student_id = a.id where orientation_attendance <> '' and b.session_of_admission = ? group by orientation_attendance";
		$query = $this->db->query($query, [$admissionSession]);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		$temp = $query->result_array();
		$result = [];
		foreach ($temp as $item) {
			$temp = str_replace('-', '_', $item['name']);
			$result[$temp] = $item['total'];
		}
		return $result;
	}

}
