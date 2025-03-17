<?php

namespace App\Filters;

use App\Enums\AuthEnum;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
use Exception;
use App\Entities\Users_new;
use App\Entities\Students;
use App\Entities\Applicants;
use App\Models\WebSessionManager;

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
     * This is to validate request header
     * @param object $request [description]
     * @return bool [type]          [description]
     */
    private function validateHeader(object $request, string $type): bool
    {
        $apiKey = null;
        if ($type === 'admin') {
            $apiKey = getenv('xAppAdminKey');
        }else if ($type === 'student') {
            $apiKey = getenv('xAppStudentKey');
        }else if ($type === 'web-finance') {
            $apiKey = getenv('xAppFinanceKey');
        }else if ($type === 'apex') {
            $apiKey = getenv('xAppApexKey');
        }
        return (array_key_exists('HTTP_X_APP_KEY', $_SERVER) && $request->getServer('HTTP_X_APP_KEY') == $apiKey ||
            array_key_exists('HTTP_ACESS_TOKEN', $_SERVER) && $request->getServer('HTTP_ACESS_TOKEN') == $apiKey);
    }

    /**
     * This is to validate request
     * @param object $request [description]
     * @param array $args [description]
     * @return bool [type]          [description]
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
            $user_id = isset($token_array['data']) ? $token_array['data']->id : $token_array['sub'];
            $user_type = isset($token_array['data']) ? $token_array['data']->type : ($token_array['acc_type'] ?? null);

            // coming from the auth server
            if(isset($token_array['acc_type'])){
                $userNew = new Users_new;
                $userNew->id = $token_array['acc_type'];
                if(!$userNew->load()){
                    return false;
                }
                $token_array['data'] = $userNew->toArray();
            }

            $currentUser = false;
            if ($user_type === AuthEnum::STUDENT->value) {
                $students = new Students;
                $tempUser = $students->getWhere(['id' => $user_id], $c, 0, null, false);
                $tempUser[0]->password = null;
                $tempUser[0]->user_pass = null;
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
        $customer = WebSessionManager::currentAPIUser();
        $customer = $customer ? $customer->user_id : '';
        $time = Time::createFromTimestamp($request->getServer('REQUEST_TIME'));
        $time = $time->format('Y-m-d H:i:s');

        $param = [
            'user_id' => $customer,
            'host' => $request->getServer('HTTP_HOST'),
            'url' => $uri,
            'user_agent' => toUserAgent($request->getUserAgent()),
            'ip_address' => $request->getIPAddress(),
            'created_at' => $time,
        ];
        $builder->insert($param);
    }
}