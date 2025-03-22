<?php
require_once 'application/models/Crud.php';
/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the events table.
 */
class Events extends Crud {
	protected static $tablename = 'Events';
/* this array contains the field that can be null*/
	static $nullArray = array('color');
	static $compositePrimaryKey = array();
	static $uploadDependency = array();
/*this array contains the fields that are unique*/
	static $uniqueArray = array();
/*this is an associative array containing the fieldname and the type of the field*/
	static $typeArray = array('name' => 'varchar', 'image' => 'varchar', 'start_date' => 'date', 'end_date' => 'date', 'time' => 'varchar', 'color' => 'int', 'location' => 'varchar', 'description' => 'text');
/*this is a dictionary that map a field name with the label name that will be shown in a form*/
	static $labelArray = array('id' => '', 'name' => '', 'image' => '', 'start_date' => '', 'end_date' => '', 'time' => '', 'color' => '', 'location' => '', 'description' => '');
/*associative array of fields that have default value*/
	static $defaultArray = array('color' => '1');
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
	static $documentField = array(); //array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

	static $relation = array();
	static $tableAction = array('delete' => 'delete/events', 'edit' => 'edit/events');
	function __construct($array = array()) {
		parent::__construct($array);
	}
	/**
	 * @param mixed $value
	 * @return string
	 */
	function getNameFormField($value = '') {

		return "<div class='form-group'>
	<label for='name' >Name</label>
		<input type='text' name='name' id='name' value='$value' class='form-control' required />
</div> ";

	}
	/**
	 * @param mixed $value
	 * @return string
	 */
	function getImageFormField($value = '') {

		return "<div class='form-group'>
	<label for='image' >Image</label>
		<input type='text' name='image' id='image' value='$value' class='form-control' required />
</div> ";

	}
	/**
	 * @param mixed $value
	 * @return string
	 */
	function getStart_dateFormField($value = '') {

		return "<div class='form-group'>
	<label for='start_date' >Start Date</label>
	<input type='date' name='start_date' id='start_date' value='$value' class='form-control' required />
</div> ";

	}
	/**
	 * @param mixed $value
	 * @return string
	 */
	function getEnd_dateFormField($value = '') {

		return "<div class='form-group'>
	<label for='end_date' >End Date</label>
	<input type='date' name='end_date' id='end_date' value='$value' class='form-control' required />
</div> ";

	}
	/**
	 * @param mixed $value
	 * @return string
	 */
	function getTimeFormField($value = '') {

		return "<div class='form-group'>
	<label for='time' >Time</label>
		<input type='text' name='time' id='time' value='$value' class='form-control' required />
</div> ";

	}
	/**
	 * @param mixed $value
	 * @return string
	 */
	function getColorFormField($value = '') {

		return "<div class='form-group'>
	<label for='color' >Color</label><input type='number' name='color' id='color' value='$value' class='form-control'  />
</div> ";

	}
	/**
	 * @param mixed $value
	 * @return string
	 */
	function getLocationFormField($value = '') {

		return "<div class='form-group'>
	<label for='location' >Location</label>
		<input type='text' name='location' id='location' value='$value' class='form-control' required />
</div> ";

	}
	/**
	 * @param mixed $value
	 * @return string
	 */
	function getDescriptionFormField($value = '') {

		return "<div class='form-group'>
	<label for='description' >Description</label>
<textarea id='description' name='description' class='form-control' required>$value</textarea>
</div> ";

	}
	/**
	 * @param mixed $student
	 * @return bool|<missing>
	 */
	public function getAllEvents($student = null) {
		$query = " SELECT *  from events where cast(end_date as date) >= curdate()";
		$result = $this->query($query);
		if (!$result) {
			return false;
		}
		return $result;
	}
	/**
	 * @param mixed $course
	 * @return bool|<missing>
	 */
	public function getEventByCourseId($course) {
		$query = " SELECT a.id as main_event_id,a.session_id,a.event_date,a.event_category,b.* from events a join events_exams_meta b on b.events_id = a.id where courses_id = ? and status = '1'";
		$result = $this->query($query, [$course]);
		if (!$result) {
			return false;
		}
		return $result;
	}
	/**
	 * @param mixed $course
	 * @param mixed $student
	 * @param mixed $session
	 * @return bool|<missing>
	 */
	public function getEventStudentByCourseId($course, $student, $session) {
		$query = " SELECT distinct b.code as course_code,a.* from events_exams_student a left join courses b on b.id = a.courses_id join course_enrollment c on c.course_id = b.id where a.courses_id = ? and a.students_id = ? and a.session_id = ?";
		$result = $this->query($query, [$course, $student, $session]);
		if (!$result) {
			return false;
		}
		return $result;
	}
	/**
	 * @param mixed $session
	 * @return bool|<missing>
	 */
	public function getGeneralEvent($session) {
		$query = " SELECT a.* from events a where session_id = ? and status = '1' and event_type = 'general' ";
		$result = $this->query($query, [$session]);
		if (!$result) {
			return false;
		}
		return $result;
	}

}

?>