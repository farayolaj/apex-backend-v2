<?php
namespace App\Entities;

use App\Models\Crud;
use App\Libraries\EntityLoader;

/**
		* This class  is automatically generated based on the structure of the table. And it represent the model of the course_registration_log table.
		*/
		class Course_registration_log extends Crud
		{
protected static $tablename='Course_registration_log';
/* this array contains the field that can be null*/
static $nullArray=array('date_created' );
static $compositePrimaryKey=array();
static $uploadDependency = array();
/*this array contains the fields that are unique*/
static $uniqueArray=array();
/*this is an associative array containing the fieldname and the type of the field*/
static $typeArray = array('username'=>'varchar','operation'=>'text','date_created'=>'timestamp','student_id'=>'int','course_id'=>'int','level'=>'int','session_id'=>'int','course_unit'=>'int','course_status'=>'varchar');
/*this is a dictionary that map a field name with the label name that will be shown in a form*/
static $labelArray=array('id'=>'','username'=>'','operation'=>'','date_created'=>'','student_id'=>'','course_id'=>'','level'=>'','session_id'=>'','course_unit'=>'','course_status'=>'');
/*associative array of fields that have default value*/
static $defaultArray = array('date_created'=>'current_timestamp()');
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.
		
static $relation=array('course'=>array( 'course_id', 'ID')
);
static $tableAction=array('delete'=>'delete/course_registration_log','edit'=>'edit/course_registration_log');
function __construct($array=array())
{
	parent::__construct($array);
}
function getUsernameFormField($value=''){
	
	return "<div class='form-group'>
	<label for='username' >Username</label>
		<input type='text' name='username' id='username' value='$value' class='form-control' required />
</div> ";

}
function getOperationFormField($value=''){
	
	return "<div class='form-group'>
	<label for='operation' >Operation</label>
<textarea id='operation' name='operation' class='form-control' required>$value</textarea>
</div> ";

}
function getDate_createdFormField($value=''){
	
	return " ";

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
function getLevelFormField($value=''){
	
	return "<div class='form-group'>
	<label for='level' >Level</label><input type='number' name='level' id='level' value='$value' class='form-control' required />
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


		
protected function getCourse(){
	$query ='SELECT * FROM course WHERE id=?';
	if (!isset($this->array['id'])) {
		return null;
	}
	$id = $this->array['id'];
	$result = $this->db->query($query,array($id));
	$result =$result->getResultArray();
	if (empty($result)) {
		return false;
	}
	return new \App\Entities\Course($result[0]);
}
		}
		