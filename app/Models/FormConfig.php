<?php
/**
 * this class help save the configuration needed by the form in order to use a single file for all the form code.
 * you only need to include the configuration data that matters. the default value will be substituted for other configuration value that does not have a key  for a particular entity.
 */
namespace App\Models;

use App\Models\WebSessionManager;

class FormConfig {
	private array $insertConfig = [];
	private $updateConfig;
	private $webSessionManager;
	public $currentRole;
	private $apiEntity = false;

	public function __construct(bool $currentRole = false, bool $apiEntity = false) {
		$this->currentRole = $currentRole;
		$this->apiEntity = $apiEntity;
		$this->webSessionManager = new WebSessionManager;
		if ($currentRole) {
			$this->buildInsertConfig();
			$this->buildUpdateConfig();
		}

	}

	/**
	 * this is the function to change when an entry for a particular entitiy needed to be addded. this is only necessary for entities that has a custom configuration for the form.Each of the key for the form model append insert option is included. This option inculde:
	 * form_name the value to set as the name and as the id of the form. The value will be overridden by the default value if the value if false.
	 * has_upload this field is used to determine if the form should include a form upload section for the table form list
	 * hidden this  are the field that should be pre-filled. This must contain an associative array where the key of the array is the field and the value is the value to be pre-filled on the value.
	 * showStatus field is used to show the status flag on the form. once the value is true the status field will be visible on the form and false otherwise.
	 * exclude contains the list of entities field name that should not be shown in the form. The filed for this form will not be display on the form.
	 * submit_label is the label that is going to be displayed on the submit button
	 * 	table_exclude is the list of field that should be removed when displaying the table.
	 * table_action contains an associative arrays action to be displayed on the action table and the link to perform the action.
	 * the query paramete is used to specify a query for getting the data out of the entity
	 * upload_param contains the name of the function to be called to perform
	 *
	 */
	private function buildInsertConfig(): void
    {
		if ($this->apiEntity) {
			$this->insertConfig = array
				(
				'voters' => array(
					'search' => array('firstname'),
				),
			);
		} else {
            $this->insertConfig = array
            (

                'users_new' => array
                (
                    'search' => array('firstname', 'lastname', 'email', 'phone_number'),
                    'exclude' => ['user_pass'],
                ),
                'department' => array
                (
                    'search' => array('name', 'code'),
                ),
                'programme' => array
                (
                    'search' => array('name', 'code'),
                ),
                'fee' => array
                (
                    'search' => array('amount', 'description'),
                ),
                'levels' => array
                (
                    'search' => array('name'),
                ),
                'applicants' => array
                (
                    'search' => ['applicant_id', 'firstname', 'lastname', 'email', 'phone', 'applicant_id'],
                ),
                'students' => array
                (
                    'search' => ['matric_number', 'application_number', 'firstname', 'lastname', 'othernames', 'gender', 'phone', 'reg_num', 'e.name', 'd.name'],
                ),
                'student_verification_fee' => array
                (
                    'search' => ['matric_number', 'firstname', 'lastname', 'department.name', 'faculty.name'],
                ),
                'student_transaction_change_programme' => array
                (
                    'search' => ['application_number', 'firstname', 'lastname', 'department.name', 'faculty.name', 'matric_number'],
                ),
                'student_verification_cards' => array
                (
                    'search' => ['application_number', 'firstname', 'lastname'],
                ),
                'student_registration_courses' => array
                (
                    'search' => ['code', 'title'],
                ),
                'transaction_custom' => array
                (
                    'search' => ['name', 'email', 'phone_number', 'amount_paid', 'rrr_code', 'fee_description.description'],
                ),
                'transaction_archive' => array
                (
                    'search' => ['firstname', 'lastname', 'rrr_code', 'matric_number', 'c.description', 'transaction_ref', 'amount_paid'],
                ),
                'transaction_outflow' => array
                (
                    'search' => ['firstname', 'lastname', 'rrr_code', 'beneficiary_description', 'transaction_ref', 'amount_paid', 'beneficiary_bank_name', 'beneficiary_account'],
                ),
                'payment' => array(
                    'search' => ['fee_description.description', 'fee_description.code', 'payment.amount', 'payment.service_charge', 'payment.subaccount_amount', 'payment.payment_code'],
                ),
                'applicant_payment' => array(
                    'search' => ['fee_description.description', 'applicant_payment.amount', 'applicant_payment.service_charge', 'applicant_payment.subaccount_amount'],
                ),
                'fee_description' => array(
                    'search' => [],
                ),
                'admission' => array(
                    'search' => ['admission_mode', 'admission.description', 'criteria', 'name', 'code'],
                ),
                'admissions_programme_list' => array(
                    'search' => ['name', 'code'],
                ),
                'sessions' => array(
                    'search' => [],
                ),
                'bank_lists' => array(
                    'search' => ['name', 'code'],
                ),
                'sundry_finance_transaction' => [
                    'search' => ['firstname', 'lastname', 'rrr_code', 'matric_number', 'a.payment_description', 'transaction_ref', 'amount_paid'],
                ],
                'practicum_form' => [
                    'search' => ['firstname', 'lastname', 'matric_number'],
                ],
                'projects' => [
                    'search' => ['a.title'],
                ],
                'project_tasks' => [
                    'search' => ['task_title', 'b.title'],
                ],
                'contractors' => [
                    'search' => ['registered_name', 'cac_number', 'tin_number', 'email'],
                ],
                'user_requests' => [
                    'search' => ['b.task_title', 'a.title', 'a.description'],
                ],
                'user_banks' => [
                    'search' => ['account_name', 'account_number', 'bank_code', 'b.name'],
                ],
                'staffs' => [
                    'search' => ['firstname', 'lastname', 'othernames', 'email', 'staff_id', 'title', 'b.user_login'],
                ],
                'roles' => [
                    'search' => ['name'],
                ],
                'roles_permission' => [
                    'search' => ['permission'],
                ],
                'examination_courses' => [
                    'search' => ['b.title', 'b.code'],
                ],
                'course_request_claims' => [
                    'search' => ['a.title', 'a.request_no', 'description', 'a.amount'],
                ],

                //add new entry to this array
            );
		}
	}

