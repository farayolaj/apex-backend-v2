<?php

namespace App\Controllers;

use App\Entities\Staffs;
use App\Entities\Students;
use App\Enums\AuthEnum as AuthType;
use App\Models\Mailer;
use App\Models\WebSessionManager;
use Exception;

/**
 * This is the authentication class handler for the web
 */
class Auth extends BaseController
{
    private WebSessionManager $webSessionManager;
    private $mailer;

    public function __construct()
    {
        $this->webSessionManager = new WebSessionManager;
        $this->mailer = new Mailer;
    }

    /**
     * @throws Exception
     */
    private function getUser(string $table, string $ruser = 'user_login', string $rpass = 'user_pass',
                             string $usernameField = 'user_login', string $passwordField = 'user_pass', $hasActive = 1)
    {
        $username = $this->request->getPost($usernameField);
        $password = $this->request->getPost($passwordField);
        if (!($username || $password)) {
            return sendApiResponse(false, 'Invalid entry data');
        }

        $tableObj = loadClass($table);
        $param = [
            $ruser => $username,
        ];

        if ($hasActive) {
            $param['active'] = 1;
        }

        if ($table == 'students') {
            $query = "SELECT students.* from students where user_login=? and students.active = '1' or 
                exists (select * from academic_record where student_id=students.id and 
                (matric_number=? or application_number = ?))";

            $result = $this->db->query($query, [$username, $username, $username]);
            if ($result->getNumRows() > 0) {
                $user = $result->getResultArray();
                $user = [new Students($user[0])];
            }
        } else {
            // tableObj here would be Users_new class
            $user = $tableObj->getWhere($param, $c, 0, null, false);
        }

        if (!$user) {
            return sendApiResponse(false, 'Invalid username or password');
        }

        $user = $user[0];
        if ($table === 'users_new') {
            $user->last_logged_in = date('Y-m-d H:i:s');
            $user->update();
            logAction($this->db, 'auth_user_login', $user->user_login);
        }

        if (!decode_password($password, $user->$rpass)) {
            return sendApiResponse(false, 'Invalid username or password');
        }
        return $user;
    }

    /**
     *
     * THIS METHOD HANDLES APEX AUTH LOGIN
     * @throws Exception
     */
    public function web()
    {
        $user = $this->getUser('users_new', 'user_login', 'password', 'username', 'password');
        if (!$user) {
            return false;
        }
        $userDetails = $this->getUserDetails($user);
        $payload = $user->toArray() ?? null;
        $payload['user_department'] = null;
        $userID = $user->id;
        $loginType = 'admin';

        if ($userDetails) {
            $payload = array_merge($payload, $userDetails->toArray());
            if ($userDetails->user_department && $userDetails->user_department != 0) {
                $department = $this->getUserDepartment($userDetails->user_department);
                if ($department) {
                    $payload['user_department'] = [
                        'id' => $department->id,
                        'name' => $department->name,
                    ];
                    $loginType = 'department';
                }
            }
        }
        $payload['id'] = $userID;
        unset($payload['user_pass'], $payload['password']);

        $payload['type'] = AuthType::ADMIN->value;
        $payload['origin'] = base_url();
        $arr = [
            'token' => generateJwtToken($payload),
            'profile' => $payload,
            'login_type' => $loginType
        ];
        return sendApiResponse(true, "You've successfully logged in", $arr);
    }

    private function getUserDepartment($user_department)
    {
        return $this->db->table('department')->where(['id' => $user_department, 'type' => 'academic'])->get()->getRow();
    }

    /**
     * This is to validate the student user login first, after wards password
     * @return true
     */
    public function validate_student()
    {
        $userLogin = $this->request->getPost('user_login');
        $query = "SELECT students.* from students where user_login=? and students.active = '1' or exists 
		    (select * from academic_record where student_id=students.id and (matric_number=? or application_number = ?))";
        $result = $this->db->query($query, [$userLogin, $userLogin, $userLogin]);
        if ($result->getNumRows() <= 0) {
            return sendApiResponse(false, 'No matching record found. Contact school administrator');
        }
        return sendApiResponse(true, 'Validate success');
    }

