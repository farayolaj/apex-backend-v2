<?php
namespace App\Entities;

use App\Models\Crud;
use App\Libraries\EntityLoader;

/**
 * This class is automatically generated based on the structure of the table.
 * And it represent the model of the project_tasks table
 */
class Project_tasks extends Crud
{

	/**
	 * This is the entity name equivalent to the table name
	 * @var string
	 */
	protected static $tablename = "Project_tasks";

	/**
	 * This array contains the field that can be null
	 * @var array
	 */
	public static $nullArray = ['project_id', 'assign_to', 'task_title', 'amount_spent', 'task_status', 'date_completed', 'updated_at'];

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
	public static $displayField = 'project_id';

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
	public static $typeArray = ['project_id' => 'int', 'assign_to' => 'int', 'task_title' => 'varchar', 'task_status' => 'enum', 'date_completed' => 'timestamp', 'created_at' => 'timestamp', 'updated_at' => 'timestamp', 'multiple_invoice'=>'tinyint'];

	/**
	 * This is a dictionary that map a field name with the label name that
	 * will be shown in a form
	 * @var array
	 */
	public static $labelArray = ['id' => '', 'project_id' => '', 'assign_to' => '', 'task_title' => '', 'task_status' => '', 'date_completed' => '', 'created_at' => '', 'updated_at' => '','multiple_invoice'=>''];

	/**
	 * Associative array of fields in the table that have default value
	 * @var array
	 */
	public static $defaultArray = ['task_status' => 'pending', 'created_at' => 'current_timestamp()'];

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
	public static $relation = ['project' => array('project_id', 'id')
		, 'user' => array('assign_to', 'id')
	];

	/**
	 * This are the action allowed to be performed on the entity and this can
	 * be changed in the formConfig model file for flexibility
	 * @var array
	 */
	public static $tableAction = ['delete' => 'delete/project_tasks', 'edit' => 'edit/project_tasks'];

	public static $apiSelectClause = ['id', 'project_id', 'assign_to', 'task_title', 'task_status', 'date_completed', 'created_at', 'updated_at','multiple_invoice'];

	public function __construct(array $array = [])
	{
		parent::__construct($array);
	}

