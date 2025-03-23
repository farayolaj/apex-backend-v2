<?php
namespace App\Entities;

use App\Models\Crud;

/**
 * This class is automatically generated based on the structure of the table.
 * And it represent the model of the transaction_archive table
 */
class Transaction_archive extends Crud
{

	/**
	 * This is the entity name equivalent to the table name
	 * @var string
	 */
	protected static $tablename = "Transaction_archive";

	/**
	 * This array contains the field that can be null
	 * @var array
	 */
	public static $nullArray = ['payment_url'];

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
	public static $displayField = 'payment_url';

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
	public static $typeArray = ['transaction_id' => 'int', 'payment_id' => 'varchar', 'real_payment_id' => 'int', 'payment_description' => 'varchar', 'payment_option' => 'int', 'student_id' => 'int', 'programme_id' => 'int', 'session' => 'int', 'level' => 'tinyint', 'transaction_ref' => 'varchar', 'rrr_code' => 'varchar', 'payment_status' => 'varchar', 'beneficiary_1' => 'varchar', 'beneficiary_2' => 'varchar', 'payment_status_description' => 'varchar', 'amount_paid' => 'varchar', 'penalty_fee' => 'varchar', 'service_charge' => 'varchar', 'total_amount' => 'varchar', 'payment_url' => 'text', 'date_performed' => 'datetime', 'date_completed' => 'datetime', 'date_payment_communicated' => 'datetime'];

	/**
	 * This is a dictionary that map a field name with the label name that
	 * will be shown in a form
	 * @var array
	 */
	public static $labelArray = ['ID' => '', 'transaction_id' => '', 'payment_id' => '', 'real_payment_id' => '', 'payment_description' => '', 'payment_option' => '', 'student_id' => '', 'programme_id' => '', 'session' => '', 'level' => '', 'transaction_ref' => '', 'rrr_code' => '', 'payment_status' => '', 'beneficiary_1' => '', 'beneficiary_2' => '', 'payment_status_description' => '', 'amount_paid' => '', 'penalty_fee' => '', 'service_charge' => '', 'total_amount' => '', 'payment_url' => '', 'date_performed' => '', 'date_completed' => '', 'date_payment_communicated' => ''];

	/**
	 * Associative array of fields in the table that have default value
	 * @var array
	 */
	public static $defaultArray = [];

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
	public static $relation = ['transaction' => array('transaction_id', 'id')
		, 'payment' => array('payment_id', 'id')
		, 'real_payment' => array('real_payment_id', 'id')
		, 'student' => array('student_id', 'id')
		, 'programme' => array('programme_id', 'id')
	];

	/**
	 * This are the action allowed to be performed on the entity and this can
	 * be changed in the formConfig model file for flexibility
	 * @var array
	 */
	public static $tableAction = ['delete' => 'delete/transaction_archive', 'edit' => 'edit/transaction_archive'];

	static $apiSelectClause = ['id', 'transaction_id', 'payment_id', 'real_payment_id', 'payment_description', 'payment_option', 'student_id', 'programme_id', 'session', 'level', 'transaction_ref', 'rrr_code', 'payment_status', 'beneficiary_1', 'beneficiary_2', 'beneficiary_3', 'payment_status_description', 'amount_paid', 'penalty_fee', 'service_charge', 'total_amount', 'payment_url', 'date_performed', 'date_completed', 'date_payment_communicated', 'preselected_payment', 'transaction_ref_id', 'subaccount_amount', 'mainaccount_amount'];

	public function __construct(array $array = [])
	{
		parent::__construct($array);
	}

