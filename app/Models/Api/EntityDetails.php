<?php 

/**
 * This will get different entity details that can be use inside the APIs
 */
namespace App\Models\Api;

use App\Libraries\EntityLoader;
use App\Models\WebSessionManager;
use App\Traits\CommonTrait;

class EntityDetails
{
    use CommonTrait;

    private $db;

	public function __construct()
    {
        helper('string');
        $this->db = db_connect();
    }

    /**
     * @param $uuid
     * @return array|<missing>
     */
    public function getApplicantsDetails($uuid): array
    {
        $extract = self::extractApplicantEntity($uuid);
        $id = $extract[0];
        $entity = $extract[1];
        EntityLoader::loadClass($this, $entity);
        EntityLoader::loadClass($this, 'sessions');

        $this->$entity->entityModel->id = $id;
        if (!$this->$entity->load()) {
            return [];
        }
        $result = $this->$entity->toArray();
        $result['phone'] = decryptData($result['phone']);
        $result['phone2'] = decryptData($result['phone2']);
        $session = $this->sessions->getSessionById($result['session_id']);
        $result['session_id'] = $session[0]['date'];
        $result['programme_name'] = $this->$entity->programme->name ?? null;
        $result['olevel_details'] = json_decode($result['olevel_details']);
        $result['programmes'] = $this->loadProgramme();
        $result['transaction'] = $this->$entity->applicantTransaction ?? null;
        $result['academic_record'] = $result['admission_status'] == 'Admitted' ? $this->$entity->academicRecord : null;
        $result['applicant_type'] = $entity;
        $currentUser = WebSessionManager::currentAPIUser();
        logAction('view_applicant_detail', $currentUser->user_login);
        return $result;
    }

    public function getStaffsDetails($id)
    {
        EntityLoader::loadClass($this, 'staffs');
        EntityLoader::loadClass($this, 'users_new');
        EntityLoader::loadClass($this, 'roles');
        $this->staffs->id = $id;
        if (!$this->staffs->load()) {
            return [];
        }
        $result = $this->staffs->toArray();
        $userInfo = $this->users_new->getUserInfo('staffs', 'staff', $this->staffs->id);
        $userID = $userInfo['user_id'];
        $role = $this->roles->getUserRole($userID);
        $result['username'] = $userInfo['username'];
        $result['orig_user_id'] = $userID;
        if ($result['avatar']) {
            $result['avatar'] = userImagePath($result['avatar']);
        }
        if ($role) {
            $result['role'] = $role['id'];
        }
        return $result;
    }

    /**
     * @param mixed $id
     * @param mixed $returnResult
     * @return array
     */
    public function getStudentsDetails($id, $returnResult = false): array
    {
        EntityLoader::loadClass($this, 'students');
        EntityLoader::loadClass($this, 'users_new');
        EntityLoader::loadClass($this, 'sessions');
        $this->students->id = $id;
        if (!$this->students->load()) {
            return [];
        }
        $result = $this->students->getStudentViewRecord();
        $result['phone'] = decryptData($result['phone']);
        $passport = $this->students->updatePassportPath();
        $currentUser = WebSessionManager::currentAPIUser();
        unset($result['password'], $result['user_pass']);
        if (!$returnResult) {
            $result['transaction'] = $this->students->studentTransaction;
            $result['transaction_archive'] = $this->students->studentTransactionArchive;
            $temp = null;
            if (json_decode($result['verified_by'])) {
                $verify = json_decode($result['verified_by'], true);
                $i = 1;
                $param = [];
                foreach ($verify as $item) {
                    $users = $this->users_new->getRealUserInfo(@$item['user_id'], 'staffs', 'staff') ?? null;
                    $name = ($users) ? $users['title'] . " " . $users['lastname'] . " " . $users['firstname'] : null;
                    $param['user_id'] = $name;
                    $param['attempt'] = $i;
                    $param['verify_status'] = $result['is_verified'];
                    $temp[] = $param;
                    $i++;
                }
                $result['verified_by'] = $temp;
            }
        }

        $entryYear = $this->sessions->getSessionById($result['year_of_entry']);
        $result['entry_year'] = $entryYear ? $entryYear[0]['date'] : null;
        $result['verify_comments'] = json_decode($result['verify_comments']);
        $result['jamb_details'] = json_decode($result['jamb_details']);
        $result['olevel_details'] = json_decode($result['olevel_details']);
        $result['alevel_details'] = json_decode($result['alevel_details']);
        $result['nce_nd_hnd'] = json_decode($result['nce_nd_hnd']);
        $result['institutions_attended'] = json_decode($result['institutions_attended']);
        $result['verification_documents'] = $this->students->getStudentVerificationDocuments();
        $result['passport'] = $passport;
        if (!$returnResult) {
            logAction( 'view_student_detail', $currentUser->user_login, $id);
        }
        return $result;
    }

    /**
     * @param mixed $id
     */
    public function getStudent_verification_feeDetails($id): array
    {
        return $this->getStudentsDetails($id, true);
    }

    /**
     * This get all the programme
     * @return array|<missing>
     */
    private function loadProgramme(): array
    {
        $query = "SELECT a.id as id,a.name as value from programme a where a.active = '1' group by id, value order by value asc";
        $query = $this->db->query($query);
        $result = [];
        if ($query->getNumRows() <= 0) {
            return $result;
        }
        return $query->getResultArray();
    }

