<?php
require_once('application/models/Crud.php');

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the sessions table.
 */
class Sessions extends Crud
{
	protected static $tablename = 'Sessions';
	/* this array contains the field that can be null*/
	static $nullArray = array();
	static $compositePrimaryKey = array();
	static $uploadDependency = array();
	/*this array contains the fields that are unique*/
	static $uniqueArray = array();
	/*this is an associative array containing the fieldname and the type of the field*/
	static $typeArray = array('date' => 'varchar', 'active' => 'tinyint', 'date_created' => 'datetime');
	/*this is a dictionary that map a field name with the label name that will be shown in a form*/
	static $labelArray = array('id' => '', 'date' => '', 'active' => '', 'date_created' => '');
	/*associative array of fields that have default value*/
	static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
	static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

	static $relation = array();
	static $tableAction = array('delete' => 'delete/sessions', 'edit' => 'edit/sessions');
	static $apiSelectClause = ['id', 'date', 'active'];

	function __construct($array = array())
	{
		parent::__construct($array);
	}

	function getDateFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='date' >Date</label>
		<input type='text' name='date' id='date' value='$value' class='form-control' required />
</div> ";

	}

	function getActiveFormField($value = '')
	{

		return "<div class='form-group'>
	<label class='form-checkbox'>Active</label>
	<select class='form-control' id='active' name='active' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

	}

	function getDate_createdFormField($value = '')
	{

		return " ";

	}

	public function delete($id = null, &$dbObject = null, $type = null): bool
	{
		permissionAccess($this, 'session_delete', 'delete');
		$currentUser = $this->webSessionManager->currentAPIUser();
		$db = $dbObject ?? $this->db;
		if (parent::delete($id, $db)) {
			logAction($this, 'session_deletion', $currentUser->id, $id);
			return true;
		}
		return false;
	}

	public function APIList($filterList, $queryString, $start, $len, $orderBy)
	{
		$selectData = static::$apiSelectClause;
		return $this->apiQueryListFiltered($selectData, $filterList, $queryString, $start, $len, $orderBy);
	}

	public function getSessionById($session)
	{
		$query = "SELECT id,date from sessions where id = ?";
		return $this->query($query, [$session]);
	}

	public function getSessionIdByDate($date)
	{
		$query = $this->db->get_where('sessions', array('date' => $date, 'active' => 1));

		if ($query->num_rows() > 0) {
			$row = $query->row();
			return $row->id;
		} else {
			return '';
		}
	}

	public function getSessionsWithResult(): array
	{
		$query = "select distinct b.id, b.date as session from course_enrollment a join sessions b on b.id=a.session_id order by session desc";
		$result = $this->query($query);
		if (!$result) {
			return [];
		}
		return $result;
	}

	public function getTransactionSession(){
		$orderBy = " value desc";
		if (isset($_GET['sortBy'])) {
			$sortBy = request()->getGet('sortBy');
			$sortDirection = request()->getGet('sortDirection');
			$sortDirection = ($sortDirection == 'down') ? 'desc' : 'asc';
			$orderBy = " $sortBy $sortDirection ";
		}
		$query = "SELECT a.id,a.date as value from sessions a left join transaction b on b.session = a.id where a.active = '1' group by id, value order by $orderBy";
		$query = $this->db->query($query);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array();
	}

	public function getCompleteTransactionSession(){
		$orderBy = " value desc";
		if (isset($_GET['sortBy'])) {
			$sortBy = request()->getGet('sortBy');
			$sortDirection = request()->getGet('sortDirection');
			$sortDirection = ($sortDirection == 'down') ? 'desc' : 'asc';
			$orderBy = " $sortBy $sortDirection ";
		}
		$query = "SELECT a.id,a.date as value from sessions a join transaction b on b.session = a.id where a.active = '1' group by id, value order by $orderBy";
		$query = $this->db->query($query);
		$result = [];
		if ($query->num_rows() <= 0) {
			return $result;
		}
		return $query->result_array();
	}

}
