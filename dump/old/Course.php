<?php
		require_once('application/models/Crud.php');
		/**
		* This class  is automatically generated based on the structure of the table. And it represent the model of the course table.
		*/
		class Course extends Crud
		{
protected static $tablename='Course';
/* this array contains the field that can be null*/
static $nullArray=array();
static $compositePrimaryKey=array();
static $uploadDependency = array();
/*this array contains the fields that are unique*/
static $uniqueArray=array();
/*this is an associative array containing the fieldname and the type of the field*/
static $typeArray = array('code'=>'varchar','title'=>'varchar','description'=>'text','unit'=>'int','status'=>'varchar','pass_score'=>'int','programme'=>'text','pre_select'=>'tinyint','level'=>'int','session'=>'int','active'=>'tinyint','date_created'=>'datetime');
/*this is a dictionary that map a field name with the label name that will be shown in a form*/
static $labelArray=array('id'=>'','code'=>'','title'=>'','description'=>'','unit'=>'','status'=>'','pass_score'=>'','programme'=>'','pre_select'=>'','level'=>'','session'=>'','active'=>'','date_created'=>'');
/*associative array of fields that have default value*/
static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.
		
static $relation=array('approved_courses'=>array(array( 'ID', 'course_id', 1))
,'course_enrollment'=>array(array( 'ID', 'course_id', 1))
,'course_enrollment1'=>array(array( 'ID', 'course_id', 1))
,'course_enrollment2'=>array(array( 'ID', 'course_id', 1))
,'course_enrollment_archive'=>array(array( 'ID', 'course_id', 1))
,'course_manager'=>array(array( 'ID', 'course_id', 1))
,'course_mapping'=>array(array( 'ID', 'course_id', 1))
,'course_registration_log'=>array(array( 'ID', 'course_id', 1))
);
static $tableAction=array('enable'=>'getEnabled','delete'=>'delete/course','edit'=>'edit/course');
function __construct($array=array())
{
	parent::__construct($array);
}
function getCodeFormField($value=''){
	
	return "<div class='form-group'>
	<label for='code' >Code</label>
		<input type='text' name='code' id='code' value='$value' class='form-control' required />
</div> ";

}
function getTitleFormField($value=''){
	
	return "<div class='form-group'>
	<label for='title' >Title</label>
		<input type='text' name='title' id='title' value='$value' class='form-control' required />
</div> ";

}
function getDescriptionFormField($value=''){
	
	return "<div class='form-group'>
	<label for='description' >Description</label>
<textarea id='description' name='description' class='form-control' required>$value</textarea>
</div> ";

}
function getUnitFormField($value=''){
	
	return "<div class='form-group'>
	<label for='unit' >Unit</label><input type='number' name='unit' id='unit' value='$value' class='form-control' required />
</div> ";

}
function getStatusFormField($value=''){
	
	return "<div class='form-group'>
	<label for='status' >Status</label>
		<input type='text' name='status' id='status' value='$value' class='form-control' required />
</div> ";

}
function getPass_scoreFormField($value=''){
	
	return "<div class='form-group'>
	<label for='pass_score' >Pass Score</label><input type='number' name='pass_score' id='pass_score' value='$value' class='form-control' required />
</div> ";

}
function getProgrammeFormField($value=''){
	
	return "<div class='form-group'>
	<label for='programme' >Programme</label>
<textarea id='programme' name='programme' class='form-control' required>$value</textarea>
</div> ";

}
function getPre_selectFormField($value=''){
	
	return "<div class='form-group'>
	<label class='form-checkbox'>Pre Select</label>
	<select class='form-control' id='pre_select' name='pre_select' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

}
function getLevelFormField($value=''){
	
	return "<div class='form-group'>
	<label for='level' >Level</label><input type='number' name='level' id='level' value='$value' class='form-control' required />
</div> ";

}
function getSessionFormField($value=''){
	
	return "<div class='form-group'>
	<label for='session' >Session</label><input type='number' name='session' id='session' value='$value' class='form-control' required />
</div> ";

}
function getActiveFormField($value=''){
	
	return "<div class='form-group'>
	<label class='form-checkbox'>Active</label>
	<select class='form-control' id='active' name='active' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

}
function getDate_createdFormField($value=''){
	
	return " ";

}


		
protected function getApproved_courses(){
	$query ='SELECT * FROM approved_courses WHERE course_id=?';
	$id = $this->array['ID'];
	$result = $this->db->query($query,array($id));
	$result =$result->result_array();
	if (empty($result)) {
		return false;
	}
	include_once('Approved_courses.php');
	$resultobjects = array();
	foreach ($result as  $value) {
		$resultObjects[] = new Approved_courses($value);
	}

	return $resultObjects;
}
		
protected function getCourse_enrollment(){
	$query ='SELECT * FROM course_enrollment WHERE course_id=?';
	$id = $this->array['ID'];
	$result = $this->db->query($query,array($id));
	$result =$result->result_array();
	if (empty($result)) {
		return false;
	}
	include_once('Course_enrollment.php');
	$resultobjects = array();
	foreach ($result as  $value) {
		$resultObjects[] = new Course_enrollment($value);
	}

	return $resultObjects;
}
		
protected function getCourse_enrollment1(){
	$query ='SELECT * FROM course_enrollment1 WHERE course_id=?';
	$id = $this->array['ID'];
	$result = $this->db->query($query,array($id));
	$result =$result->result_array();
	if (empty($result)) {
		return false;
	}
	include_once('Course_enrollment1.php');
	$resultobjects = array();
	foreach ($result as  $value) {
		$resultObjects[] = new Course_enrollment1($value);
	}

	return $resultObjects;
}
		
protected function getCourse_enrollment2(){
	$query ='SELECT * FROM course_enrollment2 WHERE course_id=?';
	$id = $this->array['ID'];
	$result = $this->db->query($query,array($id));
	$result =$result->result_array();
	if (empty($result)) {
		return false;
	}
	include_once('Course_enrollment2.php');
	$resultobjects = array();
	foreach ($result as  $value) {
		$resultObjects[] = new Course_enrollment2($value);
	}

	return $resultObjects;
}
		
protected function getCourse_enrollment_archive(){
	$query ='SELECT * FROM course_enrollment_archive WHERE course_id=?';
	$id = $this->array['ID'];
	$result = $this->db->query($query,array($id));
	$result =$result->result_array();
	if (empty($result)) {
		return false;
	}
	include_once('Course_enrollment_archive.php');
	$resultobjects = array();
	foreach ($result as  $value) {
		$resultObjects[] = new Course_enrollment_archive($value);
	}

	return $resultObjects;
}
		
protected function getCourse_manager(){
	$query ='SELECT * FROM course_manager WHERE course_id=?';
	$id = $this->array['ID'];
	$result = $this->db->query($query,array($id));
	$result =$result->result_array();
	if (empty($result)) {
		return false;
	}
	include_once('Course_manager.php');
	$resultobjects = array();
	foreach ($result as  $value) {
		$resultObjects[] = new Course_manager($value);
	}

	return $resultObjects;
}
		
protected function getCourse_mapping(){
	$query ='SELECT * FROM course_mapping WHERE course_id=?';
	$id = $this->array['ID'];
	$result = $this->db->query($query,array($id));
	$result =$result->result_array();
	if (empty($result)) {
		return false;
	}
	include_once('Course_mapping.php');
	$resultobjects = array();
	foreach ($result as  $value) {
		$resultObjects[] = new Course_mapping($value);
	}

	return $resultObjects;
}
		
protected function getCourse_registration_log(){
	$query ='SELECT * FROM course_registration_log WHERE course_id=?';
	$id = $this->array['ID'];
	$result = $this->db->query($query,array($id));
	$result =$result->result_array();
	if (empty($result)) {
		return false;
	}
	include_once('Course_registration_log.php');
	$resultobjects = array();
	foreach ($result as  $value) {
		$resultObjects[] = new Course_registration_log($value);
	}

	return $resultObjects;
}
		}
		?>