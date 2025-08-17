<?php

namespace App\Entities;

use App\Libraries\EntityLoader;
use App\Models\Crud;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the course_manager table.
 */
class Course_manager extends Crud
{
    protected static $tablename = 'Course_manager';
    /* this array contains the field that can be null*/
    static $nullArray = array();
    static $compositePrimaryKey = array();
    static $uploadDependency = array();
    /*this array contains the fields that are unique*/
    static $uniqueArray = array();
    /*this is an associative array containing the fieldname and the type of the field*/
    static $typeArray = array('session_id' => 'varchar', 'course_id' => 'varchar', 'course_manager_id' => 'varchar',
        'course_lecturer_id' => 'varchar', 'active' => 'tinyint', 'date_created' => 'datetime',
        'course_question_tutor_id' => 'varchar', 'course_e_tutor_id' => 'varchar');
    /*this is a dictionary that map a field name with the label name that will be shown in a form*/
    static $labelArray = array('id' => '', 'session_id' => '', 'course_id' => '', 'course_manager_id' => '',
        'course_lecturer_id' => '', 'active' => '', 'date_created' => '', 'course_question_tutor_id' => '',
        'course_e_tutor_id' => '');
    /*associative array of fields that have default value*/
    static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
    static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

    static $relation = array('course' => array('course_id', 'ID')
    , 'course_manager' => array('course_manager_id', 'ID', array('ID', 'course_manager_id', 1))
    );
    static $tableAction = array('delete' => 'delete/course_manager', 'edit' => 'edit/course_manager');

    function __construct($array = array())
    {
        parent::__construct($array);
    }

    function getSession_idFormField($value = '')
    {
        $fk = null;//change the value of this variable to array('table'=>'session','display'=>'session_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

        if (is_null($fk)) {
            return $result = "<input type='hidden' value='$value' name='session_id' id='session_id' class='form-control' />
			";
        }
        if (is_array($fk)) {
            $result = "<div class='form-group'>
		<label for='session_id'>Session Id</label>";
            $option = $this->loadOption($fk, $value);
            //load the value from the given table given the name of the table to load and the display field
            $result .= "<select name='session_id' id='session_id' class='form-control'>
			$option
		</select>";
        }
        $result .= "</div>";
        return $result;

    }

    function getCourse_idFormField($value = '')
    {
        $fk = null;//change the value of this variable to array('table'=>'course','display'=>'course_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

        if (is_null($fk)) {
            return $result = "<input type='hidden' value='$value' name='course_id' id='course_id' class='form-control' />
			";
        }
        if (is_array($fk)) {
            $result = "<div class='form-group'>
		<label for='course_id'>Course Id</label>";
            $option = $this->loadOption($fk, $value);
            //load the value from the given table given the name of the table to load and the display field
            $result .= "<select name='course_id' id='course_id' class='form-control'>
			$option
		</select>";
        }
        $result .= "</div>";
        return $result;

    }

    function getCourse_manager_idFormField($value = '')
    {
        $fk = null;//change the value of this variable to array('table'=>'course_manager','display'=>'course_manager_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

        if (is_null($fk)) {
            return $result = "<input type='hidden' value='$value' name='course_manager_id' id='course_manager_id' class='form-control' />
			";
        }
        if (is_array($fk)) {
            $result = "<div class='form-group'>
		<label for='course_manager_id'>Course Manager Id</label>";
            $option = $this->loadOption($fk, $value);
            //load the value from the given table given the name of the table to load and the display field
            $result .= "<select name='course_manager_id' id='course_manager_id' class='form-control'>
			$option
		</select>";
        }
        $result .= "</div>";
        return $result;

    }

