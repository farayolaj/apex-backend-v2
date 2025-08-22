<?php
		require_once('application/models/Crud.php');
		/**
		* This class  is automatically generated based on the structure of the table. And it represent the model of the accommodation_hall_bookings table.
		*/
		class Accommodation_hall_bookings extends Crud
		{
protected static $tablename='Accommodation_hall_bookings';
/* this array contains the field that can be null*/
static $nullArray=array('transaction_ref' );
static $compositePrimaryKey=array();
static $uploadDependency = array();
/*this array contains the fields that are unique*/
static $uniqueArray=array();
/*this is an associative array containing the fieldname and the type of the field*/
static $typeArray = array('hall_detail_id'=>'int','student_id'=>'int','session'=>'int','level'=>'varchar','booking_reference'=>'varchar','transaction_ref'=>'varchar','amount'=>'varchar','service_charge'=>'int','date_reserved'=>'datetime','date_booked'=>'datetime','is_coupon_payment'=>'tinyint','coupon_used'=>'int','time_booked'=>'varchar','time_to_set_inactive'=>'varchar','booking_status'=>'tinyint','active'=>'tinyint');
/*this is a dictionary that map a field name with the label name that will be shown in a form*/
static $labelArray=array('id'=>'','hall_detail_id'=>'','student_id'=>'','session'=>'','level'=>'','booking_reference'=>'','transaction_ref'=>'','amount'=>'','service_charge'=>'','date_reserved'=>'','date_booked'=>'','is_coupon_payment'=>'','coupon_used'=>'','time_booked'=>'','time_to_set_inactive'=>'','booking_status'=>'','active'=>'');
/*associative array of fields that have default value*/
static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.
		
static $relation=array();
static $tableAction=array('delete'=>'delete/accommodation_hall_bookings','edit'=>'edit/accommodation_hall_bookings');
function __construct($array=array())
{
	parent::__construct($array);
}
	 function getHall_detail_idFormField($value=''){
	$fk=null;//change the value of this variable to array('table'=>'hall_detail','display'=>'hall_detail_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

	if (is_null($fk)) {
		return $result="<input type='hidden' value='$value' name='hall_detail_id' id='hall_detail_id' class='form-control' />
			";
	}
	if (is_array($fk)) {
		$result ="<div class='form-group'>
		<label for='hall_detail_id'>Hall Detail Id</label>";
		$option = $this->loadOption($fk,$value);
		//load the value from the given table given the name of the table to load and the display field
		$result.="<select name='hall_detail_id' id='hall_detail_id' class='form-control'>
			$option
		</select>";
	}
	$result.="</div>";
	return  $result;

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
function getSessionFormField($value=''){
	
	return "<div class='form-group'>
	<label for='session' >Session</label><input type='number' name='session' id='session' value='$value' class='form-control' required />
</div> ";

}
function getLevelFormField($value=''){
	
	return "<div class='form-group'>
	<label for='level' >Level</label>
		<input type='text' name='level' id='level' value='$value' class='form-control' required />
</div> ";

}
function getBooking_referenceFormField($value=''){
	
	return "<div class='form-group'>
	<label for='booking_reference' >Booking Reference</label>
		<input type='text' name='booking_reference' id='booking_reference' value='$value' class='form-control' required />
</div> ";

}
function getTransaction_refFormField($value=''){
	
	return "<div class='form-group'>
	<label for='transaction_ref' >Transaction Ref</label>
		<input type='text' name='transaction_ref' id='transaction_ref' value='$value' class='form-control'  />
</div> ";

}
function getAmountFormField($value=''){
	
	return "<div class='form-group'>
	<label for='amount' >Amount</label>
		<input type='text' name='amount' id='amount' value='$value' class='form-control' required />
</div> ";

}
function getService_chargeFormField($value=''){
	
	return "<div class='form-group'>
	<label for='service_charge' >Service Charge</label><input type='number' name='service_charge' id='service_charge' value='$value' class='form-control' required />
</div> ";

}
function getDate_reservedFormField($value=''){
	
	return " ";

}
function getDate_bookedFormField($value=''){
	
	return " ";

}
function getIs_coupon_paymentFormField($value=''){
	
	return "<div class='form-group'>
	<label class='form-checkbox'>Is Coupon Payment</label>
	<select class='form-control' id='is_coupon_payment' name='is_coupon_payment' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

}
function getCoupon_usedFormField($value=''){
	
	return "<div class='form-group'>
	<label for='coupon_used' >Coupon Used</label><input type='number' name='coupon_used' id='coupon_used' value='$value' class='form-control' required />
</div> ";

}
function getTime_bookedFormField($value=''){
	
	return "<div class='form-group'>
	<label for='time_booked' >Time Booked</label>
		<input type='text' name='time_booked' id='time_booked' value='$value' class='form-control' required />
</div> ";

}
function getTime_to_set_inactiveFormField($value=''){
	
	return "<div class='form-group'>
	<label for='time_to_set_inactive' >Time To Set Inactive</label>
		<input type='text' name='time_to_set_inactive' id='time_to_set_inactive' value='$value' class='form-control' required />
</div> ";

}
function getBooking_statusFormField($value=''){
	
	return "<div class='form-group'>
	<label class='form-checkbox'>Booking Status</label>
	<select class='form-control' id='booking_status' name='booking_status' >
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


		}
		?>