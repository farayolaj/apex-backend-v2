<?php
require_once('application/models/Crud.php');

require_once APPPATH . 'traits/CommonTrait.php';
require_once APPPATH . 'constants/TransactionCode.php';
require_once APPPATH . 'constants/BookstoreStatus.php';

/**
 * This class is automatically generated based on the structure of the table.
 * And it represent the model of the student_payment_bookstore table
 */
class Student_payment_bookstore extends Crud
{
	use CommonTrait;

	/**
	 * This is the entity name equivalent to the table name
	 * @var string
	 */
	protected static $tablename = "Student_payment_bookstore";

	/**
	 * This array contains the field that can be null
	 * @var array
	 */
	public static $nullArray = ['total_amount', 'service_charge', 'reserved_until', 'book_status', 'active', 'created_at', 'updated_at', 'payment_id', 'amount'];

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
	public static $displayField = 'order_id';

	/**
	 * This array contains the fields that are unique
	 * @var array
	 */
	public static $uniqueArray = ['order_id', 'transaction_ref'];

	/**
	 * This is an associative array containing the fieldname and the datatype
	 * of the field
	 * @var array
	 */
	public static $typeArray = ['student_id' => 'int', 'session' => 'int', 'order_id' => 'varchar', 'total_amount' => 'decimal', 'service_charge' => 'decimal', 'transaction_ref' => 'varchar', 'reserved_until' => 'varchar', 'book_status' => 'varchar', 'active' => 'tinyint', 'created_at' => 'timestamp', 'updated_at' => 'timestamp', 'payment_id' => 'int', 'amount' => 'decimal', 'service_type_id' => 'varchar'];

	/**
	 * This is a dictionary that map a field name with the label name that
	 * will be shown in a form
	 * @var array
	 */
	public static $labelArray = ['id' => '', 'student_id' => '', 'session' => '', 'order_id' => '', 'total_amount' => '', 'service_charge' => '', 'transaction_ref' => '', 'reserved_until' => '', 'book_status' => '', 'active' => '', 'created_at' => '', 'updated_at' => '', 'payment_id' => '', 'amount' => '', 'service_type_id' => ''];

	/**
	 * Associative array of fields in the table that have default value
	 * @var array
	 */
	public static $defaultArray = ['book_status' => 'pending', 'active' => '1', 'created_at' => 'current_timestamp()', 'updated_at' => 'current_timestamp()'];

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
	public static $relation = ['student' => array('student_id', 'id')
		, 'order' => array('order_id', 'id')
		, 'payment' => array('payment_id', 'id')
		, 'service_type' => array('service_type_id', 'id')
	];

	/**
	 * This are the action allowed to be performed on the entity and this can
	 * be changed in the formConfig model file for flexibility
	 * @var array
	 */
	public static $tableAction = ['delete' => 'delete/student_payment_bookstore', 'edit' => 'edit/student_payment_bookstore'];

	public function __construct(array $array = [])
	{
		parent::__construct($array);
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

	public function getSessionFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='session'>Session</label>
		<input type='text' name='session' id='session' value='$value' class='form-control' required />
	</div>";
	}

