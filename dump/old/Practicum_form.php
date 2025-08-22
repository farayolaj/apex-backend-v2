<?php

require_once 'application/models/Crud.php';

/**
 * This class is automatically generated based on the structure of the table.
 * And it represent the model of the practicum_form table
 */
class Practicum_form extends Crud
{

	/**
	 * This is the entity name equivalent to the table name
	 * @var string
	 */
	protected static $tablename = "Practicum_form";

	/**
	 * This array contains the field that can be null
	 * @var array
	 */
	public static $nullArray = [];

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
	public static $typeArray = ['student_id' => 'int', 'session' => 'int', 'sch_contact_addr' => 'text', 'school_location_desc' => 'text', 'school_city' => 'varchar', 'school_lga' => 'varchar', 'school_state' => 'varchar', 'school_name' => 'varchar', 'student_phone' => 'varchar', 'created_at' => 'timestamp'];

	/**
	 * This is a dictionary that map a field name with the label name that
	 * will be shown in a form
	 * @var array
	 */
	public static $labelArray = ['id' => '', 'student_id' => '', 'session' => '', 'sch_contact_addr' => '', 'school_location_desc' => '', 'school_city' => '', 'school_lga' => '', 'school_state' => '', 'school_name' => '', 'student_phone' => '', 'created_at' => ''];

	/**
	 * Associative array of fields in the table that have default value
	 * @var array
	 */
	public static $defaultArray = ['created_at' => 'current_timestamp()'];

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
	];

	/**
	 * This are the action allowed to be performed on the entity and this can
	 * be changed in the formConfig model file for flexibility
	 * @var array
	 */
	public static $tableAction = ['delete' => 'delete/practicum_form', 'edit' => 'edit/practicum_form'];

	public static $apiSelectClause = ['id', 'student_id', 'sch_contact_addr', 'school_location_desc', 'school_city', 'school_lga',
		'school_state', 'school_name', 'student_phone', 'created_at'];

	public function __construct(array $array = [])
	{
		parent::__construct($array);
	}

	public function getStudent_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'student','display'=>'student_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'student_name' as value from 'student' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('student', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

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

	public function getSessionFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='session'>Session</label>
		<input type='text' name='session' id='session' value='$value' class='form-control' required />
	</div>";
	}

	public function getSch_contact_addrFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='sch_contact_addr'>Sch Contact Addr</label>
		<input type='text' name='sch_contact_addr' id='sch_contact_addr' value='$value' class='form-control' required />
	</div>";
	}

	public function getSchool_location_descFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='school_location_desc'>School Location Desc</label>
		<input type='text' name='school_location_desc' id='school_location_desc' value='$value' class='form-control' required />
	</div>";
	}

	public function getSchool_cityFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='school_city'>School City</label>
		<input type='text' name='school_city' id='school_city' value='$value' class='form-control' required />
	</div>";
	}

	public function getSchool_lgaFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='school_lga'>School Lga</label>
		<input type='text' name='school_lga' id='school_lga' value='$value' class='form-control' required />
	</div>";
	}

	public function getSchool_stateFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='school_state'>School State</label>
		<input type='text' name='school_state' id='school_state' value='$value' class='form-control' required />
	</div>";
	}

	public function getSchool_nameFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='school_name'>School Name</label>
		<input type='text' name='school_name' id='school_name' value='$value' class='form-control' required />
	</div>";
	}

	public function getStudent_phoneFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='student_phone'>Student Phone</label>
		<input type='text' name='student_phone' id='student_phone' value='$value' class='form-control' required />
	</div>";
	}

	public function getCreated_atFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='created_at'>Created At</label>
		<input type='text' name='created_at' id='created_at' value='$value' class='form-control' required />
	</div>";
	}

	protected function getStudent()
	{
		$query = 'SELECT * FROM students WHERE id=?';
		if (!isset($this->array['id'])) {
			return null;
		}
		$id = $this->array['id'];
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		include_once('Students.php');
		$resultObject = new Students($result[0]);
		return $resultObject;
	}

	public function APIList($filterList, $queryString, $start, $len, $orderBy = null, $exportApi = null)
	{
		$tempCode = null;
		if (isset($filterList['b.code']) && $filterList['b.code']) {
			$tempCode = $filterList['b.code'];
			unset($filterList['b.code']);
		}

		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		if ($tempCode) {
			$code = $tempCode == 'suspension' ? 'SuS' : 'RoS';
			$filterQuery .= ($filterQuery ? " and " : " where ") . " b.code='$code'";
		}

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy";
		} else {
			if ($exportApi && $orderBy) {
				$filterQuery .= " order by $orderBy";
			} else {
				$filterQuery .= " order by a.created_at desc ";
			}
		}

		if ($len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$filterQuery .= " limit $start, $len";
		}

		if (!$filterValues) {
			$filterValues = [];
		}

		if ($exportApi) {
			$tablename = strtolower(self::$tablename);
			$query = "SELECT " . buildApiClause(static::$apiSelectClause, 'a') . ", concat(firstname, ' ',lastname,' ',othernames) as fullname,
			d.matric_number,c.date as session,b.user_login as email,d.current_level as student_level from $tablename a join students b on b.id = a.student_id join sessions c on c.id = a.session 
			join academic_record d on d.student_id = b.id $filterQuery";
		} else {
			$tablename = strtolower(self::$tablename);
			$query = "SELECT " . buildApiClause(static::$apiSelectClause, 'a') . ", concat(firstname, ' ',lastname) as fullname,
			d.matric_number,c.date as session from $tablename a join students b on b.id = a.student_id join sessions c on c.id = a.session 
			join academic_record d on d.student_id = b.id $filterQuery";
		}

		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->result_array();
		$res2 = $this->db->query($query2);
		$res2 = $res2->result_array();

		return [$res, $res2];
	}

	public function practicumEligibility($student_id)
	{
		$currentSession = get_setting('active_session_student_portal');
		$code = 'SOW310';
		$code1 = 'SOW401';
		$query = "SELECT a.id,b.code from course_enrollment a join courses b on b.id = a.course_id 
         where (b.code = ? or b.code = ?) and session_id = ? and a.student_id = ?";
		$result = $this->db->query($query, [$code, $code1, $currentSession, $student_id]);
		return $result->num_rows() > 0 ? $result->row() : false;
	}


}

