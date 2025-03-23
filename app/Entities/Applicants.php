<?php
namespace App\Entities;

use App\Enums\CommonEnum as CommonSlug;
use App\Models\Crud;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the applicants table.
 */
class Applicants extends Crud
{
	protected static $tablename = 'Applicants';
	/* this array contains the field that can be null*/
	static $nullArray = array('is_admitted', 'admitted_level', 'programme_duration', 'final_submit');
	static $compositePrimaryKey = array();
	static $uploadDependency = array();
	/*this array contains the fields that are unique*/
	static $uniqueArray = array('applicant_id', 'email');
	/*this is an associative array containing the fieldname and the type of the field*/
	static $typeArray = array('applicant_id' => 'varchar', 'lastname' => 'varchar', 'firstname' => 'varchar', 'othernames' => 'varchar', 'gender' => 'varchar', 'marital_status' => 'varchar', 'dob' => 'varchar', 'disabilities' => 'varchar', 'phone' => 'varchar', 'email' => 'varchar', 'contact_address' => 'varchar', 'state_of_origin' => 'varchar', 'lga' => 'varchar', 'nationality' => 'varchar', 'passport' => 'varchar', 'entry_mode' => 'varchar', 'de_type' => 'varchar', 'programme_id' => 'varchar', 'programme_given' => 'int', 'session_id' => 'varchar', 'jamb_details' => 'text', 'olevel_details' => 'text', 'alevel_details' => 'text', 'nce_nd_hnd' => 'text', 'institutions_attended' => 'text', 'referee' => 'text', 'admission_status' => 'varchar', 'is_admitted' => 'tinyint', 'admitted_level' => 'tinyint', 'programme_duration' => 'tinyint', 'step' => 'varchar', 'final_submit' => 'tinyint', 'access_token' => 'varchar', 'verification_code' => 'varchar', 'user_agent' => 'text', 'date_created' => 'datetime');
	/*this is a dictionary that map a field name with the label name that will be shown in a form*/
	static $labelArray = array('id' => '', 'applicant_id' => '', 'lastname' => '', 'firstname' => '', 'othernames' => '', 'gender' => '', 'marital_status' => '', 'dob' => '', 'disabilities' => '', 'phone' => '', 'email' => '', 'contact_address' => '', 'state_of_origin' => '', 'lga' => '', 'nationality' => '', 'passport' => '', 'entry_mode' => '', 'de_type' => '', 'programme_id' => '', 'programme_given' => '', 'session_id' => '', 'jamb_details' => '', 'olevel_details' => '', 'alevel_details' => '', 'nce_nd_hnd' => '', 'institutions_attended' => '', 'referee' => '', 'admission_status' => '', 'is_admitted' => '', 'admitted_level' => '', 'programme_duration' => '', 'step' => '', 'final_submit' => '', 'access_token' => '', 'verification_code' => '', 'user_agent' => '', 'date_created' => '');
	/*associative array of fields that have default value*/
	static $defaultArray = array('is_admitted' => '0', 'admitted_level' => '0', 'programme_duration' => '0', 'final_submit' => '0');
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
	static $documentField = array(); //array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

	static $relation = array('programme' => array('programme_id', 'ID'),
	);
	static $tableAction = array('delete' => 'delete/applicants', 'edit' => 'edit/applicants');

	static $apiSelectClause = ['id', 'applicant_id', 'lastname', 'firstname', 'othernames', 'phone', 'email', 'passport', 'entry_mode', 'programme_id', 'programme_given', 'session_id', 'admission_id', 'admission_status', 'step', 'date_created', 'last_access'];

	function __construct($array = array())
	{
		parent::__construct($array);
	}

