<?php
namespace App\Entities;

use App\Models\Crud;
use App\Libraries\EntityLoader;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the programme table.
 */
use App\Enums\AdmissionEnum as Admission;
class Programme extends Crud
{
	protected static $tablename = 'Programme';
	/* this array contains the field that can be null*/
	static $nullArray = array('code');
	static $compositePrimaryKey = array();
	static $uploadDependency = array();
	/*this array contains the fields that are unique*/
	static $uniqueArray = array();
	/*this is an associative array containing the fieldname and the type of the field*/
	static $typeArray = array('faculty_id' => 'int', 'department_id' => 'int', 'name' => 'varchar', 'code' => 'varchar', 'active' => 'tinyint', 'date_created' => 'datetime');
	/*this is a dictionary that map a field name with the label name that will be shown in a form*/
	static $labelArray = array('id' => '', 'faculty_id' => '', 'department_id' => '', 'name' => '', 'code' => '', 'active' => '', 'date_created' => '');
	/*associative array of fields that have default value*/
	static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
	static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

	static $relation = array('faculty' => array('faculty_id', 'ID')
	, 'department' => array('department_id', 'ID')
	, 'academic_record' => array(array('ID', 'programme_id', 1))
	, 'admission_programme_requirements' => array(array('ID', 'programme_id', 1))
	, 'applicants' => array(array('ID', 'programme_id', 1))
	, 'course_configuration' => array(array('ID', 'programme_id', 1))
	, 'course_mapping' => array(array('ID', 'programme_id', 1))
	, 'grade_sheet_temp_table' => array(array('ID', 'programme_id', 1))
	, 'matric_number_generated' => array(array('ID', 'programme_id', 1))
	, 'transaction' => array(array('ID', 'programme_id', 1))
	, 'transaction1' => array(array('ID', 'programme_id', 1))
	, 'transaction2' => array(array('ID', 'programme_id', 1))
	, 'transaction_archive' => array(array('ID', 'programme_id', 1))
	);
	static $tableAction = array('delete' => 'delete/programme', 'edit' => 'edit/programme');
	static $apiSelectClause = ['id', 'name', 'code'];

	function __construct($array = array())
	{
		parent::__construct($array);
	}

	function getFaculty_idFormField($value = '')
	{
		$fk = null;//change the value of this variable to array('table'=>'faculty','display'=>'faculty_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

		if (is_null($fk)) {
			return $result = "<input type='hidden' value='$value' name='faculty_id' id='faculty_id' class='form-control' />
			";
		}
		if (is_array($fk)) {
			$result = "<div class='form-group'>
		<label for='faculty_id'>Faculty Id</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='faculty_id' id='faculty_id' class='form-control'>
			$option
		</select>";
		}
		$result .= "</div>";
		return $result;

	}

	function getDepartment_idFormField($value = '')
	{
		$fk = null;//change the value of this variable to array('table'=>'department','display'=>'department_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

		if (is_null($fk)) {
			return $result = "<input type='hidden' value='$value' name='department_id' id='department_id' class='form-control' />
			";
		}
		if (is_array($fk)) {
			$result = "<div class='form-group'>
		<label for='department_id'>Department Id</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='department_id' id='department_id' class='form-control'>
			$option
		</select>";
		}
		$result .= "</div>";
		return $result;

	}

