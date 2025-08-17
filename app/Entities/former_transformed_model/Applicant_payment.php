<?php
namespace App\Entities;

use App\Models\Crud;
use App\Libraries\EntityLoader;

/**
		* This class  is automatically generated based on the structure of the table. And it represent the model of the applicant_payment table.
		*/
		class Applicant_payment extends Crud
		{
protected static $tablename='Applicant_payment';
/* this array contains the field that can be null*/
static $nullArray=array();
static $compositePrimaryKey=array();
static $uploadDependency = array();
/*this array contains the fields that are unique*/
static $uniqueArray=array();
/*this is an associative array containing the fieldname and the type of the field*/
static $typeArray = array('amount'=>'int','subaccount_amount'=>'varchar','description'=>'varchar','session_id'=>'int','service_type_id'=>'varchar','service_charge'=>'int','date_created'=>'datetime');
/*this is a dictionary that map a field name with the label name that will be shown in a form*/
static $labelArray=array('id'=>'','amount'=>'','subaccount_amount'=>'','description'=>'','session_id'=>'','service_type_id'=>'','service_charge'=>'','date_created'=>'');
/*associative array of fields that have default value*/
static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.
		
static $relation=array();
static $tableAction=array('delete'=>'delete/applicant_payment','edit'=>'edit/applicant_payment');
function __construct($array=array())
{
	parent::__construct($array);
}
function getAmountFormField($value=''){
	
	return "<div class='form-group'>
	<label for='amount' >Amount</label><input type='number' name='amount' id='amount' value='$value' class='form-control' required />
</div> ";

}
function getDescriptionFormField($value=''){
	
	return "<div class='form-group'>
	<label for='description' >Description</label>
		<input type='text' name='description' id='description' value='$value' class='form-control' required />
</div> ";

}
	 function getSession_idFormField($value=''){
	$fk=null;//change the value of this variable to array('table'=>'session','display'=>'session_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

	if (is_null($fk)) {
		return $result="<input type='hidden' value='$value' name='session_id' id='session_id' class='form-control' />
			";
	}
	if (is_array($fk)) {
		$result ="<div class='form-group'>
		<label for='session_id'>Session Id</label>";
		$option = $this->loadOption($fk,$value);
		//load the value from the given table given the name of the table to load and the display field
		$result.="<select name='session_id' id='session_id' class='form-control'>
			$option
		</select>";
	}
	$result.="</div>";
	return  $result;

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
function getService_chargeFormField($value=''){
	
	return "<div class='form-group'>
	<label for='service_charge' >Service Charge</label><input type='number' name='service_charge' id='service_charge' value='$value' class='form-control' required />
</div> ";

}
function getDate_createdFormField($value=''){
	
	return " ";

}

public function getFeeDescription($description=null)
{
	EntityLoader::loadClass($this, 'fee_description');
	$description = $description ? $description : $this->description;
	$feeDesc = $this->fee_description->getWhere(['id'=>$description],$c,0,null,false);
	$description = $feeDesc?$feeDesc[0]->description:null;
	return $description;
}

public function APIList($filterList, $queryString,$start,$len,$orderBy)
{
	$temp = getFilterQueryFromDict($filterList);
	$filterQuery = $temp[0];
	$filterValues =$temp[1];
	if ($filterQuery || $queryString) {
		$filterQuery.= ($filterQuery?' and ':' where ').$queryString;
	}

	if(isset($_GET['sortBy']) && $orderBy){
		$sortDirection = ($_GET['sortDirection'] == 'down') ? 'desc' : 'asc';
		if($_GET['sortBy'] == 'description_name'){
			$filterQuery .= "order by fee_description.description $sortDirection";
		}else if($_GET['sortBy'] == 'session_name'){
			$filterQuery .= "order by applicant_payment.session_id $sortDirection";
		}
		else{
			$filterQuery .= " order by $orderBy ";
		}
	}else{
		$filterQuery.=" order by id desc ";
	}

	if ($len && isset($_GET['start'])) {
		$start = $this->db->escapeString($start);
		$len = $this->db->escapeString($len);
		$filterQuery.=" limit $start, $len";
	}

	if (!$filterValues) {
		$filterValues = [];
	}

	$query = "SELECT SQL_CALC_FOUND_ROWS applicant_payment.* from applicant_payment left join fee_description on fee_description.id = applicant_payment.description $filterQuery";
	$query2 = "SELECT FOUND_ROWS() as totalCount";
	$res = $this->db->query($query,$filterValues);
	$res = $res->getResultArray();
	$res2  = $this->db->query($query2);
	$res2 = $res2->getResultArray();
	$res = $this->processList($res);

	return [$res,$res2];
}

private function processList($items)
{
	EntityLoader::loadClass($this, 'fee_description');
	EntityLoader::loadClass($this, 'sessions');
	for ($i = 0; $i < count($items); $i++) {
		$items[$i] = $this->loadExtras($items[$i]);
	}
	return $items;
}

private function loadExtras($item)
{
	$description = $this->fee_description->getWhere(['id'=>$item['description']]);
	$description = $description[0];
	$item['description_name'] = $description->description;

	if($item['session_id']){
		$sesssion = $this->sessions->getWhere(['id'=>$item['session_id']]);
		$item['session_name'] = $sesssion[0]->date;
	}

	return $item;
}





}