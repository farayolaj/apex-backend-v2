<?php

namespace App\Traits;

use App\Libraries\ApiResponse;
use App\Models\Remita;
use App\Models\WebSessionManager;
use App\Enums\AuthEnum as AuthType;
use Config\Services;

trait AccountTrait
{
    /**
     * Fetches the profile of the current user.
     */
    public function accountProfile()
    {
        $request = Services::request();

        if ($request->getMethod() === 'GET') {
            $currentUser = WebSessionManager::currentAPIUser();
            $userBankModel = loadClass('user_banks');
            $staffModel = loadClass('staffs');
            $rolesPermissionModel = loadClass('roles_permission');
            $loginType = 'admin';
            $payload = $currentUser->toArray();

            $banks = $userBankModel->getWhereNonObject(['users_id' => $currentUser->id], $c, 0, null, false);
            $permissions = $rolesPermissionModel->permissionQuery($currentUser->id);

            if (!empty($payload['user_department'])) {
                $loginType = 'department';
            }

            if (!empty($payload['units_id'])) {
                $payload['unit'] = $staffModel->getStaffDepartment($payload['units_id'])->name;
            }

            if (!empty($payload['avatar'])) {
                $payload['avatar'] = userImagePath($payload['avatar']);
            }

            $banks = $banks ?: [];
            $payload['bank_detail'] = $banks;

            if ($payload['type'] === AuthType::ADMIN->value || $payload['type'] === AuthType::FINANCE_OUTFLOW->value) {
                $payload['role_permission'] = $permissions;
                $payload['current_session'] = get_setting('active_session_student_portal');
                $payload['current_admission_session'] = get_setting('admission_session_update');
            }

            $payload['login_type'] = $loginType;
            return ApiResponse::success("You've successfully fetched profile", $payload);
        }
    }

    /**
     * Fetches the department details for a user.
     *
     * @param $userDepartment
     * @return object|null
     */
    private function getUserDepartment($userDepartment): ?object
    {
        return db_connect()->table('department')
            ->where(['id' => $userDepartment, 'type' => 'academic'])
            ->get()
            ->getRow();
    }

    /**
     * Fetches the details of the user based on their type.
     *
     * @param object $user The user object.
     */
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
            $entityModel = loadClass($entity);
            $entity = $entityModel->getWhere(['id' => $user->user_table_id], $c, 0, null, false);
            if ($entity) {
                $entity = $entity[0];
            }
        }
        return $entity;
    }

    /**
     * Fetches the activity logs for the current user.
     *
     */
    public function accountActivityLogs()
    {
        $usersNewModel = loadClass('users_new');
        $currentUser = WebSessionManager::currentAPIUser();
        $logs = $usersNewModel->getUserLog($currentUser->user_login, true);
        return ApiResponse::success('Success', $logs);
    }

    /**
     * Fetches the authentication login logs for the current user.
     *
     */
    public function accountAuthLoginLogs()
    {
        $usersNewModel = loadClass('users_new');
        $currentUser = WebSessionManager::currentAPIUser();
        $logs = $usersNewModel->performed_action($currentUser->user_login, 'auth_user_login');
        return ApiResponse::success('Success', $logs);
    }

    /**
     * Fetches the bank details for the current user.
     *
     */
    public function getBankDetails()
    {
        $userBankModel = loadClass('user_banks');
        $currentUser = WebSessionManager::currentAPIUser();
        $banks = $userBankModel->getUserBankDetails($currentUser->id);
        return ApiResponse::success("You've successfully fetched user bank details", $banks ?: []);
    }

    /**
     * Sets a bank as primary for the current user.
     *
     */
    public function bankAsPrimary()
    {
        $request = Services::request();
        $validation = Services::validation();

        $validation->setRules([
            'user_bank_id' => [
                'label' => 'bank',
                'rules' => 'required',
            ],
        ]);

        if (!$validation->withRequest($request)->run()) {
            $errors = $validation->getErrors();
            return ApiResponse::error(reset($errors));
        }

        $currentUser = WebSessionManager::currentAPIUser();
        $bankID = $request->getPost('user_bank_id');
        $userBankModel = loadClass('user_banks');

        $banks = $userBankModel->getWhere(['id' => $bankID, 'users_id' => $currentUser->id], $c, 0, null, false);
        if (!$banks) {
            return ApiResponse::error('User bank details not found');
        }

        $userBankModel->reverseBankPrimary($currentUser->id);
        $bank = $banks[0];
        $bank->is_primary = 1;

        if (!$userBankModel->update()) {
            return ApiResponse::error('Unable to update bank as primary, please try again later');
        }

        return ApiResponse::success('Bank has been set to primary');
    }

    /**
     * Performs an account name enquiry.
     *
     */
    public function accountNameEnquiry()
    {
        $request = Services::request();
        $validation = Services::validation();

        $accountNumber = $request->getGet('beneficiary_account');
        $bankCode = $request->getGet('bank_code');

        $validation->setRules([
            'beneficiary_account' => [
                'label' => 'account number',
                'rules' => 'required|numeric',
            ],
            'bank_code' => [
                'label' => 'bank code',
                'rules' => 'required|numeric',
            ],
        ]);

        if (!$validation->withRequest($request)->run()) {
            $errors = $validation->getErrors();
            return ApiResponse::error(reset($errors));
        }

        $remitaModel = new Remita;
        $accounts = $remitaModel->getAccountNameEnquiry($accountNumber, $bankCode);

        if (isset($accounts['status']) && !$accounts['status']) {
            return ApiResponse::error($accounts['message']);
        }

        $payload = [
            'account_name' => $accounts['sourceAccountName'],
            'account_number' => $accounts['sourceAccount'],
            'bank_code' => $accounts['sourceBankCode'],
        ];

        return ApiResponse::success("You've successfully fetched account name", $payload);
    }

    /**
     * Fetches the bank code for a given bank name.
     *
     */
    public function accountBankCode()
    {
        $request = Services::request();
        $validation = Services::validation();

        $bankName = $request->getGet('name');

        $validation->setRules([
            'name' => [
                'label' => 'bank name',
                'rules' => 'required',
            ],
        ]);

        if (!$validation->withRequest($request)->run()) {
            $errors = $validation->getErrors();
            return ApiResponse::error(reset($errors));
        }

        $bankListModel = loadClass('bank_lists');
        $names = $bankListModel->inferBankName($bankName);
        $names = $names ?: [];

        return ApiResponse::success("You've successfully fetched bank code", $names);
    }
}