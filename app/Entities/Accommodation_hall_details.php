<?php
namespace App\Entities;

use App\Models\Crud;

/**
		* This class  is automatically generated based on the structure of the table. And it represent the model of the accommodation_hall_details table.
		*/
		class Accommodation_hall_details extends Crud
		{
protected static $tablename='Accommodation_hall_details';
/* this array contains the field that can be null*/
static $nullArray=array('allotted_level' );
static $compositePrimaryKey=array();
static $uploadDependency = array();
/*this array contains the fields that are unique*/
static $uniqueArray=array();
/*this is an associative array containing the fieldname and the type of the field*/
static $typeArray = array('hall_id'=>'int','unit_number'=>'varchar','block_number'=>'varchar','room_number'=>'varchar','bed_space_number'=>'varchar','allotted_level'=>'varchar','is_couponable'=>'tinyint','staff_id'=>'int','date_created'=>'datetime');
/*this is a dictionary that map a field name with the label name that will be shown in a form*/
static $labelArray=array('id'=>'','hall_id'=>'','unit_number'=>'','block_number'=>'','room_number'=>'','bed_space_number'=>'','allotted_level'=>'','is_couponable'=>'','staff_id'=>'','date_created'=>'');
/*associative array of fields that have default value*/
static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.
		
static $relation=array();
static $tableAction=array('delete'=>'delete/accommodation_hall_details','edit'=>'edit/accommodation_hall_details');
function __construct($array=array())
{
	parent::__construct($array);
}
	 function getHall_idFormField($value=''){
	$fk=null;//change the value of this variable to array('table'=>'hall','display'=>'hall_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

	if (is_null($fk)) {
		return $result="<input type='hidden' value='$value' name='hall_id' id='hall_id' class='form-control' />
			";
	}
	if (is_array($fk)) {
		$result ="<div class='form-group'>
		<label for='hall_id'>Hall Id</label>";
		$option = $this->loadOption($fk,$value);
		//load the value from the given table given the name of the table to load and the display field
		$result.="<select name='hall_id' id='hall_id' class='form-control'>
			$option
		</select>";
	}
	$result.="</div>";
	return  $result;

}
function getUnit_numberFormField($value=''){
	
	return "<div class='form-group'>
	<label for='unit_number' >Unit Number</label>
		<input type='text' name='unit_number' id='unit_number' value='$value' class='form-control' required />
</div> ";

}
function getBlock_numberFormField($value=''){
	
	return "<div class='form-group'>
	<label for='block_number' >Block Number</label>
		<input type='text' name='block_number' id='block_number' value='$value' class='form-control' required />
</div> ";

}
function getRoom_numberFormField($value=''){
	
	return "<div class='form-group'>
	<label for='room_number' >Room Number</label>
		<input type='text' name='room_number' id='room_number' value='$value' class='form-control' required />
</div> ";

}
function getBed_space_numberFormField($value=''){
	
	return "<div class='form-group'>
	<label for='bed_space_number' >Bed Space Number</label>
		<input type='text' name='bed_space_number' id='bed_space_number' value='$value' class='form-control' required />
</div> ";

}
function getAllotted_levelFormField($value=''){
	
	return "<div class='form-group'>
	<label for='allotted_level' >Allotted Level</label>
		<input type='text' name='allotted_level' id='allotted_level' value='$value' class='form-control'  />
</div> ";

}
function getIs_couponableFormField($value=''){
	
	return "<div class='form-group'>
	<label class='form-checkbox'>Is Couponable</label>
	<select class='form-control' id='is_couponable' name='is_couponable' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

}
	 function getStaff_idFormField($value=''){
	$fk=null;//change the value of this variable to array('table'=>'staff','display'=>'staff_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

	if (is_null($fk)) {
		return $result="<input type='hidden' value='$value' name='staff_id' id='staff_id' class='form-control' />
			";
	}
	if (is_array($fk)) {
		$result ="<div class='form-group'>
		<label for='staff_id'>Staff Id</label>";
		$option = $this->loadOption($fk,$value);
		//load the value from the given table given the name of the table to load and the display field
		$result.="<select name='staff_id' id='staff_id' class='form-control'>
			$option
		</select>";
	}
	$result.="</div>";
	return  $result;

}
function getDate_createdFormField($value=''){
	
	return " ";

}


		}
		