    public function getUser_requestsDetails($id)
    {
        EntityLoader::loadClass($this, 'user_requests');
        $this->user_requests->id = $id;
        if (!$this->user_requests->load()) {
            return null;
        }
        $result = $this->user_requests->toArray();
        $result['initiated_by'] = 'initiated_by';
        $result['prev_request'] = 'prev_request';
        $result = $this->user_requests->loadExtras($result);
        $requestType = $this->user_requests->request_type->toArray() ?? null;
        $result['request_type'] = $requestType['name'] ?? null;
        $result['transaction'] = [];

        return $result;
    }

    public function getUser_requests_archiveDetails($id)
    {
        EntityLoader::loadClass($this, 'user_requests_archive');
        $this->user_requests_archive->id = $id;
        if (!$this->user_requests_archive->load()) {
            return null;
        }
        $result = $this->user_requests_archive->toArray();
        $result = $this->user_requests_archive->loadExtras($result);
        $requestType = $this->user_requests_archive->request_type->toArray() ?? null;
        $result['request_type'] = $requestType['name'] ?? null;

        return $result;
    }

    public function getStaff_departmentDetails($id)
    {
        EntityLoader::loadClass('department');
        $this->department->id = $id;
        if (!$this->department->load()) {
            return null;
        }
        return $this->department->toArray();
    }

    public function getRolesDetails($id)
    {
        EntityLoader::loadClass($this, 'roles');
        $this->roles->id = $id;
        if (!$this->roles->load()) {
            return null;
        }
        return $this->roles->toArray();
    }

    public function getRoles_permissionDetails($id)
    {
        EntityLoader::loadClass($this, 'roles_permission');
        EntityLoader::loadClass($this, 'roles');
        $this->roles_permission->id = $id;
        if (!$this->roles_permission->load()) {
            return null;
        }
        $result = $this->roles_permission->toArray();
        $content = $this->roles_permission->loadExtras($result, false);
        $content['role_id'] = json_decode($content['role_id'], true);
        return $content;
    }

    public function getFacultyDetails($id)
    {
        EntityLoader::loadClass($this, 'faculty');
        $this->faculty->id = $id;
        if (!$this->faculty->load()) {
            return null;
        }
        return $this->faculty->toArray();
    }

    public function getCourse_managerDetails($id)
    {
        EntityLoader::loadClass($this, 'course_manager');
        $this->course_manager->id = $id;
        if (!$this->course_manager->load()) {
            return null;
        }
        $result = $this->course_manager->toArray();
        return $this->course_manager->loadExtras($result, true);
    }

    public function getGradesDetails($id)
    {
        EntityLoader::loadClass($this, 'grades');
        $this->grades->id = $id;
        if (!$this->grades->load()) {
            return null;
        }
        return $this->grades->toArray();
    }

    public function getSessionsDetails($id)
    {
        permissionAccess('session_listing', 'view');
        EntityLoader::loadClass($this, 'sessions');
        $this->sessions->id = $id;
        if (!$this->sessions->load()) {
            return null;
        }
        return $this->sessions->toArray();
    }

    public function getCourse_committeeDetails($id)
    {
        EntityLoader::loadClass($this, 'course_committee');
        EntityLoader::loadClass($this, 'users_new');
        $this->course_committee->id = $id;
        if (!$this->course_committee->load()) {
            return null;
        }
        $result = $this->course_committee->toArray();
        return $this->course_committee->loadExtras($result, true);
    }

    public function getCourse_request_claimsDetails($id)
    {
        EntityLoader::loadClass($this, 'user_requests');
        EntityLoader::loadClass($this, 'users_new');
        EntityLoader::loadClass($this, 'course_request_claims');
        $this->user_requests->id = $id;
        if (!$this->user_requests->load()) {
            return null;
        }
        $result = $this->user_requests->toArray();
        unset($result['admon_reference'], $result['deduction'], $result['retire_advance_doc'],
            $result['voucher_document'], $result['withhold_tax'], $result['vat'], $result['stamp_duty'], $result['deduction_amount']);

        return $this->course_request_claims->loadExtras($result, true);
    }

    public function getPayment_bookstoreDetails($id)
    {
        EntityLoader::loadClass($this, 'payment_bookstore');
        $this->payment_bookstore->id = $id;
        if (!$this->payment_bookstore->load()) {
            return null;
        }
        $result = $this->payment_bookstore->toArray();
        unset($result['service_type_id'], $result['service_charge'], $result['amount'], $result['subaccount_amount']);

        return $result;
    }

    public function getBookstore_transactionDetails($id)
    {
        EntityLoader::loadClass($this, 'payment_bookstore');
        $transaction = $this->payment_bookstore->getBookstoreTransaction(null, $id);
        if (empty($transaction)) {
            $result = null;
        }
        return $transaction[0];
    }

    public function getCourse_mappingDetails($id)
    {
        EntityLoader::loadClass($this, 'course_mapping');
        $this->course_mapping->id = $id;
        if (!$this->course_mapping->load()) {
            return null;
        }
        $data = $this->course_mapping->toArray();
        return $this->course_mapping->loadExtras($data);
    }


}

