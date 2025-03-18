<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Libraries\ApiResponse;
use App\Models\WebSessionManager;

if(!function_exists('permissionAccess')){
    function permissionAccess(string $permission, ?string $message = null)
    {
        $currentUser = WebSessionManager::currentAPIUser() ?? 0;
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

if(!function_exists('checkPermission')){
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

if(!function_exists('isTimeExpired')){
    function isTimeExpired($expirationTime, $leeWay = 60): bool{
        $currentTime =  $leeWay ? time() - $leeWay : time();
        return $currentTime > $expirationTime;
    }
}

/**
 * Encode the token to JWT
 */
if(!function_exists('generateJwtToken')){
    function generateJwtToken($payload): string
    {
        $key = getenv('jwtKey');
        $expiration = time() + (60 * getenv('tokenExpiration'));
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
if(!function_exists('decodeJwtToken')){
    function decodeJwtToken($payload): stdClass
    {
        $key = getenv('jwtKey');
        JWT::$leeway = 60; // $leeway in seconds
        return JWT::decode($payload, new Key($key, 'HS256'));
    }
}

function getAuthorizationHeader() {
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

/**
 * @throws Exception
 */
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
