<?php

use App\Libraries\ApiResponse;
use App\Models\WebSessionManager;
use CodeIgniter\Config\Factories;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

if (!function_exists("encryptData")) {
    function encryptData($data): string
    {
        $key = env('customEncrytKey');
        $method = 'aes-256-cbc';
        $ivSize = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($ivSize);
        $encrypted = openssl_encrypt($data, $method, $key, 0, $iv);
        return base64_encode($iv . $encrypted);
    }
}

if (!function_exists('decryptData')) {
    function decryptData($data)
    {
        $key = env('customEncrytKey');;
        if (strlen((string)$data) < 20) {
            return $data;
        }
        $method = 'aes-256-cbc';
        $ivSize = openssl_cipher_iv_length($method);
        $data = base64_decode($data);

        $iv = substr($data, 0, $ivSize);
        $encrypted = substr($data, $ivSize);
        return openssl_decrypt($encrypted, $method, $key, 0, $iv);
    }
}

if (!function_exists('permissionAccess')) {
    function permissionAccess(string $permission, ?string $message = null)
    {
        $currentUser = WebSessionManager::currentAPIUser();
        if (!checkPermission($permission, $currentUser->id)) {
            if ($message == 'create') {
                $message = "It looks like you do not have access to create item on the page.";
            } else if ($message == 'view') {
                $message = "It looks like you do not have access to view this page.";
            } else if ($message == 'edit') {
                $message = "It looks like you do not have access to edit item on the page.";
            } else if ($message == 'delete') {
                $message = "It looks like you do not have access to delete item on the page";
            } else {
                $message = "It looks like you do not have access to perform the action.";
            }
            return ApiResponse::error($message, null, 403);
        }
    }
}

if (!function_exists('checkPermission')) {
    function checkPermission($permission, $userID): bool
    {
        $db = db_connect();
        $query = $db->table('roles_permission')->getWhere(array('permission' => $permission));
        if ($query->getNumRows() > 0) {
            $role = $query->getRow();
            $user_role = get_user_role_id($userID);
            $roles_array = json_decode($role->role_id, true);
            if (in_array($user_role, $roles_array)) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('isTimeExpired')) {
    function isTimeExpired($expirationTime, $leeWay = 60): bool
    {
        $currentTime = $leeWay ? time() - $leeWay : time();
        return $currentTime > $expirationTime;
    }
}

/**
 * Encode the token to JWT
 */
if (!function_exists('generateJwtToken')) {
    function generateJwtToken($payload): string
    {
        $key = env('jwtKey');
        $expiration = time() + (60 * env('tokenExpiration'));
        // Make an array for the JWT Payload
        $payload = array(
            "iss" => base_url(),
            "iat" => time(),
            "nbf" => time() - 5,
            "exp" => $expiration,
            "data" => $payload,
        );
        // encode the payload using our secret key and return the token
        return JWT::encode($payload, $key, 'HS256');
    }
}

/**
 * Decode the JWT token
 */
if (!function_exists('decodeJwtToken')) {
    function decodeJwtToken($payload): stdClass
    {
        $key = env('jwtKey');
        JWT::$leeway = 60; // $leeway in seconds
        return JWT::decode($payload, new Key($key, 'HS256'));
    }
}

if (!function_exists('getAuthorizationHeader')) {
    function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } else {
            $urlPath = apache_request_headers();
            $headers = array_key_exists('Authorization', $urlPath) ? $urlPath['Authorization'] : (array_key_exists('authorization', $urlPath) ? $urlPath['authorization'] : false);
        }
        return $headers;
    }
}


/**
 * @throws Exception
 */
if (!function_exists('getBearerToken')) {
    function getBearerToken(): string
    {
        $headers = getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        throw new \Exception('Access Token Not found');
    }
}

/**
 * Create the hashid object using config settings unless override values are passed thru.
 *
 * @access  public
 * @return  object
 */
if (!function_exists('hashids_createobject')) {
    /**
     * @throws Exception
     */
    function hashids_createobject($salt_ov = NULL, $min_hash_length_ov = NULL, $alphabet_ov = NULL)
    {

        $salt = (!$salt_ov) ? env('hashidsSalt') : $salt_ov;
        $min_hash_length = (!$min_hash_length_ov) ? env('hashidsMinHashLength') : $min_hash_length_ov;
        $alphabet = (!$alphabet_ov) ? env('hashidsAlphabet') : $alphabet_ov;

        $hashids = Factories::libraries('Hashids');
        return new $hashids($salt, $min_hash_length, $alphabet);
    }
}

/**
 * Encrypt an ID or array of ID's to a hashid.
 *
 * @access  public
 * @param interger or array input
 * @return  string  hashid
 */
if (!function_exists('hashids_encrypt')) {
    /**
     * @throws Exception
     */
    function hashids_encrypt($input, $salt = NULL, $min_hash_length = NULL, $alphabet = NULL)
    {
        if (!is_array($input)) {
            $input = array(intval($input));
        }

        $hashids = hashids_createobject($salt, $min_hash_length, $alphabet);
        return call_user_func_array(array($hashids, "encrypt"), $input);
    }
}

/**
 * Decrypt a hashid to an integer or array of integers.
 *
 * @access  public
 * @param string  hashid
 * @return  array or integer - array returned if more than one value exists, else integer - NULL if not decryptable.
 */
if (!function_exists('hashids_decrypt')) {
    /**
     * @throws Exception
     */
    function hashids_decrypt($hash, $salt = '', $min_hash_length = 0, $alphabet = '')
    {
        $hashids = hashids_createobject($salt, $min_hash_length, $alphabet);
        $output = $hashids->decrypt($hash);
        if (count($output) < 1) {
            return NULL;
        }

        return (count($output) == 1) ? reset($output) : $output;
    }
}

