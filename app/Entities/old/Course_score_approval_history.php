<?php
		require_once('application/models/Crud.php');
		/**
		* This class  is automatically generated based on the structure of the table. And it represent the model of the course_score_approval_history table.
		*/
		class Course_score_approval_history extends Crud
		{
protected static $tablename='Course_score_approval_history';
/* this array contains the field that can be null*/
static $nullArray=array('semester' ,'date_created' );
static $compositePrimaryKey=array();
static $uploadDependency = array();
/*this array contains the fields that are unique*/
static $uniqueArray=array();
/*this is an associative array containing the fieldname and the type of the field*/
static $typeArray = array('session'=>'varchar','course_code'=>'varchar','semester'=>'varchar','status'=>'tinyint','date_created'=>'timestamp');
/*this is a dictionary that map a field name with the label name that will be shown in a form*/
static $labelArray=array('id'=>'','session'=>'','course_code'=>'','semester'=>'','status'=>'','date_created'=>'');
/*associative array of fields that have default value*/
static $defaultArray = array('date_created'=>'current_timestamp()');
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.
		
static $relation=array();
static $tableAction=array('enable'=>'getEnabled','delete'=>'delete/course_score_approval_history','edit'=>'edit/course_score_approval_history');
function __construct($array=array())
{
	parent::__construct($array);
}
function getSessionFormField($value=''){
	
	return "<div class='form-group'>
	<label for='session' >Session</label>
		<input type='text' name='session' id='session' value='$value' class='form-control' required />
</div> ";

}
function getCourse_codeFormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_code' >Course Code</label>
		<input type='text' name='course_code' id='course_code' value='$value' class='form-control' required />
</div> ";

}
function getSemesterFormField($value=''){
	
	return "<div class='form-group'>
	<label for='semester' >Semester</label>
		<input type='text' name='semester' id='semester' value='$value' class='form-control'  />
</div> ";

}
function getStatusFormField($value=''){
	
	return "<div class='form-group'>
	<label class='form-checkbox'>Status</label>
	<select class='form-control' id='status' name='status' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

}
function getDate_createdFormField($value=''){
	
	return " ";

}


		}
		?>