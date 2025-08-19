<?php

namespace App\Filters;

use App\Entities\Applicants;
use App\Entities\Students;
use App\Entities\Users_new;
use App\Enums\AuthEnum;
use App\Libraries\EntityLoader;
use App\Models\WebSessionManager;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
use Exception;

class ApiAuthFilter implements FilterInterface
{

    /**
     * [before description]
     * @param RequestInterface $request [description]
     * @param null $arguments
     * @return void [type]   [description]
     * @throws Exception
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // Do something here
        helper(['security', 'string']);
        $type = $arguments[0];
        $response = service('response');
        $this->logRequest($request);

        if (!$this->validateHeader($request, $type)) {
            return $response->setStatusCode(405)->setJSON(['status' => false, 'message' => 'Authorization denied']);
        }

        if (!$this->canProceed($request, $request->getUri()->getSegments(), $type, $message)) {
            $message = $message ?? 'Authorization denied';
            return $response->setStatusCode(401)->setJSON(['status' => false, 'message' => $message]);
        }

    }

    /**
     * [after description]
     * @param RequestInterface $request [description]
     * @param ResponseInterface $response [description]
     * @param null $arguments
     * @return void [type]                       [description]
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
        // Do something here
        $response->setHeader('Content-Type', 'application/json');
    }

    /**
     * This is to validate the request header
     * @param object $request [description]
     * @return bool [type] [description]
     */
    private function validateHeader(object $request, string $type): bool
    {
        $apiKey = null;
        if ($type === AuthEnum::ADMIN->value) {
            $apiKey = env('xAppAdminKey');
        }else if ($type === AuthEnum::STUDENT->value) {
            $apiKey = env('xAppStudentKey');
        }else if ($type === AuthEnum::FINANCE_OUTFLOW->value) {
            $apiKey = env('xAppFinanceKey');
        }else if ($type === AuthEnum::APEX->value) {
            $apiKey = env('xAppApexKey');
        }
        return (array_key_exists('HTTP_X_APP_KEY', $_SERVER) && $request->getServer('HTTP_X_APP_KEY') == $apiKey ||
            array_key_exists('HTTP_ACESS_TOKEN', $_SERVER) && $request->getServer('HTTP_ACESS_TOKEN') == $apiKey);
    }

    /**
     * This is to validate request
     * @param object $request [description]
     * @param array $args [description]
     * @return bool [type] [description]
     */
    private function canProceed(object $request, array $args, string $type, &$message=null): bool
    {
        if ($this->isExempted($request, $args)) {
            return true;
        }

        return $this->validateAPIRequest($type, $message);
    }

    /**
     * This is to exempt certain request from the jwt auth
     * @param object $request
     * @param array $arguments
     * @return boolean
     */
    private function isExempted(object $request, array $arguments): bool
    {
        $exemptionList = [
            'POST::register',
            'POST::request_change',
            'POST::change_pass',
            'POST::reset_password',
            'GET::baseUrl',
            'POST::applicant_fee_details',
            'POST::authenticate',
            'POST::validate_student',
        ];
        $argument = $arguments[1];
        $argPath = strtoupper($request->getMethod()) . '::' . $argument;

        return in_array($argPath, $exemptionList);
    }

    private function validateAPIRequest(string $userType = 'student', &$message = ''): bool
    {
        try {
            $token = getBearerToken();
            $token = decodeJwtToken($token);
            $token_array = (array) $token;

            $user_id = null;
            $user_type = null;

            if (isset($token_array['data'])) {
                $user_id = $token_array['data']->id;
                $user_type = $token_array['data']->type;
            } else if (isset($token_array['sub'])) {
                $sub = explode('-', $token_array['sub']); // Todo: change - to ::
                $user_type = $sub[0];
                $user_id = $sub[1];
            } else {
                $message = 'Invalid token';
                return false;
            }

            $departmentModel = loadClass('department');
            $excludeUsers = [AuthEnum::STUDENT->value, AuthEnum::APPLICANT->value];
            // coming from the auth server
            if ($user_id && !in_array($user_type, $excludeUsers)) {
                $userNew = EntityLoader::loadClass($this, 'users_new');
                $userNew->id = $user_id;

                if (!$userNew->load()) {
                    return false;
                }

                $payload = array_merge($userNew->toArray(), ["type" => $user_type]);
                $payload['user_department'] = null;

                $userDetails = $userNew->getUserDetails($userNew);
                if ($userDetails) {
                    $payload = array_merge($payload, $userDetails->toArray());
                    $payload['id'] = $user_id;
                    if ($userDetails->user_department && $userDetails->user_department != 0) {
                        $department = $departmentModel->getUserDepartment($userDetails->user_department);
                        if ($department) {
                            $payload['user_department'] = [
                                'id' => $department->id,
                                'name' => $department->name,
                            ];
                        }
                    }
                }
                $token_array['data'] = $payload;
            }

            $currentUser = false;
            if ($user_type === AuthEnum::STUDENT->value) {
                $students = new Students;
                $tempUser = $students->getWhere(['id' => $user_id], $c, 0, null, false);
                $currentUser = $tempUser[0];
            }

            if ($user_type === AuthEnum::APPLICANT->value) {
                $applicants = new Applicants;
                $tempUser = $applicants->getWhere(['id' => $user_id], $c, 0, null, false);
                $currentUser = $tempUser[0];
            }

            if ($user_type === AuthEnum::ADMIN->value) {
                $value = (array) $token_array['data'];
                $currentUser = new Users_new($value);
            }

            if ($user_type === AuthEnum::FINANCE_OUTFLOW->value) {
                $value = (array) $token_array['data'];
                $currentUser = new Users_new($value);
            }

            if ($user_type === AuthEnum::CONTRACTOR->value) {
                $value = (array) $token_array['data'];
                $currentUser = new Users_new($value);
            }
            // this auth type here is for apex mobile
            if ($user_type === AuthEnum::APEX->value) {
                $value = (array) $token_array['data'];
                $currentUser = new Users_new($value);
            }

            if (!$currentUser) {
                return false;
            }

            // load current user and user type in the server global variable
            WebSessionManager::rememberUser($currentUser, $user_type);
            return true;

        } catch (Exception $e) {
            $message = "Unauthorized access";
            return false;
        }
    }

    /**
     * This is to track users activity on the platform
     * @param  [type] $request [description]
     * @return void [type]          [description]
     * @throws Exception
     */
    private function logRequest($request): void
    {
        $uri = $request->getUri();
        $uri = $uri->getPath();
        $db = db_connect();
        $builder = $db->table('audit_logs');
        $time = Time::createFromTimestamp($request->getServer('REQUEST_TIME'));
        $time = $time->format('Y-m-d H:i:s');

        $param = [
            'host' => $request->getServer('HTTP_HOST'),
            'url' => $uri,
            'user_agent' => toUserAgent($request->getUserAgent()),
            'ip_address' => $request->getIPAddress(),
            'created_at' => $time,
        ];
        $builder->insert($param);
    }
}