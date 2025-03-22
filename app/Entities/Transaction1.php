<?php
namespace App\Entities;

use App\Models\Crud;
use App\Libraries\EntityLoader;

/**
		* This class  is automatically generated based on the structure of the table. And it represent the model of the transaction1 table.
		*/
		class Transaction1 extends Crud
		{
protected static $tablename='Transaction1';
/* this array contains the field that can be null*/
static $nullArray=array('payment_url' ,'is_third_party' );
static $compositePrimaryKey=array();
static $uploadDependency = array();
/*this array contains the fields that are unique*/
static $uniqueArray=array();
/*this is an associative array containing the fieldname and the type of the field*/
static $typeArray = array('payment_id'=>'varchar','real_payment_id'=>'int','payment_description'=>'varchar','payment_option'=>'int','student_id'=>'int','programme_id'=>'int','session'=>'int','level'=>'tinyint','transaction_ref'=>'varchar','rrr_code'=>'varchar','payment_status'=>'varchar','beneficiary_1'=>'varchar','beneficiary_2'=>'varchar','payment_status_description'=>'varchar','amount_paid'=>'varchar','penalty_fee'=>'varchar','service_charge'=>'varchar','total_amount'=>'varchar','payment_url'=>'text','is_third_party'=>'tinyint','merchant_name'=>'varchar','date_performed'=>'datetime','date_completed'=>'datetime','date_payment_communicated'=>'datetime');
/*this is a dictionary that map a field name with the label name that will be shown in a form*/
static $labelArray=array('id'=>'','payment_id'=>'','real_payment_id'=>'','payment_description'=>'','payment_option'=>'','student_id'=>'','programme_id'=>'','session'=>'','level'=>'','transaction_ref'=>'','rrr_code'=>'','payment_status'=>'','beneficiary_1'=>'','beneficiary_2'=>'','payment_status_description'=>'','amount_paid'=>'','penalty_fee'=>'','service_charge'=>'','total_amount'=>'','payment_url'=>'','is_third_party'=>'','merchant_name'=>'','date_performed'=>'','date_completed'=>'','date_payment_communicated'=>'');
/*associative array of fields that have default value*/
static $defaultArray = array('is_third_party'=>'0');
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.
		
static $relation=array('payment'=>array( 'payment_id', 'ID')
,'programme'=>array( 'programme_id', 'ID')
);
static $tableAction=array('delete'=>'delete/transaction1','edit'=>'edit/transaction1');
function __construct($array=array())
{
	parent::__construct($array);
}
	 function getPayment_idFormField($value=''){
	$fk=null;//change the value of this variable to array('table'=>'payment','display'=>'payment_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

	if (is_null($fk)) {
		return $result="<input type='hidden' value='$value' name='payment_id' id='payment_id' class='form-control' />
			";
	}
	if (is_array($fk)) {
		$result ="<div class='form-group'>
		<label for='payment_id'>Payment Id</label>";
		$option = $this->loadOption($fk,$value);
		//load the value from the given table given the name of the table to load and the display field
		$result.="<select name='payment_id' id='payment_id' class='form-control'>
			$option
		</select>";
	}
	$result.="</div>";
	return  $result;

}
	 function getReal_payment_idFormField($value=''){
	$fk=null;//change the value of this variable to array('table'=>'real_payment','display'=>'real_payment_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

	if (is_null($fk)) {
		return $result="<input type='hidden' value='$value' name='real_payment_id' id='real_payment_id' class='form-control' />
			";
	}
	if (is_array($fk)) {
		$result ="<div class='form-group'>
		<label for='real_payment_id'>Real Payment Id</label>";
		$option = $this->loadOption($fk,$value);
		//load the value from the given table given the name of the table to load and the display field
		$result.="<select name='real_payment_id' id='real_payment_id' class='form-control'>
			$option
		</select>";
	}
	$result.="</div>";
	return  $result;

}
function getPayment_descriptionFormField($value=''){
	
	return "<div class='form-group'>
	<label for='payment_description' >Payment Description</label>
		<input type='text' name='payment_description' id='payment_description' value='$value' class='form-control' required />
</div> ";

}
function getPayment_optionFormField($value=''){
	
	return "<div class='form-group'>
	<label for='payment_option' >Payment Option</label><input type='number' name='payment_option' id='payment_option' value='$value' class='form-control' required />
</div> ";

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
	 function getProgramme_idFormField($value=''){
	$fk=null;//change the value of this variable to array('table'=>'programme','display'=>'programme_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

	if (is_null($fk)) {
		return $result="<input type='hidden' value='$value' name='programme_id' id='programme_id' class='form-control' />
			";
	}
	if (is_array($fk)) {
		$result ="<div class='form-group'>
		<label for='programme_id'>Programme Id</label>";
		$option = $this->loadOption($fk,$value);
		//load the value from the given table given the name of the table to load and the display field
		$result.="<select name='programme_id' id='programme_id' class='form-control'>
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
function getLevelFormField($value=''){
	
	return "<div class='form-group'>
	<label class='form-checkbox'>Level</label>
	<select class='form-control' id='level' name='level' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

}
function getTransaction_refFormField($value=''){
	
	return "<div class='form-group'>
	<label for='transaction_ref' >Transaction Ref</label>
		<input type='text' name='transaction_ref' id='transaction_ref' value='$value' class='form-control' required />
</div> ";

}
function getRrr_codeFormField($value=''){
	
	return "<div class='form-group'>
	<label for='rrr_code' >Rrr Code</label>
		<input type='text' name='rrr_code' id='rrr_code' value='$value' class='form-control' required />
</div> ";

}
function getPayment_statusFormField($value=''){
	
	return "<div class='form-group'>
	<label for='payment_status' >Payment Status</label>
		<input type='text' name='payment_status' id='payment_status' value='$value' class='form-control' required />
</div> ";

}
function getBeneficiary_1FormField($value=''){
	
	return "<div class='form-group'>
	<label for='beneficiary_1' >Beneficiary 1</label>
		<input type='text' name='beneficiary_1' id='beneficiary_1' value='$value' class='form-control' required />
</div> ";

}
function getBeneficiary_2FormField($value=''){
	
	return "<div class='form-group'>
	<label for='beneficiary_2' >Beneficiary 2</label>
		<input type='text' name='beneficiary_2' id='beneficiary_2' value='$value' class='form-control' required />
</div> ";

}
function getPayment_status_descriptionFormField($value=''){
	
	return "<div class='form-group'>
	<label for='payment_status_description' >Payment Status Description</label>
		<input type='text' name='payment_status_description' id='payment_status_description' value='$value' class='form-control' required />
</div> ";

}
function getAmount_paidFormField($value=''){
	
	return "<div class='form-group'>
	<label for='amount_paid' >Amount Paid</label>
		<input type='text' name='amount_paid' id='amount_paid' value='$value' class='form-control' required />
</div> ";

}
function getPenalty_feeFormField($value=''){
	
	return "<div class='form-group'>
	<label for='penalty_fee' >Penalty Fee</label>
		<input type='text' name='penalty_fee' id='penalty_fee' value='$value' class='form-control' required />
</div> ";

}
function getService_chargeFormField($value=''){
	
	return "<div class='form-group'>
	<label for='service_charge' >Service Charge</label>
		<input type='text' name='service_charge' id='service_charge' value='$value' class='form-control' required />
</div> ";

}
function getTotal_amountFormField($value=''){
	
	return "<div class='form-group'>
	<label for='total_amount' >Total Amount</label>
		<input type='text' name='total_amount' id='total_amount' value='$value' class='form-control' required />
</div> ";

}
function getPayment_urlFormField($value=''){
	
	return "<div class='form-group'>
	<label for='payment_url' >Payment Url</label>
<textarea id='payment_url' name='payment_url' class='form-control' >$value</textarea>
</div> ";

}
function getIs_third_partyFormField($value=''){
	
	return "<div class='form-group'>
	<label class='form-checkbox'>Is Third Party</label>
	<select class='form-control' id='is_third_party' name='is_third_party' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

}
function getMerchant_nameFormField($value=''){
	
	return "<div class='form-group'>
	<label for='merchant_name' >Merchant Name</label>
		<input type='text' name='merchant_name' id='merchant_name' value='$value' class='form-control' required />
</div> ";

}
function getDate_performedFormField($value=''){
	
	return " ";

}
function getDate_completedFormField($value=''){
	
	return " ";

}
function getDate_payment_communicatedFormField($value=''){
	
	return " ";

}


		
protected function getPayment(){
	$query ='SELECT * FROM payment WHERE id=?';
	if (!isset($this->array['id'])) {
		return null;
	}
	$id = $this->array['id'];
	$result = $this->db->query($query,array($id));
	$result =$result->getResultArray();
	if (empty($result)) {
		return false;
	}
	return new \App\Entities\Payment($result[0]);
}
		
protected function getProgramme(){
	$query ='SELECT * FROM programme WHERE id=?';
	if (!isset($this->array['id'])) {
		return null;
	}
	$id = $this->array['id'];
	$result = $this->db->query($query,array($id));
	$result =$result->getResultArray();
	if (empty($result)) {
		return false;
	}
	return new \App\Entities\Programme($result[0]);
}
		}
		