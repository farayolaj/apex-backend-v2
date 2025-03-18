<?php

namespace App\Entities;

use App\Models\Crud;

/**
 * This class is automatically generated based on the structure of the table.
 * And it represent the model of the user_banks table
 */
class User_banks extends Crud
{

	/**
	 * This is the entity name equivalent to the table name
	 * @var string
	 */
	protected static $tablename = "User_banks";

	/**
	 * This array contains the field that can be null
	 * @var array
	 */
	public static $nullArray = ['updated_at'];

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
	public static $displayField = 'account_number';

	/**
	 * This array contains the fields that are unique
	 * @var array
	 */
	public static $uniqueArray = ['account_number'];

	/**
	 * This is an associative array containing the fieldname and the datatype
	 * of the field
	 * @var array
	 */
	public static $typeArray = ['users_id' => 'int', 'account_name' => 'varchar', 'account_number' => 'varchar', 'bank_lists_id' => 'int',
		'is_primary' => 'tinyint', 'created_at' => 'timestamp', 'updated_at' => 'timestamp', 'bank_code' => 'varchar'];

	/**
	 * This is a dictionary that map a field name with the label name that
	 * will be shown in a form
	 * @var array
	 */
	public static $labelArray = ['id' => '', 'users_id' => '', 'account_name' => '', 'account_number' => '', 'bank_lists_id' => '',
		'is_primary' => '', 'created_at' => '', 'updated_at' => '', 'bank_code' => ''];

	/**
	 * Associative array of fields in the table that have default value
	 * @var array
	 */
	public static $defaultArray = ['is_primary' => '0', 'created_at' => 'current_timestamp()'];

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
	public static $relation = ['users' => array('users_id', 'id')
		, 'bank_lists' => array('bank_lists_id', 'id')
	];

	/**
	 * This are the action allowed to be performed on the entity and this can
	 * be changed in the formConfig model file for flexibility
	 * @var array
	 */
	public static $tableAction = ['delete' => 'delete/user_banks', 'edit' => 'edit/user_banks'];

	public static $apiSelectClause = ['id', 'users_id', 'account_name', 'account_number', 'bank_lists_id', 'bank_Code', 'is_primary',
		'created_at', 'updated_at'];

	public function __construct(array $array = [])
	{
		parent::__construct($array);
	}

	public function getUsers_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'users','display'=>'users_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'users_name' as value from 'users' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('users', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='users_id' id='users_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
		<label for='users_id'>Users</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='users_id' id='users_id' class='form-control'>
					$option
				</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getAccount_nameFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='account_name'>Account Name</label>
		<input type='text' name='account_name' id='account_name' value='$value' class='form-control' required />
	</div>";
	}

	public function getAccount_numberFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='account_number'>Account Number</label>
		<input type='text' name='account_number' id='account_number' value='$value' class='form-control' required />
	</div>";
	}

	public function getBank_lists_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'bank_lists','display'=>'bank_lists_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'bank_lists_name' as value from 'bank_lists' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('bank_lists', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='bank_lists_id' id='bank_lists_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
		<label for='bank_lists_id'>Bank Lists</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='bank_lists_id' id='bank_lists_id' class='form-control'>
					$option
				</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getIs_primaryFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='is_primary'>Is Primary</label>
		<input type='text' name='is_primary' id='is_primary' value='$value' class='form-control' required />
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


	protected function getUsers()
	{
		$query = 'SELECT * FROM users_new WHERE id=?';
		if (!isset($this->array['id'])) {
			return null;
		}
		$id = $this->array['id'];
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		include_once('Users_new.php');
		$resultObject = new Users_new($result[0]);
		return $resultObject;
	}

	protected function getBank_lists()
	{
		$query = 'SELECT * FROM bank_lists WHERE id=?';
		if (!isset($this->array['bank_lists_id'])) {
			return null;
		}
		$id = $this->array['bank_lists_id'];
		$result = $this->query($query, [$id]);
		if (!$result) {
			return null;
		}
		include_once('Bank_lists.php');
		$resultObject = new Bank_lists($result[0]);
		return $resultObject;
	}

	public function getUserBankDetails($user_id)
	{
		$query = "SELECT a.*,b.name as bank_name,b.code as bank_code,b.slug as bank_accroymn from user_banks a join 
		bank_lists b on a.bank_lists_id = b.id where a.users_id=?";
		return $this->query($query, [$user_id]);
	}

	public function reverseBankPrimary($id)
	{
		$query = "UPDATE user_banks SET is_primary = '0' WHERE users_id = ?";
		return $this->db->query($query, [$id]);
	}

	public static function formatRequestBeneficiaries(object $userBank, $amount = 0): array
	{
		$bankName = $userBank->bank_lists->name ?? '';
		return [
			'id' => $userBank->id,
			'account_name' => $userBank->account_name,
			'account_number' => $userBank->account_number,
			'bank_code' => $userBank->bank_code,
			'bank_name' => $bankName,
			'amount' => $amount,
		];
	}

	public function APIList($filterList, $queryString, $start, $len, $orderBy): array
	{
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by a.created_at asc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}

		return $this->apiListQuery($filterQuery, $filterValues, 'project-title');
	}


	public function APIListStaff($filterList, $queryString, $start, $len, $orderBy): array
	{
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		$filterQuery .= ($filterQuery ? " and " : " where ") . " c.user_type = 'staff' and a.is_primary = '1' ";

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by a.created_at asc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}

		return $this->apiListQuery($filterQuery, $filterValues, 'all-staff');
	}

	private function apiListQuery(string $filterQuery, ?array $filterValues, string $queryType = 'project'): array
	{
		$tablename = strtolower(self::$tablename);
		if ($queryType === 'project') {
			$query = "SELECT " . buildApiClause(static::$apiSelectClause, 'a') . " from $tablename a $filterQuery";
		} else if ($queryType === 'all-staff') {
			$query = "SELECT " . buildApiClause(static::$apiSelectClause, 'a') . ", b.slug as bank_acronym,b.name as bank_name 
			from $tablename a join bank_lists b on b.id = a.bank_lists_id join users_new c on c.id = a.users_id $filterQuery";
		}

		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->result_array();
		$res2 = $this->db->query($query2);
		$res2 = $res2->result_array();
		return [$res, $res2];
	}


}

