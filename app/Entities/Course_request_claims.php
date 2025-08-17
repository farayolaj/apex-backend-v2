<?php
namespace App\Entities;

use App\Models\Crud;
use App\Libraries\EntityLoader;

use App\Traits\ResultManagerTrait;
use App\Enums\ClaimEnum as ClaimType;
/**
 * This class is automatically generated based on the structure of the table.
 * And it represent the model of the course_request_claims table
 */
class Course_request_claims extends Crud
{

	/**
	 * This is the entity name equivalent to the table name
	 * @var string
	 */
	protected static $tablename = "Course_request_claims";

	/**
	 * This array contains the field that can be null
	 * @var array
	 */
	public static $nullArray = ['exam_type', 'enrolled', 'with_score', 'with_score_amount', 'with_score_extra', 'with_score_unit', 'with_score_extra_amount', 'total_amount', 'updated_at', 'approval_status'];

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
	public static $displayField = 'exam_type';

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
	public static $typeArray = ['course_id' => 'int', 'session_id' => 'int', 'course_manager_id' => 'int', 'exam_type' => 'varchar', 'enrolled' => 'int', 'with_score' => 'int', 'user_request_id' => 'int', 'with_score_amount' => 'decimal', 'with_score_extra' => 'decimal', 'with_score_unit' => 'int', 'with_score_extra_amount' => 'decimal', 'total_amount' => 'decimal', 'created_at' => 'timestamp', 'updated_at' => 'timestamp', 'approval_status' => 'tinyint'];

	/**
	 * This is a dictionary that map a field name with the label name that
	 * will be shown in a form
	 * @var array
	 */
	public static $labelArray = ['id' => '', 'course_id' => '', 'session_id' => '', 'course_manager_id' => '', 'exam_type' => '', 'enrolled' => '', 'with_score' => '', 'user_request_id' => '', 'with_score_amount' => '', 'with_score_extra' => '', 'with_score_unit' => '', 'with_score_extra_amount' => '', 'total_amount' => '', 'created_at' => '', 'updated_at' => '', 'approval_status' => ''];

