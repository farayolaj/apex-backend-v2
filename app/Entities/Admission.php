<?php
namespace App\Entities;

use App\Models\Crud;
use App\Libraries\EntityLoader;

/** 
* This class is automatically generated based on the structure of the table.
* And it represent the model of the admission table
*/
class Admission extends Crud {

/** 
* This is the entity name equivalent to the table name
* @var string
*/
protected static $tablename = "Admission"; 

/** 
* This array contains the field that can be null
* @var array
*/
public static $nullArray = ['code','criteria','date_created'];

/** 
* This are fields that must be unique across a row in a table.
* Similar to composite primary key in sql(oracle,mysql)
* @var array
*/
public static $compositePrimaryKey = [];

/** 
* This is to provided an array of fields that can be used for building a
* template header for batch upload using csv format
* @var array
*/
public static $uploadDependency = [];

/** 
* If there is a relationship between this table and another table, this display field properties is used as a column in the query.
* A field in the other table that displays the connection between this name and this table's name,something along these lines
* table_id. We cannot use a name similar to table id in the table that is displayed to the user, so the display field is used in
* place of it. To ensure that the other model queries use that field name as a column to be fetched with the query rather than the
* table id alone, the display field name provided must be a column in the table to replace the table id shown to the user.
* @var array|string
*/
public static $displayField = 'description';

/** 
* This array contains the fields that are unique
* @var array
*/
public static $uniqueArray = ['name'];

/** 
* This is an associative array containing the fieldname and the datatype
* of the field
* @var array
*/
public static $typeArray = ['name' => 'varchar','session_id' => 'int','admission_mode' => 'enum','description' => 'text','applicant_payment_id' => 'int','criteria' => 'text','active' => 'tinyint','date_created' => 'timestamp','code' => 'varchar'];

/** 
* This is a dictionary that map a field name with the label name that
* will be shown in a form
* @var array
*/
public static $labelArray = ['ID' => '','name' => '','session_id' => '','admission_mode' => '','description' => '','applicant_payment_id' => '','criteria' => '','active' => '','date_created' => '','code' => ''];

/** 
* Associative array of fields in the table that have default value
* @var array
*/
public static $defaultArray = ['active' => '0','date_created' => 'current_timestamp()'];

/** 
*  This is an array containing an associative array of field that should be regareded as document
* field. array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
* the folder to save must represent a path from the basepath. it should be a relative
* path,preserve filename will be either true or false. when true,the file will be uploaded with
* it default filename else the system will pick the current user id in the session as the name of
* the file.
* @var array
*/
public static $documentField = []; 

/** 
* This is an associative array of fields showing relationship between
* entities
* @var array
*/
public static $relation = ['session' => array('session_id','id')
,'applicant_payment' => array('applicant_payment_id','id')
];

/** 
* This are the action allowed to be performed on the entity and this can
* be changed in the formConfig model file for flexibility
* @var array
*/
public static $tableAction = ['delete' => 'delete/admission', 'edit' => 'edit/admission'];

public function __construct(array $array = [])
{
	parent::__construct($array);
}
 
public function getNameFormField($value = ''){
	return "<div class='form-group'>
				<label for='name'>Name</label>
				<input type='text' name='name' id='name' value='$value' class='form-control' required />
			</div>";
} 
public function getSession_idFormField($value = ''){
	$fk = null; 
 	//change the value of this variable to array('table'=>'session','display'=>'session_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'session_name' as value from 'session' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('session', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if(is_null($fk)){
			return $result = "<input type='hidden' name='session_id' id='session_id' value='$value' class='form-control' />";
		}

