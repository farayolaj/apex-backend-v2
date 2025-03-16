<?php
require_once 'application/models/Crud.php';
require_once APPPATH . 'traits/CommonTrait.php';

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the payment table.
 */
class Payment extends Crud
{
	protected static $tablename = 'Payment';
	/* this array contains the field that can be null*/
	static $nullArray = array('fee_category', 'prerequisite_fee', 'preselected_fee');
	static $compositePrimaryKey = array();
	static $uploadDependency = array();
	/*this array contains the fields that are unique*/
	static $uniqueArray = array('payment_code');
	/*this is an associative array containing the fieldname and the type of the field*/
	static $typeArray = array('amount' => 'varchar', 'description' => 'varchar', 'options' => 'tinyint', 'fee_category' => 'tinyint', 'programme' => 'text', 'session' => 'int', 'level' => 'text', 'prerequisite_fee' => 'int', 'preselected_fee' => 'int', 'entry_mode' => 'text', 'level_to_include' => 'text', 'entry_mode_to_include' => 'text', 'date_due' => 'varchar', 'service_type_id' => 'varchar', 'penalty_fee' => 'int', 'service_charge' => 'int', 'status' => 'tinyint', 'is_visible' => 'tinyint', 'date_created' => 'datetime', 'fee_breakdown' => 'text', 'discount_amount' => 'varchar', 'payment_code' => 'text');
	/*this is a dictionary that map a field name with the label name that will be shown in a form*/
	static $labelArray = array('id' => '', 'amount' => '', 'description' => '', 'options' => '', 'fee_category' => '', 'programme' => '', 'session' => '', 'level' => '', 'prerequisite_fee' => '', 'preselected_fee' => '', 'entry_mode' => '', 'level_to_include' => '', 'entry_mode_to_include' => '', 'date_due' => '', 'service_type_id' => '', 'penalty_fee' => '', 'service_charge' => '', 'status' => '', 'is_visible' => '', 'date_created' => '', 'fee_breakdown' => '', 'discount_amount' => '', 'payment_code' => '');
	/*associative array of fields that have default value*/
	static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
	static $documentField = array(); //array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

	static $relation = array('accommodation_transaction' => array(array('ID', 'payment_id', 1))
	, 'accommodation_transaction_archive' => array(array('ID', 'payment_id', 1))
	, 'applicant_transaction' => array(array('ID', 'payment_id', 1))
	, 'transaction' => array(array('ID', 'payment_id', 1))
	, 'transaction1' => array(array('ID', 'payment_id', 1))
	, 'transaction2' => array(array('ID', 'payment_id', 1))
	, 'transaction_archive' => array(array('ID', 'payment_id', 1)),
	);
	static $tableAction = array('enable' => 'getEnabled', 'delete' => 'delete/payment', 'edit' => 'edit/payment');

	function __construct($array = array())
	{
		parent::__construct($array);
	}

	function getAmountFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='amount' >Amount</label>
		<input type='text' name='amount' id='amount' value='$value' class='form-control' required />
</div> ";

	}

	function getDescriptionFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='description' >Description</label>
		<input type='text' name='description' id='description' value='$value' class='form-control' required />
</div> ";

	}

	function getOptionsFormField($value = '')
	{

		return "<div class='form-group'>
	<label class='form-checkbox'>Options</label>
	<select class='form-control' id='options' name='options' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

	}

	function getFee_categoryFormField($value = '')
	{

		return "<div class='form-group'>
	<label class='form-checkbox'>Fee Category</label>
	<select class='form-control' id='fee_category' name='fee_category' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

	}

	function getProgrammeFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='programme' >Programme</label>
<textarea id='programme' name='programme' class='form-control' required>$value</textarea>
</div> ";

	}

	function getSessionFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='session' >Session</label><input type='number' name='session' id='session' value='$value' class='form-control' required />
</div> ";

	}

	function getLevelFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='level' >Level</label>
<textarea id='level' name='level' class='form-control' required>$value</textarea>
</div> ";

	}

	function getPrerequisite_feeFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='prerequisite_fee' >Prerequisite Fee</label><input type='number' name='prerequisite_fee' id='prerequisite_fee' value='$value' class='form-control'  />
