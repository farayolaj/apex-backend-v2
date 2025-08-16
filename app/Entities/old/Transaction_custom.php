<?php

require_once 'application/models/Crud.php';
require_once APPPATH . 'constants/RemitaResponse.php';
require_once APPPATH . 'traits/CommonTrait.php';

/**
 * This class is automatically generated based on the structure of the table.
 * And it represent the model of the transaction_custom table
 */
class Transaction_custom extends Crud {

	/**
	 * This is the entity name equivalent to the table name
	 * @var string
	 */
	protected static $tablename = "Transaction_custom";

	/**
	 * This array contains the field that can be null
	 * @var array
	 */
	public static $nullArray = ['payment_url', 'transaction_id'];

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
	public static $typeArray = ['payment_id' => 'int', 'payment_description' => 'varchar', 'payment_option' => 'int', 'custom_users_id' => 'int', 'transaction_ref' => 'varchar', 'rrr_code' => 'varchar', 'payment_status' => 'varchar', 'beneficiary_1' => 'varchar', 'beneficiary_2' => 'varchar', 'beneficiary_3' => 'varchar', 'payment_status_description' => 'varchar', 'amount_paid' => 'varchar', 'service_charge' => 'varchar', 'total_amount' => 'varchar', 'payment_url' => 'text', 'merchant_name' => 'varchar', 'date_performed' => 'datetime', 'date_completed' => 'datetime', 'date_payment_communicated' => 'datetime', 'transaction_id' => 'varchar', 'subaccount_amount' => 'varchar', 'mainaccount_amount' => 'varchar', 'start_date' => 'datetime', 'end_date' => 'datetime'];

	/**
	 * This is a dictionary that map a field name with the label name that
	 * will be shown in a form
	 * @var array
	 */
	public static $labelArray = ['id' => '', 'payment_id' => '', 'payment_description' => '', 'payment_option' => '', 'custom_users_id' => '', 'transaction_ref' => '', 'rrr_code' => '', 'payment_status' => '', 'beneficiary_1' => '', 'beneficiary_2' => '', 'beneficiary_3' => '', 'payment_status_description' => '', 'amount_paid' => '', 'service_charge' => '', 'total_amount' => '', 'payment_url' => '', 'merchant_name' => '', 'date_performed' => '', 'date_completed' => '', 'date_payment_communicated' => '', 'transaction_id' => '', 'subaccount_amount' => '', 'mainaccount_amount' => '', 'start_date' => '', 'end_date' => ''];

	/**
	 * Associative array of fields in the table that have default value
	 * @var array
	 */
	public static $defaultArray = ['payment_status' => '021', 'subaccount_amount' => '0'];

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
	public static $relation = ['custom_users' => array('custom_users_id', 'id')
		, 'transaction' => array('transaction_id', 'id'),
	];

	/**
	 * This are the action allowed to be performed on the entity and this can
	 * be changed in the formConfig model file for flexibility
	 * @var array
	 */
	public static $tableAction = ['delete' => 'delete/transaction_custom', 'edit' => 'edit/transaction_custom'];
	static $apiSelectClause = ['id', 'payment_description', 'payment_id', 'payment_option', 'transaction_ref', 'rrr_code', 'payment_status', 'payment_status_description', 'amount_paid', 'total_amount', 'date_performed', 'date_completed', 'date_payment_communicated', 'transaction_id', 'subaccount_amount', 'mainaccount_amount', 'start_date', 'end_date'];

	public function __construct(array $array = []) {
		parent::__construct($array);
	}

	public function getPayment_descriptionFormField($value = '') {
		return "<div class='form-group'>
				<label for='payment_description'>Payment Description</label>
				<input type='text' name='payment_description' id='payment_description' value='$value' class='form-control' required />
			</div>";
	}

	public function getPayment_idFormField($value = '') {
		return "<div class='form-group'>
				<label for='payment_id'>Fee Description</label>
				<input type='text' name='payment_id' id='payment_id' value='$value' class='form-control' required />
			</div>";
	}

