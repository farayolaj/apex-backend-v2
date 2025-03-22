<?php
namespace App\Entities;

use App\Models\Crud;
use App\Libraries\EntityLoader;

/**
		* This class  is automatically generated based on the structure of the table. And it represent the model of the medical_record table.
		*/
		class Medical_record extends Crud
		{
protected static $tablename='Medical_record';
/* this array contains the field that can be null*/
static $nullArray=array('disabilities' );
static $compositePrimaryKey=array();
static $uploadDependency = array();
/*this array contains the fields that are unique*/
static $uniqueArray=array();
/*this is an associative array containing the fieldname and the type of the field*/
static $typeArray = array('student_id'=>'int','blood_group'=>'varchar','genotype'=>'varchar','height'=>'varchar','weight'=>'varchar','allergy'=>'text','disabilities'=>'varchar','others'=>'text');
/*this is a dictionary that map a field name with the label name that will be shown in a form*/
static $labelArray=array('id'=>'','student_id'=>'','blood_group'=>'','genotype'=>'','height'=>'','weight'=>'','allergy'=>'','disabilities'=>'','others'=>'');
/*associative array of fields that have default value*/
static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.
		
static $relation=array();
static $tableAction=array('delete'=>'delete/medical_record','edit'=>'edit/medical_record');
function __construct($array=array())
{
	parent::__construct($array);
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
function getBlood_groupFormField($value=''){
	
	return "<div class='form-group'>
	<label for='blood_group' >Blood Group</label>
		<input type='text' name='blood_group' id='blood_group' value='$value' class='form-control' required />
</div> ";

}
function getGenotypeFormField($value=''){
	
	return "<div class='form-group'>
	<label for='genotype' >Genotype</label>
		<input type='text' name='genotype' id='genotype' value='$value' class='form-control' required />
</div> ";

}
function getHeightFormField($value=''){
	
	return "<div class='form-group'>
	<label for='height' >Height</label>
		<input type='text' name='height' id='height' value='$value' class='form-control' required />
</div> ";

}
function getWeightFormField($value=''){
	
	return "<div class='form-group'>
	<label for='weight' >Weight</label>
		<input type='text' name='weight' id='weight' value='$value' class='form-control' required />
</div> ";

}
function getAllergyFormField($value=''){
	
	return "<div class='form-group'>
	<label for='allergy' >Allergy</label>
<textarea id='allergy' name='allergy' class='form-control' required>$value</textarea>
</div> ";

}
function getDisabilitiesFormField($value=''){
	
	return "<div class='form-group'>
	<label for='disabilities' >Disabilities</label>
		<input type='text' name='disabilities' id='disabilities' value='$value' class='form-control'  />
</div> ";

}
function getOthersFormField($value=''){
	
	return "<div class='form-group'>
	<label for='others' >Others</label>
<textarea id='others' name='others' class='form-control' required>$value</textarea>
</div> ";

}


		}
		