</div> ";

	}

	function getPreselected_feeFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='preselected_fee' >CoursePack Fee</label><input type='number' name='preselected_fee' id='preselected_fee' value='$value' class='form-control'  />
</div> ";

	}

	function getEntry_modeFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='entry_mode' >Entry Mode</label>
<textarea id='entry_mode' name='entry_mode' class='form-control' required>$value</textarea>
</div> ";

	}

	function getLevel_to_includeFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='level_to_include' >Level To Include</label>
<textarea id='level_to_include' name='level_to_include' class='form-control' required>$value</textarea>
</div> ";

	}

	function getEntry_mode_to_includeFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='entry_mode_to_include' >Entry Mode To Include</label>
<textarea id='entry_mode_to_include' name='entry_mode_to_include' class='form-control' required>$value</textarea>
</div> ";

	}

	function getDate_dueFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='date_due' >Date Due</label>
		<input type='text' name='date_due' id='date_due' value='$value' class='form-control' required />
</div> ";

	}

	function getService_type_idFormField($value = '')
	{
		$fk = null; //change the value of this variable to array('table'=>'service_type','display'=>'service_type_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

		if (is_null($fk)) {
			return $result = "<input type='hidden' value='$value' name='service_type_id' id='service_type_id' class='form-control' />
			";
		}
		if (is_array($fk)) {
			$result = "<div class='form-group'>
		<label for='service_type_id'>Service Type Id</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='service_type_id' id='service_type_id' class='form-control'>
			$option
		</select>";
		}
		$result .= "</div>";
		return $result;

	}

	function getPenalty_feeFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='penalty_fee' >Penalty Fee</label><input type='number' name='penalty_fee' id='penalty_fee' value='$value' class='form-control' required />
</div> ";

	}

	function getService_chargeFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='service_charge' >Service Charge</label><input type='number' name='service_charge' id='service_charge' value='$value' class='form-control' required />
</div> ";

	}

	function getDiscount_amountFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='discount_amount' >Discount Amount</label><input type='number' name='discount_amount' id='discount_amount' value='$value' class='form-control' required />