	public function getTransaction_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'transaction','display'=>'transaction_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'transaction_name' as value from 'transaction' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('transaction', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='transaction_id' id='transaction_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
			<label for='transaction_id'>Transaction</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='transaction_id' id='transaction_id' class='form-control'>
						$option
					</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getPayment_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'payment','display'=>'payment_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'payment_name' as value from 'payment' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('payment', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='payment_id' id='payment_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
			<label for='payment_id'>Payment</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='payment_id' id='payment_id' class='form-control'>
						$option
					</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getReal_payment_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'real_payment','display'=>'real_payment_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'real_payment_name' as value from 'real_payment' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('real_payment', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='real_payment_id' id='real_payment_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
			<label for='real_payment_id'>Real Payment</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='real_payment_id' id='real_payment_id' class='form-control'>
						$option
					</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getPayment_descriptionFormField($value = '')
	{
		return "<div class='form-group'>
				<label for='payment_description'>Payment Description</label>
				<input type='text' name='payment_description' id='payment_description' value='$value' class='form-control' required />
			</div>";
	}

	public function getPayment_optionFormField($value = '')
	{
		return "<div class='form-group'>
				<label for='payment_option'>Payment Option</label>
				<input type='text' name='payment_option' id='payment_option' value='$value' class='form-control' required />
			</div>";
	}

	public function getStudent_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'student','display'=>'student_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'student_name' as value from 'student' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('student', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='student_id' id='student_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
			<label for='student_id'>Student</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='student_id' id='student_id' class='form-control'>
						$option
					</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getProgramme_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'programme','display'=>'programme_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'programme_name' as value from 'programme' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('programme', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='programme_id' id='programme_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
			<label for='programme_id'>Programme</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='programme_id' id='programme_id' class='form-control'>
						$option
					</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getSessionFormField($value = '')
	{
		return "<div class='form-group'>
				<label for='session'>Session</label>
				<input type='text' name='session' id='session' value='$value' class='form-control' required />
			</div>";
	}

	public function getLevelFormField($value = '')
	{
		return "<div class='form-group'>
				<label for='level'>Level</label>
				<input type='text' name='level' id='level' value='$value' class='form-control' required />
			</div>";
	}

	public function getTransaction_refFormField($value = '')
	{
		return "<div class='form-group'>
				<label for='transaction_ref'>Transaction Ref</label>
				<input type='text' name='transaction_ref' id='transaction_ref' value='$value' class='form-control' required />
			</div>";
	}

	public function getRrr_codeFormField($value = '')
	{
		return "<div class='form-group'>
				<label for='rrr_code'>Rrr Code</label>
				<input type='text' name='rrr_code' id='rrr_code' value='$value' class='form-control' required />
			</div>";
	}

	public function getPayment_statusFormField($value = '')
	{
		return "<div class='form-group'>
				<label for='payment_status'>Payment Status</label>
				<input type='text' name='payment_status' id='payment_status' value='$value' class='form-control' required />
			</div>";
	}

	public function getBeneficiary_1FormField($value = '')
	{
		return "<div class='form-group'>
				<label for='beneficiary_1'>Beneficiary 1</label>
				<input type='text' name='beneficiary_1' id='beneficiary_1' value='$value' class='form-control' required />
			</div>";
	}

	public function getBeneficiary_2FormField($value = '')
	{
		return "<div class='form-group'>
				<label for='beneficiary_2'>Beneficiary 2</label>
				<input type='text' name='beneficiary_2' id='beneficiary_2' value='$value' class='form-control' required />
			</div>";
	}

	public function getPayment_status_descriptionFormField($value = '')
	{
		return "<div class='form-group'>
				<label for='payment_status_description'>Payment Status Description</label>
				<input type='text' name='payment_status_description' id='payment_status_description' value='$value' class='form-control' required />
			</div>";
	}

	public function getAmount_paidFormField($value = '')
	{
		return "<div class='form-group'>
				<label for='amount_paid'>Amount Paid</label>
				<input type='text' name='amount_paid' id='amount_paid' value='$value' class='form-control' required />
			</div>";
	}

	public function getPenalty_feeFormField($value = '')
	{
		return "<div class='form-group'>
				<label for='penalty_fee'>Penalty Fee</label>
				<input type='text' name='penalty_fee' id='penalty_fee' value='$value' class='form-control' required />
			</div>";
	}

