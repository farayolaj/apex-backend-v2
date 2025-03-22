<?php
namespace App\Entities;

use App\Models\Crud;
use App\Libraries\EntityLoader;

/**
		* This class  is automatically generated based on the structure of the table. And it represent the model of the fee table.
		*/
		class Fee extends Crud
		{
protected static $tablename='Fee';
/* this array contains the field that can be null*/
static $nullArray=array('description' ,'date_created' );
static $compositePrimaryKey=array();
static $uploadDependency = array();
/*this array contains the fields that are unique*/
static $uniqueArray=array();
/*this is an associative array containing the fieldname and the type of the field*/
static $typeArray = array('amount'=>'varchar','description'=>'varchar','active'=>'tinyint','date_created'=>'timestamp');
/*this is a dictionary that map a field name with the label name that will be shown in a form*/
static $labelArray=array('id'=>'','amount'=>'','description'=>'','active'=>'','date_created'=>'');
/*associative array of fields that have default value*/
static $defaultArray = array('date_created'=>'CURRENT_TIMESTAMP');
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.
		
static $relation=array();
static $tableAction=array('delete'=>'delete/fee','edit'=>'edit/fee');
function __construct($array=array())
{
	parent::__construct($array);
}
function getDescriptionFormField($value=''){
	
	return "<div class='form-group'>
	<label for='description' >Description</label>
		<input type='text' name='description' id='description' value='$value' class='form-control'  />
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
function getAmountFormField($value=''){
	
	return "<div class='form-group'>
	<label for='amount' >Amount</label>
	<input type='text' name='amount' id='amount' class='form-control' />
</div> ";

}


		}
		