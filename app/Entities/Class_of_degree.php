<?php
namespace App\Entities;

use App\Models\Crud;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the class_of_degree table.
 */
class Class_of_degree extends Crud
{
	protected static $tablename = 'Class_of_degree';
	/* this array contains the field that can be null*/
	static $nullArray = array('short_form');
	static $compositePrimaryKey = array();
	static $uploadDependency = array();
	/*this array contains the fields that are unique*/
	static $uniqueArray = array();
	/*this is an associative array containing the fieldname and the type of the field*/
	static $typeArray = array('name' => 'varchar', 'short_form' => 'varchar', 'cgpa_from' => 'double', 'cgpa_to' => 'double', 'year_of_entry' => 'int', 'active' => 'tinyint');
	/*this is a dictionary that map a field name with the label name that will be shown in a form*/
	static $labelArray = array('id' => '', 'name' => '', 'short_form' => '', 'cgpa_from' => '', 'cgpa_to' => '', 'year_of_entry' => '', 'active' => '');
	/*associative array of fields that have default value*/
	static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
	static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

	static $relation = array();
	static $tableAction = array('delete' => 'delete/class_of_degree', 'edit' => 'edit/class_of_degree');
	static $apiSelectClause = ['id', 'name', 'short_form', 'cgpa_from', 'cgpa_to', 'year_of_entry'];

	function __construct($array = array())
	{
		parent::__construct($array);
	}

	function getNameFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='name' >Name</label>
		<input type='text' name='name' id='name' value='$value' class='form-control' required />
</div> ";

	}

	function getShort_formFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='short_form' >Short Form</label>
		<input type='text' name='short_form' id='short_form' value='$value' class='form-control'  />
</div> ";

	}

	function getCgpa_fromFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='cgpa_from' >Cgpa From</label>
		<input type='text' name='cgpa_from' id='cgpa_from' value='$value' class='form-control' required />
</div> ";

	}

	function getCgpa_toFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='cgpa_to' >Cgpa To</label>
		<input type='text' name='cgpa_to' id='cgpa_to' value='$value' class='form-control' required />
</div> ";

	}

	function getYear_of_entryFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='year_of_entry' >Year Of Entry</label><input type='number' name='year_of_entry' id='year_of_entry' value='$value' class='form-control' required />
</div> ";

	}

	function getActiveFormField($value = '')
	{

		return "<div class='form-group'>
	<label class='form-checkbox'>Active</label>
	<select class='form-control' id='active' name='active' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

	}

	public function getCgpaClass($cgpa, $class_session)
	{
		$query = "SELECT name from class_of_degree where year_of_entry = ? and '$cgpa' between cgpa_from and cgpa_to";
		$result = $this->query($query, [$class_session]);
		if ($result) {
			return $result[0]['name'];
		}
	}

	public function delete($id = null, &$dbObject = null, $type = null): bool
	{
		permissionAccess('exam_grade_delete', 'delete');
		$currentUser = WebSessionManager::currentAPIUser();
		$db = $dbObject ?? $this->db;
		if (parent::delete($id, $db)) {
			logAction($this->db, 'class_of_degree_deletion', $currentUser->id, $id);
			return true;
		}
		return false;
	}

	public function APIList($filterList, $queryString, $start, $len, $orderBy): array
	{
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		$filterQuery .= ($filterQuery ? " and " : " where ") . " a.active = '1' ";

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by b.date desc, a.name asc";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->escape($start);
			$len = $this->db->escape($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}
		$tablename = strtolower(self::$tablename);
		$query = "SELECT " . buildApiClause(static::$apiSelectClause, 'a') . ", b.date as year_of_entry from $tablename a 
		join sessions b on b.id = a.year_of_entry $filterQuery";

		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->getResultArray();
		$res2 = $this->db->query($query2);
		$res2 = $res2->getResultArray();
		return [$res, $res2];
	}


}