	public function getService_chargeFormField($value = '')
	{
		return "<div class='form-group'>
				<label for='service_charge'>Service Charge</label>
				<input type='text' name='service_charge' id='service_charge' value='$value' class='form-control' required />
			</div>";
	}

	public function getTotal_amountFormField($value = '')
	{
		return "<div class='form-group'>
				<label for='total_amount'>Total Amount</label>
				<input type='text' name='total_amount' id='total_amount' value='$value' class='form-control' required />
			</div>";
	}

	public function getPayment_urlFormField($value = '')
	{
		return "<div class='form-group'>
				<label for='payment_url'>Payment Url</label>
				<input type='text' name='payment_url' id='payment_url' value='$value' class='form-control' required />
			</div>";
	}

	public function getDate_performedFormField($value = '')
	{
		return "<div class='form-group'>
				<label for='date_performed'>Date Performed</label>
				<input type='text' name='date_performed' id='date_performed' value='$value' class='form-control' required />
			</div>";
	}

	public function getDate_completedFormField($value = '')
	{
		return "<div class='form-group'>
				<label for='date_completed'>Date Completed</label>
				<input type='text' name='date_completed' id='date_completed' value='$value' class='form-control' required />
			</div>";
	}

	public function getDate_payment_communicatedFormField($value = '')
	{
		return "<div class='form-group'>
				<label for='date_payment_communicated'>Date Payment Communicated</label>
				<input type='text' name='date_payment_communicated' id='date_payment_communicated' value='$value' class='form-control' required />
			</div>";
	}

	protected function getTransaction()
	{
		$query = 'SELECT * FROM transaction WHERE id=?';
		if (!isset($this->array['transaction_id'])) {
			return null;
		}
		$id = $this->array['transaction_id'];
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		return new \App\Entities\Transaction($result[0]);
	}

