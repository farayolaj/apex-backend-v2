<?php

namespace App\Entities;

use App\Models\Crud;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the student_educational_background table.
 */
class Student_educational_background extends Crud
{
    protected static $tablename = 'Student_educational_background';
    /* this array contains the field that can be null*/
    static $nullArray = array();
    static $compositePrimaryKey = array();
    static $uploadDependency = array();
    /*this array contains the fields that are unique*/
    static $uniqueArray = array();
    /*this is an associative array containing the fieldname and the type of the field*/
    static $typeArray = array('student_id' => 'int', 'institutions_attended' => 'text', 'sitting' => 'smallint', 'exam_results' => 'text', 'jamb_result' => 'text');
    /*this is a dictionary that map a field name with the label name that will be shown in a form*/
    static $labelArray = array('id' => '', 'student_id' => '', 'institutions_attended' => '', 'sitting' => '', 'exam_results' => '', 'jamb_result' => '');
    /*associative array of fields that have default value*/
    static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
    static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

    static $relation = array();
    static $tableAction = array('delete' => 'delete/student_educational_background', 'edit' => 'edit/student_educational_background');

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

    function getInstitutions_attendedFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='institutions_attended' >Institutions Attended</label>
<textarea id='institutions_attended' name='institutions_attended' class='form-control' required>$value</textarea>
</div> ";

    }

    function getSittingFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='sitting' >Sitting</label>
</div> ";

    }

    function getExam_resultsFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='exam_results' >Exam Results</label>
<textarea id='exam_results' name='exam_results' class='form-control' required>$value</textarea>
</div> ";

    }

    function getJamb_resultFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='jamb_result' >Jamb Result</label>
<textarea id='jamb_result' name='jamb_result' class='form-control' required>$value</textarea>
</div> ";

    }


}
		