    function getCourse_lecturer_idFormField($value = '')
    {
        $fk = null;//change the value of this variable to array('table'=>'course_lecturer','display'=>'course_lecturer_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

        if (is_null($fk)) {
            return $result = "<input type='hidden' value='$value' name='course_lecturer_id' id='course_lecturer_id' class='form-control' />
			";
        }
        if (is_array($fk)) {
            $result = "<div class='form-group'>
		<label for='course_lecturer_id'>Course Lecturer Id</label>";
            $option = $this->loadOption($fk, $value);
            //load the value from the given table given the name of the table to load and the display field
            $result .= "<select name='course_lecturer_id' id='course_lecturer_id' class='form-control'>
			$option
		</select>";
        }
        $result .= "</div>";
        return $result;

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

    protected function getCourse()
    {
        $query = 'SELECT * FROM course WHERE id=?';
        if (!isset($this->array['id'])) {
            return null;
        }
        $id = $this->array['id'];
        $result = $this->db->query($query, array($id));
        $result = $result->getResultArray();
        if (empty($result)) {
            return false;
        }
        return new \App\Entities\Course($result[0]);
    }

    protected function getCourse_manager()
    {
        $query = 'SELECT * FROM course_manager WHERE id=?';
        if (!isset($this->array['id'])) {
            return null;
        }
        $id = $this->array['id'];
        $result = $this->db->query($query, array($id));
        $result = $result->getResultArray();
        if (empty($result)) {
            return false;
        }
        return new \App\Entities\Course_manager($result[0]);
    }

    public function APIList($filterList, $queryString, $start, $len, $orderBy): array
    {
        $departmentQuery = '';
        $departmentWhere = '';
        // the idea is to get the department id from the departmentalApiModel and use
        // it to filter the course manager
        if (isset($filterList['api_department'])) {
            $departmentID = $filterList['api_department'];
            $departmentWhere .= " f.id = '$departmentID' ";
            unset($filterList['api_department']);
        }
        $temp = getFilterQueryFromDict($filterList);
        $filterQuery = buildCustomWhereString($temp[0], $queryString, false);

        $filterValues = $temp[1];
        // $filterQuery .= ($filterQuery ? " and " : " where ") . " a.active = '1' ";

        if ($departmentWhere) {
            $filterQuery .= ($filterQuery ? " and " : " where ") . $departmentWhere;
        }

        if (isset($_GET['sortBy']) && $orderBy) {
            $filterQuery .= " order by $orderBy ";
        } else {
            $filterQuery .= " order by a.date_created desc, course_manager asc ";
        }

        if (isset($_GET['start']) && $len) {
            $start = $this->db->escapeString($start);
            $len = $this->db->escapeString($len);
            $filterQuery .= " limit $start, $len";
        }
        if (!$filterValues) {
            $filterValues = [];
        }
        $query = "SELECT SQL_CALC_FOUND_ROWS a.id,c.date as session,a.course_lecturer_id,b.code as course_code,b.title as course_title, 
		CASE 
        	WHEN a.course_manager_id IS NULL THEN 'N/A'
        	ELSE CONCAT(COALESCE(e.title, ''), ' ', e.lastname, ' ', e.firstname) 
    	END as course_manager,a.course_manager_id,a.course_e_tutor_id,a.course_question_tutor_id from course_manager a 
    	join courses b on b.id = a.course_id 
    	join sessions c on c.id = a.session_id 
    	left join users_new d on d.id = a.course_manager_id and d.user_type='staff'
    	left join staffs e on e.id = d.user_table_id 
    	left join department f on f.id = b.department_id
    	{$departmentQuery} $filterQuery";

        $query2 = "SELECT FOUND_ROWS() as totalCount";
        $res = $this->db->query($query, $filterValues);
        $res = $res->getResultArray();
        $res2 = $this->db->query($query2);
        $res2 = $res2->getResultArray();
        $res = $this->processList($res);
        return [$res, $res2];
    }

    private function processList($items)
    {
        EntityLoader::loadClass($this, 'users_new');
        for ($i = 0; $i < count($items); $i++) {
            $items[$i] = $this->loadExtras($items[$i]);
        }
        return $items;
    }

    public function loadExtras($item, $noManager = false)
    {
        if (isset($item['course_lecturer_id'])) {
            $lecturers = json_decode($item['course_lecturer_id'], true);
            $fullname = [];
            if ($lecturers) {
                foreach ($lecturers as $lecturer) {
                    $lecturer = $this->users_new->getRealUserInfo($lecturer, 'staffs', 'staff');
                    if ($lecturer) {
                        $fullname[] = $lecturer['title'] . ' ' . $lecturer['lastname'] . ' ' . $lecturer['firstname'];
                    }
                }
            }
            $item['course_lecturer'] = $fullname;
        }

        if ($noManager) {
            $manager = $this->users_new->getRealUserInfo($item['course_manager_id'], 'staffs', 'staff');
            $item['course_manager'] = $manager ? $manager['title'] . ' ' . $manager['lastname'] . ' ' . $manager['firstname'] : '';
        }

        if ($item['course_e_tutor_id']) {
            $eTutor = $this->users_new->getRealUserInfo($item['course_e_tutor_id'], 'staffs', 'staff');
            $item['course_e_tutor'] = $eTutor ? $eTutor['title'] . ' ' . $eTutor['lastname'] . ' ' . $eTutor['firstname'] : '';
        }

        if ($item['course_question_tutor_id']) {
            $questionTutor = $this->users_new->getRealUserInfo($item['course_question_tutor_id'], 'staffs', 'staff');
            $item['course_question_tutor'] = $questionTutor ? $questionTutor['title'] . ' ' . $questionTutor['lastname'] . ' ' . $questionTutor['firstname'] : '';
        }

        $item['course_lecturer_id'] = ($item['course_lecturer_id'] != '') ? json_decode($item['course_lecturer_id'], true) : [];

        return $item;
    }

    public function getCourseManagerByCourseId($course, $session)
    {
        $query = "SELECT * FROM course_manager WHERE course_id=? and session_id=? and active = '1' order by date_created desc limit 1";
        $result = $this->query($query, [$course, $session]);
        if (!$result) {
            return null;
        }
        return $result[0];
    }


}
