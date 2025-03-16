<?php

namespace App\Filters;

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

        if (!$this->canProceed($request, $request->getUri()->getSegments(), $message)) {
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
        } else {
            $apiKey = getenv('xAppCandidateKey');
        }
        return (array_key_exists('HTTP_X_APP_KEY', $_SERVER) && $request->getServer('HTTP_X_APP_KEY') == $apiKey);
    }

    /**
     * This is to validate request
     * @param object $request [description]
     * @param array $args [description]
     * @return bool [type]          [description]
     */
    private function canProceed(object $request, array $args, &$message=null): bool
    {
        if ($this->isExempted($request, $args)) {
            return true;
        }

        return $this->validateAPIRequest($message);
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
            'POST::authenticate',
        ];
        $argument = $arguments[1];
        $argPath = strtoupper($request->getMethod()) . '::' . $argument;

        return in_array($argPath, $exemptionList);
    }

    private function validateAPIRequest(&$message = ''): bool
    {
        try {
            $token = getBearerToken();
            $token = decodeJwtToken($token);
            $decodedToken = $token->data;
            $id = $decodedToken->user_table_id; // the real users_id and any other users
            $userType = $decodedToken->user_type;
            $userType = loadClass($userType);
            $tempUser = new $userType(array('id' => $id));

            if (!$tempUser->load()) {
                return false;
            }
            $newUser = $tempUser;
            if (isset($decodedToken->user_type)) {
                $newUser->user_type = $decodedToken->user_type;
                $newUser->user_id = $decodedToken->id;
            }
            $_SERVER['CURRENT_USER'] = $newUser;
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
        $customer = currentAPIUser();
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