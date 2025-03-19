<?php 

/**
 * This will get different entity details that can be use inside the APIs
 */
namespace App\Models\Api;

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
        $entityModel = loadClass($this->load, $entity);
        $sessionsModel = loadClass($this->load, 'sessions');

        $entityModel->id = $id;
        if (!$entityModel->load()) {
            return [];
        }
        $result = $entityModel->toArray();
        $result['phone'] = decryptData($result['phone']);
        $result['phone2'] = decryptData($result['phone2']);
        $session = $sessionsModel->getSessionById($result['session_id']);
        $result['session_id'] = $session[0]['date'];
        $result['programme_name'] = $entityModel->programme->name ?? null;
        $result['olevel_details'] = json_decode($result['olevel_details']);
        $result['programmes'] = $this->loadProgramme();
        $result['transaction'] = $entityModel->applicantTransaction ?? null;
        $result['academic_record'] = $result['admission_status'] == 'Admitted' ? $entityModel->academicRecord : null;
        $result['applicant_type'] = $entity;
        $currentUser = WebSessionManager::currentAPIUser();
        logAction($this->db,'view_applicant_detail', $currentUser->user_login);
        return $result;
    }

    public function getStaffsDetails($id)
    {
        $staffsModel = loadClass('staffs');
        $usersNewModel = loadClass('users_new');
        $rolesModel = loadClass('roles');

        $staffsModel->id = $id;
        if (!$staffsModel->load()) {
            return [];
        }
        $result = $staffsModel->toArray();
        $userInfo = $usersNewModel->getUserInfo('staffs', 'staff', $staffsModel->id);
        $userID = $userInfo['user_id'];
        $role = $rolesModel->getUserRole($userID);
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
        $studentsModel = loadClass('students');
        $usersNewModel = loadClass('users_new');
        $sessionsModel = loadClass('sessions');

        $studentsModel->id = $id;
        if (!$studentsModel->load()) {
            return [];
        }
        $result = $studentsModel->getStudentViewRecord();
        $result['phone'] = decryptData($result['phone']);
        $passport = $studentsModel->updatePassportPath();
        $currentUser = WebSessionManager::currentAPIUser();
        unset($result['password'], $result['user_pass']);
        if (!$returnResult) {
            $result['transaction'] = $studentsModel->studentTransaction;
            $result['transaction_archive'] = $studentsModel->studentTransactionArchive;
            $temp = null;
            if (json_decode($result['verified_by'])) {
                $verify = json_decode($result['verified_by'], true);
                $i = 1;
                $param = [];
                foreach ($verify as $item) {
                    $users = $usersNewModel->getRealUserInfo(@$item['user_id'], 'staffs', 'staff') ?? null;
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

        $entryYear = $sessionsModel->getSessionById($result['year_of_entry']);
        $result['entry_year'] = $entryYear ? $entryYear[0]['date'] : null;
        $result['verify_comments'] = json_decode($result['verify_comments']);
        $result['jamb_details'] = json_decode($result['jamb_details']);
        $result['olevel_details'] = json_decode($result['olevel_details']);
        $result['alevel_details'] = json_decode($result['alevel_details']);
        $result['nce_nd_hnd'] = json_decode($result['nce_nd_hnd']);
        $result['institutions_attended'] = json_decode($result['institutions_attended']);
        $result['verification_documents'] = $studentsModel->getStudentVerificationDocuments();
        $result['passport'] = $passport;
        if (!$returnResult) {
            logAction($this->db, 'view_student_detail', $currentUser->user_login, $id);
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
        $userRequestsModel = loadClass('user_requests');
        $userRequestsModel->id = $id;
        if (!$userRequestsModel->load()) {
            return null;
        }
        $result = $userRequestsModel->toArray();
        $result['initiated_by'] = 'initiated_by';
        $result['prev_request'] = 'prev_request';
        $result = $userRequestsModel->loadExtras($result);
        $requestType = $userRequestsModel->request_type->toArray() ?? null;
        $result['request_type'] = $requestType['name'] ?? null;
        $result['transaction'] = [];

        return $result;
    }

    public function getUser_requests_archiveDetails($id)
    {
        $userRequestArchiveModel = loadClass('user_requests_archive');
        $userRequestArchiveModel->id = $id;
        if (!$userRequestArchiveModel->load()) {
            return null;
        }
        $result = $userRequestArchiveModel->toArray();
        $result = $userRequestArchiveModel->loadExtras($result);
        $requestType = $userRequestArchiveModel->request_type->toArray() ?? null;
        $result['request_type'] = $requestType['name'] ?? null;

        return $result;
    }

    public function getStaff_departmentDetails($id)
    {
        $department = loadClass('department');
        $department->id = $id;
        if (!$department->load()) {
            return null;
        }
        return $department->toArray();
    }

    public function getRolesDetails($id)
    {
        $roles = loadClass( 'roles');
        $roles->id = $id;
        if (!$roles->load()) {
            return null;
        }
        return $roles->toArray();
    }

    public function getRoles_permissionDetails($id)
    {
        $rolesPermissionModel = loadClass('roles_permission');
        $rolesPermissionModel->id = $id;
        if (!$rolesPermissionModel->load()) {
            return null;
        }
        $result = $rolesPermissionModel->toArray();
        $content = $rolesPermissionModel->loadExtras($result, false);
        $content['role_id'] = json_decode($content['role_id'], true);
        return $content;
    }

    public function getFacultyDetails($id)
    {
        $facultyModel = loadClass( 'faculty');
        $facultyModel->id = $id;
        if (!$facultyModel->load()) {
            return null;
        }
        return $facultyModel->toArray();
    }

    public function getCourse_managerDetails($id)
    {
        $courseManagerModel = loadClass('course_manager');
        $courseManagerModel->id = $id;
        if (!$courseManagerModel->load()) {
            return null;
        }
        $result = $courseManagerModel->toArray();
        return $courseManagerModel->loadExtras($result, false);
    }

    public function getGradesDetails($id)
    {
        $gradesModel = loadClass('grades');
        $gradesModel->id = $id;
        if (!$gradesModel->load()) {
            return null;
        }
        return $gradesModel->toArray();
    }

    public function getSessionsDetails($id)
    {
        permissionAccess('session_listing', 'view');
        $sessionsModel = loadClass('sessions');
        $sessionsModel->id = $id;
        if (!$sessionsModel->load()) {
            return null;
        }
        return $sessionsModel->toArray();
    }




}

