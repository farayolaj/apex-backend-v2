<?php
require_once('application/models/Crud.php');

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the course_configuration table.
 */
class Course_configuration extends Crud
{
	protected static $tablename = 'Course_configuration';
	/* this array contains the field that can be null*/
	static $nullArray = array();
	static $compositePrimaryKey = array();
	static $uploadDependency = array();
	/*this array contains the fields that are unique*/
	static $uniqueArray = array();
	/*this is an associative array containing the fieldname and the type of the field*/
	static $typeArray = array('programme_id' => 'int', 'semester' => 'int', 'level' => 'int', 'entry_mode' => 'varchar', 'min_unit' => 'int', 'max_unit' => 'int', 'enable_reg' => 'tinyint', 'date_created' => 'datetime');
	/*this is a dictionary that map a field name with the label name that will be shown in a form*/
	static $labelArray = array('id' => '', 'programme_id' => '', 'semester' => '', 'level' => '', 'entry_mode' => '', 'min_unit' => '', 'max_unit' => '', 'enable_reg' => '', 'date_created' => '');
	/*associative array of fields that have default value*/
	static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
	static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

	static $relation = array('programme' => array('programme_id', 'ID')
	);
	static $tableAction = array('delete' => 'delete/course_configuration', 'edit' => 'edit/course_configuration');

	function __construct($array = array())
	{
		parent::__construct($array);
	}

	function getProgramme_idFormField($value = '')
	{
		$fk = null;//change the value of this variable to array('table'=>'programme','display'=>'programme_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

		if (is_null($fk)) {
			return $result = "<input type='hidden' value='$value' name='programme_id' id='programme_id' class='form-control' />
			";
		}
		if (is_array($fk)) {
			$result = "<div class='form-group'>
		<label for='programme_id'>Programme Id</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='programme_id' id='programme_id' class='form-control'>
			$option
		</select>";
		}
		$result .= "</div>";
		return $result;

	}

	function getSemesterFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='semester' >Semester</label><input type='number' name='semester' id='semester' value='$value' class='form-control' required />
</div> ";

	}

	function getLevelFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='level' >Level</label><input type='number' name='level' id='level' value='$value' class='form-control' required />
</div> ";

	}

	function getEntry_modeFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='entry_mode' >Entry Mode</label>
		<input type='text' name='entry_mode' id='entry_mode' value='$value' class='form-control' required />
</div> ";

	}

	function getMin_unitFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='min_unit' >Min Unit</label><input type='number' name='min_unit' id='min_unit' value='$value' class='form-control' required />
</div> ";

	}

	function getMax_unitFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='max_unit' >Max Unit</label><input type='number' name='max_unit' id='max_unit' value='$value' class='form-control' required />
</div> ";

	}

	function getEnable_regFormField($value = '')
	{

		return "<div class='form-group'>
	<label class='form-checkbox'>Enable Reg</label>
	<select class='form-control' id='enable_reg' name='enable_reg' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

	}

	function getDate_createdFormField($value = '')
	{

		return " ";

	}

	protected function getSemesters()
	{
		$query = 'SELECT * FROM semesters WHERE id=?';
		if (!isset($this->array['semester'])) {
			return null;
		}
		$id = $this->array['semester'];
		$result = $this->db->query($query, array($id));
		$result = $result->result_array();
		if (empty($result)) {
			return false;
		}
		include_once('Semesters.php');
		return new Semesters($result[0]);
	}

	protected function getProgramme()
	{
		$query = 'SELECT * FROM programme WHERE id=?';
		if (!isset($this->array['ID'])) {
			return null;
		}
		$id = $this->array['ID'];
		$result = $this->db->query($query, array($id));
		$result = $result->result_array();
		if (empty($result)) {
			return false;
		}
		include_once('Programme.php');
		return new Programme($result[0]);
	}

	public function registrationIsOpened($programme, $level, $entry_mode, $semester = false)
	{
		$courseSemester = ($semester && $semester == 'first') ? 1 : 2;
		if ($semester && $courseSemester != get_setting('active_semester')) {
			return false;
		}
		$param = [
			'programme_id' => $programme,
			'level' => $level
		];
		if ($semester) {
			$param['semester'] = $courseSemester;
		}
		$item = $this->getWhere($param, $c, 0, null, false);
		if (!$item) {
			return true;
		}
		$item = $item[0];
		return $item->enable_reg;
	}

	// TODO: REMEMBER TO REMOVE ALWAYS TRUE VALUE RETURN
	// WHILE WE WAIT TO IMPLEMENT PROFILE UPDATE
	public function isPassportCheckValid($student)
	{
		return true;
		$passportPath = 'assets/images/students/passports/';
		$setting = trim(get_setting('force_course_reg_image_upload'));
		if (!$setting) {
			return true;
		}

		if (!trim($student->passport)) {
			return false;
		}
		$path = $passportPath . $student->passport;
		if (file_exists($path)) {
			return true;
		}
		return false;
	}

	public function delete($id = null, &$dbObject = null, $type = null): bool
	{
		permissionAccess($this, 'course_config_delete', 'delete');
		$currentUser = $this->webSessionManager->currentAPIUser();
		$db = $dbObject ?? $this->db;
		if (parent::delete($id, $db)) {
			logAction($this, 'course_config_delete', $currentUser->user_login);
			return true;
		}
		return false;
	}

	public function APIList($filterList, $queryString, $start, $len, $orderBy)
	{
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by b.name asc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}

		$query = "SELECT SQL_CALC_FOUND_ROWS a.*, b.name as programme_name from course_configuration a 
                join programme b on b.id = a.programme_id $filterQuery";
		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->result_array();
		$res2 = $this->db->query($query2);
		$res2 = $res2->result_array();
		return [$res, $res2];
	}


}
