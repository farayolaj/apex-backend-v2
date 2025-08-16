<?php
namespace App\Entities;

use App\Models\Crud;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the users table.
 */
class Users extends Crud
{
	protected static $tablename = 'Users';
	/* this array contains the field that can be null*/
	static $nullArray = array('date_registered');
	static $compositePrimaryKey = array();
	static $uploadDependency = array();
	/*this array contains the fields that are unique*/
	static $uniqueArray = array();
	/*this is an associative array containing the fieldname and the type of the field*/
	static $typeArray = array('title' => 'varchar', 'staff_id' => 'varchar', 'firstname' => 'varchar', 'othernames' => 'varchar', 'lastname' => 'varchar', 'gender' => 'varchar', 'dob' => 'varchar', 'marital_status' => 'varchar', 'user_login' => 'varchar', 'user_pass' => 'text', 'user_phone' => 'varchar', 'user_email' => 'varchar', 'user_rank' => 'varchar', 'user_unit' => 'varchar', 'user_role' => 'varchar', 'is_lecturer' => 'tinyint', 'avatar' => 'varchar', 'address' => 'varchar', 'active' => 'tinyint', 'date_registered' => 'timestamp');
	/*this is a dictionary that map a field name with the label name that will be shown in a form*/
	static $labelArray = array('id' => '', 'title' => '', 'staff_id' => '', 'firstname' => '', 'othernames' => '', 'lastname' => '', 'gender' => '', 'dob' => '', 'marital_status' => '', 'user_login' => '', 'user_pass' => '', 'user_phone' => '', 'user_email' => '', 'user_rank' => '', 'user_unit' => '', 'user_role' => '', 'is_lecturer' => '', 'avatar' => '', 'address' => '', 'active' => '', 'date_registered' => '');
	/*associative array of fields that have default value*/
	static $defaultArray = array('date_registered' => 'current_timestamp()');
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
	static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

	static $relation = array();
	static $tableAction = array('delete' => 'delete/users', 'edit' => 'edit/users');

	function __construct($array = array())
	{
		parent::__construct($array);
	}

	function getTitleFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='title' >Title</label>
		<input type='text' name='title' id='title' value='$value' class='form-control' required />
</div> ";

	}

	function getStaff_idFormField($value = '')
	{
		$fk = null;//change the value of this variable to array('table'=>'staff','display'=>'staff_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

		if (is_null($fk)) {
			return $result = "<input type='hidden' value='$value' name='staff_id' id='staff_id' class='form-control' />
			";
		}
		if (is_array($fk)) {
			$result = "<div class='form-group'>
		<label for='staff_id'>Staff Id</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='staff_id' id='staff_id' class='form-control'>
			$option
		</select>";
		}
		$result .= "</div>";
		return $result;

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

	public function APIList($filterList, $queryString, $start, $len)
	{
		$temp = getFilterQueryFromDict($filterList);
        $filterQuery = buildCustomWhereString($temp[0], $queryString);
		$filterValues = $temp[1];

		if ($len) {
			$start = $this->db->escapeString($start);
			$len = $this->db->escapeString($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}

		$query = "SELECT SQL_CALC_FOUND_ROWS id,title,lastname,firstname,othernames,dob,gender,
            marital_status,user_phone ,user_email,user_login ,is_lecturer, address, active from users $filterQuery";
		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->getResultArray();
		$res2 = $this->db->query($query2);
		$res2 = $res2->getResultArray();
		return [$res, $res2];
	}

	public function getUserByID($userID, $name = false)
	{
		$query = "SELECT * from users where id = ?";
		$result = $this->query($query, [$userID]);
		if (!$result) {
			return false;
		}

		if ($name) {
			return $result[0]['title'] . " " . $result[0]['lastname'] . " " . $result[0]['firstname'];
		}

		return $result[0];
	}


}