	function getNameFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='name' >Name</label>
		<input type='text' name='name' id='name' value='$value' class='form-control' required />
</div> ";

	}

	function getCodeFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='code' >Code</label>
		<input type='text' name='code' id='code' value='$value' class='form-control'  />
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


	protected function getFaculty()
	{
		$query = 'SELECT * FROM faculty WHERE id=?';
		if (!isset($this->array['id'])) {
			return null;
		}
		$id = $this->array['id'];
		$result = $this->db->query($query, array($id));
		$result = $result->getResultArray();
		if (empty($result)) {
			return false;
		}
		return new \App\Entities\Faculty($result[0]);
	}

	protected function getDepartment()
	{
		$query = 'SELECT * FROM department WHERE id=?';
		if (!isset($this->array['id'])) {
			return null;
		}
		$id = $this->array['id'];
		$result = $this->db->query($query, array($id));
		$result = $result->getResultArray();
		if (empty($result)) {
			return false;
		}
		return new \App\Entities\Department($result[0]);
	}

	protected function getAcademic_record()
	{
		$query = 'SELECT * FROM academic_record WHERE programme_id=?';
		$id = $this->array['id'];
		$result = $this->db->query($query, array($id));
		$result = $result->getResultArray();
		if (empty($result)) {
			return false;
		}
		$resultObjects = [];
		foreach ($result as $value) {
    		$resultObjects[] = new \App\Entities\Academic_record($value);
		}

		return $resultObjects;
	}

	protected function getAdmission_programme_requirements()
	{
		$query = 'SELECT * FROM admission_programme_requirements WHERE programme_id=?';
		$id = $this->array['id'];
		$result = $this->db->query($query, array($id));
		$result = $result->getResultArray();
		if (empty($result)) {
			return false;
		}
		$resultObjects = [];
		foreach ($result as $value) {
    		$resultObjects[] = new \App\Entities\Admission_programme_requirements($value);
		}

		return $resultObjects;
	}

	protected function getApplicants()
	{
		$query = 'SELECT * FROM applicants WHERE programme_id=?';
		$id = $this->array['id'];
		$result = $this->db->query($query, array($id));
		$result = $result->getResultArray();
		if (empty($result)) {
			return false;
		}
		$resultObjects = [];
		foreach ($result as $value) {
    		$resultObjects[] = new \App\Entities\Applicants($value);
		}

		return $resultObjects;
	}

	protected function getCourse_configuration()
	{
		$query = 'SELECT * FROM course_configuration WHERE programme_id=?';
		$id = $this->array['id'];
		$result = $this->db->query($query, array($id));
		$result = $result->getResultArray();
		if (empty($result)) {
			return false;
		}
		$resultObjects = [];
		foreach ($result as $value) {
    		$resultObjects[] = new \App\Entities\Course_configuration($value);
		}

		return $resultObjects;
	}

	protected function getCourse_mapping()
	{
		$query = 'SELECT * FROM course_mapping WHERE programme_id=?';
		$id = $this->array['id'];
		$result = $this->db->query($query, array($id));
		$result = $result->getResultArray();
		if (empty($result)) {
			return false;
		}
		$resultObjects = [];
		foreach ($result as $value) {
    		$resultObjects[] = new \App\Entities\Course_mapping($value);
		}

		return $resultObjects;
	}

	protected function getGrade_sheet_temp_table()
	{
		$query = 'SELECT * FROM grade_sheet_temp_table WHERE programme_id=?';
		$id = $this->array['id'];
		$result = $this->db->query($query, array($id));
		$result = $result->getResultArray();
		if (empty($result)) {
			return false;
		}
		$resultObjects = [];
		foreach ($result as $value) {
    		$resultObjects[] = new \App\Entities\Grade_sheet_temp_table($value);
		}

		return $resultObjects;
	}

	protected function getMatric_number_generated()
	{
		$query = 'SELECT * FROM matric_number_generated WHERE programme_id=?';
		$id = $this->array['id'];
		$result = $this->db->query($query, array($id));
		$result = $result->getResultArray();
		if (empty($result)) {
			return false;
		}
		$resultObjects = [];
		foreach ($result as $value) {
    		$resultObjects[] = new \App\Entities\Matric_number_generated($value);
		}

		return $resultObjects;
	}

	protected function getTransaction()
	{
		$query = 'SELECT * FROM transaction WHERE programme_id=?';
		$id = $this->array['id'];
		$result = $this->db->query($query, array($id));
		$result = $result->getResultArray();
		if (empty($result)) {
			return false;
		}
		$resultObjects = [];
		foreach ($result as $value) {
    		$resultObjects[] = new \App\Entities\Transaction($value);
		}

		return $resultObjects;
	}

	protected function getTransaction1()
	{
		$query = 'SELECT * FROM transaction1 WHERE programme_id=?';
		$id = $this->array['id'];
		$result = $this->db->query($query, array($id));
		$result = $result->getResultArray();
		if (empty($result)) {
			return false;
		}
		$resultObjects = [];
		foreach ($result as $value) {
    		$resultObjects[] = new \App\Entities\Transaction1($value);
		}

		return $resultObjects;
	}

	protected function getTransaction2()
	{
		$query = 'SELECT * FROM transaction2 WHERE programme_id=?';
		$id = $this->array['id'];
		$result = $this->db->query($query, array($id));
		$result = $result->getResultArray();
		if (empty($result)) {
			return false;
		}
		$resultObjects = [];
		foreach ($result as $value) {
    		$resultObjects[] = new \App\Entities\Transaction2($value);
		}

		return $resultObjects;
	}

	protected function getTransaction_archive()
	{
		$query = 'SELECT * FROM transaction_archive WHERE programme_id=?';
		$id = $this->array['id'];
		$result = $this->db->query($query, array($id));
		$result = $result->getResultArray();
		if (empty($result)) {
			return false;
		}
		$resultObjects = [];
		foreach ($result as $value) {
    		$resultObjects[] = new \App\Entities\Transaction_archive($value);
		}

		return $resultObjects;
	}

	public function delete($id = null, &$dbObject = null, $type = null): bool
	{
		permissionAccess('faculty_programme_delete', 'delete');
		$currentUser = WebSessionManager::currentAPIUser();
		$db = $dbObject ?? $this->db;
		if (parent::delete($id, $db)) {
			logAction($this->db, 'programme_deletion', $currentUser->id, $id);
			return true;
		}
		return false;
	}

	public function APIList($filterList, $queryString, $start, $len, $orderBy)
	{
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		$filterQuery .= ($filterQuery ? " and " : " where ") . " active = '1' ";

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by name asc ";
		}

		if ($len) {
			$start = $this->db->escape($start);
			$len = $this->db->escape($len);
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

	public function getProgrammeById($id, $getCode = false)
	{
		$query = $this->db->table('programme')
                  ->where('id', $id)->where('active', 1)
                  ->get();

		if ($query->getNumRows() > 0) {
			$row = $query->getRow();
			return ($getCode == true) ? $row->code : $row->name;
		} else {
			return null;
		}
	}

	public function getProgrammeIdByName($name)
	{
		$name = strtolower($name);
		$query = $this->db->table('programme')
                  ->where('name', $name)
                  ->get();

		if ($query->getNumRows() > 0) {
			$row = $query->getRow();
			return $row->id;
		} else {
			return '';
		}
	}

	public function getProgrammeRecord($id)
	{
		$query = "SELECT a.name as programme, c.name as faculty, b.name as department from programme a left join department b 
		on b.id = a.department_id left join faculty c on c.id = a.faculty_id where a.id = ? and a.active = 1";
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}

		return [
			'programme' => $result[0]['programme'],
			'faculty' => $result[0]['faculty'],
			'department' => $result[0]['department']
		];
	}

	public function getFacultyByProgramme($id)
	{
		$query = $this->db->table('programme')
                  ->where('id', $id)->where('active', 1)
                  ->get();

		if ($query->getNumRows() > 0) {
			return $query->getRow();
		} else {
			return null;
		}
	}

	public function applicantProgramme(){
		$admissionSession = get_setting('admission_session_update');
		$admission = Admission::CHANGE_MODE_OF_STUDY->value->value;
		$query = "select programme.id,upper(programme.name) as name from programme join department on department.id=programme.department_id join faculty on faculty.id=programme.faculty_id join admission_programme_requirements on programme.id=admission_programme_requirements.programme_id and admission_programme_requirements.active=1 and admission_programme_requirements.session=? and admission_id=? where programme.active=1";
		return $this->query($query, [$admissionSession, $admission]);
	}


}
