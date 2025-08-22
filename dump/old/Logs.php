<?php
		require_once('application/models/Crud.php');
		/**
		* This class  is automatically generated based on the structure of the table. And it represent the model of the logs table.
		*/
		class Logs extends Crud
		{
protected static $tablename='Logs';
/* this array contains the field that can be null*/
static $nullArray=array('params' ,'rtime' ,'response_code' );
static $compositePrimaryKey=array();
static $uploadDependency = array();
/*this array contains the fields that are unique*/
static $uniqueArray=array();
/*this is an associative array containing the fieldname and the type of the field*/
static $typeArray = array('uri'=>'varchar','method'=>'varchar','params'=>'text','api_key'=>'varchar','ip_address'=>'varchar','time'=>'int','rtime'=>'float','authorized'=>'varchar','response_code'=>'smallint');
/*this is a dictionary that map a field name with the label name that will be shown in a form*/
static $labelArray=array('id'=>'','uri'=>'','method'=>'','params'=>'','api_key'=>'','ip_address'=>'','time'=>'','rtime'=>'','authorized'=>'','response_code'=>'');
/*associative array of fields that have default value*/
static $defaultArray = array('response_code'=>'0');
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.
		
static $relation=array();
static $tableAction=array('delete'=>'delete/logs','edit'=>'edit/logs');
function __construct($array=array())
{
	parent::__construct($array);
}
function getUriFormField($value=''){
	
	return "<div class='form-group'>
	<label for='uri' >Uri</label>
		<input type='text' name='uri' id='uri' value='$value' class='form-control' required />
</div> ";

}
function getMethodFormField($value=''){
	
	return "<div class='form-group'>
	<label for='method' >Method</label>
		<input type='text' name='method' id='method' value='$value' class='form-control' required />
</div> ";

}
function getParamsFormField($value=''){
	
	return "<div class='form-group'>
	<label for='params' >Params</label>
<textarea id='params' name='params' class='form-control' >$value</textarea>
</div> ";

}
function getApi_keyFormField($value=''){
	
	return "<div class='form-group'>
	<label for='api_key' >Api Key</label>
		<input type='text' name='api_key' id='api_key' value='$value' class='form-control' required />
</div> ";

}
function getIp_addressFormField($value=''){
	
	return "<div class='form-group'>
	<label for='ip_address' >Ip Address</label>
		<input type='text' name='ip_address' id='ip_address' value='$value' class='form-control' required />
</div> ";

}
function getTimeFormField($value=''){
	
	return "<div class='form-group'>
	<label for='time' >Time</label><input type='number' name='time' id='time' value='$value' class='form-control' required />
</div> ";

}
function getRtimeFormField($value=''){
	
	return "<div class='form-group'>
	<label for='rtime' >Rtime</label>
		<input type='text' name='rtime' id='rtime' value='$value' class='form-control'  />
</div> ";

}
function getAuthorizedFormField($value=''){
	
	return "<div class='form-group'>
	<label for='authorized' >Authorized</label>
		<input type='text' name='authorized' id='authorized' value='$value' class='form-control' required />
</div> ";

}
function getResponse_codeFormField($value=''){
	
	return "<div class='form-group'>
	<label for='response_code' >Response Code</label>
</div> ";

}


		}
		?>