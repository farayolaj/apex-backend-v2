<?php
namespace App\Entities;

use App\Models\Crud;
use App\Libraries\EntityLoader;

/**
		* This class  is automatically generated based on the structure of the table. And it represent the model of the admission_programme_requirements table.
		*/
		class Admission_programme_requirements extends Crud
		{
protected static $tablename='Admission_programme_requirements';
/* this array contains the field that can be null*/
static $nullArray=array('date_created','olevel_requirements','alevel_requirements','other_requirements');
static $compositePrimaryKey=array();
static $uploadDependency = array();
/*this array contains the fields that are unique*/
static $uniqueArray=array();
/*this is an associative array containing the fieldname and the type of the field*/
static $typeArray = array('programme_id'=>'int','olevel_requirements'=>'text','alevel_requirements'=>'text','other_requirements'=>'text','session'=>'int','active'=>'tinyint','date_created'=>'datetime','admission_id' => 'int');
/*this is a dictionary that map a field name with the label name that will be shown in a form*/
static $labelArray=array('id'=>'','programme_id'=>'','olevel_requirements'=>'','alevel_requirements'=>'','other_requirements'=>'','session'=>'','active'=>'','date_created'=>'','admission_id' => '');
/*associative array of fields that have default value*/
static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.
		
static $relation=array('programme'=>array( 'programme_id', 'ID')
);
static $tableAction=array('delete'=>'delete/admission_programme_requirements','edit'=>'edit/admission_programme_requirements');
function __construct($array=array())
{
	parent::__construct($array);
}
	 function getProgramme_idFormField($value=''){
	$fk=null;//change the value of this variable to array('table'=>'programme','display'=>'programme_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

	if (is_null($fk)) {
		return $result="<input type='hidden' value='$value' name='programme_id' id='programme_id' class='form-control' />
			";
	}
	if (is_array($fk)) {
		$result ="<div class='form-group'>
		<label for='programme_id'>Programme Id</label>";
		$option = $this->loadOption($fk,$value);
		//load the value from the given table given the name of the table to load and the display field
		$result.="<select name='programme_id' id='programme_id' class='form-control'>
			$option
		</select>";
	}
	$result.="</div>";
	return  $result;

}
function getOlevel_requirementsFormField($value=''){
	
	return "<div class='form-group'>
	<label for='olevel_requirements' >Olevel Requirements</label>
<textarea id='olevel_requirements' name='olevel_requirements' class='form-control' required>$value</textarea>
</div> ";

}
function getAlevel_requirementsFormField($value=''){
	
	return "<div class='form-group'>
	<label for='alevel_requirements' >Alevel Requirements</label>
<textarea id='alevel_requirements' name='alevel_requirements' class='form-control' required>$value</textarea>
</div> ";

}
function getOther_requirementsFormField($value=''){
	
	return "<div class='form-group'>
	<label for='other_requirements' >Other Requirements</label>
<textarea id='other_requirements' name='other_requirements' class='form-control' required>$value</textarea>
</div> ";

}
function getSessionFormField($value=''){
	
	return "<div class='form-group'>
	<label for='session' >Session</label><input type='number' name='session' id='session' value='$value' class='form-control' required />
</div> ";

}
function getAdmission_idFormField($value=''){
	
	return "<div class='form-group'>
	<label for='admission_id' >Admission</label><input type='number' name='admission_id' id='admission_id' value='$value' class='form-control' required />
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
		
protected function getProgramme(){
	$query ='SELECT * FROM programme WHERE id=?';
	if (!isset($this->array['id'])) {
		return null;
	}
	$id = $this->array['id'];
	$result = $this->db->query($query,array($id));
	$result =$result->getResultArray();
	if (empty($result)) {
		return false;
	}
	return new \App\Entities\Programme($result[0]);
}

public function transformUpdateData($param){
	$array = [
		'status' => 'active'
	];
	$result = [];
	$keys = array_keys($array);
	$valueKeys = array_keys($param);
	$temp = array_intersect($keys, $valueKeys);
	foreach ($temp as $value) {
		$result[$array[$value]]= $param[$value];
	}
	return $result;
}



}