	public function getProject_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'project','display'=>'project_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'project_name' as value from 'project' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('project', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='project_id' id='project_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
		<label for='project_id'>Project</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='project_id' id='project_id' class='form-control'>
					$option
				</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getUser_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'user','display'=>'user_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'user_name' as value from 'user' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('user', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='assign_to' id='assign_to'' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
		<label for='assign_to'>User</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='assign_to' id='assign_to'' class='form-control'>
					$option
				</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getTask_titleFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='task_title'>Task Title</label>
		<input type='text' name='task_title' id='task_title' value='$value' class='form-control' required />
	</div>";
	}

	public function getAmount_spentFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='amount_spent'>Amount Spent</label>
		<input type='text' name='amount_spent' id='amount_spent' value='$value' class='form-control' required />
	</div>";
	}

	public function getTask_statusFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='task_status'>Task Status</label>
		<input type='text' name='task_status' id='task_status' value='$value' class='form-control' required />
	</div>";
	}

	public function getDate_completedFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='date_completed'>Date Completed</label>
		<input type='text' name='date_completed' id='date_completed' value='$value' class='form-control' required />
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


	protected function getProjects()
	{
		$query = 'SELECT * FROM projects WHERE id=?';
		if (!isset($this->array['id'])) {
			return null;
		}
		$id = $this->array['id'];
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		return new \App\Entities\Projects($result[0]);
	}

	protected function getUser()
	{
		$query = 'SELECT * FROM user WHERE id=?';
		if (!isset($this->array['id'])) {
			return null;
		}
		$id = $this->array['id'];
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		return new \App\Entities\Users_new($result[0]);
	}

	public function APIList($filterList, $queryString, $start, $len, $orderBy): array
	{
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];
		$currentUser = WebSessionManager::currentAPIUser();
		$proceed = false;

		if (isset($_GET['project_type']) && $_GET['project_type'] === 'contractors') {
			$proceed = true;
			$filterQuery .= ($filterQuery ? " and " : " where ") . " b.users_id='{$currentUser->id}' ";
		} else if (isset($_GET['project_type']) && $_GET['project_type'] === 'admin') {
			$proceed = true;
		}

		$filterQuery .= ($filterQuery ? " and " : " where ") . " c.user_type='contractor' ";

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by a.created_at desc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->escape($start);
			$len = $this->db->escape($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}

		if ($proceed) {
			return $this->apiListQuery($filterQuery, $filterValues);
		}
		return [];
	}

	public function APIListSingle($filterList, $queryString, $start, $len, $orderBy): array
	{
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		$filterQuery .= ($filterQuery ? " and " : " where ") . " c.user_type='contractor' ";

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by a.created_at desc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->escape($start);
			$len = $this->db->escape($len);
			$filterQuery .= " limit $start, $len";
		}

		if (!$filterValues) {
			$filterValues = [];
		}

		return $this->apiListQuery($filterQuery, $filterValues);
	}

	public function APIListTasks($filterList, $queryString, $start, $len, $orderBy): array
	{
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		$filterQuery .= ($filterQuery ? " and " : " where ") . " c.user_type='contractor' ";

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by a.created_at desc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->escape($start);
			$len = $this->db->escape($len);
			$filterQuery .= " limit $start, $len";
		}

		if (!$filterValues) {
			$filterValues = [];
		}

		return $this->apiListQuery($filterQuery, $filterValues, false);
	}


	private function apiListQuery(string $filterQuery, ?array $filterValues, bool $allowExtra = true): array
	{
		$tablename = strtolower(self::$tablename);
		$dbColumn = $allowExtra ? ",d.id as contractor,'project_amount' " : "";
		$query = "SELECT distinct " . buildApiClause(static::$apiSelectClause, 'a') . ", b.title as project_title,
			b.project_status,b.description as project_desc,b.date_initiated as project_date_initiated,b.date_completed as project_date_completed 
			$dbColumn from $tablename a join projects b on b.id = a.project_id join users_new c on c.id = a.assign_to 
			join contractors d on d.id = c.user_table_id $filterQuery";

		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->getResultArray();
		$res2 = $this->db->query($query2);
		$res2 = $res2->getResultArray();

		if ($allowExtra) {
			$res = $this->processList($res);
		}

		return [$res, $res2];
	}

	private function processList($items)
	{
		EntityLoader::loadClass($this, 'contractors');
		for ($i = 0; $i < count($items); $i++) {
			$items[$i] = $this->loadExtras($items[$i]);
		}
		return $items;
	}

	private function loadExtras($item)
	{
		if (isset($item['contractor'])) {
			$contractor = $this->contractors->getWhere(['id' => $item['contractor']]);
			$item['contractor'] = null;
			if ($contractor) {
				$contractor = $contractor[0];
				$contractor->cac_certificate = site_url("{$this->config->item('cac_certificate_path')}{$contractor->cac_certificate}");
				$item['contractor'] = $contractor->toArray();
			}
		}

		if (isset($item['project_amount'])) {
			$amounts = $this->getProjectAmount($item['id'], $item['assign_to']);
			$item['project_amount'] = $amounts;
		}
		return $item;
	}

	public function getProjectAmount($userID, $id)
	{
		$query = "SELECT if(sum(amount) > 0, sum(amount), 0) as total_amount, if(sum(total_amount) > 0, sum(total_amount), 0) as net_amount 
		from user_requests where project_task_id = ? and user_id = ?";
		$result = $this->query($query, [$id, $userID]);
		if (!$result) {
			return null;
		}
		return $result[0];
	}

	public function getProjectAmountStats(string $queryClause = null)
	{
		$query = "SELECT if(sum(a.amount) > 0, sum(a.amount), 0) as total_amount, if(sum(a.total_amount) > 0, sum(a.total_amount), 0) 
		as net_amount from user_requests a join project_tasks b on b.id = a.project_task_id join projects c on c.id = b.project_id $queryClause";
		$result = $this->query($query);
		if (!$result) {
			return [
				'total_amount' => 0,
				'net_amount' => 0
			];
		}
		return $result[0];
	}


}
