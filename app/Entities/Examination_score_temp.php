<?php
namespace App\Entities;

use App\Models\Crud;
use App\Libraries\EntityLoader;

/**
		* This class  is automatically generated based on the structure of the table. And it represent the model of the examination_score_temp table.
		*/
		class Examination_score_temp extends Crud
		{
protected static $tablename='Examination_score_temp';
/* this array contains the field that can be null*/
static $nullArray=array();
static $compositePrimaryKey=array();
static $uploadDependency = array();
/*this array contains the fields that are unique*/
static $uniqueArray=array();
/*this is an associative array containing the fieldname and the type of the field*/
static $typeArray = array('matric_number'=>'varchar','course_code'=>'varchar','session'=>'varchar','ca_score'=>'int','exam_score'=>'int','total_score'=>'int');
/*this is a dictionary that map a field name with the label name that will be shown in a form*/
static $labelArray=array('id'=>'','matric_number'=>'','course_code'=>'','session'=>'','ca_score'=>'','exam_score'=>'','total_score'=>'');
/*associative array of fields that have default value*/
static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.
		
static $relation=array();
static $tableAction=array('delete'=>'delete/examination_score_temp','edit'=>'edit/examination_score_temp');
function __construct($array=array())
{
	parent::__construct($array);
}
function getMatric_numberFormField($value=''){
	
	return "<div class='form-group'>
	<label for='matric_number' >Matric Number</label>
		<input type='text' name='matric_number' id='matric_number' value='$value' class='form-control' required />
</div> ";

}
function getCourse_codeFormField($value=''){
	
	return "<div class='form-group'>
	<label for='course_code' >Course Code</label>
		<input type='text' name='course_code' id='course_code' value='$value' class='form-control' required />
</div> ";

}
function getSessionFormField($value=''){
	
	return "<div class='form-group'>
	<label for='session' >Session</label>
		<input type='text' name='session' id='session' value='$value' class='form-control' required />
</div> ";

}
function getCa_scoreFormField($value=''){
	
	return "<div class='form-group'>
	<label for='ca_score' >Ca Score</label><input type='number' name='ca_score' id='ca_score' value='$value' class='form-control' required />
</div> ";

}
function getExam_scoreFormField($value=''){
	
	return "<div class='form-group'>
	<label for='exam_score' >Exam Score</label><input type='number' name='exam_score' id='exam_score' value='$value' class='form-control' required />
</div> ";

}
function getTotal_scoreFormField($value=''){
	
	return "<div class='form-group'>
	<label for='total_score' >Total Score</label><input type='number' name='total_score' id='total_score' value='$value' class='form-control' required />
</div> ";

}


		}
		