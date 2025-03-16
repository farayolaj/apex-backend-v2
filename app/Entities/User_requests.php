<?php

require_once('application/models/Crud.php');
require_once APPPATH . "constants/OutflowStatus.php";

/**
 * This class is automatically generated based on the structure of the table.
 * And it represent the model of the user_requests table
 */
class User_requests extends Crud
{

	/**
	 * This is the entity name equivalent to the table name
	 * @var string
	 */
	protected static $tablename = "User_requests";

	/**
	 * This array contains the field that can be null
	 * @var array
	 */
	public static $nullArray = ['request_no', 'title', 'user_id', 'request_id', 'amount', 'description', 'beneficiaries',
		'deduction', 'withhold_tax', 'vat', 'stamp_duty', 'total_amount', 'request_status', 'project_task_id', 'feedback',
		'date_approved', 'updated_at', 'action_timeline', 'stage', 'admon_reference'];

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
	public static $typeArray = ['request_no' => 'varchar', 'title' => 'varchar', 'user_id' => 'int', 'request_id' => 'int', 'amount' => 'decimal',
		'description' => 'text', 'beneficiaries' => 'text', 'deduction' => 'decimal', 'withhold_tax' => 'decimal', 'vat' => 'decimal',
		'stamp_duty' => 'decimal', 'total_amount' => 'decimal', 'request_status' => 'enum', 'project_task_id' => 'int', 'feedback' => 'text',
		'date_approved' => 'timestamp', 'created_at' => 'timestamp', 'updated_at' => 'timestamp', 'action_timeline' => 'text',
		'request_from' => 'enum', 'stage' => 'enum', 'deduction_amount' => 'text', 'voucher_document' => 'text', 'retire_advance_doc' => 'text', 'admon_reference' => 'text'];

	/**
	 * This is a dictionary that map a field name with the label name that
	 * will be shown in a form
	 * @var array
	 */
	public static $labelArray = ['id' => '', 'request_no' => '', 'title' => '', 'user_id' => '', 'request_id' => '', 'amount' => '',
		'description' => '', 'beneficiaries' => '', 'deduction' => '', 'withhold_tax' => '', 'vat' => '', 'stamp_duty' => '', 'total_amount' => '',
		'request_status' => '', 'project_task_id' => '', 'feedback' => '', 'date_approved' => '', 'created_at' => '', 'updated_at' => '',
		'action_timeline' => '', 'request_from' => '', 'stage' => '', 'deduction_amount' => '',
		'voucher_document' => '', 'retire_advance_doc' => '', 'admon_reference' => ''];

