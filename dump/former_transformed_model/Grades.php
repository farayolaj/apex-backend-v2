<?php

namespace App\Entities;

use App\Models\Crud;
use App\Models\WebSessionManager;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the grades table.
 */
class Grades extends Crud
{
    protected static $tablename = 'Grades';
    /* this array contains the field that can be null*/
    static $nullArray = array();
    static $compositePrimaryKey = array();
    static $uploadDependency = array();
    /*this array contains the fields that are unique*/
    static $uniqueArray = array();
    /*this is an associative array containing the fieldname and the type of the field*/
    static $typeArray = array('name' => 'varchar', 'point' => 'tinyint', 'mark_from' => 'tinyint', 'mark_to' => 'tinyint', 'year_of_entry' => 'int', 'active' => 'tinyint');
    /*this is a dictionary that map a field name with the label name that will be shown in a form*/
    static $labelArray = array('id' => '', 'name' => '', 'point' => '', 'mark_from' => '', 'mark_to' => '', 'year_of_entry' => '', 'active' => '');
    /*associative array of fields that have default value*/
    static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
    static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

    static $relation = array();
    static $tableAction = array('delete' => 'delete/grades', 'edit' => 'edit/grades');
    static $apiSelectClause = ['id', 'name', 'point', 'mark_from', 'mark_to', 'year_of_entry', 'active'];

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

    function getPointFormField($value = '')
    {

        return "<div class='form-group'>
	<label class='form-checkbox'>Point</label>
	<select class='form-control' id='point' name='point' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

    }

    function getMark_fromFormField($value = '')
    {

        return "<div class='form-group'>
	<label class='form-checkbox'>Mark From</label>
	<select class='form-control' id='mark_from' name='mark_from' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

    }

    function getMark_toFormField($value = '')
    {

        return "<div class='form-group'>
	<label class='form-checkbox'>Mark To</label>
	<select class='form-control' id='mark_to' name='mark_to' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
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

    public function getGrade($score, $gradeSession)
    {
        $query = "SELECT name from grades where year_of_entry = ? and '$score' between mark_from and mark_to";
        $result = $this->query($query, [$gradeSession]);
        if ($result) {
            return $result[0]['name'];
        }
    }

    public function getGradePoint($score, $gradeSession)
    {
        $query = "SELECT point from grades where year_of_entry = ? and '$score' between mark_from and mark_to";
        $result = $this->query($query, [$gradeSession]);
        if ($result) {
            return $result[0]['point'];
        }
    }

    public function delete($id = null, &$dbObject = null, $type = null): bool
    {
        permissionAccess('exam_grade_delete', 'delete');
        $currentUser = WebSessionManager::currentAPIUser();
        $db = $dbObject ?? $this->db;
        if (parent::delete($id, $db)) {
            logAction($db, 'grades_deletion', $currentUser->id, $id);
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
            $filterQuery .= " order by a.name asc";
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
        $query = "SELECT " . buildApiClause(static::$apiSelectClause, 'a') . ", b.date as year_of_entry from $tablename a join sessions b on b.id = a.year_of_entry $filterQuery";

        $query2 = "SELECT FOUND_ROWS() as totalCount";
        $res = $this->db->query($query, $filterValues);
        $res = $res->getResultArray();
        $res2 = $this->db->query($query2);
        $res2 = $res2->getResultArray();
        return [$res, $res2];
    }


}
