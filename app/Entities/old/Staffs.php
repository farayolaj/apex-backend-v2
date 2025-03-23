<?php

require_once('application/models/Crud.php');

/**
 * This class is automatically generated based on the structure of the table.
 * And it represent the model of the staffs table
 */
class Staffs extends Crud
{

	/**
	 * This is the entity name equivalent to the table name
	 * @var string
	 */
	protected static $tablename = "Staffs";

	/**
	 * This array contains the field that can be null
	 * @var array
	 */
	public static $nullArray = ['gender', 'units_id', 'active', 'updated_at'];

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
	public static $displayField = 'gender';

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
	public static $typeArray = ['title' => 'varchar', 'staff_id' => 'varchar', 'firstname' => 'varchar', 'lastname' => 'varchar',
		'othernames' => 'varchar', 'gender' => 'enum', 'dob' => 'varchar', 'marital_status' => 'varchar', 'phone_number' => 'varchar',
		'email' => 'varchar', 'units_id' => 'int', 'user_rank' => 'varchar', 'role' => 'varchar', 'avatar' => 'varchar', 'address' => 'varchar',
		'active' => 'tinyint', 'created_at' => 'timestamp', 'updated_at' => 'timestamp', 'can_upload' => 'tinyint'];

	/**
	 * This is a dictionary that map a field name with the label name that
	 * will be shown in a form
	 * @var array
	 */
	public static $labelArray = ['id' => '', 'title' => '', 'staff_id' => '', 'firstname' => '', 'lastname' => '', 'othernames' => '',
		'gender' => '', 'dob' => '', 'marital_status' => '', 'phone_number' => '', 'email' => '', 'units_id' => '', 'user_rank' => '',
		'role' => '', 'avatar' => '', 'address' => '', 'active' => '', 'created_at' => '', 'updated_at' => '', 'can_upload' => ''];

	/**
	 * Associative array of fields in the table that have default value
	 * @var array
	 */
	public static $defaultArray = ['gender' => 'Others', 'marital_status' => 'Single', 'active' => '1', 'created_at' => 'current_timestamp()'];

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
	public static $relation = ['staff' => array('staff_id', 'id')
		, 'units' => array('units_id', 'id')
	];

	/**
	 * This are the action allowed to be performed on the entity and this can
	 * be changed in the formConfig model file for flexibility
	 * @var array
	 */
	public static $tableAction = ['delete' => 'delete/staffs', 'edit' => 'edit/staffs'];

	public static $apiSelectClause = ['id', 'title', 'lastname', 'firstname', 'othernames', 'gender', 'dob', 'marital_status', 'phone_number',
		'email', 'units_id', 'user_rank', 'role', 'avatar', 'address', 'active', 'created_at', 'updated_at'];

	public function __construct(array $array = [])
	{
		parent::__construct($array);
	}

