<?php

/**
 * This is the Model that manages Api specific request
 */

namespace App\Models\Api;

use App\Enums\CommonEnum as CommonSlug;
use App\Enums\PaymentFeeDescriptionEnum as PaymentFeeDescription;
use App\Models\Mailer;
use App\Models\WebSessionManager;
use App\Traits\AccountTrait;
use App\Traits\ApiModelTrait;
use App\Traits\AuthTrait;
use App\Traits\CommonTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Model;
use Exception;


class WebApiModel extends Model
{
    use AuthTrait, ApiModelTrait, AccountTrait;

    protected ?RequestInterface $request;

    protected ?ResponseInterface $response;

    private $mailer;

    private WebSessionManager $webSessionManager;

    protected $db;

    public function __construct(RequestInterface $request = null, ResponseInterface $response = null)
    {
        parent::__construct();
        $this->db = db_connect();
        $this->request = $request;
        $this->response = $response;
        $this->webSessionManager = new WebSessionManager;
        $this->mailer = new Mailer;
    }

    public function profile()
    {
       return $this->accountProfile();
    }

    public function activity_logs()
    {
        return $this->accountActivityLogs();
    }

    public function auth_logs()
    {
        return $this->accountAuthLoginLogs();
    }

    /**
     * This is would get the session tied to transaction for filter
     * @return array
     */
    public function transaction_session()
    {
        loadClass($this->load, 'sessions');
        $result = $this->sessions->getTransactionSession();
        return sendAPiResponse(true, 'success', $result);
    }

    /**
     * @return bool|<missing>
     */
    private function currentSession()
    {
        // return $this->adminModel->currentSession();
        $query = "select settings_value as id from settings where settings_name = 'active_admission_session'";
        $result = $this->db->query($query);
        $result = $result->result_array();
        if (!$result) {
            return false;
        }
        return $result[0]['id'];
    }

    /**
     * @param mixed $value
     * @return void
     */
    public function student_photos($value = '')
    {
        $result = $this->adminModel->getStudentPhotos();
        return sendAPiResponse(true, 'success', $result);
    }

