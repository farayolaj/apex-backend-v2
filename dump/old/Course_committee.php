<?php
require_once('application/models/Crud.php');

/**
 * This class is automatically generated based on the structure of the table.
 * And it represent the model of the course_committee table
 */
class Course_committee extends Crud
{

	/**
	 * This is the entity name equivalent to the table name
	 * @var string
	 */
	protected static $tablename = "Course_committee";

	/**
	 * This array contains the field that can be null
	 * @var array
	 */
	public static $nullArray = ['department_id', 'user_id', 'session_id', 'active', 'updated_at'];

	/**
	 * This are fields that must be unique across a row in a table.
	 * Similar to composite primary key in sql(oracle,mysql)
	 * @var array
	 */
	public static $compositePrimaryKey = [];

	/**
	 * This is to provided an array of fields that can be used for building a
	 * template header for batch upload using csv format
	 * @var array
	 */
	public static $uploadDependency = [];

	/**
	 * If there is a relationship between this table and another table, this display field properties is used as a column in the query.
	 * A field in the other table that displays the connection between this name and this table's name,something along these lines
	 * table_id. We cannot use a name similar to table id in the table that is displayed to the user, so the display field is used in
	 * place of it. To ensure that the other model queries use that field name as a column to be fetched with the query rather than the
	 * table id alone, the display field name provided must be a column in the table to replace the table id shown to the user.
	 * @var array|string
	 */
	public static $displayField = 'department_id';

	/**
	 * This array contains the fields that are unique
	 * @var array
	 */
	public static $uniqueArray = [];

	/**
	 * This is an associative array containing the fieldname and the datatype
	 * of the field
	 * @var array
	 */
	public static $typeArray = ['department_id' => 'int', 'user_id' => 'varchar', 'session_id' => 'int', 'active' => 'tinyint', 'created_at' => 'timestamp', 'updated_at' => 'timestamp'];

	/**
	 * This is a dictionary that map a field name with the label name that
	 * will be shown in a form
	 * @var array
	 */
	public static $labelArray = ['id' => '', 'department_id' => '', 'user_id' => '', 'session_id' => '', 'active' => '', 'created_at' => '', 'updated_at' => ''];

	/**
	 * Associative array of fields in the table that have default value
	 * @var array
	 */
	public static $defaultArray = ['active' => '1', 'created_at' => 'current_timestamp()'];

	/**
	 *  This is an array containing an associative array of field that should be regareded as document field.
	 * it will contain the setting for max size and data type. Example: populate this array with fields that
	 * are meant to be displayed as document in the format
	 * array('fieldname'=>array('type'=>array('jpeg','jpg','png','gif'),'size'=>'1048576','directory'=>'directoryName/','preserve'=>false,'max_width'=>'1000','max_height'=>'500')).
	 * the folder to save must represent a path from the basepath. it should be a relative path,preserve
	 * filename will be either true or false. when true,the file will be uploaded with it default filename
	 * else the system will pick the current user id in the session as the name of the file
	 * @var array
	 */
	public static $documentField = [];

	/**
	 * This is an associative array of fields showing relationship between
	 * entities
	 * @var array
	 */
	public static $relation = ['department' => array('department_id', 'id')
		, 'user' => array('user_id', 'id')
		, 'session' => array('session_id', 'id')
	];

	/**
	 * This are the action allowed to be performed on the entity and this can
	 * be changed in the formConfig model file for flexibility
	 * @var array
	 */
	public static $tableAction = ['delete' => 'delete/course_committee', 'edit' => 'edit/course_committee'];

	public function __construct(array $array = [])
	{
		parent::__construct($array);
	}

