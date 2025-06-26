<?php
require_once 'application/models/Crud.php';
require_once APPPATH . 'constants/PaymentFeeDescription.php';
require_once APPPATH . 'constants/FeeDescriptionCode.php';
require_once APPPATH . 'traits/CommonTrait.php';
require_once APPPATH . 'constants/CommonSlug.php';

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the students table.
 */
class Students extends Crud
{
	protected static $tablename = 'Students';
	/* this array contains the field that can be null*/
	static $nullArray = ['referee', 'alternative_email', 'verified_by', 'verify_attempt', 'screened_by', 'screening_attempt', 'date_created'];
	static $compositePrimaryKey = [];
	static $uploadDependency = [];
	/*this array contains the fields that are unique*/
	static $uniqueArray = [];
	/*this is an associative array containing the fieldname and the type of the field*/
	static $typeArray = ['firstname' => 'varchar', 'othernames' => 'varchar', 'lastname' => 'varchar', 'gender' => 'varchar', 'DoB' => 'varchar', 'phone' => 'varchar', 'marital_status' => 'varchar', 'religion' => 'varchar', 'contact_address' => 'varchar', 'postal_address' => 'varchar', 'profession' => 'varchar', 'state_of_origin' => 'varchar', 'lga' => 'varchar', 'nationality' => 'varchar', 'reg_num' => 'varchar', 'passport' => 'varchar', 'full_image' => 'varchar', 'next_of_kin' => 'varchar', 'next_of_kin_phone' => 'varchar', 'next_of_kin_address' => 'varchar', 'referee' => 'text', 'alternative_email' => 'varchar', 'user_login' => 'varchar', 'user_pass' => 'varchar', 'session_key' => 'mediumtext', 'user_agent' => 'text', 'ip_address' => 'varchar', 'last_logged_in' => 'varchar', 'active' => 'tinyint', 'is_verified' => 'tinyint', 'verified_by' => 'text', 'verify_attempt' => 'tinyint', 'date_verified' => 'datetime', 'date_created' => 'timestamp', 'password' => 'varchar', 'document_verification' => 'varchar', 'verify_comments' => 'text'];
	/*this is a dictionary that map a field name with the label name that will be shown in a form*/
	static $labelArray = ['ID' => '', 'firstname' => '', 'othernames' => '', 'lastname' => '', 'gender' => '', 'DoB' => '', 'phone' => '', 'marital_status' => '', 'religion' => '', 'contact_address' => '', 'postal_address' => '', 'profession' => '', 'state_of_origin' => '', 'lga' => '', 'nationality' => '', 'reg_num' => '', 'passport' => '', 'full_image' => '', 'next_of_kin' => '', 'next_of_kin_phone' => '', 'next_of_kin_address' => '', 'referee' => '', 'alternative_email' => '', 'user_login' => '', 'user_pass' => '', 'session_key' => '', 'user_agent' => '', 'ip_address' => '', 'last_logged_in' => '', 'active' => '', 'is_verified' => '', 'verified_by' => '', 'verify_attempt' => '', 'date_verified' => '', 'date_created' => '', 'password' => '', 'document_verification' => '', 'verify_comments' => ''];
	/*associative array of fields that have default value*/
	static $defaultArray = ['date_created' => 'current_timestamp()'];
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
	static $documentField = []; //array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

	static $relation = [];

	static $tableAction = ['delete' => 'delete/students', 'edit' => 'edit/students'];

	static $apiSelectClause = ['id', 'firstname', 'lastname', 'othernames', 'gender', 'dob', 'phone', 'contact_address', 'state_of_origin'];

	static $uploadFields = ['matric_number', 'surname', 'firstname', 'other_names', 'programme', 'phone_number', 'user_login', 'alternative_email', 'gender', 'date_of_birth', 'marital_status', 'entry_mode', 'mode_of_study', 'academic_level', 'interactive_center', 'exam_center', 'teaching_subject', 'nationality', 'state_of_origin', 'lga', 'level_of_admission', 'session_of_admission', 'current_session', 'min_programme_duration', 'max_programme_duration', 'has_matric_number', 'has_institution_email', 'application_number'];