    /**
     * @param mixed $value
     * @return void
     */
    public function photo_download($value = '')
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            http_response_code(405);
            return sendAPiResponse(false, 'Bad request');
        }
        $result = $this->adminModel->downloadPhotos();
        if (!$result) {
            return sendAPiResponse(false, 'Filter result is empty', []);
        }

        $payload = [
            'export_link' => $result,
        ];
        return sendAPiResponse(true, 'Please do click/copy the link for download', $payload);
    }

    /**
     * This create a single admissino for applicant
     * @return void
     */
    public function single_admission()
    {
        $programme = $this->input->post('programme', true);
        $adminStatus = $this->input->post('adminStatus', true);
        $entryMode = trim($this->input->post('entryMode', true));
        $applicantID = $this->input->post('applicant_id', true);
        $teachingSubject = $this->input->post('teaching_subject', true);
        $entity = $this->input->post('applicant_type', true) ?: 'applicants';
        $applicant = fetchSingle($this, $entity, 'applicant_id', $applicantID);
        $applicantLevel = null;
        $applicantDuration = null;
        $currentUser = $this->webSessionManager->currentAPIUser();

        $this->form_validation->set_rules('programme', 'programme', 'trim|required');
        $this->form_validation->set_rules('adminStatus', 'admission status', 'trim|required');
        $this->form_validation->set_rules('entryMode', 'entry_mode', 'trim|required');
        $this->form_validation->set_rules('teaching_subject', 'teaching subject', 'trim');
        $this->form_validation->set_rules('applicant_id', 'applicant', 'trim|required', [
            'required' => 'Please provide a valid applicant',
        ]);
        $this->form_validation->set_rules('applicant_type', 'applicant type', 'trim|required|in_list[applicant_post_utme,applicants]');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                return sendAPiResponse(false, $error);
            }
        }

        if ($entryMode == CommonSlug::O_LEVEL || $entryMode == CommonSlug::O_LEVEL_PUTME) {
            $applicantLevel = "1";
            $applicantDuration = "5";
        } else if ($entryMode == CommonSlug::DIRECT_ENTRY) {
            $applicantLevel = "2";
            $applicantDuration = "4";
        } else if ($entryMode == CommonSlug::FAST_TRACK) {
            $applicantLevel = "2";
            $applicantDuration = "4";
        }

        if (!$applicant) {
            return sendAPiResponse(false, "Applicant record not found");
        }
        if ($applicant['is_admitted'] == 1) {
            return sendAPiResponse(false, "Record has been previously moved");
        }

        if ($entity === 'applicants') {
            loadClass($this->load, 'applicants');
            $result = $this->applicants->applicantPayment($applicant['id']);
            if (!$result) {
                return sendAPiResponse(false, "Applicant $applicantID has not returned a successful transaction for form");
            }
        }

        if ($entity === 'applicant_post_utme') {
            if (empty($applicant['olevel_details'])) {
                return sendApiResponse(false, "A minimum of one(1) O'level sitting is required");
            }
        }

        $this->db->trans_begin();
        $isMoved = getSingleRecordExclude($this, 'academic_record', "matric_number='$applicantID' OR application_number = '$applicantID' ");
        if ($isMoved) {
            return sendAPiResponse(false, "Record has been previously moved");
        }

        $details = [
            'programme_given' => $programme,
            'admission_status' => $adminStatus,
            'is_admitted' => ($adminStatus == 'Admitted') ? 1 : 0,
            'admitted_level' => $applicantLevel,
            'programme_duration' => $applicantDuration,
        ];
        if (!update_record($this, $entity, 'applicant_id', $applicantID, $details)) {
            $this->db->trans_rollback();
            return sendAPiResponse(false, "Something went wrong, please try again later");
        }
        logAction($this, 'changed_applicants_admission_status', $currentUser->user_login);
        $salt = $this->config->item('salt');
        $pass = hash('SHA256', $salt . trim(strtolower($applicant['lastname'])));
        $password = encode_password(removeNonCharacter(strtolower($applicant['lastname'])));
        $message = "Update was successful";
        if ($adminStatus == 'Admitted') {
            $this->moveApplicantBiodata($applicantID, $pass, $password, $entity);
            $student_id = $this->db->insert_id();
            $this->moveApplicantAcademicRecord($student_id, $applicantID, $programme, $applicantLevel, $applicantDuration, $teachingSubject, $entryMode, $entity);
            $this->moveApplicantMedicalData($student_id, $applicantID, $entity);
            logAction($this, 'applicant_admit', $currentUser->user_login);
            $this->movePassport($applicant['passport'], $student_id);
            $this->db->trans_commit();
            $message = "You have successfully admitted the applicant";

            $fullname = strtoupper($applicant['lastname']) . ", " . ucwords(strtolower($applicant['firstname'])) . "" . ucwords(strtolower($applicant['othernames']));
            $programme = fetchSingleField($this->db, 'programme', 'id', $programme, 'name');
//			$variables = array(
//				'firstname' => $applicant['lastname'],
//				'fullname' => $fullname,
//				'institution_name' => get_setting('institution_name'),
//				'programme' => $programme,
//			);

            // send email
            // $this->mailer->send_new_mail('admission-notice', $applicant['email'], $variables);
        }
        return sendAPiResponse(true, $message, ['programme' => $programme]);
    }

    /**
     * This is to handle bulk admission
     * @return void|null
     */
    public function upload_admission()
    {
        try {
            $maxSize = 1000000;
            $allowedType = 'csv';
            $uploadName = 'admissionList';
            $content = getUploadedFileContent($uploadName, $maxSize, $allowedType);
            $content = trim($content);
            $lines = explode("\n", $content);
            // skip the heading
            $reports = [];
            for ($i = 1; $i < count($lines); $i++) {
                $item = trim($lines[$i]);
                $list = explode(',', $item);
                $report = $this->createAdmission($list);
                if (is_string($report)) {
                    $temp = ['index' => $i, 'message' => $report];
                    $reports[] = $temp;
                }
            }
            return sendAPiResponse(true, 'Admission successfully uploaded', $reports);
        } catch (Exception $e) {
            return sendAPiResponse(false, $e->getMessage());
        }

    }

    /**
     * @param mixed $item
     * @return string|bool
     */
    private function createAdmission($item)
    {
        // first load the applicant in question
        if (count($item) < 5) {
            return "Incomplete row";
        }
        $applicantID = removeNonCharacter($item[0]);
        $programme = removeNonCharacter($item[1]);
        $entry = removeNonCharacter($item[2]);
        $level = removeNonCharacter($item[3]);
        $duration = removeNonCharacter($item[4]);
        $teachingSubject = removeNonCharacter($item[5]);
        if (!($applicantID && $programme && $entry && $level && $duration)) {
            return "Incomplete data for $applicantID";
        }
        $entity = 'applicants';
        $applicant = fetchSingle($this, $entity, 'applicant_id', $applicantID);
        if (!$applicant) {
            $entity = 'applicant_post_utme';
            $applicant = fetchSingle($this, $entity, 'applicant_id', $applicantID);
            if (!$applicant) {
                return "Applicant with id $applicantID does not exist";
            }
        }

        // check if the applicant has been admitted
        if ($applicant['is_admitted'] == 1) {
            return "Applicant $applicantID is already admitted";
        }
        // treat every operation here as atomic
        $programme_id = getIDByName($this, 'programme', 'name', trim($programme));
        $level_id = getLevelID($level);
        if (!($programme_id && $level_id)) {
            return "Cannot find programme or level specified for $applicantID";
        }
        loadClass($this->load, $entity);
        $result = $this->$entity->applicantPayment($applicant['id']);
        if (!$result) {
            return "Applicant $applicantID has not returned a successful transaction for form";
        }

        if ($entry == CommonSlug::O_LEVEL || $entry == CommonSlug::O_LEVEL_PUTME) {
            $duration = "5";
            $level_id = '1';
        } else if ($entry == CommonSlug::DIRECT_ENTRY) {
            $duration = "4";
            $level_id = '2';
        } else if ($entry == CommonSlug::FAST_TRACK) {
            $duration = "4";
            $level_id = '2';
        }

        $currentUser = $this->webSessionManager->currentAPIUser();
        $this->db->trans_begin();

        $isMoved = getSingleRecordExclude($this, 'academic_record', "matric_number='$applicantID' OR application_number = '$applicantID' ");
        if ($isMoved) {
            return "Record has been previously moved";
        }

        $details = [
            'programme_given' => $programme_id,
            'admission_status' => "Admitted",
            'is_admitted' => 1,
            'admitted_level' => $level_id,
            'programme_duration' => $duration,
        ];
        if (!update_record($this, $entity, 'applicant_id', $applicantID, $details)) {
            return "Cannot update record for $applicantID";
        }
        $students = fetchSingle($this, 'students', 'user_login', $applicant['email']);
        if ($students) {
            return "Applicant had already been moved";
        }
        logAction($this, 'changed_applicants_admission_status', $currentUser->user_login);
        $salt = $this->config->item('salt');
        $pass = hash('SHA256', $salt . trim(strtolower($applicant['lastname'])));
        $password = encode_password(removeNonCharacter(strtolower($applicant['lastname'])));
        $this->moveApplicantBiodata($applicantID, $pass, $password, $entity);
        $student_id = $this->db->insert_id();

        $this->moveApplicantAcademicRecord($student_id, $applicantID, $programme_id, $level_id, $duration, $teachingSubject, $entry, $entity);
        $this->moveApplicantMedicalData($student_id, $applicantID, $entity);
        logAction($this, 'applicant_admit', $currentUser->user_login);
        $this->db->trans_complete();
        $this->movePassport($applicant['passport'], $student_id);

        // $this->sendEmail($applicant);
        return true;
    }

    /**
     * This move applicant passport to student passport directory
     * @param mixed $passport
     * @param mixed $studentID
     */
    private function movePassport($passport, $studentID)
    {
        if (ENVIRONMENT !== 'production') {
            return true;
        }

        return movePassport($this, $studentID, $passport);
    }

    /**
     * This is for test purpose to confirm if passport is being copied truly
     * @test - CAUTION !!!  - Not to be used
     * @return void
     */
    public function move_passport()
    {
        $passport = $this->input->post('passport');
        $studentID = $this->input->post('student_id');
        $passport = basename($passport);

        $imagePath = returnFormalDirectory('applicants') . $passport;
        $path = FCPATH . $this->config->item('student_passport_path');
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $newPath = $path . $passport;

        if (file_exists($imagePath)) {
            if (copy($imagePath, $newPath)) {
                $details = ['passport' => $passport];
                $updated = update_record($this, 'students', 'id', $studentID, $details); // fail gracefully
                displayJson(true, 'passport successfully copied');
                return;
            }
        }
        return sendAPiResponse(false, 'Passport was not copied');
    }

    /**
     * This is to move applicant biodata to student
     * @param mixed $applicantID
     * @param mixed $pass
     * @param mixed $newPass
     */
    private function moveApplicantBiodata($applicantID, $pass, $newPass = null, $entity = 'applicants')
    {
        $query = "INSERT into students (
			firstname,othernames,lastname,gender,DoB,phone,marital_status,religion,contact_address,postal_address,profession,
			state_of_origin,lga,nationality,passport,referee,alternative_email,user_login,user_pass,active,is_verified,
            date_created,password)
		SELECT firstname,othernames,lastname,gender, dob,phone,marital_status,'',contact_address,'','',state_of_origin,lga,
			nationality,passport,referee,lower(email),lower(email),'$pass',1,0,now(),'$newPass' from $entity
			where applicant_id=?";
        return $this->db->query($query, [$applicantID]);
    }

    /**
     * This is to move applicant into academic record
     * @param mixed $student_id
     * @param mixed $applicant_id
     * @param mixed $programme_id
     * @param mixed $admittedLevel
     * @param mixed $duration
     * @param mixed $teachingSubject
     */
    private function moveApplicantAcademicRecord($student_id, $applicant_id, $programme_id, $admittedLevel, $duration,
                                                 $teachingSubject = null, $entryMode = null, $entity = 'applicants')
    {
        $minDuration = $duration * 12;
        $maxDuration = $duration * 12;
        $entryMode = $this->db->escapeString($entryMode);
        $query = "INSERT INTO academic_record(
			student_id,jamb_details,olevel_details,alevel_details,nce_nd_hnd,institutions_attended,programme_id,matric_number,
            has_matric_number,has_institution_email,programme_duration,min_programme_duration,max_programme_duration,
            year_of_entry,entry_mode,mode_of_study,interactive_center,exam_center,teaching_subject,level_of_admission,
            session_of_admission,current_level,current_session,application_number,applicant_type)
		SELECT '$student_id',jamb_details,olevel_details,alevel_details,nce_nd_hnd,institutions_attended,'$programme_id',applicant_id,0,0,
		'$duration','$minDuration','$maxDuration',session_id,'$entryMode','','','','$teachingSubject','$admittedLevel',session_id,
		'$admittedLevel',session_id,applicant_id,'$entity' from $entity where applicant_id=?";
        return $this->db->query($query, [$applicant_id]);
    }

    /**
     * This move applicant into medical record
     * @param mixed $student_id
     * @param mixed $applicant_id
     */
    private function moveApplicantMedicalData($student_id, $applicant_id, $entity = 'applicants')
    {
        $query = "INSERT INTO medical_record (student_id,blood_group,genotype,height,weight,allergy,disabilities,others)
			select $student_id,'','','','','',disabilities,'' from $entity where applicant_id=?";
        return $this->db->query($query, [$applicant_id]);
    }

    /**
     * @param mixed $value
     * @return void
     */
    public function photo_download_deprecated($value = '')
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            http_response_code(405);
            return sendAPiResponse(false, 'bad request');
        }
        $this->load->model('adminModel');
        $result = $this->adminModel->downloadPhotos();
        if ($result) {
            return sendAPiResponse(true, 'success', $result);
        } else {
            return sendAPiResponse(false, 'Filter result is empty', []);
        }

    }

    /**
     * @param mixed $name
     * @param mixed $type
     * @param mixed $move
     * @param mixed $directory
     * @return bool|array<int,mixed>|string
     */
    private function getUploadContent($name, $type, $move = false, $directory = false)
    {
        $extension = explode('.', $_FILES[$name]['name']);
        $extension = $extension[count($extension) - 1];
        if (!(array_key_exists($name, $_FILES) && $extension == $type && !$_FILES[$name]['error'])) {
            return false;
        }
        if ($move) {
            if (!is_dir($directory)) {
                mkdir($directory);
            }
            $path = $directory . date('Y_m_d H_i_s') . $_FILES[$name]['name'];
            if (!move_uploaded_file($_FILES[$name]['tmp_name'], $path)) {
                return false;
            }
        }
        if ($move) {
            return [file_get_contents($path), $path];
        }
        return file_get_contents($_FILES[$name]['tmp_name']);

    }

    /**
     * @param mixed $array
     * @param mixed $message
     * @return array<int,array<int,mixed>>
     */
    private function transformResult($array, &$message = []): array
    {

        $result = [];
        $count = 1;
        foreach ($array as $item) {
            $count++;
            $reg_num = $item[0];
            if (!$reg_num) {
                $message[] = " invalid reg_num  on row $count ";
                continue;
            }
            $student_id = fetchSingleField($this->db, 'academic_record', 'matric_number', $reg_num, 'student_id');
            if (!$student_id) {
                $message[] = " student '$reg_num' does not exist on row $count ";
                continue;
            }
            $course_id = fetchSingleField($this->db, 'courses', 'code', $item[1]);
            if (!$course_id) {
                $message[] = " invalid course code '{$item[1]}'  on row $count ";
                continue;
            }
            $session_id = fetchSingleField($this->db, 'sessions', 'date', $item[2]);
            if (!$session_id) {
                $message[] = " invalid session date '{$item[2]}'  on row $count ";
                continue;
            }
            $caScore = $item[3];
            $examScore = $item[4];
            $totalScore = $item[5];
            $level = fetchSingleField($this->db, 'levels', 'name', $item[6]);
            if (!$level) {
                $message[] = " invalid level '{$item[6]}'  on row $count ";
                continue;
            }
            // $semester = fetchSingleField($this->db,'semesters','name',$item[7]);
            $semester = $item[7];

            if (!($reg_num && $student_id && $course_id && $session_id && $totalScore && $level && $semester)) {
                $message[] = " invalid data on row $count ";
                continue;
            }
            $result[] = [$student_id, $reg_num, $course_id, 'C', $semester, $session_id, $level, $caScore, $examScore, $totalScore, 0, date('Y-m-d h:i:s'), date('Y-m-d h:i:s')];

        }
        return $result;
    }

    /**
     * @param mixed $array
     * @param mixed $message
     * @return array<int,array<int,mixed>>
     */
    private function transformTetiaryResult($array, &$message = []): array
    {

        $result = [];
        $count = 1;
        foreach ($array as $item) {
            $count++;
            $matric_number = $item[0];
            if (!$matric_number) {
                $message[] = " invalid matric_number  on row $count ";
                continue;
            }
            $student_id = fetchSingleField($this->db, 'academic_record', 'matric_number', $matric_number, 'student_id');
            if (!$student_id) {
                $message[] = " student does not exist on row $count ";
                continue;
            }
            $course_id = fetchSingleField($this->db, 'courses', 'code', $item[1]);
            if (!$course_id) {
                $message[] = " invalid course code  on row $count ";
                continue;
            }
            $session_id = fetchSingleField($this->db, 'sessions', 'date', $item[2]);
            if (!$session_id) {
                $message[] = " invalid session date  on row $count ";
                continue;
            }
            $caScore = $item[3];
            $examScore = $item[4];
            $totalScore = $item[5];

            if (!($matric_number && $student_id && $course_id && $session_id && $totalScore)) {
                $message[] = " invalid data on row $count ";
                continue;
            }
            $result[] = [$student_id, $course_id, $session_id, $caScore, $examScore, $totalScore, date('Y-m-d h:i:s'), date('Y-m-d h:i:s')];

        }
        return $result;
    }

    /**
     * @param mixed $type
     * @return string|bool
     */
    private function getUploadPermission($type)
    {
        switch ($type) {
            case "secondary":
            case "tertiary":
            case 'primary':
                return "exam_manager";
            default:
                return false;
        }
    }

    /**
     * @param mixed $type
     * @return void
     */
    public function upload_result($type)
    {
        $type = $type[0];
        $permission = $this->getUploadPermission($type);
        $expected = $type == 'tertiary' ?
            ['matric_number', 'course_code', 'session', 'ca_score', 'exam_score', 'total_score'] :
            ['reg_number', 'subject_code', 'session', 'ca_score', 'exam_score', 'total_score', 'class', 'term', 'remark'];
        checkPermission($this, $permission);
        $uploadName = 'upload-content';
        $content = $this->getUploadContent($uploadName, 'csv', true, 'uploads/course_uploads/');
        $path = @$content[1];
        $content = @$content[0];
        if (!$content) {
            displayJson(false, 'invalid data, please try again');
            return;
        }
        $array = stringToCsv($content);
        $header = array_shift($array);
        if ($header != $expected) {
            displayJson(false, 'invalid data, please try again', ['invalid header']);
            return;
        }
        $data = $type == 'tertiary' ? $this->transformTetiaryResult($array, $message) : $this->transformResult($array, $message);
        if (!$data) {
            displayJson(false, 'No data to insert', $message);
            return;
        }
        $this->load->model('Crud');
        $multiple = $this->Crud->buildmultipleInsertValue($data);
        $query = $type == 'tertiary' ?
            "INSERT IGNORE into course_enrollment(student_id,course_id,session_id, ca_score,exam_score,total_score, date_last_update,date_created) values $multiple" :

            "INSERT IGNORE into course_enrollment(student_id,reg_num,course_id,course_status,semester,session_id,student_level, ca_score,exam_score,total_score,is_approved, date_last_update,date_created) values $multiple";
        $result = $this->db->query($query);
        if (!$result) {
            displayJson(false, 'An error occured while uploading please try again');
            return;
        }
        $inserted = $this->db->affected_rows();
        $dbMessage = $this->db->conn_id->info;
        logAction($this, 'score_upload');
        $this->load->model('Messaging');
        $this->Messaging->sendUploadEmailNotification($path);
        displayJson(true, 'success', ['counts' => $inserted, 'dbinfo' => $dbMessage, 'message' => $message]);
    }

    /**
     * @param mixed $user
     */
    private function getUsableSidebar($user)
    {
        loadClass($this->load, 'roles');
        //filter the sidebar content based on the user role
        //for now just return everything
        return $this->roles->loadSidebarContent();
    }

    /**
     * @param mixed $param
     * @return void
     */
    public function entity_config($param)
    {
        $model = @$param[0];
        if (!$model) {
            http_response_code(405);
            displayJson(false, 'bad request');
            return;
        }
        //first check that thentitiy is presnet
        echo "$model";
        exit;
        $entities = listEntities($this->db);
        if (!in_array($model, $entities)) {
            http_response_code(405);
            displayJson(false, 'bad request');
            return;
        }
        //load the object class to get all the necessary parameters for the structure
        echo "$model";
        exit;
        $this->load->model('formConfig');
        $typedArray = $model::$typeArray;
        $typedArray = $model::$typeArray;
    }

    /**
     * @param mixed $description
     * @param mixed $transaction
     * @return bool|array
     */
    private function hasFee($description, $transaction)
    {
        loadClass($this->load, 'payment');
        $query = "select * from payment where id=?";
        $result = $this->db->query($query, [$transaction['real_payment_id']]);
        $payment = $result->result_array();
        // $payment = $this->payment->getWhere(['id'=>$transaction['real_payment_id']]);
        if (!$payment || !$payment[0]['fee_breakdown']) {
            return false;
        }
        $breakdown = $payment[0]['fee_breakdown'];
        $list = json_decode($breakdown);
        return in_array($description, $list);
    }

    /**
     * This is to verify the student for their admission
     * @return void
     */
    public function student_verification()
    {
        $remarks = $this->input->post('remark', true) ?: '';
        $verificationStatus = $this->input->post('verificationStatus', true);
        $studentID = $this->input->post('studentID', true);
        $currentUser = $this->webSessionManager->currentAPIUser();

        $this->form_validation->set_rules('verificationStatus', 'verification status', 'trim|required|in_list[1,0]', ['in_list' => 'Please provide a valid verification status value']);
        $this->form_validation->set_rules('studentID', 'student', 'trim|required', ['required' => 'Please choose a student']);
        ($verificationStatus == '0') ? $this->form_validation->set_rules('remark', 'remark', 'trim|required') : null;

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                return sendAPiResponse(false, $error);
            }
        }

        loadClass($this->load, 'students');
        $this->students->id = $studentID;
        if (!$this->students->load()) {
            return sendAPiResponse(false, 'Invalid student info');
        }
        $record = $this->students;
        if ($record->is_verified == 1) {
            return sendAPiResponse(false, 'Student has been previously verified');
        }

        // if (!$record->checkStudentTransactionByCode('ACF')) {
        // 	return sendAPiResponse(false, "The student must pay acceptance fee before verification.");
        // }

        $verifyAttempt = 1;
        $verifiedByParam = [];
        $date = date('Y-m-d H:i:s');
        $param = [
            'user_id' => $currentUser->id,
            'remark' => $remarks,
            'date_verified' => $date,
        ];

        if (json_decode($record->verified_by)) {
            // convert json object to array
            $verifiedByParam = json_decode($record->verified_by, true);
            $verifyAttempt = ($record->verify_attempt + 1);
        }

        $verifiedByParam[] = $param;
        $verifiedByParam = json_encode($verifiedByParam);
        $details = [
            'is_verified' => ($verificationStatus == '1') ? 1 : 0,
            'verified_by' => $verifiedByParam,
            'verify_attempt' => $verifyAttempt,
        ];

        if (!update_record($this, 'students', 'id', $studentID, $details)) {
            return sendAPiResponse(false, "Update not done, something went wrong!");
        }
        logAction($this, 'verified_student_status', $currentUser->user_login);
        $fullname = strtoupper($record->lastname) . ", " . ucwords(strtolower($record->firstname)) . " " . ucwords(strtolower($record->othernames));
        $variables = [
            'firstname' => $record->firstname,
            'lastname' => $record->lastname,
            'fullname' => $fullname,
            'verification_status' => $verificationStatus,
            'verification_remark' => $remarks,
        ];
        $message = "You have successfully verified the student";
        // send email
        // $this->mailer->send_new_mail('student-verification-email', $record->user_login, $variables);
        return sendAPiResponse(true, $message);
    }

    /**
     * This is to verify the student documents result
     * @return void
     */
    public function student_documents_verification()
    {
        $verificationStatus = $this->input->post('verificationStatus', true);
        $studentID = $this->input->post('studentID', true);
        $comment = $this->input->post('comment', true);
        $currentUser = $this->webSessionManager->currentAPIUser();

        $this->form_validation->set_rules('verificationStatus', 'verification status', 'trim|required|in_list[Verified,Not Verified,Pending]');
        $this->form_validation->set_rules('studentID', 'student', 'trim|required', ['required' => 'Please choose a student']);
        $this->form_validation->set_rules('comment', 'comment', 'trim|required');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                displayJson(false, $error);
                return;
            }
        }

        loadClass($this->load, 'students');
        $this->students->id = $studentID;
        if (!$this->students->load()) {
            displayJson(false, 'Invalid student info');
            return;
        }

        $record = $this->students;
        if (!$record->checkStudentTransactionByCode('ACF')) {
            return sendAPiResponse(false, "The student must pay acceptance fee before verification.");
        }
        $date = date('Y-m-d H:i:s');
        $param = ['comment' => $comment, 'date_created' => $date];
        if ($record->document_verification == 'Verified') {
            displayJson(false, 'Student document has been previously verified');
            return;
        }

        if (json_decode($record->verify_comments)) {
            $commentParam = json_decode($record->verify_comments, true); // turn to array
        }

        $commentParam[] = $param;
        $commentParam = json_encode($commentParam);
        $details = [
            'document_verification' => $verificationStatus,
            'verify_comments' => $commentParam,
        ];
        if (!update_record($this, 'students', 'id', $studentID, $details)) {
            displayJson(false, "Something went wrong, please try again later!");
            return;
        }

        logAction($this, 'verified_document_status', $currentUser->user_login);
        $message = "You have successfully updated the document status";
        if ($verificationStatus == 'Verified') {
            $message = "You have successfully verified the student documents";
        }
        $record->updateStudentCardsUsage();
        // send email
        $fullname = strtoupper($record->lastname) . ", " . ucwords(strtolower($record->firstname)) . " " . ucwords(strtolower($record->othernames));
        $variables = [
            'firstname' => $record->firstname,
            'lastname' => $record->lastname,
            'fullname' => $fullname,
            'verification_status' => $verificationStatus,
            'verification_remark' => $comment,
        ];
        // $this->mailer->send_new_mail('student-verification-email', $record->user_login, $variables);
        displayJson(true, $message);
    }

    /**
     * @return void
     */
    public function student_all_verification_documents()
    {
        $studentID = request()->getGet('student_id', true);
        $data = [
            'student' => $studentID,
        ];
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('student', 'student', 'trim|required', ['required' => 'Please choose a student']);

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                displayJson(false, $error);
                return;
            }
        }

        loadClass($this->load, 'students');
        $this->students->id = $studentID;
        if (!$this->students->load()) {
            displayJson(false, 'Invalid student info');
            return;
        }
        $payload = $this->students->getStudentVerificationDocuments();
        $payload = $payload ?: [];
        displayJson(true, 'Document fetched successfully', $payload);
        return;
    }

    /**
     * @return void
     */
    public function student_documents_complete()
    {
        loadClass($this->load, 'student_verification_fee');
        $payload = $this->student_verification_fee->getStudentUploadDocumentComplete(true);
        $payload = $payload ?: [];
        $count = count($payload);
        displayJson(true, "{$count} - Document fetched successfully", $payload);
        return;
    }

    /**
     * This allows to know the total number of students that have completed their document upload
     * alongside the count of their olevel result
     * @return void
     */
    public function student_documents_complete_grouping()
    {
        loadClass($this->load, 'student_verification_fee');
        $payload = $this->student_verification_fee->getStudentUploadDocumentComplete(false, true);
        $payload = $payload ?: [];

        $i = 0;
        $j = 0;
        foreach ($payload as $item) {
            $olevel = json_decode($item['olevel_details'], true);
            if ($olevel) {
                $olevelType = strtolower($olevel[0]['exam']);
                if ($olevelType) {
                    if (strpos($olevelType, 'waec') !== false) {
                        $i++;
                    } else if (strpos($olevelType, 'neco') !== false) {
                        $j++;
                    }
                }

                if (count($olevel) > 1) {
                    $olevelType2 = strtolower($olevel[1]['exam']);
                    if ($olevelType2) {
                        if (strpos($olevelType2, 'waec') !== false) {
                            $i++;
                        } else if (strpos($olevelType2, 'neco') !== false) {
                            $j++;
                        }
                    }
                }

            }
        }

        return sendAPiResponse(true, 'message', [
            'Total WAEC' => $i,
            'Total NECO' => $j
        ]);
    }

    /**
     * @return void
     */
    public function student_assign_cards()
    {
        $studentID = request()->getGet('student_id', true);
        $currentUser = $this->webSessionManager->currentAPIUser();

        $data = [
            'student_id' => $studentID,
        ];
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('student_id', 'student', 'trim|required', ['required' => 'Please choose a student']);

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                displayJson(false, $error);
                return;
            }
        }

        loadClass($this->load, 'students');
        $this->students->id = $studentID;
        if (!$this->students->load()) {
            displayJson(false, 'Invalid student info');
            return;
        }

        $cards = $this->students->getStudentAssignCards();
        displayJson(true, 'success', $cards);
        return;
    }

    public function student_print_cover_timelog()
    {
        $data = fetchSingle($this, 'users_log', 'action_performed', CommonSlug::BULK_PRINT_STUDENT_COVER, " order by date_performed desc limit 20", true);
        $payload = null;
        if ($data) {
            foreach ($data as $item) {
                $payload[] = json_decode($item['new_data'], true);
            }
        }
        return sendAPiResponse(true, 'success', $payload);
    }

    /**
     * This is to update password for logedin user
     * @return void
     */
    public function auth_update_password()
    {
        $this->updateUsersPassword('users_new');
    }

    public function user_update_password()
    {
        $this->form_validation->set_rules('orig_user_id', 'user', 'trim|required', ['required' => 'Please provide a selected user']);

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                displayJson(false, $error);
                return;
            }
        }

        $userID = $this->input->post('orig_user_id', true);
        $this->updateUsersPassword('users_new', $userID, true);
    }

    /**
     * This allow the admin to reset student password according to admin discretion
     * @return void
     */
    public function student_reset_password()
    {
        $password = $this->input->post('password');
        $confirmPassword = $this->input->post('confirm_password');
        $studentID = $this->input->post('student_id');

        // validate input
        $this->form_validation->set_rules('password', 'password', 'trim|required');
        $this->form_validation->set_rules('confirm_password', 'password confirm', 'trim|required|matches[password]');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                displayJson(false, $error);
                return;
            }
        }

        loadClass($this->load, 'students');
        $students = $this->students->getWhere(['id' => $studentID], $c, 0, 1, false);
        if (!$students) {
            displayJson(false, "The student is not valid, please check student details");
            return;
        }
        $new = encode_password($password);
        $currentUser = $this->webSessionManager->currentAPIUser();
        $query = "update students set password = '$new' where id=?";
        if ($this->db->query($query, [$studentID])) {
            logAction($this, 'student_password_reset', $currentUser->user_login);
            displayJson(true, 'You have successfully updated student password');
            return;
        } else {
            displayJson(false, "An error occured, you can't update student password, please try again later");
            return;
        }
    }

    /**
     * This would check the availability of custom users
     * @return void
     */
    public function custom_users_availability()
    {
        $email = $this->input->post('email', true);
        loadClass($this->load, 'users_custom');
        $users = $this->users_custom->getWhere(['email' => $email], $c, 0, null, false);
        if (!$users) {
            displayJson(false, 'User is unavailable');
            return;
        }
        $users = $users[0];
        $users = $users->toArray();
        displayJson(true, 'User is available', $users);
        return;
    }

    /**
     * This would get invoice category
     * @return void
     */
    public function invoice_categories()
    {
        loadClass($this->load, 'fee_description');
        $categories = $this->fee_description->getWhereNonObject(['category' => 4], $c, 0, null, false);
        if (!$categories) {
            displayJson(false, 'Not available');
            return;
        }
        displayJson(true, 'success', $categories);
        return;
    }

    /**
     * THis would create custom users for custom transaction
     * @return bool|<missing>
     */
    private function createCustomUsers()
    {
        $name = $this->input->post('name', true);
        $email = $this->input->post('email', true);
        $phone_number = $this->input->post('phone_number', true);
        $address = $this->input->post('address', true);
        $contact = $this->input->post('contact_person', true);

        $param = [
            'name' => $name,
            'email' => $email,
            'phone_number' => encryptData($this, $phone_number),
            'address' => $address,
            'contact_person' => $contact,
        ];

        $this->users_custom->setArray($param);
        if (!$this->users_custom->insert($this->db)) {
            return false;
        }
        return $email;
    }

    /**
     * This would create user invoice and send to the user email
     * @return void
     */
    public function create_users_invoice()
    {
        if (get_setting('disable_all_non_student_fees') == 1) {
            $message = "Payment has not been opened yet. Please try again later.";
            return sendAPiResponse(false, $message);
        }

        $paymentDesc = $this->input->post('payment_description', true);
        $amount = $this->input->post('amount', true);
        $email = $this->input->post('email', true);
        $category = $this->input->post('category', true);
        $startDate = $this->input->post('start_date', true);
        $endDate = $this->input->post('end_date', true);

        // validate input
        $this->form_validation->set_rules('payment_description', 'payment description', 'trim|required');
        $this->form_validation->set_rules('amount', 'amount', 'trim|required');
        $this->form_validation->set_rules('name', 'name', 'trim|required');
        $this->form_validation->set_rules('email', 'email', 'trim|required|valid_email');
        $this->form_validation->set_rules('phone_number', 'phone number', 'trim|required');
        $this->form_validation->set_rules('category', 'category', 'trim|required');
        $this->form_validation->set_rules('start_date', 'start date', 'trim|required');
        $this->form_validation->set_rules('end_date', 'end date', 'trim|required');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                return sendAPiResponse(false, $error);
            }
        }
        $amount = str_replace(",", '', $amount);
        loadClass($this->load, 'users_custom');
        loadClass($this->load, 'payment');

        $users = $this->users_custom->getWhere(['email' => $email], $c, 0, null, false);
        if (!$users) {
            $users = $this->createCustomUsers();
            $users = $this->users_custom->getWhere(['email' => $users], $c, 0, null, false);
        }
        $users = $users[0];
        $serviceCharge = 505;
        $total = $amount + $serviceCharge;
        $param = [
            'requery' => false,
            'description' => $paymentDesc,
            'amount' => $amount,
            'serviceCharge' => $serviceCharge,
            'total' => $total,
            'payment_id' => $category,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'transaction_type' => 'non-student',
        ];
        $payment_channel = get_setting('payment_gateway');
        $initDetails = $this->payment->customInitPayment($users, $payment_channel, $param);
        // validate the remita response
        if (isset($initDetails['status']) && !$initDetails['status']) {
            return sendAPiResponse(false, $initDetails['message']);
        }
        $contacts = $users->contact_person;
        $contact = null;
        $contactPhone = null;
        if ($contacts) {
            [$contact, $contactPhone] = explode(":", $contacts);
        }

        $param = [
            'payment_description' => $paymentDesc,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_amount' => $total,
            'contact' => $contact,
            'contactPhone' => $contactPhone,
            'rrr' => $initDetails['rrr'],
            'date_performed' => $initDetails['date_performed'],
        ];
        $this->sendTransactionInvoice($users, $param);
    }

    /**
     * This is to print user transaction invoice
     * @return void
     */
    public function print_users_invoice()
    {
        $this->resend_users_invoice(true);
    }

    /**
     * This is to re-send user transaction invoice
     * @param boolean $returnHtml [description]
     * @return void
     */
    public function resend_users_invoice(bool $returnHtml = false)
    {
        $invoice = $this->input->post('invoice_id', true);
        $this->form_validation->set_rules('invoice_id', 'transaction invoice ID', 'trim|required');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                return sendAPiResponse(false, $error);
            }
        }
        loadClass($this->load, 'transaction_custom');
        loadClass($this->load, 'payment');
        $transaction = $this->transaction_custom->getWhere(['id' => $invoice], $c, 0, null, false);
        if (!$transaction) {
            return sendAPiResponse(false, 'Invalid invoice transaction ');
        }
        $transaction = $transaction[0];
        $users = $transaction->users_custom;
        if (!$users) {
            return sendAPiResponse(false, 'There is no valid user');
        }
        $payment_channel = get_setting('payment_gateway');
        $initDetails = null;
        if ($transaction->transaction_ref && $transaction->rrr_code == '') {
            $param = [
                'requery' => true,
                'description' => $transaction->payment_description,
                'serviceCharge' => $transaction->service_charge,
                'total' => $transaction->total_amount,
                'payment_id' => $transaction->payment_id,
            ];
            $initDetails = $this->payment->customInitPayment($users, $payment_channel, $param, $transaction);
        } else {
            $initDetails = $this->payment->getCustomPaymentDetails($users, $payment_channel, $transaction);
        }

        // validate the remita response
        if (isset($initDetails['status']) && !$initDetails['status']) {
            return sendAPiResponse(false, $initDetails['message']);
        }
        $contacts = $users->contact_person;
        $contact = null;
        $contactPhone = null;
        if ($contacts) {
            [$contact, $contactPhone] = explode(":", $contacts);
        }

        $param = [
            'payment_description' => $transaction->payment_description,
            'start_date' => $transaction->start_date,
            'end_date' => $transaction->end_date,
            'total_amount' => $transaction->total_amount,
            'contact' => $contact,
            'contactPhone' => $contactPhone,
            'rrr' => $initDetails['rrr'],
            'date_performed' => $initDetails['date_performed'],
        ];
        $this->sendTransactionInvoice($users, $param, $returnHtml);
    }

    /**
     * This is to send non-student transaction invoice
     * @param object $users [description]
     * @param array $param [description]
     * @param bool|boolean $returnHtml [description]
     * @return void
     */
    private function sendTransactionInvoice(object $users, array $param, bool $returnHtml = false)
    {
        $this->load->library('parser');
        $receiptsData = ['menu_items' => []];
        $receiptsData['menu_items'][] = [
            'item_name' => 'Fee Category',
            'description' => $param['payment_description'],
            'start_date' => $param['start_date'],
            'end_date' => $param['end_date'],
            'amount' => number_format($param['total_amount'], 2),
        ];

        $globalVariables = [
            'fullname' => ucfirst($users->name),
            'address' => $users->address ?? null,
            'contact' => $param['contact'],
            'contact_phone' => $param['contactPhone'],
            'RRR' => $param['rrr'],
            'date_initiated' => $param['date_performed'],
            'total_amount' => number_format($param['total_amount'], 2),
        ];
        $variables = $globalVariables + $receiptsData;
        $receipient = $users->email;
        $html = $this->load->view('print/custom_receipt.html', '', true);
        $html = $this->parser->parse_string($html, $variables, true);
        if ($returnHtml) {
            return sendAPiResponse(true, 'Print Invoice', ['print' => $html]);
        }
        $subject = "UIDLC (Fee Category) - Invoice RRR [{$param['rrr']}]";
        // if(!$this->mailer->sendMail('DLC', $receipient, $subject, $html)){
        // 	return sendApiResponse(false, "Unable to send the invoice via email, please try again");
        // }
        return sendAPiResponse(true, 'You have successfully created the invoice');

    }

    /**
     * This would verify custom transaction for custom users
     * @return void
     */
    public function verify_transaction_invoice()
    {
        $rrrCode = request()->getGet('rrr_code', true);
        if (!$rrrCode) {
            displayJson(false, "Please provide a valid RRR code");
            return;
        }
        loadClass($this->load, 'transaction_custom');
        $trans = $this->transaction_custom->getWhere(['rrr_code' => $rrrCode], $c, 0, null, false);
        if (!$trans) {
            displayJson(false, "Transaction not found");
            return;
        }
        $trans = $trans[0];
        $payment_channel = get_setting('payment_gateway');
        $response = $trans->verify_transaction($rrrCode, $payment_channel, null);
        if (isset($response['status']) && !$response['status']) {
            displayJson(false, $response['message']);
            return;
        }
        displayJson(true, 'Transaction succesfully verified');
        return;
    }

    /**
     * This check if student had made any payment during change of programme
     * @return void
     */
    public function check_student_payment()
    {
        $studentID = $this->input->post('student_id', true);
        $transaction = $this->adminModel->validateStudentPaymentTransaction($studentID);
        // student have not paid
        if (!$transaction) {
            displayJson(false, 'The student have not paid for the session semester');
            return;
        }
        displayJson(true, 'The student have made payment already');
        return;
    }

    /**
     * @return array
     */
    private function validateStudentProgrammeField()
    {
        $currentProgramme = $this->input->post('current_programme', true);
        $newProgramme = $this->input->post('new_programme', true);
        $studentID = $this->input->post('student_id', true);
        $level = $this->input->post('new_level', true);
        $entryMode = $this->input->post('entry_mode', true);

        $this->form_validation->set_rules('current_programme', 'current programme', 'trim|required');
        $this->form_validation->set_rules('new_programme', 'new programme', "trim|required|differs[current_programme]");
        $this->form_validation->set_rules('new_level', 'new level', 'trim|required');
        $this->form_validation->set_rules('entry_mode', 'new entry mode', 'trim|required');
        $this->form_validation->set_rules('student_id', 'student id', 'trim|required', [
            'required' => 'Please choose a student',
        ]);

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                displayJson(false, $error);
                return;
            }
        }

        return [
            'newProgramme' => $newProgramme,
            'level' => $level,
            'entryMode' => $entryMode,
            'studentID' => $studentID,
            'currentProgramme' => $currentProgramme,
        ];
    }

    /**
     * This checks the student payment validity to pay more or nothing
     * @return void
     */
    public function create_student_topup()
    {
        $validation = $this->validateStudentProgrammeField();

        loadClass($this->load, 'students');
        loadClass($this->load, 'payment');
        loadClass($this->load, 'fee_description');
        $student = $this->students->getStudentRecordDetails($validation['studentID']);
        if (!$student) {
            displayJson(false, 'The student is not valid');
            return;
        }

        if ($student->programme_id == $validation['newProgramme']) {
            return sendAPiResponse(false, "You cannot change to the same programme as the current one.");
        }

        $studentTransaction = $this->adminModel->validateStudentPaymentTransaction($validation['studentID']);
        if (!$studentTransaction) {
            $details = [
                'programme_id' => $validation['newProgramme'],
                'current_level' => $validation['level'],
                'entry_mode' => $validation['entryMode'],
                'student_id' => $validation['studentID'],
            ];
            $validation['programmeStatus'] = 'success';
            return $this->change_student_programme($details, $validation, $student);
        }

        $currentSemester = get_setting('active_semester');
        $param = [
            $student->current_session,
            $validation['newProgramme'],
            $validation['level'],
            $validation['entryMode'],
            $validation['newProgramme'],
            $validation['level'],
            $validation['entryMode'],
            $currentSemester,
        ];
        $newPayment = $student->loadMainFees($student->academic_record, $param);
        if (!$newPayment) {
            displayJson(false, 'Unable to get student new payment, contact the administrator');
            return;
        }
        $newPayment = $newPayment[0];
        $newAmount = $newPayment['total'];
        $formerAmount = $studentTransaction['amount_paid'];
        $payload = [];
        if ($formerAmount >= $newAmount) {
            $details = [
                'programme_id' => $validation['newProgramme'],
                'current_level' => $validation['level'],
                'entry_mode' => $validation['entryMode'],
                'student_id' => $validation['studentID'],
            ];
            $validation['programmeStatus'] = 'success';
            return $this->change_student_programme($details, $validation, $student, $studentTransaction);
        }

        $payload = [
            'prev_amount' => (int)$formerAmount,
            'new_amount' => $newAmount,
            'topup_payment' => $newAmount - $formerAmount,
            'payment_id' => hashids_decrypt($newPayment['payment_id']),
            'description' => $newPayment['description'],
            'payment_code' => $newPayment['payment_code2'],
        ];

        return $this->createTopupPayment($student, $payload, $validation);
    }

    /**
     * This create the topup school fee invoice
     * @param mixed $payload
     * @param mixed $validation
     * @return void@param mixed $student
     */
    private function createTopupPayment($student, $payload, $validation)
    {
        loadClass($this->load, 'student_change_of_programme');
        $users = [
            'id' => $student->id,
            'name' => $student->firstname . ' ' . $student->lastname . ' ' . $student->othernames,
            'phone_number' => decryptData($this, $student->phone),
            'email' => $student->user_login,
            'matric' => $student->matric_number,
            'programme_id' => $validation['newProgramme'],
            'session' => $student->current_session,
            'current_level' => $validation['level'],
        ];
        $users = (object)$users;
        $serviceCharge = 505;
        $amount = $payload['topup_payment'];
        $total = $amount + $serviceCharge;
        $param = [
            'requery' => false,
            'description' => $payload['description'] . ' ' . "Top Up Balance",
            'amount' => $amount,
            'serviceCharge' => $serviceCharge,
            'total' => $total,
            'payment_id' => $payload['payment_code'],
            'real_payment_id' => $payload['payment_id'],
            'transaction_type' => 'top_up',
        ];
        $insertFresh = true;
        $transaction = $this->payment->getPendingTopupTransaction($payload['payment_id'], $student->id, $student->current_session) ?? null;
        if ($transaction) {
            $param['requery'] = true;
            $insertFresh = false;
        }

        $payment_channel = get_setting('payment_gateway');
        $initDetails = $this->payment->customInitPayment($users, $payment_channel, $param, $transaction);
        // validate the remita response
        if (isset($initDetails['status']) && !$initDetails['status']) {
            displayJson(false, $initDetails['message']);
            return;
        }

        $param = [
            'payment_description' => $payload['description'],
            'total_amount' => $total,
            'contact' => $student->contact_address ?? null,
            'contactPhone' => decryptData($this, $student->phone) ?? null,
            'rrr' => $initDetails['rrr'],
            'date_performed' => $initDetails['date_performed'],
            'start_date' => $initDetails['date_performed'],
            'end_date' => date('Y-m-d H:i:s'),
        ];
        // update the student prev_program_id
        $details = ['prev_programme_id' => $validation['currentProgramme']];
        if (!update_record($this, 'academic_record', 'student_id', $validation['studentID'], $details)) {
            displayJson(false, "Update not done, something went wrong!");
            return;
        }

        if ($insertFresh) {
            $validation['programmeStatus'] = 'pending';
            if (!$this->createStudentChangeProgramme($validation, $student, $initDetails['orig_transaction_id'])) {
                displayJson(false, "Something went wrong, please try again");
                return;
            }
        }
        $this->sendTopupTransactionInvoice($users, $param, true);
    }

    /**
     * This generate the topup invoice receipt
     * @param object $users [description]
     * @param array $param [description]
     * @param bool|boolean $returnHtml [description]
     * @return void
     */
    private function sendTopupTransactionInvoice(object $users, array $param, bool $returnHtml = false)
    {
        $this->load->library('parser');
        $receiptsData = ['menu_items' => []];
        $receiptsData['menu_items'][] = [
            'item_name' => 'Fee Category',
            'description' => $param['payment_description'],
            'start_date' => $param['start_date'],
            'end_date' => $param['end_date'],
            'amount' => number_format($param['total_amount'], 2),
        ];

        $globalVariables = [
            'fullname' => ucfirst($users->name),
            'address' => $users->address ?? null,
            'contact' => $param['contact'],
            'contact_phone' => $param['contactPhone'],
            'RRR' => $param['rrr'],
            'date_initiated' => $param['date_performed'],
            'total_amount' => number_format($param['total_amount'], 2),
        ];
        $variables = $globalVariables + $receiptsData;
        $receipient = $users->email;
        $html = $this->load->view('print/custom_receipt.html', '', true);
        $html = $this->parser->parse_string($html, $variables, true);
        if ($returnHtml) {
            displayJson(true, 'Print Invoice', ['print' => $html]);
            return;
        }
        $subject = "UIDLC (Fee Category) - Invoice RRR [{$param['rrr']}]";
        // $this->mailer->sendMail('DLC', $receipient, $subject, $html);
        displayJson(true, 'You have successfully created the invoice');
    }

    /**
     * @param array<int,mixed> $validation
     * @param mixed $transactionID
     * @return bool
     */
    private function createStudentChangeProgramme(array $validation, object $student, $transactionID = null): bool
    {
        loadClass($this->load, 'student_change_of_programme');
        $semester = get_setting('active_semester');
        $data = [
            'student_id' => $validation['studentID'],
            'old_programme_id' => $validation['currentProgramme'],
            'new_programme_id' => $validation['newProgramme'],
            'old_level_id' => $student->current_level,
            'new_level_id' => $validation['level'],
            'old_entry_mode' => $student->entry_mode,
            'new_entry_mode' => $validation['entryMode'],
            'session' => $student->current_session,
            'transaction_id' => $transactionID,
            'programme_status' => $validation['programmeStatus'],
            'semester' => $semester,
        ];
        $insert = new Student_change_of_programme($data);
        if (!$insert->insert()) {
            return false;
        }
        return true;
    }

    /**
     * This change student programme
     * @param boolean $data [description]
     * @param mixed $student
     * @param mixed $studentTransaction
     * @param mixed $updateTransaction
     * @return void|null
     */
    public function change_student_programme($data = false, $validation = false, $student = false, $studentTransaction = null, $updateTransaction = false)
    {
        $internal = false;
        $rrrCode = null;
        $transaction = false;
        if ($data) {
            $details = $data;
            $internal = true;
        } else {
            // this would be coming from the endpoint
            $validation = $this->validateStudentProgrammeField();
            $details = [
                'programme_id' => $validation['newProgramme'],
                'current_level' => $validation['level'],
                'entry_mode' => $validation['entryMode'],
                'student_id' => $validation['studentID'],
            ];
            $rrrCode = $this->input->post('rrr_code', true);
        }

        loadClass($this->load, 'students');
        loadClass($this->load, 'transaction');

        // means it's coming from create_student_topup method and from change_student_academia
        if ($student && $validation && $internal) {
            // this should only apply provided the programme had changed
            if ($student->programme_id != $validation['newProgramme']) {
                $programmeStatus = $this->adminModel->validateStudentProgrammeStatus($student->studentID, $student->current_session);
                if ($programmeStatus) {
                    return sendAPiResponse(false, "Student programme had already been changed for the session");
                }

                if (!$this->createStudentChangeProgramme($validation, $student, null)) {
                    return sendAPiResponse(false, "Something went wrong, please try again");
                }
            }
        }

        $currentUser = $this->webSessionManager->currentAPIUser();
        if (!$internal) {
            $student = $this->students->getStudentRecordDetails($details['student_id']);
            if (!$student) {
                return sendAPiResponse(false, 'The student is not valid');
            }
            $transaction = $this->transaction->checkTransactionByRRR($rrrCode);
            if (!$transaction) {
                return sendAPiResponse(false, 'The transaction payment has not been confirmed, please try again later');
            }
            $details2 = ['programme_status' => 'success'];
            if (!update_record($this, 'student_change_of_programme', 'transaction_id', $transaction['id'], $details2)) {
                return sendAPiResponse(false, "Unable to change student programme, please try again later");
            }
        }

        if (!update_record($this, 'academic_record', 'student_id', $details['student_id'], $details)) {
            return sendAPiResponse(false, "Unable to change student programme, please try again later");
        }

        // this is coming from student who already paid and it's sufficient
        if ($internal && $studentTransaction) {
            $newRecord = [
                'programme_id' => $validation['newProgramme'],
            ];
            if ($updateTransaction) {
                $newRecord['level'] = $validation['level'];
            }
            if (!update_record($this, 'transaction', 'id', $studentTransaction['id'], $newRecord)) {
                return sendAPiResponse(false, "Unable to update student programme, please try again later");
            }
        }

        if ($transaction) {
            logAction($this, 'updated_student_programme', $currentUser->user_login);
        } else {
            logAction($this, 'changed_student_programme', $currentUser->user_login);
        }
        return sendAPiResponse(true, "You have successfully updated student record");

    }

    /**
     * @return void
     */
    public function change_student_biodata()
    {
        $studentID = $this->input->post('student_id', true);
        $firstname = $this->input->post('firstname', true);
        $lastname = $this->input->post('lastname', true);
        $othernames = $this->input->post('othernames', true);
        $gender = $this->input->post('gender', true);
        $dob = $this->input->post('dob', true);
        $email = $this->input->post('email', true);
        $phone = $this->input->post('phone', true);

        $this->form_validation->set_rules('student_id', 'student id', 'trim|required', [
            'required' => 'Please choose a student',
        ]);
        $this->form_validation->set_rules('firstname', 'firstname', 'trim');
        $this->form_validation->set_rules('lastname', 'lastname', 'trim');
        $this->form_validation->set_rules('othernames', 'othernames', 'trim');
        $this->form_validation->set_rules('gender', 'gender', 'trim|in_list[Male,Female]');
        $this->form_validation->set_rules('dob', 'dob', 'trim');
        $this->form_validation->set_rules('email', 'email', 'trim');
        $this->form_validation->set_rules('phone', 'phone', 'trim');

        if ($this->form_validation->run() == false) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                displayJson(false, $error);
                return;
            }
        }
        loadClass($this->load, 'students');
        $student = $this->students->getStudentRecordDetails($studentID);
        if (!$student) {
            displayJson(false, 'The student is not valid');
            return;
        }

        $currentUser = $this->webSessionManager->currentAPIUser();
        $details = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'othernames' => $othernames,
            'gender' => $gender,
            'DoB' => $dob,
            'phone' => encryptData($this, $phone),
        ];
        if ($student->has_institution_email == '0') {
            $details['user_login'] = $email;
            $details['alternative_email'] = $email;
        }

        $oldData = [
            'firstname' => $student->firstname,
            'lastname' => $student->lastname,
            'othernames' => $student->othernames,
            'gender' => $student->gender,
            'DoB' => $student->DoB,
            'user_login' => $student->user_login,
            'alternative_email' => $student->alternative_email,
        ];
        if (!update_record($this, 'students', 'id', $studentID, $details)) {
            displayJson(false, "Unable to change student biodata, please try again later");
            return;
        }
        $newData = json_encode($details);
        $oldData = json_encode($oldData);
        logAction($this, 'changed_student_biodata', $currentUser->user_login, $studentID, $oldData, $newData);
        displayJson(true, "You have successfully changed student biodata");
        return;
    }

    /**
     * This is strictly for freshers change of academic details record under admissions update
     * @return void
     */
    public function change_student_academia()
    {
        $studentID = $this->input->post('student_id', true);
        $programme = $this->input->post('programme', true);
        $modeOfEntry = $this->input->post('entry_mode', true);
        $teachingSubject = $this->input->post('teaching_subject', true);
        $level = null;
        $this->form_validation->set_rules('student_id', 'student id', 'trim|required', [
            'required' => 'Please choose a student',
        ]);
        $this->form_validation->set_rules('programme', 'programme', 'trim');
        $this->form_validation->set_rules('entry_mode', 'mode of entry', 'trim');
        $this->form_validation->set_rules('teaching_subject', 'teaching subject', 'trim');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                return sendAPiResponse(false, $error);
            }
        }

        loadClass($this->load, 'students');
        loadClass($this->load, 'payment');
        loadClass($this->load, 'fee_description');
        $student = $this->students->getStudentRecordData($studentID);
        if (!$student) {
            return sendAPiResponse(false, 'The student is not valid');
        }

        if ($modeOfEntry == CommonSlug::O_LEVEL || $modeOfEntry == CommonSlug::O_LEVEL_PUTME) {
            $level = "1";
        } else if ($modeOfEntry == CommonSlug::DIRECT_ENTRY || $modeOfEntry == CommonSlug::FAST_TRACK) {
            $level = "2";
        } else {
            $level = $student->current_level;
        }

        $studentTransaction = $this->adminModel->validateStudentPaymentTransaction($studentID);
        if ($studentTransaction) {
            $currentPaymentId = (get_setting('active_semester') == 1) ? PaymentFeeDescription::SCH_FEE_FIRST : PaymentFeeDescription::SCH_FEE_SECOND;
            $param = [
                $student->current_session,
                $programme,
                $level,
                $modeOfEntry,
                $programme,
                $level,
                $modeOfEntry,
                $currentPaymentId,
            ];
            $newPayment = $student->loadMainFees($student->academic_record, $param);
            if (!$newPayment) {
                return sendAPiResponse(false, 'Unable to get student new payment, contact the administrator');
            }
            $newPayment = $newPayment[0];
            $newAmount = $newPayment['total'];

            $formerAmount = $studentTransaction['amount_paid'];
            $payload = [];
            if ($formerAmount >= $newAmount) {
                $details = [
                    'entry_mode' => $modeOfEntry,
                    'current_level' => $level,
                    'programme_id' => $programme,
                    'student_id' => $studentID,
                ];
                if ($teachingSubject) {
                    $details['teaching_subject'] = $teachingSubject;
                }
                $validation = [
                    'newProgramme' => $programme,
                    'level' => $level,
                    'entryMode' => $modeOfEntry,
                    'studentID' => $studentID,
                    'currentProgramme' => $student->programme_id,
                ];
                $validation['programmeStatus'] = 'success';
                return $this->change_student_programme($details, $validation, $student, $studentTransaction, true);
            } else {
                return sendAPiResponse(false, "The student would need to pay a topup amount, please contact the administrator.");
            }
        }

        $details = [
            'entry_mode' => $modeOfEntry,
            'teaching_subject' => $teachingSubject,
            'current_level' => $level,
            'programme_id' => $programme,
        ];
        $oldData = [
            'teaching_subject' => $student->teaching_subject,
            'entry_mode' => $student->entry_mode,
            'level' => $student->current_level,
            'programme_id' => $student->programme_id,
        ];
        $currentUser = $this->webSessionManager->currentAPIUser();
        if (!update_record($this, 'academic_record', 'student_id', $studentID, $details)) {
            return sendAPiResponse(false, "Unable to change student academic record, please try again later");
        }
        $newData = json_encode($details);
        $oldData = json_encode($oldData);
        logAction($this, 'changed_student_academic', $currentUser->user_login, $studentID, $oldData, $newData);
        return sendAPiResponse(true, "You have successfully changed student academic");
    }

    /**
     * This get student specific change of programme details in a session
     * @return void
     */
    public function student_change_programme()
    {
        loadClass($this->load, 'student_change_of_programme');
        $student = request()->getGet('student', true);
        $session = request()->getGet('session', true);

        $data = [
            'session' => $session,
            'student' => $student,
        ];
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('session', 'session', 'trim|required');
        $this->form_validation->set_rules('student', 'student', 'trim|required');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                return sendAPiResponse(false, $error);
            }
        }

        $result = $this->student_change_of_programme->getStudentChangeProgrogramme($student, $session);
        return sendAPiResponse(true, 'success', $result);
    }

    /**
     * This verifies transaction status using the rrr code
     * @return void
     */
    public function verify_transaction()
    {
        $rrrCode = request()->getGet('rrr_code', true);
        $type = request()->getGet('requery_type', true) ?: 'student_trans';
        $data = [
            'rrr_code' => $rrrCode,
            'requery_type' => $type,
        ];
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('rrr_code', 'RRR code', 'trim|required', [
            'required' => 'Please provide a valid RRR code',
        ]);
        $this->form_validation->set_rules('requery_type', 'requery type', 'trim|required', [
            'required' => 'Please provide a valid requery type',
        ]);

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                displayJson(false, $error);
                return;
            }
        }

        if ($type == 'student_trans') {
            return $this->verifyStudentTransaction($rrrCode);
        } else if ($type == 'admission_trans') {
            return $this->verifyApplicantTransaction($rrrCode);
        } else if ($type == 'custom_trans') {
            return $this->verifyCustomTransaction($rrrCode);
        }

    }

    /**
     * @param mixed $rrrCode
     * @return void
     */
    public function verifyStudentTransaction($rrrCode)
    {
        loadClass($this->load, 'transaction');
        loadClass($this->load, 'students');
        $trans = $this->transaction->getWhere(['rrr_code' => $rrrCode], $c, 0, null, false);
        if (!$trans) {
            displayJson(false, 'Transaction not found');
            return;
        }
        $trans = $trans[0];
        $payment_channel = get_setting('payment_gateway');
        $response = $trans->verify_transaction($rrrCode, $payment_channel);
        if (isset($response['status']) && !$response['status']) {
            displayJson(false, $response['message']);
            return;
        }

        // auto generate matric
        $this->students->id = $trans->student_id;
        if ($this->students->load()) {
            if ($trans->payment_id == PaymentFeeDescription::SCH_FEE_FIRST) {
                $matric = $this->students->autoGenerateMatricNumber($this->students);
                if ($matric) {
                    $this->students->autoGenerateInstitutionalEmail($this->students, $matric);
                }
            }
        }

        displayJson(true, 'Transaction successfully verified');
        return;
    }

    /**
     * @param mixed $rrrCode
     * @return void
     */
    public function verifyApplicantTransaction($rrrCode)
    {
        loadClass($this->load, 'applicant_transaction');
        $trans = $this->applicant_transaction->getWhere(['rrr_code' => $rrrCode], $c, 0, null, false);
        if (!$trans) {
            displayJson(false, 'Transaction not found');
            return;
        }
        $trans = $trans[0];
        $payment_channel = get_setting('payment_gateway');
        $response = $trans->verify_transaction($rrrCode, $payment_channel);
        if (isset($response['status']) && !$response['status']) {
            displayJson(false, $response['message']);
            return;
        }
        displayJson(true, 'Transaction succesfully verified');
        return;
    }

    /**
     * @param mixed $rrrCode
     * @return void
     */
    public function verifyCustomTransaction($rrrCode)
    {
        loadClass($this->load, 'transaction_custom');
        $trans = $this->transaction_custom->getWhere(['rrr_code' => $rrrCode], $c, 0, null, false);
        if (!$trans) {
            displayJson(false, 'Transaction not found');
            return;
        }
        $trans = $trans[0];
        $payment_channel = get_setting('payment_gateway');
        $response = $trans->verify_transaction($rrrCode, $payment_channel);
        if (isset($response['status']) && !$response['status']) {
            displayJson(false, $response['message']);
            return;
        }
        displayJson(true, 'Transaction succesfully verified');
        return;
    }

    /**
     * @return void
     */
    public function student_transactions()
    {
        $studentID = request()->getGet('student', true);
        $data = [
            'student' => $studentID,
        ];
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('student', 'student', 'trim|required', [
            'required' => 'Please choose a student',
        ]);

        if ($this->form_validation->run() == false) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                displayJson(false, $error);
                return;
            }
        }

        loadClass($this->load, 'students');
        $this->students->id = $studentID;
        if (!$this->students->load()) {
            displayJson(false, 'Invalid student info');
            return;
        }
        $transaction = $this->students->studentTransaction;
        displayJson(true, "Success", $transaction);
        return;
    }

    /**
     * @return void
     */
    public function preselected_fee()
    {
        loadClass($this->load, 'payment');
        $payments = $this->adminModel->getAllPayments();

        $content = [];
        if ($payments) {
            foreach ($payments as $payment) {
                $sessionName = ($payment['session'] && $payment['session'] != 0) ? " [{$payment['session']}] " : "";
                $paymentType = str_replace("_", " ", paymentCategoryType($payment['fee_category'], true));
                $paymentType = ucwords($paymentType);
                $item = [
                    'id' => $payment['id'],
                    // 'value' => $payment['description']." [".$paymentType."]".$sessionName ,
                    'value' => $payment['description'] . " - " . $payment['payment_code'],
                ];
                $content[] = $item;
            }
        }
        displayJson(true, 'success', $content);
    }

    /**
     * @return void
     */
    public function prerequisites_fee()
    {
        loadClass($this->load, 'payment');
        $payments = $this->adminModel->getAllPayments();

        $content = [];
        if ($payments) {
            foreach ($payments as $payment) {
                $sessionName = ($payment['session'] && $payment['session'] != 0) ? " [{$payment['session']}] " : "";
                $paymentType = str_replace("_", " ", paymentCategoryType($payment['fee_category'], true));
                $paymentType = ucwords($paymentType);
                $item = [
                    'id' => $payment['id'],
                    // 'value' => $payment['description']." [".$paymentType."]".$sessionName ,
                    'value' => $payment['description'] . " - " . $payment['payment_code'],
                ];
                $content[] = $item;
            }
        }
        displayJson(true, 'success', $content);
    }

    /**
     * @return void
     */
    public function restore_finance_transaction()
    {
        $transactionID = $this->input->post('id', true);
        $transactionType = $this->input->post('trans_type', true);
        $this->form_validation->set_rules('id', 'transaction', 'trim|required');
        $this->form_validation->set_rules('trans_type', 'transaction type', 'trim|required|in_list[student_trans,admission_trans,custom_trans]');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                displayJson(false, $error);
                return;
            }
        }

        if ($transactionType == 'student_trans') {
            $currentUser = $this->webSessionManager->currentAPIUser() ?? 0;
            loadClass($this->load, 'transaction');
            $this->db->trans_begin();
            $archive = $this->transaction->checkArchivePaymentStatus($transactionID);
            if (!$archive) {
                $this->db->trans_rollback();
                displayJson(false, 'Transaction archive not found');
                return;
            }

            if (!$this->transaction->restoreTransactionToArchive($transactionID, 'transaction')) {
                $this->db->trans_rollback();
                displayJson(false, "An error occured, Transaction cannot be restored at the moment");
                return;
            }
            $id = $archive->id;
            if (!$archive->delete($id)) {
                $this->db->trans_rollback();
                displayJson(false, "An error occured, Transaction cannot be restored at the moment");
                return;
            }

            logAction($this, 'restore_transaction', $currentUser->user_login);
            $this->db->trans_commit();
            displayJson(true, "Transaction restored successfully");
            return;
        }

        if ($transactionType == 'admission_trans') {
            $currentUser = $this->webSessionManager->currentAPIUser() ?? 0;
            loadClass($this->load, 'applicant_transaction');
            loadClass($this->load, 'transaction');
            $this->db->trans_begin();
            $archive = $this->transaction->checkArchivePaymentStatus($transactionID);
            if (!$archive) {
                $this->db->trans_rollback();
                displayJson(false, 'Applicant transaction archive not found');
                return;
            }

            if (!$this->transaction->restoreTransactionToArchive($transactionID, 'applicant_transaction')) {
                $this->db->trans_rollback();
                displayJson(false, "An error occured, applicant transaction cannot be restored at the moment");
                return;
            }
            $id = $archive->id;
            if (!$archive->delete($id)) {
                $this->db->trans_rollback();
                displayJson(false, "An error occured, applicant transaction cannot be restored at the moment");
                return;
            }

            logAction($this, 'restore_applicant_transaction', $currentUser->user_login);
            $this->db->trans_commit();
            displayJson(true, "Applicant transaction restored successfully");
            return;
        }
    }

    /**
     * @return void
     */
    public function students_create()
    {
        permissionAccess($this, 'student_create', 'create');

        // Grab details from form
        $matric = $this->input->post('matric', true);
        $entry_mode = $this->input->post('entry_mode', true);
        $level = $this->input->post('level', true);
        $session = $this->input->post('current_session', true);
        $lastname = $this->input->post('lastname', true);
        $othernames = $this->input->post('othernames', true);
        $firstname = $this->input->post('firstname', true);
        $gender = $this->input->post('gender', true);
        $dob = $this->input->post('dob', true);
        $marital_status = $this->input->post('marital', true);
        $religion = $this->input->post('religion', true);
        $phone = $this->input->post('phone', true);
        $email = $this->input->post('email', true);
        $alt_email = $this->input->post('alt_email', true);
        $profession = $this->input->post('profession', true);
        $contact_address = $this->input->post('contact_address', true);
        $postal_address = $this->input->post('postal_address', true);
        $state_of_origin = $this->input->post('state_of_origin', true);
        $lga = $this->input->post('lga', true);
        $nationality = $this->input->post('nationality', true);
        $next_of_kin = $this->input->post('next_of_kin', true);
        $next_of_kin_p = $this->input->post('next_of_kin_phone', true);
        $nok_address = $this->input->post('next_of_kin_addr', true);

        $status = $this->input->post('status');
        $is_verified = $this->input->post('verification_status');

        $entry_year = $this->input->post('entry_year', true);
        $entry_level = $this->input->post('admission_level', true);
        $programme = $this->input->post('programme', true);
        $prog_duration = $this->input->post('prog_duration', true);
        $min_prog_duration = $this->input->post('min_prog_duration', true);
        $max_prog_duration = $this->input->post('max_prog_duration', true);
        $interactive_center = $this->input->post('interactive_center', true);
        $exam_center = $this->input->post('exam_center', true);
        $teaching_subject = $this->input->post('teaching_subject', true);
        $has_matric_number = $this->input->post('has_matric_number', true);
        $has_institution_email = $this->input->post('has_institution_email', true);
        $application_number = $this->input->post('application_number', true);
        $session_of_admission = $this->input->post('session_of_admission', true);

        $height = $this->input->post('height', true);
        $weight = $this->input->post('weight', true);
        $allergy = $this->input->post('allergy', true);
        $blood_group = $this->input->post('blood_grp', true);
        $genotype = $this->input->post('genotype', true);
        $other_medical = $this->input->post('other_medical', true);

        // validation input
        $this->form_validation->set_rules('matric', 'matriculation number', 'trim');
        $this->form_validation->set_rules('entry_mode', 'entry mode', 'trim|required');
        $this->form_validation->set_rules('level', 'level', 'trim|required');
        $this->form_validation->set_rules('current_session', 'session', 'trim|required');
        $this->form_validation->set_rules('lastname', 'lastname', 'trim|required|xss_clean');
        $this->form_validation->set_rules('othernames', 'othernames', 'trim|xss_clean');
        $this->form_validation->set_rules('firstname', 'firstname', 'trim|required|xss_clean');
        $this->form_validation->set_rules('gender', 'gender', 'trim|required');
        $this->form_validation->set_rules('dob', 'date of birth', 'trim|required');
        $this->form_validation->set_rules('marital', 'marital status', 'trim|required');
        $this->form_validation->set_rules('religion', 'religion', 'trim');
        $this->form_validation->set_rules('phone', 'phone number', 'trim');
        $this->form_validation->set_rules('email', 'email address', 'trim|valid_email|required');
        $this->form_validation->set_rules('alt_email', 'alternative email address', 'trim|valid_email');
        $this->form_validation->set_rules('profession', 'profession ', 'trim');
        $this->form_validation->set_rules('contact_address', 'contact address ', 'trim');
        $this->form_validation->set_rules('postal_address', 'postal address ', 'trim');
        $this->form_validation->set_rules('state_of_origin', 'state of origin ', 'trim');
        $this->form_validation->set_rules('lga', 'local government area ', 'trim');
        $this->form_validation->set_rules('nationality', 'nationality ', 'trim');
        $this->form_validation->set_rules('next_of_kin', 'next of kin ', 'trim');
        $this->form_validation->set_rules('next_of_kin_phone', 'next of kin phone number ', 'trim');
        $this->form_validation->set_rules('next_of_kin_addr', 'next of kin address ', 'trim');

        $this->form_validation->set_rules('status', 'status', 'trim|required');
        $this->form_validation->set_rules('verification_status', 'verification status ', 'trim|required');

        $this->form_validation->set_rules('entry_year', 'entry year ', 'trim|required');
        $this->form_validation->set_rules('session_of_admission', 'session of admission ', 'trim|required');
        $this->form_validation->set_rules('admission_level', 'level of entry ', 'trim|required');
        $this->form_validation->set_rules('programme', 'programme ', 'trim|required');
        $this->form_validation->set_rules('prog_duration', 'programme duration ', 'trim|required');
        $this->form_validation->set_rules('min_prog_duration', 'min programme duration ', 'trim');
        $this->form_validation->set_rules('max_prog_duration', 'max programme duration ', 'trim');
        $this->form_validation->set_rules('interactive_center', 'interactive center ', 'trim');
        $this->form_validation->set_rules('exam_center', 'exam center ', 'trim');
        $this->form_validation->set_rules('teaching_subject', 'teaching subject ', 'trim');
        $this->form_validation->set_rules('has_matric_number', 'has matric number', 'trim|required');
        $this->form_validation->set_rules('has_institution_email', 'has institution email ', 'trim|required');
        $this->form_validation->set_rules('application_number', 'application number ', 'trim');

        $this->form_validation->set_rules('height', 'height ', 'trim');
        $this->form_validation->set_rules('weight', 'weight ', 'trim');
        $this->form_validation->set_rules('allergy', 'allergy ', 'trim');
        $this->form_validation->set_rules('blood_grp', 'blood group ', 'trim');
        $this->form_validation->set_rules('blood_grp', 'genotype ', 'trim');
        $this->form_validation->set_rules('other_medical', 'other medical details', 'trim');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                displayJson(false, $message);
                return;
            }
        }

        $safe_email = (!strtolower($email) || !fetchSingle($this, 'students', 'user_login', $email));
        if (!$safe_email) {
            $message = "Email address '" . strtolower($email) . "' is already in use by another person";
            displayJson(false, $message);
            return;
        }

        $safe_phone = (!$phone || !fetchSingle($this, 'students', 'phone', $phone));
        if (!$safe_phone) {
            $message = "Phone number '" . strtolower($phone) . "' is already in use by another person";
            displayJson(false, $message);
            return;
        }

        $safe_matric = (!$matric || !fetchSingle($this, 'academic_record', 'matric_number', $matric));
        if (!$safe_matric) {
            $message = "Matric number '" . strtolower($matric) . "' is already in use by another person";
            displayJson(false, $message);
            return;
        }

        $user_pass = encode_password(trim(strtolower($lastname)));
        $date = date('Y-m-d H:i:s');

        $bioData = [
            'firstname' => ucwords(strtolower($firstname)),
            'othernames' => ucwords(strtolower($othernames)) ?? '',
            'lastname' => ucwords(strtolower($lastname)),
            'gender' => $gender,
            'DoB' => $dob,
            'phone' => encryptData($this, $phone) ?? '',
            'marital_status' => $marital_status,
            'religion' => $religion ?? '',
            'contact_address' => ucwords(strtolower($contact_address)) ?? '',
            'postal_address' => ucwords(strtolower($postal_address)) ?? '',
            'profession' => $profession ?? '',
            'state_of_origin' => $state_of_origin ?? '',
            'lga' => $lga ?? '',
            'nationality' => $nationality ?? '',
            'passport' => '',
            'full_image' => '',
            'next_of_kin' => $next_of_kin ?? '',
            'next_of_kin_phone' => $next_of_kin_p ?? '',
            'active' => $status ?? '0',
            'is_verified' => $is_verified ?? '0',
            'user_login' => strtolower($email),
            'alternative_email' => strtolower($alt_email) ?? '',
            'user_pass' => $user_pass,
            'date_created' => $date,
            'password' => $user_pass,
        ];

        $this->db->trans_begin();
        $student_id = create_record($this, 'students', $bioData);
        if (!$student_id) {
            $this->db->trans_rollback();
            displayJson(false, "Student cannot be added, something went wrong!");
            return;
        }

        $academicData = [
            'student_id' => $student_id,
            'programme_id' => $programme,
            'matric_number' => ($matric) ? $matric : 'not allocated',
            'has_matric_number' => $has_matric_number,
            'has_institution_email' => $has_institution_email,
            'programme_duration' => $prog_duration,
            'min_programme_duration' => $min_prog_duration ?? '',
            'max_programme_duration' => $max_prog_duration ?? '',
            'year_of_entry' => $entry_year,
            'entry_mode' => $entry_mode,
            'interactive_center' => $interactive_center ?? '',
            'exam_center' => $exam_center ?? '',
            'teaching_subject' => $teaching_subject ?? '',
            'level_of_admission' => $entry_level,
            'current_level' => $level,
            'current_session' => $session,
            'application_number' => $application_number ?? '',
            'session_of_admission' => $session_of_admission,
        ];

        $medicalData = [
            'student_id' => $student_id,
            'blood_group' => $blood_group ?? '',
            'genotype' => $genotype ?? '',
            'height' => $height ?? '',
            'weight' => $weight ?? '',
            'allergy' => $allergy ?? '',
            'others' => $other_medical ?? '',
        ];

        $currentUser = $this->webSessionManager->currentAPIUser();
        logAction($this, 'create_student', $currentUser->user_login, $student_id);

        // create academic record
        if (!create_record($this, 'academic_record', $academicData)) {
            $this->db->trans_rollback();
            displayJson(false, "Student cannot be added, something went wrong!");
            return;
        }

        // create medical record
        if (!create_record($this, 'medical_record', $medicalData)) {
            $this->db->trans_rollback();
            displayJson(false, "Student cannot be added, something went wrong!");
            return;
        }

        $this->db->trans_commit();
        displayJson(true, "Student added successfully");
        return;
    }

    /**
     * @return void
     */
    public function students_edit()
    {
        permissionAccess($this, 'student_edit', 'edit');

        // Grab details from form
        $matric = $this->input->post('matric', true);
        $entry_mode = trim($this->input->post('entry_mode', true));
        $session = $this->input->post('current_session', true);
        $lastname = $this->input->post('lastname', true);
        $othernames = $this->input->post('othernames', true);
        $firstname = $this->input->post('firstname', true);
        $gender = $this->input->post('gender', true);
        $dob = $this->input->post('dob', true);
        $marital_status = $this->input->post('marital', true);
        $religion = $this->input->post('religion', true);
        $phone = $this->input->post('phone', true);
        $email = $this->input->post('email', true);
        $alt_email = $this->input->post('alt_email', true);
        $profession = $this->input->post('profession', true);
        $contact_address = $this->input->post('contact_address', true);
        $postal_address = $this->input->post('postal_address', true);
        $state_of_origin = $this->input->post('state_of_origin', true);
        $lga = $this->input->post('lga', true);
        $nationality = $this->input->post('nationality', true);
        $next_of_kin = $this->input->post('next_of_kin', true);
        $next_of_kin_p = $this->input->post('next_of_kin_phone', true);
        $nok_address = $this->input->post('next_of_kin_addr', true);

        $status = $this->input->post('status');
        $is_verified = $this->input->post('verification_status');

        $entry_year = $this->input->post('entry_year', true);
        $entry_level = $this->input->post('admission_level', true);
        $prog_duration = $this->input->post('prog_duration', true);
        $min_prog_duration = $this->input->post('min_prog_duration', true);
        $max_prog_duration = $this->input->post('max_prog_duration', true);
        $interactive_center = $this->input->post('interactive_center', true);
        $exam_center = $this->input->post('exam_center', true);
        $teaching_subject = $this->input->post('teaching_subject', true);
        $has_matric_number = $this->input->post('has_matric_number', true);
        $has_institution_email = $this->input->post('has_institution_email', true);
        $application_number = $this->input->post('application_number', true);
        $session_of_admission = $this->input->post('session_of_admission', true);

        $height = $this->input->post('height', true);
        $weight = $this->input->post('weight', true);
        $allergy = $this->input->post('allergy', true);
        $blood_group = $this->input->post('blood_grp', true);
        $genotype = $this->input->post('genotype', true);
        $other_medical = $this->input->post('other_medical', true);

        $programme = $this->input->post('programme', true);
        $level = $this->input->post('level', true);

        // validation input
        $this->form_validation->set_rules('matric', 'matriculation number', 'trim');
        $this->form_validation->set_rules('entry_mode', 'entry mode', 'trim|required');
        $this->form_validation->set_rules('current_session', 'session', 'trim|required');
        $this->form_validation->set_rules('lastname', 'lastname', 'trim|required|xss_clean');
        $this->form_validation->set_rules('othernames', 'othernames', 'trim|xss_clean');
        $this->form_validation->set_rules('firstname', 'firstname', 'trim|required|xss_clean');
        $this->form_validation->set_rules('gender', 'gender', 'trim|required');
        $this->form_validation->set_rules('dob', 'date of birth', 'trim|required');
        $this->form_validation->set_rules('marital', 'marital status', 'trim|required');
        $this->form_validation->set_rules('religion', 'religion', 'trim');
        $this->form_validation->set_rules('phone', 'phone number', 'trim');
        $this->form_validation->set_rules('email', 'email address', 'trim|required|valid_email');
        $this->form_validation->set_rules('alt_email', 'alternative email address', 'trim|valid_email');
        $this->form_validation->set_rules('profession', 'profession ', 'trim');
        $this->form_validation->set_rules('contact_address', 'contact address ', 'trim');
        $this->form_validation->set_rules('postal_address', 'postal address ', 'trim');
        $this->form_validation->set_rules('state_of_origin', 'state of origin ', 'trim');
        $this->form_validation->set_rules('lga', 'local government area ', 'trim');
        $this->form_validation->set_rules('nationality', 'nationality ', 'trim');
        $this->form_validation->set_rules('next_of_kin', 'next of kin ', 'trim');
        $this->form_validation->set_rules('next_of_kin_phone', 'next of kin phone number ', 'trim');
        $this->form_validation->set_rules('next_of_kin_addr', 'next of kin address ', 'trim');

        $this->form_validation->set_rules('status', 'status', 'trim|required');
        $this->form_validation->set_rules('verification_status', 'verification status ', 'trim|required');

        $this->form_validation->set_rules('entry_year', 'entry year ', 'trim|required');
        $this->form_validation->set_rules('session_of_admission', 'session of admission ', 'trim|required');
        $this->form_validation->set_rules('admission_level', 'level of entry ', 'trim|required');
        $this->form_validation->set_rules('prog_duration', 'programme duration ', 'trim|required');
        $this->form_validation->set_rules('min_prog_duration', 'min programme duration ', 'trim');
        $this->form_validation->set_rules('max_prog_duration', 'max programme duration ', 'trim');
        $this->form_validation->set_rules('interactive_center', 'interactive center ', 'trim');
        $this->form_validation->set_rules('exam_center', 'exam center ', 'trim');
        $this->form_validation->set_rules('teaching_subject', 'teaching subject ', 'trim');
        $this->form_validation->set_rules('has_matric_number', 'has matric number', 'trim|required');
        $this->form_validation->set_rules('has_institution_email', 'has institution email ', 'trim|required');
        $this->form_validation->set_rules('application_number', 'application number ', 'trim');

        $this->form_validation->set_rules('height', 'height ', 'trim');
        $this->form_validation->set_rules('weight', 'weight ', 'trim');
        $this->form_validation->set_rules('allergy', 'allergy ', 'trim');
        $this->form_validation->set_rules('blood_grp', 'blood group ', 'trim');
        $this->form_validation->set_rules('blood_grp', 'genotype ', 'trim');
        $this->form_validation->set_rules('other_medical', 'other medical details', 'trim');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return sendAPiResponse(false, $message);
            }
        }

        $id = trim($this->uri->segment(3));
        $studentRecord = fetchSingle($this, 'students', 'id', $id);
        $academicRecord = fetchSingle($this, 'academic_record', 'student_id', $id);
        $medicalRecord = fetchSingle($this, 'medical_record', 'student_id', $id);

        $safe_email = (getSingleRecordExclude($this, 'students', "user_login='$email' AND id <> '$id' "));
        if ($safe_email) {
            $message = "Email address '" . strtolower($email) . "' is already in use by another person";
            return sendAPiResponse(false, $message);
        }

        $safe_phone = ($phone && getSingleRecordExclude($this, 'students', "phone='$phone' AND id <> '$id'"));
        if ($safe_phone) {
            $message = "Phone number '" . strtolower($phone) . "' is already in use by another person";
            return sendAPiResponse(false, $message);
        }

        $safe_matric = ($matric && getSingleRecordExclude($this, 'academic_record', "matric_number='$matric' AND student_id <> '$id'"));
        if ($safe_matric) {
            $message = "Matric number '" . strtolower($matric) . "' is already in use by another person";
            return sendAPiResponse(false, $message);
        }

        $date = date('Y-m-d H:i:s');
        $bioData = [
            'firstname' => ucwords(strtolower($firstname)),
            'othernames' => ucwords(strtolower($othernames)) ?? '',
            'lastname' => ucwords(strtolower($lastname)),
            'gender' => $gender,
            'DoB' => $dob,
            'phone' => encryptData($this, $phone) ?? '',
            'marital_status' => $marital_status,
            'religion' => $religion ?? '',
            'contact_address' => ucwords(strtolower($contact_address)) ?? '',
            'postal_address' => ucwords(strtolower($postal_address)) ?? '',
            'profession' => $profession ?? '',
            'state_of_origin' => $state_of_origin ?? '',
            'lga' => $lga ?? '',
            'nationality' => $nationality ?? '',
            'passport' => '',
            'full_image' => '',
            'next_of_kin' => $next_of_kin ?? '',
            'next_of_kin_phone' => $next_of_kin_p ?? '',
            'next_of_kin_address' => $nok_address ?? '',
            'active' => $status ?? '0',
            'is_verified' => $is_verified ?? '0',
            'user_login' => strtolower($email),
            'alternative_email' => strtolower($alt_email) ?? '',
        ];

        $this->db->trans_begin();
        $student_id = update_record($this, 'students', 'id', $id, $bioData);
        if (!$student_id) {
            $this->db->trans_rollback();
            return sendAPiResponse(false, "Student cannot be added, something went wrong!");
        }

        $academicData = [
            'matric_number' => $matric ?: '',
            'has_matric_number' => $has_matric_number,
            'has_institution_email' => $has_institution_email,
            'programme_duration' => $prog_duration,
            'min_programme_duration' => $min_prog_duration ?? '',
            'max_programme_duration' => $max_prog_duration ?? '',
            'year_of_entry' => $entry_year,
            'entry_mode' => $entry_mode,
            'interactive_center' => $interactive_center ?? '',
            'exam_center' => $exam_center ?? '',
            'teaching_subject' => $teaching_subject ?? '',
            'level_of_admission' => $entry_level,
            'current_session' => $session,
            'application_number' => $application_number ?? '',
            'session_of_admission' => $session_of_admission,
        ];

        if (($academicRecord['current_level'] == '1' && $academicRecord['entry_mode'] == CommonSlug::O_LEVEL) ||
            ($academicRecord['current_level'] == '1' && $academicRecord['entry_mode'] == CommonSlug::O_LEVEL_PUTME) ||
            ($academicRecord['current_level'] == '2' && $academicRecord['entry_mode'] == CommonSlug::DIRECT_ENTRY) ||
            ($academicRecord['current_level'] == '2' && $academicRecord['entry_mode'] == CommonSlug::FAST_TRACK)) {
            ($programme) ? $academicData['programme_id'] = $programme : null;
        }

        if ($level) {
            $academicData['current_level'] = $level ?: $academicRecord['current_level'];
        }

        $medicalData = [
            'blood_group' => $blood_group ?? '',
            'genotype' => $genotype ?? '',
            'height' => $height ?? '',
            'weight' => $weight ?? '',
            'allergy' => $allergy ?? '',
            'others' => $other_medical ?? '',
        ];

        $currentUser = $this->webSessionManager->currentAPIUser();
        $oldData = [
            'students' => $studentRecord,
            'academic_record' => $academicRecord,
            'medical_record' => $medicalRecord,
        ];
        $newData = [
            'students' => $bioData,
            'academic_record' => $academicData,
            'medical_record' => $medicalData,
        ];
        $newData = json_encode($newData);
        $oldData = json_encode($oldData);
        logAction($this, 'edit_student', $currentUser->user_login, $student_id, $oldData, $newData);

        // update academic record
        if (!update_record($this, 'academic_record', 'student_id', $id, $academicData)) {
            $this->db->trans_rollback();
            return sendAPiResponse(false, "Student cannot be added, something went wrong!");
        }

        // update medical record
        if (!update_record($this, 'medical_record', 'student_id', $id, $medicalData)) {
            $this->db->trans_rollback();
            return sendAPiResponse(false, "Student cannot be added, something went wrong!");
        }

        $this->db->trans_commit();
        return sendAPiResponse(true, "Student record updated successfully");
    }

    /**
     * @return void
     */
    public function student_all_registered_courses()
    {
        permissionAccess($this, 'student_edit');
        $studentID = request()->getGet('student_id', true);

        $data = [
            'student_id' => $studentID,
        ];
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('student_id', 'student', 'trim|required', [
            'required' => 'Please choose a student',
        ]);

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                displayJson(false, $error);
                return;
            }
        }

        loadClass($this->load, 'students');
        $this->students->id = $studentID;
        if (!$this->students->load()) {
            displayJson(false, 'Invalid student info');
            return;
        }
        $academicRecord = $this->students->academic_record;
        $courses = $this->students->getAllStudentRegisteredCourses($studentID);
        $payload = [
            'has_payment' => $this->students->hasPayment($studentID, $academicRecord->current_session),
            'course_registration_log' => $this->students->getCourseRegistrationLog($studentID),
            'registered_courses' => $courses,
        ];
        displayJson(true, 'Student courses fetched successfully', $payload);
    }

    /**
     * @return void
     */
    public function student_registration_courses()
    {
        $method = strtolower($this->input->method(true));
        if ($method === 'post') {
            return $this->stdentRegistrationCoursesPost();
        }

        if ($method === 'get') {
            return $this->stdentRegistrationCoursesGet();
        }
    }

    /**
     * @return void
     */
    public function student_all_paid_session()
    {
        $studentID = request()->getGet('student_id', true);
        $data = [
            'student_id' => $studentID,
        ];
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('student_id', 'student', 'trim|required', [
            'required' => 'Please choose a student',
        ]);

        if ($this->form_validation->run() == false) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                displayJson(false, $error);
                return;
            }
        }

        loadClass($this->load, 'students');
        $this->students->id = $studentID;
        if (!$this->students->load()) {
            displayJson(false, 'Invalid student info');
            return;
        }

        $sessions = $this->students->getAllPaidTransactionSession() ?? [];
        displayJson(true, 'success', $sessions);
    }

    /**
     * @return void
     */
    public function stdentRegistrationCoursesPost()
    {
        permissionAccess($this, 'student_course_reg');

        $studentID = $this->input->post('student_id', true);
        $session = $this->input->post('session', true);
        $enrolledCourses = $this->input->post('courses', true);
        $this->form_validation->set_rules('student_id', 'student', 'trim|required', [
            'required' => 'Please choose a student',
        ]);
        $this->form_validation->set_rules('session', 'session', 'trim|required');
        $this->form_validation->set_rules('courses', 'courses', 'trim');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                displayJson(false, $error);
                return;
            }
        }
        if (!is_array($enrolledCourses)) {
            displayJson(false, "Courses cannot be registered, something went wrong!");
            return;
        }

        loadClass($this->load, 'students');
        loadClass($this->load, 'courses');
        $this->students->id = $studentID;
        if (!$this->students->load()) {
            displayJson(false, 'Invalid student info');
            return;
        }
        $academicRecord = $this->students->academic_record;
        $coursesToAdd = [];
        $logsToAdd = [];
        $currentUser = $this->webSessionManager->currentAPIUser();
        $paidLevel = $this->students->hasPayment($studentID, $session, null, true);
        $level = ($paidLevel) ? $paidLevel[0]['level'] : $academicRecord->current_level;

        foreach ($enrolledCourses as $key => $val) {
            $course = $this->students->getCourseMappingDetails($academicRecord->programme_id, $val, $level);
            $code = $this->courses->getCourseCodeById($val);
            if (!$course) {
                displayJson(false, "Courses cannot be registered, cannot find course mapping details for student, course code: '.$code.'!");
                return;
            }

            // check for semester fee based on the course
            $courseSemester = $course['semester'];
            $semesters = ['first', 'second'];
            $semesterIndex = $courseSemester - 1; // semesters start at index 0
            $semester = @$semesters[$semesterIndex];

            if (!$this->students->hasPayment($studentID, $session, $courseSemester)) {
                displayJson(false, $this->courses->getCourseById($val) . " is a {$semester} semester course, and sch-fee for {$semester} semeter not paid");
                return;
            }

            if ($this->students->alreadyHasRegistration($studentID, $val, $session, $level)) {
                displayJson(false, 'You have previously registered for ' . $this->courses->getCourseById($val));
                return;
            }

            $date = date('Y-m-d H:i:s');
            $enrollment = [
                'student_id' => $studentID,
                'course_id' => $val,
                'session_id' => $session,
                'student_level' => $level,
                'is_approved' => '0',
                'date_last_update' => '',
                'date_created' => $date,
                'course_unit' => $course['course_unit'],
                'course_status' => $course['course_status'],
                'semester' => $course['semester'],
            ];

            $log = [
                'student_id' => $studentID,
                'course_id' => $val,
                'session_id' => $session,
                'level' => $level,
                'username' => $currentUser->user_login,
                'date_created' => $date,
                'operation' => 'add_course_registration',
                'course_unit' => $course['course_unit'],
                'course_status' => $course['course_status'],
            ];

            $coursesToAdd[] = $enrollment;
            $logsToAdd[] = $log;
        }

        foreach ($coursesToAdd as $index => $enrollment) {
            // create course record
            $log = $logsToAdd[$index];
            $create_record = create_record($this, 'course_enrollment', $enrollment);
            $create_record = create_record($this, 'course_registration_log', $log);
        }
        if ($this->students->checkExamRecord($session, $level)) {
            $data = ['student_id' => $studentID, 'session_id' => $session];
            update_record($this, 'exam_record', 'student_id', $studentID, $data);
        } else {
            $data = [
                'student_id' => $studentID,
                'session_id' => $session,
                'student_level' => $level,
                'gpa' => '',
                'cgpa' => '',
                'active' => 0,
                'date_created' => $date,
            ];
            create_record($this, 'exam_record', $data);
        }
        logAction($this, 'course_registration', $currentUser->user_login);
        displayJson(true, "Course registration was successfully");
    }

    /**
     * @return void
     */
    public function stdentRegistrationCoursesGet()
    {
        permissionAccess($this, 'student_edit');

        $studentID = request()->getGet('student_id', true);
        $session = request()->getGet('session', true);
        $semester = request()->getGet('semester', true);
        $data = [
            'student_id' => $studentID,
            'session' => $session,
            'semester' => $semester,
        ];
        $this->form_validation->set_data($data);
        $this->form_validation->set_rules('student_id', 'student', 'trim|required', [
            'required' => 'Please choose a student',
        ]);
        $this->form_validation->set_rules('session', 'session', 'trim|required', [
            'required' => 'Please choose a session',
        ]);
        $this->form_validation->set_rules('semester', 'semester', 'trim|required', [
            'required' => 'Please choose a semester',
        ]);

        if ($this->form_validation->run() == false) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                displayJson(false, $error);
                return;
            }
        }

        loadClass($this->load, 'students');
        $this->students->id = $studentID;
        if (!$this->students->load()) {
            displayJson(false, 'Invalid student info');
            return;
        }
        $semester = ($semester && $semester == 'first') ? 1 : 2;
        $paymentRecord = $this->students->hasPayment($studentID, $session, $semester, true);
        if (!$paymentRecord) {
            displayJson(false, "It appears the student has not paid school fee for the semester");
            return;
        }
        $academicRecord = $this->students->academic_record;
        $level = $paymentRecord ? $paymentRecord[0]['level'] : $academicRecord->current_level;
        $session = $session ?: $academicRecord->current_session;

        $courses = $this->students->getRegistrationCourses($studentID, $level, $academicRecord->programme_id, $academicRecord->entry_mode, $session, $semester);
        displayJson(true, 'Student courses fetched successfully', $courses);
    }

    /**
     * @return void
     */
    public function delete_student_registered_courses()
    {
        permissionAccess($this, 'student_delete_course_registration', 'delete');
        $studentID = $this->input->post('student_id', true);
        $courseID = $this->input->post('course_id', true);
        $sessionID = $this->input->post('course_session', true);
        $levelID = $this->input->post('course_level', true);

        $this->form_validation->set_rules('student_id', 'student', 'trim|required', [
            'required' => 'Please choose a student',
        ]);
        $this->form_validation->set_rules('course_id', 'course', 'trim|required');
        $this->form_validation->set_rules('course_session', 'course session', 'trim|required');
        $this->form_validation->set_rules('course_level', 'course level', 'trim|required');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                displayJson(false, $error);
                return;
            }
        }

        loadClass($this->load, 'students');
        loadClass($this->load, 'otp_code');
        loadClass($this->load, 'courses');
        $this->students->id = $studentID;
        if (!$this->students->load()) {
            displayJson(false, 'Invalid student info');
            return;
        }

        $courseHasScore = $this->students->courseHasScore($studentID, $courseID, $sessionID, $levelID);
        $currentUser = $this->webSessionManager->currentAPIUser();
        $username = $currentUser->user_login;

        $canDelete = !$courseHasScore;

        if ($canDelete && $this->courses->deleteCourseRegistration($studentID, $courseID, $sessionID, $levelID)) {
            // log the activity here
            $academicRecord = $this->students->academic_record;
            $course = $this->students->getCourseMappingDetails($academicRecord->programme_id, $courseID, $levelID);
            $date = date('Y-m-d H:i:s');
            $log = [
                'student_id' => $studentID,
                'course_id' => $courseID,
                'session_id' => $sessionID,
                'level' => $levelID,
                'username' => $username,
                'date_created' => $date,
                'operation' => 'remove_course_registration',
                'course_unit' => $course['course_unit'],
                'course_status' => $course['course_status'],
            ];
            $create_record = create_record($this, 'course_registration_log', $log);
            displayJson(true, $this->courses->getCourseById($courseID) . ' had been unregistered successfully');
            return;
        } else {
            displayJson(false, 'An error has occured, ' . $this->courses->getCourseById($courseID) . ' could not be unregistered');
            return;
        }
    }

    /**
     * @deprecated - Don't know if used again
     * @param $student_id
     * @param mixed $session_id
     * @param mixed $level_id
     * @param mixed $course_id
     * @param mixed $courseHasScore
     * @param mixed $tokenRequest
     * @param mixed $token
     * @return void|null@param mixed $student_id
     */
    private function processPreDelete($student_id, $session_id, $level_id, $course_id, $courseHasScore, $tokenRequest, $token)
    {
        return null;
        if (!$courseHasScore || ($tokenRequest || $token)) {
            return;
        }
        // send a message back to redirect to the page, that a token is required  after sending the token to the user
        $currentUser = $this->webSessionManager->currentAPIUser();
        $username = $currentUser->user_login;
        $code = $this->generateOTPCode();
        // save token to the database
        $param = array_merge(['code' => $code, 'username' => $username]);
        $create = create_record($this, 'otp_code', $param);
        // now send the token
        $email = $currentUser->user_email;
        $phone = $currentUser->user_phone;
        $variables = ['token' => $code];
        // $this->mailer->send_new_mail('course-registration-token', $email, $variables);
        // send sms to applicant
        // send_sms('delete-registration-token', $phone, $variables);
    }

    /**
     * @param mixed $num
     */
    private function generateOTPCode($num = 6)
    {
        $code = '';
        do {
            $code = generateCode($num);
        } while (rowExists($this, 'otp_code', ['code' => $code]));
        return $code;
    }

    /**
     * @return void
     */
    public function student_upload_passport()
    {
        permissionAccess($this, 'student_upload');
        $studentID = $this->input->post('student_id', true);

        $this->form_validation->set_rules('student_id', 'student', 'trim|required', [
            'required' => 'Please choose a student',
        ]);

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                displayJson(false, $error);
                return;
            }
        }

        loadClass($this->load, 'students');
        $this->students->id = $studentID;
        if (!$this->students->load()) {
            displayJson(false, 'Invalid student info');
            return;
        }
        $academicRecord = $this->students->academic_record;
        $lastname = $this->students->lastname;
        $firstname = $this->students->firstname;
        $passport = $this->students->passport;

        $config['upload_path'] = FCPATH . $this->config->item('student_passport_path');
        $config['file_name'] = strtolower($firstname . '_' . $lastname) . '_passport_' . time();
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['max_size'] = '100';
        $config['max_width'] = '200';
        $config['max_height'] = '200';
        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('student_passprt')) {
            // display error
            $error = $this->upload->display_errors('', '');
            displayJson(false, $error);
            return;
        }

        if (isset($_FILES['student_passprt']['name']) && !empty($_FILES['student_passprt']['name'])) {
            $fileName = $this->upload->data('file_name');
            $fileName = basename($fileName);
            $details = ['passport' => $fileName];
            $result = update_record($this, 'students', 'id', $studentID, $details);
            if (!$result) {
                displayJson(false, "Passport cannot be updated at this time, please try again");
                return;
            }
            $currentUser = $this->webSessionManager->currentAPIUser();
            logAction($this, 'student_passport_update', $currentUser->user_login);
            if ($passport) {
                $passport = basename($passport);
                $filename = FCPATH . $this->config->item('student_passport_path') . $passport;
                $this->deleteFile($filename);
            }
            displayJson(true, "Your passport has been updated");
            return;
        } else {
            displayJson(false, "Passport cannot be added at this time, an error occured");
            return;
        }
    }

    /**
     * @return void
     */
    public function student_upload_fullimage()
    {
        permissionAccess($this, 'student_upload');
        $studentID = $this->input->post('student_id', true);

        $this->form_validation->set_rules('student_id', 'student', 'trim|required', [
            'required' => 'Please choose a student',
        ]);

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                displayJson(false, $error);
                return;
            }
        }

        loadClass($this->load, 'students');
        $this->students->id = $studentID;
        if (!$this->students->load()) {
            displayJson(false, 'Invalid student info');
            return;
        }
        $academicRecord = $this->students->academic_record;
        $lastname = $this->students->lastname;
        $firstname = $this->students->firstname;
        $fullImage = $this->students->full_image;

        $config['upload_path'] = FCPATH . $this->config->item('student_fullimage_path');
        $config['file_name'] = strtolower($firstname . '_' . $lastname) . '_full_' . time();
        $config['allowed_types'] = 'gif|jpg|png|jpeg';
        $config['max_size'] = '200';
        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('student_fullimage')) {
            // display error
            $error = $this->upload->display_errors('', '');
            displayJson(false, $error);
            return;
        }

        if (!empty($_FILES['student_fullimage']['name'])) {
            $fileName = $this->upload->data('file_name');
            $fileName = basename($fileName);
            $details = ['full_image' => $fileName];
            $result = update_record($this, 'students', 'id', $studentID, $details);
            if (!$result) {
                displayJson(false, "Full image cannot be updated at this time, please try again");
                return;
            }
            $currentUser = $this->webSessionManager->currentAPIUser();
            logAction($this, 'student_full_image_update', $currentUser->user_login);
            if ($fullImage) {
                $passport = basename($fullImage);
                $filename = FCPATH . $this->config->item('student_fullimage_path') . $passport;
                $this->deleteFile($filename);
            }
            displayJson(true, "Your picture has been updated");
            return;
        } else {
            displayJson(false, "Full image cannot be added at this time, an error occured");
            return;
        }
    }

    /**
     * @return void
     */
    public function student_list_result()
    {
        $this->studentListResult();
    }

    /**
     * @return void
     */
    public function student_statement_result()
    {
        $this->studentStatementResult();
    }

    /**
     * @param mixed $filename
     */
    private function deleteFile($filename)
    {
        return deleteFile($filename);
    }

    public function download_template()
    {
        $model = request()->getGet('entity');
        if (!$model) {
            displayJson(false, 'Please provide a document name');
            return;
        }
        $this->load->model('entityCreator');
        return $this->entityCreator->template($model);
    }

    /**
     * @return void
     */
    public function active_banks()
    {
        $this->load->model('remita');
        $banks = $this->remita->getActiveBanks();
        if (isset($banks['status']) && !$banks['status']) {
            displayJson(false, $banks['message']);
            return;
        }

        if (!empty($banks)) {
            if ($this->db->truncate('bank_lists')) {
                foreach ($banks as $bank) {
                    $bankCode = (string)$bank['bankCode'];
                    $insertParam = [
                        'name' => $bank['bankName'],
                        'code' => strlen($bankCode) < 3 ? "0" . $bank['bankCode'] : $bank['bankCode'],
                        'status' => '1',
                        'slug' => $bank['bankAccronym'] ?? '',
                    ];
                    $this->db->insert('bank_lists', $insertParam);
                }

                displayJson(true, "You have successfully fetched active banks");
                return;
            }
        }
        displayJson(false, "Something went wrong, please try again later");
        return;
    }

    public function sch_fee_setting()
    {
        if (strtolower($this->input->method()) !== 'post') {
            return sendAPiResponse(false, 'Oops, you are not allowed to perform the operation');
        }
        permissionAccess($this, 'settings');
        $currentUser = $this->webSessionManager->currentAPIUser();
        loadClass($this->load, 'settings');

        $disable_all_school_fees = $this->input->post('disable_all_school_fees', true) ?: 0;
        $this->form_validation->set_rules('disable_all_school_fees', 'disable all student fees', 'trim');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                return sendAPiResponse(false, $error);
            }
        }

        // check if create method was successful
        $this->settings->registerSettings([
            'disable_all_school_fees' => $disable_all_school_fees,
        ]);
        logAction($this, 'sch_fees_setting', $currentUser->user_login);
        return sendAPiResponse(true, "Settings saved successfully");
    }

    /**
     * @return void
     */
    public function app_settings()
    {
        if (strtolower($this->input->method()) !== 'post') {
            return sendAPiResponse(false, 'Oops, you are not allowed to perform the operation');
        }

        permissionAccess($this, 'settings');
        $currentUser = $this->webSessionManager->currentAPIUser();
        loadClass($this->load, 'settings');
        // general settings
        $institution_name = $this->input->post('institution_name', true);
        $institution_address = $this->input->post('institution_address', true);
        $institution_phone = $this->input->post('institution_phone', true);
        $institution_email = $this->input->post('institution_primary_email', true);
        $timezone = $this->input->post('system_timezone', true);

        // Application (Admission) settings
        $active_session = $this->input->post('active_session', true);
        $application_code_prefix = $this->input->post('application_code_prefix', true);

        // Matric number setting
        $auto_gen_matric = $this->input->post('auto_generate_matric_number', true);
        $dept_zero_reset = $this->input->post('matric_dept_zero_reset', true);
        $dept_code = $this->input->post('matric_dept_code_format', true);
        $prog_zero_reset = $this->input->post('matric_prog_zero_reset', true);
        $prog_code = $this->input->post('matric_prog_code_format', true);
        $matric_number_prefix = $this->input->post('matric_number_prefix', true);
        $matric_number_format = $this->input->post('matric_number_format', true);
        $level = $this->input->post('matric_level_filter', true) ?: [];
        $entryMode = $this->input->post('matric_entry_mode_filter', true) ?: [];
        $level_to_include = $this->input->post('matric_level_to_include', true) ?: [];
        $entry_mode_to_include = $this->input->post('matric_entry_mode_to_include', true) ?: [];

        // Obtainable marks setting
        $continuous_assessment = $this->input->post('obtainable_ca_score', true);
        $examination = $this->input->post('obtainable_exam_score', true);

        // Payment Gateway
        $payment_gateway = $this->input->post('payment_gateway', true);
        $school_fees_code = $this->input->post('school_fees_code', true);

        // Messaging settings
        $email_server_url = $this->input->post('email_server_url', true);
        $email_server_port = $this->input->post('email_server_port', true);
        $email_server_username = $this->input->post('email_server_url_username', true);
        $email_server_password = $this->input->post('email_server_url_password', true);
        $email_domain_name = $this->input->post('email_domain_name', true);
        $email_domain = $this->input->post('email_domain', true);

        // SMS
        $sms_provider = $this->input->post('sms_provider', true);
        $sms_sender_name = $this->input->post('sender_name', true);

        // Auto create email
        $auto_gen_email = $this->input->post('auto_generate_email', true);
        $email_domain_address = $this->input->post('email_domain_address', true);
        $email_admin_account = $this->input->post('email_admin_account', true);

        // Portal Settings
        $portal_url = $this->input->post('portal_url', true);

        // Course reg settings
        $course_reg_status = $this->input->post('global_course_reg_status', true);
        $force_image_upload = $this->input->post('force_course_reg_image_upload', true);
        $course_reg_semester = $this->input->post('global_course_reg_semester_status', true);
        $active_semester = $this->input->post('active_semester', true);
        $active_session_student_portal = $this->input->post('active_session_student_portal', true);
        $session_semester_payment_start = $this->input->post('session_semester_payment_start', true);
        $active_admission_session = $this->input->post('active_admission_session', true);
        $admission_session_update = $this->input->post('admission_session_update', true);

        // applicant settings
        $institution_learnersupport_email = $this->input->post('institution_learnersupport_email', true);
        $institution_website = $this->input->post('institution_website', true);
        $institution_ihelp = $this->input->post('institution_ihelp', true);
        $institution_facebook = $this->input->post('institution_facebook', true);
        $institution_twitter = $this->input->post('institution_twitter', true);
        $institution_whatsapp = $this->input->post('institution_whatsapp', true);
        $institution_youtube = $this->input->post('institution_youtube', true);
        $institution_whatsapp_widget = $this->input->post('institution_whatsapp_widget', true);
        $application_portal_title = $this->input->post('application_portal_title', true);
        $apply_portal_notice = $this->input->post('apply_portal_notice', true);
        $faqs = $this->input->post('faqs', true);
        $global_course_unreg_status = $this->input->post('global_course_unreg_status', true);

        // remita setting
        $remita_gateway_status = $this->input->post('remita_gateway_status', true);
        // $remita_merchant_id    = $this->input->post('remita_merchant_id', TRUE);
        // $remita_api_key    = $this->input->post('remita_api_key', TRUE);
        // $remita_public_key    = $this->input->post('remita_public_key', TRUE);
        // $remita_secret_key    = $this->input->post('remita_secret_key', TRUE);
        // fees settings
        $disable_all_student_fees = $this->input->post('disable_all_student_fees', true) ?: 0;
        $disable_all_non_student_fees = $this->input->post('disable_all_non_student_fees', true) ?: 0;
        $disable_all_school_fees = $this->input->post('disable_all_school_fees', true) ?: 0;
        $disable_all_sundry_fees = $this->input->post('disable_all_sundry_fees', true) ?: 0;

        $apply_student_notice_header = $this->input->post('apply_student_notice_header', true);
        $apply_student_notice_body = $this->input->post('apply_student_notice_body', true);

        $institution_logo = $this->settings->getInstitiutionLogo('institution_logo');

        $config['upload_path'] = FCPATH . 'assets/images';
        $config['file_name'] = 'institution_logo_' . time();
        $config['allowed_types'] = 'jpg|png|jpeg';
        $config['max_size'] = '1024';
        $config['max_width'] = '750';
        $config['max_height'] = '750';

        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        // validate the settings
        $this->form_validation->set_rules('institution_name', 'institution name', 'trim');
        $this->form_validation->set_rules('institution_address', 'institution address', 'trim');
        $this->form_validation->set_rules('institution_phone', 'phone number', 'trim');
        $this->form_validation->set_rules('institution_primary_email', 'email', 'trim|valid_email');
        $this->form_validation->set_rules('system_timezone', 'timezone', 'trim');

        $this->form_validation->set_rules('active_session', 'active session', 'trim');
        $this->form_validation->set_rules('application_code_prefix', 'application code prefix', 'trim');

        $this->form_validation->set_rules('auto_generate_matric_number', 'auto-matric. number generation', 'trim');
        $this->form_validation->set_rules('matric_dept_zero_reset', 'department reset number position', 'trim');
        ($dept_zero_reset == 'yes') ? $this->form_validation->set_rules('matric_dept_code_format', 'department code', 'trim|required') : $this->form_validation->set_rules('matric_dept_code_format', 'department code', 'trim');
        $this->form_validation->set_rules('matric_prog_zero_reset', 'programme reset number position', 'trim');
        ($dept_zero_reset == 'yes') ? $this->form_validation->set_rules('matric_prog_code_format', 'programme code', 'trim|required') : $this->form_validation->set_rules('matric_prog_code_format', 'programme code', 'trim');
        $this->form_validation->set_rules('matric_number_prefix', 'matric. number prefix', 'trim');
        $this->form_validation->set_rules('matric_number_format', 'matric. number format', 'trim');
        $this->form_validation->set_rules('matric_level_filter', 'level', 'trim');
        $this->form_validation->set_rules('matric_entry_mode_filter', 'entry mode', 'trim');
        $this->form_validation->set_rules('matric_level_to_include', 'level', 'trim');
        $this->form_validation->set_rules('matric_entry_mode_to_include', 'entry mode', 'trim');

        $this->form_validation->set_rules('obtainable_ca_score', 'continuous assessment score', 'trim|numeric|is_natural|max_length[2]');
        $this->form_validation->set_rules('obtainable_exam_score', 'exam score', 'trim|numeric|is_natural|max_length[2]|less_than_equal_to[99]');

        $this->form_validation->set_rules('payment_gateway', 'payment gateway', 'trim');
        $this->form_validation->set_rules('school_fees_code', 'school fees code', 'trim');

        $this->form_validation->set_rules('email_server_url', 'SMTP server url', 'trim');
        $this->form_validation->set_rules('email_server_port', 'SMTP server port', 'trim|numeric|is_natural');
        $this->form_validation->set_rules('email_server_url_username', 'SMTP server username', 'trim');
        $this->form_validation->set_rules('email_server_url_password', 'SMTP server password', 'trim');
        $this->form_validation->set_rules('email_domain_name', 'email domain name', 'trim');
        $this->form_validation->set_rules('email_domain', 'email domain', 'trim');

        $this->form_validation->set_rules('sms_provider', 'SMS provider', 'trim');
        $this->form_validation->set_rules('sender_name', 'SMS sender\'s name', 'trim|max_length[11]');

        $this->form_validation->set_rules('auto_generate_email', 'auto create email', 'trim');
        $this->form_validation->set_rules('email_domain_address', 'email domain address', 'trim');
        $this->form_validation->set_rules('email_admin_account', 'email admin account', 'trim');

        $this->form_validation->set_rules('portal_url', 'portal url', 'trim');

        // course reg settings
        $this->form_validation->set_rules('global_course_reg_status', 'disable course reg.', 'trim');
        $this->form_validation->set_rules('global_course_unreg_status', 'disable course del. reg.', 'trim');
        $this->form_validation->set_rules('force_course_reg_image_upload', 'force course reg. image upload', 'trim');
        $this->form_validation->set_rules('global_course_reg_semester_status', 'disable course reg. semester', 'trim|required');
        $this->form_validation->set_rules('active_semester', 'active semester', 'trim|required');
        $this->form_validation->set_rules('active_session_student_portal', 'active session student portal', 'trim|required');
        $this->form_validation->set_rules('session_semester_payment_start', 'session semester payment start', 'trim|required');
        $this->form_validation->set_rules('active_admission_session', 'active admission session', 'trim|required');
        $this->form_validation->set_rules('admission_session_update', 'admission session update', 'trim|required');
        $this->form_validation->set_rules('institution_learnersupport_email', 'institution learnersupport email', 'trim');
        $this->form_validation->set_rules('institution_website', 'institution website', 'trim');
        $this->form_validation->set_rules('institution_ihelp', 'institution ihelp', 'trim');
        $this->form_validation->set_rules('institution_ihelp', 'institution ihelp', 'trim');
        $this->form_validation->set_rules('institution_facebook', 'institution facebook', 'trim');
        $this->form_validation->set_rules('institution_twitter', 'institution twitter', 'trim');
        $this->form_validation->set_rules('institution_whatsapp', 'institution whatsapp', 'trim');
        $this->form_validation->set_rules('institution_youtube', 'institution youtube', 'trim');
        $this->form_validation->set_rules('institution_whatsapp_widget', 'institution whatsapp widget', 'trim');
        $this->form_validation->set_rules('application_portal_title', 'application portal title', 'trim');
        $this->form_validation->set_rules('apply_portal_notice', 'apply portal notice', 'trim');
        $this->form_validation->set_rules('apply_student_notice_header', 'apply student notice header', 'trim');
        $this->form_validation->set_rules('apply_student_notice_body', 'apply student notice body', 'trim');
        $this->form_validation->set_rules('faqs', 'faqs', 'trim');

        $this->form_validation->set_rules('remita_gateway_status', 'remita gateway status', 'trim|required');
        // $this->form_validation->set_rules('remita_merchant_id', 'remita merchant ID', 'trim|required' );
        // $this->form_validation->set_rules('remita_api_key', 'remita api ket', 'trim|required' );
        // $this->form_validation->set_rules('remita_public_key', 'remita public key', 'trim|required' );
        // $this->form_validation->set_rules('remita_secret_key', 'remita secret key', 'trim|required' );

        $this->form_validation->set_rules('disable_all_non_student_fees', 'disable all non student fees', 'trim');
        $this->form_validation->set_rules('disable_all_school_fees', 'disable all school fees', 'trim');
        $this->form_validation->set_rules('disable_all_sundry_fees', 'disable all sundry fees', 'trim');
        $this->form_validation->set_rules('disable_all_student_fees', 'disable all student fees', 'trim');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                return sendAPiResponse(false, $error);
            }
        }

        if (!empty($_FILES['institution_logo']['name'])) {
            // upload logo
            if (!$this->upload->do_upload('institution_logo')) {
                // display error
                $error = $this->upload->display_errors('', '');
                return sendAPiResponse(false, $error);
            }
            // get uploaded data file name
            $institutionLogo = $this->upload->data('file_name');
        } else {
            $institutionLogo = "";
        }

        $institutionLogo = ($institutionLogo != null) ? $institutionLogo : $institution_logo;
        $old_logo = $_SERVER["DOCUMENT_ROOT"] . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . $institution_logo;

        if ($institutionLogo != $institution_logo && file_exists($old_logo)) {
            unlink($old_logo);
        }

        $settings_data = [
            'institution_name' => $institution_name,
            'institution_address' => $institution_address,
            'institution_phone' => $institution_phone,
            'institution_primary_email' => $institution_email,
            'system_timezone' => $timezone,
            'active_session' => $active_session,
            'application_code_prefix' => $application_code_prefix,
            'auto_generate_matric_number' => $auto_gen_matric,
            'matric_dept_zero_reset' => $dept_zero_reset,
            'matric_dept_code_format' => $dept_code,
            'matric_prog_zero_reset' => $prog_zero_reset,
            'matric_prog_code_format' => $prog_code,
            'matric_number_prefix' => $matric_number_prefix,
            'matric_number_format' => $matric_number_format,
            'matric_level_filter' => json_encode($level),
            'matric_entry_mode_filter' => json_encode($entryMode),
            'matric_level_to_include' => json_encode($level_to_include),
            'matric_entry_mode_to_include' => json_encode($entry_mode_to_include),
            'obtainable_ca_score' => $continuous_assessment,
            'obtainable_exam_score' => $examination,
            'institution_logo' => $institutionLogo,
            'payment_gateway' => $payment_gateway,

            'school_fees_code' => $school_fees_code,
            'email_server_url' => $email_server_url,
            'email_server_port' => $email_server_port,
            'email_server_url_username' => $email_server_username,
            'email_server_url_password' => $email_server_password,
            'email_domain_name' => $email_domain_name,
            'email_domain' => $email_domain,
            'sms_provider' => $sms_provider,
            'sender_name' => $sms_sender_name,
            'auto_generate_email' => $auto_gen_email,
            'email_domain_address' => $email_domain_address,
            'email_admin_account' => $email_admin_account,
            'portal_url' => $portal_url,

            'global_course_reg_status' => $course_reg_status,
            'force_course_reg_image_upload' => $force_image_upload,
            'global_course_reg_semester_status' => $course_reg_semester,
            'active_semester' => $active_semester,
            'active_session_student_portal' => $active_session_student_portal,
            'session_semester_payment_start' => $session_semester_payment_start,
            'active_admission_session' => $active_admission_session,
            'admission_session_update' => $admission_session_update,

            'institution_learnersupport_email' => $institution_learnersupport_email,
            'institution_website' => $institution_website,
            'institution_ihelp' => $institution_ihelp,
            'institution_facebook' => $institution_facebook,
            'institution_twitter' => $institution_twitter,
            'institution_whatsapp' => $institution_whatsapp,
            'institution_youtube' => $institution_youtube,
            'institution_whatsapp_widget' => $institution_whatsapp_widget,
            'application_portal_title' => $application_portal_title,
            'apply_portal_notice' => $apply_portal_notice,
            'apply_student_notice_header' => $apply_student_notice_header,
            'apply_student_notice_body' => $apply_student_notice_body,
            'faqs' => $faqs,
            'global_course_unreg_status' => $global_course_unreg_status,

            'remita_gateway_status' => $remita_gateway_status,
            // 'remita_merchant_id'    		=> $remita_merchant_id,
            // 'remita_api_key'    			=> $remita_api_key,
            // 'remita_public_key'    			=> $remita_public_key,
            // 'remita_secret_key'    			=> $remita_secret_key,

            'disable_all_non_student_fees' => $disable_all_non_student_fees,
            'disable_all_school_fees' => $disable_all_school_fees,
            'disable_all_sundry_fees' => $disable_all_sundry_fees,
            'disable_all_student_fees' => $disable_all_student_fees,
        ];

        // check if create method was successful
        $this->settings->registerSettings($settings_data);
        logAction($this, 'register_settings', $currentUser->user_login);
        return sendAPiResponse(true, "Settings saved successfully");
    }

    public function user_activity()
    {
        loadClass($this->load, 'users_new');
        $user = request()->getGet('orig_user_id', true);

        $this->form_validation->set_data([
            'orig_user_id' => $user,
        ]);
        $this->form_validation->set_rules('orig_user_id', 'user', 'trim|required', [
            'required' => 'Please provide a valid user',
        ]);
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                return sendAPiResponse(false, $error);
            }
        }
        $user = $this->users_new->getUserByID($user, true);
        $logs = $this->users_new->getUserLog($user);
        return sendAPiResponse(true, 'success', $logs);
    }

    public function assign_role()
    {
        permissionAccess($this, 'user_role');
        $currentUser = $this->webSessionManager->currentAPIUser();
        loadClass($this->load, 'roles');

        $this->form_validation->set_rules('role_id', 'role', 'trim|required|alpha_numeric');
        $this->form_validation->set_rules('orig_user_id', 'user', 'trim|required|alpha_numeric', [
            'required' => 'Please select a user to assign a role.',
        ]);
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                return sendAPiResponse(false, $error);
            }
        }
        $role = $this->input->post('role_id', true);
        $user = $this->input->post('orig_user_id', true);
        $message = "Your role has been updated successfully";
        if ($this->roles->getUserRole($user)) {
            if (!update_record($this, 'roles_user', 'user_id', $user, ['role_id' => $role])) {
                return sendAPiResponse(false, "Role could not be assigned at this time, please try again ");
            }
        } else {
            $details = [
                'user_id' => $user,
                'role_id' => $role,
            ];
            if (!create_record($this, 'roles_user', $details)) {
                return sendAPiResponse(false, "Role could not be assigned at this time, please try again ");
            }
            $message = "Role has been created successfully ";
        }

        logAction($this, 'assign_user_role', $currentUser->user_login);
        return sendAPiResponse(true, $message);
    }

    public function assign_user_departmental()
    {
        permissionAccess($this, 'user_role');
        $currentUser = $this->webSessionManager->currentAPIUser();

        $this->form_validation->set_rules('department_id', 'department', 'trim|required');
        $this->form_validation->set_rules('user_id', 'user', 'trim|required|alpha_numeric', [
            'required' => 'Please select a user to assign to a department.',
        ]);
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                return sendAPiResponse(false, $error);
            }
        }

        $department = $this->input->post('department_id', true);
        $user = $this->input->post('user_id', true);
        $message = "You've successfully assigned user to a department";

        if ($department === 'not_department') {
            $department = null;
            $message = "You've successfully unassign the user on a department";
        }

        if (!update_record($this, 'staffs', 'id', $user, [
            'user_department' => $department,
        ])) {
            return sendAPiResponse(false, "Unable to assign user to department at this time, please try again.");
        }
        logAction($this, 'assign_user_departmental', $currentUser->user_login);
        return sendAPiResponse(true, $message);
    }

    public function assign_user_outflow()
    {
        permissionAccess($this, 'user_role');
        $currentUser = $this->webSessionManager->currentAPIUser();

        $this->form_validation->set_rules('name', 'name', 'trim|required|in_list[dir,db,db-staff,aud,proc,not_outflow]', [
            'in_list' => 'Please provide a valid name',
        ]);
        $this->form_validation->set_rules('user_id', 'user', 'trim|required|alpha_numeric', [
            'required' => 'Please select a user to assign to outflow admin.',
        ]);
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                return sendAPiResponse(false, $error);
            }
        }

        $name = $this->input->post('name', true);
        $user = $this->input->post('user_id', true);
        $message = "You've successfully assigned user to outflow admin";

        if ($name === 'not_outflow') {
            $name = null;
            $message = "You've successfully unassign the user on outflow admin";
        }

        if (!update_record($this, 'staffs', 'id', $user, [
            'outflow_slug' => $name,
        ])) {
            return sendAPiResponse(false, "Unable to assign user to outflow admin at this time, please try again.");
        }
        logAction($this, 'assign_outflow_admin', $currentUser->user_login);
        return sendAPiResponse(true, $message);
    }

    public function user_upload_passport()
    {
        $user = $this->input->post('user_id', true);
        $currentUser = $this->users_new->getUserInfo('staffs', 'staff', $user);
        $lastname = trim($currentUser['lastname']);
        $firstname = trim($currentUser['firstname']);
        $passport = $currentUser['avatar'];

        $this->form_validation->set_rules('user_id', 'user', 'trim|required', [
            'required' => 'Please select a user to upload their image.',
        ]);
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                return sendAPiResponse(false, $error);
            }
        }

        $config['upload_path'] = FCPATH . $this->config->item('user_passport_path');
        $config['file_name'] = strtolower($firstname . '_' . $lastname) . '_avatar_' . time();
        $config['allowed_types'] = 'jpg|png|jpeg';
        $config['max_size'] = '150';
        $config['max_width'] = '750';
        $config['max_height'] = '750';
        $this->load->library('upload', $config);
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('user_passport')) {
            $error = $this->upload->display_errors('', '');
            return sendAPiResponse(false, $error);
        }

        if (isset($_FILES['user_passport']['name']) && !empty($_FILES['user_passport']['name'])) {
            $fileName = basename($this->upload->data('file_name'));
            $details = ['avatar' => $fileName];
            $result = update_record($this, 'staffs', 'id', $user, $details);
            if (!$result) {
                $filename = FCPATH . $this->config->item('user_passport_path') . $fileName;
                deleteFile($filename);
                return sendAPiResponse(false, "Image cannot be updated at this time, please try again");
            }
            if ($passport) {
                $passport = basename($passport);
                $filename = FCPATH . $this->config->item('user_passport_path') . $passport;
                deleteFile($filename);
            }
            return sendAPiResponse(true, "User image has been successfully uploaded", [
                'avatar' => userImagePath($this, $fileName),
            ]);
        } else {
            return sendAPiResponse(false, "Image can not be added at this time, an error occurred");
        }
    }

    public function get_all_users()
    {
        loadClass($this->load, 'users_new');
        $payload = $this->users_new->getAllUsers();

        return sendAPiResponse(true, 'success', $payload);
    }

    /**
     * @return void
     */
    public function applicant_admission()
    {
        loadClass($this->load, 'admission');
        $admissions = $this->admission->getWhereNonObject(['active' => 1], $c, 0, null, false);
        $admissions = $admissions ?: [];
        displayJson(true, "You've successfully fetched admission", $admissions);
        return;
    }

    /**
     * This script is to generate matric number for student who had paid based on the current session
     * @return void
     * @throws Exception
     * @deprecated
     */
    public function generate_student_matric()
    {
        loadClass($this->load, 'students');

        $generated = false;
        $studentNoMatric = $this->students->getAllStudentWithNoMatric();
        $message = "You have successfully run the generated script";
        $matricString = '';
        if ($studentNoMatric) {
            foreach ($studentNoMatric as $student) {
                $student = new Students($student);
                $matric = $this->students->autoGenerateMatricNumber($student);
                if ($matric) {
                    $this->students->autoGenerateInstitutionalEmail($student, $matric);
                }
                $generated = true;
                $matricString .= ($matricString) ? ',' : '' . $student->user_login;
            }
        }
        if ($generated) {
            $message = "Matric number successfully generated the for the following students {$matricString}";
        }
        displayJson(true, $message);
    }

    public function get_bank_details()
    {
        $this->getBankDetails();
    }

    public function bank_as_primary()
    {
        $this->bankAsPrimary();
    }

    public function account_name_enquiry()
    {
        $this->accountNameEnquiry();
    }

    public function bank_code()
    {
        $this->accountBankCode();
    }

    public function delete_remita_transaction()
    {
        $type = $this->input->post('type', true);
        $transactionRef = $this->input->post('transaction_ref', true);

        $this->form_validation->set_rules('type', 'type', 'trim|required', [
            'required' => 'Please provide a valid log type',
        ]);
        $this->form_validation->set_rules('transaction_ref', 'transaction reference', 'trim|required', [
            'required' => 'Please provide a valid transaction reference',
        ]);
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                return sendAPiResponse(false, $error);
            }
        }

        loadClass($this->load, 'transaction');
        $transType = 'student_trans';
        if ($type === 'students') {
            $transactionData = $this->transaction->getWhere(['transaction_ref' => $transactionRef], $c, 0, null, false);
            if (!$transactionData) {
                return sendAPiResponse(false, "Transaction no longer available");
            }
            $transactionData = $transactionData[0];
        } else if ($type === 'non-students') {
            loadClass($this->load, 'transaction_custom');
            $transactionData = $this->transaction_custom->getWhere(['transaction_ref' => $transactionRef], $c, 0, null, false);
            if (!$transactionData) {
                return sendAPiResponse(false, "Transaction no longer available");
            }
            $transactionData = $transactionData[0];
            $transType = 'custom_trans';
        } else if ($type === 'applicants') {
            loadClass($this->load, 'applicant_transaction');
            $transactionData = $this->applicant_transaction->getWhere(['transaction_ref' => $transactionRef], $c, 0, null, false);
            if (!$transactionData) {
                return sendAPiResponse(false, "Transaction no longer available");
            }
            $transactionData = $transactionData[0];
            $transType = 'admission_trans';
        }

        if (!CommonTrait::isPaymentValid($transactionData->payment_status)) {
            return $this->transaction->delete($transactionData->id, $this->db, $transType);
        }

        return sendAPiResponse(false, 'Transaction may have already been resolved');
    }

    public function topup_fee_session()
    {
        permissionAccess($this, 'student_topup');

        $studentID = request()->getGet('student_id', true);
        $this->form_validation->set_data([
            'student_id' => $studentID,
        ]);
        $this->form_validation->set_rules('student_id', 'student', 'trim|required', [
            'required' => 'Please provide a student',
        ]);

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                return sendAPiResponse(false, $error);
            }
        }

        loadClass($this->load, 'students');
        $this->students->id = $studentID;
        if (!$this->students->load()) {
            return sendAPiResponse(false, 'Invalid student info');
        }
        $activeSemester = get_setting('active_semester');
        $semesterName = ($activeSemester && $activeSemester == '1') ? 'first' : 'second';
        $sessions = $this->students->getAllPaidTransactionSession(false, $semesterName);

        return sendAPiResponse(true, 'success', $sessions);
    }

    private function buildPaymentDescription($description, $semester, $currentSession, $prevSession)
    {
        $currentSession = $this->sessions->getSessionById($currentSession);
        $prevSession = $this->sessions->getSessionById($prevSession);

        if ($semester) {
            $sem = $semester == 1 ? '1st Semester' : '2nd Semester';
            $description = $sem . ' ' . $description;
        }

        if ($currentSession) {
            $description .= ' (' . $currentSession[0]['date'] . ')';
        }

        if ($prevSession) {
            $description .= ' From ' . $prevSession[0]['date'];
        }

        return $description;
    }

    private function calcStudentFormerPaymentAmount($studentID, $sessionID, $paymentID)
    {
        $studentTransaction = $this->students->checkStudentPaymentBySession($studentID, $sessionID, $paymentID);
        if (!isPaymentComplete($studentTransaction['payment_option'])) {
            $paymentCode = inferPaymentCode($paymentID);
            $checkPaymentTransaction = $this->payment->getPaymentTransaction($paymentID, $studentID, $sessionID, null, $paymentCode);
            if ($checkPaymentTransaction) {
                $totalAmount = $studentTransaction['amount_paid'] + $checkPaymentTransaction->amount_paid;
                $rrrCode = $studentTransaction['rrr_code'] . "::" . $checkPaymentTransaction->rrr_code;
                $studentTransaction['amount_paid'] = $totalAmount;
                $studentTransaction['rrr_code'] = $rrrCode;
            }
        }

        return $studentTransaction;
    }

    public function topup_fee_payment_detail()
    {
        loadClass($this->load, 'payment');
        loadClass($this->load, 'fee_description');
        loadClass($this->load, 'students');
        loadClass($this->load, 'sessions');

        $studentID = request()->getGet('student_id');
        $sessionID = request()->getGet('session_id');
        $this->form_validation->set_data([
            'student_id' => $studentID,
            'session_id' => $sessionID,
        ]);
        $this->form_validation->set_rules('student_id', 'student', 'trim|required', [
            'required' => 'Please provide a student',
        ]);
        $this->form_validation->set_rules('session_id', 'session', 'trim|required', [
            'required' => 'Please provide a session',
        ]);

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                return sendAPiResponse(false, $error);
            }
        }

        $this->students->id = $studentID;
        if (!$this->students->load()) {
            return sendAPiResponse(false, 'Invalid student info');
        }
        $academicRecord = $this->students->academic_record;
        $activeSemester = get_setting('active_semester');
        $paymentID = $activeSemester == 1 ? PaymentFeeDescription::SCH_FEE_FIRST : PaymentFeeDescription::SCH_FEE_SECOND;
        $studentTransaction = $this->calcStudentFormerPaymentAmount($studentID, $sessionID, $paymentID);
        $paymentDetail = $this->payment->getPaymentByDescriptionCode(FeeDescriptionCode::TOPUP_FEE);
        $description = $this->buildPaymentDescription($paymentDetail['description'], $activeSemester, $academicRecord->current_session, $sessionID);

        $param = [
            $academicRecord->current_session,
            $academicRecord->programme_id,
            $academicRecord->current_level,
            $academicRecord->entry_mode,
            $academicRecord->programme_id,
            $academicRecord->current_level,
            $academicRecord->entry_mode,
            $activeSemester
        ];
        $newPayment = $this->students->loadMainFees($academicRecord, $param);
        if (!$newPayment) {
            return sendAPiResponse(false, 'Unable to get student new payment info');
        }
        $newPayment = $newPayment[0];
        $newAmount = $newPayment['total'];
        $serviceCharge = $newPayment['service_charge'];
        $formerAmount = $studentTransaction['amount_paid'];
        $topupAmount = ($newAmount - $formerAmount) + $serviceCharge;
        $payload = [
            'fee_description' => [
                'id' => hashids_encrypt($paymentDetail['payment_id']),
                'real_payment_id' => hashids_encrypt($paymentDetail['real_payment_id']),
                'description' => $description,
            ],
            'payment' => [
                'rrr_code' => $studentTransaction['rrr_code'],
                'prev_amount' => (int)$formerAmount,
                'current_amount' => $newAmount,
                'topup_without_service_charge' => $topupAmount - $serviceCharge,
                'service_charge' => $serviceCharge,
                'topup_amount' => $topupAmount,
            ]
        ];
        if ($topupAmount < 0) {
            return sendAPiResponse(false, 'Student has already paid the required fee', $payload);
        }

        return sendAPiResponse(true, 'success', $payload);
    }

    public function create_topup_fee()
    {
        permissionAccess($this, 'student_topup', 'create');

        loadClass($this->load, 'payment');
        loadClass($this->load, 'fee_description');
        loadClass($this->load, 'students');
        loadClass($this->load, 'transaction');

        $studentID = $this->input->post('student_id', true);
        $paymentID = $this->input->post('real_payment_id', true);
        $serviceCharge = $this->input->post('service_charge', true);
        $totalAmount = $this->input->post('total_amount', true);
        $feeDescription = $this->input->post('fee_description', true);
        $topAmount = $this->input->post('topup_amount', true);

        $this->form_validation->set_rules('student_id', 'student', 'trim|required', [
            'required' => 'Please provide a student',
        ]);
        $this->form_validation->set_rules('real_payment_id', 'payment', 'trim|required', [
            'required' => 'Please provide a valid payment type',
        ]);
        $this->form_validation->set_rules('service_charge', 'service charge', 'trim|required', [
            'required' => 'Please provide the service charge',
        ]);
        $this->form_validation->set_rules('total_amount', 'total amount', 'trim|required', [
            'required' => 'Please provide the total amount',
        ]);
        $this->form_validation->set_rules('topup_amount', 'topup amount', 'trim|required', [
            'required' => 'Please provide a valid topup amount',
        ]);

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                return sendAPiResponse(false, $error);
            }
        }

        $this->students->id = $studentID;
        if (!$this->students->load()) {
            return sendAPiResponse(false, 'Invalid student info');
        }

        if ($totalAmount < $topAmount) {
            return sendAPiResponse(false, 'Total amount can not be less than the topup amount');
        }
        $students = $this->students;
        $academicRecord = $this->students->academic_record;
        $activeSemester = get_setting('active_semester');
        $currentUser = $this->webSessionManager->currentAPIUser();

        $paymentDescription = $activeSemester == 1 ? PaymentFeeDescription::SCH_FEE_FIRST : PaymentFeeDescription::SCH_FEE_SECOND;
        $paymentDetail = $this->payment->getPaymentByDescriptionCode(FeeDescriptionCode::TOPUP_FEE_BAL);
        $paymentID = hashids_decrypt($paymentID);
        $payment = $this->payment->getPaymentById($paymentID);
        if (!$payment) {
            return sendAPiResponse(false, 'Invalid payment info');
        }
        $activeSession = $academicRecord->current_session ?: get_setting('active_session_student_portal');
        $phone = padPhoneNumber(decryptData($this, $students->phone));

        $prerequisites = $students->validateSpecialPaymentPrequisites($academicRecord, $payment, $activeSemester);
        if (!empty($prerequisites)) {
            foreach ($prerequisites as $item) {
                if (!$item['paid']) {
                    $message = "The student has an outstanding payment from previous session. Please visit the student dashboard to settle it.";
                    return sendAPiResponse(false, $message);
                }
            }
        }

        $users = [
            'id' => $students->id,
            'name' => $students->firstname . ' ' . $students->lastname . ' ' . $students->othernames,
            'phone_number' => $phone,
            'email' => $students->user_login,
            'matric' => $academicRecord->matric_number,
            'programme_id' => $academicRecord->programme_id,
            'session' => $activeSession,
            'current_level' => $academicRecord->current_level,
            'service_type_id' => @$paymentDetail['service_type_id'] ?: null,
        ];
        $users = (object)$users;
        $param = [
            'requery' => false,
            'description' => $feeDescription,
            'amount' => $totalAmount - $serviceCharge,
            'serviceCharge' => $serviceCharge,
            'total' => $totalAmount,
            'payment_id' => $paymentDescription,
            'real_payment_id' => $paymentID,
            'transaction_type' => 'top_up',
        ];

        $transaction = $this->transaction->getWhere([
            'student_id' => $students->id,
            'session' => $activeSession,
            'payment_id' => $paymentDescription,
            'real_payment_id' => $paymentID,
        ], $c, 0, 1, false);
        if ($transaction) {
            $param['requery'] = true;
            $transaction = $transaction[0];
        }

        $payment_channel = get_setting('payment_gateway');
        $initDetails = $this->payment->customInitPayment($users, $payment_channel, $param, $transaction ?: null);
        if (isset($initDetails['status']) && !$initDetails['status']) {
            return sendAPiResponse(false, $initDetails['message']);
        }

        if (!$initDetails['transaction_obj']) {
            $rrr = $initDetails['rrr'];
            $tranx = $this->transaction->getWhere(['rrr_code' => $rrr], $c, 0, 1, false);
            if (!$tranx) {
                return sendAPiResponse(false, 'Transaction not available');
            }
            $initDetails['transaction_obj'] = $tranx[0];
        }
        $transaction = $initDetails['transaction_obj'];
        $publicKey = isLive() ?
            get_setting('remita_public_key') :
            $this->config->item('remita_public_key');
        $payload = array(
            'transaction_id' => $transaction->id,
            'payment_id' => $transaction->payment_id,
            'description' => $transaction->payment_description,
            'payment_option' => $transaction->payment_option,
            'session' => $transaction->sessions->date,
            'level' => formatStudentLevel($transaction->level),
            'transaction_ref' => $transaction->transaction_ref,
            'rrr' => $transaction->rrr_code,
            'payment_status' => $transaction->payment_status,
            'payment_status_description' => $transaction->payment_status_description,
            'amount_paid' => $transaction->amount_paid,
            'total_amount' => $transaction->total_amount,
            'date_performed' => formatPaymentDate($transaction->date_performed),
            'date_completed' => formatPaymentDate($transaction->date_completed),
            'public_key' => $publicKey,
        );

        logAction($this, 'create_topup_fee', $currentUser->user_login, $studentID, null, json_encode($payload));
        return sendAPiResponse(true, 'success', $payload);
    }

    public function custom_transaction_status()
    {
        loadClass($this->load, 'payment');
        loadClass($this->load, 'users_custom');
        loadClass($this->load, 'transaction_custom');

        $userID = request()->getGet('user_id');
        $ref = request()->getGet('transaction_ref');

        $users = $this->users_custom->getWhere(['id' => $userID], $c, 0, 1, false);
        if (!$users) {
            return sendAPiResponse(false, 'User not found');
        }
        $users = $users[0];
        $transaction = $this->transaction_custom->getWhere(['transaction_ref' => $ref], $c, 0, 1, false);
        if (!$transaction) {
            return sendAPiResponse(false, 'transaction not found');
        }
        $transaction = $transaction[0];
        $remita = $this->payment->getCustomPaymentDetails($users, 'remita', $transaction);
        if (!$remita['status']) {
            return sendAPiResponse(false, $remita['message']);
        }

        $payload = $remita;
        $payload['transaction_obj'] = $remita['transaction_obj']->toArray();
        return sendAPiResponse(true, 'success', $payload);
    }


    /**
     * @return void
     */
    public function test_password()
    {

        dddump(hashids_decrypt($this->input->post('input')));

        $this->load->model('googleService');
        $matric_number = 'E10';
        $student = ['lastname' => 'Service', 'firstname' => 'Edutech'];

        $new_email = strtolower($matric_number . '.' . $student['lastname'] . '@' . get_setting('email_domain_address'));
        // dddump($new_email);

        // send new email to gsuite for creation
        $institution_email = GoogleService::createInstitutionEmail($student['lastname'], $student['firstname'], $new_email);
        dddump($institution_email);

        echo gmdate('Y-m-d\TH:i:s\Z');
        exit;
        echo substr_replace(gmdate('c'), 'Z', -6) . "\n";
        echo gmdate('Y-m-d\TH:i:s\Z');

        echo hashids_decrypt('N0kO3DBAX6GPR6xqg479');
        exit;
    }

}
