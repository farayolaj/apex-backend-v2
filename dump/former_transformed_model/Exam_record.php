<?php

namespace App\Entities;

use App\Models\Crud;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the exam_record table.
 */
class Exam_record extends Crud
{
    protected static $tablename = 'Exam_record';
    /* this array contains the field that can be null*/
    static $nullArray = array();
    static $compositePrimaryKey = array();
    static $uploadDependency = array();
    /*this array contains the fields that are unique*/
    static $uniqueArray = array();
    /*this is an associative array containing the fieldname and the type of the field*/
    static $typeArray = array('student_id' => 'int', 'session_id' => 'int', 'student_level' => 'int', 'tcu' => 'int', 'twgp' => 'int', 'cgpa_rule' => 'text', 'gpa' => 'float', 'cgpa' => 'float', 'active' => 'tinyint', 'date_created' => 'datetime');
    /*this is a dictionary that map a field name with the label name that will be shown in a form*/
    static $labelArray = array('id' => '', 'student_id' => '', 'session_id' => '', 'student_level' => '', 'tcu' => '', 'twgp' => '', 'cgpa_rule' => '', 'gpa' => '', 'cgpa' => '', 'active' => '', 'date_created' => '');
    /*associative array of fields that have default value*/
    static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
    static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

    static $relation = array();
    static $tableAction = array('delete' => 'delete/exam_record', 'edit' => 'edit/exam_record');

    function __construct($array = array())
    {
        parent::__construct($array);
    }

    function getStudent_idFormField($value = '')
    {
        $fk = null;//change the value of this variable to array('table'=>'student','display'=>'student_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

        if (is_null($fk)) {
            return $result = "<input type='hidden' value='$value' name='student_id' id='student_id' class='form-control' />
			";
        }
        if (is_array($fk)) {
            $result = "<div class='form-group'>
		<label for='student_id'>Student Id</label>";
            $option = $this->loadOption($fk, $value);
            //load the value from the given table given the name of the table to load and the display field
            $result .= "<select name='student_id' id='student_id' class='form-control'>
			$option
		</select>";
        }
        $result .= "</div>";
        return $result;

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

    function getStudent_levelFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='student_level' >Student Level</label><input type='number' name='student_level' id='student_level' value='$value' class='form-control' required />
</div> ";

    }

    function getTcuFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='tcu' >Tcu</label><input type='number' name='tcu' id='tcu' value='$value' class='form-control' required />
</div> ";

    }

    function getTwgpFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='twgp' >Twgp</label><input type='number' name='twgp' id='twgp' value='$value' class='form-control' required />
</div> ";

    }

    function getCgpa_ruleFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='cgpa_rule' >Cgpa Rule</label>
<textarea id='cgpa_rule' name='cgpa_rule' class='form-control' required>$value</textarea>
</div> ";

    }

    function getGpaFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='gpa' >Gpa</label>
		<input type='text' name='gpa' id='gpa' value='$value' class='form-control' required />
</div> ";

    }

    function getCgpaFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='cgpa' >Cgpa</label>
		<input type='text' name='cgpa' id='cgpa' value='$value' class='form-control' required />
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

    public function deleteRecord($student, $session, $studentLevel)
    {
        $query = "DELETE from exam_record where student_id = ? and session_id = ? and student_level = ? and gpa is null";
        $result = $this->query($query, [$student, $session, $studentLevel]);
        if (!$result) {
            return false;
        }
        return true;
    }

    /**
     * @param  [type] $student [description]
     * @return [type]          [description]
     * @deprecated - There is now a correct similar method in student entity
     */
    public function getExamRecordList($student)
    {
        $query = "SELECT exam_record.session_id as exam_session, exam_record.student_level, sessions.date as exam_session_date from exam_record join sessions on sessions.id = exam_record.session_id where exam_record.student_id = ? order by sessions.date asc";
        $result = $this->query($query, [$student]);
        if (!$result) {
            return false;
        }
        return $result;
    }


}