	/**
	 * Associative array of fields in the table that have default value
	 * @var array
	 */
	public static $defaultArray = ['exam_type' => 'paper', 'enrolled' => '0', 'with_score' => '0', 'created_at' => 'current_timestamp()', 'approval_status' => '0'];

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
		, 'session' => array('session_id', 'id')
		, 'course_manager' => array('course_manager_id', 'id')
		, 'user_request' => array('user_request_id', 'id'),
	];

	/**
	 * This are the action allowed to be performed on the entity and this can
	 * be changed in the formConfig model file for flexibility
	 * @var array
	 */
	public static $tableAction = ['delete' => 'delete/course_request_claims', 'edit' => 'edit/course_request_claims'];

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

	public function getSession_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'session','display'=>'session_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'session_name' as value from 'session' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('session', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='session_id' id='session_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
		<label for='session_id'>Session</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='session_id' id='session_id' class='form-control'>
					$option
				</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getCourse_manager_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'course_manager','display'=>'course_manager_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'course_manager_name' as value from 'course_manager' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('course_manager', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='course_manager_id' id='course_manager_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
		<label for='course_manager_id'>Course Manager</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='course_manager_id' id='course_manager_id' class='form-control'>
					$option
				</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getExam_typeFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='exam_type'>Exam Type</label>
		<input type='text' name='exam_type' id='exam_type' value='$value' class='form-control' required />
	</div>";
	}

	public function getEnrolledFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='enrolled'>Enrolled</label>
		<input type='text' name='enrolled' id='enrolled' value='$value' class='form-control' required />
	</div>";
	}

	public function getWith_scoreFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='with_score'>With Score</label>
		<input type='text' name='with_score' id='with_score' value='$value' class='form-control' required />
	</div>";
	}

	public function getUser_request_idFormField($value = '')
	{
		$fk = null;
		//change the value of this variable to array('table'=>'user_request','display'=>'user_request_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'user_request_name' as value from 'user_request' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('user_request', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

		if (is_null($fk)) {
			return $result = "<input type='hidden' name='user_request_id' id='user_request_id' value='$value' class='form-control' />";
		}

		if (is_array($fk)) {

			$result = "<div class='form-group'>
		<label for='user_request_id'>User Request</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='user_request_id' id='user_request_id' class='form-control'>
					$option
				</select>";
			$result .= "</div>";
			return $result;
		}

	}

	public function getWith_score_amountFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='with_score_amount'>With Score Amount</label>
		<input type='text' name='with_score_amount' id='with_score_amount' value='$value' class='form-control' required />
	</div>";
	}

	public function getWith_score_extraFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='with_score_extra'>With Score Extra</label>
		<input type='text' name='with_score_extra' id='with_score_extra' value='$value' class='form-control' required />
	</div>";
	}

	public function getWith_score_unitFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='with_score_unit'>With Score Unit</label>
		<input type='text' name='with_score_unit' id='with_score_unit' value='$value' class='form-control' required />
	</div>";
	}

	public function getWith_score_extra_amountFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='with_score_extra_amount'>With Score Extra Amount</label>
		<input type='text' name='with_score_extra_amount' id='with_score_extra_amount' value='$value' class='form-control' required />
	</div>";
	}

	public function getTotal_amountFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='total_amount'>Total Amount</label>
		<input type='text' name='total_amount' id='total_amount' value='$value' class='form-control' required />
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

	public function getApproval_statusFormField($value = '')
	{
		return "<div class='form-group'>
		<label for='approval_status'>Approval Status</label>
		<input type='text' name='approval_status' id='approval_status' value='$value' class='form-control' required />
	</div>";
	}

	protected function getCourse()
	{
		$query = 'SELECT * FROM course WHERE id=?';
		if (!isset($this->array['id'])) {
			return null;
		}
		$id = $this->array['id'];
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		include_once 'Courses.php';
		return new Courses($result[0]);
	}

	protected function getSession()
	{
		$query = 'SELECT * FROM session WHERE id=?';
		if (!isset($this->array['id'])) {
			return null;
		}
		$id = $this->array['id'];
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		include_once 'Sessions.php';
		return new Sessions($result[0]);
	}

	protected function getCourse_manager()
	{
		$query = 'SELECT * FROM course_manager WHERE id=?';
		if (!isset($this->array['id'])) {
			return null;
		}
		$id = $this->array['id'];
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		include_once 'Course_manager.php';
		return new Course_manager($result[0]);
	}

	protected function getUser_request()
	{
		$query = 'SELECT * FROM user_request WHERE id=?';
		if (!isset($this->array['id'])) {
			return null;
		}
		$id = $this->array['id'];
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		include_once 'User_requests.php';
		return new User_requests($result[0]);
	}

	public function getOldestCourseClaims($course, $session)
	{
		$query = "SELECT * FROM course_request_claims WHERE course_id=? AND session_id=? and status = '0'
                ORDER BY created_at ASC, id ASC LIMIT 1";
		return $this->query($query, [$course, $session]);
	}

	/**
	 * @param $session
	 * @param null $course
	 * @param null $claimType
	 * @return mixed
	 */
	public function getNewestCourseClaims($session, $course = null)
	{
		$query = "SELECT * FROM course_request_claims WHERE session_id=? and status = '0' ";
		if ($course) {
			$query .= " and course_id = '$course' ";
		}
		$query .= " ORDER BY created_at desc LIMIT 1";
		return $this->query($query, [$session]);
	}

	public function getExistingCourseClaims($session, $course = null, $claimType = null)
	{
		$query = "SELECT * FROM course_request_claims a join course_request_claim_items b on b.course_request_claim_id = a.id 
         WHERE a.session_id=? ";
		if ($course) {
			$query .= " and a.course_id = '$course' ";
		}
		if ($claimType) {
			$query .= " and b.claim_type = '$claimType' ";
		}
		$query .= " ORDER BY a.created_at desc LIMIT 1";
		return $this->query($query, [$session]);
	}

	public function getCountCourseClaims($course, $session)
	{
		$query = "SELECT count(course_id) as total FROM course_request_claims WHERE course_id=? AND session_id=? and status = '0' ";
		return $this->query($query, [$course, $session])[0]['total'];
	}

	public function getSumCourseScoreClaims($course, $session)
	{
		$query = "SELECT sum(with_score) as total FROM course_request_claims WHERE course_id=? AND session_id=? and status = '1' ";
		return $this->query($query, [$course, $session])[0]['total'];
	}

	public function APIList($filterList, $queryString, $start, $len, $orderBy): array
	{
		if (isset($filterList['a.request_status']) && $filterList['a.request_status'] === 'all') {
			unset($filterList['a.request_status']);
		}
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];
		$currentUser = WebSessionManager::currentAPIUser();

		$filterQuery .= ($filterQuery ? " and " : " where ") . " a.user_id = '{$currentUser->id}' and exists(
			SELECT * from course_request_claims b where a.id = b.user_request_id
		) ";

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by a.created_at desc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->escapeString($start);
			$len = $this->db->escapeString($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}
		$query = "SELECT SQL_CALC_FOUND_ROWS a.id,a.request_no,a.title,a.amount,a.description,a.request_status,
       		created_at,a.user_id from user_requests a $filterQuery";

		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->getResultArray();
		$res2 = $this->db->query($query2);
		$res2 = $res2->getResultArray();
		$res = $this->processList($res);
		return [$res, $res2];
	}

	private function processList($items)
	{
		EntityLoader::loadClass($this, 'users_new');
		for ($i = 0; $i < count($items); $i++) {
			$items[$i] = $this->loadExtras($items[$i]);
		}
		return $items;
	}

	public function loadExtras(array $item, $isDetail = false): array
	{
		if ($isDetail) {
			if (isset($item['beneficiaries'])) {
				$item['beneficiaries'] = ($item['beneficiaries'] != '') ? json_decode($item['beneficiaries'], true) : [];
			}

			if (isset($item['action_timeline'])) {
				$actionLine = ($item['action_timeline'] != '') ? json_decode($item['action_timeline'], true) : [];
				if (is_null($item['stage'])) {
					$item['stage'] = 'director';
					EntityLoader::loadClass($this, 'user_requests');
					$currentUser = WebSessionManager::currentAPIUser();
					$tempAction = User_requests::actionTimelineData($currentUser, null, null, true);
					$actionLine = array_map(function ($item) {
						return [
							'stage' => $item['stage'] ?? 'director',
							'state' => '',
							'action' => '',
							'date_performed' => '',
						];
					}, json_decode($tempAction, true));
				}
				$item['action_timeline'] = $this->removeRedundantTimeline($actionLine);
			}

			if (isset($item['id'])) {
				$courses = $this->getCoursesClaims($item['id']);
				$item['course_breakdown'] = $courses ?: [];
			}
		}

		if (isset($item['id'])) {
			$requestSession = $this->getCourseClaimsSession($item['id']);
			$item['session'] = @$requestSession[0]['session'] ?: '';

			$url = 'web/claims_request_html/' . hashids_encrypt($item['id']) . '/' . hashids_encrypt($item['user_id']);
			$url = ResultManagerTrait::generateReportLink('claims_request.html', $url);
			$item['print_url'] = $url;
		}

		return $item;
	}

	private function removeRedundantTimeline(array $data): ?array
	{
		$fieldsToRemove = ['firstname', 'lastname', 'othernames', 'assignee_to', 'user_id'];

		return removeRedundantArrayKey($data, $fieldsToRemove);
	}

	public function getCoursesClaims($userRequest)
	{
		$query = "SELECT a.*,c.date as session,b.title,b.code from course_request_claims a 
            left join courses b on b.id = a.course_id
			join sessions c on c.id = a.session_id where a.user_request_id = ? ";
		$result = $this->query($query, [$userRequest]);
		if (!$result) {
			return [];
		}
		$payload = [];
		foreach ($result as $item) {
			$temp = $item;
			if ($item['exam_type'] === claimType::DEPARTMENTAL_RUN_COST) {
				$temp['is_department_running_cost'] = true;
			}
			if ($item['exam_type'] === claimType::COURSE_AUTHOR_COMMITTEE) {
				$temp['is_course_committee'] = true;
			}
			if ($item['exam_type'] === claimType::LOGISTICS_ALLOWANCE) {
				$temp['is_logistic_allowance'] = true;
			}
			$temp['claim_item_list'] = $this->getCourseClaimItems($item['id']) ?: [];
			$payload[] = $temp;
		}

		return $payload;
	}

	private function getCourseClaimItems($courseClaim)
	{
		$query = "SELECT * from course_request_claim_items where course_request_claim_id = ?";
		return $this->query($query, [$courseClaim]);
	}

	public function getCourseClaimsSession($userRequest)
	{
		$query = "SELECT c.date as session, a.created_at from course_request_claims a join sessions c on c.id = a.session_id
                where a.user_request_id = ? group by a.user_request_id, c.date, a.created_at";
		return $this->query($query, [$userRequest]);
	}

	public function getAllCourseClaimItems($courseClaim)
	{
		$query = "SELECT * from course_request_claim_items where course_request_claim_id in ($courseClaim) ";
		return $this->query($query, [$courseClaim]);
	}

	public function deleteClaimRequestItem($requestNo)
	{
		$query = "DELETE FROM course_request_claim_items WHERE course_request_claim_id IN ( 
			SELECT id FROM course_request_claims WHERE claim_no = ? )";
		if (!$this->query($query, [$requestNo])) {
			return false;
		}

		$query1 = "DELETE FROM course_request_claims WHERE claim_no = ?";
		if (!$this->query($query1, [$requestNo])) {
			return false;
		}

		$query2 = "DELETE FROM user_requests WHERE request_no = ?";
		if (!$this->query($query2, [$requestNo])) {
			return false;
		}
		return true;
	}

	public function getCourseClaimManager($course, $session)
	{
		$query = "SELECT id, essential_inline_waiver as waiver, exam_type from course_manager where course_id = ? and session_id = ? 
                order by date_created desc limit 1";
		return $this->query($query, [$course, $session]);
	}

}
