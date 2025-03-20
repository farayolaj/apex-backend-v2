<?php
namespace App\Entities;

use App\Models\Crud;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the users_log table.
 */
class Users_log extends Crud {
	protected static $tablename = 'Users_log';
	/* this array contains the field that can be null*/
	static $nullArray = array('description');
	static $compositePrimaryKey = array();
	static $uploadDependency = array();
	/*this array contains the fields that are unique*/
	static $uniqueArray = array();
	/*this is an associative array containing the fieldname and the type of the field*/
	static $typeArray = array('username' => 'varchar', 'action_performed' => 'varchar', 'description' => 'text', 'user_agent' => 'text', 'user_ip' => 'varchar', 'user_long' => 'varchar', 'user_lat' => 'varchar', 'date_performed' => 'datetime');
	/*this is a dictionary that map a field name with the label name that will be shown in a form*/
	static $labelArray = array('id' => '', 'username' => '', 'action_performed' => '', 'description' => '', 'user_agent' => '', 'user_ip' => '', 'user_long' => '', 'user_lat' => '', 'date_performed' => '');
	/*associative array of fields that have default value*/
	static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
	static $documentField = array(); //array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

	static $relation = array();
	static $tableAction = array('delete' => 'delete/users_log', 'edit' => 'edit/users_log');

	function __construct($array = array()) {
		parent::__construct($array);
	}

	function getUsernameFormField($value = '') {

		return "<div class='form-group'>
	<label for='username' >Username</label>
		<input type='text' name='username' id='username' value='$value' class='form-control' required />
</div> ";

	}

	function getAction_performedFormField($value = '') {

		return "<div class='form-group'>
	<label for='action_performed' >Action Performed</label>
		<input type='text' name='action_performed' id='action_performed' value='$value' class='form-control' required />
</div> ";

	}

	function getDescriptionFormField($value = '') {

		return "<div class='form-group'>
	<label for='description' >Description</label>
<textarea id='description' name='description' class='form-control' >$value</textarea>
</div> ";

	}

	function getUser_agentFormField($value = '') {

		return "<div class='form-group'>
	<label for='user_agent' >User Agent</label>
<textarea id='user_agent' name='user_agent' class='form-control' required>$value</textarea>
</div> ";

	}

	function getUser_ipFormField($value = '') {

		return "<div class='form-group'>
	<label for='user_ip' >User Ip</label>
		<input type='text' name='user_ip' id='user_ip' value='$value' class='form-control' required />
</div> ";

	}

	function getUser_longFormField($value = '') {

		return "<div class='form-group'>
	<label for='user_long' >User Long</label>
		<input type='text' name='user_long' id='user_long' value='$value' class='form-control' required />
</div> ";

	}

	function getUser_latFormField($value = '') {

		return "<div class='form-group'>
	<label for='user_lat' >User Lat</label>
		<input type='text' name='user_lat' id='user_lat' value='$value' class='form-control' required />
</div> ";

	}

	function getDate_performedFormField($value = '') {

		return " ";

	}

	public function APIList($filterList, $queryString, $start, $len): array {
		$q = $this->input->get('q', true) ?: false;
		$action = $this->input->get('action', true);
		$student = $this->input->get('student_id', true);

		if ($action === 'remita_error') {
			if ($q) {
				$searchArr = ['a.username'];
				$queryString = buildCustomSearchString($searchArr, $q);
				$queryString .= " OR JSON_CONTAINS(new_data, '{\"transaction_ref\": \"{$q}\"}')";
			}
		}

		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		if ($action === 'view_student_detail') {
			return $this->getViewStudentLogs($action, $filterQuery, $filterValues, $student, $start, $len);
		} else if ($action === 'remita_error') {
			return $this->getViewRemitaError($action, $filterQuery, $filterValues, $start, $len);
		}

		return [[], []];
	}