	public function getOrder_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'order','display'=>'order_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'order_name' as value from 'order' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('order', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='order_id' id='order_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
		<label for='order_id'>Order</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='order_id' id='order_id' class='form-control'>
					$option
				</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getTotal_amountFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='total_amount'>Total Amount</label>
		<input type='text' name='total_amount' id='total_amount' value='$value' class='form-control' required />
	</div>";
	}

	public function getService_chargeFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='service_charge'>Service Charge</label>
		<input type='text' name='service_charge' id='service_charge' value='$value' class='form-control' required />
	</div>";
	}

	public function getTransaction_refFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='transaction_ref'>Transaction Ref</label>
		<input type='text' name='transaction_ref' id='transaction_ref' value='$value' class='form-control' required />
	</div>";
	}

	public function getReserved_untilFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='reserved_until'>Reserved Until</label>
		<input type='text' name='reserved_until' id='reserved_until' value='$value' class='form-control' required />
	</div>";
	}

	public function getBook_statusFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='book_status'>Book Status</label>
		<input type='text' name='book_status' id='book_status' value='$value' class='form-control' required />
	</div>";
	}

	public function getActiveFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='active'>Active</label>
		<input type='text' name='active' id='active' value='$value' class='form-control' required />
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

	public function getAmountFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='amount'>Amount</label>
		<input type='text' name='amount' id='amount' value='$value' class='form-control' required />
	</div>";
	}

	public function getService_type_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'service_type','display'=>'service_type_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'service_type_name' as value from 'service_type' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('service_type', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='service_type_id' id='service_type_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
		<label for='service_type_id'>Service Type</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='service_type_id' id='service_type_id' class='form-control'>
					$option
				</select>";
			$result .= "</div>";
			return $result;
		}

	}


	protected function getStudent()
	{
		$query = 'SELECT * FROM student WHERE id=?';
		if (!isset($this->array['ID'])) {
			return null;
		}
		$id = $this->array['ID'];
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		include_once('Students.php');
		$resultObject = new Students($result[0]);
		return $resultObject;
	}

	public function cancelBookstoreQuantity($studentPaymentBookstoreID)
	{
		loadClass($this->load, 'payment_bookstore');
		$getItems = $this->payment_bookstore->getBookPaymentItems($studentPaymentBookstoreID);
		if ($getItems) {
			foreach ($getItems as $item) {
				$qty = $item['quantity'];
				$paymentBookstore = get_single_record($this, 'payment_bookstore', [
					'id' => $item['bookstore_id']
				]);
				$availQty = $paymentBookstore->quantity;
				$qty = $availQty + $qty;
				if (!update_record($this, 'payment_bookstore', 'id', $item['bookstore_id'], [
					'quantity' => $qty
				])) {
					return false;
				}
			}
			return true;
		}
		return false;
	}

	public function bookstorePaymentCancel($orderID, $studentID = null)
	{
		loadClass($this->load, 'student_payment_bookstore');
		loadClass($this->load, 'transaction');

		if (!$orderID) {
			return [false, "Invalid book order supplied"];
		}
		$whereData = [
			'order_id' => $orderID,
		];
		if ($studentID) {
			$whereData['student_id'] = $studentID;
		}
		$studentPaymentBookStore = $this->student_payment_bookstore->getWhere($whereData, $count, 0, 1, false);
		if (!$studentPaymentBookStore) {
			return [false, "Invalid book payment order."];
		}
		$studentPaymentBookStore = $studentPaymentBookStore[0];
		if ($studentPaymentBookStore->book_status == BookstoreStatus::CANCELLED) {
			return [false, "Book payment order has already been cancelled."];
		}

		if ($studentPaymentBookStore->book_status == BookstoreStatus::COMPLETED) {
			return [false, "Book payment order has already been completed."];
		}

		$this->db->trans_begin();
		$transaction = $this->transaction->getWhere(['transaction_ref' => $studentPaymentBookStore->transaction_ref], $count, 0, 1, false);
		if ($transaction) {
			$transaction = $transaction[0];
			if (self::isPaymentValid($transaction->payment_status)) {
				return [false, "Unable to cancel book payment. Transaction already completed."];
			}
			$transaction->payment_status = TransactionCode::PENDING;
			$id = $transaction->id;
			if (!$transaction->update($id)) {
				$this->db->trans_rollback();
				return [false, "Unable to cancel book payment transaction."];
			}
		}

		if (!$studentPaymentBookStore->cancelBookstoreQuantity($studentPaymentBookStore->id)) {
			$this->db->trans_rollback();
			return [false, "Book payment cancellation failed."];
		}

		$studentPaymentBookStore->book_status = BookstoreStatus::CANCELLED;
		if (!$studentPaymentBookStore->update($studentPaymentBookStore->id)) {
			$this->db->trans_rollback();
			return [false, "Unable to cancel book payment order."];
		}

		$this->db->trans_commit();
		return [true, "Book payment cancelled successfully."];
	}


}

