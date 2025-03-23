<?php

require_once('application/models/Crud.php');

/**
 * This class is automatically generated based on the structure of the table.
 * And it represent the model of the student_change_of_programme table
 */
class Student_change_of_programme extends Crud
{

	/**
	 * This is the entity name equivalent to the table name
	 * @var string
	 */
	protected static $tablename = "Student_change_of_programme";

	/**
	 * This array contains the field that can be null
	 * @var array
	 */
	public static $nullArray = ['date_created'];

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
	public static $displayField = '';// this display field properties is used as a column in a query if a their is a relationship between this table and another table.In the other table, a field showing the relationship between this name having the name of this table i.e something like this. table_id. We cant have the name like this in the table shown to the user like table_id so the display field is use to replace that table_id.However,the display field name provided must be a column in the table to replace the table_id shown to the user,so that when the other model queries,it will use that field name as a column to be fetched along the query rather than the table_id alone.;

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
	public static $typeArray = ['student_id' => 'int', 'old_programme_id' => 'int', 'new_programme_id' => 'int', 'old_level_id' => 'int', 'new_level_id' => 'int', 'old_entry_mode' => 'varchar', 'new_entry_mode' => 'varchar', 'date_created' => 'timestamp', 'session' => 'int', 'transaction_id' => 'int'];

	/**
	 * This is a dictionary that map a field name with the label name that
	 * will be shown in a form
	 * @var array
	 */
	public static $labelArray = ['ID' => '', 'student_id' => '', 'old_programme_id' => '', 'new_programme_id' => '', 'old_level_id' => '', 'new_level_id' => '', 'old_entry_mode' => '', 'new_entry_mode' => '', 'date_created' => '', 'session' => '', 'transaction_id' => ''];

	/**
	 * Associative array of fields in the table that have default value
	 * @var array
	 */
	public static $defaultArray = ['date_created' => 'current_timestamp()'];

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
	public static $relation = ['student' => array('student_id', 'id')
		, 'old_programme' => array('old_programme_id', 'id')
		, 'new_programme' => array('new_programme_id', 'id')
		, 'old_level' => array('old_level_id', 'id')
		, 'new_level' => array('new_level_id', 'id')
	];

	/**
	 * This are the action allowed to be performed on the entity and this can
	 * be changed in the formConfig model file for flexibility
	 * @var array
	 */
	public static $tableAction = ['delete' => 'delete/student_change_of_programme', 'edit' => 'edit/student_change_of_programme'];

	public function __construct(array $array = [])
	{
		parent::__construct($array);
	}

	public function getStudent_idFormField($value = '')
	{
		$fk = null;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='student_id' id='student_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
			<label for='student_id'>Student</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='student_id' id='student_id' class='form-control'>
						$option
					</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getOld_programme_idFormField($value = '')
	{
		$fk = null;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='old_programme_id' id='old_programme_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
			<label for='old_programme_id'>Old Programme</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='old_programme_id' id='old_programme_id' class='form-control'>
						$option
					</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getNew_programme_idFormField($value = '')
	{
		$fk = null;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='new_programme_id' id='new_programme_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
			<label for='new_programme_id'>New Programme</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='new_programme_id' id='new_programme_id' class='form-control'>
						$option
					</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getOld_level_idFormField($value = '')
	{
		$fk = null;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='old_level_id' id='old_level_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
			<label for='old_level_id'>Old Level</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='old_level_id' id='old_level_id' class='form-control'>
						$option
					</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getNew_level_idFormField($value = '')
	{
		$fk = null;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='new_level_id' id='new_level_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
			<label for='new_level_id'>New Level</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='new_level_id' id='new_level_id' class='form-control'>
						$option
					</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getOld_entry_modeFormField($value = '')
	{
		return "<div class='form-group'>
				<label for='old_entry_mode'>Old Entry Mode</label>
				<input type='text' name='old_entry_mode' id='old_entry_mode' value='$value' class='form-control' required />
			</div>";
	}

	public function getNew_entry_modeFormField($value = '')
	{
		return "<div class='form-group'>
				<label for='new_entry_mode'>New Entry Mode</label>
				<input type='text' name='new_entry_mode' id='new_entry_mode' value='$value' class='form-control' required />
			</div>";
	}

	public function getDate_createdFormField($value = '')
	{
		return "";
	}

	public function getSessionFormField($value = '')
	{
		return "<div class='form-group'>
				<label for='session'>Session</label>
				<input type='text' name='session' id='session' value='$value' class='form-control' required />
			</div>";
	}

	public function getTransaction_idFormField($value = '')
	{
		$fk = null;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='transaction_id' id='transaction_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
			<label for='transaction_id'>Transaction</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='transaction_id' id='transaction_id' class='form-control'>
						$option
					</select>";
			$result .= "</div>";
			return $result;
		}

	}

	protected function getStudent()
	{
		$query = 'SELECT * FROM student WHERE id=?';
		$id = $this->id;
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		include_once('Students.php');
		$resultObject = new Students($result[0]);
		return $resultObject;
	}

	public function APIList($filterList, $queryString, $start, $len)
	{
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		// if($filterQuery){
		// 	$filterQuery .= " and fee_description.code = ? ";
		// 	$filterValues[] = ['TP-SC'];
		// }else{
		// 	$filterQuery .= " where fee_description.code = ? ";
		// 	$filterValues[] = ['TP-SC'];
		// }

		$filterQuery .= " order by b.date_completed asc ";
		if (request()->getGet('start') && $len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}

		$query = $this->queryStudentChangeProgrogramme();
		$query .= $filterQuery;
		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->result_array();
		$res2 = $this->db->query($query2);
		$res2 = $res2->result_array();
		return [$res, $res2];
	}

	private function queryStudentChangeProgrogramme()
	{
		$query = "SELECT distinct SQL_CALC_FOUND_ROWS students.id as student_id, CONCAT(students.lastname, ' ', students.firstname, ' ', students.othernames) AS fullname,
        passport, sessions.date as session, department.name as department, faculty.name as faculty, p1.name as old_programme,
        p2.name as new_programme,old_entry_mode,new_entry_mode,l1.name as old_level,l2.name as new_level,a.new_programme_id,
        b.payment_description,a.programme_status,b.rrr_code from student_change_of_programme a left join transaction b on 
        b.id = a.transaction_id join students on students.id = a.student_id left join sessions on sessions.id = a.session left join 
        programme p1 on p1.id = a.old_programme_id left join programme p2 on p2.id = a.new_programme_id left join levels l1 
        on l1.id = a.old_level_id left join levels l2 on l2.id = a.new_level_id join department on department.id = p2.department_id 
        join faculty on faculty.id = p2.faculty_id ";
		return $query;
	}

	public function getStudentChangeProgrogramme($student, $session)
	{
		$query = $this->queryStudentChangeProgrogramme();
		$query .= " where a.student_id = ? and a.session = ?";
		$result = $this->query($query, [$student, $session]);
		if (!$result) {
			return null;
		}
		return $result[0];
	}


}