		if(is_array($fk)){
			
			$result ="<div class='form-group'>
			<label for='session_id'>Session</label>";
			$option = $this->loadOption($fk,$value);
			//load the value from the given table given the name of the table to load and the display field
			$result.="<select name='session_id' id='session_id' class='form-control'>
						$option
					</select>";
					$result.="</div>";
		return $result;
		}
		
}
public function getAdmission_modeFormField($value = ''){
	return "<div class='form-group'>
				<label for='admission_mode'>Admission Mode</label>
				<input type='text' name='admission_mode' id='admission_mode' value='$value' class='form-control' required />
			</div>";
} 
public function getDescriptionFormField($value = ''){
	return "<div class='form-group'>
				<label for='description'>Description</label>
				<input type='text' name='description' id='description' value='$value' class='form-control' required />
			</div>";
} 
public function getApplicant_payment_idFormField($value = ''){
	$fk = null; 
 	//change the value of this variable to array('table'=>'applicant_payment','display'=>'applicant_payment_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'applicant_payment_name' as value from 'applicant_payment' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('applicant_payment', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if(is_null($fk)){
			return $result = "<input type='hidden' name='applicant_payment_id' id='applicant_payment_id' value='$value' class='form-control' />";
		}

		if(is_array($fk)){
			
			$result ="<div class='form-group'>
			<label for='applicant_payment_id'>Applicant Payment</label>";
			$option = $this->loadOption($fk,$value);
			//load the value from the given table given the name of the table to load and the display field
			$result.="<select name='applicant_payment_id' id='applicant_payment_id' class='form-control'>
						$option
					</select>";
					$result.="</div>";
		return $result;
		}
		
}
public function getCriteriaFormField($value = ''){
	return "<div class='form-group'>
				<label for='criteria'>Criteria</label>
				<input type='text' name='criteria' id='criteria' value='$value' class='form-control' required />
			</div>";
} 
public function getActiveFormField($value = ''){
	return "<div class='form-group'>
				<label for='active'>Active</label>
				<input type='text' name='active' id='active' value='$value' class='form-control' required />
			</div>";
} 
public function getDate_createdFormField($value = ''){
	return "<div class='form-group'>
				<label for='date_created'>Date Created</label>
				<input type='text' name='date_created' id='date_created' value='$value' class='form-control' required />
			</div>";
} 
public function getCodeFormField($value = ''){
	return "<div class='form-group'>
				<label for='code'>Code</label>
				<input type='text' name='code' id='code' value='$value' class='form-control' required />
			</div>";
} 

protected function getSession(){
	$query = 'SELECT * FROM session WHERE id=?';
	if (!isset($this->array['id'])) {
		return null;
	}
	$id = $this->array['id'];
	$result = $this->db->query($query,[$id]);
	$result = $result->getResultArray();
	if (empty($result)) {
		return false;
	}
	return new \App\Entities\Session($result[0]);
}

protected function getApplicant_payment(){
	$query = 'SELECT * FROM applicant_payment WHERE id=?';
	if (!isset($this->array['id'])) {
		return null;
	}
	$id = $this->array['id'];
	$result = $this->db->query($query,[$id]);
	$result = $result->getResultArray();
	if (empty($result)) {
		return false;
	}
	return new \App\Entities\Applicant_payment($result[0]);
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
		$filterQuery .= " order by $orderBy ";
	}else{
		$filterQuery .= " order by id desc";
	}

	if ($len && isset($_GET['start'])) {
		$start = $this->db->escape($start);
		$len = $this->db->escape($len);
		$filterQuery.=" limit $start, $len";
	}

	if (!$filterValues) {
		$filterValues = [];
	}

	$query = "SELECT SQL_CALC_FOUND_ROWS admission.*,applicant_payment.description as payment_desc from admission left join applicant_payment on applicant_payment.id = admission.applicant_payment_id $filterQuery";
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
	if($item['payment_desc']){
		$description = $this->fee_description->getWhere(['id'=>$item['payment_desc']]);
		$description = $description[0];
		$item['applicant_payment_name'] = $description->description;
	}
	if($item['session_id']){
		$sesssion = $this->sessions->getWhere(['id'=>$item['session_id']]);
		$item['session_name'] = $sesssion[0]->date;
	}

	return $item;
}


}
