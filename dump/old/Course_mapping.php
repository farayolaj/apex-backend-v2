<?php
require_once 'application/models/Crud.php';

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the course_mapping table.
 */
class Course_mapping extends Crud
{
	protected static $tablename = 'Course_mapping';
	/* this array contains the field that can be null*/
	static $nullArray = array();
	static $compositePrimaryKey = array();
	static $uploadDependency = array();
	/*this array contains the fields that are unique*/
	static $uniqueArray = array();
	/*this is an associative array containing the fieldname and the type of the field*/
	static $typeArray = array('course_id' => 'int', 'programme_id' => 'int', 'semester' => 'int', 'course_unit' => 'int',
		'course_status' => 'varchar', 'level' => 'text', 'mode_of_entry' => 'text', 'pass_score' => 'int',
		'pre_select' => 'tinyint');
	/*this is a dictionary that map a field name with the label name that will be shown in a form*/
	static $labelArray = array('id' => '', 'course_id' => '', 'programme_id' => '', 'semester' => '', 'course_unit' => '',
		'course_status' => '', 'level' => '', 'mode_of_entry' => '', 'pass_score' => '', 'pre_select' => '');
	/*associative array of fields that have default value*/
	static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
	static $documentField = array(); //array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

	static $relation = array('course' => array('course_id', 'ID')
	, 'programme' => array('programme_id', 'ID'),
	);
	static $tableAction = array('delete' => 'delete/course_mapping', 'edit' => 'edit/course_mapping');

	function __construct($array = array())
	{
		parent::__construct($array);
	}

	function getCourse_idFormField($value = '')
	{
		$fk = null; //change the value of this variable to array('table'=>'course','display'=>'course_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

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

	function getProgramme_idFormField($value = '')
	{
		$fk = null; //change the value of this variable to array('table'=>'programme','display'=>'programme_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

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

	function getLevelFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='level' >Level</label>
<textarea id='level' name='level' class='form-control' required>$value</textarea>
</div> ";

	}

	function getMode_of_entryFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='mode_of_entry' >Mode Of Entry</label>
<textarea id='mode_of_entry' name='mode_of_entry' class='form-control' required>$value</textarea>
</div> ";

	}

