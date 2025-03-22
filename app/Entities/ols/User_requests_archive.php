<?php

use App\Entities\New_request;
use App\Entities\Project_task;
use App\Entities\User;

require_once('application/models/Crud.php');

/**
 * This class is automatically generated based on the structure of the table.
 * And it represent the model of the user_requests_archive table
 */
class User_requests_archive extends Crud
{

	/**
	 * This is the entity name equivalent to the table name
	 * @var string
	 */
	protected static $tablename = "User_requests_archive";

	/**
	 * This array contains the field that can be null
	 * @var array
	 */
	public static $nullArray = ['request_no', 'title', 'user_id', 'request_id', 'amount', 'description', 'beneficiaries', 'deduction', 'withhold_tax', 'vat', 'stamp_duty', 'total_amount', 'request_status', 'project_task_id', 'feedback', 'date_approved', 'updated_at', 'action_timeline', 'request_from', 'stage', 'voucher_document', 'deduction_amount', 'new_request_id'];

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
	public static $displayField = 'request_no';

	/**
	 * This array contains the fields that are unique
	 * @var array
	 */
	public static $uniqueArray = [];

	/**
	 * This is an associative array containing the fieldname and the datatype
	 * of the field
	 * @var array
	 */
	public static $typeArray = ['request_no' => 'varchar', 'title' => 'varchar', 'user_id' => 'int', 'request_id' => 'int', 'amount' => 'decimal', 'description' => 'text', 'beneficiaries' => 'text', 'deduction' => 'decimal', 'withhold_tax' => 'decimal', 'vat' => 'decimal', 'stamp_duty' => 'decimal', 'total_amount' => 'decimal', 'request_status' => 'enum', 'project_task_id' => 'int', 'feedback' => 'text', 'date_approved' => 'timestamp', 'created_at' => 'timestamp', 'updated_at' => 'timestamp', 'action_timeline' => 'text', 'request_from' => 'enum', 'stage' => 'enum', 'voucher_document' => 'varchar', 'deduction_amount' => 'text', 'new_request_id' => 'int'];

	/**
	 * This is a dictionary that map a field name with the label name that
	 * will be shown in a form
	 * @var array
	 */
	public static $labelArray = ['id' => '', 'request_no' => '', 'title' => '', 'user_id' => '', 'request_id' => '', 'amount' => '', 'description' => '', 'beneficiaries' => '', 'deduction' => '', 'withhold_tax' => '', 'vat' => '', 'stamp_duty' => '', 'total_amount' => '', 'request_status' => '', 'project_task_id' => '', 'feedback' => '', 'date_approved' => '', 'created_at' => '', 'updated_at' => '', 'action_timeline' => '', 'request_from' => '', 'stage' => '', 'voucher_document' => '', 'deduction_amount' => '', 'new_request_id' => ''];

	/**
	 * Associative array of fields in the table that have default value
	 * @var array
	 */
	public static $defaultArray = ['request_status' => 'pending', 'created_at' => 'current_timestamp()', 'request_from' => 'staff'];

	/**
	 *  This is an array containing an associative array of field that should be regareded as document field.
	 * it will contain the setting for max size and data type. Example: populate this array with fields that
	 * are meant to be displayed as document in the format
	 * array('fieldname'=>array('type'=>array('jpeg','jpg','png','gif'),'size'=>'1048576','directory'=>'directoryName/','preserve'=>false,'max_width'=>'1000','max_height'=>'500')).
	 * the folder to save must represent a path from the basepath. it should be a relative path,preserve
	 * filename will be either true or false. when true,the file will be uploaded with it default filename
	 * else the system will pick the current user id in the session as the name of the file
	 * @var array
	 */
	public static $documentField = [];

	/**
	 * This is an associative array of fields showing relationship between
	 * entities
	 * @var array
	 */
	public static $relation = ['user' => array('user_id', 'id')
		, 'request' => array('request_id', 'id')
		, 'project_task' => array('project_task_id', 'id')
		, 'new_request' => array('new_request_id', 'id')
	];

	/**
	 * This are the action allowed to be performed on the entity and this can
	 * be changed in the formConfig model file for flexibility
	 * @var array
	 */
	public static $tableAction = ['delete' => 'delete/user_requests_archive', 'edit' => 'edit/user_requests_archive'];

	public function __construct(array $array = [])
	{
		parent::__construct($array);
	}

