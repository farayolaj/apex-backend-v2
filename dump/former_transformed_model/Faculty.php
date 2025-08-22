<?php
namespace App\Entities;

use App\Models\Crud;
use App\Models\WebSessionManager;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the faculty table.
 */
class Faculty extends Crud
{
	protected static $tablename = 'Faculty';
	/* this array contains the field that can be null*/
	static $nullArray = array();
	static $compositePrimaryKey = array();
	static $uploadDependency = array();
	/*this array contains the fields that are unique*/
	static $displayField = 'name';
	static $uniqueArray = array();
	/*this is an associative array containing the fieldname and the type of the field*/
	static $typeArray = array('name' => 'varchar','slug'=>'varchar', 'active' => 'tinyint', 'date_created' => 'datetime');
	/*this is a dictionary that map a field name with the label name that will be shown in a form*/
	static $labelArray = array('id' => '', 'name' => '','slug'=>'', 'active' => '', 'date_created' => '');
	/*associative array of fields that have default value*/
	static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
	static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

	static $relation = array('department' => array(array('ID', 'faculty_id', 1))
	, 'programme' => array(array('ID', 'faculty_id', 1))
	);
	static $tableAction = array('delete' => array('icon' => 'fa fa-close', 'link' => 'delete/faculty'), 'edit' => ['icon' => 'fa fa-edit', 'link' => 'edit/faculty']);
	static $apiSelectClause = ['id', 'name', 'slug'];

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

	function getDate_createdFormField($value = '')
	{

		return " ";

	}


	protected function getDepartment()
	{
		$query = 'SELECT * FROM department WHERE faculty_id=?';
		$id = $this->array['id'];
		$result = $this->db->query($query, array($id));
		$result = $result->getResultArray();
		if (empty($result)) {
			return false;
		}
		$resultObjects = [];
		foreach ($result as $value) {
    		$resultObjects[] = new \App\Entities\Department($value);
		}
		return $resultObjects;
	}

	protected function getProgramme()
	{
		$query = 'SELECT * FROM programme WHERE faculty_id=?';
		$id = $this->array['id'];
		$result = $this->db->query($query, array($id));
		$result = $result->getResultArray();
		if (empty($result)) {
			return false;
		}
		$resultObjects = [];
		foreach ($result as $value) {
    		$resultObjects[] = new \App\Entities\Programme($value);
		}
		return $resultObjects;
	}

	public function getFacultyById($id)
	{
		$query = $this->db->table('faculty')
                  ->where('id', $id)->where('active', 1)
                  ->get();

		if ($query->getNumRows() > 0) {
			return $query->getRow();
		} else {
			return null;
		}
	}

	public function delete($id = null, &$dbObject = null, $type = null): bool
	{
		permissionAccess('faculty_delete', 'delete');
		$currentUser = WebSessionManager::currentAPIUser();
		$db = $dbObject ?? $this->db;
		if (parent::delete($id, $db)) {
			logAction( 'faculty_deletion', $currentUser->id, $id);
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
			$filterQuery .= " order by name asc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->escapeString($start);
			$len = $this->db->escapeString($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}
		$tablename = strtolower(self::$tablename);
		$query = "SELECT " . buildApiClause(static::$apiSelectClause, $tablename) . " from $tablename $filterQuery";

		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->getResultArray();
		$res2 = $this->db->query($query2);
		$res2 = $res2->getResultArray();
		return [$res, $res2];
	}


}
