<?php
require_once('application/models/Crud.php');
require_once APPPATH . 'constants/BookstoreStatus.php';

/**
 * This class is automatically generated based on the structure of the table.
 * And it represent the model of the payment_bookstore table
 */
class Payment_bookstore extends Crud
{

	/**
	 * This is the entity name equivalent to the table name
	 * @var string
	 */
	protected static $tablename = "Payment_bookstore";

	/**
	 * This array contains the field that can be null
	 * @var array
	 */
	public static $nullArray = ['course_id', 'authors'];

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
	public static $displayField = 'course_id';

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
	public static $typeArray = ['course_id' => 'int', 'title' => 'varchar', 'authors' => 'text', 'price' => 'decimal',
		'quantity' => 'int', 'fee_description' => 'varchar', 'service_type_id' => 'varchar', 'amount' => 'varchar',
		'subaccount_amount' => 'varchar', 'service_charge' => 'int', 'created_at' => 'timestamp',
		'updated_at' => 'timestamp', 'session' => 'int', 'is_visible' => 'tinyint', 'book_type' => 'varchar'];

	/**
	 * This is a dictionary that map a field name with the label name that
	 * will be shown in a form
	 * @var array
	 */
	public static $labelArray = ['id' => '', 'course_id' => '', 'title' => '', 'authors' => '', 'price' => '',
		'quantity' => '', 'fee_description' => '', 'service_type_id' => '', 'amount' => '',
		'subaccount_amount' => '', 'service_charge' => '', 'created_at' => '', 'updated_at' => '',
		'session' => '', 'is_visible' => '', 'book_type' => ''];

	/**
	 * Associative array of fields in the table that have default value
	 * @var array
	 */
	public static $defaultArray = ['created_at' => 'current_timestamp()', 'updated_at' => 'current_timestamp()'];

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
	public static $relation = ['course' => array('course_id', 'id')
		, 'service_type' => array('service_type_id', 'id')
	];

	/**
	 * This are the action allowed to be performed on the entity and this can
	 * be changed in the formConfig model file for flexibility
	 * @var array
	 */
	public static $tableAction = ['delete' => 'delete/payment_bookstore', 'edit' => 'edit/payment_bookstore'];

	public static $apiSelectClause = ['id', 'course_id', 'title', 'authors', 'price', 'quantity',
		'is_visible', 'created_at', 'updated_at', 'book_type'];

	public function __construct(array $array = [])
	{
		parent::__construct($array);
	}

