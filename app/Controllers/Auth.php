<?php

namespace App\Controllers;

use App\Enums\AuthEnum;
use App\Models\Mailer1;
use App\Models\WebSessionManager;
use CodeIgniter\HTTP\RedirectResponse;
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
        $this->mailer = new Mailer1;
    }

    /**
     * @param string $userType
     * @return bool
     */
    private function allowOnlyMainEntity(string $userType): bool
    {
        $result = ['nlrc'];
        if (in_array($userType, $result)) {
            return false;
        }
        return true;
    }

    /**
     * This is to return the user based dashboard
     *
     * @param string $userType
     * @return string
     */
    private function getUserPage(string $userType): string
    {
        $link = array(
            'admin' => 'vc/admin/dashboard',
        );
        return $link[$userType];
    }

    /**
     * @return string|array|false|null
     * @throws Exception
     */
    public function web()
    {
        $validation = \Config\Services::validation();
        $validation->setRules([
            'email' => 'required',
            'password' => 'required',
            'isajax' => 'required',
        ]);

        $validateData = $this->request->getPost();
        $isAjax = isset($_POST['isajax']) && $_POST['isajax'] == "true";
        if (!$validation->run($validateData)) {
            $errors = $validation->getErrors();
            foreach ($errors as $error) {
                if ($isAjax) {
                    return buildResponse(false, $error);
                } else {
                    $this->webSessionManager->setFlashMessage('error', $error);
                    redirect(base_url('auth/login'));
                }
            }
        }
        $validData = $validation->getValidated();
        $username = $validData['email'];
        $password = $validData['password'];
        $remember = null;

        $user = loadClass('user');
        if (!$user->findBoth2($username)) {
            if ($isAjax) {
                return buildResponse(false, 'Invalid email or password');
            } else {
                $this->webSessionManager->setFlashMessage('error', 'invalid email or password');
                redirect(base_url('auth/login'));
            }
        }
        $user = $user->data();
        if ($user->status == '0') {
            return buildResponse(false, 'Your account is not activated');
        }
        $checkPass = decode_password(trim($password), $user->password);
        if (!$checkPass) {
            if ($isAjax) {
                return buildResponse(false, 'Invalid email or password');
            } else {
                $this->webSessionManager->setFlashMessage('error', 'invalid email or password');
                redirect(base_url('auth/login'));
            }
        }
        if ($user->user_type != 'admin' && $user->user_type != 'superagent' && $user->user_type != 'nlrc' && $user->user_type != 'influencer' && $user->user_type != 'promoter') {
            return buildResponse(false, 'Oops, invalid username or password');
        }
        $baseurl = base_url();
        if ($this->allowOnlyMainEntity($user->user_type)) {
            $this->webSessionManager->saveCurrentUser($user);
        } else {
            $this->webSessionManager->saveOnlyCurrentUser($user);
        }
        $baseurl .= $this->getUserPage($user->user_type);
        $user->last_login = formatToUTC();
        $user->update();

        if ($isAjax) {
            return buildResponse(true, $baseurl);
        } else {
            redirect($baseurl);
            exit;
        }
    }

    /**
     * @throws Exception
     */
    public function login()
    {
        $validation = service('validation');
        $validation->setRules([
            'username' => [
                'label' => 'username',
                'rules' => 'required',
            ],
            'password' => [
                'label' => 'password',
                'rules' => 'required',
            ],
        ]);

        $validateData = $this->request->getPost();
        if (!$validation->run($validateData)) {
            $errors = $validation->getErrors();
            foreach ($errors as $error) {
                return sendApiResponse(false, $error);
            }
        }

        $validData = $validation->getValidated();
        $username = $validData['username'];
        $password = $validData['password'];

        $user = loadClass('users');
        if (!$user->findUser($username, 'admin')) {
            return sendApiResponse(false, 'Invalid username or password');
        }
        $user = $user->data();
        if ($user->status == '0') {
            return sendApiResponse(false, 'Your account is not activated');
        }
        if (!decode_password($password, $user->password)) {
            return sendApiResponse(false, 'Invalid username or password');
        }
        $payload = $this->userPayloadData($user, $message);
        if (!$payload) {
            return sendApiResponse(false, $message);
        }
        return sendApiResponse(true, "You're successfully authenticated", $payload);
    }

    /**
     * This return the user payload data
     * @param object $user [description]
     * @param null $message
     * @return array|false [type]       [description]
     * @throws Exception
     */
    private function userPayloadData(object $user , &$message=null): bool|array
    {
        $tokens = $this->db->table('user_tokens')->getWhere(['user_id' => $user->id]);
        $expiration = time() + (60 * getenv('tokenExpiration'));

        if($tokens->getNumRows() > 0){
            $tokens = $tokens->getLastRow();
            if(!isTimeExpired($tokens->expiration)){
                if($tokens->status == '1'){
                    return [
                        'token' => $tokens->token,
                        'details' => json_decode($tokens->payload, true)
                    ];
                }
            }
            $this->db->table('user_tokens')->update(['status'=>0], ['user_id'=>$user->id]);
        }
        $userType = $this->webSessionManager->saveCurrentUser($user,true);
        if(!$userType){
            $message = "Oops, it appears your account is deactivated at the moment.";
            return false;
        }
        unset($userType['password']);
        $payload = $userType;
        $payload['type'] = AuthEnum::ADMIN;
        $payload['origin'] = base_url();

        $builder = $this->db->table('users');
        $builder->update(['last_login'=>formatToUTC()], ['id'=>$payload['id']]);
        $token = generateJwtToken($payload, $expiration);
        unset($payload['user_table_id']);

        $this->db->table('user_tokens')->insert([
            'user_id' => $payload['id'],
            'token' => $token,
            'payload' => json_encode($payload),
            'status' => 1,
            'expiration' => $expiration
        ]);
        return [
            'token' => $token,
            'details' => $payload
        ];
    }

    /**
     * @throws Exception
     */
    public function voterLogin()
    {
        if ( $this->request->getPost('username') != 'demo' && get_setting('disable_login') == 0) {
            return sendAPiResponse(false, "The system is currently not available, please try again later.");
        }

        $validation = service('validation');
        $validation->setRules([
            'username' => [
                'label' => 'username',
                'rules' => 'required',
            ],
            'password' => [
                'label' => 'password',
                'rules' => 'required',
            ],
        ]);

        $validateData = $this->request->getPost();
        if (!$validation->run($validateData)) {
            $errors = $validation->getErrors();
            foreach ($errors as $error) {
                return sendApiResponse(false, $error);
            }
        }

        $validData = $validation->getValidated();
        $username = $validData['username'];
        $password = $validData['password'];

        $user = loadClass('users');
        if (!$user->findUser($username, 'voters')) {
            return sendApiResponse(false, 'Invalid username or password');
        }
        $user = $user->data();
        if ($user->status == '0') {
            return sendApiResponse(false, 'Your account is not activated');
        }
        if (!decode_password($password, $user->password)) {
            return sendApiResponse(false, 'Invalid username or password');
        }
        $payload = $this->voterPayloadData($user, $message);
        if (!$payload) {
            return sendApiResponse(false, $message);
        }
        return sendApiResponse(true, "You're successfully authenticated", $payload);
    }

    /**
     * @throws Exception
     */
    private function voterPayloadData(object $user , &$message=null): bool|array
    {
        $tokens = $this->db->table('user_tokens')->getWhere(['user_id' => $user->id]);
        if($tokens->getNumRows() > 0){
            $tokens = $tokens->getLastRow();
            if($tokens->status == '1'){
                return [
                    'token' => $tokens->token,
                    'details' => json_decode($tokens->payload, true)
                ];
            }
            $message = "It appears your account had already voted";
            return false;
        }
        $userType = $this->webSessionManager->saveCurrentUser($user,true);
        if(!$userType){
            $message = "Oops, it appears your account is deactivated at the moment.";
            return false;
        }
        unset($userType['password']);
        $payload = $userType;
        $payload['type'] = AuthEnum::VOTER;
        $payload['origin'] = base_url();

        $builder = $this->db->table('users');
        $builder->update(['last_login'=>formatToUTC()], ['id'=>$payload['id']]);
        $token = generateJwtToken($payload);
        unset($payload['user_table_id']);

        $this->db->table('user_tokens')->insert([
            'user_id' => $payload['id'],
            'token' => $token,
            'payload' => json_encode($payload),
            'status' => 1,
        ]);
        return [
            'token' => $token,
            'details' => $payload
        ];
    }

    public function logout()
    {
        $currentUser = currentAPIUser();
        if($currentUser){
            $this->db->table('user_tokens')->update(['status'=>0], ['user_id'=>$currentUser->user_id]);
        }
        $this->webSessionManager->logout();
        return sendApiResponse(true, 'You have successfully logged out');
    }
}
