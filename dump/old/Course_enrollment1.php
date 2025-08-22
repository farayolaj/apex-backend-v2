<?php
		require_once('application/models/Crud.php');
		/**
		* This class  is automatically generated based on the structure of the table. And it represent the model of the course_enrollment1 table.
		*/
		class Course_enrollment1 extends Crud
		{
protected static $tablename='Course_enrollment1';
/* this array contains the field that can be null*/
static $nullArray=array('ca_score' ,'exam_score' ,'total_score' );
static $compositePrimaryKey=array();
static $uploadDependency = array();
/*this array contains the fields that are unique*/
static $uniqueArray=array();
/*this is an associative array containing the fieldname and the type of the field*/
static $typeArray = array('student_id'=>'int','course_id'=>'int','course_unit'=>'int','course_status'=>'varchar','semester'=>'int','session_id'=>'int','student_level'=>'int','ca_score'=>'int','exam_score'=>'int','total_score'=>'int','is_approved'=>'tinyint','date_last_update'=>'datetime','date_created'=>'datetime');
/*this is a dictionary that map a field name with the label name that will be shown in a form*/
static $labelArray=array('id'=>'','student_id'=>'','course_id'=>'','course_unit'=>'','course_status'=>'','semester'=>'','session_id'=>'','student_level'=>'','ca_score'=>'','exam_score'=>'','total_score'=>'','is_approved'=>'','date_last_update'=>'','date_created'=>'');
/*associative array of fields that have default value*/
static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.
		
static $relation=array('course'=>array( 'course_id', 'ID')
);
static $tableAction=array('delete'=>'delete/course_enrollment1','edit'=>'edit/course_enrollment1');
function __construct($array=array())
{
	parent::__construct($array);
}
	 function getStudent_idFormField($value=''){
	$fk=null;//change the value of this variable to array('table'=>'student','display'=>'student_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

	if (is_null($fk)) {
		return $result="<input type='hidden' value='$value' name='student_id' id='student_id' class='form-control' />
			";
	}
	if (is_array($fk)) {
		$result ="<div class='form-group'>
		<label for='student_id'>Student Id</label>";
		$option = $this->loadOption($fk,$value);
		//load the value from the given table given the name of the table to load and the display field
		$result.="<select name='student_id' id='student_id' class='form-control'>
			$option
		</select>";
	}
	$result.="</div>";
	return  $result;

}
	 function getCourse_idFormField($value=''){
	$fk=null;//change the value of this variable to array('table'=>'course','display'=>'course_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

	if (is_null($fk)) {
		return $result="<input type='hidden' value='$value' name='course_id' id='course_id' class='form-control' />
			";
	}
	if (is_array($fk)) {
		$result ="<div class='form-group'>
		<label for='course_id'>Course Id</label>";
		$option = $this->loadOption($fk,$value);
		//load the value from the given table given the name of the table to load and the display field
		$result.="<select name='course_id' id='course_id' class='form-control'>
			$option
		</select>";
	}
	$result.="</div>";
	return  $result;

}
function getCourse_unitFormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_unit' >Course Unit</label><input type='number' name='course_unit' id='course_unit' value='$value' class='form-control' required />
</div> ";

}
function getCourse_statusFormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_status' >Course Status</label>
		<input type='text' name='course_status' id='course_status' value='$value' class='form-control' required />
</div> ";

}
function getSemesterFormField($value=''){
	
	return "<div class='form-group'>
	<label for='semester' >Semester</label><input type='number' name='semester' id='semester' value='$value' class='form-control' required />
</div> ";

}
	 function getSession_idFormField($value=''){
	$fk=null;//change the value of this variable to array('table'=>'session','display'=>'session_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

	if (is_null($fk)) {
		return $result="<input type='hidden' value='$value' name='session_id' id='session_id' class='form-control' />
			";
	}
	if (is_array($fk)) {
		$result ="<div class='form-group'>
		<label for='session_id'>Session Id</label>";
		$option = $this->loadOption($fk,$value);
		//load the value from the given table given the name of the table to load and the display field
		$result.="<select name='session_id' id='session_id' class='form-control'>
			$option
		</select>";
	}
	$result.="</div>";
	return  $result;

}
function getStudent_levelFormField($value=''){
	
	return "<div class='form-group'>
	<label for='student_level' >Student Level</label><input type='number' name='student_level' id='student_level' value='$value' class='form-control' required />
</div> ";

}
function getCa_scoreFormField($value=''){
	
	return "<div class='form-group'>
	<label for='ca_score' >Ca Score</label><input type='number' name='ca_score' id='ca_score' value='$value' class='form-control'  />
</div> ";

}
function getExam_scoreFormField($value=''){
	
	return "<div class='form-group'>
	<label for='exam_score' >Exam Score</label><input type='number' name='exam_score' id='exam_score' value='$value' class='form-control'  />
</div> ";

}
function getTotal_scoreFormField($value=''){
	
	return "<div class='form-group'>
	<label for='total_score' >Total Score</label><input type='number' name='total_score' id='total_score' value='$value' class='form-control'  />
</div> ";

}
function getIs_approvedFormField($value=''){
	
	return "<div class='form-group'>
	<label class='form-checkbox'>Is Approved</label>
	<select class='form-control' id='is_approved' name='is_approved' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

}
function getDate_last_updateFormField($value=''){
	
	return " ";

}
function getDate_createdFormField($value=''){
	
	return " ";

}


		
protected function getCourse(){
	$query ='SELECT * FROM course WHERE id=?';
	if (!isset($this->array['ID'])) {
		return null;
	}
	$id = $this->array['ID'];
	$result = $this->db->query($query,array($id));
	$result =$result->result_array();
	if (empty($result)) {
		return false;
	}
	include_once('Course.php');
	$resultObject = new Course($result[0]);
	return $resultObject;
}
		}
		?>