	public function getCourse_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'course','display'=>'course_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'course_name' as value from 'course' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('course', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='course_id' id='course_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
		<label for='course_id'>Course</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='course_id' id='course_id' class='form-control'>
					$option
				</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getTitleFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='title'>Title</label>
		<input type='text' name='title' id='title' value='$value' class='form-control' required />
	</div>";
	}

	public function getAuthorsFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='authors'>Authors</label>
		<input type='text' name='authors' id='authors' value='$value' class='form-control' required />
	</div>";
	}

	public function getPriceFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='price'>Price</label>
		<input type='text' name='price' id='price' value='$value' class='form-control' required />
	</div>";
	}

	public function getQuantityFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='quantity'>Quantity</label>
		<input type='text' name='quantity' id='quantity' value='$value' class='form-control' required />
	</div>";
	}

	public function getFee_descriptionFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='fee_description'>Fee Description</label>
		<input type='text' name='fee_description' id='fee_description' value='$value' class='form-control' required />
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

	public function getAmountFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='amount'>Amount</label>
		<input type='text' name='amount' id='amount' value='$value' class='form-control' required />
	</div>";
	}

	public function getSubaccount_amountFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='subaccount_amount'>Subaccount Amount</label>
		<input type='text' name='subaccount_amount' id='subaccount_amount' value='$value' class='form-control' required />
	</div>";
	}

	public function getService_chargeFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='service_charge'>Service Charge</label>
		<input type='text' name='service_charge' id='service_charge' value='$value' class='form-control' required />
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

	public function APIList($filterList, $queryString, $start, $len, $orderBy): array
	{
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		$filterQuery .= ($filterQuery ? " and " : " where ") . " a.is_visible ='1' ";

		if (!$filterValues) {
			$filterValues = [];
		}

		$bookStatus = BookstoreStatus::COMPLETED;
		$bookStatusPending = BookstoreStatus::PENDING;
		$query = "SELECT " . buildApiClause(static::$apiSelectClause, 'a') . ", 
		IF(a.course_id <> '0', b.code, '') AS course_code, 
		COALESCE(SUM(CASE WHEN c.book_status = '$bookStatus' THEN d.quantity ELSE 0 END), 0) AS quantity_sold,
    	COALESCE(SUM(CASE WHEN c.book_status = '$bookStatusPending' THEN d.quantity ELSE 0 END), 0) AS quantity_hold
		from payment_bookstore a 
		left join courses b on b.id = a.course_id 
		left join student_payment_bookstore c on c.active = '1' 
		left join student_payment_bookstore_items d on d.student_payment_bookstore_id = c.id and d.payment_bookstore_id = a.id
		 $filterQuery";
		$query .= " GROUP BY a.id, a.course_id, a.title, a.authors, a.price, a.quantity, 
    		a.is_visible, a.created_at, a.updated_at, b.code, a.book_type";

		if (isset($_GET['sortBy']) && $orderBy) {
			$query .= " order by $orderBy ";
		} else {
			$query .= " order by b.code asc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$query .= " limit $start, $len";
		}

		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->result_array();
		$res2 = $this->db->query($query2);
		$res2 = $res2->result_array();
		return [$res, $res2];
	}

	public function getBookstoreCourseLists($academicRecord): array
	{
		$level = $academicRecord->current_level;
		if ($level === '501') {
			$level = 5;
		}
		//$semester = get_setting('active_semester');
		// $session = $academicRecord->current_session;
		$session = null;
		$semester = null;
		$programme_id = $academicRecord->programme_id;
		$entryMode = $academicRecord->entry_mode;

		$query = "SELECT c.id, a.code, a.title, b.programme_id, b.semester, b.level,c.price,c.quantity from courses a 
        join payment_bookstore c on c.course_id = a.id left join course_mapping b on b.course_id = a.id  
        where b.programme_id = ? and b.mode_of_entry = ? and c.is_visible = '1' ";
		if ($session) {
			$query .= " and c.id = '$session' ";
		}
		if ($semester) {
			$query .= " and b.semester = '$semester' ";
		}
		$query .= "order by a.code asc";
		$courses = $this->query($query, [$programme_id, $entryMode]);

		$result = [];
		if (!$courses) {
			return $result;
		}

		foreach ($courses as $courseData) {
			$mappedLevel = json_decode($courseData['level'], true);
			if (is_array($mappedLevel) && in_array($level, $mappedLevel)) {
				unset($courseData['level']);
				$courseData['semester'] = (int)$courseData['semester'];
				$result[] = $courseData;
			}
		}
		return $result;
	}

	public function getPaymentBookCourse($id)
	{
		$query = "SELECT a.*, b.code from payment_bookstore a left join courses b on b.id = a.course_id 
                   where a.id = ?";
		$result = $this->query($query, [$id]);
		if (!$result) {
			return null;
		}
		return new Payment_bookstore($result[0]);
	}

	public function getBookstoreTransaction($studentID = null, $paymentStoreID = null): array
	{
		if ($paymentStoreID) {
			$query = "SELECT a.id, a.session, a.order_id, a.total_amount, a.transaction_ref, a.book_status, 
       		a.created_at, COALESCE(b.rrr_code, 'N/A') as rrr_code, b.payment_description, b.payment_status, b.amount_paid, 
       		b.date_performed, b.date_completed, c.firstname, c.lastname, c.othernames,d.matric_number,c.id as student_id
			from student_payment_bookstore a 
			left join transaction b on b.transaction_ref = a.transaction_ref 
			join students c on c.id = a.student_id 
			join academic_record d on d.student_id = c.id
			where a.active = '1' and a.id = '$paymentStoreID' order by a.created_at desc ";
		} else {
			$query = "SELECT a.id, a.session, a.order_id, a.total_amount, a.transaction_ref, a.book_status, 
       		a.created_at, b.rrr_code, b.payment_description, b.payment_status, b.amount_paid, 
       		b.date_performed, b.date_completed
			from student_payment_bookstore a left join transaction b on b.transaction_ref = a.transaction_ref 
			where a.active = '1' and a.student_id = '$studentID' order by a.order_id desc ";
		}

		$results = $this->query($query);
		if (!$results) {
			return [];
		}

		$payload = [];
		loadClass($this->load, 'student_payment_bookstore_items');
		foreach ($results as $item) {
			$itemsList = $this->getBookPaymentItems($item['id']);
			$item['book_items'] = $itemsList;
			$payload[] = $item;
		}

		return $payload;
	}

	public function getBookPaymentItems($id)
	{
		$query = "SELECT course_id, title, quantity, price, amount, payment_bookstore_id as bookstore_id,book_type from 
                student_payment_bookstore_items where student_payment_bookstore_id = ?";
		return $this->query($query, [$id]);
	}

}