	/**
	 * Associative array of fields in the table that have default value
	 * @var array
	 */
	public static $defaultArray = ['request_status' => 'pending', 'created_at' => 'current_timestamp()'];

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
	];

	/**
	 * This are the action allowed to be performed on the entity and this can
	 * be changed in the formConfig model file for flexibility
	 * @var array
	 */
	public static $tableAction = ['delete' => 'delete/user_requests', 'edit' => 'edit/user_requests'];

	public static $apiSelectClause = ['id', 'request_no', 'title', 'user_id', 'request_id', 'amount', 'description',
		'beneficiaries', 'deduction', 'withhold_tax', 'vat', 'stamp_duty', 'total_amount', 'request_status', 'project_task_id', 'feedback',
		'date_approved', 'created_at', 'updated_at', 'action_timeline', 'stage', 'deduction_amount', 'retire_advance_doc', 'voucher_document', 'admon_reference'];

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


	protected function getUser()
	{
		$query = 'SELECT * FROM users_new WHERE id=?';
		if (!isset($this->array['user_id'])) {
			return null;
		}
		$id = $this->array['user_id'];
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		include_once('Users_new.php');
		return new Users_new($result[0]);
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
		return new Request_type($result[0]);
	}

	protected function getProject_task()
	{
		$query = 'SELECT * FROM project_tasks WHERE id=?';
		if (!isset($this->array['project_task_id'])) {
			return null;
		}
		$id = $this->array['project_task_id'];
		$result = $this->query($query, [$id]);
		if (!$result) {
			return null;
		}
		include_once('Project_tasks.php');
		return new Project_tasks($result[0]);
	}

	public function APIList($filterList, $queryString, ?int $start, ?int $len, ?string $orderBy): array
	{
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by a.created_at desc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}

		return $this->apiListQuery($filterQuery, $filterValues);
	}

	public function APIListRequests($filterList, $queryString, ?int $start, ?int $len, ?string $orderBy): array
	{
		$from = $this->input->get('start_date', true) ?? null;
		$to = $this->input->get('end_date', true) ?? null;

		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		if ($from && $to) {
			$from = ($this->db->escape_str($from));
			$to = ($this->db->escape_str($to));
			$filterQuery .= ($filterQuery ? " and " : " where ") . " date(a.created_at) between date('$from') and date('$to') ";
		} else if ($from) {
			$from = ($this->db->escape_str($from));
			$filterQuery .= ($filterQuery ? " and " : " where ") . " date(a.created_at) = date('$from') ";
		}

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by a.created_at desc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}
		return $this->apiListQuery($filterQuery, $filterValues, 'project');
	}

	public function APIListRequestsAction($filterList, $queryString, ?int $start, ?int $len, ?string $orderBy, ?array $timelineFilter): array
	{
		$q = $this->input->get('q', true) ?: false;
		$from = $this->input->get('start_date', true) ?? null;
		$to = $this->input->get('end_date', true) ?? null;

		if ($q) {
			$searchArr = ['b.task_title', 'a.title', 'a.description', 'c.title'];
			$queryString = buildCustomSearchString($searchArr, $q);
		}
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		if ($timelineFilter) {
			$stage = $timelineFilter[0];
			$stageAction = $timelineFilter[1];
			$filterQuery .= ($filterQuery ? ' and ' : ' where ') . "JSON_CONTAINS(action_timeline, '{\"stage\": \"{$stage}\", \"state\": \"{$stageAction}\"}')";
		}

		if ($from && $to) {
			$from = ($this->db->escape_str($from));
			$to = ($this->db->escape_str($to));
			$filterQuery .= ($filterQuery ? " and " : " where ") . " date(a.created_at) between date('$from') and date('$to') ";
		} else if ($from) {
			$from = ($this->db->escape_str($from));
			$filterQuery .= ($filterQuery ? " and " : " where ") . " date(a.created_at) = date('$from') ";
		}

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by a.created_at desc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}

		return $this->apiListQuery($filterQuery, $filterValues, 'project');
	}

	public function APIListVoucherRequests($filterList, $queryString, ?int $start, ?int $len, ?string $orderBy, bool $allowCharges = true): array
	{
		$q = $this->input->get('q', true) ?: false;
		if ($q) {
			$searchArr = ['b.task_title', 'a.title', 'a.description', 'c.title'];
			$queryString = buildCustomSearchString($searchArr, $q);
		}
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by e.created_at desc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}

		return $this->apiListQuery($filterQuery, $filterValues, 'voucher', $allowCharges);
	}

	public function APIListRequestTitle($filterList, $queryString, ?int $start, ?int $len, ?string $orderBy): array
	{
		$from = $this->input->get('start_date', true) ?? null;
		$to = $this->input->get('end_date', true) ?? null;

		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		if ($from && $to) {
			$from = ($this->db->escape_str($from));
			$to = ($this->db->escape_str($to));
			$filterQuery .= ($filterQuery ? " and " : " where ") . " date(a.created_at) between date('$from') and date('$to') ";
		} else if ($from) {
			$from = ($this->db->escape_str($from));
			$filterQuery .= ($filterQuery ? " and " : " where ") . " date(a.created_at) = date('$from') ";
		}

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by a.created_at desc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}

		return $this->apiListQuery($filterQuery, $filterValues, 'project-title');
	}

	public function APIListSalaryAdvanceRequests($filterList, $queryString, ?int $start, ?int $len, ?string $orderBy): array
	{
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by a.created_at desc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}
		return $this->apiListQuery($filterQuery, $filterValues, 'salary-advance');
	}

	private function apiListQuery(string $filterQuery, ?array $filterValues, string $queryType = 'non-project', bool $allowCharges = true): array
	{
		$tablename = strtolower(self::$tablename);
		if ($queryType == 'non-project') {
			$query = "SELECT " . buildApiClause(static::$apiSelectClause, 'a') . ",b.task_title,'prev_request' from $tablename a left join 
			project_tasks b on b.id = a.project_task_id $filterQuery";
		} else if ($queryType == 'project') {
			$query = "SELECT " . buildApiClause(static::$apiSelectClause, 'a') . ",b.task_title,c.title as project_title,
			c.id as project_id,d.name as request_type,d.is_auditable,'prev_request' from $tablename a left join project_tasks b on b.id = a.project_task_id left join projects c 
			on c.id = b.project_id join request_type d on d.id = a.request_id  $filterQuery";
		} else if ($queryType == 'voucher') {
			$dbColumn = $allowCharges ? ",'charges_list'" : "";
			$query = "SELECT " . buildApiClause(static::$apiSelectClause, 'a') . ",b.task_title,c.title as project_title,
			c.id as project_id,d.name as request_type $dbColumn from $tablename a left join project_tasks b on b.id = a.project_task_id left join projects c 
			on c.id = b.project_id join request_type d on d.id = a.request_id join user_request_assignee e on e.user_request_id = a.id  $filterQuery";
		} else if ($queryType == 'project-title') {
			$query = "SELECT a.id, request_no,title,a.user_id,description,a.created_at,a.request_status,a.request_id from $tablename a $filterQuery";
		} else if ($queryType == 'salary-advance') {
			$query = "SELECT " . buildApiClause(static::$apiSelectClause, 'a') . ", 'prev_request' from $tablename a $filterQuery";
		}
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
		loadClass($this->load, 'request_type_charges');
		loadClass($this->load, 'users_new');

		$generator = useGenerators($items);
		$payload = [];
		foreach ($generator as $item) {
			$payload[] = $this->loadExtras($item);
		}
		return $payload;
	}

	public function loadExtras($item)
	{
		if (isset($item['beneficiaries'])) {
			$item['beneficiaries'] = ($item['beneficiaries'] != '') ? json_decode($item['beneficiaries'], true) : [];

			$userInfo = $this->users_new->getRequestUserInfo($item['user_id']);
			if ($userInfo) {
				$userInfo = $userInfo[0];
				$name = $userInfo['firstname'] . ' ' . $userInfo['lastname'];
				$item['initiated_by'] = [
					'id' => $item['user_id'],
					'name' => $name,
				];
			} else {
				$item['initiated_by'] = [
					'id' => $item['user_id'],
					'name' => null,
				];
			}
		}

		if (isset($item['action_timeline'])) {
			$item['action_timeline'] = ($item['action_timeline'] != '') ? json_decode($item['action_timeline'], true) : [];
		}

		if (isset($item['deduction_amount'])) {
			$item['deduction_amount'] = ($item['deduction_amount'] != '') ? json_decode($item['deduction_amount'], true) : [];
		}

		if (isset($item['feedback'])) {
			$item['feedback'] = json_decode($item['feedback'], true);
		} else {
			$item['feedback'] = [];
		}

		if (isset($item['charges_list'])) {
			$charges = $this->getListCharges($item['request_id']);
			$requestType = $this->chargeAuditable($item['request_id']);
			if ($charges) {
				$item['charges_list'] = [
					'request_type' => $requestType['name'],
					'request_type_slug' => $requestType['slug'],
					'is_auditable' => $requestType['is_auditable'],
					'data' => $charges
				];
			} else {
				$item['charges_list'] = [
					'request_type' => $requestType['name'],
					'request_type_slug' => $requestType['slug'],
					'is_auditable' => $requestType['is_auditable'],
					'data' => []
				];
			}
		}

		if (isset($item['prev_request'])) {
			$prevRequest = $this->getPrevUserRequest($item['id']);
			if ($prevRequest) {
				$item['prev_request'] = $prevRequest[0];
			} else {
				$item['prev_request'] = null;
			}
		}

		if (isset($item['voucher_document']) && $item['voucher_document'] != '') {
			$item['voucher_document'] = userImagePath($this, $item['voucher_document'], 'payment_voucher_path');
		}

		if (isset($item['retire_advance_doc']) && $item['retire_advance_doc'] != '') {
			$item['retire_advance_doc'] = userImagePath($this, $item['retire_advance_doc'], 'retire_advance_path');
		}

		return $item;
	}

	private function getPrevUserRequest($newRequestID)
	{
		$query = "SELECT id,request_no,title,amount,description,feedback,created_at from user_requests_archive where new_request_id = ? limit 1";
		return $this->query($query, [$newRequestID]);
	}

	private function getListCharges($requestID)
	{
		$query = "SELECT c.name as request_charge,c.slug,c.amount,a.is_editable,a.active from 
		request_type_charges a join request_type b on a.request_type_id = b.id join request_charges c on 
		c.id = a.request_charge_id where a.request_type_id = $requestID";
		$result = $this->query($query);
		return $result;
	}

	public function chargeAuditable($requestID)
	{
		$query = "SELECT name,slug,is_auditable from request_type where id = ?";
		$result = $this->query($query, [$requestID]);
		return $result[0];
	}

	public static function feedbackComment(object $currentUser, ?string $feedbacks = null, string $comment = null)
	{
		$feedback = [
			'user_id' => $currentUser->id,
			'fullname' => $currentUser->firstname . ' ' . $currentUser->lastname,
			'comment' => $comment,
			'date_created' => date('Y-m-d H:i:s'),
		];
		$feedbacks = json_decode($feedbacks, true);
		if ($feedbacks) {
			$feedbacks[] = $feedback;
		} else {
			$feedbacks[] = $feedback;
		}

		return json_encode($feedbacks);
	}

	public static function actionTimelineData(object $currentUser, string $stage, string $action, bool $isAuditable)
	{
		$empty = [];
		$insertParam = [
			[
				'firstname' => $currentUser->firstname,
				'othernames' => $currentUser->othernames,
				'lastname' => $currentUser->lastname,
				'user_id' => $currentUser->id,
				'assignee_to' => $empty,
				'stage' => $stage,
				'state' => 'done',
				'action' => $action,
				'date_performed' => date('Y-m-d H:i:s'),
			],
			[
				'firstname' => '',
				'othernames' => '',
				'lastname' => '',
				'user_id' => '',
				'assignee_to' => $empty,
				'stage' => 'payment_voucher',
				'state' => 'assigning',
				'action' => '',
				'date_performed' => '',
			],
			[
				'firstname' => '',
				'othernames' => '',
				'lastname' => '',
				'user_id' => '',
				'assignee_to' => $empty,
				'stage' => 'mandate',
				'state' => 'assigning',
				'action' => '',
				'date_performed' => '',
			],
			[
				'firstname' => '',
				'othernames' => '',
				'lastname' => '',
				'user_id' => '',
				'assignee_to' => $empty,
				'stage' => 'payment',
				'state' => 'pending',
				'action' => '',
				'date_performed' => '',
			]
		];
		if ($isAuditable) {
			$insertParam[] = [
				'firstname' => '',
				'othernames' => '',
				'lastname' => '',
				'user_id' => '',
				'assignee_to' => $empty,
				'stage' => 'auditor',
				'state' => 'pending',
				'action' => '',
				'date_performed' => '',
			];
		}
		return json_encode($insertParam);
	}

	public function updateActionTimeline(int $requestID, object $currentUser, int $stateIdx, string $state, string $action, string $stageWhere, string $stateWhere = 'pending')
	{
		$firstname = $currentUser->firstname;
		$othernames = $currentUser->othernames;
		$lastname = $currentUser->lastname;
		$date = date('Y-m-d H:i:s');
		$userID = $currentUser->id;

		$query = "UPDATE user_requests SET action_timeline = JSON_SET(action_timeline, 
		\"$[{$stateIdx}].firstname\", \"{$firstname}\", 
		\"$[{$stateIdx}].othernames\", \"{$othernames}\", 
		\"$[{$stateIdx}].lastname\", \"{$lastname}\", 
		\"$[{$stateIdx}].user_id\", \"{$userID}\", 
		\"$[{$stateIdx}].date_performed\", \"{$date}\", 
		\"$[{$stateIdx}].state\", \"{$state}\", 
		\"$[{$stateIdx}].action\", \"{$action}\" )  
    	where id = '{$requestID}' and JSON_CONTAINS(action_timeline, '{\"stage\": \"{$stageWhere}\", \"state\": \"{$stateWhere}\"}')";
		return $this->query($query);
	}

	public function updateActionOnlyTimeline(int $requestID, int $stateIdx, string $state, string $action, string $stageWhere, string $paymentDate = null)
	{
		$firstname = '';
		$othernames = '';
		$lastname = '';
		$date = $paymentDate ?: date('Y-m-d H:i:s');
		$userID = '';

		$query = "UPDATE user_requests SET action_timeline = JSON_SET(action_timeline, 
		\"$[{$stateIdx}].firstname\", \"{$firstname}\", 
		\"$[{$stateIdx}].othernames\", \"{$othernames}\", 
		\"$[{$stateIdx}].lastname\", \"{$lastname}\", 
		\"$[{$stateIdx}].user_id\", \"{$userID}\", 
		\"$[{$stateIdx}].date_performed\", \"{$date}\", 
		\"$[{$stateIdx}].state\", \"{$state}\", 
		\"$[{$stateIdx}].action\", \"{$action}\" )  
    	where id = '{$requestID}' and JSON_CONTAINS(action_timeline, '{\"stage\": \"{$stageWhere}\"}')";
		return $this->query($query);
	}

	public function rejectActionTimeline(int $requestID, object $currentUser, int $stateIdx, string $stage, string $action, string $stageWhere, ?string $stateWhere)
	{
		$firstname = $currentUser->firstname;
		$othernames = $currentUser->othernames;
		$lastname = $currentUser->lastname;
		$date = date('Y-m-d H:i:s');
		$userID = $currentUser->id;

		$query = "UPDATE user_requests SET action_timeline = JSON_SET(action_timeline, 
		\"$[{$stateIdx}].firstname\", \"{$firstname}\", 
		\"$[{$stateIdx}].othernames\", \"{$othernames}\", 
		\"$[{$stateIdx}].lastname\", \"{$lastname}\", 
		\"$[{$stateIdx}].user_id\", \"{$userID}\", 
		\"$[{$stateIdx}].date_performed\", \"{$date}\", 
		\"$[{$stateIdx}].stage\", \"{$stage}\", 
		\"$[{$stateIdx}].action\", \"{$action}\" )  
    	where id = '{$requestID}' and JSON_CONTAINS(action_timeline, '{\"stage\": \"{$stageWhere}\", \"state\": \"{$stateWhere}\"}')";
		return $this->query($query);
	}

	public function updateAssigneeActionTimeline(int $requestID, array $name, int $stateIdx, string $state, string $whereStage, string $whereState)
	{
		$date = date('Y-m-d H:i:s');
		$fullname = $name['fullname'];
		$assignDate = $name['created_at'];
		$userID = $name['user_id'];

		$query = "UPDATE user_requests SET action_timeline = JSON_SET(action_timeline,
    	\"$[{$stateIdx}].assignee_to\", JSON_ARRAY(JSON_OBJECT('fullname', '$fullname', 'created_at', '$assignDate','user_id', '$userID')),
        \"$[{$stateIdx}].state\", \"{$state}\"
        )	
        where id = '{$requestID}' and JSON_CONTAINS(action_timeline, '{\"stage\": \"{$whereStage}\", \"state\": \"{$whereState}\"}')";
		return $this->query($query);
	}

	public function updateReassigneeActionTimeline(int $requestID, array $name, int $stateIdx, string $whereStage, string $whereState)
	{
		$fullname = $name['fullname'];
		$assignDate = $name['created_at'];
		$userID = $name['user_id'];

		$query = "UPDATE user_requests SET action_timeline = JSON_ARRAY_APPEND(action_timeline,
		\"$[{$stateIdx}].assignee_to\", JSON_OBJECT('fullname', '$fullname', 'created_at', '$assignDate','user_id', '$userID') ) 
		where id = '{$requestID}' and JSON_CONTAINS(action_timeline, '{\"stage\": \"{$whereStage}\", \"state\": \"{$whereState}\"}')";
		return $this->query($query);
	}

	public function updateUserAssignee(int $requestID, string $requestType = 'payment_voucher')
	{
		$query = "UPDATE user_request_assignee set status = '1' where user_request_id = ? and request_type = ?";
		return $this->query($query, [$requestID, $requestType]);
	}

	public function checkUserAssignee(int $userID, int $requestID, string $requestType = 'payment_voucher')
	{
		$query = "SELECT * from user_request_assignee where user_request_id = ? and request_type = ? and assign_to = ?";
		$result = $this->query($query, [$requestID, $requestType, $userID]);
		return $result;
	}

	public function getLastRequestNo(string $table, string $column)
	{
		$query = "SELECT {$column} from {$table} order by created_at desc limit 1";
		$result = $this->query($query);
		return ($result) ? $result[0][$column] : null;
	}

	public static function emptyActionTimeline()
	{
		return json_encode([
			[
				'state' => '',
				'stage' => 'director',
				'action' => ''
			]
		]);
	}

	public function getNumberOfRequestInLast6Month(string $type = null)
	{
		$whereClause = null;
		if ($type === 'approve') {
			$whereClause = " and a.request_status = 'approved' ";
		} else if ($type === 'declined') {
			$whereClause = " and a.request_status = 'rejected' ";
		} else if ($type === 'pending') {
			$whereClause = " and a.request_status = 'pending' ";
		} else if ($type === 'success') {
			$whereClause = " and a.request_status in ('paid', 'advance_return_confirmed')  ";
		}

		$query = "
				SELECT DATE_FORMAT(months.month, '%M') AS label,  COALESCE(COUNT(a.request_no), 0) AS total 
				FROM 
    			(
        		SELECT DATE_FORMAT(CURDATE() - INTERVAL seq MONTH, '%Y-%m-01') AS month FROM 
            		(SELECT 0 AS seq UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5) AS seq_table
    			) AS months
				LEFT JOIN user_requests a ON DATE_FORMAT(a.created_at, '%Y-%m') = DATE_FORMAT(months.month, '%Y-%m')
    			{$whereClause}
				GROUP BY months.month ORDER BY months.month ASC
		";
		$query = $this->db->query($query);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array();
	}

	public function getAmountSpentInLast6Month()
	{
		$pendingCredit = OutflowStatus::PENDING_CREDIT;
		$query = "SELECT ANY_VALUE(date_format(a.created_at, '%M')) as label, ANY_VALUE(UNIX_TIMESTAMP(a.created_at)) as ord,
		sum(a.total_amount) as total from transaction_request a where a.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
		and payment_status_description = ? group by year(a.created_at), month(a.created_at), label order by ord asc";
		$query = $this->db->query($query, [$pendingCredit]);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array();
	}

	public function getDirectorAverageProcessingTime()
	{
		$query = " SELECT round(avg(TIMESTAMPDIFF(DAY,created_at,date_performed)), 3) as avg_time from 
        (
			SELECT created_at, JSON_UNQUOTE(JSON_EXTRACT(action_timeline, CONCAT('$[', idx - 1, '].date_performed'))) AS date_performed
			FROM user_requests, JSON_TABLE(action_timeline, '$[*]' COLUMNS (
      		idx FOR ORDINALITY,
      		stage VARCHAR(50) PATH '$.stage',
      		action VARCHAR(50) PATH '$.action'
    	)) AS jt WHERE jt.stage = 'director' AND jt.action = 'approved'
    	) as a where date_performed is not null and created_at <= date_performed";
		$query = $this->db->query($query);
		$result = 0;
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array()[0]['avg_time'];
	}

	public function getDBAverageProcessingTime()
	{
		$query = " SELECT round(avg(TIMESTAMPDIFF(DAY,date_performed, updated_at)), 3) as avg_time from 
        (
			SELECT updated_at, JSON_UNQUOTE(JSON_EXTRACT(action_timeline, CONCAT('$[', idx - 1, '].date_performed'))) AS date_performed
			FROM user_requests, JSON_TABLE(action_timeline, '$[*]' COLUMNS (
      		idx FOR ORDINALITY,
      		stage VARCHAR(50) PATH '$.stage',
      		action VARCHAR(50) PATH '$.action'
    	)) AS jt WHERE jt.stage = 'director' AND action = 'approved'
    	) as a where updated_at is not null and date_performed <= updated_at";
		$query = $this->db->query($query);
		$result = 0;
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array()[0]['avg_time'];
	}

	public function getLast6MonthDirectives($stage)
	{
		$whereClause = '';
		if (is_array($stage)) {
			$whereClause = " stage in ('" . implode("','", $stage) . "')";
		} else {
			$whereClause = " stage = '$stage'";
		}
		$query = "SELECT ANY_VALUE(date_format(a.created_at, '%M')) as label, ANY_VALUE(UNIX_TIMESTAMP(a.created_at)) as ord,
		count(*) as total from user_requests a where $whereClause and a.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) group by year(a.created_at), 
		month(a.created_at), label order by ord asc";
		$query = $this->db->query($query);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array();
	}

	public function getLast6MonthProcessAmount()
	{
		$pendingCredit = OutflowStatus::PENDING_CREDIT;
		$query = "SELECT ANY_VALUE(date_format(a.created_at, '%M')) as label, ANY_VALUE(UNIX_TIMESTAMP(a.created_at)) as ord,
		sum(a.total_amount) as total from transaction_request a where a.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
		and payment_status_description = '$pendingCredit' group by year(a.created_at), month(a.created_at), label order by ord asc";
		$query = $this->db->query($query);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array();
	}


}