	public function __construct($array = [])
	{
		parent::__construct($array);
	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getFirstnameFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='firstname' >Firstname</label>
		<input type='text' name='firstname' id='firstname' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getOthernamesFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='othernames' >Othernames</label>
		<input type='text' name='othernames' id='othernames' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getLastnameFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='lastname' >Lastname</label>
		<input type='text' name='lastname' id='lastname' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getGenderFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='gender' >Gender</label>
		<input type='text' name='gender' id='gender' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getDoBFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='DoB' >DoB</label>
		<input type='text' name='DoB' id='DoB' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getPhoneFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='phone' >Phone</label>
		<input type='text' name='phone' id='phone' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getMarital_statusFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='marital_status' >Marital Status</label>
		<input type='text' name='marital_status' id='marital_status' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getReligionFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='religion' >Religion</label>
		<input type='text' name='religion' id='religion' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getContact_addressFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='contact_address' >Contact Address</label>
		<input type='text' name='contact_address' id='contact_address' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getPostal_addressFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='postal_address' >Postal Address</label>
		<input type='text' name='postal_address' id='postal_address' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getProfessionFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='profession' >Profession</label>
		<input type='text' name='profession' id='profession' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getState_of_originFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='state_of_origin' >State Of Origin</label>
		<input type='text' name='state_of_origin' id='state_of_origin' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getLgaFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='lga' >Lga</label>
		<input type='text' name='lga' id='lga' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getNationalityFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='nationality' >Nationality</label>
		<input type='text' name='nationality' id='nationality' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getPassportFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='passport' >Passport</label>
		<input type='text' name='passport' id='passport' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getFull_imageFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='full_image' >Full Image</label>
		<input type='text' name='full_image' id='full_image' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getNext_of_kinFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='next_of_kin' >Next Of Kin</label>
		<input type='text' name='next_of_kin' id='next_of_kin' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getNext_of_kin_phoneFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='next_of_kin_phone' >Next Of Kin Phone</label>
		<input type='text' name='next_of_kin_phone' id='next_of_kin_phone' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getNext_of_kin_addressFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='next_of_kin_address' >Next Of Kin Address</label>
		<input type='text' name='next_of_kin_address' id='next_of_kin_address' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getRefereeFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='referee' >Referee</label>
<textarea id='referee' name='referee' class='form-control' >$value</textarea>
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getAlternative_emailFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='alternative_email' >Alternative Email</label>
	<input type='email' name='alternative_email' id='alternative_email' value='$value' class='form-control'  />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getUser_loginFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='user_login' >User Login</label>
		<input type='text' name='user_login' id='user_login' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getUser_passFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='user_pass' >User Pass</label>
		<input type='text' name='user_pass' id='user_pass' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getSession_keyFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='session_key' >Session Key</label>
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getUser_agentFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='user_agent' >User Agent</label>
<textarea id='user_agent' name='user_agent' class='form-control' required>$value</textarea>
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getIp_addressFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='ip_address' >Ip Address</label>
		<input type='text' name='ip_address' id='ip_address' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getLast_logged_inFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='last_logged_in' >Last Logged In</label>
		<input type='text' name='last_logged_in' id='last_logged_in' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getActiveFormField($value = '')
	{

		return "<div class='form-group'>
	<label class='form-checkbox'>Active</label>
	<select class='form-control' id='active' name='active' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getIs_verifiedFormField($value = '')
	{

		return "<div class='form-group'>
	<label class='form-checkbox'>Is Verified</label>
	<select class='form-control' id='is_verified' name='is_verified' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getVerified_byFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='verified_by' >Verified By</label>
<textarea id='verified_by' name='verified_by' class='form-control' >$value</textarea>
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getVerify_attemptFormField($value = '')
	{

		return "<div class='form-group'>
	<label class='form-checkbox'>Verify Attempt</label>
	<select class='form-control' id='verify_attempt' name='verify_attempt' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getDate_verifiedFormField($value = '')
	{

		return " ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getIs_screenedFormField($value = '')
	{

		return "<div class='form-group'>
	<label class='form-checkbox'>Is Screened</label>
	<select class='form-control' id='is_screened' name='is_screened' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getScreened_byFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='screened_by' >Screened By</label>
<textarea id='screened_by' name='screened_by' class='form-control' >$value</textarea>
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getScreening_attemptFormField($value = '')
	{

		return "<div class='form-group'>
	<label class='form-checkbox'>Screening Attempt</label>
	<select class='form-control' id='screening_attempt' name='screening_attempt' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

	}

	/**
	 * @param mixed $type
	 * @return void
	 */
	public function getState_of_originOptions($type = '')
	{
		exit("i am trying to load state and origin options here");
	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	public function getDate_createdFormField($value = '')
	{

		return " ";

	}

	/**
	 * @param mixed $username
	 * @return bool|Students
	 */
	public function checkStudentLogin($username)
	{
		$query = "select students.* from students where user_login=? and active = '1' or exists (select * from academic_record where  student_id=students.id and matric_number=?)";
		$result = $this->query($query, [$username, $username]);
		if (!$result) {
			return false;
		}
		return new Students($result[0]);
	}

	public function updatePassportPath()
	{

		$passport = studentImagePath($this->passport);
		$this->passport = $passport;

		return $this->passport;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function getDashboardData()
	{
		$result = [];
		$this->updatePassportPath();
		$biodata = $this->toArray();
		unset($biodata['user_pass']);
		unset($biodata['password']);
		unset($biodata['user_login']);
		unset($biodata['id']);

		$academic = $this->academic_record;
		$biodata['matric_number'] = $academic->matric_number;
		$biodata['exam_center'] = $academic->exam_center;
		$result['bioData'] = $biodata;
		$medicalRecord = $this->Medical_record ?? null;
		$result['medicalRecord'] = $medicalRecord;
		$programDetails = $this->getProgramDetails() ?? null;
		if (@$programDetails['level']) {
			$programDetails['level'] = formatStudentLevel($programDetails['level']);
		}
		$result['programmeDetails'] = $programDetails;
		$session = $academic->current_session;
		$result['registered_course'] = $this->getCourseEnrollment($session, null, null, 10);
		$result['cgpa'] = null;
		return $result;
	}

	/**
	 * @param mixed $value
	 * @return bool|Medical_record
	 */
	public function getMedical_record($value = '')
	{
		$query = "SELECT * from medical_record where student_id=?";
		$result = $this->query($query, [$this->id]);
		if (!$result) {
			return false;
		}
		loadClass($this->load, 'medical_record');
		return new Medical_record($result[0]);
	}

	/**
	 * @param mixed $value
	 * @return bool|Academic_record
	 */
	public function getAcademic_record($value = '')
	{
		$query = "SELECT * from academic_record where student_id=? order by id desc limit 1";
		$result = $this->query($query, [$this->id]);
		if (!$result) {
			return false;
		}
		loadClass($this->load, 'academic_record');
		return new Academic_record($result[0]);
	}

	/**
	 * @return array|<missing>
	 */
	protected function getStudentTransaction()
	{
		$query = "SELECT transaction.*,programme.name as programme_name from transaction left join programme on programme.id = transaction.programme_id where student_id=?";
		$result = $this->query($query, [$this->id]);
		if (!$result) {
			return [];
		}
		$content = [];
		$currentSession = get_setting('active_session_student_portal');
		foreach ($result as $item) {
			if ($item['payment_option']) {
				$item['is_part_payment'] = $currentSession == $item['session'] && !isPaymentComplete($item['payment_option']) ? true : false;
				$item['encoded_real_payment_id'] = hashids_encrypt($item['real_payment_id']);
				$item['is_current_sch_fee'] = $currentSession == $item['session'] && ($item['payment_id'] == PaymentFeeDescription::SCH_FEE_FIRST || $item['payment_id'] == PaymentFeeDescription::SCH_FEE_SECOND);
				$content[] = $item;
			}
		}
		return $content;
	}

	/**
	 * @return array
	 */
	protected function getStudentTransactionArchive()
	{
		$query = "SELECT * from transaction_archive where student_id=?";
		$result = $this->query($query, [$this->id]);
		if (!$result) {
			return [];
		}
		$contents = [];
		foreach ($result as $res) {
			$res['trans_type'] = 'student_trans';
			$contents[] = $res;
		}
		return $contents;
	}

	/**
	 * @param mixed $semester
	 * @param mixed $id
	 * @return bool|<missing>
	 */
	public function getStudentCurrentSessionPayment($semester = null, $id = null)
	{
		$schoolFeesCode = get_setting('school_fees_code');
		$query = "SELECT students.id as student_id, students.*, academic_record.*, academic_record.programme_id as programme_id_code, academic_record.current_session as current_session_code, fee_description.*, fee_description.code as payment_code, transaction.*, transaction.session as trans_session from students left join academic_record on academic_record.student_id = students.id left join transaction on transaction.student_id = students.id left join fee_description on fee_description.id = transaction.payment_id where transaction.student_id = ? and fee_description.code = ? and transaction.session = academic_record.current_session";
		if ($semester) {
			$query .= " and transaction.payment_id = '$semester'";
		}
		$id = $id ?? $this->id;
		$results = $this->query($query, [$id, $schoolFeesCode]);
		if (!$results) {
			return false;
		}
		foreach ($results as $res) {
			if (isset($res['payment_status']) && $res['payment_status'] == '01' || isset($res['payment_status']) && $res['payment_status'] == '00') {
				return $res;
			}
		}
		return $results[0];
	}

	/**
	 * @param mixed $id
	 * @return bool|<missing>
	 */
	public function getStudentAcademicRecord($id = null, $matric = null)
	{
		$query = "SELECT distinct students.id as student_id, students.*, programme.department_id, programme.faculty_id,
       	academic_record.matric_number, academic_record.programme_id, academic_record.current_level, academic_record.year_of_entry,
       	academic_record.entry_mode, academic_record.current_session,academic_record.id as academic_id,academic_record.has_institution_email from students join academic_record on
        academic_record.student_id = students.id join programme on programme.id = academic_record.programme_id where students.id = ?";
		if ($matric) {
			$query .= " and academic_record.matric_number = '$matric' ";
		}
		$id = $id ?? $this->id;
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		return $result[0];
	}

	/**
	 * @param mixed $id
	 * @return bool|<missing>
	 */
	public function getStudentAcademicRecordOnly($id = null)
	{
		$query = "SELECT a.id as student_id, b.matric_number, b.programme_id, b.current_level, b.year_of_entry, b.entry_mode, b.current_session,
       	b.application_number,a.user_login,a.alternative_email from students a join academic_record b on b.student_id = a.id 
    	where a.id = ?";
		$id = $id ?? $this->id;
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		return $result[0];
	}

	/**
	 * You might be tempted to add payment_status to verify payment was valid, but don't do that
	 * because the student is just using these to get session that exists in transaction only
	 * whether paid or not
	 * @param boolean $code [description]
	 * @param  [type]  $semester [description]
	 * @param mixed $semester
	 * @return [type]            [description]
	 */
	public function getAllPaidSession($code = false, $semester = null)
	{
		$code = $code ? $code : get_setting('school_fees_code');
		$semesterName = ($semester && $semester == 'first') ? 1 : 2;
		$query = "SELECT sessions.id,date FROM sessions left join transaction on transaction.session=sessions.id 
			left join fee_description on fee_description.id=transaction.payment_id where transaction.student_id=? and 
		    fee_description.code=? ";
		if ($semester) {
			$query .= " and transaction.payment_id = '$semesterName'";
		}
		$query .= 'order by date desc';
		return $this->query($query, [$this->id, $code]);
	}

	/**
	 * @param mixed $code
	 * @param mixed $semester
	 * @return <missing>|array
	 */
	public function getAllPaidTransactionSession($code = false, $semester = null)
	{
		$code = $code ?: get_setting('school_fees_code');
		$semesterName = ($semester && $semester == 'first') ? 1 : 2;
		$query = "SELECT distinct sessions.id,date,transaction.level FROM sessions left join transaction on 
			transaction.session=sessions.id left join fee_description on fee_description.id=transaction.payment_id 
            where transaction.student_id=? and fee_description.code=? and payment_status in ('00', '01') ";
		if ($semester) {
			$query .= " and transaction.payment_id = '$semesterName'";
		}
		$query .= 'order by date desc';
		$result = $this->query($query, [$this->id, $code]);
		return $result ?: [];
	}

	/**
	 * Validate student session against transaction if it exists
	 * @param int $session [description]
	 * @param mixed $semester
	 * @return boolean          [description]
	 */
	public function isValidSession($session, $semester = null)
	{
		if (!$session) {
			return false;
		}
		$paymentValidation = get_setting('session_semester_payment_start');
		$semester = ($session >= $paymentValidation) ? $semester : null;
		$sessions = $this->getAllPaidSession(false, $semester);
		foreach ($sessions as $ses) {
			if ($ses['id'] == $session) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param mixed $session
	 * @param mixed $semester
	 * @return bool
	 */
	public function isValidPaidSession($session, $semester = null)
	{
		if (!$session) {
			return false;
		}
		$paymentValidation = get_setting('session_semester_payment_start');
		$semester = ($session >= $paymentValidation) ? $semester : null;
		$sessions = $this->getAllPaidTransactionSession(false, $semester);
		foreach ($sessions as $ses) {
			if ($ses['id'] == $session) {
				return true;
			}
		}
		return false;
	}

	/**
	 * [getCourseEnrollment description]
	 * @param mixed $session
	 * @param mixed $semester
	 * @param mixed $currentLevel
	 * @param boolean $limit [description]
	 * @return [type] [description]
	 */
	public function getCourseEnrollment($session, $semester = null, $currentLevel = null, $limit = false)
	{
		$courseSemester = ($semester && $semester == 'first') ? 1 : 2;
		$query = "SELECT distinct courses.id as main_course_id, courses.code as course_code, courses.title as course_title, courses.description,
        course_enrollment.course_id as course_enrollment_id, course_enrollment.course_unit as course_enrollment_unit,
        course_enrollment.course_status as course_enrollment_status, course_enrollment.semester as course_enrollment_semester,
	   courses.course_guide_url,course_manager.course_lecturer_id, staffs.firstname, staffs.othernames, staffs.lastname FROM
	   course_enrollment left join courses on courses.id=course_enrollment.course_id left join course_manager on
	    course_manager.course_id = courses.id left join users_new on users_new.id = course_manager.course_lecturer_id
	    left join staffs on staffs.id=users_new.user_table_id where course_enrollment.student_id=? and course_enrollment.session_id = ? ";
		if ($semester) {
			$query .= " and course_enrollment.semester = '$courseSemester'";
		}
		if ($currentLevel) {
			$query .= " and course_enrollment.student_level = '$currentLevel'";
		}
		$query .= " order by course_code";
		if ($limit) {
			$query .= " limit {$limit}";
		}
		return $this->query($query, [$this->id, $session]);
	}

	/**
	 * [getCourseEnrollment description]
	 * @param mixed $session
	 * @param mixed $semester
	 * @param mixed $currentLevel
	 * @return array|string|NULL [type] [description]
	 */
	public function getStudentCourseEnrollment($session, $semester = null, $currentLevel = null)
	{
		$courseSemester = ($semester && $semester == 'first') ? 1 : 2;
		$query = "SELECT distinct b.id as main_course_id, b.code as course_code, b.title as course_title, a.course_id as course_enrollment_id,
            a.course_unit as course_enrollment_unit, a.course_status as course_enrollment_status, a.semester as course_enrollment_semester,
            a.student_level,date(a.date_created) as course_date FROM course_enrollment a join courses b on b.id=a.course_id where a.student_id=? and a.session_id = ? ";
		if ($semester) {
			$query .= " and a.semester = '$courseSemester'";
		}
		if ($currentLevel) {
			$query .= " and a.student_level = '$currentLevel'";
		}
		$query .= " order by course_code";
		return $this->query($query, [$this->id, $session]);
	}

	/**
	 * [getAllCourseEnrollment description]
	 * @return array
	 */
	public function getAllCourseEnrollment()
	{
		$sessions = $this->getAllPaidSession();
		$result = [];
		foreach ($sessions as $val) {
			$result[$val['date']] = $this->getCourseEnrollment($val['id']);
		}
		return $result;
	}

	/**
	 * [getCourseScores description]
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $entryMode
	 * @param null $semester [description]
	 * @return array|string|NULL
	 */
	public function getCourseScores($session, $level, $entryMode, $semester = null)
	{
		$courseSemester = ($semester && $semester == 'first') ? 1 : 2;
		$query = "SELECT distinct courses.code as course_code, courses.title as course_title, course_enrollment.course_id as course_enrollment_course_id,
        course_enrollment.course_unit as course_enrollment_unit, course_enrollment.course_status as course_enrollment_status, course_enrollment.semester as course_enrollment_semester,
        course_enrollment.ca_score, course_enrollment.exam_score, course_enrollment.total_score,if(total_score >= pass_score, 'pass', 'fail') as remark from
        course_enrollment left join courses on courses.id = course_enrollment.course_id left join course_mapping on course_mapping.course_id = courses.id
        where course_enrollment.session_id =? and course_enrollment.student_id = ? and course_enrollment.student_level =? and course_mapping.mode_of_entry = ?";
		if ($semester != '') {
			$query .= " and course_enrollment.semester = '$courseSemester'";
		}
		$result = $this->query($query, [$session, $this->id, $level, $entryMode]);
		return $result;
	}

	public function getResultCourseScores($session, $level, $semester = null)
	{
		$courseSemester = ($semester && $semester == 'first') ? 1 : 2;
		$query = "SELECT distinct b.code as course_code, b.title as course_title, a.course_id as course_enrollment_course_id,
        a.course_unit as course_enrollment_unit, a.course_status as course_enrollment_status, a.semester as course_enrollment_semester,
        a.ca_score, a.exam_score, a.total_score from course_enrollment a left join courses b on b.id = a.course_id
        where a.session_id =? and a.student_id = ? and a.student_level =?";
		if ($semester != '') {
			$query .= " and a.semester = '$courseSemester'";
		}
		$result = $this->query($query, [$session, $this->id, $level]);
		return $result;
	}

	/**
	 * [getAllCourseScores description]
	 * @param  [type]  $entryMode [description]
	 * @param boolean $session [description]
	 * @param mixed $entryMode
	 * @return [type]             [description]
	 */
	public function getAllCourseScores($entryMode, $session = false)
	{
		$query = "SELECT distinct courses.code as course_code,
        course_enrollment.course_unit as course_enrollment_unit,course_enrollment.total_score, if(total_score >= pass_score, 'pass', 'fail') as remark from course_enrollment left join courses on courses.id = course_enrollment.course_id left join course_mapping on course_mapping.course_id = courses.id where course_enrollment.student_id = ? and course_mapping.mode_of_entry = ? ";
		if ($session) {
			$query .= " and course_enrollment.session_id = '$session'";
		}
		$result = $this->query($query, [$this->id, $entryMode]);
		return $result;
	}

	/**
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $semester
	 * @return array<string,array>
	 */
	public function getFailedCourses($session, $level, $semester)
	{
		$courseSemester = ($semester != '' && $semester == 'first') ? 1 : 2;
		$query = "SELECT course_enrollment.student_id, course_enrollment.course_id, course_enrollment.session_id as session, course_enrollment.student_level,
        course_enrollment.total_score, courses.code as course_code, courses.title as course_title, course_enrollment.course_unit, course_enrollment.course_status
		from course_enrollment join courses on courses.id = course_enrollment.course_id where course_enrollment.student_id = ? and
		course_enrollment.session_id = ? and course_enrollment.student_level = ?";
		if ($semester) {
			$query .= " and course_enrollment.semester = '$courseSemester'";
		}
		$result = $this->query($query, [$this->id, $session, $level]);
		if (!$result) {
			false;
		}
		$courseCodeArray = [];
		$courseUnitArray = [];
		$courseStatusArray = [];
		$academicRecord = $this->academic_record;
		loadClass($this->load, 'grades');
		foreach ($result as $data) {
			$gradeSession = $this->getClosestSessionId($this->id);
			if ($gradeSession == false) {
				exit("student with matric number {$academicRecord->matric_number} has no year of entry");
			}
			if ($this->grades->getGrade($data['total_score'], $gradeSession) == 'F' || $this->grades->getGrade($data['total_score'], $gradeSession) == '') {
				$coursesCodes = $data['course_code'];
				$courseCodeArray[] = $coursesCodes;

				$coursesUnits = $data['course_unit'];
				$courseUnitArray[] = $coursesUnits;

				$coursesStatus = $data['course_status'];
				$courseStatusArray[] = $coursesStatus;
			} else {
				echo "";
			}
		}
		return ['course_codes' => $courseCodeArray, 'course_units' => $courseUnitArray, 'course_status' => $courseStatusArray];
	}

	/**
	 * @param mixed $id
	 * @return bool|<missing>
	 */
	public function getClosestSessionId($id = null)
	{
		$id = $id ?: $this->id;
		$query = "select year_of_entry from grades join sessions on sessions.id=grades.year_of_entry where
            sessions.date <= (select date from sessions join academic_record on academic_record.year_of_entry=sessions.id
            where academic_record.student_id=? limit 1) order by date desc limit 1";
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		return $result[0]['year_of_entry'];
	}

	/**
	 * @param mixed $session
	 * @param mixed $level
	 */
	public function getResultDetails($session, $level)
	{
		$query = "SELECT exam_record.*, sessions.date as exam_date_session from exam_record  join sessions on sessions.id = exam_record.session_id where exam_record.session_id =? and exam_record.student_level=? and exam_record.student_id =? order by sessions.date asc";
		$result = $this->query($query, [$session, $level, $this->id]);
		return $result;
	}

	/**
	 * @param mixed $prerequisite_fee
	 * @param mixed $session
	 * @param mixed $level
	 * @return array<string,mixed>
	 * @deprecated - Not used again since we changed prerequisites approach using payment id directly
	 */
	public function transformPaymentParam($prerequisite_fee, $session, $level)
	{
		loadClass($this->load, 'sessions');
		$preqFee = $prerequisite_fee != 0 ? $this->fee_description->getWhere(['id' => $prerequisite_fee], $c, 0, null, false) : null;
		$prerequisiteDesc = $preqFee ? $preqFee[0]->description : null;
		$sess = $this->sessions->getWhere(['id' => $session], $c, 0, null, false);
		$session_name = $sess ? " - " . $sess[0]->date : '';

		// check globally if acceptance fee had been paid irrespective of the paid session
		if (PaymentFeeDescription::ACCEPTANCE_FEE == $prerequisite_fee) {
			$session = null;
			$level = null;
		}
		// check if prerequistes had been paid
		$checkPaymentTransaction = $this->payment->getPaymentTransaction($prerequisite_fee, $this->id, $session, $level);
		$paymentId = $this->payment->getPaymentByDescription($prerequisite_fee, $session);
		$prerequisite_fee = hashids_encrypt(@$paymentId['id']);

		return [
			'prerequisite' => @$prerequisite_fee ?? 0,
			'description' => $prerequisite_fee ? $prerequisiteDesc . $session_name : null,
			'paid' => $checkPaymentTransaction ? true : false,
			'paid_id' => $checkPaymentTransaction ? $checkPaymentTransaction->id : null,
		];
	}

	private function transformPrerequisiteDesc($description, $session): ?string
	{
		$preqFee = $description != 0 ? $this->fee_description->getWhere(['id' => $description], $c, 0, null, false) : null;
		$prerequisiteDesc = $preqFee ? $preqFee[0]->description : null;
		$sess = $this->sessions->getWhere(['id' => $session], $c, 0, null, false);
		$session_name = $sess ? " - " . $sess[0]->date : '';
		return $prerequisiteDesc . $session_name;
	}

	private function processSinglePaymentPrerequisite(object $payment, object $academic_record, $session, $level, bool $isVisible = false, $acceptanceSession = null)
	{
		loadClass($this->load, 'sessions');
		loadClass($this->load, 'fee_description');
		loadClass($this->load, 'sessions');
		$isVisiblePassed = true;
		$showPayment = true;
		$acceptanceSession = $acceptanceSession ?: get_setting('session_semester_payment_start');
		$isPaymentFull = true;
		$studentID = $academic_record->student_id;

		if ($isVisible) {
			$isVisiblePassed = $payment->is_visible == 1;
		}
		if ($isVisiblePassed && $payment) {
			$newSession = $payment->session != 0 ? $payment->session : $session;
			$preqDesc = $this->transformPrerequisiteDesc($payment->description, $newSession);
			// check globally if acceptance fee had been paid irrespective of the paid session
			if (PaymentFeeDescription::ACCEPTANCE_FEE == $payment->description) {
				$session = null;
				$level = null;
			}
			$paidTransaction = false;
			$checkPaymentTransaction = $this->payment->getPaymentTransaction($payment->description, $studentID, $session, $level);
			$paidTransaction = $checkPaymentTransaction && $isPaymentFull;
			$paidTransactionID = $paidTransaction ? $checkPaymentTransaction->id : null;
			$prerequisiteFee = hashids_encrypt($payment->id);
			$preqDesc = $prerequisiteFee ? $preqDesc : null;

			if ($checkPaymentTransaction && !isPaymentComplete($checkPaymentTransaction->payment_option)) {
				$isPaymentFull = false;
				$preqDesc .= ($preqDesc) ? " Balance" : null;
				$paymentCode = inferPaymentCode($payment->description);
				$checkPaymentTransaction = $this->payment->getPaymentTransaction($payment->description, $studentID, $session, null, $paymentCode);
				$paidTransaction = $checkPaymentTransaction ? true : false;
				$paidTransactionID = $paidTransaction ? $checkPaymentTransaction->id : null;
			}

			if ($payment->description == PaymentFeeDescription::ACCEPTANCE_FEE && $acceptanceSession > $academic_record->year_of_entry) {
				$showPayment = false;
			}

			if ($showPayment) {
				return [
					'prerequisite' => @$prerequisiteFee ?? 0,
					'description' => $preqDesc,
					'paid' => $paidTransaction,
					'paid_id' => $paidTransactionID,
				];
			}
		}
	}

	/**
	 * @param mixed $academic_record
	 * @param mixed $prerequisite_fee
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $isVisible
	 * @return array<int,array<string,mixed>>
	 */
	public function transformPaymentPrerequisiteParam($academic_record, $prerequisite_fee, $session, $level = null, $isVisible = false)
	{
		loadClass($this->load, 'payment');
		$prerequisites = [];
		$acceptanceSession = get_setting('session_semester_payment_start');

		foreach ($prerequisite_fee as $preq) {
			$payment = $this->payment->getWhere(['id' => $preq], $c, 0, null, false);
			$payment = @$payment[0];
			$preqResult = $this->processSinglePaymentPrerequisite($payment, $academic_record, $session, $level, $isVisible, $acceptanceSession);
			if ($preqResult) {
				$prerequisites[] = $preqResult;
			}
		}

		return $prerequisites;
	}

	/**
	 * @param mixed $prerequisite_fee
	 * @param mixed $session
	 * @param mixed $transactionRef
	 * @return array<string,mixed>
	 */
	public function transformDirectPaymentParam($prerequisite_fee, $session, $transactionRef = null, $paymentDesc = null)
	{
		loadClass($this->load, 'sessions');
		$preqDesc = null;
		if ($paymentDesc) {
			$preqDesc = $paymentDesc;
		} else {
			$preqDesc = $this->transformPrerequisiteDesc($prerequisite_fee, $session);
		}
		$paymentId = $this->payment->getPaymentByDescription($prerequisite_fee);
		$prerequisite_fee = hashids_encrypt(@$paymentId['id']);

		return [
			'transaction_ref' => $transactionRef ?? null,
			'payment_transaction' => "trans_normal",
			'prerequisite' => @$prerequisite_fee ?? 0,
			'description' => $prerequisite_fee ? $preqDesc : null,
			'paid' => false,
			'paid_id' => null,
		];
	}

	/**
	 * @param mixed $prerequisite_fee
	 * @param mixed $session
	 * @param mixed $payment
	 * @param mixed $isVisible
	 * @return array<string,mixed>
	 */
	public function transformDirectTransactionPaymentParam($prerequisite_fee, $session, array $payment, bool $isVisible = false, ?string $transactionRef = null, ?string $paymentDesc = null): ?array
	{
		loadClass($this->load, 'sessions');
		$preqDesc = $paymentDesc ?: $this->transformPrerequisiteDesc($prerequisite_fee, $session);
		$isVisiblePassed = true;

		if ($isVisible) {
			$isVisiblePassed = $payment['is_visible'] == 1;
		}
		$prerequisite_fee = hashids_encrypt($payment['id']);

		if ($isVisiblePassed) {
			return [
				'transaction_ref' => $transactionRef ?: null,
				'payment_transaction' => "trans_normal",
				'prerequisite' => @$prerequisite_fee ?? 0,
				'description' => $prerequisite_fee ? $preqDesc : null,
				'paid' => false,
				'paid_id' => null,
			];
		}
		return null;
	}

	public function transformDirectPreqTransactionParam(array $transaction, $prerequisite_fee, $session): array
	{
		loadClass($this->load, 'sessions');
		$preqDesc = @$transaction['payment_description'] ?: $this->transformPrerequisiteDesc($prerequisite_fee, $session);
		$prerequisite_fee = hashids_encrypt($transaction['real_payment_id']);

		return [
			'transaction_ref' => @$transaction['transaction_ref'] ?: null,
			'payment_transaction' => "trans_normal",
			'prerequisite' => @$prerequisite_fee ?? 0,
			'description' => $prerequisite_fee ? $preqDesc : null,
			'paid' => false,
			'paid_id' => null,
		];
	}

	/**
	 * @param mixed $transactionPaymentSession
	 * @return array<string,mixed>
	 */
	public function prepPaymentAmount(object $payment, $transactionPaymentSession = null)
	{
		$toReturn = [];

		$feeDesc = $this->fee_description->getWhere(['id' => $payment->description], $c, 0, null, false);
		$sess = $this->sessions->getWhere(['id' => $transactionPaymentSession], $c, 0, null, false);

		$description = $feeDesc ? $feeDesc[0]->description : null;
		$session_name = $sess ? $sess[0]->date : null;
		$date_due = null;
		$penalty_fee = 0;
		if (@$payment->date_due != '') {
			$dueDateParam = $payment->getFormatDueDateParam($payment);
			$penalty_fee = (int)$dueDateParam[0];
			$date_due = $dueDateParam[1];
		}
		$serviceCharge = (int)$payment->service_charge;
		$originalAmount = (int)$payment->amount;

		// if($discountAmount = $payment->validateVerificationFee($academic_record,$payment->id)){
		// 	if($discountAmount){
		// 		$originalAmount = $discountAmount;
		// 	}
		// }

		if ($payment->subaccount_amount) {
			$originalAmount += $payment->subaccount_amount;
		}

		$originalAmountService = $originalAmount + $serviceCharge;
		$totalAmount = ($originalAmountService + $penalty_fee);

		$toReturn['description'] = $transactionPaymentSession ? $description . " " . $session_name : $description;
		$toReturn['penalty_fee'] = $penalty_fee;
		$toReturn['date_due'] = $date_due;
		$toReturn['serviceCharge'] = $serviceCharge;
		$toReturn['originalAmount'] = $originalAmount;
		$toReturn['originalAmountService'] = $originalAmountService;
		$toReturn['totalAmount'] = $totalAmount;

		return $toReturn;
	}

	/**
	 * @param mixed $student
	 * @param mixed $code
	 * @param mixed $session
	 * @param mixed $paymentId
	 * @return bool|<missing>
	 */
	public function checkStudentPaymentByCode($student, $code, $session = null, $paymentId = null)
	{
		$query = "SELECT fee_description.code as payment_code,transaction.payment_id as payment_id,transaction.id as vid FROM
		transaction join fee_description ON fee_description.id = transaction.payment_id where transaction.student_id = ? and
		fee_description.code = ? and transaction.payment_status in ('00', '01')";
		$param = [$student, $code];

		if ($paymentId) {
			$query .= " and transaction.payment_id = ?";
			$param[] = $paymentId;
		}

		if ($session) {
			$query .= " and transaction.session = ?";
			$param[] = $session;
		}

		$result = $this->query($query, $param);
		if (!$result) {
			return false;
		}
		return $result[0];
	}

	/**
	 * @param mixed $student
	 * @param mixed $code
	 * @param mixed $session
	 * @param mixed $paymentId
	 * @return bool|<missing>
	 */
	public function checkStudentPendingPaymentByCode($student, $code, $session = null, $paymentId = null)
	{
		$query = "SELECT fee_description.code as payment_code,transaction.payment_id as payment_id,transaction.id as vid,
       		transaction.session,transaction.transaction_ref,transaction.real_payment_id FROM transaction join fee_description
       		ON fee_description.id = transaction.payment_id where transaction.student_id = ? and fee_description.code = ? and
    		transaction.payment_status not in ('00', '01')";
		$param = [$student, $code];
		if ($paymentId) {
			$query .= " and transaction.payment_id = ?";
			$param[] = $paymentId;
		}

		if ($session) {
			$query .= " and transaction.session = ?";
			$param[] = $session;
		}

		$result = $this->query($query, $param);
		if (!$result) {
			return false;
		}
		return $result[0];
	}

	/**
	 * Validate year of entry using just the pre-session layer[2018] of the session format [2019/2019]
	 * for comparison and evaluation
	 * @param $session
	 * @param mixed $yearOfEntry
	 * @return bool@param mixed $session
	 */
	private function validateYearOfEntry($session, $yearOfEntry): bool
	{
		$sessionLatter = explode('/', $session);
		$yearOfEntryLatter = explode('/', $yearOfEntry);

		if (isset($sessionLatter[0]) && isset($yearOfEntryLatter[0])) {
			return $sessionLatter[0] >= $yearOfEntryLatter[0];
		}
		return false;
	}

	/**
	 * @param mixed $academic_record
	 * @return array
	 */
	private function validateStudentSpecialPrerequisites($academic_record): array
	{
		$structure = [
			[
				'session' => '2022/2023',
				'ros' => 63,
				'sus' => 64,
				'ros_code' => 'RoS',
				'sus_code' => 'SuS',
			],
			[
				'session' => '2021/2022',
				'ros' => 4,
				'sus' => 18,
				'ros_code' => 'RoS',
				'sus_code' => 'SuS',
			],
			[
				'session' => '2020/2021',
				'ros' => 42,
				'sus' => 9,
				'ros_code' => 'RoS',
				'sus_code' => 'SuS',
			],
			[
				'session' => '2018/2019',
				'ros' => 41,
				'sus' => 3,
				'ros_code' => 'RoS',
				'sus_code' => 'SuS',
			],
		];

		loadClass($this->load, 'sessions');
		loadClass($this->load, 'transaction');

		$result = [];
		$yearOfEntry = $this->sessions->getSessionById($academic_record->year_of_entry)[0]['date'];
		$currentStudentSession = get_setting('active_session_student_portal');
		foreach ($structure as $key => $item) {
			$session = $this->sessions->getSessionIdByDate($item['session']);
			$prevSession = isset($structure[$key + 1]) ? $structure[$key + 1]['session'] : $item['session'];
			$prevSession = $this->sessions->getSessionIdByDate($prevSession);

			if ($this->validateYearOfEntry($item['session'], $yearOfEntry)) {
				// check if student was active in that session using the transaction table.
				$transaction = $this->checkStudentPaymentByCode($academic_record->student_id, FeeDescriptionCode::SCHOOL_FEE, $session);
				// If yes, check if ROS was paid in that session.
				if ($transaction) {
					// There is a special session for 2020/2021 which should come first
					if ($academic_record->year_of_entry != getSessionValue('session_2020_special')) {
						if ($academic_record->year_of_entry != $session && $session == $currentStudentSession) {
							// check if student paid sch-fees in prev session
							if ($prevSession && !$this->checkStudentPaymentByCode($academic_record->student_id, FeeDescriptionCode::SCHOOL_FEE, $prevSession)) {
								$transactionReactivation = $this->checkStudentPaymentByCode($academic_record->student_id, $item['ros_code'], null, $item['ros']);
								if (!$transactionReactivation) {
									$pendingPayment = $this->checkStudentPendingPaymentByCode($academic_record->student_id, $item['ros_code'], null, $item['ros']);
									if ($pendingPayment) {
										$result[] = $this->transformDirectPaymentParam($pendingPayment['payment_id'], $pendingPayment['session'], $pendingPayment['transaction_ref']);
									} else {
										$result[] = $this->transformDirectPaymentParam($item['ros'], $session);
									}
								}
							}
						}
					}
				} else {
					// If No, check if the student paid both ROS & SUS.Else flag it as prerequisite for student

					if ($session != $currentStudentSession) {
						$transactionSuspension = $this->checkStudentPaymentByCode($academic_record->student_id, $item['sus_code'], null, $item['sus']);
						if (!$transactionSuspension) {
							$pendingPayment = $this->checkStudentPendingPaymentByCode($academic_record->student_id, $item['sus_code'], null, $item['sus']);
							if ($pendingPayment) {
								$result[] = $this->transformDirectPaymentParam($pendingPayment['payment_id'], $pendingPayment['session'], $pendingPayment['transaction_ref']);
							} else {
								$result[] = $this->transformDirectPaymentParam($item['sus'], $session);
							}
						}
					}

					// excluding the student year of entry
					if ($academic_record->year_of_entry != getSessionValue('session_2020_special')) {
						if ($academic_record->year_of_entry != $session && $session == $currentStudentSession) {
							// check if student paid sch-fees in prev session
							if ($prevSession && !$this->checkStudentPaymentByCode($academic_record->student_id, FeeDescriptionCode::SCHOOL_FEE, $prevSession)) {
								$transactionReactivation = $this->checkStudentPaymentByCode($academic_record->student_id, $item['ros_code'], null, $item['ros']);
								if (!$transactionReactivation) {
									$pendingPayment = $this->checkStudentPendingPaymentByCode($academic_record->student_id, $item['ros_code'], null, $item['ros']);
									if ($pendingPayment) {
										$result[] = $this->transformDirectPaymentParam($pendingPayment['payment_id'], $pendingPayment['session'], $pendingPayment['transaction_ref']);
									} else {
										$result[] = $this->transformDirectPaymentParam($item['ros'], $session);
									}
								}
							}
						}
					}
				}
			}
		}

		return $result;
	}

	private function validateCompleteOutstanding($session, $payment, $academic_record, $returnPayment)
	{
		$payload = [];
		if ($payment->is_visible == 1 && $payment->description != PaymentFeeDescription::ACCEPTANCE_FEE) {
			$sessionSemesterStart = get_setting('session_semester_payment_start');
			if ($session >= $sessionSemesterStart) {
				// this checks for session 22 second semester successful transaction
				$transaction = $this->checkStudentPaymentByCode($academic_record->student_id, FeeDescriptionCode::SCHOOL_FEE, $session, PaymentFeeDescription::SCH_FEE_SECOND);
				if (!$transaction) {
					$pendingPayment = $this->checkStudentPendingPaymentByCode($academic_record->student_id, FeeDescriptionCode::OUTSTANDING_FEE, null, PaymentFeeDescription::OUTSTANDING_22);
					if ($pendingPayment) {
						if ($returnPayment) {
							$result = $payment->getSingleTransactionByRef($pendingPayment['transaction_ref'], false);
							$tempPayment = $this->payment->getPaymentById($result['real_payment_id']);
							return $this->convertTransaction($result, $tempPayment);
						}

						// ensuring the same prerequisite is not requiring itself
						if ($pendingPayment['payment_id'] != $payment->description) {
							$payload = $this->transformDirectPaymentParam($pendingPayment['payment_id'], $pendingPayment['session'], $pendingPayment['transaction_ref']);
						}

					} else {
						$param = [
							$session,
							$academic_record->programme_id,
							$academic_record->current_level,
							$academic_record->entry_mode,
							$academic_record->programme_id,
							$academic_record->current_level,
							$academic_record->entry_mode,
							PaymentFeeDescription::OUTSTANDING_22,
						];
						$newPayment = $this->loadMainFeesSkeleton($academic_record, $param);
						if ($newPayment) {
							$newPayment = $newPayment[0];
							if ($returnPayment) {
								return $this->convertMainOutstandingPayment($newPayment, $academic_record);
							}

							// ensuring the same prerequisite is not requiring itself
							if ($payment->id != $newPayment['id']) {
								$param = $this->transformDirectTransactionPaymentParam(PaymentFeeDescription::OUTSTANDING_22, $session, $newPayment, true);
								if ($param) {
									$payload = $param;
								}
							}
						}
					}
				}
			}
		}

		return $payload;
	}

	/**
	 * @throws Exception
	 */
	private function processSinglePaymentOutstanding($paymentDescription, $session, $payment, $academic_record, $returnPayment)
	{
		$isCompleted = $this->payment->getCompleteTransaction($academic_record->student_id, $paymentDescription, $session);
		if ($isCompleted) {
			// means there is a pending transaction
			if (!CommonTrait::isPaymentValid($isCompleted['payment_status'])) {
				if ($returnPayment) {
					$result = $this->payment->getSingleTransactionByRef($isCompleted['transaction_ref'], false);
					$tempPayment = $this->payment->getPaymentById($result['real_payment_id']);
					return $this->convertTransaction($result, $tempPayment);
				}

				if ($isCompleted['payment_id'] != $payment->description) {
					$param = $this->transformDirectPreqTransactionParam($isCompleted, $paymentDescription, $session);
					if ($param) {
						return $param;
					}
				}
			}
		}
		// check if there is a part payment and need to pay it equiv balance or the full payment
		$prevLevel = CommonTrait::inferPreviousLevel($academic_record->entry_mode, $academic_record->current_level);
		$param = [
			$session,
			$academic_record->programme_id,
			$prevLevel,
			$academic_record->entry_mode,
			$academic_record->programme_id,
			$prevLevel,
			$academic_record->entry_mode,
			$paymentDescription,
		];

		$newPayment = $this->loadMainFeesSkeleton($academic_record, $param);
		if ($newPayment) {
			$newPayment = $newPayment[0];
			if ($returnPayment) {
				return $this->convertMainOutstandingPayment($newPayment, $academic_record, $session, $prevLevel);
			}

			// ensuring the same prerequisite is not requiring itself
			if ($payment->id != $newPayment['id']) {
				$newPayment = new Payment($newPayment);
				$param = $this->processSinglePaymentPrerequisite($newPayment, $academic_record, $session, $prevLevel, true);
				if ($param) {
					return $param;
				}
			}
		}
	}

	/**
	 * @throws Exception
	 */
	private function validatePartOutstanding($session, $payment, $academic_record, $returnPayment = false): array
	{
		$results = [];
		$firstSemCompleted = $this->payment->getPaymentTransaction(PaymentFeeDescription::SCH_FEE_FIRST, $academic_record->student_id, $session);
		if ($firstSemCompleted) {
			$isCompleted = $this->processSinglePaymentOutstanding(PaymentFeeDescription::SCH_FEE_SECOND, $session, $payment, $academic_record, $returnPayment);
			if ($isCompleted) {
				$results[] = $isCompleted;
			}
		}
		return $results;
	}

	/**
	 * @param mixed $payment
	 * @param mixed $academic_record
	 * @param bool $returnPayment
	 * @return array
	 * @throws Exception
	 */
	private function validateStudentOutstanding(object $payment, object $academic_record, bool $returnPayment = false): array
	{
		$payload = [];
		$sessions = $academic_record->outstanding_session;
		if ($sessions) {
			$sessions = json_decode($sessions, true);
			foreach ($sessions as $session) {
				if ($session >= PaymentFeeDescription::OUTSTANDING_PART_SESSION) {
					$temp = $this->validatePartOutstanding($session, $payment, $academic_record, $returnPayment);
					if (!empty($temp)) {
						$payload = array_merge($payload, $temp);
					}
				} else {
					$temp = $this->validateCompleteOutstanding($session, $payment, $academic_record, $returnPayment);
					if (!empty($temp)) {
						$payload[] = $temp;
					}
				}
			}
		}

		return $payload;
	}

	/**
	 * @param mixed $academic_record
	 * @param mixed $payment
	 * @param mixed $currentSemester
	 * @return array|array<<missing>,<missing>>
	 */
	public function validateSpecialPaymentPrequisites($academic_record, $payment, $currentSemester): array
	{
		$prerequisites = [];
		// check for special prerequisite on student current session excluding acceptance fee
		$currentStudentSession = get_setting('active_session_student_portal');
		if ($academic_record->has_matric_number == 1) {
			if ($currentStudentSession == $academic_record->current_session && $currentSemester == 1) {
				if ((PaymentFeeDescription::SCH_FEE_FIRST == $payment->description || PaymentFeeDescription::TOPUP_FEE_22 == $payment->description) && $payment->description != PaymentFeeDescription::ACCEPTANCE_FEE) {
					$specialPrerequisites = $this->validateStudentSpecialPrerequisites($academic_record);
					$prerequisites = array_merge($prerequisites, $specialPrerequisites);
				}
			}
		}

		// checking for outstanding payment in a session
		if ($academic_record->topup_session == null && $academic_record->outstanding_session) {
			$specialPrerequisites = $this->validateStudentOutstanding($payment, $academic_record);
			$prerequisites = array_merge($prerequisites, $specialPrerequisites);
		}

		return $prerequisites;
	}

	public function prepOutStandingStudentPaymentParam($payment, $academic_record, $session = null, $transactionRef = null, $transactionLevel = null): array
	{
		$prerequisites = [];
		$isPaymentFull = true;
		$transactionSession = $session ? $session : $academic_record->current_session;
		if (!$transactionLevel) {
			$transactionLevel = @$payment->trans_level ?? $academic_record->current_level;
		}

		// check globally if acceptance fee had been paid irrespective of the paid session
		if (PaymentFeeDescription::ACCEPTANCE_FEE == $payment->description) {
			$transactionSession = null;
			$transactionLevel = null;
		}

		$transaction = $payment->getSuccessTransactionByDescription($this->id, $transactionSession, $transactionLevel, $transactionRef, false);
		if ($transaction && !isPaymentComplete($transaction->payment_option)) {
			$isPaymentFull = false;

			$paymentCode = inferPaymentCode($payment->description);
			$checkPaymentTransaction = $payment->getTransactionByOption($this->id, $paymentCode, $session, $transactionLevel);
			if ($checkPaymentTransaction) {
				$checkPaymentTransaction = $checkPaymentTransaction->toArray();
				return $this->prepStudentPartPaymentParam($payment, $checkPaymentTransaction);
			}

		} else if ($transactionRef) {
			$tempTransaction = $payment->getSingleTransactionByRef($transactionRef, false);
			if ($tempTransaction) {
				$isPaymentFull = !isPaymentComplete($tempTransaction['payment_option']) ? false : true;
				if (!$isPaymentFull) {
					return $this->prepStudentPartPaymentParam($payment, $tempTransaction);
				}
			}
		}

		$transactionPaymentSession = $transaction ? $transaction->session : $payment->session;
		$currentSemester = get_setting('active_semester');

		// load course pack fee
		$preselectedFee = null;
		$preselectedFeeAmount = 0;
		if ($payment->preselected_fee != 0) {
			$preselected = $this->transformPaymentAmount($payment->preselected_fee);
			$preselectedFeeAmount = $preselected['amount'];
			$preselectedFee = $preselected['desc'];
		}

		$paymentParam = $this->prepPaymentAmount($payment, $transactionPaymentSession);
		$description = $paymentParam['description'];
		$date_due = $paymentParam['date_due'];
		$penalty_fee = $paymentParam['penalty_fee'];
		$serviceCharge = $paymentParam['serviceCharge'];
		$originalAmount = $paymentParam['originalAmount'];
		$originalAmountService = $paymentParam['originalAmountService'];
		$totalAmount = $paymentParam['totalAmount'];

		$paymentType = ($payment->fee_category == 1) ? 'Main' : 'Sundry';
		if ($transaction && $isPaymentFull) {
			$transactionRef = $transaction->transaction_ref ?: null;
		}

		$enablePayment = 0;
		if ($paymentType == 'Main') {
			$enablePayment = (get_setting('disable_all_school_fees') == 0) ? 1 : 0;
		}

		if ($paymentType == 'Sundry') {
			$enablePayment = (get_setting('disable_all_sundry_fees') == 0) ? 1 : 0;
		}

		if ($enablePayment == 1) {
			$enablePayment = $payment->status;
		}

		$paymentTypeOption = paymentOptionsType($transaction ? $transaction->payment_option : $payment->options, true);
		$paymentCode = $transaction && $isPaymentFull ? $transaction->payment_description : $payment->description;
		$paymentDescription = $transaction && $isPaymentFull ? $transaction->payment_description : $description;
		$paymentTotal = ($transaction && $transaction->total_amount) ? $transaction->total_amount : $totalAmount;
		$paidTransaction = $transaction && $isPaymentFull ? true : false;
		$paidTransactionID = $transaction && $isPaymentFull ? $transaction->id : null;
		$paidPaymentTransactionID = $transaction && $isPaymentFull ? $transaction->payment_id : null;
		$paidTransaction_id = $transaction && $isPaymentFull ? $transaction->transaction_id : null;

		if ($transaction && !$isPaymentFull) {
			$serviceCharge = 505;
			$paymentTotal = ($totalAmount - $transaction->total_amount) + $serviceCharge;
			$paymentDescription .= " Balance";
			$paymentTypeOption = inferPaymentOption($payment->description);
			// using original payment_id to validate since it would be present when paying for the balance
			$transaction = $payment->getSuccessTransaction($this->id, $transactionSession);
			if ($transaction) {
				$transactionRef = $transaction->transaction_ref;
				$paidTransaction = true;
				$paidTransactionID = $transaction->id;
				$paidPaymentTransactionID = $transaction->payment_id;
				$paidTransaction_id = $transaction->transaction_id;
			}
		}

		return [
			'payment_id' => hashids_encrypt($payment->id),
			'transaction_ref' => $transactionRef,
			'payment_transaction' => 'trans_normal',
			'payment_code' => $paymentCode,
			'payment_code2' => $payment->description,
			'level' => $transaction ? $transaction->level : $academic_record->current_level,
			'session' => $transactionPaymentSession,
			'prerequisites' => $prerequisites,
			'preselected' => @$payment->preselected_fee ?? 0,
			'preselected_fee_readable' => $preselectedFee,
			'preselected_amount' => $preselectedFeeAmount,
			'fee_category' => $payment->fee_category,
			'fee_category_readable' => $paymentType,
			'description' => $paymentDescription,
			'amount' => $originalAmount,
			'penalty_fee' => ($penalty_fee != 0) ? $penalty_fee : 0,
			'service_charge' => $serviceCharge,
			'total_fee_service_charge' => $originalAmountService,
			'total' => $paymentTotal,
			'date_due' => $date_due,
			'paid' => $paidTransaction,
			'paid_id' => $paidTransactionID,
			'is_active' => $enablePayment,
			'is_visible' => $payment->is_visible,
			'payment_category' => strtolower($paymentType),
			'transaction_payment_id' => $paidPaymentTransactionID,
			'date_performed' => $transaction->date_performed ?? null,
			'date_completed' => $transaction->date_completed ?? null,
			'transactionID' => $paidTransaction_id,
			'transaction_rrr' => $transaction->rrr_code ?? null,
			'payment_type_option' => $paymentTypeOption,
		];
	}

	/**
	 * This would prep the payment details for student
	 * @param  [type] $payment         [description]
	 * @param  [type] $academic_record [description]
	 * @param  [type] $session         [description]
	 * @param  [type] $transactionRef  [description]
	 * @return array<string,mixed>@param mixed $payment
	 */
	public function prepStudentPaymentParam($payment, $academic_record, $session = null, $transactionRef = null, $transactionLevel = null): array
	{
		$prerequisites = [];
		$isPaymentFull = true;
		$transactionSession = $session ?: $academic_record->current_session;
		if (!$transactionLevel) {
			$transactionLevel = @$payment->trans_level ?? $academic_record->current_level;
		}

		// check globally if acceptance fee had been paid irrespective of the paid session
		if (PaymentFeeDescription::ACCEPTANCE_FEE == $payment->description) {
			$transactionSession = null;
			$transactionLevel = null;
		}

		$transaction = $payment->getSuccessTransactionByDescription($this->id, $transactionSession, $transactionLevel, $transactionRef, false);
		if ($transaction && !isPaymentComplete($transaction->payment_option)) {
			$isPaymentFull = false;
			$paymentCode = inferPaymentCode($payment->description);
			$checkPaymentTransaction = $payment->getTransactionByOption($this->id, $paymentCode, $session, $transactionLevel);
			if ($checkPaymentTransaction) {
				$checkPaymentTransaction = $checkPaymentTransaction->toArray();
				return $this->prepStudentPartPaymentParam($payment, $checkPaymentTransaction);
			}

		} else if ($transactionRef) {
			$tempTransaction = $payment->getSingleTransactionByRef($transactionRef, false);
			if ($tempTransaction) {
				$isPaymentFull = !isPaymentComplete($tempTransaction['payment_option']) ? false : true;
				if (!$isPaymentFull) {
					return $this->prepStudentPartPaymentParam($payment, $tempTransaction);
				}
			}
		}

		$transactionPaymentSession = $transaction ? $transaction->session : $payment->session;
		$currentSemester = get_setting('active_semester');
		// now check for prerequisite
		if ($payment->prerequisite_fee != '') {
			$prerequisitesID = json_decode($payment->prerequisite_fee, true);
			if (!empty($prerequisitesID)) {
				$tempPrerequsites = $this->transformPaymentPrerequisiteParam($academic_record, $prerequisitesID, $payment->session, $transactionLevel);
				$prerequisites = array_merge($prerequisites, $tempPrerequsites);
			}
		}

		// NOTE: THIS IS MEANT TO BE TEMPORAL PREREQUISITES
		$tempPrerequisites = $this->validateSpecialPaymentPrequisites($academic_record, $payment, $currentSemester);
		if ($tempPrerequisites) {
			$prerequisites = array_merge($prerequisites, $tempPrerequisites);
		}

		// load course pack fee
		$preselectedFee = null;
		$preselectedFeeAmount = 0;
		if ($payment->preselected_fee != 0) {
			$preselected = $this->transformPaymentAmount($payment->preselected_fee);
			$preselectedFeeAmount = $preselected['amount'];
			$preselectedFee = $preselected['desc'];
		}

		$paymentParam = $this->prepPaymentAmount($payment, $transactionPaymentSession);
		$description = $paymentParam['description'];
		$date_due = $paymentParam['date_due'];
		$penalty_fee = $paymentParam['penalty_fee'];
		$serviceCharge = $paymentParam['serviceCharge'];
		$originalAmount = $paymentParam['originalAmount'];
		$originalAmountService = $paymentParam['originalAmountService'];
		$totalAmount = $paymentParam['totalAmount'];

		$paymentType = ($payment->fee_category == 1) ? 'Main' : 'Sundry';
		if ($transaction && $isPaymentFull) {
			$transactionRef = $transaction->transaction_ref ?: null;
		}

		$enablePayment = 0;
		if ($paymentType == 'Main') {
			$enablePayment = (get_setting('disable_all_school_fees') == 0) ? 1 : 0;
		}

		if ($paymentType == 'Sundry') {
			$enablePayment = (get_setting('disable_all_sundry_fees') == 0) ? 1 : 0;
		}

		if ($enablePayment == 1) {
			$enablePayment = $payment->status;
		}

		$paymentTypeOption = paymentOptionsType($transaction ? $transaction->payment_option : $payment->options, true);
		$paymentCode = $transaction && $isPaymentFull ? $transaction->payment_description : $payment->description;
		$paymentDescription = $transaction && $isPaymentFull ? $transaction->payment_description : $description;
		$paymentTotal = ($transaction && $transaction->total_amount) ? $transaction->total_amount : $totalAmount;
		$paidTransaction = $transaction && $isPaymentFull;
		$paidTransactionID = $transaction && $isPaymentFull ? $transaction->id : null;
		$paidPaymentTransactionID = $transaction && $isPaymentFull ? $transaction->payment_id : null;
		$paidTransaction_id = $transaction && $isPaymentFull ? $transaction->transaction_id : null;

		if ($transaction && !$isPaymentFull) {
			$serviceCharge = 505;
			$paymentTotal = ($totalAmount - $transaction->total_amount) + $serviceCharge;
			$paymentDescription .= " Balance";
			$paymentTypeOption = inferPaymentOption($payment->description);
			// using original payment_id to validate since it would be present when paying for the balance
			$transaction = $payment->getSuccessTransaction($this->id, $transactionSession);
			if ($transaction) {
				$transactionRef = $transaction->transaction_ref;
				$paidTransaction = true;
				$paidTransactionID = $transaction->id;
				$paidPaymentTransactionID = $transaction->payment_id;
				$paidTransaction_id = $transaction->transaction_id;
			}

			// if payment is 1st sem balance, prerequisites is not needed
			if ($payment->description == PaymentFeeDescription::SCH_FEE_FIRST) {
				$prerequisites = [];
			}
		}

		// if payment is outstanding, prerequisites is not needed
		if ($payment->description == PaymentFeeDescription::OUTSTANDING_22) {
			$prerequisites = [];
		}

		return [
			'payment_id' => hashids_encrypt($payment->id),
			'transaction_ref' => $transactionRef,
			'payment_transaction' => 'trans_normal',
			'payment_code' => $paymentCode,
			'payment_code2' => $payment->description,
			'level' => $transaction ? $transaction->level : $transactionLevel,
			'session' => $transactionPaymentSession,
			'prerequisites' => $prerequisites,
			'preselected' => @$payment->preselected_fee ?? 0,
			'preselected_fee_readable' => $preselectedFee,
			'preselected_amount' => $preselectedFeeAmount,
			'fee_category' => $payment->fee_category,
			'fee_category_readable' => $paymentType,
			'description' => $paymentDescription,
			'amount' => $originalAmount,
			'penalty_fee' => ($penalty_fee != 0) ? $penalty_fee : 0,
			'service_charge' => $serviceCharge,
			'total_fee_service_charge' => $originalAmountService,
			'total' => $paymentTotal,
			'date_due' => $date_due,
			'paid' => $paidTransaction,
			'paid_id' => $paidTransactionID,
			'is_active' => $enablePayment,
			'is_visible' => $payment->is_visible,
			'payment_category' => strtolower($paymentType),
			'transaction_payment_id' => $paidPaymentTransactionID,
			'date_performed' => $transaction->date_performed ?? null,
			'date_completed' => $transaction->date_completed ?? null,
			'transactionID' => $paidTransaction_id,
			'transaction_rrr' => $transaction->rrr_code ?? null,
			'payment_type_option' => $paymentTypeOption,
		];
	}

	public function prepStudentPartPaymentParam($payment, $transaction): array
	{
		$paidTransaction = false;
		$paymentTypeOption = paymentOptionsType($transaction ? $transaction['payment_option'] : $payment->options, true);
		if ($transaction && CommonTrait::isPaymentValid($transaction['payment_status'])) {
			$paidTransaction = true;
		}

		$preselectedFee = null;
		$preselectedFeeAmount = 0;

		$paymentType = ($payment->fee_category == 1) ? 'Main' : 'Sundry';
		$enablePayment = 0;
		if ($paymentType == 'Main') {
			$enablePayment = (get_setting('disable_all_school_fees') == 0) ? 1 : 0;
		}

		if ($paymentType == 'Sundry') {
			$enablePayment = (get_setting('disable_all_sundry_fees') == 0) ? 1 : 0;
		}

		if ($enablePayment == 1) {
			$enablePayment = $payment->status;
		}

		return [
			'payment_id' => hashids_encrypt($payment->id),
			'transaction_ref' => $transaction['transaction_ref'],
			'payment_transaction' => "trans_normal",
			'payment_code' => $transaction['payment_description'],
			'payment_code2' => $transaction['payment_id'],
			'level' => $transaction['level'],
			'session' => $transaction['session'],
			'prerequisites' => [],
			'preselected' => 0,
			'preselected_fee_readable' => $preselectedFee,
			'preselected_amount' => $preselectedFeeAmount,
			'fee_category' => $payment->fee_category,
			'fee_category_readable' => $paymentType,
			'description' => $transaction['payment_description'],
			'amount' => $transaction['total_amount'],
			'penalty_fee' => 0,
			'service_charge' => $transaction['service_charge'],
			'total_fee_service_charge' => $transaction['total_amount'],
			'total' => $transaction['total_amount'],
			'date_due' => null,
			'paid' => $paidTransaction,
			'paid_id' => $paidTransaction ? $transaction['id'] : null,
			'is_active' => $enablePayment,
			'is_visible' => $payment->is_visible,
			'payment_category' => strtolower($paymentType),
			'transaction_payment_id' => $paidTransaction ? $transaction['payment_id'] : null,
			'date_performed' => $transaction['date_performed'] ?? null,
			'date_completed' => $transaction['date_completed'] ?? null,
			'transactionID' => $transaction['transaction_id'] ?? null,
			'transaction_rrr' => $transaction['rrr_code'] ?? null,
			'payment_type_option' => $paymentTypeOption,
		];
	}

	/**
	 * This would prep part payment details
	 * @param  [type] $payment         [description]
	 * @param  [type] $academic_record [description]
	 * @return array<string,mixed>@param mixed $payment
	 */
	public function prepStudentPaymentDirectParam($payment, $academic_record, $transactionLevel = null): array
	{
		$prerequisites = [];
		$isPaymentFull = true;
		$transactionSession = $payment ? $payment->session : $academic_record->current_session;
		$transactionLevel = $transactionLevel ?: (@$payment->trans_level ?: $academic_record->current_level);
		$transactionRef = null;

		$transaction = $payment->getSuccessTransactionByDescription($this->id, $transactionSession, $transactionLevel, null, false);
		if ($transaction && !isPaymentComplete($transaction->payment_option)) {
			$isPaymentFull = false;
		}

		$transactionPaymentSession = $transaction ? $transaction->session : $transactionSession;
		// load course pack fee
		$preselectedFee = null;
		$preselectedFeeAmount = 0;
		if ($payment->preselected_fee != 0) {
			$preselected = $this->transformPaymentAmount($payment->preselected_fee);
			$preselectedFeeAmount = $preselected['amount'];
			$preselectedFee = $preselected['desc'];
		}

		$paymentParam = $this->prepPaymentAmount($payment, $transactionPaymentSession);
		$description = $paymentParam['description'];
		$date_due = $paymentParam['date_due'];
		$penalty_fee = $paymentParam['penalty_fee'];
		$serviceCharge = $paymentParam['serviceCharge'];
		$originalAmount = $paymentParam['originalAmount'];
		$originalAmountService = $paymentParam['originalAmountService'];
		$totalAmount = $paymentParam['totalAmount'];

		$paymentType = ($payment->fee_category == 1) ? 'Main' : 'Sundry';
		if ($transaction && $isPaymentFull) {
			$transactionRef = $transaction->transaction_ref ?: null;
		}

		$enablePayment = 0;
		if ($paymentType == 'Main') {
			$enablePayment = (get_setting('disable_all_school_fees') == 0) ? 1 : 0;
		}

		if ($paymentType == 'Sundry') {
			$enablePayment = (get_setting('disable_all_sundry_fees') == 0) ? 1 : 0;
		}

		if ($enablePayment == 1) {
			$enablePayment = $payment->status;
		}

		$paymentTypeOption = paymentOptionsType($transaction ? $transaction->payment_option : $payment->options, true);
		$paymentCode = $transaction && $isPaymentFull ? $transaction->payment_description : $payment->description;
		$paymentDescription = $transaction && $isPaymentFull ? $transaction->payment_description : $description;
		$paymentTotal = ($transaction && $transaction->total_amount) ? $transaction->total_amount : $totalAmount;
		if ($transaction && !$isPaymentFull) {
			$serviceCharge = 505;
			$paymentTotal = ($totalAmount - $transaction->total_amount) + $serviceCharge;
			$paymentDescription .= " Balance";
			$paymentTypeOption = inferPaymentOption($payment->description);
		}

		return [
			'payment_id' => hashids_encrypt($payment->id),
			'transaction_ref' => $transactionRef,
			'payment_transaction' => 'trans_normal',
			'payment_code' => $paymentCode,
			'payment_code2' => $payment->description,
			'level' => $transaction ? $transaction->level : $transactionLevel,
			'session' => $transactionPaymentSession,
			'prerequisites' => $prerequisites,
			'preselected' => @$payment->preselected_fee ?? 0,
			'preselected_fee_readable' => $preselectedFee,
			'preselected_amount' => $preselectedFeeAmount,
			'fee_category' => $payment->fee_category,
			'fee_category_readable' => $paymentType,
			'description' => $paymentDescription,
			'amount' => $originalAmount,
			'penalty_fee' => ($penalty_fee != 0) ? $penalty_fee : 0,
			'service_charge' => $serviceCharge, // 0,
			'total_fee_service_charge' => $originalAmountService,
			'total' => $paymentTotal,
			'date_due' => $date_due,
			'paid' => $transaction && $isPaymentFull,
			'paid_id' => $transaction && $isPaymentFull ? $transaction->id : null,
			'is_active' => $enablePayment,
			'is_visible' => $payment->is_visible,
			'payment_category' => strtolower($paymentType),
			'transaction_payment_id' => $transaction && $isPaymentFull ? $transaction->payment_id : null,
			'date_performed' => $transaction->date_performed ?? null,
			'date_completed' => $transaction->date_completed ?? null,
			'transactionID' => $transaction && $isPaymentFull ? $transaction->transaction_id : null,
			'transaction_rrr' => $transaction->rrr_code ?? null,
			'payment_type_option' => $paymentTypeOption,
		];
	}

	/**
	 * This would prep the payment details for student
	 * @param  [type] $payment         [description]
	 * @param  [type] $academic_record [description]
	 * @param  [type] $session         [description]
	 * @param mixed $useTransactionRRR
	 * @param mixed $transactionRef
	 * @return array<string,mixed>@param mixed $payment
	 */
	public function prepExistingStudentPaymentParam($payment, $academic_record, $session, $useTransactionRRR, $transactionRef = null): array
	{
		$prerequisites = [];
		$transactionSession = $session ? $session : $academic_record->current_session;
		$transaction = $payment->getSuccessTransaction($this->id, $transactionSession, $transactionRef);
		$useTransactionRRR = $this->transaction->getWhere(['rrr_code' => $useTransactionRRR], $c, 0, 1, false);
		$useTransactionRRR = $useTransactionRRR[0];
		$transactionPaymentSession = $useTransactionRRR ? $useTransactionRRR->session : $payment->session;

		// load course pack fee
		$preselectedFee = null;
		$preselectedFeeAmount = 0;

		$feeDesc = $this->fee_description->getWhere(['id' => $payment->description], $c, 0, null, false);
		$description = $feeDesc ? $feeDesc[0]->description : null;
		$sess = $this->sessions->getWhere(['id' => $transactionPaymentSession], $c, 0, null, false);
		$session_name = $sess ? $sess[0]->date : '';

		$paymentType = ($payment->fee_category == 1) ? 'Main' : 'Sundry';
		if ($transaction) {
			$transactionRef = $transaction->transaction_ref ? $transaction->transaction_ref : null;
		}

		$enablePayment = 0;
		if ($paymentType == 'Main') {
			$enablePayment = (get_setting('disable_all_school_fees') == 0) ? 1 : 0;
		}

		if ($paymentType == 'Sundry') {
			$enablePayment = (get_setting('disable_all_sundry_fees') == 0) ? 1 : 0;
		}

		if ($enablePayment == 1) {
			$enablePayment = $payment->status;
		}

		$paymentTypeOption = paymentOptionsType($transaction ? $transaction->payment_option : $payment->options, true);
		return [
			'payment_id' => hashids_encrypt($payment->id),
			'transaction_ref' => $useTransactionRRR->transaction_ref ?? $transactionRef,
			'payment_transaction' => $useTransactionRRR ? 'trans_custom' : "trans_normal",
			'payment_code' => $useTransactionRRR ? $useTransactionRRR->payment_description : $payment->description,
			'payment_code2' => $payment->description,
			'level' => $useTransactionRRR ? $useTransactionRRR->level : $academic_record->current_level,
			'session' => $transactionPaymentSession,
			'prerequisites' => $prerequisites,
			'preselected' => 0,
			'preselected_fee_readable' => $preselectedFee,
			'preselected_amount' => $preselectedFeeAmount,
			'fee_category' => $payment->fee_category,
			'fee_category_readable' => $paymentType,
			'description' => $useTransactionRRR ? $useTransactionRRR->payment_description : $description . " " . $session_name,
			'amount' => $useTransactionRRR->total_amount,
			'penalty_fee' => 0,
			'service_charge' => $useTransactionRRR->service_charge,
			'total_fee_service_charge' => $useTransactionRRR->total_amount,
			'total' => $useTransactionRRR->total_amount,
			'date_due' => null,
			'paid' => $transaction ? true : false,
			'paid_id' => $transaction ? $transaction->id : null,
			'is_active' => $enablePayment,
			'is_visible' => $payment->is_visible,
			'payment_category' => strtolower($paymentType),
			'transaction_payment_id' => $transaction ? $transaction->payment_id : null,
			'date_performed' => $transaction->date_performed ?? null,
			'date_completed' => $transaction->date_completed ?? null,
			'transactionID' => $transaction->transaction_id ?? null,
			'transaction_rrr' => $transaction->rrr_code ?? null,
			'payment_type_option' => $paymentTypeOption,
		];
	}

	/**
	 * This transform payment to appropriate format
	 * @param $payment
	 * @param mixed $academic_record
	 * @param mixed $session
	 * @param mixed $paymentType
	 * @param mixed $transactionRef
	 * @param mixed $useTransactionRRR
	 * @return array<string,null>@param mixed $payment
	 * @throws Exception
	 */
	private function convertPayment($payment, $academic_record, $session = null, $paymentType = 'main', $transactionRef = null, $useTransactionRRR = false): array
	{
		$payment = new Payment($payment);
		$payment_schedule = [];
		if ($useTransactionRRR) {
			// useful if the details already exists in the transaction table
			$payment_schedule = $this->prepExistingStudentPaymentParam($payment, $academic_record, $session, $useTransactionRRR, $transactionRef);
		} else {
			$payment_schedule = $this->prepStudentPaymentParam($payment, $academic_record, $session, $transactionRef);
		}

		$this->load->model('remita');
		$remitaResponse = null;
		if ($payment_schedule['paid']) {
			$transactionRef = $payment_schedule['transaction_ref'];
			$temp = $this->remita->getRemitaData($transactionRef, null, null, $transactionRef, 'student');
			$extraData = $temp['extraData'];
			$response = $this->remita->remitaTransactionDetails($extraData['url'], $temp['header']);
			$remitaResponse = (isset($response['RRR'])) ? $response : null;
			if ($remitaResponse) {
				$remitaResponse['date_performed'] = date_format(date_create($payment_schedule['date_performed']), "M. d, Y");
				$remitaResponse['date_completed'] = date_format(date_create($payment_schedule['date_completed']), "M. d, Y");
			}
		}
		unset($payment_schedule['date_completed'], $payment_schedule['date_performed']);

		$payment_schedule['split_payment'] = $remitaResponse;
		return $payment_schedule;
	}

	/**
	 * @throws Exception
	 */
	private function convertMainPayment(array $payment, object $academic_record, $session = null, $level = null): array
	{
		$payment = new Payment($payment);
		$payment_schedule = $this->prepStudentPaymentParam($payment, $academic_record, $session, null, $level);

		$this->load->model('remita');
		$remitaResponse = null;
		if ($payment_schedule['paid']) {
			$transactionRef = $payment_schedule['transaction_ref'];
			$temp = $this->remita->getRemitaData($transactionRef, null, null, $transactionRef, 'student');
			$extraData = $temp['extraData'];
			$response = $this->remita->remitaTransactionDetails($extraData['url'], $temp['header']);
			$remitaResponse = (isset($response['RRR'])) ? $response : null;
			if ($remitaResponse) {
				$remitaResponse['date_performed'] = formatPaymentDate($payment_schedule['date_performed']);
				$remitaResponse['date_completed'] = formatPaymentDate($payment_schedule['date_completed']);
			}
		}
		unset($payment_schedule['date_completed'], $payment_schedule['date_performed']);

		$payment_schedule['split_payment'] = $remitaResponse;
		return $payment_schedule;
	}

	/**
	 * @throws Exception
	 */
	private function convertMainOutstandingPayment(array $payment, object $academic_record, $session = null, $level = null): array
	{
		$payment = new Payment($payment);
		$payment_schedule = $this->prepOutStandingStudentPaymentParam($payment, $academic_record, $session, null, $level);

		$this->load->model('remita');
		$remitaResponse = null;
		if ($payment_schedule['paid']) {
			$transactionRef = $payment_schedule['transaction_ref'];
			$temp = $this->remita->getRemitaData($transactionRef, null, null, $transactionRef, 'student');
			$extraData = $temp['extraData'];
			$response = $this->remita->remitaTransactionDetails($extraData['url'], $temp['header']);
			$remitaResponse = (isset($response['RRR'])) ? $response : null;
			if ($remitaResponse) {
				$remitaResponse['date_performed'] = formatPaymentDate($payment_schedule['date_performed']);
				$remitaResponse['date_completed'] = formatPaymentDate($payment_schedule['date_completed']);
			}
		}
		unset($payment_schedule['date_completed'], $payment_schedule['date_performed']);

		$payment_schedule['split_payment'] = $remitaResponse;
		return $payment_schedule;
	}

	/**
	 * This prep the payment details using transaction info
	 * @param array $transaction [description]
	 * @param object|null $payment [description]
	 * @return array<string,mixed>
	 * @throws Exception
	 */
	public function convertTransaction(array $transaction, ?object $payment = null): array
	{
		$transaction = new Transaction($transaction);
		$paymentType = 'Main';
		if ($payment) {
			$paymentType = ($payment->fee_category == 1) ? 'Main' : 'Sundry';
		}

		$transactionStatus = (CommonTrait::isPaymentValid($transaction->payment_status)) ? true : false;
		$paymentTypeOption = paymentOptionsType($transaction ? $transaction->payment_option : $payment->options, true);
		$isVisible = $payment ? $payment->is_visible : 1;
		$isActive = $payment ? $payment->status : 0;
		return [
			'payment_id' => hashids_encrypt($transaction->real_payment_id),
			'transaction_ref' => $transaction->transaction_ref ?? null,
			'payment_transaction' => "trans_normal", // no way to diff yet
			'payment_code' => $transaction->payment_description,
			'payment_code2' => $transaction->payment_id,
			'level' => $transaction->level ?? null,
			'session' => $transaction->session,
			'prerequisites' => [],
			'preselected' => 0,
			'preselected_fee_readable' => null,
			'preselected_amount' => null,
			'fee_category' => 1,
			'fee_category_readable' => $paymentType,
			'description' => $transaction->payment_description,
			'amount' => $transaction->total_amount,
			'penalty_fee' => 0,
			'service_charge' => $transaction->service_charge,
			'total_fee_service_charge' => $transaction->total_amount,
			'total' => $transaction->total_amount,
			'date_due' => null,
			'paid' => $transactionStatus,
			'paid_id' => $transactionStatus ? $transaction->id : null,
			'is_active' => $isActive,
			'is_visible' => $isVisible,
			'payment_category' => strtolower($paymentType),
			'transaction_payment_id' => $transaction ? $transaction->payment_id : null,
			'date_performed' => $transaction->date_performed ?? null,
			'date_completed' => $transaction->date_completed ?? null,
			'transactionID' => $transaction->transaction_id ?? null,
			'transaction_rrr' => $transaction->rrr_code ?? null,
			'payment_type_option' => $paymentTypeOption,
		];
	}

	/**
	 *
	 * This load the student main fees
	 * @param mixed $academic_record
	 * @param mixed $extra
	 * @param mixed $param
	 * @return array
	 * @throws Exception
	 */
	public function loadMainFees($academic_record, $extra = false, $param = true, &$message = '')
	{
		$query = "SELECT payment.id,payment.amount,payment.description,fee_category,payment.session,payment.level,payment.prerequisite_fee,
       date_due,payment.penalty_fee,payment.service_charge,payment.status,is_visible,payment.date_created,payment.preselected_fee,payment.discount_amount,
       payment.subaccount_amount,payment.options from payment where fee_category=1 and session=? and is_visible=1 and
    	( ( JSON_SEARCH(programme,'one',?) is not null and JSON_SEARCH(level,'one',?) is not null and  JSON_SEARCH(entry_mode,'one',?) is not null )
    	or ( JSON_SEARCH(programme,'one',?) is not null and JSON_SEARCH(level_to_include,'one',?) is not null and
    	JSON_SEARCH(entry_mode_to_include,'one',?) is not null) )";

		if ($extra) {
			$query .= ($param) ? " and payment.description = ?" : ""; // to load only the description(sch fees)
			$param = $extra;
		} else {
			$param = [
				trim($academic_record->current_session),
				trim($academic_record->programme_id),
				trim($academic_record->current_level),
				$this->db->escapeString(trim($academic_record->entry_mode)),
				trim($academic_record->programme_id),
				trim($academic_record->current_level),
				$this->db->escapeString(trim($academic_record->entry_mode)),
			];
		}
		$payments = $this->query($query, $param);
		if (!$payments) {
			return null;
		}

		$toReturn = [];
		$acceptanceSession = get_setting('session_semester_payment_start');
		foreach ($payments as $payment) {
			$showPayment = true;

			// Now allowing payment to show but handle from FE
			// if ($this->isFreshStudent($academic_record) && $payment['description'] != PaymentFeeDescription::ACCEPTANCE_FEE &&
			// 	!$this->is_verified) {
			// 	$showPayment = true;
			// }

			// acceptance payment should start from student having year of entry >= 22.
			if ($payment['description'] == PaymentFeeDescription::ACCEPTANCE_FEE && $acceptanceSession > $academic_record->year_of_entry) {
				$showPayment = false;
			}

			if ($showPayment) {
				$toReturn[] = $this->convertMainPayment($payment, $academic_record);
			}
		}

		return $toReturn;
	}

	/**
	 * @param mixed $academic_record
	 * @param mixed $description
	 * @return array
	 * @throws Exception
	 */
	public function loadTopupFees($academic_record, $description = 68): array
	{
		$query = "SELECT id from payment where description = '$description' order by id desc limit 1";
		$result = $this->query($query);
		$paymentID = $result[0]['id'];
		$studentID = $academic_record->student_id;

		// this avoid loading topup payment if there is a pending/success already
		$query = "SELECT * from transaction where real_payment_id = '$paymentID' and student_id = ? and (payment_status not in ('00','01') or payment_status in ('00','01')) order by id desc";
		$result = $this->query($query, [$studentID]);
		if ($result) {
			return [];
		}

		$result = [];
		$query = "SELECT * from payment where fee_category=2 and description = '$description' and is_visible=1 order by id desc limit 1";
		$temp = $this->query($query);
		if (!$temp) {
			return [];
		}

		foreach ($temp as $item) {
			$result[] = $this->convertPayment($item, $this->academic_record);
		}
		return $result;
	}

	/**
	 * @param mixed $academic_record
	 * @param mixed $extra
	 * @param mixed $param
	 * @return bool|<missing>
	 */
	public function loadMainFeesSkeleton($academic_record, $extra = false, $param = true)
	{
		$query = "SELECT payment.id,payment.amount,payment.description,fee_category,payment.session,payment.level,payment.prerequisite_fee,date_due,
       payment.penalty_fee,payment.service_charge,payment.status,is_visible,payment.date_created,payment.preselected_fee,
       payment.discount_amount,payment.subaccount_amount,payment.options from payment where fee_category=1 and session=? and
       is_visible=1 and ( ( JSON_SEARCH(programme,'one',?) is not null and JSON_SEARCH(level,'one',?) is not null and
       JSON_SEARCH(entry_mode,'one',?) is not null ) or ( JSON_SEARCH(programme,'one',?) is not null and
       JSON_SEARCH(level_to_include,'one',?) is not null and  JSON_SEARCH(entry_mode_to_include,'one',?) is not null) )";

		if ($extra) {
			$query .= ($param) ? " and payment.description = ?" : ""; // to load only the description(sch fees)
			$param = $extra;
		} else {
			$param = [
				$academic_record->current_session,
				$academic_record->programme_id,
				$academic_record->current_level,
				$academic_record->entry_mode,
				$academic_record->programme_id,
				$academic_record->current_level,
				$academic_record->entry_mode,
			];
		}

		$payments = $this->query($query, $param);
		if (!$payments) {
			return false;
		}

		return $payments;
	}

	/**
	 * This get all previous successful payment transaction that has been paid by the student
	 * @param  [type] $academic_record [description]
	 * @return bool|array@param mixed $academic_record
	 * @throws Exception
	 */
	private function loadStudentPreviousPayment($academic_record)
	{
		$query = "SELECT transaction.*,transaction.level as trans_level,transaction.session as payment_session from transaction
		left join fee_description on transaction.payment_id = fee_description.id where transaction.session <> ? and
		transaction.payment_status in ('00', '01') and transaction.student_id = ?";
		$param = [
			$academic_record->current_session,
			$this->id,
		];
		$payments = $this->query($query, $param);
		$toReturn = [];
		if (!$payments) {
			return false;
		}
		foreach ($payments as $payment) {
			$transaction = $this->convertTransaction($payment);
			$transaction['split_payment'] = null;
			$toReturn[] = $transaction;
		}
		return $toReturn;
	}

	/**
	 * @param $session
	 * @param mixed $paymentID
	 * @return bool|Transaction@param mixed $session
	 * @throws Exception
	 * @deprecated - This is not in use
	 */
	private function getStudentPendingPaymentInsession($session, $paymentID)
	{
		$query = "SELECT payment.id,payment.amount,payment.description,fee_category,payment.session,payment.level,payment.
		prerequisite_fee,date_due,payment.penalty_fee,payment.service_charge,payment.status,is_visible,payment.date_created,
		payment.preselected_fee,payment.discount_amount,payment.subaccount_amount,transaction_ref,transaction.session,
		transaction.rrr_code from transaction join payment on transaction.real_payment_id = payment.id where fee_category = 1
		and payment_status not in ('00','01') and transaction.session = ? and transaction.student_id = ? and
		transaction.payment_id = ? order by transaction.id desc";
		$param = [
			$session,
			$this->id,
			$paymentID,
		];
		$result = $this->query($query, $param);
		if (!$result) {
			return false;
		}
		loadClass($this->load, 'transaction');
		return new Transaction($result[0]);
	}

	/**
	 * This load student new payment for those that did change of programme
	 * @param  [type] $academic_record [description]
	 * @return bool|array@param mixed $academic_record
	 * @throws Exception
	 */
	private function loadStudentNewProgrammePayment($academic_record)
	{
		$query = "SELECT payment.id,payment.amount,payment.description,fee_category,payment.session,payment.level,payment.prerequisite_fee,date_due,payment.penalty_fee,payment.service_charge,payment.status,is_visible,payment.date_created,payment.preselected_fee,payment.discount_amount,payment.subaccount_amount,transaction_ref,transaction.session,transaction.rrr_code,options from transaction left join payment on transaction.real_payment_id = payment.id join student_change_of_programme sc on sc.transaction_id = transaction.id where fee_category = 1 and transaction.session = ? and transaction.student_id = ? and payment_status not in ('00','01') ";

		$param = [
			$academic_record->current_session,
			$this->id,
		];
		$payments = $this->query($query, $param);
		$toReturn = [];
		if (!$payments) {
			return false;
		}
		foreach ($payments as $payment) {
			$toReturn[] = $this->convertPayment($payment, $academic_record, $payment['session'], 'main', $payment['transaction_ref'], $payment['rrr_code']);
		}
		return $toReturn;
	}

	/**
	 * This load student previous programme based on the session the programme was changed
	 * @param $student
	 * @param mixed $prev
	 * @param mixed $session
	 * @return bool|<missing>@param mixed $student
	 */
	private function loadPreviousStudentProgramme($student, $prev, $session)
	{
		$query = "SELECT * from student_change_of_programme where student_id = ? and old_programme_id = ? and session = ? order by date_created desc limit 1";
		$result = $this->query($query, [$student, $prev, $session]);
		if (!$result) {
			return false;
		}
		return $result[0];
	}

	/**
	 * @param mixed $academic_record
	 * @return bool
	 */
	public function isFreshStudent($academic_record): bool
	{
		if (($academic_record->entry_mode == CommonSlug::DIRECT_ENTRY && $academic_record->current_level == 2) ||
			($academic_record->entry_mode == CommonSlug::O_LEVEL && $academic_record->current_level == 1) ||
			($academic_record->entry_mode == CommonSlug::O_LEVEL_PUTME && $academic_record->current_level == 1) ||
			($academic_record->entry_mode == CommonSlug::FAST_TRACK && $academic_record->current_level == 2)) {
			return true;
		}
		return false;
	}

	public function getOutstandingFees()
	{
		loadClass($this->load, 'fee_description');
		loadClass($this->load, 'payment');

		$academic_record = $this->academic_record;
		$paymentOutstanding = null;

		$param = [
			$academic_record->current_session,
			$academic_record->programme_id,
			$academic_record->current_level,
			$academic_record->entry_mode,
			$academic_record->programme_id,
			$academic_record->current_level,
			$academic_record->entry_mode,
			PaymentFeeDescription::SCH_FEE_FIRST,
		];
		$result = $this->loadMainFeesSkeleton($academic_record, $param);
		if (!empty($result)) {
			$paymentOutstanding = array_filter($result, function ($item) {
				return $item['description'] == PaymentFeeDescription::SCH_FEE_FIRST;
			}, ARRAY_FILTER_USE_BOTH);
			if ($paymentOutstanding) {
				$paymentOutstanding = [...$paymentOutstanding][0];
				$paymentOutstanding['payment_id'] = hashids_encrypt($paymentOutstanding['id']);
				$result = $this->loadAllOutstandingFee($paymentOutstanding, $academic_record);
				if ($result) {
					return $result;
				}
			}
		}

		return null;

	}

	private function loadAllOutstandingFee($payment, $academic_record): array {
		$outstandingResult = [];
		if ($academic_record->topup_session == null && $academic_record->outstanding_session) {
			$paymentID = hashids_decrypt($payment['payment_id']);
			$tempPayment = $this->payment->getWhere(['id' => $paymentID], $c, 0, 1, false);
			if ($tempPayment) {
				$tempPayment = $tempPayment[0];
				$result = $this->validateStudentOutstanding($tempPayment, $academic_record, true);
				if ($result) {
					$outstandingResult = $result;
				}
			}
		}

		if ($academic_record->topup_session == null && $academic_record->current_session) {
			$paymentID = hashids_decrypt($payment['payment_id']);
			$tempPayment = $this->payment->getWhere(['id' => $paymentID], $c, 0, 1, false);
			if ($tempPayment) {
				$tempPayment = $tempPayment[0];
				$result = $this->validateStudentCurrentOutstanding($tempPayment, $academic_record);
				if ($result) {
					$outstandingResult[] = $result;
				}
			}
		}

		return $outstandingResult;
	}

	private function validateStudentCurrentOutstanding($payment, $academic_record){
		$session = get_setting('active_session_student_portal');
		$paymentDescription = get_setting('active_semester') == '1' ? PaymentFeeDescription::SCH_FEE_FIRST : PaymentFeeDescription::SCH_FEE_SECOND;
		$isCompleted = $this->payment->getPartialTransactionOption($academic_record->student_id, $paymentDescription, $session);
		if ($isCompleted) {
			if(!CommonTrait::isPaymentValid($isCompleted['payment_status'])){
				$tempPayment = $this->payment->getPaymentById($isCompleted['real_payment_id']);
				return $this->convertTransaction($isCompleted, $tempPayment);
			}	
		}
		$param = [
			$session,
			$academic_record->programme_id,
			$academic_record->current_level,
			$academic_record->entry_mode,
			$academic_record->programme_id,
			$academic_record->current_level,
			$academic_record->entry_mode,
			$paymentDescription,
		];
		$newPayment = $this->loadMainFeesSkeleton($academic_record, $param);
		if($newPayment){
			$newPayment = $newPayment[0];
			return $this->convertMainOutstandingPayment($newPayment, $academic_record, $session, $academic_record->current_level);
		}
	}

	/**
	 * This get the main/compulsory fees to be paid by the student
	 * @param string|null $message [description]
	 * @return bool|array|array<<missing>,<missing>>
	 * @throws Exception
	 */
	public function loadFees(?string &$message = '')
	{
		$toReturn = [];
		loadClass($this->load, 'fee_description');
		loadClass($this->load, 'sessions');
		loadClass($this->load, 'payment');
		loadClass($this->load, 'transaction');

		// check if returning student programme had been changed from their original programme given.
		// and allow the paid sch fees in their previous programme to load as part of payment in the
		// student portal. Since they changed their programme and they have paid in the prev programme
		$academic_record = $this->academic_record;
		$prevProgramme = $academic_record->prev_programme_id ?? null;
		$paymentOutstanding = null;
		if ($prevProgramme != null && $prevProgramme != $academic_record->programme_id) {
			$progParam = $this->loadPreviousStudentProgramme($this->id, $prevProgramme, $academic_record->current_session);
			$currentSemester = get_setting('active_semester');
			$param = [
				$academic_record->current_session,
				$progParam['old_programme_id'],
				$progParam['old_level_id'],
				$progParam['old_entry_mode'],
				$progParam['old_programme_id'],
				$progParam['old_level_id'],
				$progParam['old_entry_mode'],
			];

			$extraParam = false;
			if ($currentSemester == 2) {
				$param[] = 1;
				$extraParam = true;
			}
			$result = $this->loadStudentNewProgrammePayment($academic_record);
			if ($result) {
				$toReturn = array_merge($toReturn, $result);
			}

			// the idea is to load only prev programme first semester paid fee
			if ($extraParam) {
				$oldPayment = $this->loadMainFees($academic_record, $param, $extraParam);
				if ($oldPayment) {
					$toReturn = array_merge($toReturn, $oldPayment);
				}
			}
		} else {
			$result = $this->loadStudentPreviousPayment($academic_record);
			if ($result) {
				$toReturn = array_merge($toReturn, $result);
			}

			$result = $this->loadMainFees($academic_record, false, true, $message);
			if (!empty($result)) {
				$paymentOutstanding = array_filter($result, function ($item) {
					return $item['payment_code2'] == PaymentFeeDescription::SCH_FEE_FIRST;
				}, ARRAY_FILTER_USE_BOTH);
				if ($paymentOutstanding) {
					$paymentOutstanding = [...$paymentOutstanding][0];
				}
				$toReturn = array_merge($toReturn, $result);
			} else if ($message) {
				return false;
			}
		}

		// load topup fees on the payment card page
		if ($academic_record->topup_session && $academic_record->topup_session != null) {
			$result = $this->loadTopupFees($academic_record, PaymentFeeDescription::TOPUP_FEE_22);
			if ($result) {
				$toReturn = array_merge($toReturn, $result);
			}
		}

		// adding only once outstanding fee alongside other fees on student dashboard
		if ($paymentOutstanding) {
			$result = $this->loadOutstandingFees($paymentOutstanding, $academic_record);
			if ($result) {
				if ($result) {
					$toReturn = array_merge($toReturn, $result);
				}
			}
		}

		// adding sundry transaction to the main fee
		$sundryFees = $this->loadSundryTransaction($academic_record);
		if ($sundryFees) {
			$toReturn = array_merge($toReturn, $sundryFees);
		}

		return $this->removeDuplicateTransaction($toReturn);
	}

	private function removeDuplicateTransaction(?array $data): array
	{
		$result = [];
		$temp = [];
		foreach ($data as $value) {
			if ($value['paid'] && in_array($value['transaction_ref'], $temp)) {
				continue;
			}
			$temp[] = $value['transaction_ref'];
			$result[] = $value;
		}
		return $result;
	}

	/**
	 * @throws Exception
	 */
	private function loadOutstandingFees($payment, $academic_record): array
	{
		$outstandingResult = [];
		if ($academic_record->topup_session == null && $academic_record->outstanding_session) {
			$paymentID = hashids_decrypt($payment['payment_id']);
			$payment = $this->payment->getWhere(['id' => $paymentID], $c, 0, 1, false);
			if ($payment) {
				$tempPayment = $payment[0];
				$outstandingResult = $this->validateStudentOutstanding($tempPayment, $academic_record, true);
			}
		}
		return $outstandingResult;

	}

	/**
	 * This shows result for all pending and present success sundry transaction
	 * @param mixed $academic_record
	 * @return bool|array
	 * @throws Exception
	 */
	public function loadSundryTransaction($academic_record)
	{
		// no need to include successful sundry since loadStudentPreviousPayment method shows all main and sundry
		// however this now ensure current session sundry is included in the result
		$query = "SELECT distinct payment.*,transaction_ref,rrr_code,transaction.level as trans_level,transaction.session as payment_session
		from transaction join payment on transaction.real_payment_id = payment.id where (fee_category = 2 and transaction.student_id = ?
		and transaction.transaction_ref is not null and payment_status not in ('00','01')) or (payment_status in ('00','01') and
		fee_category = 2 and transaction.session = ? and transaction.student_id = ? )";
		$param = [$this->id, $academic_record->current_session, $this->id];

		$toReturn = [];
		$payments = $this->query($query, $param);
		if (!$payments) {
			return false;
		}
		foreach ($payments as $payment) {
			$toReturn[] = $this->convertPayment($payment, $academic_record, $payment['payment_session'], 'sundry', $payment['transaction_ref'], $payment['rrr_code']);
		}
		return $toReturn;
	}

	/**
	 * Loading the sundry fees separately from the main fee
	 * @param mixed $message
	 * @return array|false
	 */
	public function loadSundryFees(&$message = '')
	{
		loadClass($this->load, 'fee_description');
		loadClass($this->load, 'sessions');
		loadClass($this->load, 'payment');

		$academic_record = $this->academic_record;
		// if ($this->isFreshStudent($academic_record) && !$this->is_verified) {
		// 	$message = 'Student has not been verified';
		// 	return false;
		// }
		return $this->getSundryFees();
	}

	/**
	 *
	 * These are payment not necessarily compulsory but required
	 * @return array
	 */
	private function getSundryFees(): array
	{
		$result = [];
		$query = "SELECT * from payment where fee_category=2 and is_visible=1";
		$temp = $this->query($query);
		if (!$temp) {
			return [];
		}

		$academic_record = $this->academic_record;
		foreach ($temp as $item) {
			$result[] = $this->convertPayment($item, $academic_record, null, 'sundry');
		}
		return $result;
	}

	/**
	 * @param $programme
	 * @param mixed $levels
	 * @param mixed $entryModes
	 * @return void@param mixed $programme
	 * @deprecated - NOT IN USED AT THE MOMENT
	 */
	public function getTempPayment($programme, $levels, $entryModes)
	{
		foreach ($levels as $level_key => $level_filter) {
			if (!is_array($entryModes)) {
				continue;
			}

		}
	}

	/**
	 * @param mixed $examRecord
	 * @param mixed $session
	 * @param mixed $semester
	 * @return bool|<missing>
	 */
	public function getProgramDetails($examRecord = false, $session = false, $semester = null)
	{
		$data = [];
		if ($examRecord) {
			$query = "SELECT distinct entry_mode, sessions.date as entry_year, academic_record.current_level as level,
                (select date from sessions s2 where s2.id=course_enrollment.session_id) as current_session,
                academic_record.current_session as current_session_id, department.name as department, programme.name as programme,
                faculty.name as faculty, mode_of_study, entry_mode,course_enrollment.student_level from students left join
                academic_record on academic_record.student_id = students.id left join course_enrollment on course_enrollment.student_id = academic_record.student_id
                join programme on programme.id = academic_record.programme_id join department on department.id = programme.department_id
                join faculty on faculty.id=programme.faculty_id join sessions on sessions.id = academic_record.year_of_entry
                where students.id=? and course_enrollment.student_id = ? and
                course_enrollment.session_id = ?";
			$data = [$this->id, $this->id, $session];
			if ($semester) {
				$semesterName = ($semester && $semester == 'first') ? 1 : 2;
				$query .= " and course_enrollment.semester = ?";
				$data[] = $semesterName;
			}
			$query .= " order by course_enrollment.student_level desc";
		} else {
			$query = "SELECT entry_mode, sessions.date as entry_year, academic_record.current_level as level,
            (select date from sessions where id=academic_record.current_session) as current_session,academic_record.current_session as current_session_id,
            department.name as department, programme.name as programme, faculty.name as faculty, mode_of_study, entry_mode from
             academic_record  join programme on programme.id = academic_record.programme_id join department on department.id = programme.department_id
            join faculty on faculty.id=programme.faculty_id join sessions on sessions.id = academic_record.year_of_entry
             where academic_record.student_id=?";
			$data = [$this->id];
		}
		$result = $this->query($query, $data);
		if (!$result) {
			return false;
		}
		return $result[0];
	}

	public function getStudentProgramDetails()
	{
		$data = [];
		$query = "SELECT distinct entry_mode, f.date as entry_year, (select date from sessions s2 where s2.id=a.current_session) as current_session,
		a.current_session as current_session_id, d.name as department, c.name as programme, e.name as faculty, a.mode_of_study,
        a.entry_mode,a.current_level as student_level from academic_record a join programme c on c.id = a.programme_id
        join department d on d.id = c.department_id join faculty e on e.id=c.faculty_id join sessions f on f.id = a.year_of_entry
        where a.student_id = ?";
		$data = [$this->id];
		$query .= " order by student_level desc";
		$result = $this->query($query, $data);
		if (!$result) {
			return false;
		}
		return $result[0];
	}

	/**
	 * @return bool|<missing>
	 */
	public function checkStudentTransactionByCode(string $code)
	{
		$query = "SELECT students.id as student_id, fee_description.code as payment_code,transaction.real_payment_id as payment_id,
       	transaction.id as vid FROM students left join transaction on transaction.student_id = students.id left join
        fee_description ON fee_description.id = transaction.payment_id where transaction.student_id=? and
        fee_description.code=? and transaction.payment_status in ('00', '01')";
		$result = $this->query($query, [$this->id, $code]);
		if (!$result) {
			return false;
		}
		return $result[0];
	}

	/**
	 * The checks student payment for the session
	 * @param  [type]  $session [description]
	 * @param mixed $student
	 * @param mixed $session
	 * @param mixed $semester
	 * @param mixed $return
	 * @return boolean          [description]
	 */
	public function hasPayment($student, $session, $semester = null, $return = false)
	{
		$data = [$student, $session];
		$sessionSemesterStart = get_setting('session_semester_payment_start');
		// to validate for student prior when fee was not based on semester
		if ($session < $sessionSemesterStart) {
			$semester = 1;
		}

		$query = "SELECT * from transaction where student_id=? and session=? and payment_status in ('00', '01')";
		if ($semester) {
			$query .= " and payment_id = ?";
			$data[] = $semester;
		}
		$query .= " order by date_performed desc";
		$result = $this->query($query, $data);
		if (!$result) {
			return false;
		}
		return ($return) ? $result : true;
	}

	/**
	 * Checking if student is already entrolled for a course
	 * @param mixed $session
	 * @param mixed $studentLevel
	 * @param mixed $courseArray
	 * @param mixed $semester
	 * @return bool|<missing>@param mixed $course
	 */
	public function checkEnrolledCourses($course, $session, $studentLevel, $courseArray = false, $semester = null)
	{
		$query = "SELECT course_id, student_id, session_id, semester from course_enrollment where student_id = ? and course_id = ? and student_level = ?";
		$data = [$this->id, $course, $studentLevel];
		$sessionSemesterStart = get_setting('session_semester_payment_start');
		// to validate for student prior when fee was not based on semester
		if ($session < $sessionSemesterStart) {
			$semester = null;
		}
		if ($semester) {
			$query .= " and semester = ?";
			$data[] = $semester;
		}

		$result = $this->query($query, $data);
		if (!$result) {
			return false;
		}

		foreach ($result as $value) {
			if ($course == $value['course_id'] && $session == $value['session_id']) {
				if ($courseArray) {
					return $value;
				}
				return true;
			}
		}
		return false;
	}

	/**
	 * @param mixed $session
	 * @param mixed $studentLevel
	 * @return bool
	 */
	public function checkExamRecord($session, $studentLevel = false)
	{
		$query = "SELECT id from exam_record where student_id = ? and session_id = ?";
		if ($studentLevel) {
			$query .= " and student_level = '$studentLevel'";
		}
		$result = $this->query($query, [$this->id, $session]);
		if (!$result) {
			return false;
		}
		return true;
	}

	/**
	 * @param mixed $student_id
	 * @param mixed $session_id
	 * @param mixed $level_id
	 */
	public function getStudentRegisteredCourseResult($student_id, $session_id, $level_id)
	{
		$query = "SELECT distinct courses.code as course_code,course_enrollment.course_unit as course_enrollment_unit,course_enrollment.total_score,if(total_score >= pass_score, 'pass', 'fail') as remark from course_enrollment join courses on courses.id=course_enrollment.course_id join course_mapping on course_mapping.course_id = courses.id where course_enrollment.student_id=? and course_enrollment.student_level=? and course_enrollment.session_id=?";
		$result = $this->query($query, [$student_id, $level_id, $session_id]);
		return $result;
	}

	/**
	 * @param mixed $student_id
	 * @param mixed $session_id
	 * @param mixed $level_id
	 * @param mixed $semester
	 */
	public function getStudentRegisteredCourses($student_id, $session_id, $level_id, $semester = null)
	{
		$query = "SELECT *,date(course_enrollment.date_created) as date_registered from course_enrollment join courses on courses.id=course_enrollment.course_id where student_id=? and student_level=? and session_id=?";
		if ($semester) {
			$query .= " and course_enrollment.semester = '$semester'";
		}
		$result = $this->query($query, [$student_id, $level_id, $session_id]);
		return $result;
	}

	/**
	 * @param mixed $programme_id
	 * @param mixed $course_id
	 * @param mixed $level_id
	 * @return bool|<missing>
	 */
	public function getCourseMappingDetails($programme_id, $course_id, $level_id)
	{
		$query = 'SELECT * from course_mapping where course_id=? and programme_id=?';
		$result = $this->query($query, [$course_id, $programme_id]);
		if (!$result) {
			return false;
		}
		$searchingLevel = $level_id;
		while ($searchingLevel > 0) {
			foreach ($result as $res) {
				$lev = json_decode($res['level']);
				if (in_array($searchingLevel, $lev)) {
					return $res;
				}
			}
			$searchingLevel--;
		}
		return false;
	}

	/**
	 * @param mixed $id
	 * @param mixed $orderBy
	 * @return bool|<missing>
	 */
	private function getAllStudentEnrolledSession($id = null, $orderBy = 'desc')
	{
		$query = "SELECT distinct session_id,student_level,sessions.date from course_enrollment join sessions on sessions.id=course_enrollment.session_id where student_id=? order by sessions.date {$orderBy}";
		$id = $id ?: $this->id;
		$allSession = $this->query($query, [$id]);
		if (!$allSession) {
			return false;
		}
		return $allSession;
	}

	/**
	 * @param mixed $id
	 * @param mixed $orderBy
	 * @return bool|<missing>
	 */
	private function getAllStudentEnrolledSessionSemester($id = null, $orderBy = 'desc')
	{
		$query = "SELECT distinct semester,session_id,student_level,sessions.date from course_enrollment join sessions on sessions.id=course_enrollment.session_id where student_id=? order by sessions.date {$orderBy}";
		$id = $id ?: $this->id;
		$allSession = $this->query($query, [$id]);
		if (!$allSession) {
			return false;
		}
		return $allSession;
	}

	/**
	 * @param mixed $student_id
	 * @param mixed $course
	 * @param mixed $currentSession
	 * @param mixed $currentLevel
	 * @return bool
	 */
	public function checkCourseEnrollmentStatus($student_id, $course, $currentSession, $currentLevel)
	{
		$query = "SELECT * from course_enrollment where student_id = ? and course_id = ? and session_id = ? and student_level = ?";
		$result = $this->query($query, [$student_id, $course, $currentSession, $currentLevel]);
		if ($result) {
			if (isset($result['course_id']) && $course == $result['course_id']) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param mixed $student_id
	 * @param mixed $course_id
	 * @param mixed $session_id
	 * @param mixed $student_level
	 * @return bool
	 */
	public function alreadyHasRegistration($student_id, $course_id, $session_id, $student_level)
	{
		$query = "SELECT id,course_id from course_enrollment where student_id=? and course_id=? and session_id=? and student_level=?";
		$result = $this->query($query, [$student_id, $course_id, $session_id, $student_level]);
		if ($result) {
			return true;
		}
		return false;
	}

	/**
	 * @param mixed $student_id
	 * @param mixed $level
	 * @param mixed $programme
	 * @param mixed $entryMode
	 * @param mixed $session
	 * @param mixed $semester
	 * @return array
	 */
	public function getRegistrationCourses($student_id, $level, $programme, $entryMode, $session, $semester)
	{
		// make sure student cannot register for courses that is higher
		$query = "SELECT *,courses.id  as cid from course_mapping join courses on courses.id=course_mapping.course_id where programme_id=? and course_mapping.mode_of_entry=? and course_mapping.semester = ?  and not exists (select * from course_enrollment where student_id=? and  course_id=courses.id and session_id=?)";
		$result = $this->query($query, [$programme, $entryMode, $semester, $student_id, $session]);
		if (!$result) {
			return [];
		}
		$return = [];
		$academicRecord = $this->academic_record;
		foreach ($result as $res) {
			$lev = json_decode($res['level']);
			$maxLevel = max($lev);
			if ($level >= $maxLevel) {
				// ensuring higher level course doesn't show up above current level
				$return[] = $res;
			}
		}
		return $return;
	}

	/**
	 * @param mixed $id
	 * @return <missing>|array
	 */
	public function getAllStudentSessionSemesterPaid($id)
	{
		$id = $id ?: $this->id;
		$code = get_setting('school_fees_code');
		$query = "SELECT distinct payment_id as semester, sessions.id as session_id,transaction.level as student_level,date FROM sessions left join transaction on transaction.session=sessions.id left join fee_description on fee_description.id=transaction.payment_id where transaction.student_id=? and fee_description.code=? and payment_status in ('00', '01') ";
		$query .= 'order by date desc';
		$result = $this->query($query, [$id, $code]);
		return $result ?: [];
	}

	/**
	 * @param mixed $student_id
	 * @return array
	 */
	public function getAllStudentRegisteredCourses($student_id): array
	{
		$allSession1 = $this->getAllStudentEnrolledSessionSemester($student_id);
		$allSession2 = $this->getAllStudentSessionSemesterPaid($student_id);
		if (!$allSession1) {
			return [];
		}

		$allSession = array_merge($allSession1, $allSession2);
		$allSession = removeDuplicateValues($allSession);

		$results = [];
		$tmp = [];
		foreach ($allSession as $content) {
			if (in_array($content['session_id'] . "_" . $content['semester'], $tmp)) {
				continue;
			}

			if (array_key_exists($content['session_id'], $results) !== false) {
				$results[$content['session_id'] . "_" . $content['student_level']][] = $content;
			} else {
				$results[$content['session_id'] . "_" . $content['student_level']][] = $content;
			}
			$tmp[] = $content['session_id'] . "_" . $content['semester']; // to remove edge case duplicate when level varies
		}

		$contents = [];
		if (!empty($results)) {
			foreach ($results as $result) {
				$contents[] = $this->groupCourseBySemester($student_id, $result);
			}
		}
		return $contents;
	}

	/**
	 * @param mixed $student_id
	 * @param mixed $data
	 * @return array<int,array<string,mixed>>
	 */
	private function groupCourseBySemester($student_id, $data): array
	{
		$result = [];
		$semesters = ['first', 'second'];
		foreach ($data as $session) {
			$semesterIndex = $session['semester'] - 1; // semesters start at index 0
			$semester = @$semesters[$semesterIndex];

			$courseSemesterLabel = "course_record_" . $semester;
			$courses = $this->getStudentRegisteredCourses($student_id, $session['session_id'], $session['student_level']);
			$payload = [
				'session_name' => $session['date'],
				'session_id' => $session['session_id'],
				'student_level' => $session['student_level'],
				"$courseSemesterLabel" => [
					'semester' => $semester,
					'has_payment' => $this->hasPayment($student_id, $session['session_id'], $session['semester']),
					'print_url' => site_url("web/courseregistrationAll/{$student_id}/{$session['session_id']}/{$session['semester']}"),
				],
				'courses_data' => $courses,
			];
			$result[] = $payload;
		}

		if (!empty($result)) {
			$result = array_merge(...$result); // flatten the array to two-dimensional array
		}
		return $result;
	}

	/**
	 * @param mixed $student_id
	 * @return <missing>|array
	 */
	public function getCourseRegistrationLog($student_id = false): array
	{
		$query = "SELECT *,course_registration_log.date_created,concat_ws(' ',staffs.firstname,staffs.lastname) as name from
		course_registration_log join students on students.id=course_registration_log.student_id join users_new on users_new.user_login=course_registration_log.username
		join sessions on  sessions.id=course_registration_log.session_id join courses on courses.id=course_registration_log.course_id
        left join staffs on staffs.id=users_new.user_table_id order by course_registration_log.id desc limit 200";
		if ($student_id) {
			$query = "SELECT courses.code,course_registration_log.operation,course_registration_log.date_created,concat_ws(' ',staffs.firstname,staffs.lastname) as name from
            course_registration_log join students on students.id=course_registration_log.student_id join users_new on users_new.user_login=course_registration_log.username
            join sessions on  sessions.id=course_registration_log.session_id join courses on courses.id=course_registration_log.course_id
           left join staffs on staffs.id=users_new.user_table_id where students.id='$student_id' order by course_registration_log.id desc limit 200";
		}
		$result = $this->query($query);
		return $result ?: [];
	}

	/**
	 * @param mixed $student_id
	 * @param mixed $course_id
	 * @param mixed $session_id
	 * @param mixed $level_id
	 * @return bool
	 */
	public function courseHasScore($student_id, $course_id, $session_id, $level_id)
	{
		// find the course enrollment role with that value
		$query = "SELECT * from  course_enrollment where student_id=? and course_id=? and session_id=? and student_level=?";
		$result = $this->query($query, [$student_id, $course_id, $session_id, $level_id]);
		return !@is_null($result[0]['total_score']);
	}

	/**
	 * [checkStudentLevelInSession description]
	 * @param $session
	 * @param mixed $level
	 * @return bool@param mixed $session
	 * @deprecated - NOT USED AGAIN
	 */
	public function checkStudentLevelInSession($session, $level): bool
	{
		$allSession = $this->getAllStudentEnrolledSession(null, 'asc');
		if (!$allSession) {
			return false;
		}
		$result = [];
		foreach ($allSession as $sessions) {
			$content = $sessions['session_id'] . "." . $sessions['student_level'];
			$result[] = $content;
		}
		if (!empty($result)) {
			$needle = $session . "." . $level;
			if (in_array($needle, $result) !== false) {
				return true;
			}
			return false;
		}
		return true;
	}

	/**
	 * @return bool|array<int,array>
	 */
	public function getExamRecordList()
	{
		$allSession = $this->getAllStudentEnrolledSession(null, 'asc');
		if (!$allSession) {
			return false;
		}

		$result = [];
		foreach ($allSession as $session) {
			$content = [
				'exam_session_id' => $session['session_id'] . '.' . $session['student_level'],
				'exam_session' => $session['date'],
			];
			$result[] = $content;
		}
		return $result;
	}

	/**
	 * @return bool|array<int,array<string,mixed>>
	 */
	public function getAllStudentRegisteredCoursesResult()
	{
		$allSession = $this->getAllStudentEnrolledSession(null, 'desc');
		if (!$allSession) {
			return false;
		}

		$result = [];
		foreach ($allSession as $session) {
			$content = [];
			$content['name'] = $session['date'];
			$content['session_id'] = $session['session_id'] . '.' . $session['student_level'];
			$courses = $this->getStudentRegisteredCourseResult($this->id, $session['session_id'], $session['student_level']);
			$content['result'] = $courses ?: [];
			$result[] = $content;
		}
		return $result;
	}

	/**
	 * @return bool|array
	 */
	public function getStudentVerificationDocuments()
	{
		$query = "SELECT distinct a.id,a.students_id,b.name as document_name,other,document_path,a.date_created,b.id as verification_documents_requirement_id
		from student_verification_documents a left join verification_documents_requirement b on b.id = a.verification_documents_requirement_id
		where a.students_id = ?";
		$result = $this->query($query, [$this->id]);
		if (!$result) {
			return false;
		}
		$toReturn = [];
		foreach ($result as $res) {
			if (!$res['document_path'] || $res['document_path'] == '') {
				$res['document_path'] = null;
			} else {
				$baseurl = str_replace('admin/', '', base_url());
				$baseurl = $baseurl . $res['document_path'];
				$res['document_path'] = $baseurl;
			}
			$toReturn[] = $res;
		}
		return $toReturn;
	}

	/**
	 * @param mixed $id
	 * @return bool|<missing>
	 */
	public function getStudentVerificationDocumentsById($id)
	{
		$query = "SELECT distinct a.id,a.students_id,b.name as document_name,other,document_path,a.date_created,b.id as verification_documents_requirement_id from student_verification_documents a left join verification_documents_requirement b on b.id = a.verification_documents_requirement_id where a.id = ?";
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		$res = $result[0];
		if (!$res['document_path'] || $res['document_path'] == '') {
			$res['document_path'] = null;
		} else {
			$baseurl = "https://apex.ui.edu.ng/";
			$baseurl = $baseurl . $res['document_path'];
			$res['document_path'] = $baseurl;
		}
		return $res;
	}

	/**
	 * @return bool|<missing>
	 */
	public function getDocumentVerificationStatus()
	{
		$query = "SELECT id,document_verification,verify_comments from students where id = ?";
		$result = $this->query($query, [$this->id]);
		if (!$result) {
			return false;
		}
		return $result[0];
	}

	/**
	 * @return bool*@deprecated - This is not in used at the moment
	 * [getCourseConfig description]
	 */
	public function getCourseConfig()
	{
		loadClass($this->load, 'course_configuration');
		$record = $this->academic_record;
		$query = "select * from course_configuration where programme_id=? and level=? and entry_mode =?";
		$tempResult = $this->query($query);
		if (!$tempResult) {
			return false;
		}
		$result = [];
	}

	/**
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $payment
	 * @return array<string,bool>
	 */
	public function checkPayment($session = null, $level = null, $payment = null): array
	{
		$query = "SELECT * from transaction where student_id = ? and payment_id = ? and payment_status in ('00', '01')";
		$data = [$this->id, $payment];
		if ($session) {
			$query .= " and session = ?";
			$data[] = $session;
		}
		if ($level) {
			$query .= " and level = ?";
			$data[] = $level;
		}

		$result = $this->query($query, $data);
		if (!$result) {
			return ['status' => false];
		}
		$result = $result[0];
		return ['status' => true];
	}

	/**
	 * @param mixed $student
	 * @return bool|Students
	 */
	public function getStudentRecordDetails($student = false)
	{
		$query = "SELECT students.id as studentID,students.*, academic_record.*, s1.date as entry_year, s2.date as current_academic_session,
       medical_record.*, programme.name as programme, department.name as department, faculty.name as faculty from students join
        academic_record on academic_record.student_id = students.id left join medical_record on medical_record.student_id = students.id
        left join sessions s1 on s1.id = academic_record.year_of_entry left join sessions s2 on s2.id = academic_record.current_session join
        programme on programme.id = academic_record.programme_id left join department on department.id = programme.department_id left join
        faculty on faculty.id = programme.faculty_id where students.id = ?";
		$id = $student ? $student : $this->id;
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		unset($result[0]['password'], $result[0]['user_pass']);
		return new Students($result[0]);
	}

	public function getStudentRecordData($student = false)
	{
		$query = "SELECT students.id as studentID,students.*, academic_record.*,students.id as id, s1.date as entry_year, s2.date as current_academic_session, programme.name as programme, department.name as department, faculty.name as faculty from students join
        academic_record on academic_record.student_id = students.id left join sessions s1 on s1.id = academic_record.year_of_entry left join sessions s2 on s2.id = academic_record.current_session join programme on programme.id = academic_record.programme_id left join department on department.id = programme.department_id left join
        faculty on faculty.id = programme.faculty_id where students.id = ?";
		$id = $student ? $student : $this->id;
		$result = $this->query($query, [$id]);
		if (!$result) {
			return false;
		}
		unset($result[0]['password'], $result[0]['user_pass']);
		return new Students($result[0]);
	}

	/**
	 * @param mixed $transaction
	 * @return bool|Students
	 */
	public function getStudentTransactionReceipt($transaction)
	{
		$query = "SELECT transaction.*, academic_record.student_id, academic_record.programme_id, academic_record.matric_number,
        students.firstname, students.othernames, students.lastname, students.passport, students.user_login as student_email, programme.name as programme, faculty.name as faculty,
        department.name as department,s1.date as transaction_session from transaction join academic_record on academic_record.student_id = transaction.student_id join students on students.id = transaction.student_id left join programme on programme.id = academic_record.programme_id left join department on department.id = programme.department_id left join faculty on faculty.id = programme.faculty_id left join sessions s1 on s1.id = transaction.session where transaction.payment_status in ('00', '01') and transaction.rrr_code = ?";
		$result = $this->query($query, [$transaction]);
		if (!$result) {
			return false;
		}
		$result = $result[0];
		$preselectedStatus = false;
		if ($result['preselected_payment'] && $result['preselected_payment'] != 0) {
			$preselectedStatus = true;
			$preselected = $this->transformPaymentAmount($result['preselected_payment'], true);
			$result['preselected_desc'] = $preselected['desc'];
			$result['preselected_amount'] = $preselected['amount'];
		}
		if ($result['payment_id']) {
			if (!$preselectedStatus) {
				$result['main_payment_desc'] = $result['payment_description'];
				$result['main_amount'] = $result['amount_paid'];
			} else {
				$mainPayment = $this->transformPaymentAmount($result['real_payment_id'], true);
				$result['main_payment_desc'] = $mainPayment['desc'];
				$result['main_amount'] = $mainPayment['amount'];
			}
		}
		return new Students($result);
	}

	/**
	 * @param mixed $payment
	 * @param mixed $skipDueDate
	 * @return array|array<string,mixed>
	 */
	public function transformPaymentAmount($payment, $skipDueDate = false)
	{
		loadClass($this->load, 'payment');
		loadClass($this->load, 'fee_description');
		loadClass($this->load, 'sessions');
		$payment = $this->payment->getWhere(['id' => $payment], $count, 0, null, false);
		$result = [];
		if ($payment) {
			$payment = $payment[0];
			$session = $payment->session;
			$sess = $this->sessions->getWhere(['id' => $session], $c, 0, null, false);
			$session_name = $sess ? $sess[0]->date : '';
			$paymentDesc = $this->fee_description->getWhere(['id' => $payment->description], $c, 0, null, false);
			$paymentDesc = $paymentDesc ? $paymentDesc[0]->description . ' ' . $session_name : null;
			$serviceCharge = $payment->service_charge;
			$penalty_fee = 0;
			if (!$skipDueDate && @$payment->date_due) {
				$dueDateParam = $payment->getFormatDueDateParam($payment);
				$penalty_fee = (int)$dueDateParam[0];
			}
			$originalAmount = (int)$payment->amount;
			if ($payment->subaccount_amount) {
				$originalAmount += $payment->subaccount_amount;
			}
			$originalAmountService = $originalAmount + $serviceCharge;
			$totalAmount = ($originalAmountService + $penalty_fee);
			$result['desc'] = $paymentDesc;
			$result['amount'] = $totalAmount;
			$result['serviceCharge'] = $serviceCharge;
			$result['preAmount'] = $originalAmount;
		}
		return $result;
	}

	/**
	 * @return bool|<missing>
	 */
	public function getStudentViewRecord()
	{
		$query = "SELECT students.id as studentID, students.*, academic_record.*, academic_record.programme_id as programme_id_code, academic_record.current_session as current_session_code, programme.name as programme, department.name as department, faculty.name as faculty, medical_record.*, transaction.*, transaction.session as trans_session, s1.date as entry_year, s2.date as current_session from students left join academic_record on academic_record.student_id = students.id left join medical_record on medical_record.student_id = students.id left join programme on programme.id = academic_record.programme_id left join department on department.id = programme.department_id left join faculty on faculty.id = programme.faculty_id left join sessions s1 on s1.id = academic_record.year_of_entry left join sessions s2 on s2.id = academic_record.current_session left join transaction on transaction.student_id = students.id where students.id = ? order by academic_record.student_id desc limit 1";
		$result = $this->query($query, [$this->id]);
		if (!$result) {
			return false;
		}
		return $result[0];
	}

	/**
	 * @param mixed $student
	 * @param mixed $orderBy
	 * @return bool|<missing>
	 */
	private function loadStudentSessionEnrollment($student, $orderBy = 'asc')
	{
		$query = "SELECT sessions.id, date, student_level from sessions join course_enrollment on course_enrollment.session_id = sessions.id where student_id = ? group by sessions.id, student_level order by sessions.date $orderBy";
		$result = $this->query($query, [$student]);
		if (!$result) {
			return false;
		}
		return $result;
	}

	/**
	 * @return array|array<int,array>
	 */
	public function getStudentResults()
	{
		$result = $this->loadStudentSessionEnrollment($this->id);
		if (!$result) {
			return [];
		}
		loadClass($this->load, 'exam_record');
		$content = [];

		$cummWeightGpa = [];
		$cummUnit = [];
		$cgpaTotal = 0;
		foreach ($result as $data) {
			$temp = $this->getStudentCgpaStudentDetails($this->id, $data['id'], $data['student_level']);
			$temp = $this->processStudentWgp($temp);
			$result = $temp[0];
			$stats = $temp[1];

			$failedCourses = $this->getStudentFailedCourses($this->id, $data['id'], $data['student_level']);
			$carryOverCourses = $this->processFailedCourses($failedCourses, 'course_codes');
			$carryOverCoursesStatus = $this->processFailedCoursesStatus($failedCourses, 'course_status');

			$cummWeightGpa[] = $stats['cwgp'];
			$cummUnit[] = $stats['tur'];

			$cgpaTotal = (!empty($result)) ? number_format(array_sum($cummWeightGpa) / array_sum($cummUnit), 2) : 0;
			// $cgpaTotal += (!empty($result)) ? $this->processCGPA($stats['cwgp'], $stats['tur']) : 0;
			$totalUnitPassed = (!empty($result)) ? $this->getStudentTotalPassedUnitCourses($this->id, $data['id'], $data['student_level']) : 0;

			$stats['carry_over_courses'] = $carryOverCourses;
			$stats['outstanding_course'] = null;
			$stats['status'] = $carryOverCoursesStatus;
			// $stats['tup'] = $this->processTotalUnitEarned($failedCourses, $stats['tur']);
			$stats['tup'] = $totalUnitPassed;
			$stats['cgpa'] = (!empty($result)) ? (float)$cgpaTotal : 0;

			$payload = [
				'session_id' => $data['id'],
				'session_name' => $data['date'],
				'student_level' => $data['student_level'],
				'result' => $result,
				'stats' => $stats,
			];
			$content[] = $payload;
		}

		return $content;
	}

	/**
	 * @param mixed $wga
	 * @param mixed $unit
	 * @return string
	 */
	private function processCGPA($wga, $unit)
	{
		return number_format($wga / $unit, 2);
	}

	/**
	 * @param mixed $student_id
	 * |
	 * | START CGPA TEST CASE AND ABANDONED FOR NOW
	 * |
	 * @param mixed $session
	 * @return float
	 */
	private function getCGPATillSession($student_id, $session)
	{
		$allTotalWeight = $this->getTotalWeightTillSession($student_id, $session);
		$allTotalUnit = $this->getTotalUnitTillSession($student_id, $session);

		return (float)number_format($allTotalWeight / $allTotalUnit, 2);
	}

	/**
	 * @param mixed $session
	 * @return int
	 */
	private function getTotalRegisteredWeight($session)
	{
		$gradeSession = $this->getClosestSessionId();

		$query = "SELECT distinct courses.code as course_code,course_enrollment.course_unit,course_enrollment.total_score,grades.point from course_enrollment join courses on courses.id = course_enrollment.course_id left join grades on course_enrollment.total_score between grades.mark_from and grades.mark_to and grades.year_of_entry = ? where course_enrollment.student_id = ? and course_enrollment.session_id = ?";
		$result = $this->query($query, [$gradeSession, $this->id, $session]);
		if (!$result) {
			return 0;
		}
		$sum = 0;
		foreach ($result as $res) {
			$sum += $res['course_unit'] * $res['point'];
		}

		return $sum;
	}

	/**
	 * @param mixed $student_id
	 * @param mixed $session
	 * @return int
	 */
	private function getTotalWeightTillSession($student_id, $session)
	{
		$query = "SELECT distinct sessions.id from sessions join course_enrollment on course_enrollment.session_id = sessions.id where course_enrollment.student_id = ? and sessions.id <= (select id from sessions where id = ?) group by sessions.id order by sessions.id";
		$result = $this->query($query, [$student_id, $session]);
		$total = 0;
		if (!$result) {
			return 0;
		}

		foreach ($result as $val) {
			$temp = $this->getTotalRegisteredWeight($val['id']);
			$total += $temp;
		}
		return $total;
	}

	/**
	 * @param mixed $student_id
	 * @param mixed $session
	 * @return int|<missing>
	 */
	private function getTotalUnitTillSession($student_id, $session)
	{
		$query = "SELECT sum(course_enrollment.course_unit) as total from course_enrollment join courses on courses.id = course_enrollment.course_id where course_enrollment.student_id = ? and course_enrollment.session_id in (SELECT distinct sessions.id from sessions join course_enrollment on course_enrollment.session_id = sessions.id where sessions.id <= (select id from sessions where id = ?) group by sessions.id)";
		$result = $this->query($query, [$student_id, $session]);
		if (!$result) {
			return 0;
		}
		return $result[0]['total'];
	}

	/**
	 * @param array<int,mixed> $data
	 * |
	 * | End CGPA TEST CASE
	 * |
	 * @param mixed $totalUnit
	 * @return int
	 */

	private function processTotalUnitEarned(array $data = [], $totalUnit = null)
	{
		$result = array_sum($data['course_units']);

		return $totalUnit - $result;
	}

	/**
	 * @param array<int,mixed> $data
	 * @return string
	 */
	private function processFailedCourses(array $data = [], string $column = null)
	{
		$result = "";
		if (!empty($data)) {
			foreach ($data[$column] as $codes) {
				if ($result) {
					$result .= ", ";
				}
				$result .= $codes;
			}
		}

		return $result;
	}

	/**
	 * @param array<int,mixed> $data
	 * @return string
	 */
	private function processFailedCoursesStatus(array $data = [], string $column = null)
	{
		$status = array_search('C', $data[$column]);
		if ($status != '' && $data['course_status'][$status] == 'C') {
			return "Fail";
		} elseif ($status != '' && $data['course_status'][$status] == 'R') {
			return "Fail";
		} elseif ($status != '' && $data['course_status'][$status] == 'E') {
			return "Pass";
		} else {
			return "N/A";
		}
	}

	/**
	 * @param array<int,mixed> $data
	 * @return array<int,mixed>
	 */
	private function processStudentWgp(array $data = [])
	{
		$content = [];
		$content1['tur'] = 0;
		$content1['cwgp'] = 0;
		$content1['gpa'] = 0;

		if (!empty($data)) {
			foreach ($data as $item) {
				$payload = $item;
				$gp = ($item['course_unit'] * $item['grade_point']);
				$payload['wgp'] = $gp;
				$content[] = $payload;

				$content1['tur'] += $item['course_unit'];
				$content1['cwgp'] += $gp;
			}
			$content1['gpa'] = (float)number_format($content1['cwgp'] / $content1['tur'], 2);
		}

		return [$content, $content1];
	}

	/**
	 * @param mixed $student_id
	 * @param mixed $sessionid
	 * @param mixed $level
	 * @return array|<missing>
	 */
	public function getStudentCgpaStudentDetails($student_id, $sessionid, $level)
	{
		$gradeSession = $this->getClosestSessionId($student_id);
		$query = "SELECT distinct a.student_id,b.programme_id,a.course_id,a.ca_score,a.exam_score,a.total_score,c.code as course_code,c.title as course_title,a.course_unit,a.course_status,if(e.name is not null, e.name, 'E') as grade,e.point as grade_point from course_enrollment a join academic_record b on b.student_id = a.student_id join courses c on c.id = a.course_id join approved_courses d on d.course_id = c.id and d.session_id = a.session_id left join grades e on a.total_score between e.mark_from and e.mark_to and e.year_of_entry = ? where a.student_id = ? and a.session_id = ? and a.student_level = ? order by c.code asc";
		$result = $this->query($query, [$gradeSession, $student_id, $sessionid, $level]);
		if (!$result) {
			return [];
		}
		return $result;
	}

	/**
	 * @param mixed $student_id
	 * @param mixed $session
	 * @param mixed $level
	 * @return array<string,array>
	 */
	public function getStudentFailedCourses($student_id, $session, $level)
	{
		$gradeSession = $this->getClosestSessionId($student_id);
		$courseCodeArray = [];
		$courseUnitArray = [];
		$courseStatusArray = [];

		$query = "SELECT course_enrollment.student_id, course_enrollment.course_id, course_enrollment.session_id as session, course_enrollment.student_level,course_enrollment.total_score, courses.code as course_code, courses.title as course_title, course_enrollment.course_unit, course_enrollment.course_status from course_enrollment join courses on courses.id = course_enrollment.course_id left join grades on course_enrollment.total_score between grades.mark_from and grades.mark_to and grades.year_of_entry = ? where grades.point = 0 and course_enrollment.student_id = ? and course_enrollment.session_id = ? and course_enrollment.student_level = ?";
		$result = $this->query($query, [$gradeSession, $this->id, $session, $level]);
		if (!$result) {
			return ['course_codes' => $courseCodeArray, 'course_units' => $courseUnitArray, 'course_status' => $courseStatusArray];
		}

		foreach ($result as $data) {
			$coursesCodes = $data['course_code'];
			$courseCodeArray[] = $coursesCodes;

			$coursesUnits = $data['course_unit'];
			$courseUnitArray[] = $coursesUnits;

			$coursesStatus = $data['course_status'];
			$courseStatusArray[] = $coursesStatus;
		}

		return ['course_codes' => $courseCodeArray, 'course_units' => $courseUnitArray, 'course_status' => $courseStatusArray];
	}

	/**
	 * @param mixed $student_id
	 * @param mixed $session
	 * @param mixed $level
	 * @return int
	 */
	public function getStudentTotalPassedUnitCourses($student_id, $session, $level)
	{
		$gradeSession = $this->getClosestSessionId($student_id);

		$query = "SELECT sum(course_enrollment.course_unit) as total from course_enrollment join courses on courses.id = course_enrollment.course_id left join grades on course_enrollment.total_score between grades.mark_from and grades.mark_to and grades.year_of_entry = ? and grades.point > 0 where course_enrollment.student_id = ? and course_enrollment.session_id = ? and course_enrollment.student_level = ?";
		$result = $this->query($query, [$gradeSession, $this->id, $session, $level]);
		if (!$result) {
			return 0;
		}
		return (int)$result[0]['total'];
	}

	/**
	 * @param mixed $student_id
	 * @param mixed $session
	 * @param mixed $level
	 * @param mixed $programme_id
	 * @return void
	 */
	public function getOutstandingCourses($student_id, $session, $level, $programme_id)
	{

	}

	/**
	 * @return array|array<string,array<string,mixed>>
	 */
	public function getStudentResultStatement()
	{
		$allSession = $this->loadStudentSessionEnrollment($this->id, 'desc');
		if (!$allSession) {
			return [];
		}

		$results = [];
		$overallUnit = 0;
		$overallPassedUnit = 0;
		$overallWeight = 0;
		foreach ($allSession as $session) {
			$courses = $this->getStudentRegisteredCourses($this->id, $session['id'], $session['student_level']);
			$courses = $this->processResultStats($courses);
			$payload = [
				'session_id' => $session['id'],
				'session_name' => $session['date'],
				'student_level' => $session['student_level'],
				'result' => $courses[0],
				'stats' => $courses[1],
			];
			$overallUnit += $courses[1]['total_unit'];
			$overallPassedUnit += $courses[1]['total_unit_passed'];
			$overallWeight += $courses[1]['total_weight'];

			$results['result_per_session'][] = $payload;
		}

		$results['result_overall_stats'] = [
			'overall_unit' => $overallUnit,
			'overall_passed_unit' => $overallPassedUnit,
			'overall_weight' => $overallWeight,
			'overall_cgpa' => (float)number_format($overallWeight / $overallUnit, 2),
			'class_of_degree' => $this->getCgpaClassByStudentId($this->id),
		];
		return $results;
	}

	/**
	 * @param mixed $student
	 * @return bool
	 */
	private function getCgpaClassByStudentId($student)
	{
		$metric = $this->getStudentResultsWithPoint($student);
		return $metric[count($metric) - 1];
	}

	/**
	 * @param mixed $student
	 * @param mixed $session
	 * @param mixed $semester
	 * @return bool
	 */
	private function getStudentResultsWithPoint($student, $session = false, $semester = false)
	{
		$gradeSession = $this->getClosestSessionId($student);
		$extra = '';
		$param = [$gradeSession, $student];
		if ($session) {
			$extra = " and session_id=?";
			$param[] = $session;
		}

		if ($semester) {
			$extra .= " and semester=?";
			$param[] = $semester;
		}
		$query = "SELECT *,(select point from grades where total_score between mark_from and mark_to and year_of_entry=?) as grade_point from course_enrollment where student_id=? $extra";
		$result = $this->query($query, $param);
		if (!$result) {
			return false;
		}
		$result_metrics = $this->getResultMetrics($result);
		loadClass($this->load, 'class_of_degree');
		if (!$session) {
			$class_of_degree_session = $this->getClosestClassSessionId($student);
			if (!$class_of_degree_session) {
				$student_info = $this->academic_record;
				exit("An error occured: check that student with matric number {$student_info->matric_number} has year of entry and that class of degree is configured correctly");
			}
			$cgpa = $result_metrics[3];
			$class = $this->class_of_degree->getCgpaClass($cgpa, $class_of_degree_session);
			$result_metrics[count($result_metrics)] = $class;
		}
		return $result_metrics;
	}

	/**
	 * @param array<int,mixed> $results
	 * @return array<int,mixed>
	 */
	private function getResultMetrics(array $results = [])
	{
		$unitRegistered = 0;
		$unitsPassed = 0;
		$wgp = 0;
		$cgpa = 0;
		$class = '';
		$resultNotIn = 0;
		foreach ($results as $result) {
			$unitRegistered += $result['course_unit'];
			if ($result['grade_point']) {
				$unitsPassed += $result['course_unit'];
			}
			if (is_null($result['total_score'])) {
				$resultNotIn += $result['course_unit'];
			}
			$wgp += $result['grade_point'] * $result['course_unit'];
		}
		$cgpa = number_format($wgp / $unitRegistered, 2);
		$result = [$unitRegistered, $unitsPassed, $wgp, $cgpa, $resultNotIn, $class];
		return $result;
	}

	/**
	 * @param mixed $student_id
	 * @return bool|<missing>
	 */
	public function getClosestClassSessionId($student_id)
	{
		$query = "SELECT year_of_entry from class_of_degree join sessions on sessions.id=class_of_degree.year_of_entry where sessions.date <= (select date from sessions join academic_record on academic_record.year_of_entry=sessions.id where academic_record.student_id=? limit 1) order by date desc limit 1";
		$result = $this->query($query, [$student_id]);
		if (!$result) {
			return false;
		}
		return $result[0]['year_of_entry'];
	}

	/**
	 * @param array<int,mixed> $data
	 * @return array<int,mixed>
	 */
	private function processResultStats(array $data = [])
	{
		$content = [];
		$content1 = [];
		$totalUnit = 0;
		$totalUnitPassed = 0;
		$totalWeight = 0;
		$gradeSession = $this->getClosestSessionId($this->id);
		loadClass($this->load, 'grades');

		if (!empty($data)) {
			foreach ($data as $item) {
				$point = $this->grades->getGradePoint($item['total_score'], $gradeSession);
				$payload = [
					'code' => $item['code'],
					'title' => $item['title'],
					'course_status' => $item['course_status'],
					'course_unit' => $item['course_unit'],
					'total_score' => $item['total_score'],
					'gp' => (int)$point,
					'wgp' => $point * $item['course_unit'],
				];
				$content[] = $payload;

				$totalUnit += $item['course_unit'];
				$totalUnitPassed += $point > 0 ? $item['course_unit'] : 0;
				$totalWeight += ($point * $item['course_unit']);
			}
		}

		$content1 = [
			'total_unit' => $totalUnit,
			'total_unit_passed' => $totalUnitPassed,
			'total_weight' => $totalWeight,
			'cgpa' => (float)number_format($totalWeight / $totalUnit, 2),
		];

		return [$content, $content1];
	}

	/**
	 * @param mixed $filterList
	 * @param mixed $queryString
	 * @param mixed $start
	 * @param mixed $len
	 * @param mixed $orderBy
	 * @return array
	 */
	public function APIList($filterList, $queryString, $start, $len, $orderBy): array
	{
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by a.date_created desc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}

		$query = "SELECT " . buildApiClause(static::$apiSelectClause, 'a') . " ,CONCAT(upper(a.lastname), ', ', a.firstname) AS fullname,
		b.matric_number as matric_number,b.current_level as level, c.date as year_of_entry,
		e.name as department, f.name as faculty, d.name as programme,b.application_number
		from students a join academic_record b on b.student_id = a.id left join sessions c on c.id = b.year_of_entry
		join programme d on d.id = b.programme_id join department e on e.id = d.department_id join
		faculty f on f.id = d.faculty_id $filterQuery";

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
		$generator = useGenerators($items);
		$payload = [];
		foreach ($generator as $item) {
			$payload[] = $this->loadExtras($item);
		}
		return $payload;
	}

	public function loadExtras($item)
	{
		if (isset($item['phone'])) {
			$item['phone'] = decryptData($this, $item['phone']);
		}

		return $item;
	}

	/**
	 * @param mixed $id
	 * @param mixed $get_code
	 * @return <missing>|null
	 */
	public function getDepartmentById($id, $get_code = false)
	{
		//Query database to get template
		$query = $this->db->get_where('department', ['id' => $id, 'active' => 1, 'type' => 'academic']);
		if ($query->num_rows() > 0) {
			$row = $query->row();
			return $get_code ? $row->code : $row->name;
		} else {
			return null;
		}
	}

	/**
	 * @param mixed $id
	 * @param mixed $get_code
	 * @return <missing>|null
	 */
	public function getProgrammeById($id, $get_code = false)
	{
		//Query database to get template
		$query = $this->db->get_where('programme', ['id' => $id, 'active' => 1]);
		if ($query->num_rows() > 0) {
			$row = $query->row();
			return $get_code ? $row->code : $row->name;
		} else {
			return null;
		}
	}

	public function getFacultyAttendance($student, $programmeID): ?string
	{
		$content = [
			'f_ar' => 'Tues 19 Nov., 2024',
			'f_sc' => 'Tues 19 Nov., 2024',
			'f_so' => 'Mon 18 Nov., 2024',
			'f_ed' => 'Wed 20 Nov., 2024',
			'f_cl' => 'Wed 20 Nov., 2024'
		];
		$result = $this->getProgrammefaculty($programmeID);
		if ($result) {
			return $content[$result[0]['faculty_code']] ?: null;
		} else {
			return null;
		}
	}

	public function getProgrammefaculty($id)
	{
		$query = "SELECT a.id,a.name as programme,a.code as programme_code,b.name as faculty,b.slug as faculty_code 
		FROM programme a join faculty b on a.faculty_id = b.id where a.id = ? and a.active = ?";
		return $this->query($query, [$id, '1']);
	}

	/**
	 * @param mixed $id
	 * @return array|<missing>
	 */
	public function getStudentAssignCards($id = null)
	{
		$orderBy = " svc.id desc";
		if (isset($_GET['sortBy'])) {
			$sortBy = request()->getGet('sortBy', true);
			$sortDirection = request()->getGet('sortDirection', true);
			$sortDirection = ($sortDirection == 'down') ? 'desc' : 'asc';
			$orderBy = " $sortBy $sortDirection ";
		}
		$query = "SELECT svc.id, CONCAT(firstname,' ',lastname,' ',othernames) as fullname,usage_status,svc.date_created as assign_date,vc.* from student_verification_cards svc join students stu on stu.id = svc.student_id join verification_cards vc on vc.id = svc.verification_cards_id where svc.student_id = ? order by $orderBy";
		$id = $id ?? $this->id;
		$result = $this->query($query, [$id]);
		if (!$result) {
			return [];
		}
		return $result;
	}

	public function updateStudentCardsUsage()
	{
		$query = "UPDATE student_verification_cards, verification_cards set usage_status = '1', card_status = '1' where student_id = ? and verification_cards_id = verification_cards.id";
		return $this->query($query, [$this->id]);
	}

	/**
	 * @param mixed $matric
	 * @return bool|<missing>
	 */
	public function getStudentIdByMatricNumber($matric)
	{
		$query = "SELECT student_id from academic_record where matric_number = ?";
		$result = $this->query($query, [$matric]);
		if (!$result) {
			return false;
		}
		return $result[0]['student_id'];
	}

	/**
	 * @return bool|<missing>
	 */
	public function getStudentRecordByMatricNo(string $matric)
	{
		$query = "SELECT academic_record.*, students.*, students.id as id from academic_record join students on students.id = academic_record.student_id where matric_number = ?";
		$result = $this->query($query, [$matric]);
		if (!$result) {
			return false;
		}
		return $result[0];
	}

	/**
	 * @param mixed $department_id
	 * @param mixed $programme_id
	 * @param mixed $level
	 * @param mixed $entry_mode
	 * @return <missing>|null
	 */
	public function generateMatricNumber($department_id, $programme_id, $level, $entry_mode)
	{
		$this->load->library('parser');

		$student_dept_code = $this->getDepartmentById($department_id, true);
		$student_prog_code = $this->getProgrammeById($programme_id, true);

		$dept_variables = ($department_id != 0 || $department_id != null) ? ['dept_code' => $student_dept_code] : "";
		$prog_variables = ($programme_id != 0 || $programme_id != null) ? ['prog_code' => $student_prog_code] : "";

		if (get_setting('auto_generate_matric_number') == 'yes') {
			$prefix = get_setting('matric_number_prefix');
			$format = get_setting('matric_number_format');
			$dept_code = get_setting('matric_dept_code_format');
			$prog_code = get_setting('matric_prog_code_format');
			$reset_dept_number = get_setting('matric_dept_zero_reset');
			$reset_prog_number = get_setting('matric_prog_zero_reset');

			$readable_dept_code = $this->parser->parse_string($dept_code, $dept_variables, true);
			$readable_prog_code = $this->parser->parse_string($prog_code, $prog_variables, true);

			$levels = json_decode(get_setting('matric_level_filter'), true);
			$entryModes = json_decode(get_setting('matric_entry_mode_filter'), true);
			$levels_include = json_decode(get_setting('matric_level_to_include'), true);
			$entryModesInclude = json_decode(get_setting('matric_entry_mode_to_include'), true);

			$department_id = ($reset_dept_number == 'yes') ? $department_id : 0;
			$programme_id = ($reset_prog_number == 'yes') ? $programme_id : 0;

			$date = date('Y-m-d H:i:s');

			$level_include_check = ($levels_include != '' && in_array($level, $levels_include)) ? $level : '';
			$entry_mode_include_check = ($entryModesInclude != '' && in_array($entry_mode, $entryModesInclude)) ? $entry_mode : '';

			foreach ($levels as $level_key => $level_filter) {
				foreach ($entryModes as $entry_mode_key => $entryMode_filter) {
					if ($level_filter == $level && $entryMode_filter == $entry_mode || $level_include_check == $level && $entry_mode_include_check == $entry_mode) {
						// get last generated matric number
						$query = $this->db->get_where('matric_number_generated', ['department_id' => $department_id, 'programme_id' => $programme_id]);

						// when dept_id and or programme_id is not found, check if either one exist then perform action
						$this->db->select('*');
						$this->db->from('matric_number_generated');
						$this->db->where(['department_id' => $department_id]);
						$this->db->or_where(['programme_id' => $programme_id]);

						$result = $this->db->get();

						if ($query->num_rows() > 0) {
							$row = $query->row();
							$last_number_generated = $row->last_generated_number;
							$last_dept_code_generated = $this->getDepartmentById($row->department_id, true);
							$last_prog_code_generated = $this->getProgrammeById($row->programme_id, true);

							$array = array_map('intval', str_split($last_number_generated));

							$zero_count = array_count_values($array);

							$numbers_of_zeros = (isset($zero_count[0])) ? $zero_count[0] + 1 : '';
							$numbers_of_zeros = ($numbers_of_zeros == 2) ? $numbers_of_zeros + 1 : $numbers_of_zeros;
							$number = $last_number_generated + 1;

							$new_generated_number = sprintf('%0' . $numbers_of_zeros . 'd', $number);

							$this->db->where(['department_id' => $department_id, 'programme_id' => $programme_id]);
							$this->db->update('matric_number_generated', ['last_generated_number' => $new_generated_number, 'date_updated' => $date]);

							if ($reset_dept_number == 'yes' && $dept_code != '' || $reset_dept_number == 'yes' && $dept_code != null) {
								$full_matric_number = $prefix . $readable_prog_code . $readable_dept_code . $new_generated_number;
							} elseif ($reset_prog_number == 'yes' && $prog_code != '' || $reset_prog_number == 'yes' && $prog_code != null) {
								$full_matric_number = $prefix . $readable_prog_code . $readable_dept_code . $new_generated_number;
							} else {
								$full_matric_number = $prefix . $new_generated_number;
							}
							return $full_matric_number;
						} else if ($query->num_rows() == '' && $result->num_rows() > 0) {
							foreach ($result->result() as $value) {
								$last_number_generated = $value->last_generated_number;
								$last_dept_code_generated = $this->getDepartmentById($value->department_id, true);
								$last_prog_code_generated = $this->getProgrammeById($value->programme_id, true);

								$array = array_map('intval', str_split($last_number_generated));

								$zero_count = array_count_values($array);

								$numbers_of_zeros = (isset($zero_count[0])) ? $zero_count[0] + 1 : '';

								$numbers_of_zeros = ($numbers_of_zeros == 2) ? $numbers_of_zeros + 1 : $numbers_of_zeros;

								$number = $last_number_generated + 1;

								$new_generated_number = sprintf('%0' . $numbers_of_zeros . 'd', $number);

								$this->db->where(['department_id' => $value->department_id, 'programme_id' => $value->programme_id]);
								$this->db->update('matric_number_generated', ['last_generated_number' => $new_generated_number, 'date_updated' => $date]);

								if ($student_dept_code == $last_dept_code_generated) {
									$full_matric_number = $prefix . $readable_prog_code . $readable_dept_code . $new_generated_number;
								} elseif ($reset_prog_number == 'yes' && $student_prog_code == $last_prog_code_generated) {
									$full_matric_number = $prefix . $readable_prog_code . $readable_dept_code . $new_generated_number;
								}
							}
							return $full_matric_number;
						} else {
							$new_matric_number = $format;
							$this->db->insert('matric_number_generated', ['department_id' => $department_id, 'programme_id' => $programme_id, 'last_generated_number' => $new_matric_number, 'date_first_inserted' => $date, 'date_updated' => $date]);

							if ($reset_dept_number == 'yes' && $dept_code != '' || $reset_dept_number == 'yes' && $dept_code != null) {
								$full_matric_number = $prefix . $readable_prog_code . $readable_dept_code . $new_matric_number;
							} elseif ($reset_prog_number == 'yes' && $prog_code != '' || $reset_prog_number == 'yes' && $prog_code != null) {
								$full_matric_number = $prefix . $readable_prog_code . $readable_dept_code . $new_matric_number;
							} else {
								$full_matric_number = $prefix . $new_matric_number;
							}
							return $full_matric_number;
						}
					} else {
						return null;
					}
				}
			}
		} else {
			return null;
		}

	}

	/**
	 * @param mixed $student
	 * @return void
	 */
	public function autoGenerateMatricNumber($student)
	{
		$academicRecord = $student->academic_record;
		$feeRecord = [
			'has_matric_number' => $academicRecord->has_matric_number,
		];
		$studentID = $student->id;
		// get student academic record
		$academicRecord = $this->getStudentAcademicRecord($studentID, $academicRecord->matric_number);
		$matric_number = ($feeRecord['has_matric_number'] == 1) ? $academicRecord['matric_number'] : null;
		$this->load->model('mailer');

		//check if student has matric number, if not; generate a new number
		if (get_setting('auto_generate_matric_number') == 'yes' && $feeRecord['has_matric_number'] == 0) {
			// generate matric number
			if ($academicRecord['entry_mode'] == CommonSlug::O_LEVEL_PUTME) {
				$academicRecord['entry_mode'] = CommonSlug::O_LEVEL;
			}

			$academicRecordID = $academicRecord['academic_id'];
			$matric_number = $this->generateMatricNumber($academicRecord['department_id'], $academicRecord['programme_id'], $academicRecord['current_level'], $academicRecord['entry_mode']);

			// notify for new matric number
			if ($matric_number != null && (ENVIRONMENT === 'production' || ENVIRONMENT === 'development')) {
				$variables_matric_number = [
					'matric_number' => $matric_number,
					'lastname' => $student->lastname,
					'firstname' => $student->firstname
				];

				// update new matric number
				update_record($this, 'academic_record', 'id', $academicRecordID, [
					'application_number' => $academicRecord['matric_number'],
					'matric_number' => $matric_number,
					'has_matric_number' => 1
				]);

				// send email
				// $this->mailer->send_new_mail('matric-number-activation-notification', $student->email, $variables_matric_number);

				return $matric_number;

			}
		}

		return $matric_number;
	}

	public function autoGenerateInstitutionalEmail($student, $matricNumber = null)
	{
		$academicRecord = $student->academic_record;
		$studentID = $student->id;
		$academicRecord = $this->getStudentAcademicRecord($studentID, $matricNumber);
		$academicRecordID = $academicRecord['academic_id'];
		$this->load->model('googleService');

		if (get_setting('auto_generate_email') == 'yes' && $academicRecord['has_institution_email'] == '0' && isset($matricNumber)) {
			$institutionEmail = strtolower($matricNumber . '.' . $student->lastname . '@' . get_setting('email_domain_address'));
			GoogleService::createInstitutionEmail($student->lastname, $student->firstname, $institutionEmail);
			if (update_record($this, 'students', 'id', $studentID, [
				'user_login' => $institutionEmail,
			])
			) {
				update_record($this, 'academic_record', 'id', $academicRecordID, [
					'has_institution_email' => '1',
				]);
				return $institutionEmail;
			}
		}
	}


	/**
	 * @return bool|<missing>
	 */
	public function getAllStudentWithNoMatric()
	{
		$currentSession = get_setting('active_session_student_portal');
		$query = "SELECT b.* from transaction a join students b on b.id = a.student_id join academic_record c on c.student_id = b.id where a.payment_id = '1' and a.payment_status in ('00', '01') and c.has_matric_number = '0' and a.session = ? ";
		$result = $this->query($query, [$currentSession]);
		if (!$result) {
			return false;
		}

		return $result;
	}

	/**
	 * @return bool|<missing>
	 */
	public function getAllStudentWithNoPassport()
	{
		$currentSession = get_setting('active_session_student_portal');
		// $query = "SELECT a.id,a.passport,c.passport as applicant_passport,a.user_login as username from students a join academic_record b on b.student_id = a.id join applicants c on c.applicant_id = b.application_number where a.active = '1' and a.passport like '%var%' ";

		$query = "SELECT a.id,a.passport,c.passport as applicant_passport,a.user_login as username from students a join academic_record b on b.student_id = a.id join applicants c on c.applicant_id = b.application_number where a.passport = '' and a.active = '1' and c.passport != '' ";

		$query .= "UNION SELECT a.id,a.passport,c.passport as applicant_passport,a.user_login as username from students a join academic_record b on b.student_id = a.id join applicant_post_utme c on c.applicant_id = b.application_number where a.passport = '' and a.active = '1' and c.passport != '' ";
		$result = $this->query($query);
		if (!$result) {
			return false;
		}

		return $result;
	}

	public function getStudentExamRecordData($session, $level, $programme)
	{
		$query = "SELECT distinct b.id, a.student_id as exam_student_id,a.session_id as exam_session_id,a.student_level,ANY_VALUE(b.lastname) as lastname,
       		ANY_VALUE(b.firstname) as firstname,ANY_VALUE(b.othernames) as othernames,ANY_VALUE(c.matric_number) as matric_number,
       		ANY_VALUE(c.year_of_entry) as year_of_entry from course_enrollment a join students b on b.id=a.student_id
			join academic_record c on c.student_id=a.student_id where a.session_id=? and a.student_level=? and c.programme_id=?
			group by a.student_id, b.id, a.session_id, a.student_level order by c.matric_number asc";
		return $this->query($query, [$session, $level, $programme]);
	}

	public function getActiveStudentList($session)
	{
		$query = "SELECT distinct b.student_id,c.matric_number,outstanding_session,topup_session,current_level,current_session,
                entry_mode from transaction b join academic_record c on c.student_id=b.student_id where b.session = ? and
                b.payment_id in ('1','2') and b.payment_status in ('00','01') ";
		return $this->query($query, [$session]);
	}

	public function getClosestStudentLevel($student, $session, $currentLevel)
	{
		$query = "SELECT level from transaction where student_id = ? and payment_id = ? and session = ? order by
        		date_performed asc limit 1";
		$result = $this->query($query, [$student, PaymentFeeDescription::SCH_FEE_FIRST, $session]);
		if (!$result) {
			return $currentLevel;
		}
		$level = $result[0]['level'];
		if ($level == $currentLevel) {
			return $currentLevel;
		}
		return $level;
	}

	public function checkStudentPaymentBySession($student, $session = null, $paymentId = null)
	{
		$code = get_setting('school_fees_code');
		$query = "SELECT a.id,b.code as payment_code,a.payment_id as payment_id,total_amount,amount_paid,rrr_code,payment_option,session FROM
		transaction a join fee_description b ON b.id = a.payment_id where a.student_id = ? and b.code = ? and a.payment_status in ('00', '01')";
		$param = [$student, $code];

		if ($session) {
			$query .= " and a.session = ?";
			$param[] = $session;
		}

		if ($paymentId) {
			$query .= " and a.payment_id = ?";
			$param[] = $paymentId;
		}

		$result = $this->query($query, $param);
		if (!$result) {
			return null;
		}
		return $result[0];
	}

	public function getStudentWithOutstanding()
	{
		$query = "SELECT student_id as id, outstanding_session from academic_record where 
                outstanding_session <> '' and outstanding_session is not null ";
		$result = $this->query($query);
		if (!$result) {
			return null;
		}
		return $result;
	}


}