</div> ";

	}

	function getStatusFormField($value = '')
	{

		return "<div class='form-group'>
	<label class='form-checkbox'>Status</label>
	<select class='form-control' id='status' name='status' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

	}

	function getIs_visibleFormField($value = '')
	{

		return "<div class='form-group'>
	<label class='form-checkbox'>Is Visible</label>
	<select class='form-control' id='is_visible' name='is_visible' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

	}

	function getDate_createdFormField($value = '')
	{

		return " ";

	}

	protected function getAccommodation_transaction()
	{
		$query = 'SELECT * FROM accommodation_transaction WHERE payment_id=?';
		$id = $this->array['ID'];
		$result = $this->db->query($query, array($id));
		$result = $result->result_array();
		if (empty($result)) {
			return false;
		}
		include_once 'Accommodation_transaction.php';
		$resultobjects = array();
		foreach ($result as $value) {
			$resultObjects[] = new Accommodation_transaction($value);
		}

		return $resultObjects;
	}

	protected function getAccommodation_transaction_archive()
	{
		$query = 'SELECT * FROM accommodation_transaction_archive WHERE payment_id=?';
		$id = $this->array['ID'];
		$result = $this->db->query($query, array($id));
		$result = $result->result_array();
		if (empty($result)) {
			return false;
		}
		include_once 'Accommodation_transaction_archive.php';
		$resultobjects = array();
		foreach ($result as $value) {
			$resultObjects[] = new Accommodation_transaction_archive($value);
		}

		return $resultObjects;
	}

	protected function getApplicant_transaction()
	{
		$query = 'SELECT * FROM applicant_transaction WHERE payment_id=?';
		$id = $this->array['ID'];
		$result = $this->db->query($query, array($id));
		$result = $result->result_array();
		if (empty($result)) {
			return false;
		}
		include_once 'Applicant_transaction.php';
		$resultobjects = array();
		foreach ($result as $value) {
			$resultObjects[] = new Applicant_transaction($value);
		}

		return $resultObjects;
	}

	protected function getTransaction()
	{
		$query = 'SELECT * FROM transaction WHERE payment_id=?';
		$id = $this->array['ID'];
		$result = $this->db->query($query, array($id));
		$result = $result->result_array();
		if (empty($result)) {
			return false;
		}
		include_once 'Transaction.php';
		$resultobjects = array();
		foreach ($result as $value) {
			$resultObjects[] = new Transaction($value);
		}

		return $resultObjects;
	}

	protected function getTransaction1()
	{
		$query = 'SELECT * FROM transaction1 WHERE payment_id=?';
		$id = $this->array['ID'];
		$result = $this->db->query($query, array($id));
		$result = $result->result_array();
		if (empty($result)) {
			return false;
		}
		include_once 'Transaction1.php';
		$resultobjects = array();
		foreach ($result as $value) {
			$resultObjects[] = new Transaction1($value);
		}

		return $resultObjects;
	}

	protected function getTransaction2()
	{
		$query = 'SELECT * FROM transaction2 WHERE payment_id=?';
		$id = $this->array['ID'];
		$result = $this->db->query($query, array($id));
		$result = $result->result_array();
		if (empty($result)) {
			return false;
		}
		include_once 'Transaction2.php';
		$resultobjects = array();
		foreach ($result as $value) {
			$resultObjects[] = new Transaction2($value);
		}

		return $resultObjects;
	}

	protected function getTransaction_archive()
	{
		$query = 'SELECT * FROM transaction_archive WHERE payment_id=?';
		$id = $this->array['ID'];
		$result = $this->db->query($query, array($id));
		$result = $result->result_array();
		if (empty($result)) {
			return false;
		}
		include_once 'Transaction_archive.php';
		$resultobjects = array();
		foreach ($result as $value) {
			$resultObjects[] = new Transaction_archive($value);
		}

		return $resultObjects;
	}

	public function APIList($filterList, $queryString, $start, $len, $orderBy)
	{
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = $temp[0];
		$filterValues = $temp[1];
		if ($filterQuery || $queryString) {
			$filterQuery .= ($filterQuery ? ' and ' : ' where ') . $queryString;
		}

		if (isset($_GET['sortBy']) && $orderBy) {
			$sortDirection = ($_GET['sortDirection'] == 'down') ? 'desc' : 'asc';
			if ($_GET['sortBy'] == 'description_name') {
				$filterQuery .= "order by fee_description.description $sortDirection";
			} else {
				$filterQuery .= " order by $orderBy ";
			}
		} else {
			$filterQuery .= " order by id desc ";
		}

		if ($len && isset($_GET['start'])) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$filterQuery .= " limit $start, $len";
		}

		if (!$filterValues) {
			$filterValues = [];
		}

		$query = "SELECT SQL_CALC_FOUND_ROWS payment.* from payment left join fee_description on fee_description.id = payment.description $filterQuery";
		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->result_array();
		$res2 = $this->db->query($query2);
		$res2 = $res2->result_array();
		$res = $this->processList($res);

		return [$res, $res2];
	}

	private function processList($items)
	{
		loadClass($this->load, 'fee_description');
		loadClass($this->load, 'programme');
		for ($i = 0; $i < count($items); $i++) {
			$items[$i] = $this->loadExtras($items[$i]);
		}
		return $items;
	}

	private function loadExtras($item)
	{
		if ($item['description']) {
			$description = $this->fee_description->getWhere(['id' => $item['description']]);
			$item['description_name'] = ($description) ? $description[0]->description : '';
		}

		if ($item['options']) {
			$item['options'] = paymentOptionsType($item['options'], true);
		}

		if ($item['fee_category']) {
			$item['fee_category'] = paymentCategoryType($item['fee_category'], true);
		}

		$item['level'] = ($item['level'] != '') ? json_decode($item['level'], true) : [];
		$item['programme'] = ($item['programme'] != '') ? $this->processProgramme(json_decode($item['programme'])) : [];
		$item['entry_mode'] = ($item['entry_mode'] != '') ? json_decode($item['entry_mode'], true) : [];
		$item['level_to_include'] = ($item['level_to_include'] != '') ? json_decode($item['level_to_include'], true) : [];
		$item['entry_mode_to_include'] = ($item['entry_mode_to_include'] != '') ? json_decode($item['entry_mode_to_include'], true) : [];
		$item['prerequisite_fee'] = $this->convertFeeToArray($item['prerequisite_fee']);

		if ($item['date_due']) {
			$item['date_due'] = date('Y-m-d', strtotime(str_replace('/', '-', $item['date_due'])));
		}
		return $item;
	}

	private function convertFeeToArray($value)
	{
		$return = [];
		if ($value != '') {
			$value = json_decode($value, true);
			// a fail safe for values not changed
			if (is_int($value)) {
				$return = [(string)$value];
			} else {
				$return = $value;
			}
		}
		return $return;
	}

	private function processProgramme($items)
	{
		$content = [];
		if ($items && !empty($items)) {
			foreach ($items as $item) {
				$name = $this->programme->getProgrammeById($item);
				$item = [
					'id' => $item,
					'value' => $name,
				];
				$content[] = $item;
			}
		}
		return $content;
	}

	public function session()
	{
		$query = "SELECT * from sessions where id=?";
		$result = $this->query($query, [$this->session_id]);
		if (!$result) {
			return false;
		}
		loadClass($this->load, 'sessions');
		return new Sessions($result[0]);
	}

	public function getSuccessTransaction($student, $session, $transactionRef = null)
	{
		// the reason for not using payment_id column here is that it's not unique
		// since we're only using student_id to validate the payment and not using level alongside the payment_id.
		// Hence, unfit for the validation
		$query = "SELECT * from transaction where real_payment_id=? and payment_status in ('00','01')";
		$param = [$this->id];
		if ($student) {
			$query .= " and student_id = ? ";
			$param[] = $student;
		}
		if ($session) {
			$query .= " and session = ? ";
			$param[] = $session;
		}
		if ($transactionRef) {
			$query .= " and transaction_ref = ? ";
			$param[] = $transactionRef;
		}

		$result = $this->query($query, $param);
		if (!$result) {
			return false;
		}
		loadClass($this->load, 'transaction');
		return new Transaction($result[0]);
	}

	public function getSuccessTransactionByDescription($student, $session = null, $level = null, $transactionRef = null, $useAnySuccessfulPayment = false)
	{
		$query = "SELECT * from transaction where payment_id=? and payment_status in ('00','01')";
		$param = [$this->description];
		if ($student) {
			$query .= " and student_id = ? ";
			$param[] = $student;
		}
		if ($session) {
			$query .= " and session = ? ";
			$param[] = $session;
		}
		if ($level) {
			$query .= " and level = ? ";
			$param[] = $level;
		}
		if ($transactionRef && !$useAnySuccessfulPayment) {
			$query .= " and transaction_ref = ? ";
			$param[] = $transactionRef;
		}

		$result = $this->query($query, $param);
		if (!$result) {
			return false;
		}
		loadClass($this->load, 'transaction');
		return new Transaction($result[0]);
	}

	public function getTransactionByOption($student, $paymentOption, $session = null, $level = null)
	{
		$query = "SELECT * from transaction where payment_id=? and payment_option = ?";
		$param = [$this->description, $paymentOption];
		if ($student) {
			$query .= " and student_id = ? ";
			$param[] = $student;
		}
		if ($session) {
			$query .= " and session = ? ";
			$param[] = $session;
		}
		if ($level) {
			$query .= " and level = ? ";
			$param[] = $level;
		}

		$result = $this->query($query, $param);
		if (!$result) {
			return false;
		}
		loadClass($this->load, 'transaction');
		return new Transaction($result[0]);
	}

	public function getSuccessTransactionByOption($student, $payment_id, $session = null, $level = null)
	{
		// payment_option(1.1B) means complete payment
		$query = "SELECT * from transaction where student_id = ? and payment_id=? and payment_option in ('1','1B') and payment_status in ('00','01')";
		$param = [$student, $payment_id];
		if ($session) {
			$query .= " and session = ? ";
			$param[] = $session;
		}
		if ($level) {
			$query .= " and level = ? ";
			$param[] = $level;
		}

		$result = $this->query($query, $param);
		if (!$result) {
			return false;
		}
		loadClass($this->load, 'transaction');
		return new Transaction($result[0]);
	}

	public function getPaymentTransaction($payment, $student, $session = null, $level = null, $code = null)
	{
		$query = "SELECT * from transaction where payment_id=? and payment_status in ('00','01') and student_id = ?";
		if ($session) {
			$query .= " and session = '$session'";
		}
		if ($level) {
			$query .= " and level = '$level'";
		}
		if ($code) {
			$query .= " and payment_option = '$code'";
		}

		$result = $this->query($query, [$payment, $student]);
		if (!$result) {
			return false;
		}
		loadClass($this->load, 'transaction');
		return new Transaction($result[0]);
	}

	public function getPendingSundryPaymentTransaction($student)
	{
		$query = "SELECT a.* from transaction a where a.payment_id=? and payment_status not in ('00','01') and student_id = ? order by a.id desc";
		$result = $this->query($query, [$this->description, $student]);
		if (!$result) {
			return false;
		}
		loadClass($this->load, 'transaction');
		return new Transaction($result[0]);
	}

	public function getPendingTopupTransaction($payment, $student, $session)
	{
		$query = "SELECT a.* from transaction a join payment b on b.id = a.real_payment_id join student_change_of_programme c on c.transaction_id = a.id where a.real_payment_id=? and payment_status not in ('00','01') and a.session = ? and c.student_id = ? order by a.id desc";
		$param = [$payment, $session, $student];
		$result = $this->query($query, $param);
		if (!$result) {
			return null;
		}
		loadClass($this->load, 'transaction');
		return new Transaction($result[0]);
	}

	public function getTransactionById($student, $session, $transactionRef = null)
	{
		$query = "SELECT * from transaction where real_payment_id=?";
		$param = [$this->id];
		if ($student) {
			$query .= " and student_id = ? ";
			$param[] = $student;
		}
		if ($session) {
			$query .= " and session = ? ";
			$param[] = $session;
		}
		if ($transactionRef) {
			$query .= " and transaction_ref = ? ";
			$param[] = $transactionRef;
		}

		$result = $this->query($query, $param);
		if (!$result) {
			return null;
		}
		loadClass($this->load, 'transaction');
		return new Transaction($result[0]);
	}

	public function getTransactionByDescription($student, $session = null, $level = null, $transactionRef = null)
	{
		$query = "SELECT * from transaction where payment_id=? ";
		$param = [$this->description];
		$ignoreLevel = false;

		if ($student) {
			$query .= " and student_id = ? ";
			$param[] = $student;
		}
		if ($session) {
			$query .= " and session = ? ";
			$param[] = $session;
		}

		// transaction ref means data exist and level is already present, thus level can be ignore
		if ($transactionRef) {
			$query .= " and transaction_ref = ? ";
			$param[] = $transactionRef;
			$ignoreLevel = true;
		}

		if ($level && !$ignoreLevel) {
			$query .= " and level = ? ";
			$param[] = $level;
		}

		$result = $this->query($query, $param);
		if (!$result) {
			return null;
		}
		loadClass($this->load, 'transaction');
		return new Transaction($result[0]);
	}

	public function getPayment_session()
	{
		return $this->array['session'];
	}

	public function isStudentPayment($student)
	{
		$academic_record = $student->academic_record;
		$programmes = json_decode($this->programme, true);
		$levels = json_decode($this->level, true);
		$entryModes = json_decode($this->entry_mode, true);
		$entryInclude = json_decode($this->entry_mode_to_include, true);
		$levelInclude = json_decode($this->level_to_include, true) ?? [];
		$level = $academic_record->current_level;
		$entryMode = $academic_record->entry_mode;
		$program = $academic_record->programme_id;

		if (!in_array($program, $programmes)) {
			return false;
		}
		if (!(in_array($level, $levels) || in_array($level, @$levelInclude))) {
			return false;
		}
		if (!(in_array($entryMode, $entryModes) || in_array($entryMode, @$entryInclude))) {
			return false;
		}
		return true;

	}

	public function getFeeDescription()
	{
		loadClass($this->load, 'fee_description');

		$feeDesc = $this->fee_description->getWhere(['id' => $this->description], $c, 0, null, false);
		$description = $feeDesc ? $feeDesc[0]->description : null;
		return $description;
	}

	public function getSingleTransaction($transaction)
	{
		$query = "select * from transaction where id=?";
		$result = $this->query($query, $transaction);
		if (!$result) {
			return false;
		}
		loadClass($this->load, 'transaction');
		return new Transaction($result[0]);
	}

	public function getSingleTransactionByRef($transactionRef, $returnObject = true)
	{
		$query = "select * from transaction where transaction_ref=?";
		$result = $this->query($query, [$transactionRef]);
		if (!$result) {
			return false;
		}
		if (!$returnObject) {
			return $result[0];
		}
		loadClass($this->load, 'transaction');
		return new Transaction($result[0]);
	}

	public function validateVerificationFee($academicRecord, $paymentId)
	{
		loadClass($this->load, 'fee_description');
		$olevel = $academicRecord->olevel_details;
		if ($olevel) {
			$olevel = json_decode($olevel, true);
			if (count($olevel) >= 2) {
				$feeId = $this->fee_description->getPaymentFeeDescriptionByCode('VEF-Two');
				if ($feeId['payment_id'] != $paymentId) {
					return false; // using this to validate that the payment ID is not correct
				}
				if ($payment = $this->fee_description->getPaymentFeeDescriptionByCode('VEF-Two', $paymentId)) {
					$amount = $payment['amount'];
					$serviceCharge = $payment['service_charge'] ?? 0;
					$subAccount = $payment['subaccount_amount'] ?? 0;
					$subTotal = ($amount + $serviceCharge + $subAccount); // olevel result usually not more than 2
					$discount = $payment['discount_amount'] ?? 0;

					$totalAmount = ($discount && $discount > 0) ? $subTotal - $discount : $subTotal;
					return $totalAmount;
				}
			} else {
				$feeId = $this->fee_description->getPaymentFeeDescriptionByCode('VEF-One');
				if ($feeId['payment_id'] != $paymentId) {
					return false;
				}
				if ($payment = $this->fee_description->getPaymentFeeDescriptionByCode('VEF-One', $paymentId)) {
					$amount = $payment['amount'];
					$serviceCharge = $payment['service_charge'] ?? 0;
					$subAccount = $payment['subaccount_amount'] ?? 0;
					$subTotal = ($amount + $serviceCharge + $subAccount); // olevel result usually not more than 2
					$discount = $payment['discount_amount'] ?? 0;

					$totalAmount = ($discount && $discount > 0) ? $subTotal - $discount : $subTotal;
					return $totalAmount;
				}
			}
		}
		return false;
	}

	public function getFormatDueDateParam(object $payment)
	{
		$format_due_date = date('Y-m-d h:i:s', strtotime(str_replace('/', '-', $payment->date_due)));
		$due_date = new DateTime($format_due_date);
		$curr_date = new DateTime();
		$penalty_fee = ($due_date < $curr_date) ? $payment->penalty_fee : 0;
		$date_due = date('M. d, Y', strtotime(str_replace('/', '-', $payment->date_due))) ?? null;
		return [$penalty_fee, $date_due];
	}

	public function initPayment($student, $channel, $payment = null, $transaction = null, $amount = null, $serviceCharge = null, $preselectedPack = null)
	{
		if ($channel == 'remita') {
			$this->load->model('remita');
			$trans = $this->remita->initPayment($student, $payment, $transaction, $amount, $serviceCharge, $preselectedPack);
			return $trans;
		}
	}

	public function partInitPayment($student, $channel, $payment = null, array $param = [], $transaction = null)
	{
		if ($channel == 'remita') {
			$this->load->model('remita');
			$trans = $this->remita->partInitPayment($student, $payment, $param, $transaction);
			return $trans;
		}
	}

	public function getPaymentDetails($student, $channel, $payment = null, $transaction = null)
	{
		if ($channel == 'remita') {
			$this->load->model('remita');
			$trans = $this->remita->getRemitaDetails($student, $payment, $transaction);
			return $trans;
		}
	}

	public function getCustomPaymentDetails($users, $channel, $transaction)
	{
		if ($channel == 'remita') {
			$this->load->model('remita');
			$trans = $this->remita->getcustomRemitaDetails($users, $transaction);
			return $trans;
		}
	}

	public function customInitPayment(object $users, string $channel, array $param, $transaction = null)
	{
		if ($channel == 'remita') {
			$this->load->model('remita');
			$trans = $this->remita->customInitPayment($users, $param, $transaction);
			return $trans;
		}
	}

	public function getPaymentByDescription($description, $session = null)
	{
		$query = "SELECT a.* from payment a where description = ?";
		$data = [$description];
		if ($session && $session != 0) {
			$query .= " and a.session = ?";
			$data[] = $session;
		}

		$query .= " order by id desc limit 1";
		$result = $this->query($query, $data);
		if (!$result) {
			return null;
		}
		return $result[0];
	}

	public function getPaymentByDescriptionCode($description, $session = null)
	{
		$query = "SELECT a.id as real_payment_id,a.payment_code,a.options,a.description as payment_id,b.description,service_type_id 
			from payment a join fee_description b on b.id = a.description where b.code = ?";
		$data = [$description];
		if ($session && $session != 0) {
			$query .= " and a.session = ?";
			$data[] = $session;
		}

		$query .= " order by a.id desc limit 1";
		$result = $this->query($query, $data);
		if (!$result) {
			return null;
		}
		return $result[0];
	}

	public function getPaymentCode($code, $id = null)
	{
		$query = "SELECT id, payment_code from payment where payment_code = ?";
		$data = [$code];
		if ($id) {
			$query .= " and id <> ?";
			$data[] = $id;
		}

		$result = $this->query($query, $data);
		if (!$result) {
			return false;
		}
		return $result[0];
	}

	public function getPaymentById($payment)
	{
		$query = "SELECT a.* from payment a where id = ?";
		$data = [$payment];
		$result = $this->query($query, $data);
		if (!$result) {
			return null;
		}
		return new Payment($result[0]);
	}

	public function getPartialCompleteTransaction($student, $payment_id, $session = null)
	{
		$query = "SELECT * from transaction where student_id = ? and payment_id=? and payment_status in ('00','01') ";
		if ($payment_id == '1') {
			$query .= "and payment_option in ('1','1B')";
		} else {
			$query .= "and payment_option in ('2','2B')";
		}
		$param = [$student, $payment_id];
		if ($session) {
			$query .= " and session = ? ";
			$param[] = $session;
		}

		$result = $this->query($query, $param);
		if (!$result) {
			return false;
		}
		loadClass($this->load, 'transaction');
		return new Transaction($result[0]);
	}

	public function getPartialTransactionOption($student, $payment_id, $session = null)
	{
		$query = "SELECT * from transaction where student_id = ? and payment_id=? ";
		if ($payment_id == '1') {
			$query .= "and payment_option in ('1','1B')";
		} else {
			$query .= "and payment_option in ('2','2B')";
		}
		$param = [$student, $payment_id];
		if ($session) {
			$query .= " and session = ? ";
			$param[] = $session;
		}

		$result = $this->query($query, $param);
		if (!$result) {
			return null;
		}
		// the idea is to first find successful payment
		foreach ($result as $item) {
			if (CommonTrait::isPaymentValid($item['payment_status'])) {
				return $item;
			}
		}
		return $result[0];
	}

	public function getCompleteTransaction($student, $payment_id, $session = null)
	{
		$query = "SELECT * from transaction where student_id = ? and payment_id=? ";
		$param = [$student, $payment_id];
		if ($session) {
			$query .= " and session = ? ";
			$param[] = $session;
		}
		$query .= " order by payment_option desc ";
		$result = $this->query($query, $param);

		if (!$result) {
			return null;
		}

		// the idea is to first find successful payment
		foreach ($result as $item) {
			if (CommonTrait::isPaymentValid($item['payment_status'])) {
				return $item;
			}
		}
		return $result[0];
	}

}
