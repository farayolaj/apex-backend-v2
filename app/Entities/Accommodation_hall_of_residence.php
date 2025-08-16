<?php
namespace App\Entities;

use App\Models\Crud;

/**
		* This class  is automatically generated based on the structure of the table. And it represent the model of the accommodation_hall_of_residence table.
		*/
		class Accommodation_hall_of_residence extends Crud
		{
protected static $tablename='Accommodation_hall_of_residence';
/* this array contains the field that can be null*/
static $nullArray=array();
static $compositePrimaryKey=array();
static $uploadDependency = array();
/*this array contains the fields that are unique*/
static $uniqueArray=array();
/*this is an associative array containing the fieldname and the type of the field*/
static $typeArray = array('name'=>'varchar','address'=>'varchar','overview'=>'text','type'=>'varchar','location'=>'varchar','longtitude'=>'varchar','latitude'=>'varchar','total_bed_spaces'=>'int','total_reserved_spaces'=>'int','total_available_public_spaces'=>'int','amount'=>'int','service_charge'=>'int','service_type_id'=>'varchar','prerequisite_fee'=>'int','is_displayed'=>'tinyint','active'=>'tinyint','date_created'=>'datetime');
/*this is a dictionary that map a field name with the label name that will be shown in a form*/
static $labelArray=array('id'=>'','name'=>'','address'=>'','overview'=>'','type'=>'','location'=>'','longtitude'=>'','latitude'=>'','total_bed_spaces'=>'','total_reserved_spaces'=>'','total_available_public_spaces'=>'','amount'=>'','service_charge'=>'','service_type_id'=>'','prerequisite_fee'=>'','is_displayed'=>'','active'=>'','date_created'=>'');
/*associative array of fields that have default value*/
static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.
		
static $relation=array();
static $tableAction=array('delete'=>'delete/accommodation_hall_of_residence','edit'=>'edit/accommodation_hall_of_residence');
function __construct($array=array())
{
	parent::__construct($array);
}
function getNameFormField($value=''){
	
	return "<div class='form-group'>
	<label for='name' >Name</label>
		<input type='text' name='name' id='name' value='$value' class='form-control' required />
</div> ";

}
function getAddressFormField($value=''){
	
	return "<div class='form-group'>
	<label for='address' >Address</label>
		<input type='text' name='address' id='address' value='$value' class='form-control' required />
</div> ";

}
function getOverviewFormField($value=''){
	
	return "<div class='form-group'>
	<label for='overview' >Overview</label>
<textarea id='overview' name='overview' class='form-control' required>$value</textarea>
</div> ";

}
function getTypeFormField($value=''){
	
	return "<div class='form-group'>
	<label for='type' >Type</label>
		<input type='text' name='type' id='type' value='$value' class='form-control' required />
</div> ";

}
function getLocationFormField($value=''){
	
	return "<div class='form-group'>
	<label for='location' >Location</label>
		<input type='text' name='location' id='location' value='$value' class='form-control' required />
</div> ";

}
function getLongtitudeFormField($value=''){
	
	return "<div class='form-group'>
	<label for='longtitude' >Longtitude</label>
		<input type='text' name='longtitude' id='longtitude' value='$value' class='form-control' required />
</div> ";

}
function getLatitudeFormField($value=''){
	
	return "<div class='form-group'>
	<label for='latitude' >Latitude</label>
		<input type='text' name='latitude' id='latitude' value='$value' class='form-control' required />
</div> ";

}
function getTotal_bed_spacesFormField($value=''){
	
	return "<div class='form-group'>
	<label for='total_bed_spaces' >Total Bed Spaces</label><input type='number' name='total_bed_spaces' id='total_bed_spaces' value='$value' class='form-control' required />
</div> ";

}
function getTotal_reserved_spacesFormField($value=''){
	
	return "<div class='form-group'>
	<label for='total_reserved_spaces' >Total Reserved Spaces</label><input type='number' name='total_reserved_spaces' id='total_reserved_spaces' value='$value' class='form-control' required />
</div> ";

}
function getTotal_available_public_spacesFormField($value=''){
	
	return "<div class='form-group'>
	<label for='total_available_public_spaces' >Total Available Public Spaces</label><input type='number' name='total_available_public_spaces' id='total_available_public_spaces' value='$value' class='form-control' required />
</div> ";

}
function getAmountFormField($value=''){
	
	return "<div class='form-group'>
	<label for='amount' >Amount</label><input type='number' name='amount' id='amount' value='$value' class='form-control' required />
</div> ";

}
function getService_chargeFormField($value=''){
	
	return "<div class='form-group'>
	<label for='service_charge' >Service Charge</label><input type='number' name='service_charge' id='service_charge' value='$value' class='form-control' required />
</div> ";

}
	 function getService_type_idFormField($value=''){
	$fk=null;//change the value of this variable to array('table'=>'service_type','display'=>'service_type_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

	if (is_null($fk)) {
		return $result="<input type='hidden' value='$value' name='service_type_id' id='service_type_id' class='form-control' />
			";
	}
	if (is_array($fk)) {
		$result ="<div class='form-group'>
		<label for='service_type_id'>Service Type Id</label>";
		$option = $this->loadOption($fk,$value);
		//load the value from the given table given the name of the table to load and the display field
		$result.="<select name='service_type_id' id='service_type_id' class='form-control'>
			$option
		</select>";
	}
	$result.="</div>";
	return  $result;

}
function getPrerequisite_feeFormField($value=''){
	
	return "<div class='form-group'>
	<label for='prerequisite_fee' >Prerequisite Fee</label><input type='number' name='prerequisite_fee' id='prerequisite_fee' value='$value' class='form-control' required />
</div> ";

}
function getIs_displayedFormField($value=''){
	
	return "<div class='form-group'>
	<label class='form-checkbox'>Is Displayed</label>
	<select class='form-control' id='is_displayed' name='is_displayed' >
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


		}
		