	public function getDepartment_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'department','display'=>'department_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'department_name' as value from 'department' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('department', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='department_id' id='department_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
		<label for='department_id'>Department</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='department_id' id='department_id' class='form-control'>
					$option
				</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getUser_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'user','display'=>'user_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'user_name' as value from 'user' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('user', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='user_id' id='user_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
		<label for='user_id'>User</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='user_id' id='user_id' class='form-control'>
					$option
				</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getSession_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'session','display'=>'session_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'session_name' as value from 'session' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('session', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='session_id' id='session_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
		<label for='session_id'>Session</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='session_id' id='session_id' class='form-control'>
					$option
				</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getActiveFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='active'>Active</label>
		<input type='text' name='active' id='active' value='$value' class='form-control' required />
	</div>";
	}

	public function getCreated_atFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='created_at'>Created At</label>
		<input type='text' name='created_at' id='created_at' value='$value' class='form-control' required />
	</div>";
	}

	public function getUpdated_atFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='updated_at'>Updated At</label>
		<input type='text' name='updated_at' id='updated_at' value='$value' class='form-control' required />
	</div>";
	}


	protected function getDepartment()
	{
		$query = 'SELECT * FROM department WHERE id=?';
		if (!isset($this->array['ID'])) {
			return null;
		}
		$id = $this->array['ID'];
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		include_once('Department.php');
		return new Department($result[0]);
	}

	protected function getUsers_new()
	{
		$query = 'SELECT * FROM user WHERE id=?';
		if (!isset($this->array['ID'])) {
			return null;
		}
		$id = $this->array['ID'];
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		include_once('Users_new.php');
		return new Users_new($result[0]);
	}

	protected function getSessions()
	{
		$query = 'SELECT * FROM sessions WHERE id=?';
		if (!isset($this->array['ID'])) {
			return null;
		}
		$id = $this->array['ID'];
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		include_once('Sessions.php');
		return new Sessions($result[0]);
	}

	public function APIList($filterList, $queryString, $start, $len, $orderBy): array
	{
		$departmentQuery = '';
		$departmentWhere = '';
		// the idea is to get the department id from the departmentalApiModel and use it to filter the course manager
		if (isset($filterList['api_department'])) {
			$departmentID = $filterList['api_department'];
			$departmentQuery = " join department d on d.id = a.department_id ";
			$departmentWhere .= " f.id = '$departmentID' ";
			unset($filterList['api_department']);
		}
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		$filterQuery .= ($filterQuery ? " and " : " where ") . " a.active = '1' ";

		if ($departmentWhere) {
			$filterQuery .= ($filterQuery ? " and " : " where ") . $departmentWhere;
		}

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by a.created_at desc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}
		$query = "SELECT SQL_CALC_FOUND_ROWS a.id,a.user_id,c.date as session,a.active as session from course_committee a 
		join sessions c on c.id = a.session_id {$departmentQuery} $filterQuery";

		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->result_array();
		$res2 = $this->db->query($query2);
		$res2 = $res2->result_array();
		$res = $this->processList($res);
		return [$res, $res2];
	}

	private function processList($items)
	{
		loadClass($this->load, 'Users_new');
		for ($i = 0; $i < count($items); $i++) {
			$items[$i] = $this->loadExtras($items[$i]);
		}
		return $items;
	}

	public function loadExtras($item)
	{
		if (isset($item['user_id'])) {
			$lecturers = json_decode($item['user_id'], true);
			$fullname = [];
			if (!empty($lecturers)) {
				foreach ($lecturers as $lecturer) {
					if(isset($this->users_new)){
						$lecturer = $this->users_new->getRealUserInfo($lecturer, 'staffs', 'staff');
						if ($lecturer) {
							$fullname[] = $lecturer['title'] . ' ' . $lecturer['lastname'] . ' ' . $lecturer['firstname'];
						}
					}
				}
			}
			$item['lecturer_name'] = $fullname;
		}
		$item['user_id'] = ($item['user_id'] != '') ? json_decode($item['user_id'], true) : [];

		return $item;
	}

	public function isCourseCommittee($userID, $session, $department){
		$query = "SELECT * from course_committee where session_id = ? and department_id = ? and JSON_SEARCH(user_id,'one',?) is not null";
		$result = $this->query($query, [$session, $department, $userID]);
		if (!$result) {
			return null;
		}
		return $result[0];
	}


}

