<?php
namespace App\Entities;

use App\Models\Crud;
use App\Libraries\EntityLoader;

/**
 * The controller that validate forms that should be inserted into a table based on the request url.
 * each method wil have the structure validate[Entity]Data
 */
class ModelControllerDataValidator extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('webSessionManager');
        $this->load->helper('hashids');
    }

    public function validateStudent_verification_documentsData(&$data, $type, &$db, &$message): bool
    {

        if ($type == 'insert') {
            if (empty($_FILES)) {
                $message = "Please choose a file to upload";
                return false;
            }

            EntityLoader::loadClass($this, 'student_verification_documents');
            $student = WebSessionManager::currentAPIUser();
            if (isset($data['verification_documents_requirement_id'])) {
                $validate = $this->student_verification_documents->getWhere(['students_id' => $student->id, 'verification_documents_requirement_id' => $data['verification_documents_requirement_id']]);
                if ($validate) {
                    $message = "You have previously uploaded this document, kindly use the reupload button";
                    return false;
                }
            }

            if (isset($data['other'])) {
                $validate = $this->student_verification_documents->getWhere(['students_id' => $student->id, 'other' => $data['other']]);
                if ($validate) {
                    $message = "You have previously uploaded this document, kindly use the reupload button";
                    return false;
                }
            }

            if (isset($data['other']) && $data['other'] != '') {
                $data['verification_documents_requirement_id'] = null;
            }
            $data['students_id'] = $student->id;
        }

        return true;
    }

    public function validatePaymentData_old(&$data, $type, &$db, &$message): bool
    {
        if (!trim(@$data['fee_breakdown'])) {
            $data['fee_breakdown'] = '[]';
        }
        $json = @$data['fee_breakdown'];
        $fee_description = json_decode($json);
        $total = 0;
        EntityLoader::loadClass($this, 'fee');
        for ($i = 0; $i < count($fee_description); $i++) {
            $item = $fee_description[$i];
            $fee = $this->fee->getWhere(['description' => $item]);
            if (!$fee) {
                $index = $i + 1;
                $message = "invalid fee descriptions encountered in item $index";
                return false;
            }
            $total += $fee[0]->amount;

        }
        if ($type == 'insert') {
            $data['is_visible'] = 1;
            $data['status'] = 1;
            $data['date_created'] = date('Y-m-d h:i:s');
        }
        $data['amount'] = $total;
        return true;
    }

    public function validatePaymentData(&$data, $type, &$db, &$message): bool
    {
        if ($type == 'insert') {
            permissionAccess('payment_create');
        } else if ($type == 'update') {
            permissionAccess('payment_edit');
        }

        $fee_category = paymentCategoryType($this->input->post('fee_category'));
        $level = $this->input->post('level');
        $entryMode = $this->input->post('entry_mode');
        $levelToInclude = $this->input->post('level_to_include');
        $entryToInclude = $this->input->post('entry_mode_to_include');
        $programme = $this->input->post('programme');
        $paymentDescription = $this->input->post('description');
        $formData = [
            'level' => $level,
            'level_to_include' => $levelToInclude,
            'entry_mode' => $entryMode,
            'entry_mode_to_include' => $entryToInclude,
            'programme' => $programme,
            'ui_amount' => $this->input->post('ui_amount'),
            'dlc_amount' => $this->input->post('dlc_amount'),
            'description' => $paymentDescription,
            'options' => $this->input->post('options'),
            'session' => $this->input->post('session'),
            'date_due' => $this->input->post('date_due'),
            'prerequisite_fee' => $this->input->post('prerequisite_fee'),
            'service_type_id' => $this->input->post('service_type_id'),
            'service_charge' => $this->input->post('service_charge'),
            'penalty_fee' => $this->input->post('penalty_fee'),
            'status' => $this->input->post('status'),
            'is_visible' => $this->input->post('is_visible'),
            'fee_category' => $this->input->post('fee_category'),
            'discount_amount' => $this->input->post('discount_amount'),
            'preselected_fee' => $this->input->post('preselected_fee'),
        ];

        $this->form_validation->set_data($formData);
        $this->form_validation->set_rules('ui_amount', 'ui amount', 'trim|required|numeric|is_natural_no_zero');
        $this->form_validation->set_rules('dlc_amount', 'dlc amount', 'trim|required|numeric|is_natural_no_zero');
        $this->form_validation->set_rules('description', 'fee description', 'trim|required');
        $this->form_validation->set_rules('options', 'fee option', 'trim');
        $this->form_validation->set_rules('fee_category', 'fee category', 'trim|required');
        ($fee_category == 1) ? $this->form_validation->set_rules('session', 'session', 'trim|required') : $this->form_validation->set_rules('session', 'session', 'trim');
        $this->form_validation->set_rules('level[]', 'level', 'trim|required');
        $this->form_validation->set_rules('date_due', 'due date', 'trim|required');
        $this->form_validation->set_rules('prerequisite_fee[]', 'prerequisite fee', 'trim|required');
        $this->form_validation->set_rules('entry_mode[]', 'mode of entry', 'trim');
        $this->form_validation->set_rules('level_to_include[]', 'level to include', 'trim');
        $this->form_validation->set_rules('entry_mode_to_include[]', 'include mode of entry', 'trim');
        ($fee_category == 1) ? $this->form_validation->set_rules('programme[]', 'programme', 'trim|required') : $this->form_validation->set_rules('programme[]', 'programme', 'trim');
        $this->form_validation->set_rules('service_type_id', 'service type', 'trim|required');
        $this->form_validation->set_rules('service_charge', 'service charge', 'trim|numeric|required');
        $this->form_validation->set_rules('penalty_fee', 'penalty fee', 'trim|numeric');
        $this->form_validation->set_rules('status', 'status', 'trim');
        $this->form_validation->set_rules('is_visible', 'display status', 'trim');
        $this->form_validation->set_rules('discount_amount', 'discount amount', 'trim');
        $this->form_validation->set_rules('preselected_fee', 'preselected fee', 'trim');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return false;
            }
        }

        $data['amount'] = round($this->input->post('ui_amount'));
        $data['subaccount_amount'] = round($this->input->post('dlc_amount'));

        $data['level'] = $this->input->post('level') ? json_encode($this->input->post('level')) : json_encode([]);
        $data['entry_mode'] = $this->input->post('entry_mode') ? json_encode($this->input->post('entry_mode')) : json_encode([]);
        $data['level_to_include'] = $this->input->post('level_to_include') ? json_encode($this->input->post('level_to_include')) : json_encode([]);
        $data['entry_mode_to_include'] = $this->input->post('entry_mode_to_include') ? json_encode($this->input->post('entry_mode_to_include')) : json_encode([]);
        $data['programme'] = $this->input->post('programme') ? json_encode($this->input->post('programme')) : json_encode([]);
        if ($type == 'insert') {
            $data['date_created'] = date('Y-m-d H:i:s');
        }
        $data['options'] = paymentOptionsType($this->input->post('options'));
        $data['fee_category'] = paymentCategoryType($this->input->post('fee_category'));
        $dataID = (isset($data['id']) && $type == 'update') ? $data['id'] : null;

        $data['payment_code'] = $this->input->post('payment_code') ?: $this->generatePaymentCode($paymentDescription, $data['level'], $data['programme'], $data['entry_mode'], $data['level_to_include'], $data['entry_mode_to_include'], $data['session']);

        $data['prerequisite_fee'] = $this->input->post('prerequisite_fee') ? json_encode($this->input->post('prerequisite_fee')) : json_encode([]);

        if ($this->isPaymentCode($data['payment_code'], $dataID)) {
            $data['payment_code'] = $data['payment_code'] . "-" . generateCode(4);
        }

        return true;
    }

    private function getProgrammeCode($programmes)
    {
        foreach ($programmes as $prog) {
            $programme = $this->programme->getFacultyByProgramme($prog);
            if ($programme) {
                $facultyName = $this->faculty->getFacultyById($programme->faculty_id);
                return ($facultyName) ? $facultyName->slug : null;
            }
        }
    }

    private function generateInitialCode($description, $programme = []): string
    {
        $progName = generateCode(4);
        EntityLoader::loadClass($this, 'programme');
        EntityLoader::loadClass($this, 'faculty');
        EntityLoader::loadClass($this, 'fee_description');

        if (!empty($programme)) {
            $programme = json_decode($programme, true);
            if (!empty($programme)) {
                $progName = $this->getProgrammeCode($programme) ?? $progName;
            }
        }
        $descCode = $this->fee_description->getPaymentDescription($description, true);
        $descCode = $descCode['code'];
        return strtoupper($progName) . "-" . $descCode;
    }

    private function generateLevelMode($levels = [], $modes = [], $levelInclude = [], $modesInclude = []): string
    {
        $struct = [
            "O' Level" => 'O',
            "O' Level Putme" => 'OP',
            "O'Level" => 'O',
            'Direct Entry' => 'D',
            'Fast Track' => 'F',
        ];

        $naming = '';
        $levels = json_decode($levels, true);
        $modes = json_decode($modes, true);
        $levelInclude = json_decode($levelInclude, true);
        $modesInclude = json_decode($modesInclude, true);

        foreach ($levels as $level) {
            if ($level) {
                foreach ($modes as $mode) {
                    if ($mode) {
                        $currentMode = $struct[$mode];
                        $currentLevel = $level;
                        if ($naming) {
                            $naming .= '.';
                        }
                        $naming .= 'L' . $currentLevel . $currentMode;
                    }
                }
            }
        }

        foreach ($levelInclude as $level) {
            if ($level) {
                foreach ($modesInclude as $mode) {
                    if ($mode) {
                        $currentMode = $struct[$mode];
                        $currentLevel = $level;
                        if ($naming) {
                            $naming .= '.';
                        }
                        $naming .= 'L' . $currentLevel . $currentMode;
                    }
                }
            }
        }

        return $naming;
    }

    private function isPaymentCode($code, $id = null): bool
    {
        EntityLoader::loadClass($this, 'payment');
        $payment = $this->payment->getPaymentCode($code, $id);
        if ($payment) {
            return true;
        }
        return false;
    }

    private function generatePaymentCode($description, $levels = [], $programme = [], $entryMode = [], $levelInclude = [], $entryModeInclude = [], $session = null): string
    {
        $paymentCode = '';
        $newSession = null;
        EntityLoader::loadClass($this, 'programme');
        EntityLoader::loadClass($this, 'faculty');
        EntityLoader::loadClass($this, 'sessions');

        if ($session && $session != 0) {
            $newSession = $this->sessions->getSessionById($session)[0]['date'];
        }

        $paymentCode = $this->generateInitialCode($description, $programme);
        $levelCode = $this->generateLevelMode($levels, $entryMode, $levelInclude, $entryModeInclude);
        if ($levelCode) {
            $paymentCode .= '-' . $levelCode;
        }

        if ($newSession) {
            $paymentCode .= '-SY' . $newSession;
        }

        if ($description == '1' || $description == '2') {
            $paymentCode .= '-SM' . $description;
        }

        if ($description == '16') {
            $paymentCode .= '-SM1';
        }

        return $paymentCode;
    }

    public function validateUsers_customData(&$data, $type, &$db, &$message): bool
    {
        if ($type == 'insert') {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $message = 'Invalid email provided';
                return false;
            }

            if (!isValidPhone($data['phone_number'])) {
                $message = 'The provided phone number is invalid';
                return false;
            }
        }

        return true;
    }

    // Callback to check if fee_description is present in database
    private function check_unique_fee($name): bool
    {
        if (check_unique($this, 'fee_description', $name, 'description')) {
            $this->form_validation->set_message('check_unique_fee', 'Fee "' . $name . '" already exist');
            return false;
        } else {
            return true;
        }
    }

    public function validateFee_descriptionData(&$data, $type, &$db, &$message): bool
    {
        if ($type == 'insert') {
            permissionAccess('fees_listing', 'create');
        } else if ($type == 'update') {
            permissionAccess('faculty_department_edit', 'edit');
        }
        $this->form_validation->set_rules('description', 'description', 'trim|required');
        $this->form_validation->set_rules('category', 'category', 'trim|required|in_list[main,others,custom]');
        $this->form_validation->set_rules('code', 'code', 'trim|required');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return false;
            }
        }

        $data['description'] = ucwords(strtolower($data['description']));
        if ($type == 'insert') {
            $data['date_created'] = date('Y-m-d H:i:s');
        }
        $data['active'] = 1;
        $data['category'] = feeCategoryType($data['category']);

        return true;
    }

    public function validateVerification_cardsData(&$data, $type, &$db, &$message): bool
    {
        $this->form_validation->set_rules('serial_number', 'serial number', 'trim');
        $this->form_validation->set_rules('card_type', 'card_type', 'trim|required|in_list[Waec,Neco]');
        $this->form_validation->set_rules('pin_number', 'pin number', 'trim|required');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return false;
            }
        }

        if ($type == 'insert') {
            $data['date_created'] = date('Y-m-d H:i:s');
        }
        $data['date_modified'] = date('Y-m-d H:i:s');
        return true;
    }

    public function validateStudent_verification_cardsData(&$data, $type, &$db, &$message): bool
    {
        $this->form_validation->set_rules('student_id', 'student', 'trim|required');
        $this->form_validation->set_rules('verification_cards_id', 'verification card', 'trim|required');
        if ($type == 'update' && isset($data['usage_status'])) {
            $this->form_validation->set_rules('usage_status', 'usage status', 'trim|required|in_list[0,1]');
        }

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return false;
            }
        }

        if ($type == 'insert') {
            $data['date_created'] = date('Y-m-d H:i:s');
        }
        $data['date_modified'] = date('Y-m-d H:i:s');
        return true;
    }

    public function validateAdmissionData(&$data, $type, &$db, &$message): bool
    {
        if ($type == 'insert') {
            permissionAccess('admissions_admission_create', 'create');
        } else if ($type == 'update') {
            permissionAccess('admissions_admission_edit', 'edit');
        }

        $this->form_validation->set_rules('name', 'name', 'trim|required');
        $this->form_validation->set_rules('description', 'fee description', 'trim|required');
        $this->form_validation->set_rules('session_id', 'session', 'trim|required');
        $this->form_validation->set_rules('criteria', 'criteria', 'trim');
        $this->form_validation->set_rules('code', 'code', 'trim');
        $this->form_validation->set_rules('admission_mode', 'admission mode', 'required|trim');
        $this->form_validation->set_rules('applicant_payment_id', 'applicant payment', 'trim|required|numeric');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return false;
            }
        }

        return true;
    }

    public function validateAdmission_programme_requirementsData(&$data, $type, &$db, &$message): bool
    {
        if ($type == 'insert') {
            permissionAccess('admissions_manager', 'create');
        } else if ($type == 'update') {
            permissionAccess('admissions_manager', 'edit');
        }
        $this->form_validation->set_rules('olevel_requirements', 'O\'Level requirement', 'trim');
        $this->form_validation->set_rules('alevel_requirements', 'A\'Level requirement', 'trim');
        $this->form_validation->set_rules('other_requirements', 'other requirements', 'trim');
        $this->form_validation->set_rules('active', 'status', 'trim|required');
        $this->form_validation->set_rules('admission_id', 'admission type', 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return false;
            }
        }

        if ($type == 'insert') {
            $data['date_created'] = date('Y-m-d H:i:s');
        }

        return true;
    }

    public function validateApplicant_paymentData(&$data, $type, &$db, &$message): bool
    {
        if ($type == 'insert') {
            permissionAccess('admissions_payment_create', 'create');
        } else if ($type == 'update') {
            permissionAccess('admissions_payment_edit', 'edit');
        }
        $this->form_validation->set_rules('amount', 'amount', 'trim|required|numeric|is_natural_no_zero');
        $this->form_validation->set_rules('subaccount_amount', 'subaccount amount', 'trim|required|numeric|is_natural_no_zero');
        $this->form_validation->set_rules('description', 'description', 'trim|required');
        $this->form_validation->set_rules('session_id', 'session', 'trim|required');
        $this->form_validation->set_rules('service_type_id', 'service type id', 'trim|required');
        $this->form_validation->set_rules('service_charge', 'service charge', 'trim|required');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return false;
            }
        }

        if ($type == 'insert') {
            $data['date_created'] = date('Y-m-d H:i:s');
        }

        return true;
    }

    public function validateBank_listsData(&$data, $type, &$db, &$message): bool
    {
        $this->form_validation->set_rules('name', 'bank name', 'trim|required');
        $this->form_validation->set_rules('code', 'bank code', 'trim|required|numeric');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return false;
            }
        }

        if ($type == 'insert') {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        return true;
    }

    public function validateUser_banksData(&$data, $type, &$db, &$message): bool
    {
        $this->form_validation->set_rules('account_name', 'account name', 'trim|required');
        $this->form_validation->set_rules('account_number', 'account number', 'trim|required|numeric');
        $this->form_validation->set_rules('bank_lists_id', 'bank ID', 'trim|required|numeric');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return false;
            }
        }
        if (check_unique($this, 'user_banks', $data['account_number'], 'account_number')) {
            $message = "The account number is already in use by another user.";
            return false;
        }
        $currentUser = WebSessionManager::currentAPIUser();
        $data['users_id'] = $currentUser->id;
        if ($type == 'insert') {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        EntityLoader::loadClass($this, 'bank_lists');
        $bankCode = $this->bank_lists->getWhere(['id' => $data['bank_lists_id']], $count, 0, 1, false);
        if (!$bankCode) {
            $message = 'Unable to get the bank details, kindly reach out to the administrator';
            return false;
        }
        $data['bank_code'] = $bankCode[0]->code;
        $data['is_primary'] = 1;

        $this->updateUserBankPrimary($currentUser->id);
        return true;
    }

    private function updateUserBankPrimary($user_id)
    {
        $query = "UPDATE user_banks SET is_primary = '0' WHERE users_id = ?";
        $this->db->query($query, [$user_id]);

        // $query = "UPDATE user_banks SET is_primary = '1' WHERE users_id = ? ORDER BY created_at ASC LIMIT 1";
        //return $this->db->query($query, [$user_id]);
    }

    public function validateProjectsData(&$data, $type, &$db, &$message): bool
    {
        $this->form_validation->set_rules('title', 'project title', 'trim|required');
        $this->form_validation->set_rules('description', 'project details', 'trim|required');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return false;
            }
        }
        $currentUser = WebSessionManager::currentAPIUser();
        $data['users_id'] = $currentUser->id;
        if ($type == 'insert') {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        return true;
    }

    public function validateProject_tasksData(&$data, $type, &$db, &$message): bool
    {
        $this->form_validation->set_rules('project_id', 'project', 'trim|required', [
            'required' => 'Please choose a project'
        ]);
        $this->form_validation->set_rules('assign_to', 'contractor', 'trim|required', [
            'required' => 'Please assign a contractor'
        ]);
        $this->form_validation->set_rules('task_title', 'task title', 'trim|required');
        $this->form_validation->set_rules('multiple_invoice', 'Allow multiple', 'trim|required');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return false;
            }
        }

        if ($type == 'insert') {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }
        return true;
    }

    public function validateStaffsData(&$data, $type, &$db, &$message): bool
    {
        if ($type == 'insert') {
            permissionAccess('user_new', 'create');
        } else if ($type == 'update') {
            permissionAccess('user_edit', 'edit');
        }

        $staffID = $data['staff_id'];
        $username = $this->input->post('username');
        $this->form_validation->set_rules('title', 'title', 'trim|required');
        $this->form_validation->set_rules('firstname', 'firstname', 'trim|required');
        $this->form_validation->set_rules('othernames', 'othernames', 'trim');
        $this->form_validation->set_rules('lastname', 'lastname', 'trim|required');
        $this->form_validation->set_rules('gender', 'gender', 'trim|required');
        $this->form_validation->set_rules('dob', 'date of birth', 'required|valid_date');
        $this->form_validation->set_rules('marital_status', 'marital status', 'trim|required');
        ($type === 'insert') ? $this->form_validation->set_rules('username', 'user name', 'trim|required|min_length[6]|max_length[10]') : null;
        $this->form_validation->set_rules('phone_number', 'phone number', 'trim|required|min_length[11]|max_length[11]');
        $this->form_validation->set_rules('email', 'email', 'trim|required|valid_email');
        $this->form_validation->set_rules('user_rank', 'user rank', 'trim|required');
        $this->form_validation->set_rules('units_id', 'department', 'trim');
        $this->form_validation->set_rules('can_upload', 'staff type', 'trim|required');
        $this->form_validation->set_rules('address', 'address', 'trim|required');
        $this->form_validation->set_rules('staff_id', 'staff ID', 'trim|required');

        if($type === 'insert'){
            $this->form_validation->set_rules('staff_id', 'staff ID', array(
                'required',
                array('staff_id', function ($name) use ($staffID) {
                    $this->form_validation->set_message('staff_id', "The staff id '" . $name . "' already in use by another user");
                    return !check_unique($this, 'staffs', $staffID, 'staff_id');
                }),
            ));

            $this->form_validation->set_rules('username', 'username', array(
                'required',
                array('username', function ($name) use ($username) {
                    $this->form_validation->set_message('username', "The username '" . $name . "' already in use by another user");
                    return !check_unique($this, 'users_new', $username, 'user_login');
                }),
            ));
        }

        if($type === 'update'){
            $id = $data['id'];
            $this->form_validation->set_rules('staff_id', 'staff ID', array(
                'required',
                array('staff_id', function ($name) use ($staffID, $id) {
                    $this->form_validation->set_message('staff_id', "The staff id '" . $name . "' already in use by another user");
                    return !check_unique_multiple($this, 'staffs', ['staff_id' => $staffID, 'id !=' => $id]);
                }),
            ));

            $this->form_validation->set_rules('username', 'username', array(
                'required',
                array('username', function ($name) use ($username, $id) {
                    $this->form_validation->set_message('username', "The username '" . $name . "' already in use by another user");
                    return !check_unique_multiple($this, 'users_new', ['user_login' => $username, 'user_table_id !=' => $id, 'user_type' => 'staff']);
                }),
            ));
        }


        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return false;
            }
        }

        $data['firstname'] = ucwords(strtolower($data['firstname']));
        $data['lastname'] = ucwords(strtolower($data['lastname']));
        $data['othernames'] = ucwords(strtolower($data['othernames']));
        $data['role'] = '';
        $data['phone_number'] = (startsWith($data['phone_number'], '0')) ? $data['phone_number'] : '0' . $data['phone_number'];

        return true;
    }

    public function validateRolesData(&$data, $type, &$db, &$message): bool
    {
        if ($type === 'insert') {
            permissionAccess('role_create', 'create');
        } else if ($type === 'update') {
            permissionAccess('role_edit', 'edit');
        }

        $this->form_validation->set_rules('name', 'name', 'trim|required');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return false;
            }
        }

        $data['active '] = '1';
        return true;
    }

    public function validateStaff_departmentData(&$data, $type, &$db, &$message): bool
    {
        if ($type === 'insert') {
            permissionAccess('faculty_department_edit', 'create');
        } else if ($type === 'update') {
            permissionAccess('faculty_department_edit', 'edit');
        }

        $this->form_validation->set_rules('name', 'name', 'trim|required');
        $this->form_validation->set_rules('code', 'code', 'trim|required');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return false;
            }
        }
        $data['slug'] = $data['code'];
        $data['faculty_id'] = '';
        $data['type'] = 'non-academic';
        $data['active '] = '1';

        return true;
    }

    public function validateRoles_permissionData(&$data, $type, &$db, &$message): bool
    {
        if ($type === 'insert') {
            permissionAccess('role_assign_permission_create', 'create');
        } else if ($type === 'update') {
            permissionAccess('role_assign_permission_edit', 'edit');
        }

        $this->form_validation->set_rules('role_id[]', 'role', 'trim|required');
        ($type == 'insert') ? $this->form_validation->set_rules('permission', 'permission', array(
            'required',
            array('permission', function ($permission) {
                $this->form_validation->set_message('permission', "Permission '" . $permission . "' already exist, edit '" . $permission . "' and add new role(s) to it");
                return check_unique_permission($this, $permission);
            }),
        ))
            : $this->form_validation->set_rules('permission', 'permission', 'trim|required');

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return false;
            }
        }

        $data['role_id'] = json_encode($data['role_id']);
        return true;
    }

    public function validateFacultyData(&$data, $type, &$db, &$message): bool
    {
        if ($type === 'insert') {
            permissionAccess('faculty_listing', 'create');
        } else if ($type === 'update') {
            permissionAccess('faculty_edit', 'edit');
        }

        if ($type == 'insert') {
            $this->form_validation->set_rules('name', 'name', array(
                'required',
                array('name', function ($name) {
                    $this->form_validation->set_message('name', "Faculty '" . $name . "' already exist");
                    return !check_unique($this, 'faculty', $name, 'name');
                }),
            ));
            $this->form_validation->set_rules('slug', 'slug', array(
                'required',
                array('slug', function ($slug) {
                    $this->form_validation->set_message('slug', "Slug '" . $slug . "' already exist");
                    return !check_unique($this, 'faculty', $slug, 'slug');
                }),
            ));
            $data['date_created'] = date('Y-m-d H:i:s');
        }

        if ($type == 'update') {
            $this->form_validation->set_rules('name', 'name', 'trim|required');
            $this->form_validation->set_rules('slug', 'slug', 'trim|required');
        }


        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return false;
            }
        }
        $data['active '] = '1';
        $data['name'] = ucwords($data['name']);
        return true;
    }

    public function validateDepartmentData(&$data, $type, &$db, &$message): bool
    {
        if ($type === 'insert') {
            permissionAccess('faculty_department_listing', 'create');
        } else if ($type === 'update') {
            permissionAccess('faculty_department_edit', 'edit');
        }

        if ($type == 'insert') {
            $this->form_validation->set_rules('name', 'name', array(
                'required',
                array('name', function ($name) {
                    $this->form_validation->set_message('name', "Department '" . $name . "' already exist");
                    return !check_unique($this, 'department', $name, 'name');
                }),
            ));
            $this->form_validation->set_rules('code', 'code', array(
                'required',
                array('code', function ($code) {
                    $this->form_validation->set_message('code', "Department code '" . $code . "' already exist");
                    return !check_unique($this, 'department', $code, 'code');
                }),
            ));
            $this->form_validation->set_rules('faculty_id', 'faculty', 'trim|required');
            $data['date_created'] = date('Y-m-d H:i:s');
        }

        if ($type == 'update') {
            $this->form_validation->set_rules('name', 'name', 'trim|required');
            $this->form_validation->set_rules('code', 'code', 'trim|required');
            $this->form_validation->set_rules('faculty_id', 'faculty', 'trim|required');
        }

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return false;
            }
        }
        $data['active'] = '1';
        $data['slug'] = $data['code'];
        $data['type'] = 'academic';
        $data['name'] = ucwords($data['name']);
        return true;
    }

    public function validateProgrammeData(&$data, $type, &$db, &$message): bool
    {
        if ($type === 'insert') {
            permissionAccess('faculty_programme_listing', 'create');
        } else if ($type === 'update') {
            permissionAccess('faculty_programme_edit', 'edit');
        }

        EntityLoader::loadClass($this, 'department');
        if ($type == 'insert') {
            $this->form_validation->set_rules('name', 'name', array(
                'required',
                array('name', function ($name) {
                    $this->form_validation->set_message('name', "Programme '" . $name . "' already exist");
                    return !check_unique($this, 'programme', $name, 'name');
                }),
            ));
            $this->form_validation->set_rules('code', 'code', array(
                'required',
                array('code', function ($code) {
                    $this->form_validation->set_message('code', "Programme code '" . $code . "' already exist");
                    return !check_unique($this, 'programme', $code, 'code');
                }),
            ));
            $this->form_validation->set_rules('department_id', 'department', 'trim|required');
            $data['date_created'] = date('Y-m-d H:i:s');
        }

        if ($type == 'update') {
            $this->form_validation->set_rules('name', 'name', 'trim|required');
            $this->form_validation->set_rules('code', 'code', 'trim|required');
            $this->form_validation->set_rules('department_id', 'department', 'trim|required');
        }

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return false;
            }
        }

        $faculty = $this->department->getFacultyByDepartment($data['department_id']);
        if (!$faculty) {
            $message = "Unable to locate the department faculty";
            return false;
        }

        $data['faculty_id'] = $faculty->faculty_id;
        $data['active'] = '1';
        $data['name'] = ucwords($data['name']);
        $data['code'] = ucwords($data['code']);
        return true;
    }

    public function validateCourse_managerData(&$data, $type, &$db, &$message): bool
    {
        if ($type === 'insert') {
            permissionAccess('course_manager', 'create');
        } else if ($type === 'update') {
            permissionAccess('course_manager', 'edit');
        }

        if ($type == 'insert') {
            $session = $data['session_id'];
            $this->form_validation->set_rules('session_id', 'session', 'trim|required');
            $this->form_validation->set_rules('course_id', 'course', array(
                'required',
                array('course', function ($course) use($session) {
                    $this->form_validation->set_message('course', "Course '" . $course . "' already exist");
                    return !check_unique_multiple($this, 'course_manager', ['course_id' => $course, 'session_id' => $session]);
                }),
            ));
            $this->form_validation->set_rules('course_manager_id', 'course manager', 'trim|required');
            $this->form_validation->set_rules('course_lecturer_id[]', 'course lecturer', 'trim|required');
            $data['date_created'] = date('Y-m-d H:i:s');
            $data['active'] = '1';
        }

        if ($type == 'update') {
            // $this->form_validation->set_rules('session_id', 'session', 'trim|required');
            // $this->form_validation->set_rules('course_id', 'course', 'trim|required');
            $this->form_validation->set_rules('course_manager_id', 'course manager', 'trim|required');
            $this->form_validation->set_rules('course_lecturer_id[]', 'course lecturer', 'trim|required');

            unset($data['session_id'], $data['course_id']);
        }

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return false;
            }
        }

        $data['course_lecturer_id'] = $this->input->post('course_lecturer_id') ? json_encode($this->input->post('course_lecturer_id')) : json_encode([]);
        return true;
    }

    public function validateGradesData(&$data, $type, &$db, &$message): bool
    {
        if ($type === 'insert') {
            permissionAccess('exam_grades', 'create');
        } else if ($type === 'update') {
            permissionAccess('exam_grade_edit', 'edit');
        }

        if ($type == 'insert') {
            $yearOfEntry = $data['year_of_entry'];
            $this->form_validation->set_rules('point', 'grade point', 'trim|required|numeric|is_natural|max_length[1]');
            $this->form_validation->set_rules('name', 'grade name', array(
                'required',
                array('name', function ($name) use ($yearOfEntry) {
                    $this->form_validation->set_message('name', "Grade name '" . $name . "' already exist");
                    return !check_unique_multiple($this, 'grades', ['name' => strtoupper($name), 'year_of_entry' => $yearOfEntry]);
                }),
            ));
            $this->form_validation->set_rules('mark_from', 'mark from', 'trim|required|numeric|is_natural|less_than[' . $data['mark_to'] . ']');
            $this->form_validation->set_rules('mark_to', 'mark to', 'trim|required|numeric|is_natural|max_length[3]|less_than_equal_to[100]|greater_than[' . $data['mark_from'] . ']');
            $this->form_validation->set_rules('year_of_entry', 'year of entry', 'trim|required|numeric');
            $data['active'] = '1';
        }

        if ($type == 'update') {
            $id = $data['id'];
            $yearOfEntry = $data['year_of_entry'];
            $this->form_validation->set_rules('point', 'grade point', 'trim|required|numeric|is_natural|max_length[1]');
            $this->form_validation->set_rules('name', 'grade name', array(
                'required',
                array('name', function ($name) use ($yearOfEntry, $id) {
                    $this->form_validation->set_message('name', "Grade name '" . $name . "' already exist");
                    return !check_unique_multiple($this, 'grades', ['name' => strtoupper($name), 'year_of_entry' => $yearOfEntry, 'id !=' => $id]);
                }),
            ));
            $this->form_validation->set_rules('mark_from', 'mark from', 'trim|required|numeric|is_natural|less_than[' . $data['mark_to'] . ']');
            $this->form_validation->set_rules('mark_to', 'mark to', 'trim|required|numeric|is_natural|max_length[3]|less_than_equal_to[100]|greater_than[' . $data['mark_from'] . ']');

            unset($data['year_of_entry']);
        }

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return false;
            }
        }

        $data['name'] = strtoupper($data['name']);
        return true;
    }

    public function validateClass_of_degreeData(&$data, $type, &$db, &$message): bool
    {
        if ($type === 'insert') {
            permissionAccess('exam_grades', 'create');
        } else if ($type === 'update') {
            permissionAccess('exam_grade_edit', 'edit');
        }

        $yearOfEntry = $data['year_of_entry'];
        if ($type == 'insert') {
            $this->form_validation->set_rules('short_form', 'short form', 'trim|required');
            $this->form_validation->set_rules('name', 'class name', array(
                'required',
                array('name', function ($name) use ($yearOfEntry) {
                    $this->form_validation->set_message('name', "Class name '" . $name . "' already exist");
                    return !check_unique_multiple($this, 'class_of_degree', ['name' => $name, 'year_of_entry' => $yearOfEntry]);
                }),
            ));
            $this->form_validation->set_rules('cgpa_from', 'cgpa from', 'trim|required|less_than[' . $data['cgpa_to'] . ']');
            $this->form_validation->set_rules('cgpa_to', 'cgpa to', 'trim|required|greater_than[' . $data['cgpa_from'] . ']');
            $this->form_validation->set_rules('year_of_entry', 'year of entry', 'trim|required|numeric');
            $data['active'] = '1';
        }

        if ($type == 'update') {
            $id = $data['id'];
            $this->form_validation->set_rules('short_form', 'short form', 'trim|required');
            $this->form_validation->set_rules('name', 'class name', array(
                'required',
                array('name', function ($name) use ($yearOfEntry, $id) {
                    $this->form_validation->set_message('name', "Class name '" . $name . "' already exist");
                    return !check_unique_multiple($this, 'class_of_degree', [
                        'name' => $name,
                        'year_of_entry' => $yearOfEntry,
                        'id !=' => $id]);
                }),
            ));
            $this->form_validation->set_rules('cgpa_from', 'cgpa from', 'trim|required|less_than[' . $data['cgpa_to'] . ']');
            $this->form_validation->set_rules('cgpa_to', 'cgpa to', 'trim|required|greater_than[' . $data['cgpa_from'] . ']');
            $this->form_validation->set_rules('year_of_entry', 'year of entry', 'trim|required|numeric');
        }

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return false;
            }
        }

        return true;
    }

    public function validateSessionsData(&$data, $type, &$db, &$message): bool
    {
        if ($type === 'insert') {
            permissionAccess('session_listing', 'create');
        } else if ($type === 'update') {
            permissionAccess('session_edit', 'edit');
        }

        if ($type == 'insert') {
            $this->form_validation->set_rules('date', 'session', array(
                'required', 'min_length[9]', 'max_length[9]',
                array('date', function ($date) {
                    $this->form_validation->set_message('date', "Session '" . $date . "' already exist");
                    return !check_unique($this, 'sessions', $date, 'date');
                }),
            ));
            $data['date_created'] = date('Y-m-d H:i:s');
        }

        if ($type == 'update') {
            $id = $data['id'];
            $this->form_validation->set_rules('date', 'session', array(
                'required', 'min_length[9]', 'max_length[9]',
                array('date', function ($date) use ($id) {
                    $this->form_validation->set_message('date', "Session '" . $date . "' already exist");
                    return !check_unique_multiple($this, 'sessions', [
                        'id !=' => $id,
                        'date' => $date]);
                }),
            ));
        }

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                $message = $error;
                return false;
            }
        }
        $data['active'] = '1';
        return true;
    }

    // this is to ensure that POST method is blocked off on this endpoint
    public function validateTransactionData(&$data, $type, &$db, &$message): bool
    {
        $message = "You're not allowed to perform the action";
        return false;
    }

    public function validateStudentsData(&$data, $type, &$db, &$message): bool
    {
        $message = "You're not allowed to perform the action";
        return false;
    }

    public function validateSettingsData(&$data, $type, &$db, &$message): bool
    {
        $message = "You're not allowed to perform the action";
        return false;
    }


}
