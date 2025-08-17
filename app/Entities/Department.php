<?php

namespace App\Entities;

use App\Models\Crud;
use App\Models\WebSessionManager;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the department table.
 */
class Department extends Crud
{
    protected static $tablename = 'Department';
    /* this array contains the field that can be null*/
    static $nullArray = array();
    static $compositePrimaryKey = array();
    static $uploadDependency = array();
    /*this array contains the fields that are unique*/
    static $uniqueArray = array();
    /*this is an associative array containing the fieldname and the type of the field*/
    static $typeArray = array(
        'faculty_id' => 'int',
        'name' => 'varchar',
        'slug' => 'varchar',
        'code' => 'varchar',
        'active' => 'tinyint',
        'date_created' => 'datetime',
        'type' => 'enum'
    );
    static $displayField = 'name';
    /*this is a dictionary that map a field name with the label name that will be shown in a form*/
    static $labelArray = array(
        'id' => '',
        'faculty_id' => 'Faculty',
        'name' => '',
        'slug' => '',
        'code' => '',
        'active' => '',
        'date_created' => '',
        'type' => ''
    );
    /*associative array of fields that have default value*/
    static $defaultArray = array();
    //populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
    static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

    static $relation = array(
        'faculty' => array('faculty_id', 'ID')
    ,
        'matric_number_generated' => array(array('ID', 'department_id', 1))
    ,
        'programme' => array(array('ID', 'department_id', 1))
    );
    static $tableAction = array('delete' => 'delete/department', 'edit' => 'edit/department');
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
		<input type='text' name='code' id='code' value='$value' class='form-control' required />
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

    protected function getMatric_number_generated()
    {
        $query = 'SELECT * FROM matric_number_generated WHERE department_id=?';
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

    protected function getProgramme()
    {
        $query = 'SELECT * FROM programme WHERE department_id=?';
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

    public function getFacultyByDepartment($id)
    {
        $query = $this->db->table('department')
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
        permissionAccess('faculty_department_delete', 'delete');
        $currentUser = WebSessionManager::currentAPIUser();
        $db = $dbObject ?? $this->db;
        if (parent::delete($id, $db)) {
            logAction($this->db, 'department_deletion', $currentUser->id, $id);
            return true;
        }
        return false;
    }

    public function APIList($filterList, $queryString, $start, $len, $orderBy): array
    {
        $temp = getFilterQueryFromDict($filterList);
        $filterQuery = buildCustomWhereString($temp[0], $queryString, false);
        $filterValues = $temp[1];

        $filterQuery .= ($filterQuery ? " and " : " where ") . " type='academic' and active = '1' ";

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
        $query = "SELECT " . buildApiClause(static::$apiSelectClause, 'a') . " from $tablename a $filterQuery";

        $query2 = "SELECT FOUND_ROWS() as totalCount";
        $res = $this->db->query($query, $filterValues);
        $res = $res->getResultArray();
        $res2 = $this->db->query($query2);
        $res2 = $res2->getResultArray();
        return [$res, $res2];
    }

    public function getUserDepartment($user_department)
    {
        return $this->db->table('department')
            ->getWhere(['id' => $user_department, 'type' => 'academic'])
            ->getRow();
    }

    public function totalDepartmentStudent($department, $session, $semester)
    {
        $query = "SELECT COUNT(DISTINCT CASE WHEN e.total_score IS NOT NULL THEN e.student_id END) 
			AS total FROM courses c JOIN course_enrollment e ON c.id = e.course_id where c.department_id = ? 
			and e.session_id = ? and e.semester = ? GROUP BY c.department_id";
        $result = $this->query($query, array($department, $session, $semester));
        return $result[0]['total'] ?? 0;
    }

    public function checkDepartmentClaim($department, $session, $type)
    {
        $query = "SELECT * from course_request_claims where session_id = ? and exam_type = ? and department_id = ?
                order by created_at desc";
        return $this->query($query, [$session, $type, $department]);
    }
}