	/**
	 * @param mixed $value
	 * @return <missing>|string
	 */
	function getApplicant_idFormField($value = '')
	{
		$fk = null; //change the value of this variable to array('table'=>'applicant','display'=>'applicant_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

		if (is_null($fk)) {
			return $result = "<input type='hidden' value='$value' name='applicant_id' id='applicant_id' class='form-control' />
			";
		}
		if (is_array($fk)) {
			$result = "<div class='form-group'>
		<label for='applicant_id'>Applicant Id</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='applicant_id' id='applicant_id' class='form-control'>
			$option
		</select>";
		}
		$result .= "</div>";
		return $result;

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getLastnameFormField($value = ''): string
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
	function getFirstnameFormField($value = ''): string
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
	function getOthernamesFormField($value = ''): string
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
	function getGenderFormField($value = ''): string
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
	function getMarital_statusFormField($value = ''): string
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
	function getDobFormField($value = ''): string
	{

		return "<div class='form-group'>
	<label for='dob' >Dob</label>
		<input type='text' name='dob' id='dob' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getDisabilitiesFormField($value = ''): string
	{

		return "<div class='form-group'>
	<label for='disabilities' >Disabilities</label>
		<input type='text' name='disabilities' id='disabilities' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getPhoneFormField($value = ''): string
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
	function getEmailFormField($value = ''): string
	{

		return "<div class='form-group'>
	<label for='email' >Email</label>
	<input type='email' name='email' id='email' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getContact_addressFormField($value = ''): string
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
	function getState_of_originFormField($value = ''): string
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
	function getLgaFormField($value = ''): string
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
	function getNationalityFormField($value = ''): string
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
	function getPassportFormField($value = ''): string
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
	function getEntry_modeFormField($value = ''): string
	{

		return "<div class='form-group'>
	<label for='entry_mode' >Entry Mode</label>
		<input type='text' name='entry_mode' id='entry_mode' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getDe_typeFormField($value = ''): string
	{

		return "<div class='form-group'>
	<label for='de_type' >De Type</label>
		<input type='text' name='de_type' id='de_type' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return <missing>|string
	 */
	function getProgramme_idFormField($value = '')
	{
		$fk = null; //change the value of this variable to array('table'=>'programme','display'=>'programme_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

		if (is_null($fk)) {
			return $result = "<input type='hidden' value='$value' name='programme_id' id='programme_id' class='form-control' />
			";
		}
		if (is_array($fk)) {
			$result = "<div class='form-group'>
		<label for='programme_id'>Programme Id</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='programme_id' id='programme_id' class='form-control'>
			$option
		</select>";
		}
		$result .= "</div>";
		return $result;

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getProgramme_givenFormField($value = ''): string
	{

		return "<div class='form-group'>
	<label for='programme_given' >Programme Given</label><input type='number' name='programme_given' id='programme_given' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return <missing>|string
	 */
	function getSession_idFormField($value = '')
	{
		$fk = null; //change the value of this variable to array('table'=>'session','display'=>'session_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

		if (is_null($fk)) {
			return $result = "<input type='hidden' value='$value' name='session_id' id='session_id' class='form-control' />
			";
		}
		if (is_array($fk)) {
			$result = "<div class='form-group'>
		<label for='session_id'>Session Id</label>";
			$option = $this->loadOption($fk, $value);
			//load the value from the given table given the name of the table to load and the display field
			$result .= "<select name='session_id' id='session_id' class='form-control'>
			$option
		</select>";
		}
		$result .= "</div>";
		return $result;

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getJamb_detailsFormField($value = ''): string
	{

		return "<div class='form-group'>
	<label for='jamb_details' >Jamb Details</label>
<textarea id='jamb_details' name='jamb_details' class='form-control' required>$value</textarea>
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getOlevel_detailsFormField($value = ''): string
	{

		return "<div class='form-group'>
	<label for='olevel_details' >Olevel Details</label>
<textarea id='olevel_details' name='olevel_details' class='form-control' required>$value</textarea>
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getAlevel_detailsFormField($value = ''): string
	{

		return "<div class='form-group'>
	<label for='alevel_details' >Alevel Details</label>
<textarea id='alevel_details' name='alevel_details' class='form-control' required>$value</textarea>
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getNce_nd_hndFormField($value = ''): string
	{

		return "<div class='form-group'>
	<label for='nce_nd_hnd' >Nce Nd Hnd</label>
<textarea id='nce_nd_hnd' name='nce_nd_hnd' class='form-control' required>$value</textarea>
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getInstitutions_attendedFormField($value = ''): string
	{

		return "<div class='form-group'>
	<label for='institutions_attended' >Institutions Attended</label>
<textarea id='institutions_attended' name='institutions_attended' class='form-control' required>$value</textarea>
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getRefereeFormField($value = ''): string
	{

		return "<div class='form-group'>
	<label for='referee' >Referee</label>
<textarea id='referee' name='referee' class='form-control' required>$value</textarea>
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getAdmission_statusFormField($value = ''): string
	{

		return "<div class='form-group'>
	<label for='admission_status' >Admission Status</label>
		<input type='text' name='admission_status' id='admission_status' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getIs_admittedFormField($value = ''): string
	{

		return "<div class='form-group'>
	<label class='form-checkbox'>Is Admitted</label>
	<select class='form-control' id='is_admitted' name='is_admitted' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getAdmitted_levelFormField($value = ''): string
	{

		return "<div class='form-group'>
	<label class='form-checkbox'>Admitted Level</label>
	<select class='form-control' id='admitted_level' name='admitted_level' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getProgramme_durationFormField($value = ''): string
	{

		return "<div class='form-group'>
	<label class='form-checkbox'>Programme Duration</label>
	<select class='form-control' id='programme_duration' name='programme_duration' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getStepFormField($value = ''): string
	{

		return "<div class='form-group'>
	<label for='step' >Step</label>
		<input type='text' name='step' id='step' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getFinal_submitFormField($value = ''): string
	{

		return "<div class='form-group'>
	<label class='form-checkbox'>Final Submit</label>
	<select class='form-control' id='final_submit' name='final_submit' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getAccess_tokenFormField($value = ''): string
	{

		return "<div class='form-group'>
	<label for='access_token' >Access Token</label>
		<input type='text' name='access_token' id='access_token' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getVerification_codeFormField($value = ''): string
	{

		return "<div class='form-group'>
	<label for='verification_code' >Verification Code</label>
		<input type='text' name='verification_code' id='verification_code' value='$value' class='form-control' required />
</div> ";

	}

	/**
	 * @param mixed $value
	 * @return string
	 */
	function getUser_agentFormField($value = ''): string
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
	function getDate_createdFormField($value = ''): string
	{
		return " ";
	}

	/**
	 * @return null|Programme
	 */
	protected function getProgramme()
	{
		$query = 'SELECT * FROM programme WHERE id=?';
		if (!isset($this->array['programme_id'])) {
			return null;
		}
		$id = $this->array['programme_id'];
		$result = $this->db->query($query, array($id));
		$result = $result->getResultArray();
		if (empty($result)) {
			return null;
		}
		return new \App\Entities\Programme($result[0]);
	}

	/**
	 * @return array|array[]
     */
	protected function getApplicantTransaction()
	{
		$query = 'SELECT * FROM applicant_transaction WHERE applicant_id=?';
		if (!isset($this->array['id'])) {
			return null;
		}
		$id = $this->array['id'];
		$result = $this->db->query($query, array($id));
		$result = $result->getResultArray();
		if (empty($result)) {
			return null;
		}
		return $result;
	}

	protected function getAcademicRecord()
	{
		$query = "SELECT a.id,b.id as student_id,a.programme_id,a.entry_mode,a.teaching_subject,b.is_verified FROM academic_record a 
		join students b on b.id = a.student_id WHERE application_number=? order by id desc limit 1";
		$result = $this->query($query, [$this->applicant_id]);
		if (!$result) {
			return null;
		}
		return $result[0];
	}

	/**
	 * @param mixed $id
	 * @param mixed $email
	 * @return bool|<missing>
	 */
	public function getApplicantsByIdEmail($id, $email)
	{
		$query = "SELECT * from applicants where applicant_id = ? or email = ? ";
		$result = $this->query($query, [$id, $email]);
		if (!$result) {
			return false;
		}
		return $result[0];
	}

	/**
	 * @param mixed $id
	 * @return bool|<missing>
	 */
	public function applicantPayment($id)
	{
		$query = "SELECT * FROM applicant_transaction WHERE applicant_id=? and (payment_status = '00' or payment_status = '01')";
		$result = $this->query($query, array($id));
		if (!$result) {
			return false;
		}
		return $result;
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
		$temp = getFilterQueryFromDict($filterList, 'a');
		$temp1 = getFilterQueryFromDict($filterList, 'e');
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterQuery1 = buildCustomWhereString($temp1[0], $queryString, false);
		$filterValues = $temp[1];

		$limit = null;
		if (request()->getGet('start') && $len) {
			$start = $this->db->escapeString($start);
			$len = $this->db->escapeString($len);
			$limit = " limit $start, $len";
		}

		if (!$filterValues) {
			$filterValues = [];
		}
		$filterData = array_merge($filterValues, $filterValues);

		$query = "SELECT " . buildApiClause(static::$apiSelectClause, 'a') . " ,b.name as programme_interest,
			c.name as programme_offered,d.name as admission_type,'applicant' as applicant_type from applicants a 
			left join programme b on b.id = a.programme_id left join programme c on c.id = a.programme_given left join 
			admission d on d.id = a.admission_id $filterQuery
			UNION
			SELECT " . buildApiClause(static::$apiSelectClause, 'e', false) . " ,f.name as programme_interest,
			g.name as programme_offered,h.name as admission_type,'applicant_putme' as applicant_type from applicant_post_utme e 
			left join programme f on f.id = e.programme_id left join programme g on g.id = e.programme_given left join 
			admission h on h.id = e.admission_id $filterQuery1
		";

		if (isset($_GET['sortBy']) && $orderBy) {
			$query .= " order by $orderBy {$limit}";
		} else {
			$query .= " order by date_created desc {$limit}";
		}
		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterData);
		$res = $res->getResultArray();
		$res2 = $this->db->query($query2);
		$res2 = $res2->getResultArray();
		$res = $this->processList($res);
		return [$res, $res2];
	}

	private function processList($items): array
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
		if (isset($item['id'])) {
			$item['id'] = $item['id'] . '-' . ($item['applicant_type'] === 'applicant_putme' ?
					CommonSlug::APPLICANT_PUTME->value : CommonSlug::APPLICANT->value);
		}

		if (isset($item['phone'])) {
			$item['phone'] = decryptData($item['phone']);
		}

		if (isset($item['phone2'])) {
			$item['phone2'] = decryptData($item['phone2']);
		}

		return $item;
	}

	/**
	 * @param mixed $applicant_id
	 * @return bool|Applicants
	 * @throws Exception
	 */
	public function applicationDocument($applicant_id)
	{
		$query = "SELECT applicants.*, applicants.session_id as session, applicant_transaction.payment_id, applicant_transaction.payment_description, applicant_transaction.transaction_ref,
        applicant_transaction.rrr_code, applicant_transaction.payment_status as payment_status_code,
        applicant_transaction.payment_status_description,programme.name as programme, p2.name as programme_name_given,
        sessions.date as session_date, applicant_transaction.amount_paid, applicant_transaction.total_amount,
        applicant_transaction.date_performed as date_initiated,applicant_transaction.date_completed from applicants left join
    	programme on programme.id = applicants.programme_id left join programme p2 on p2.id = applicants.programme_given left join
    	sessions on sessions.id = applicants.session_id left join applicant_transaction on applicant_transaction.applicant_id = applicants.id
    	where applicants.applicant_id = ? and applicants.session_id = applicant_transaction.session";

		$result = $this->query($query, [$applicant_id]);
		if (!$result) {
			return false;
		}
		return new Applicants($result[0]);
	}

	public function getApplicantDetails($applicantNo)
	{
		$query = "SELECT a.id, a.applicant_id,a.lastname,a.firstname,a.othernames,a.phone,a.email,dob,contact_address,a.passport,a.entry_mode,
       		a.programme_id,a.session_id,a.admission_id,a.admission_status,a.step,a.date_created,
       		a.last_access,b.name as programme_interest,c.name as programme_offered,d.name as admission_type,
       		olevel_details,is_admitted,state_of_origin,lga,nationality,post_utme_score,gender from applicant_post_utme a
       		left join programme b on b.id = a.programme_id left join programme c on c.id = a.programme_given left join
       		admission d on d.id = a.admission_id  where  a.applicant_id = ?";
		return $this->query($query, [$applicantNo]);
	}

}
