<?php
namespace App\Entities;

use App\Models\Crud;
use App\Libraries\EntityLoader;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the course_enrollment table.
 */
class Course_enrollment extends Crud
{
	protected static $tablename = 'Course_enrollment';
	/* this array contains the field that can be null*/
	static $nullArray = array('ca_score', 'exam_score', 'total_score');
	static $compositePrimaryKey = array();
	static $uploadDependency = array();
	/*this array contains the fields that are unique*/
	static $uniqueArray = array();
	/*this is an associative array containing the fieldname and the type of the field*/
	static $typeArray = array('student_id' => 'int', 'course_id' => 'int', 'course_unit' => 'int', 'course_status' => 'varchar', 'semester' => 'int', 'session_id' => 'int', 'student_level' => 'int', 'ca_score' => 'int', 'exam_score' => 'int', 'total_score' => 'int', 'is_approved' => 'tinyint', 'date_last_update' => 'datetime', 'date_created' => 'datetime');
	/*this is a dictionary that map a field name with the label name that will be shown in a form*/
	static $labelArray = array('id' => '', 'student_id' => '', 'course_id' => '', 'course_unit' => '', 'course_status' => '', 'semester' => '', 'session_id' => '', 'student_level' => '', 'ca_score' => '', 'exam_score' => '', 'total_score' => '', 'is_approved' => '', 'date_last_update' => '', 'date_created' => '');
	/*associative array of fields that have default value*/
	static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
	static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

	static $relation = array('course' => array('course_id', 'ID')
	);
	static $tableAction = array('delete' => 'delete/course_enrollment', 'edit' => 'edit/course_enrollment');

	function __construct($array = array())
	{
		parent::__construct($array);
	}

	function getStudent_idFormField($value = '')
	{
		$fk = null;//change the value of this variable to array('table'=>'student','display'=>'student_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

		if (is_null($fk)) {
			return $result = "<input type='hidden' value='$value' name='student_id' id='student_id' class='form-control' />
			";
		}
		if (is_array($fk)) {
			$result = "<div class='form-group'>
		<label for='student_id'>Student Id</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='student_id' id='student_id' class='form-control'>
			$option
		</select>";
		}
		$result .= "</div>";
		return $result;

	}

	function getCourse_idFormField($value = '')
	{
		$fk = null;//change the value of this variable to array('table'=>'course','display'=>'course_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

		if (is_null($fk)) {
			return $result = "<input type='hidden' value='$value' name='course_id' id='course_id' class='form-control' />
			";
		}
		if (is_array($fk)) {
			$result = "<div class='form-group'>
		<label for='course_id'>Course Id</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='course_id' id='course_id' class='form-control'>
			$option
		</select>";
		}
		$result .= "</div>";
		return $result;

	}

	function getCourse_unitFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='course_unit' >Course Unit</label><input type='number' name='course_unit' id='course_unit' value='$value' class='form-control' required />
</div> ";

	}

	function getCourse_statusFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='course_status' >Course Status</label>
		<input type='text' name='course_status' id='course_status' value='$value' class='form-control' required />
</div> ";

	}

	function getSemesterFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='semester' >Semester</label><input type='number' name='semester' id='semester' value='$value' class='form-control' required />
</div> ";

	}

	function getSession_idFormField($value = '')
	{
		$fk = null;//change the value of this variable to array('table'=>'session','display'=>'session_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

		if (is_null($fk)) {
			return $result = "<input type='hidden' value='$value' name='session_id' id='session_id' class='form-control' />
			";
		}
		if (is_array($fk)) {
			$result = "<div class='form-group'>
		<label for='session_id'>Session Id</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='session_id' id='session_id' class='form-control'>
			$option
		</select>";
		}
		$result .= "</div>";
		return $result;

	}

	function getStudent_levelFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='student_level' >Student Level</label><input type='number' name='student_level' id='student_level' value='$value' class='form-control' required />
</div> ";

	}

	function getCa_scoreFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='ca_score' >Ca Score</label><input type='number' name='ca_score' id='ca_score' value='$value' class='form-control'  />
