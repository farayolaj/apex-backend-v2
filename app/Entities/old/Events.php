<?php
require_once 'application/models/Crud.php';

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the events table.
 */
class Events extends Crud
{
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

	function __construct($array = array())
	{
		parent::__construct($array);
	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getNameFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='name' >Name</label>
		<input type='text' name='name' id='name' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getImageFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='image' >Image</label>
		<input type='text' name='image' id='image' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getStart_dateFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='start_date' >Start Date</label>
	<input type='date' name='start_date' id='start_date' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getEnd_dateFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='end_date' >End Date</label>
	<input type='date' name='end_date' id='end_date' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getTimeFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='time' >Time</label>
		<input type='text' name='time' id='time' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getColorFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='color' >Color</label><input type='number' name='color' id='color' value='$value' class='form-control'  />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getLocationFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='location' >Location</label>
		<input type='text' name='location' id='location' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getDescriptionFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='description' >Description</label>
<textarea id='description' name='description' class='form-control' required>$value</textarea>
</div> ";

	}

	/**
	 * @param mixed $student
	 * @return bool|<missing>
	 */
	public function getAllEvents($student = null)
	{
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
	public function getEventByCourseId($course)
	{
		$query = " SELECT a.id as main_event_id,a.session_id,a.event_date,a.event_category,b.* from events a 
     	join events_exams_meta b on b.events_id = a.id where courses_id = ? and status = '1'";
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
	public function getEventStudentByCourseId($course, $student, $session)
	{
		$query = " SELECT distinct b.code as course_code,a.* from events_exams_student a 
     	left join courses b on b.id = a.courses_id join course_enrollment c on c.course_id = b.id 
        where a.courses_id = ? and a.students_id = ? and a.session_id = ?";
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
	public function getGeneralEvent($session)
	{
		$query = " SELECT a.* from events a where session_id = ? and status = '1' and event_type = 'general' ";
		$result = $this->query($query, [$session]);
		if (!$result) {
			return false;
		}
		return $result;
	}

	public function getAllEventsBySession($session, $startDate, $endDate)
	{
		$query = "SELECT 
			    e.event_date,
			    (
			        SELECT JSON_ARRAYAGG(
			            JSON_OBJECT(
			                'event_meta_id', concat(grouped.courses_id,':',grouped.events_id),
			                'course_code', grouped.course_code,
			                'course_title', grouped.course_title,
			                'event_time', grouped.event_time,
			                'event_type', grouped.event_type,
			                'location', grouped.location,
			                'is_confirmed', CAST(grouped.is_confirmed AS CHAR)
			            )
			        )
			        FROM (
			            SELECT 
			                MIN(em.id) AS event_meta_id,
			                c.code AS course_code,
			                c.title AS course_title,
			                em.event_time,
			                e.event_type,
			                em.location,
			                MAX(IF(pe.course_id IS NOT NULL, 1, 0)) AS is_confirmed,
			                em.events_id, em.courses_id
			            FROM events_exams_meta em
			            INNER JOIN courses c ON c.id = em.courses_id
			            LEFT JOIN physical_interactive_event pe 
			                ON pe.course_id = em.courses_id 
			                AND pe.session_id = e.session_id
			                AND pe.events_exams_meta_id = em.id
			            WHERE em.events_id = e.id
			            GROUP BY 
			                c.code, c.title, em.event_time, e.event_type,em.location
			        ) AS grouped
			    ) AS courses
			FROM events e
			WHERE 
			    e.session_id = ? 
			    AND e.event_date BETWEEN ? AND ?
			    AND EXISTS (
			        SELECT 1 FROM events_exams_meta eem WHERE eem.events_id = e.id
			    )
			ORDER BY e.event_date";

		return $this->query($query, [$session, $startDate, $endDate]);
	}

	public function getEventDetailByID($id)
	{
		$explode = explode(':', $id);
		$courseID = $explode[0];
		$eventID = $explode[1];
		
		$query = "SELECT em.id, em.event_time, e.event_date, e.event_type,em.location as venue, pe.remark,
       		c.code as course_code, c.title as course_title, e.date_created as created_at, s.title, firstname, lastname,
			IF(pe.course_id IS NOT NULL, 1, 0) as is_confirmed, pe.category
			from events_exams_meta em 
			join courses c on c.id = em.courses_id
			join events e on e.id = em.events_id
			LEFT JOIN physical_interactive_event pe ON pe.course_id = em.courses_id 
			AND pe.session_id = e.session_id and pe.events_exams_meta_id = em.id
			left join users_new u on u.id = pe.user_id
			left join staffs s on s.id = u.user_table_id and u.user_type = 'staff'
			where em.courses_id = ? and em.events_id = ?";
		return $this->query($query, [$courseID, $eventID]);
	}

	public function getEventDetails($course, $event)
	{
		$query = "SELECT em.id, em.event_time, e.event_date, c.id as course_id,
			c.code as course_code, c.title as course_title
			from events_exams_meta em 
			join courses c on c.id = em.courses_id
			join events e on e.id = em.events_id
			where em.events_id = ? and em.courses_id = ?";
		return $this->query($query, [$event, $course]);
	}

}