	public function getPayment_optionFormField($value = '') {
		return "<div class='form-group'>
				<label for='payment_option'>Payment Option</label>
				<input type='text' name='payment_option' id='payment_option' value='$value' class='form-control' required />
			</div>";
	}

	public function getCustom_users_idFormField($value = '') {
		$fk = null;
		//change the value of this variable to array('table'=>'custom_users','display'=>'custom_users_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'custom_users_name' as value from 'custom_users' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('custom_users', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='custom_users_id' id='custom_users_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
			<label for='custom_users_id'>Custom Users</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='custom_users_id' id='custom_users_id' class='form-control'>
						$option
					</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getTransaction_refFormField($value = '') {
		return "<div class='form-group'>
				<label for='transaction_ref'>Transaction Ref</label>
				<input type='text' name='transaction_ref' id='transaction_ref' value='$value' class='form-control' required />
			</div>";
	}

	public function getRrr_codeFormField($value = '') {
		return "<div class='form-group'>
				<label for='rrr_code'>Rrr Code</label>
				<input type='text' name='rrr_code' id='rrr_code' value='$value' class='form-control' required />
			</div>";
	}

	public function getPayment_statusFormField($value = '') {
		return "<div class='form-group'>
				<label for='payment_status'>Payment Status</label>
				<input type='text' name='payment_status' id='payment_status' value='$value' class='form-control' required />
			</div>";
	}

	public function getBeneficiary_1FormField($value = '') {
		return "<div class='form-group'>
				<label for='beneficiary_1'>Beneficiary 1</label>
				<input type='text' name='beneficiary_1' id='beneficiary_1' value='$value' class='form-control' required />
			</div>";
	}

	public function getBeneficiary_2FormField($value = '') {
		return "<div class='form-group'>
				<label for='beneficiary_2'>Beneficiary 2</label>
				<input type='text' name='beneficiary_2' id='beneficiary_2' value='$value' class='form-control' required />
			</div>";
	}

	public function getBeneficiary_3FormField($value = '') {
		return "<div class='form-group'>
				<label for='beneficiary_3'>Beneficiary 3</label>
				<input type='text' name='beneficiary_3' id='beneficiary_3' value='$value' class='form-control' required />
			</div>";
	}

	public function getPayment_status_descriptionFormField($value = '') {
		return "<div class='form-group'>
				<label for='payment_status_description'>Payment Status Description</label>
				<input type='text' name='payment_status_description' id='payment_status_description' value='$value' class='form-control' required />
			</div>";
	}

	public function getAmount_paidFormField($value = '') {
		return "<div class='form-group'>
				<label for='amount_paid'>Amount Paid</label>
				<input type='text' name='amount_paid' id='amount_paid' value='$value' class='form-control' required />
			</div>";
	}

	public function getService_chargeFormField($value = '') {
		return "<div class='form-group'>
				<label for='service_charge'>Service Charge</label>
				<input type='text' name='service_charge' id='service_charge' value='$value' class='form-control' required />
			</div>";
	}

	public function getTotal_amountFormField($value = '') {
		return "<div class='form-group'>
				<label for='total_amount'>Total Amount</label>
				<input type='text' name='total_amount' id='total_amount' value='$value' class='form-control' required />
			</div>";
	}

	public function getPayment_urlFormField($value = '') {
		return "<div class='form-group'>
				<label for='payment_url'>Payment Url</label>
				<input type='text' name='payment_url' id='payment_url' value='$value' class='form-control' required />
			</div>";
	}

	public function getMerchant_nameFormField($value = '') {
		return "<div class='form-group'>
				<label for='merchant_name'>Merchant Name</label>
				<input type='text' name='merchant_name' id='merchant_name' value='$value' class='form-control' required />
			</div>";
	}

	public function getDate_performedFormField($value = '') {
		return "<div class='form-group'>
				<label for='date_performed'>Date Performed</label>
				<input type='text' name='date_performed' id='date_performed' value='$value' class='form-control' required />
			</div>";
	}

	public function getDate_completedFormField($value = '') {
		return "<div class='form-group'>
				<label for='date_completed'>Date Completed</label>
				<input type='text' name='date_completed' id='date_completed' value='$value' class='form-control' required />
			</div>";
	}

	public function getDate_payment_communicatedFormField($value = '') {
		return "<div class='form-group'>
				<label for='date_payment_communicated'>Date Payment Communicated</label>
				<input type='text' name='date_payment_communicated' id='date_payment_communicated' value='$value' class='form-control' required />
			</div>";
	}

	public function getTransaction_idFormField($value = '') {
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

	public function getSubaccount_amountFormField($value = '') {
		return "<div class='form-group'>
				<label for='subaccount_amount'>Subaccount Amount</label>
				<input type='text' name='subaccount_amount' id='subaccount_amount' value='$value' class='form-control' required />
			</div>";
	}

	public function getMainaccount_amountFormField($value = '') {
		return "<div class='form-group'>
				<label for='mainaccount_amount'>Mainaccount Amount</label>
				<input type='text' name='mainaccount_amount' id='mainaccount_amount' value='$value' class='form-control' required />
			</div>";
	}

	public function getStart_dateFormField($value = '') {
		return "<div class='form-group'>
				<label for='start_date'>Start Date</label>
				<input type='text' name='start_date' id='start_date' value='$value' class='form-control' required />
			</div>";
	}

	public function getEnd_dateFormField($value = '') {
		return "<div class='form-group'>
				<label for='end_date'>End Date</label>
				<input type='text' name='end_date' id='end_date' value='$value' class='form-control' required />
			</div>";
	}

	protected function getUsers_custom() {
		$query = 'SELECT * FROM users_custom WHERE id=?';
		$result = $this->query($query, [$this->custom_users_id]);
		if (!$result) {
			return false;
		}
		loadClass($this->load, 'Users_custom');
		return new Users_custom($result[0]);
	}

	public function delete($id = NULL, &$dbObject = NULL, $type = null) {
		loadClass($this->load, 'transaction');
		return $this->transaction->delete($id, $dbObject, $type);
	}

	public function verify_transaction($rrrCode, $channel, $student = null) {
		if ($channel == 'remita') {
			$this->load->model('remita');
			$transactionRef = $this->transaction_ref ? $this->transaction_ref : null;
			if (!$transactionRef) {
				return ['status' => false, 'message' => 'Invalid transaction reference'];
			}
			$temp = $this->remita->getRemitaData($this->transaction_ref, $student, null, $transactionRef);

			// when $temp['curlStatus'] is false, the curl method is GET method
			if (!$temp['curlStatus']) {
				$extraData = $temp['extraData'];
				$response = $this->remita->remitaTransactionDetails($extraData['url'], $temp['header']);
				if (@$response[RemitaResponse::RRR] == $rrrCode && CommonTrait::isPaymentValid($response['status'])) {
					$date_payment_communicated = date('Y-m-d H:i:s');
					// update transaction data
					$record = array(
						'payment_status' => $response['status'],
						'beneficiary_1' => (isset($response['lineitems']) && array_key_exists('lineitems', $response)) ? $response['lineitems'][0]['status'] : '100',
						'beneficiary_2' => (isset($response['lineitems']) && array_key_exists('lineitems', $response)) ? $response['lineitems'][1]['status'] : '100',
						'beneficiary_3' => (isset($response['lineitems']) && array_key_exists('lineitems', $response)) ? $response['lineitems'][2]['status'] : '100',
						'payment_status_description' => $response['message'],
						'amount_paid' => $response['amount'],
						'date_completed' => (isset($response['paymentDate'])) ? $response['paymentDate'] : $response['transactiontime'],
						'date_payment_communicated' => $date_payment_communicated,
					);
					if (isset($response[RemitaResponse::RRR]) && $response[RemitaResponse::RRR] != '') {
						$record['rrr_code'] = $response[RemitaResponse::RRR];
					}
					$id = $this->id;
					$this->setArray($record);
					if (!$this->update($id)) {
						return ["status" => false, 'message' => "An error occured while processing payment"];
					}
					return ['status' => true, 'rrr_code' => $rrrCode];
				} else {
					$record = array(
						'payment_status' => (isset($response['status'])) ? $response['status'] : '100',
						'payment_status_description' => (isset($response['message'])) ? $response['message'] : 'pending',
						'amount_paid' => $response['amount'],
						'date_completed' => (isset($response['paymentDate'])) ? $response['paymentDate'] : $response['transactiontime'],
					);
					if (isset($response[RemitaResponse::RRR]) && $response[RemitaResponse::RRR] != '') {
						$record['rrr_code'] = $response[RemitaResponse::RRR];
					}
					$id = $this->id;
					$this->setArray($record);
					if (!$this->update($id)) {
						return ["status" => false, 'message' => "An error occured while processing payment"];
					}
					return ['status' => false, 'message' => (isset($response['message']) && $response['message'] != '') ? 'Transaction status: ' . strtolower($response['message']) : 'Transaction status does not exist or pending payment'];
				}
				return ["status" => false, 'message' => "An error occured while processing payment"];
			}
		}
		return ["status" => false, 'message' => "An error occured while processing payment"];
	}

	public function APIList($filterList, $queryString, $start, $len, $orderBy) {
		$paymentStatus = false;
		$tempPaymentStatus = [];
		if (isset($filterList['payment_status']) && $filterList['payment_status']) {
			$paymentStatus = true;
			$tempPaymentStatus['payment_status'] = $filterList['payment_status'];
			unset($filterList['payment_status']);
		}
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		if ($paymentStatus) {
			$value = $tempPaymentStatus['payment_status'];
			if ($value == 'paid') {
				$filterQuery .= ($filterQuery) ? " and payment_status in ('00','01') " : " where payment_status in ('00','01') ";
			} else {
				$filterQuery .= ($filterQuery) ? " and (payment_status <> '00' and payment_status <> '01') " : " where (payment_status <> '00' and payment_status <> '01') ";
			}
		}

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by id desc ";
		}

		if ($len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}

		$query = "SELECT " . buildApiClause(static::$apiSelectClause, 'transaction_custom') . " , fee_description.description as invoice_category,
		users_custom.name as fullname,email,phone_number,address,contact_person, if(payment_status = 00 || payment_status = 01, 'Paid', 'Not Paid') as paid_status,
		service_charge from transaction_custom join users_custom on users_custom.id = transaction_custom.custom_users_id join
		fee_description on fee_description.id = transaction_custom.payment_id $filterQuery";

		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->result_array();
		$res2 = $this->db->query($query2);
		$res2 = $res2->result_array();
		$res = $this->processList($res);

		return [$res, $res2];
	}

	private function processList($items): array {
		$generator = useGenerators($items);
		$payload = [];
		foreach ($generator as $item) {
			$payload[] = $this->loadExtras($item);
		}
		return $payload;
	}

	public function loadExtras($item) {
		if (isset($item['phone_number'])) {
			$item['phone_number'] = decryptData($this, $item['phone_number']);
		}

		return $item;
	}

	/**
	 * @throws Exception
	 */
	public function getTransactionCustomReceipt($rrr) {
		$query = "SELECT a.*, b.name as fullname,b.email,b.phone_number,b.address,b.contact_person,c.description from transaction_custom a
                join users_custom b on b.id = a.custom_users_id left join fee_description c on c.id = a.payment_id where a.payment_status in ('00', '01') and a.rrr_code = ?";
		$result = $this->query($query, [$rrr]);
		if (!$result) {
			return null;
		}
		$result = $result[0];
		return new Transaction_custom($result);
	}

}