	public function APIList($filterList, $queryString, $start, $len, $orderBy)
	{
		$paymentType = request()->getGet('payment_type') ?? null;
		$rrr = request()->getGet('rrr') ?? null;
		$department = request()->getGet('department') ?? null;
		$session = request()->getGet('session') ?? null;
		$transactionType = request()->getGet('transaction_type') ?: 'student';
		$q = request()->getGet('q') ?? null;

		$limit = '';
		if (request()->getGet('start') && $len) {
			$limit = " limit $start, $len";
		}

		$where = '';
		$where2 = '';
		$where3 = '';
		$skipCustom = false;

		if ($paymentType) {
			$paymentType = $this->db->escapeString($paymentType);
			$where .= ($where ? ' and ' : ' where ') . " a.payment_id='{$paymentType}'";
		}

		if ($rrr) {
			$rrr = $this->db->escapeString($rrr);
			$where .= ($where ? ' and ' : ' where ') . " a.rrr_code='{$rrr}'";
		}

		if ($transactionType) {
			$transactionTypeWhere = $this->db->escapeString($transactionType);
			$transactionTypeWhere = ($transactionTypeWhere == 'student') ? 'transaction' : 'applicant_transaction';
			$where .= ($where ? ' and ' : ' where ') . " a.source_table='{$transactionTypeWhere}'";
		}

		if ($department) {
			$department = $this->db->escapeString($department);
			$skipCustom = true;
			$where .= ($where ? ' and ' : ' where ') . " e.department_id='{$department}'";
		}

		if ($session) {
			$session = $this->db->escapeString($session);
			$skipCustom = true;
			$where .= ($where ? ' and ' : ' where ') . " a.session='{$session}'";
		}

		if ($q) {
			$where2 = $where;
			$searchList = [
				'firstname', 'lastname', 'matric_number', 'rrr_code', 'payment_description', 'transaction_ref', 'amount_paid',
				'e.name'
			];
			$searchList2 = [
				'firstname', 'lastname', 'b.applicant_id', 'rrr_code', 'payment_description', 'transaction_ref', 'amount_paid',
				'e.name'
			];

			$queryString = buildCustomSearchString($searchList, $q);
			$queryString2 = buildCustomSearchString($searchList2, $q);
			$where .= ($where ? ' and ' : ' where ') . " ($queryString) ";
			$where2 .= ($where2 ? ' and ' : ' where ') . " ($queryString2) ";
		}

		$query = "SELECT SQL_CALC_FOUND_ROWS a.id as id,a.student_id, a.transaction_id, concat(firstname, ' ',lastname) as fullname,
		matric_number as application_number, payment_description as descrip,transaction_ref,rrr_code,date_performed,payment_status,
		a.mainaccount_amount as ui_amount,a.subaccount_amount as dlc_amount,(a.mainaccount_amount+a.subaccount_amount) as total_amount,
		a.total_amount as cum_amount,timestamp(date_completed) as orderBy,f.name as department_name,a.service_charge as debit_note,
		b.reg_num,e.name as programme_name,g.date as session_text,c.description as payment_type,a.real_payment_id,'student_trans' as trans_type 
		from transaction_archive a left join payment h on h.id = a.real_payment_id left join students b on b.id = a.student_id 
		left join academic_record d on d.student_id = b.id left join fee_description c on c.id = a.payment_id left join 
		programme e on e.id = a.programme_id left join department f on f.id = e.department_id left join sessions g on g.id= a.session {$where} ";

		if ($transactionType == 'applicant') {
			$where = $where2 ?: $where;
			$query = "SELECT a.id as id,a.student_id,a.transaction_id, concat(firstname, ' ',lastname) as fullname,b.applicant_id as application_number, 
       		payment_description as descrip,transaction_ref,rrr_code,date_performed,payment_status,a.mainaccount_amount as ui_amount,
       		a.subaccount_amount as dlc_amount,(a.mainaccount_amount+a.subaccount_amount) as total_amount,a.total_amount as cum_amount,
			timestamp(date_completed) as orderBy,f.name as department_name,a.service_charge as debit_note,b.applicant_id as reg_num,
			e.name as programme_name,g.date as session_text,c.description as payment_type,a.payment_id as real_payment_id,
			'admission_trans' as trans_type from transaction_archive a join applicants b on b.id = a.student_id join applicant_payment ap 
			on ap.id = a.payment_id join fee_description c on c.id = ap.description join programme e on e.id = b.programme_id join department f 
			on f.id = e.department_id join sessions g on g.id = a.session {$where} ";
		}

		if ($transactionType == 'custom') {
			$where = $where2 ?: $where;
			$query = "SELECT a.id as id,a.student_id,a.transaction_id, concat(firstname, ' ',lastname) as fullname,b.applicant_id as application_number, 
       		payment_description as descrip,transaction_ref,rrr_code,date_performed,payment_status,a.mainaccount_amount as ui_amount,
       		a.subaccount_amount as dlc_amount,(a.mainaccount_amount+a.subaccount_amount) as total_amount,a.total_amount as cum_amount,
			timestamp(date_completed) as orderBy,f.name as department_name,a.service_charge as debit_note,b.applicant_id as reg_num,
			e.name as programme_name,g.date as session_text,c.description as payment_type,a.payment_id as real_payment_id,
			'admission_trans' as trans_type from transaction_archive a join applicants b on b.id = a.student_id join applicant_payment ap 
			on ap.id = a.payment_id join fee_description c on c.id = ap.description join programme e on e.id = b.programme_id join department f 
			on f.id = e.department_id join sessions g on g.id = a.session {$where} ";
		}

		if (isset($_GET['sortBy']) && $orderBy) {
			$query .= " order by $orderBy {$limit}";
		} else {
			$query .= " order by id desc {$limit}";
		}

		// echo $query;exit;
		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$query = $this->db->query($query);
		$query2 = $this->db->query($query2);
		$result = $query->getResultArray();
		$res2 = $query2->getResultArray();
		return [$result, $res2];
	}


}