    /**
     * This is the student validation auth code
     * @return true
     * @throws Exception
     */
    public function student()
    {
        $student = $this->getUser('students', $ruser = 'user_login', $rpass = 'password');
        if (!$student) {
            return sendApiResponse(false, 'Invalid username or password');
        }
        $payload = [
            'id' => $student->id,
            'type' => AuthType::STUDENT->value,
            'firstname' => $student->firstname,
            'lastname' => $student->lastname,
            'othernames' => $student->lastname,
            'origin' => base_url()
        ];
        $token = generateJwtToken($payload);
        $academicRecord = $student->academic_record;
        $student->updatePassportPath();
        $student->orientation_attendance_date = $student->getFacultyAttendance($student, $academicRecord->programme_id);
        $student = $student->toArray() ?? null;
        unset($student['password'], $student['id'], $student['user_pass'], $student['session_key']);

        $payload = [
            'token' => $token,
            'profile' => $student
        ];
        return sendApiResponse(true, 'success', $payload);
    }

    private function getUserDetails(object $user)
    {
        $content = [
            'staff' => 'staffs',
            'contractor' => 'contractors',
        ];
        $entity = $user->user_type ?? 'staff';
        $entity = strtolower($entity);
        $entity = $content[$entity] ?? null;
        if ($entity) {
            $entityObj = loadClass($entity);
            $entity = $entityObj->getWhere(['id' => $user->user_table_id], $c, 0, null, false);
            if ($entity) {
                $entity = $entity[0];
            }
        }
        return $entity;
    }

    /**
     *
     * THIS METHOD HANDLES TRANSACTION OUTFLOW AUTH LOGIN
     * @throws Exception
     */
    public function financeAuth()
    {
        $user = $this->getUser('users_new', 'user_login', 'password', 'username', 'password');
        if (!$user) {
            return false;
        }
        $userDetails = $this->getUserDetails($user, $user->user_type);
        $payload = $user->toArray() ?? null;
        $userID = $user->id;
        $userRoleSlug = null;
        if ($userDetails) {
            $userRoleSlug = @$userDetails->outflow_slug ?? null;
            $payload = array_merge($payload, $userDetails->toArray());
        }

        if (isset($payload['units_id']) && $payload['units_id'] != 0) {
            $staffObj = new Staffs;
            $payload['unit'] = $staffObj->getStaffDepartment($payload['units_id'])->name;
        }

        if (isset($payload['avatar'])) {
            $payload['avatar'] = userImagePath($payload['avatar']);
        }

        $payload['id'] = $userID;
        unset($payload['user_pass'], $payload['password']);
        $payload['type'] = AuthType::FINANCE_OUTFLOW;
        $payload['origin'] = base_url();
        $payload['user_role'] = $userRoleSlug;

        $payload = [
            'token' => generateJwtToken($payload),
            'profile' => $payload,
        ];
        return sendApiResponse(true , "You've successfully logged in", $payload);
    }

    /**
     * @deprecated - There is tendency that this method will be removed, as no usage was found
     * THIS METHOD HANDLES CONTRACTOR AUTH LOGIN
     * @throws Exception
     */
    public function contractorAuth(string $entity)
    {
        $user = $this->getUser('users_new', 'user_login', 'password', 'username', 'password');
        $baseurl = base_url();

        $arr['status'] = true;
        $arr['message'] = $baseurl;
        $payload = $user->toArray() ?? null;
        unset($payload['user_pass'], $payload['password']);
        $payload['type'] = AuthType::CONTRACTOR->value;
        $payload['origin'] = base_url();
        $token = generateJwtToken($payload);
        $arr['payload'] = [
            'token' => $token,
            'profile' => $payload
        ];
        return sendApiResponse(true, $arr['message'], $arr['payload']);
    }

    public function logout()
    {
        $currentUser = WebSessionManager::currentAPIUser();
        $this->webSessionManager->logout();
        return sendApiResponse(true, 'You have successfully logged out');
    }
}
