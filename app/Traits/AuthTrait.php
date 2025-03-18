<?php

namespace App\Traits;

use App\Libraries\ApiResponse;
use App\Models\WebSessionManager;
use Config\Services;
use CodeIgniter\Database\BaseConnection;
use App\Enums\AuthEnum as AuthType;
use App\Libraries\RouteURI;

trait AuthTrait
{
    /**
     * Injects authentication parameters based on the request URI.
     *
     * @return string|null
     */
    public static function injectParamToAuth(): ?string
    {
        $request = Services::request();
        $uri = $request->getUri();

        if (!empty($uri->getPath())) {
            if ($uri->getSegment(1) === 'webtranx') {
                if (in_array($uri->getSegment(2), RouteURI::FINANCE_STATS)) {
                    return AuthType::FINANCE_OUTFLOW->value;
                }
            }
        }

        return null;
    }

    /**
     * Updates a user's password.
     *
     * @param string $entity The entity (e.g., 'users').
     * @param int|null $userID The user ID.
     * @param bool $ignorePasswordCheck Whether to ignore the current password check.
     */
    protected function updateUsersPassword(string $entity = 'users_new', ?int $userID = null, bool $ignorePasswordCheck = false)
    {
        $request = Services::request();
        $validation = Services::validation();
        $db = db_connect();

        $password = $request->getPost('password');
        $newPassword = $request->getPost('newPassword');

        if (!$ignorePasswordCheck) {
            $validation->setRule('password', 'password', 'required');
        }
        $validation->setRule('newPassword', 'new password', 'required');
        $validation->setRule('confirmPassword', 'confirm password', 'required|matches[newPassword]', [
            'matches' => 'The confirm password does not match with the new password',
        ]);

        if (!$validation->withRequest($request)->run()) {
            $errors = $validation->getErrors();
            return ApiResponse::error(reset($errors));
        }

        $currentUser = WebSessionManager::currentAPIUser();
        $id = $userID ?? $currentUser->id;

        $message = "You have successfully updated your password";
        if ($userID) {
            $message = "You have successfully updated the password for the user account";
        }

        // Load the user model or entity
        $userModel = loadClass($entity);; // Replace with your actual model
        $user = $userModel->getUserByID($id);
        if (!$user) {
            return ApiResponse::error('Invalid user account');
        }

        if (!$ignorePasswordCheck) {
            $check = decode_password(trim($password), $user['password']);
            if (!$check) {
                return ApiResponse::error('Please type-in your current password correctly');
            }
        }

        $newPasswordHash = encode_password($newPassword);
        $query = "UPDATE $entity SET password = ? WHERE id = ?";
        $db->query($query, [$newPasswordHash, $id]);

        if ($db->affectedRows() > 0) {
            return ApiResponse::success($message);
        } else {
            return ApiResponse::error('An error occurred while updating your password. Please try again later.');
        }
    }

}