<?php
namespace App\Entities;

use App\Models\Crud;

/**
		* This class  is automatically generated based on the structure of the table. And it represent the model of the levels table.
		*/
		class Levels extends Crud
		{
protected static $tablename='Levels';
/* this array contains the field that can be null*/
static $nullArray=array('active' ,'date_created' );
static $compositePrimaryKey=array();
static $uploadDependency = array();
/*this array contains the fields that are unique*/
static $uniqueArray=array('name' );
/*this is an associative array containing the fieldname and the type of the field*/
static $typeArray = array('name'=>'varchar','active'=>'tinyint','date_created'=>'timestamp');
/*this is a dictionary that map a field name with the label name that will be shown in a form*/
static $labelArray=array('id'=>'','name'=>'','active'=>'','date_created'=>'');
/*associative array of fields that have default value*/
static $defaultArray = array('active'=>'1','date_created'=>'CURRENT_TIMESTAMP');
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.
		
static $relation=array();
static $tableAction=array('delete'=>'delete/levels','edit'=>'edit/levels');
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
		