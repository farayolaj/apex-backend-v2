<?php
		require_once('application/models/Crud.php');
		/**
		* This class  is automatically generated based on the structure of the table. And it represent the model of the accommodation_coupons table.
		*/
		class Accommodation_coupons extends Crud
		{
protected static $tablename='Accommodation_coupons';
/* this array contains the field that can be null*/
static $nullArray=array();
static $compositePrimaryKey=array();
static $uploadDependency = array();
/*this array contains the fields that are unique*/
static $uniqueArray=array();
/*this is an associative array containing the fieldname and the type of the field*/
static $typeArray = array('hall_detail_id'=>'int','code'=>'varchar','owner_id'=>'int','session'=>'int','status'=>'tinyint','active'=>'tinyint','date_created'=>'datetime','date_updated'=>'datetime');
/*this is a dictionary that map a field name with the label name that will be shown in a form*/
static $labelArray=array('id'=>'','hall_detail_id'=>'','code'=>'','owner_id'=>'','session'=>'','status'=>'','active'=>'','date_created'=>'','date_updated'=>'');
/*associative array of fields that have default value*/
static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.
		
static $relation=array();
static $tableAction=array('enable'=>'getEnabled','delete'=>'delete/accommodation_coupons','edit'=>'edit/accommodation_coupons');
function __construct($array=array())
{
	parent::__construct($array);
}
	 function getHall_detail_idFormField($value=''){
	$fk=null;//change the value of this variable to array('table'=>'hall_detail','display'=>'hall_detail_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

	if (is_null($fk)) {
		return $result="<input type='hidden' value='$value' name='hall_detail_id' id='hall_detail_id' class='form-control' />
			";
	}
	if (is_array($fk)) {
		$result ="<div class='form-group'>
		<label for='hall_detail_id'>Hall Detail Id</label>";
		$option = $this->loadOption($fk,$value);
		//load the value from the given table given the name of the table to load and the display field
		$result.="<select name='hall_detail_id' id='hall_detail_id' class='form-control'>
			$option
		</select>";
	}
	$result.="</div>";
	return  $result;

}
function getCodeFormField($value=''){
	
	return "<div class='form-group'>
	<label for='code' >Code</label>
		<input type='text' name='code' id='code' value='$value' class='form-control' required />
</div> ";

}
	 function getOwner_idFormField($value=''){
	$fk=null;//change the value of this variable to array('table'=>'owner','display'=>'owner_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

	if (is_null($fk)) {
		return $result="<input type='hidden' value='$value' name='owner_id' id='owner_id' class='form-control' />
			";
	}
	if (is_array($fk)) {
		$result ="<div class='form-group'>
		<label for='owner_id'>Owner Id</label>";
		$option = $this->loadOption($fk,$value);
		//load the value from the given table given the name of the table to load and the display field
		$result.="<select name='owner_id' id='owner_id' class='form-control'>
			$option
		</select>";
	}
	$result.="</div>";
	return  $result;

}
function getSessionFormField($value=''){
	
	return "<div class='form-group'>
	<label for='session' >Session</label><input type='number' name='session' id='session' value='$value' class='form-control' required />
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
function getDate_updatedFormField($value=''){
	
	return " ";

}


		}
		?>