	/**
	 * This is to get the entity filter for a model using certain pattern
	 * @example 'entity_name'=>array(
	 * array(
	 * 'filter_label'=>'request_status', # this is the field to call for the filter
	 * 'filter_display'=>'active_status' # this is the query param supplied
	 * )),
	 * @param  string $tablename [description]
	 * @return [type]            [description]
	 */
	private function getFilter(string $tablename) {
		$result = [];
		if ($this->apiEntity) {
			$result = array(

			);
		} else {
            $result = array(
                'department' => array(
                    array(
                        'filter_label' => 'faculty_id',
                        'filter_display' => 'Faculty',
                        'preload_query' => false, //this will normally be a query that returns two field id and value where id is the value to be set inthe database and value will be the display value
                        'select_items' => ['type' => 'entity', 'table' => 'faculty', 'display' => 'name', 'id' => 'id'],
                    ),

                ),
                'programme' => array(
                    array(
                        'filter_label' => 'faculty_id',
                        'filter_display' => 'Faculty',
                        'preload_query' => false, //this will normally be a query that returns two field id and value where id is the value to be set inthe database and value will be the display value
                        'select_items' => ['type' => 'entity', 'table' => 'faculty', 'display' => 'name', 'id' => 'id'],
                    ),
                    array(
                        'filter_label' => 'department_id',
                        'filter_display' => 'Department',
                        'preload_query' => false, //this will normally be a query that returns two field id and value where id is the value to be set inthe database and value will be the display value
                        'select_items' => ['type' => 'entity', 'table' => 'department', 'display' => 'name', 'id' => 'id'],
                    ),
                ),
                'applicants' => array(
                    array(
                        'filter_label' => 'session_id',
                        'filter_display' => 'session',
                    ),
                    array(
                        'filter_label' => 'programme_id',
                        'filter_display' => 'programmeInterest',
                    ),
                    array(
                        'filter_label' => 'programme_given',
                        'filter_display' => 'programmeOffered',
                    ),
                    array(
                        'filter_label' => 'admission_id',
                        'filter_display' => 'admissionType',
                    ),
                    array(
                        'filter_label' => 'step',
                        'filter_display' => 'step',
                        'preload_query' => null,
                    ),

                ),
                'students' => array(
                    array(
                        'filter_label' => 'c.id',
                        'filter_display' => 'entryYear', // to be used on the client side
                        'preload_query' => "SELECT id,date as value from sessions order by date desc",
                    ),
                    array(
                        'filter_label' => 'e.id',
                        'filter_display' => 'department',
                        'preload_query' => "SELECT id,name as value from department where type = 'academic' order by value asc",
                    ),
                    array(
                        'filter_label' => 'f.id',
                        'filter_display' => 'faculty',
                        'preload_query' => "SELECT id,name as value from faculty order by value asc",
                    ),
                    array(
                        'filter_label' => 'd.id',
                        'filter_display' => 'programme',
                        'preload_query' => "SELECT id,name as value from programme order by value asc",
                    ),
                    array(
                        'filter_label' => 'b.current_level',
                        'filter_display' => 'levels',
                        'preload_query' => null,
                    ),
                ),
                'transaction_custom' => array(
                    array(
                        'filter_label' => 'payment_id',
                        'filter_display' => 'category', // to be used on the client side
                        'preload_query' => null,
                    ),
                    array(
                        'filter_label' => 'payment_status',
                        'filter_display' => 'payment_status',
                        'preload_query' => null,
                    ),
                ),
                'transaction_archive' => array(
                    array(
                        'filter_label' => 'payment_id',
                        'filter_display' => 'payment_type', // to be used on the client side
                        'preload_query' => null,
                    ),
                    array(
                        'filter_label' => 'rrr_code',
                        'filter_display' => 'rrr',
                        'preload_query' => null,
                    ),
                ),
                'transaction_outflow' => array(
                    array(
                        'filter_label' => 'approval_status',
                        'filter_display' => 'approval_status',
                        'preload_query' => null,
                    ),
                    array(
                        'filter_label' => 'payment_status',
                        'filter_display' => 'payment_status',
                        'preload_query' => null,
                    ),
                ),
                'student_verification_fee' => array(
                    array(
                        'filter_label' => 'document_verification',
                        'filter_display' => 'verify_status',
                        'preload_query' => null,
                    ),
                ),
                'student_transaction_change_programme' => array(
                    array(
                        'filter_label' => 'payment_status',
                        'filter_display' => 'payment_status',
                        'preload_query' => null,
                    ),
                ),
                'sundry_finance_transaction' => array(
                    array(
                        'filter_label' => 'payment_status',
                        'filter_display' => 'payment_status',
                        'preload_query' => null,
                    ),
                    array(
                        'filter_label' => 'a.session',
                        'filter_display' => 'payment_session',
                        'preload_query' => null,
                    ),
                    array(
                        'filter_label' => 'd.year_of_entry',
                        'filter_display' => 'entry_year',
                        'preload_query' => null,
                    ),
                    array(
                        'filter_label' => 'b.code',
                        'filter_display' => 'sundry_type',
                        'preload_query' => null,
                    ),
                ),
                'verification_cards' => array(
                    array(
                        'filter_label' => 'card_type',
                        'filter_display' => 'type',
                        'preload_query' => null,
                    ),
                ),
                'projects' => array(
                    array(
                        'filter_label' => 'project_status',
                        'filter_display' => 'project_status',
                        'preload_query' => null,
                    ),
                    array(
                        'filter_label' => 'b.assign_to',
                        'filter_display' => 'contractor_user_id',
                        'preload_query' => null,
                    ),
                ),
                'project_tasks' => array(
                    array(
                        'filter_label' => 'project_id',
                        'filter_display' => 'project_id',
                        'preload_query' => null,
                    ),
                    array(
                        'filter_label' => 'c.id',
                        'filter_display' => 'contractor_user_id',
                        'preload_query' => null,
                    ),
                    array(
                        'filter_label' => 'a.task_status',
                        'filter_display' => 'status',
                        'preload_query' => null,
                    ),
                ),
                'user_requests' => array(
                    array(
                        'filter_label' => 'project_task_id',
                        'filter_display' => 'project_task_id',
                        'preload_query' => null,
                    ),
                    array(
                        'filter_label' => 'user_id',
                        'filter_display' => 'user_id',
                        'preload_query' => null,
                    ),
                    array(
                        'filter_label' => 'request_status',
                        'filter_display' => 'request_status',
                        'preload_query' => null,
                    ),
                    array(
                        'filter_label' => 'request_from',
                        'filter_display' => 'request_type',
                        'preload_query' => null,
                    ),
                    array(
                        'filter_label' => 'e.assign_to',
                        'filter_display' => 'assignee_user_id',
                        'preload_query' => null,
                    ),
                    array(
                        'filter_label' => 'e.status',
                        'filter_display' => 'assignee_status',
                        'preload_query' => null,
                    ),
                    array(
                        'filter_label' => 'e.request_type',
                        'filter_display' => 'assignee_request_type',
                        'preload_query' => null,
                    ),
                    array(
                        'filter_label' => 'd.is_auditable',
                        'filter_display' => 'request_auditable',
                        'preload_query' => null,
                    ),
                    array(
                        'filter_label' => 'a.stage',
                        'filter_display' => 'request_stage',
                        'preload_query' => null,
                    ),
                    array(
                        'filter_label' => 'a.id',
                        'filter_display' => 'user_request_id',
                        'preload_query' => null,
                    ),
                    array(
                        'filter_label' => 'a.rejected_by',
                        'filter_display' => 'rejected_by',
                        'preload_query' => null,
                    ),
                ),
                'contractors' => array(
                    array(
                        'filter_label' => 'a.active',
                        'filter_display' => 'status',
                        'preload_query' => null,
                    ),
                ),
                'course_manager' => array(
                    array(
                        'filter_label' => 'a.session_id',
                        'filter_display' => 'session',
                        'preload_query' => null,
                    ),
                ),
                'examination_courses' => array(
                    array(
                        'filter_label' => 'a.session_id',
                        'filter_display' => 'session',
                        'preload_query' => null,
                    ),
                ),
                'course_request_claims' => array(
                    array(
                        'filter_label' => 'a.request_status',
                        'filter_display' => 'status',
                        'preload_query' => null,
                    ),
                ),
            );
		}

		if (array_key_exists($tablename, $result)) {
			return $result[$tablename];
		}
		return false;
	}

	/**
	 * This is the configuration for the edit form of the entities.
	 * exclude take an array of fields in the entities that should be removed from the form.
	 */
	private function buildUpdateConfig() {
		$this->updateConfig = array
        (

			//add new entry to this array
		);
	}

	public function getInsertConfig(?string $entities) {
		if (array_key_exists($entities, $this->insertConfig)) {
			$result = $this->insertConfig[$entities];
			if (($fil = $this->getFilter($entities))) {
				$result['filter'] = $fil;
			}
			$this->apiEntity = false;
			return $result;
		}
		if (($fil = $this->getFilter($entities))) {
			return array('filter' => $fil);
		}
		return false;
	}

	public function getUpdateConfig(?string $entities) {
		if (array_key_exists($entities, $this->updateConfig)) {
			$result = $this->updateConfig[$entities];
			if (($fil = $this->getFilter($entities))) {
				$result['filter'] = $fil;
			}
			return $result;
		}
		if (($fil = $this->getFilter($entities))) {
			return array('filter' => $fil);
		}
		return false;
	}
}