	public function getTitleFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='title'>Title</label>
		<input type='text' name='title' id='title' value='$value' class='form-control' required />
	</div>";
	}

	public function getStaff_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'staff','display'=>'staff_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'staff_name' as value from 'staff' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('staff', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='staff_id' id='staff_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
		<label for='staff_id'>Staff</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='staff_id' id='staff_id' class='form-control'>
					$option
				</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getFirstnameFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='firstname'>Firstname</label>
		<input type='text' name='firstname' id='firstname' value='$value' class='form-control' required />
	</div>";
	}

	public function getLastnameFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='lastname'>Lastname</label>
		<input type='text' name='lastname' id='lastname' value='$value' class='form-control' required />
	</div>";
	}

	public function getOthernamesFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='othernames'>Othernames</label>
		<input type='text' name='othernames' id='othernames' value='$value' class='form-control' required />
	</div>";
	}

	public function getGenderFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='gender'>Gender</label>
		<input type='text' name='gender' id='gender' value='$value' class='form-control' required />
	</div>";
	}

	public function getDobFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='dob'>Dob</label>
		<input type='text' name='dob' id='dob' value='$value' class='form-control' required />
	</div>";
	}

	public function getMarital_statusFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='marital_status'>Marital Status</label>
		<input type='text' name='marital_status' id='marital_status' value='$value' class='form-control' required />
	</div>";
	}

	public function getPhone_numberFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='phone_number'>Phone Number</label>
		<input type='text' name='phone_number' id='phone_number' value='$value' class='form-control' required />
	</div>";
	}

	public function getEmailFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='email'>Email</label>
		<input type='text' name='email' id='email' value='$value' class='form-control' required />
	</div>";
	}

	public function getUnits_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'units','display'=>'units_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'units_name' as value from 'units' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('units', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='units_id' id='units_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
		<label for='units_id'>Units</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='units_id' id='units_id' class='form-control'>
					$option
				</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getUser_RankFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='user_rank'>User Rank</label>
		<input type='text' name='user_rank' id='user_rank' value='$value' class='form-control' required />
	</div>";
	}

	public function getRoleFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='role'>Role</label>
		<input type='text' name='role' id='role' value='$value' class='form-control' required />
	</div>";
	}

	public function getAvatarFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='avatar'>Avatar</label>
		<input type='text' name='avatar' id='avatar' value='$value' class='form-control' required />
	</div>";
	}

	public function getAddressFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='address'>Address</label>
		<input type='text' name='address' id='address' value='$value' class='form-control' required />
	</div>";
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

	protected function getUnits()
	{
		$query = 'SELECT * FROM staff_department WHERE id=?';
		if (!isset($this->array['units_id'])) {
			return null;
		}
		$id = $this->array['units_id'];
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		include_once('Staff_department.php');
		return new Staff_department($result[0]);
	}

	public function getFullname()
	{
		return $this->firstname . ' ' . $this->lastname . ' ' . $this->othernames;
	}

	public function getAbbr()
	{
		$first = $this->firstname[0];
		$second = $this->lastname[0];
		return strtoupper($first . $second);
	}

	public function delete($id = null, &$dbObject = null, $type = null): bool
	{
		permissionAccess($this, 'user_delete', 'delete');
		$currentUser = $this->webSessionManager->currentAPIUser();
		$db = $dbObject ?? $this->db;
		if (parent::delete($id, $db)) {
			$query = "DELETE from users_new where user_table_id=? and user_type='staff'";
			if ($this->query($query, array($id))) {
				logAction($this, 'user_deletion', $currentUser->id, $id);
				return true;
			}
		}
		return false;
	}

	public function APIList($filterList, $queryString, $start, $len, $orderBy): array
	{
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		$filterQuery .= ($filterQuery ? " and " : " where ") . " b.user_type ='staff' ";

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		}else {
			$filterQuery .= " order by a.lastname asc ";
		}

		if (request()->getGet('start') && $len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$filterQuery .= " limit $start, $len";
		}

		if (!$filterValues) {
			$filterValues = [];
		}

		$query = "SELECT " . buildApiClause(static::$apiSelectClause, 'a') . " ,b.user_login as username,b.id as user_id from staffs a left join users_new b on b.user_table_id = a.id $filterQuery";
		$query2 = "SELECT FOUND_ROWS() as totalCount";

		$res = $this->db->query($query, $filterValues);
		$res = $res->result_array();
		$res2 = $this->db->query($query2);
		$res2 = $res2->result_array();
		$res = $this->processList($res);
		return [$res, $res2];
	}

	private function processList(array $items)
	{
		loadClass($this->load, 'roles');
		for ($i = 0; $i < count($items); $i++) {
			$items[$i] = $this->loadExtras($items[$i]);
		}
		return $items;
	}


	private function loadExtras($item)
	{
		if($item['user_id']){
			$userRole = $this->roles->getUserRole($item['user_id']);
			$item['role'] = $userRole ? $userRole['name'] : '';
		}

		return $item;
	}

	public function getStaffDepartment($user_department)
	{
		return $this->db->get_where('department', ['id' => $user_department])->row();
	}


}