</div> ";

	}

	function getExam_scoreFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='exam_score' >Exam Score</label><input type='number' name='exam_score' id='exam_score' value='$value' class='form-control'  />
</div> ";

	}

	function getTotal_scoreFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='total_score' >Total Score</label><input type='number' name='total_score' id='total_score' value='$value' class='form-control'  />
</div> ";

	}

	function getIs_approvedFormField($value = '')
	{

		return "<div class='form-group'>
	<label class='form-checkbox'>Is Approved</label>
	<select class='form-control' id='is_approved' name='is_approved' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

	}

	function getDate_last_updateFormField($value = '')
	{

		return " ";

	}

	function getDate_createdFormField($value = '')
	{

		return " ";

	}

	protected function getCourse()
	{
		$query = 'SELECT * FROM course WHERE id=?';
		if (!isset($this->array['id'])) {
			return null;
		}
		$id = $this->array['id'];
		$result = $this->db->query($query, array($id));
		$result = $result->getResultArray();
		if (empty($result)) {
			return false;
		}
		return new \App\Entities\Course($result[0]);
	}

	public function deleteCourse($student, $course, $session, $studentLevel, $semester = null)
	{
		$query = "DELETE from course_enrollment where student_id = ? and course_id = ? and session_id = ? and student_level = ?";
		$data = [$student, $course, $session, $studentLevel];
		$sessionSemesterStart = get_setting('session_semester_payment_start');
		// to validate for student prior when fee was not based on semester
		if ($session < $sessionSemesterStart) {
			$semester = null;
		}
		if ($semester) {
			$query .= " and semester = ?";
			$data[] = $semester;
		}
		$result = $this->query($query, $data);
		if (!$result) {
			return false;
		}
		return true;
	}

	public function getAllCourses($session)
	{
		$query = "SELECT * FROM course_enrollment where session_id = ?";
		$result = $this->query($query, [$session]);
		return $result;
	}

	public function getStudentEnrollmentRecord($programme, $session, $level, $semester)
	{
		$query = 'SELECT a.student_id as id, a.student_id, c.matric_number, CONCAT(UPPER(b.lastname), ", ", b.firstname, " ", b.othernames) AS fullname,
            GROUP_CONCAT( a.course_id SEPARATOR ",") AS course_ids,
            GROUP_CONCAT( CONCAT(d.code, " ", a.course_unit ) SEPARATOR ",") AS course_codes
            FROM course_enrollment a JOIN students b ON b.id=a.student_id JOIN academic_record c ON c.student_id=a.student_id
            JOIN courses d ON d.id = a.course_id WHERE a.session_id = ' . $session . ' AND a.student_level = ' . $level . '
            AND a.semester = ' . $semester . ' AND c.programme_id = ' . $programme . ' 
            GROUP BY a.student_id, c.matric_number ORDER BY c.matric_number ASC';
		return $this->query($query);
	}

	public function getScoreGrade($student, $course, $session, $level, $semester, $gradeSession)
	{
		EntityLoader::loadClass($this, 'courses');
		EntityLoader::loadClass($this, 'grades');
		$course = $this->courses->getCourseIdByCode($course);
		$query = "SELECT a.total_score from course_enrollment a where a.student_id = ? and a.course_id = ? and a.session_id = ? 
        	and a.student_level = ? and a.semester = ?";
		$result = $this->query($query, [$student, $course, $session, $level, $semester]);
		if ($result) {
			$score = $result[0]['total_score'];
			$grade = $this->grades->getGrade($score, $gradeSession);
			return ($grade != '') ? $score . "-" . $grade : $score;
		}
	}

	public function getStudentCourseEnrollments($course, $session, $semesterCode = null)
	{
		$optionalSemester = '';
		$param = array($course, $session);
		if ($semesterCode) {
			$optionalSemester = ' and a.semester=?';
			$param[] = $semesterCode;
		}
		$query = "select a.*, c.student_id as id,c.matric_number,c.year_of_entry from course_enrollment a join courses b on b.id=a.course_id 
		join academic_record c on c.student_id=a.student_id where a.course_id=?  and  a.session_id=? $optionalSemester order by matric_number";
		return $this->query($query, $param);
	}

}