	private function getViewStudentLogs($action, $filterQuery, $filterValues, $student, $start, $len) {
		if ($action) {
			if (!$student) {
				return [];
			}
			$filterQuery .= ($filterQuery) ? " and action_performed = ? and student_id = ?" : " where action_performed = ? and student_id = ?";
			$filterValues[] = $action;
			$filterValues[] = $student;
		}

		$filterQuery .= " order by id desc";
		if (isset($_GET['start']) && $len) {
			$start = $this->db->escape($start);
			$len = $this->db->escape($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}

		$tablename = $this->getTableName();
		$query = "SELECT SQL_CALC_FOUND_ROWS * from $tablename $filterQuery";

		$res = $this->apiQueryListCustomFiltered($query, $filterValues);
		return [$res[0], $res[1]];
	}

	private function getViewRemitaError($action, $filterQuery, $filterValues, $start, $len) {
		if ($action) {
			$filterQuery .= ($filterQuery) ? " and action_performed = ?" : " where action_performed = ?";
			$filterValues[] = 'remita_payment_error';
		}

		$filterQuery .= " order by id desc";
		if (isset($_GET['start']) && $len) {
			$start = $this->db->escape($start);
			$len = $this->db->escape($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}

		$tablename = $this->getTableName();
		$query = "SELECT SQL_CALC_FOUND_ROWS a.id,action_performed,a.user_agent,a.user_ip,a.date_performed,student_id,
       		new_data,a.username as matric_number from $tablename a $filterQuery";

		$temp = $this->apiQueryListCustomFiltered($query, $filterValues);
		$res = $this->processList($temp[0]);
		return [$res, $temp[1]];
	}

	private function processList($items) {
		for ($i = 0; $i < count($items); $i++) {
			$items[$i] = $this->loadExtras($items[$i]);
		}
		return $items;
	}

	private function loadExtras($item) {
		if (isset($item['new_data']) && !empty($item['new_data'])) {
			$data = json_decode($item['new_data'], true);
			$rawData = $data['raw_data'];

			$userType = isset($data['user_type']) ? $data['user_type'] : 'students';
			if ($userType === 'students') {
				loadClass($this->load, 'students');
				if ($item['student_id']) {
					$student = $this->students->getWhere(['id' => $item['student_id']], $count, 0, 1, false);
					if ($student) {
						$student = $student[0];
						$item['firstname'] = $student->firstname;
						$item['lastname'] = $student->lastname;
						$item['othernames'] = $student->othernames;
					} else {
						$item['firstname'] = null;
						$item['lastname'] = null;
						$item['othernames'] = null;
					}
				}
			}

			if ($userType === 'non-students') {
				loadClass($this->load, 'users_custom');
				if ($item['student_id']) {
					$users = $this->users_custom->getWhere(['id' => $item['student_id']], $count, 0, 1, false);
					if ($users) {
						$users = $users[0];
						$item['firstname'] = null;
						$item['lastname'] = $users->name;
						$item['othernames'] = null;
					} else {
						$item['firstname'] = null;
						$item['lastname'] = null;
						$item['othernames'] = null;
					}
				}
			}

			if ($userType === 'applicants') {
				loadClass($this->load, 'applicants');
				if ($item['student_id']) {
					$applicants = $this->applicants->getWhere(['applicant_id' => $item['student_id']], $count, 0, 1, false);
					if ($applicants) {
						$applicants = $applicants[0];
						$item['firstname'] = $applicants->firstname;
						$item['lastname'] = $applicants->lastname;
						$item['othernames'] = $applicants->othernames;
					} else {
						$item['firstname'] = null;
						$item['lastname'] = null;
						$item['othernames'] = null;
					}
				}
			}

			$item['user_type'] = $userType;
			$payload = [
				'response_error' => $data['response_error'] ?? null,
				'response_code' => @$data['response_code'] ?: (@$rawData['status'] ?: null),
				'response_msg' => @$data['response_msg'] ?: (@$rawData['message'] ?: null),
				'payment_description' => @$data['payment_description'] ?: null,
				'transaction_ref' => @$data['transaction_ref'] ?: null,
			];
			unset($item['new_data']);
			$item['remita_data'] = $payload;
		}

		return $item;
	}

}