	function getPass_scoreFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='pass_score' >Pass Score</label><input type='number' name='pass_score' id='pass_score' value='$value' class='form-control' required />
</div> ";

	}

	function getPre_selectFormField($value = '')
	{

		return "<div class='form-group'>
	<label class='form-checkbox'>Pre Select</label>
	<select class='form-control' id='pre_select' name='pre_select' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

	}

	protected function getCourse()
	{
		$query = 'SELECT * FROM course WHERE id=?';
		if (!isset($this->array['ID'])) {
			return null;
		}
		$id = $this->array['ID'];
		$result = $this->db->query($query, array($id));
		$result = $result->result_array();
		if (empty($result)) {
			return false;
		}
		include_once 'Course.php';
		$resultObject = new Course($result[0]);
		return $resultObject;
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
		include_once 'Programme.php';
		$resultObject = new Programme($result[0]);
		return $resultObject;
	}

	/**
	 * This load courses based on programme and semester in which the
	 * result is filtered by student level according to mapped level
	 * @param $programme_id
	 * @param $level
	 * @param $entryMode
	 * @param string $semester [description]
	 * @return array [type] [description]
	 */
	public function getCourseLists($programme_id, $level, $entryMode, $semester): array
	{
		$query = "SELECT courses.id as main_course_id, courses.code, courses.title, courses.description, courses.course_guide_url, courses.active,
        course_mapping.id as course_mapping_id, course_mapping.programme_id, course_mapping.semester, course_mapping.level, course_mapping.course_unit, 
        course_mapping.course_status,course_manager.course_lecturer_id, staffs.firstname, staffs.othernames, staffs.lastname, course_mapping.pre_select 
        from courses left join course_mapping on course_mapping.course_id = courses.id  left join course_manager on course_manager.course_id = courses.id 
        left join users_new on users_new.id = course_manager.course_lecturer_id left join staffs on staffs.id=users_new.user_table_id 
        where course_mapping.programme_id = ? and course_mapping.semester = ? and 
        course_mapping.mode_of_entry = ? and courses.active = '1' order by courses.code asc ";
		$courses = $this->query($query, [$programme_id, $semester, $entryMode]);
		$result = [];
		if (!$courses) {
			return $result;
		}

		foreach ($courses as $courseData) {
			$mappedLevel = json_decode($courseData['level'], true);
			if (is_array($mappedLevel) && in_array($level, $mappedLevel)) {
				unset($courseData['level']);
				$courseData['semester'] = (int)$courseData['semester'];
				$courseData['course_unit'] = (int)$courseData['course_unit'];
				$courseData['pre_select'] = (int)$courseData['pre_select'];
				$result[] = $courseData;
			}
		}
		return $result;
	}

	public function searchCourseLists($course, $level, $semester, $programme = null): array
	{
		$query = "SELECT courses.id as main_course_id, courses.code, courses.title, courses.description, courses.course_guide_url, 
       courses.active,course_mapping.id as course_mapping_id, course_mapping.programme_id, course_mapping.semester, course_mapping.level, 
       course_mapping.course_unit, course_mapping.course_status,course_manager.course_lecturer_id, staffs.firstname, staffs.othernames, 
       staffs.lastname, course_mapping.pre_select from courses left join course_mapping on course_mapping.course_id = courses.id 
       left join course_manager on course_manager.course_id = courses.id left join users_new on users_new.id = course_manager.course_lecturer_id 
       left join staffs on staffs.id=users_new.user_table_id where 
    	(courses.code like '%$course%' or courses.title like '%$course%') and 
    		course_mapping.semester = ? and courses.active = '1' ";
		if ($programme) {
			$query .= " and course_mapping.programme_id = '$programme'";
		}
		$query .= " order by courses.code asc";
		$courses = $this->query($query, [$semester]);
		$result = [];
		if (!$courses) {
			return $result;
		}

		foreach ($courses as $courseData) {
			$mappedLevel = json_decode($courseData['level'], true);
			foreach ($mappedLevel as $key => $levelMapped) {
				if ($levelMapped <= $level) {
					unset($courseData['level']);
					$courseData['semester'] = (int)$courseData['semester'];
					$courseData['course_unit'] = (int)$courseData['course_unit'];
					$courseData['pre_select'] = (int)$courseData['pre_select'];
					$result[] = $courseData;
				}
			}

		}
		return uniqueMultidimensionalArray($result, 'main_course_id');
	}

	public function delete($id = null, &$dbObject = null, $type = null): bool
	{
		permissionAccess($this, 'course_delete', 'delete');
		$currentUser = $this->webSessionManager->currentAPIUser();
		$db = $dbObject ?? $this->db;
		if (parent::delete($id, $db)) {
			logAction($this, 'course_mapping_delete', $currentUser->user_login);
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
			$filterQuery .= " order by b.code asc, name asc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}

		$query = "SELECT SQL_CALC_FOUND_ROWS a.*, c.name, b.code as course_code from course_mapping a 
                join courses b on b.id = a.course_id join programme c on c.id = a.programme_id $filterQuery";
		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->result_array();
		$res2 = $this->db->query($query2);
		$res2 = $res2->result_array();

		return [$this->processList($res), $res2];
	}

	private function processList(array $items): array
	{
		for ($i = 0; $i < count($items); $i++) {
			$items[$i] = $this->loadExtras($items[$i]);
		}
		return $items;
	}

	public function loadExtras(array $item): array
	{
		if ($item['level']) {
			$levels = json_decode($item['level'], true);
			if ($levels) {
				$item['level'] = $levels;
			} else {
				$item['level'] = '';
			}
		} else {
			$item['level'] = '';
		}

		return $item;
	}

}