	public function getRequest_noFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='request_no'>Request No</label>
		<input type='text' name='request_no' id='request_no' value='$value' class='form-control' required />
	</div>";
	}

	public function getTitleFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='title'>Title</label>
		<input type='text' name='title' id='title' value='$value' class='form-control' required />
	</div>";
	}

	public function getUser_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'user','display'=>'user_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'user_name' as value from 'user' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('user', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='user_id' id='user_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
		<label for='user_id'>User</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='user_id' id='user_id' class='form-control'>
					$option
				</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getRequest_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'request','display'=>'request_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'request_name' as value from 'request' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('request', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='request_id' id='request_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
		<label for='request_id'>Request</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='request_id' id='request_id' class='form-control'>
					$option
				</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getAmountFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='amount'>Amount</label>
		<input type='text' name='amount' id='amount' value='$value' class='form-control' required />
	</div>";
	}

	public function getDescriptionFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='description'>Description</label>
		<input type='text' name='description' id='description' value='$value' class='form-control' required />
	</div>";
	}

	public function getBeneficiariesFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='beneficiaries'>Beneficiaries</label>
		<input type='text' name='beneficiaries' id='beneficiaries' value='$value' class='form-control' required />
	</div>";
	}

	public function getDeductionFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='deduction'>Deduction</label>
		<input type='text' name='deduction' id='deduction' value='$value' class='form-control' required />
	</div>";
	}

	public function getWithhold_taxFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='withhold_tax'>Withhold Tax</label>
		<input type='text' name='withhold_tax' id='withhold_tax' value='$value' class='form-control' required />
	</div>";
	}

	public function getVatFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='vat'>Vat</label>
		<input type='text' name='vat' id='vat' value='$value' class='form-control' required />
	</div>";
	}

	public function getStamp_dutyFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='stamp_duty'>Stamp Duty</label>
		<input type='text' name='stamp_duty' id='stamp_duty' value='$value' class='form-control' required />
	</div>";
	}

	public function getTotal_amountFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='total_amount'>Total Amount</label>
		<input type='text' name='total_amount' id='total_amount' value='$value' class='form-control' required />
	</div>";
	}

	public function getRequest_statusFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='request_status'>Request Status</label>
		<input type='text' name='request_status' id='request_status' value='$value' class='form-control' required />
	</div>";
	}

	public function getProject_task_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'project_task','display'=>'project_task_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'project_task_name' as value from 'project_task' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('project_task', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='project_task_id' id='project_task_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
		<label for='project_task_id'>Project Task</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='project_task_id' id='project_task_id' class='form-control'>
					$option
				</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getFeedbackFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='feedback'>Feedback</label>
		<input type='text' name='feedback' id='feedback' value='$value' class='form-control' required />
	</div>";
	}

	public function getDate_approvedFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='date_approved'>Date Approved</label>
		<input type='text' name='date_approved' id='date_approved' value='$value' class='form-control' required />
	</div>";
	}

	public function getCreated_atFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='created_at'>Created At</label>
		<input type='text' name='created_at' id='created_at' value='$value' class='form-control' required />
	</div>";
	}

	public function getUpdated_atFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='updated_at'>Updated At</label>
		<input type='text' name='updated_at' id='updated_at' value='$value' class='form-control' required />
	</div>";
	}

	public function getAction_timelineFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='action_timeline'>Action Timeline</label>
		<input type='text' name='action_timeline' id='action_timeline' value='$value' class='form-control' required />
	</div>";
	}

	public function getRequest_fromFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='request_from'>Request From</label>
		<input type='text' name='request_from' id='request_from' value='$value' class='form-control' required />
	</div>";
	}

	public function getStageFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='stage'>Stage</label>
		<input type='text' name='stage' id='stage' value='$value' class='form-control' required />
	</div>";
	}

	public function getVoucher_documentFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='voucher_document'>Voucher Document</label>
		<input type='text' name='voucher_document' id='voucher_document' value='$value' class='form-control' required />
	</div>";
	}

	public function getDeduction_amountFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='deduction_amount'>Deduction Amount</label>
		<input type='text' name='deduction_amount' id='deduction_amount' value='$value' class='form-control' required />
	</div>";
	}

	public function getNew_request_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'new_request','display'=>'new_request_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'new_request_name' as value from 'new_request' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('new_request', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='new_request_id' id='new_request_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
		<label for='new_request_id'>New Request</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='new_request_id' id='new_request_id' class='form-control'>
					$option
				</select>";
			$result .= "</div>";
			return $result;
		}

	}


	protected function getUser()
	{
		$query = 'SELECT * FROM user WHERE id=?';
		if (!isset($this->array['ID'])) {
			return null;
		}
		$id = $this->array['ID'];
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		$resultObject = new User($result[0]);
		return $resultObject;
	}

	protected function getRequest_type()
	{
		$query = 'SELECT * FROM request_type WHERE id=?';
		if (!isset($this->array['request_id'])) {
			return null;
		}
		$id = $this->array['request_id'];
		$result = $this->query($query, [$id]);
		if (!$result) {
			return null;
		}
		include_once('Request_type.php');
		$resultObject = new Request_type($result[0]);
		return $resultObject;
	}

	protected function getProject_task()
	{
		$query = 'SELECT * FROM project_task WHERE id=?';
		if (!isset($this->array['ID'])) {
			return null;
		}
		$id = $this->array['ID'];
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		$resultObject = new Project_task($result[0]);
		return $resultObject;
	}

	protected function getNew_request()
	{
		$query = 'SELECT * FROM new_request WHERE id=?';
		if (!isset($this->array['ID'])) {
			return null;
		}
		$id = $this->array['ID'];
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		$resultObject = new New_request($result[0]);
		return $resultObject;
	}

	public function loadExtras($item)
	{
		if (isset($item['beneficiaries'])) {
			$item['beneficiaries'] = ($item['beneficiaries'] != '') ? json_decode($item['beneficiaries'], true) : [];
		}

		if (isset($item['action_timeline'])) {
			$item['action_timeline'] = ($item['action_timeline'] != '') ? json_decode($item['action_timeline'], true) : [];
		}

		if (isset($item['deduction_amount'])) {
			$item['deduction_amount'] = ($item['deduction_amount'] != '') ? json_decode($item['deduction_amount'], true) : [];
		}

		return $item;
	}


}
