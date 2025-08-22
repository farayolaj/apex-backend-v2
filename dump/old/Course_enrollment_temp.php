<?php
		require_once('application/models/Crud.php');
		/**
		* This class  is automatically generated based on the structure of the table. And it represent the model of the course_enrollment_temp table.
		*/
		class Course_enrollment_temp extends Crud
		{
protected static $tablename='Course_enrollment_temp';
/* this array contains the field that can be null*/
static $nullArray=array();
static $compositePrimaryKey=array();
static $uploadDependency = array();
/*this array contains the fields that are unique*/
static $uniqueArray=array();
/*this is an associative array containing the fieldname and the type of the field*/
static $typeArray = array('matric_number'=>'varchar','course_code'=>'varchar','course_unit'=>'int','course_status'=>'varchar','session'=>'varchar','course_semester'=>'varchar','level'=>'varchar');
/*this is a dictionary that map a field name with the label name that will be shown in a form*/
static $labelArray=array('id'=>'','matric_number'=>'','course_code'=>'','course_unit'=>'','course_status'=>'','session'=>'','course_semester'=>'','level'=>'');
/*associative array of fields that have default value*/
static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.
		
static $relation=array();
static $tableAction=array('delete'=>'delete/course_enrollment_temp','edit'=>'edit/course_enrollment_temp');
function __construct($array=array())
{
	parent::__construct($array);
}
function getMatric_numberFormField($value=''){
	
	return "<div class='form-group'>
	<label for='matric_number' >Matric Number</label>
		<input type='text' name='matric_number' id='matric_number' value='$value' class='form-control' required />
</div> ";

}
function getCourse_codeFormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_code' >Course Code</label>
		<input type='text' name='course_code' id='course_code' value='$value' class='form-control' required />
</div> ";

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
function getSessionFormField($value=''){
	
	return "<div class='form-group'>
	<label for='session' >Session</label>
		<input type='text' name='session' id='session' value='$value' class='form-control' required />
</div> ";

}
function getCourse_semesterFormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_semester' >Course Semester</label>
		<input type='text' name='course_semester' id='course_semester' value='$value' class='form-control' required />
</div> ";

}
function getLevelFormField($value=''){
	
	return "<div class='form-group'>
	<label for='level' >Level</label>
		<input type='text' name='level' id='level' value='$value' class='form-control' required />
</div> ";

}


		}
		?>