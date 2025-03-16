<?php

/**
 * model for loading extra data needed by pages through ajax
 */
namespace App\Controllers;

use App\Models\WebSessionManager;
use CodeIgniter\Config\Factories;
use Exception;

class Ajaxdata extends BaseController {
	private $webSessionManager = null;
	public function __construct() {
		$this->webSessionManager = new WebSessionManager;
		$exclude = array('changePassword', 'savePermission', 'approvePayment');
		$page = $this->getMethod($segments);
		if ($this->webSessionManager->getCurrentUserProp('user_type') == 'admin' && in_array($page, $exclude)) {
			$role = loadClass('role');
			$role->checkWritePermission();
		}
	}
	/**
	 * @param mixed $allSegment
	 * @return string[]|bool
	 */
	private function getMethod(&$allSegment) {
		$path = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$base = base_url();
		$left = ltrim($path, $base);
		$result = explode('/', $left);
		$allSegment = $result;
		return $result[0];
	}
	/**
	 * @param mixed $query
	 * @param mixed $data
	 * @param mixed $valMessage
	 * @param mixed $errMessage
	 * @return string|bool
	 */
	private function returnJSONTransformArray($query, $data = array(), $valMessage = '', $errMessage = '') {
		$newResult = array();
		$result = $this->db->query($query, $data);
		if ($result->getNumRows() > 0) {
			$result = $result->getResultArray();
			if ($valMessage != '') {
				$result[0]['value'] = $valMessage;
			}
			return json_encode($result[0]);
		} else {
			if ($errMessage != '') {
				$dataParam = array('value' => $errMessage);
				return json_encode($dataParam);
			}
			return json_encode(array());
		}
	}
	/**
	 * @param mixed $array
	 * @return string|bool
	 */
	private function returnJSONFromNonAssocArray($array) {
		//process if into id and value then
		$result = array();
		for ($i = 0; $i < count($array); $i++) {
			$current = $array[$i];
			$result[] = array('id' => $current, 'value' => $current);
		}
		return json_encode($result);
	}
	/**
	 * @param mixed $query
	 * @param mixed $data
	 * @param mixed $valMessage
	 * @param mixed $errMessage
	 * @return string|bool
	 */
	protected function returnJsonFromQueryResult($query, $data = array(), $valMessage = '', $errMessage = '') {
		$result = $this->db->query($query, $data);
		if ($result->getNumRows() > 0) {
			$result = $result->getResultArray();
			if ($valMessage != '') {
				$result[0]['value'] = $valMessage;
			}
			// print_r($result);exit;
			return json_encode($result);
		} else {
			if ($errMessage != '') {
				$dataParam = array('value' => $errMessage);
				return json_encode($dataParam);
			}
			return "";
		}
	}
	/**
	 * @return void
	 */
	public function savePermission() {
		if (isset($_POST['role'])) {
			$role = $_POST['role'];
			if (!$role) {
				echo createJsonMessage('status', false, 'message', 'Error occured while saving permission', 'flagAction', false);
			}
			$newRole = loadClass('role');
			try {
				$removeList = json_decode($_POST['remove'], true);
				$updateList = json_decode($_POST['update'], true);
				$newRole->ID = $role;
				$result = $newRole->processPermission($updateList, $removeList);
				echo createJsonMessage('status', $result, 'message', 'Permission updated successfully', 'flagAction', true);
			} catch (Exception $e) {
				echo createJsonMessage('status', false, 'message', 'Error occured while saving permission', 'flagAction', false);
			}

		}
	}
	/**
	 * @return void
	 */
	public function approvePayment() {
		if (isset($_POST['update'])) {
			$update = $_POST['update'];
			if (!$update) {
				echo createJsonMessage('status', false, 'message', 'Error occured while approving payment', 'flagAction', false);
			}
			$wallet_payment_history = loadClass('wallet_payment_history');
			try {
				$updateList = json_decode($_POST['update'], true);
				$result = $wallet_payment_history->processVerification($updateList);
				$message = $result ? "Payment successfully approved" : "Error occured";
				echo createJsonMessage('status', $result, 'message', $message, 'flagAction', $result);
			} catch (Exception $e) {
				echo createJsonMessage('status', false, 'message', 'Error occured while approving payment', 'flagAction', false);
			}

		}
	}
	/**
	 * @param mixed $user_id
	 * @param mixed $amount
	 * @param mixed $type
	 * @param mixed $channel
	 * @param mixed $status
	 * @param mixed $tranx
	 * @param mixed $desc
	 * @return bool
	 */
	private function createFundTransaction($user_id, $amount, $type, $channel, $status = 1, $tranx = 'fund', $desc = null) {
		$builder = $this->db->table('transaction_history');
		$param = [
			'user_id' => $user_id,
			'amount' => $amount,
			'tranx_name' => $tranx,
			'tranx_type' => $type,
			'channel' => $channel,
			'status' => $status,
			'description' => $desc,
		];
		$this->db->transBegin();
		$builder->set($param);
		if (!$builder->insert()) {
			$this->db->transRollback();
			return false;
		}
		$this->db->transCommit();
		return true;
	}
	/**
	 * @param mixed $user_id
	 * @param mixed $amount
	 * @param mixed $message
	 * @param mixed $ref
	 * @return bool
	 */
	private function createWalletHistory($user_id, $amount, $message, $ref = null) {
		$wallet_payment = loadClass('wallet_payment_history');
		$this->db->transBegin();
		$wallet_pay = $wallet_payment->getWhere(['reference_number' => $ref], $count, 0, 1, false);
		if ($wallet_pay) {
			$wallet_pay = $wallet_pay[0];
			if ($wallet_pay == 'success') {
				// already approved
				return false;
			}
			$wallet_pay->payment_status = 'success';
			$wallet_pay->transaction_message = 'Approved by admin';
			if (!$wallet_pay->update()) {
				$this->db->transRollback();
				return false;
			}
			$this->db->transCommit();
			return true;
		} else {
			$payRef = generateHashRef('reference');
			$insert = array(
				'transaction_number' => $payRef,
				'reference_number' => $payRef,
				'reference_hash' => generateNumericRef($this->db, 'wallet_payment_history', 'reference_hash', 'WAF'),
				'user_id' => $user_id,
				'payment_status' => 'success',
				'date_created' => formatToUTC(),
				'payment_date' => formatToUTC(),
				'payment_channel' => 'manual',
				'amount' => $amount,
				'gateway_reference' => 'admin',
				'payment_method' => 'manual',
				'transaction_message' => $message,
			);
			$item = new $wallet_payment($insert);
			if (!$item->insert()) {
				$this->db->transRollback();
				return false;
			}
			$this->db->transCommit();
			return true;
		}
	}
	/**
	 * @param mixed $ref
	 * @param mixed $fullname
	 * @return bool
	 */
	private function updateWithdrawStatus($ref, $fullname) {
		$withdrawal_request = loadClass('withdrawal_request');
		$this->db->transBegin();

		$withdrawal = $withdrawal_request->getWhere(['reference' => $ref], $count, 0, 1, false);
		if ($withdrawal) {
			$withdrawal = $withdrawal[0];
			$withdrawal->request_status = 'approved';
			$withdrawal->message = "Approved by admin {$fullname}";
			if (!$withdrawal->update()) {
				$this->db->transRollback();
				return false;
			}
			$this->db->transCommit();
			return true;
		}
	}

	/**
	 * @return bool
	 */
	public function updateAccountWallet(string $type) {
		$amount = trim($this->request->getPost('amount'));
		$userID = $this->request->getPost('user_id');
		$pageType = $this->request->getPost('pageType');
		$pageVal = $this->request->getPost('pageVal');

		if (!$amount) {
			echo createJsonMessage('status', false, 'message', 'Please supply wallet amount', 'flagAction', false);
			return;
		}
		$amount = str_replace(',', '', $amount);
		$wallet = loadClass('wallet');
		if ($type === 'deduct') {
			$fullname = $this->webSessionManager->getCurrentUserProp('firstname') . ' ' . $this->webSessionManager->getCurrentUserProp('lastname');

			$currentWalletBalance = $wallet->getWalletBalance($userID);
			if ($currentWalletBalance <= 0 or $amount > $currentWalletBalance) {
				$message = "Oops, there's no enough amount in the wallet";
				echo createJsonMessage('status', false, 'message', $message, 'flagAction', false);
				return false;
			}

			if ($pageType == 'withdrawal') {
				// no need to deduct since it's removed at inception
				if ($this->updateWithdrawStatus($pageVal, $fullname)) {
					$this->createFundTransaction($userID, $amount, 'debit', 'manual', 1, 'withdrawal', 'Admin payment withdrawal');
				}
			} else {
				$this->createWalletHistory($userID, $amount, "Deduct from wallet by {$fullname}", $pageVal);
				// $this->createFundTransaction($userID,$amount,'debit','manual',1);

				if (!$wallet->deductWallet($userID, $amount, 'admin_withdrawal', 'withdrawal')) {
					echo createJsonMessage('status', false, 'message', 'Something went wrong', 'flagAction', false);
					return;
				}
			}
			echo createJsonMessage('status', true, 'message', 'Wallet successfully deducted', 'flagAction', true);
			return;
		} else if ($type === 'fund') {
			$fullname = $this->webSessionManager->getCurrentUserProp('firstname') . ' ' . $this->webSessionManager->getCurrentUserProp('lastname');
			$history = $this->createWalletHistory($userID, $amount, "Funded wallet by {$fullname}", $pageVal);

			if ($history) {
				if (!$wallet->updateWallet($userID, $amount, 'admin_fund', 'manual')) {
					echo createJsonMessage('status', false, 'message', 'Something went wrong', 'flagAction', false);
					return;
				}
				// $this->createFundTransaction($userID,$amount,'credit','manual',1);
			}
			echo createJsonMessage('status', true, 'message', 'Wallet successfully funded', 'flagAction', true);
			return;
		}
	}

	public function updateAccountRolloverWallet(string $type) {
		$amount = trim($this->request->getPost('amount'));
		$userID = $this->request->getPost('user_id');
		$pageType = $this->request->getPost('pageType');
		$pageVal = $this->request->getPost('pageVal');

		if (!$amount) {
			echo createJsonMessage('status', false, 'message', 'Please supply wallet amount', 'flagAction', false);
			return;
		}
		$amount = str_replace(',', '', $amount);
		$wallet = loadClass('wallet');
		if ($type === 'rollover-fund') {
			if($wallet->fundCustomerFirstWallet($userID, $amount)){
				echo createJsonMessage('status', true, 'message', 'First wallet funding successful', 'flagAction', true);return;
			}else{
				echo createJsonMessage('status', false, 'message', 'Funding can only be processed when all criteria are met.', 'flagAction', false);return;
			}
		}
	}

	/**
	 * @param mixed $userID
	 * @return bool|array
	 */
	public function validateUserAccount($userID) {
		$user = loadClass('user');
		$user->ID = $userID;
		if (!$user->load()) {
			return false;
		}
		$userType = $user->user_type;
		if (!$userType = $user->$userType) {
			return false;
		}
		return [$user, $userType];
	}
	/**
	 * @return void
	 */
	public function withdrawalRequest() {
		$validation = $this->validate([
			'amount' => 'required|is_natural_no_zero',
			'bank_name' => 'required',
			'account_number' => 'required|numeric',
			'userID' => 'required|numeric',
		]);
		if (!$validation) {
			$errors = $this->validator->getErrors();
			foreach ($errors as $error) {
				displayJson(false, $error);
				return;
			}
		}

		if (get_setting('withdrawal_status') == 0) {
			displayJson(false, "Withdrawal service is not available at the moment, please try again later.");return;
		}

		$validation = $this->validator->getValidated();
		$wallet = loadClass('wallet');
		$withdrawRequest = loadClass('withdrawal_request');
		$monnify = Factories::libraries('Monnify');

		$accountNumber = $validation['account_number'];
		$bankCode = $validation['bank_name'];
		$userID = $validation['userID'];
		$amount = $validation['amount'];
		$accountName = null;

		$userInfo = null;
		if (!$userInfo = $this->validateUserAccount($userID)) {
			echo createJsonMessage('status', false, 'message', "It seems your account is no longer valid");
			return;
		}

		// validate the amount in the user wallet
		$currentWalletBalance = $wallet->getWalletBalance($userID);
		$serviceCharge = get_setting('withdrawal_charge') ?: 0;
		$deductedAmount = $amount + $serviceCharge;

		if ($currentWalletBalance <= 0 || $deductedAmount > $currentWalletBalance) {
			$message = "Oops, you don't have enough amount in your wallet";
			echo createJsonMessage('status', false, 'message', $message);
			return;
		}

		$withdrawLimit = get_setting('withdrawal_limit');
		$withdrawLimitApproval = get_setting('withdrawal_limit_approval');
		if ($amount > $withdrawLimit) {
			$message = "Sorry, you can't withdrawal above the transaction limit[" . number_format($withdrawLimit, 2) . "]";
			displayJson(false, $message);return;
		}

		$reference = generateNumericRef($this->db, 'withdrawal_request', 'reference', 'WAD');
		// validating account number
		if (!$accountName = $monnify->getAccountName($bankCode, $accountNumber, $userID)) {
			$message = "The account number can't be validated, please verify the account number";
			echo createJsonMessage('status', false, 'message', $message);
			return;
		}
		$accountName = $accountName->accountName;
		$param = [
			'user_id' => $userID,
			'reference' => $reference,
			'total_amount' => $deductedAmount,
			'amount' => $amount,
			'account_number' => $accountNumber,
			'account_name' => $accountName,
			'bank_code' => $bankCode,
			'request_status' => 'pending',
			'service_charge' => $serviceCharge,
		];

		$withdrawalParam = [
			'withdrawal_param' => $param,
			'user_detail' => [
				'user_id' => $userID,
				'user_type' => $userInfo[0]->user_type,
			],
		];
		$directProcess = $amount <= $withdrawLimitApproval ? true : false;
		$message = ($directProcess) ? "Your withdrawal request is being processed right away." : "Your withdrawal is processing and your account will be credited shortly";

		if (!$withdrawRequest->processWithdrawal($withdrawalParam, $error)) {
			echo createJsonMessage('status', false, 'message', $error);return;
		}

		echo createJsonMessage('status', true, 'message', $message);
		return;
	}
	/**
	 * @return void
	 */
	public function appSettings() {
		$validation = \Config\Services::validation();
		$settings = loadClass('settings');

		if (!$this->validate([
			'withdrawal_limit' => [
				'label' => 'withdrawal limit',
				'rules' => 'required|numeric|is_natural',
			],
			'withdrawal_status' => [
				'label' => 'withdrawal status',
				'rules' => 'permit_empty',
			],
			'withdrawal_charge' => [
				'label' => 'withdrawal charge',
				'rules' => 'required|is_natural',
			],
			'withdrawal_limit_approval' => [
				'label' => 'withdrawal limit approval',
				'rules' => 'required|is_natural',
			],
			'disable_login' => [
				'label' => 'disable login',
				'rules' => 'required',
			],
			'disable_register' => [
				'label' => 'disable register',
				'rules' => 'required',
			],
		])) {
			$errors = $this->validator->getErrors();
			foreach ($errors as $error) {
				echo createJsonMessage('status', false, 'message', $error);return;
			}
		}
		$validData = $this->validator->getValidated();
		$settings_data = array(
			'withdrawal_limit' => $validData['withdrawal_limit'],
			'withdrawal_status' => @$validData['withdrawal_status'] ? 1 : 0,
			'withdrawal_charge' => $validData['withdrawal_charge'],
			'withdrawal_limit_approval' => $validData['withdrawal_limit_approval'],
			'disable_login' => $validData['disable_login'],
			'disable_register' => $validData['disable_register'],
		);

		// check if create method was successful
		$settings->registerSettings($settings_data);
		echo createJsonMessage('status', true, 'message', 'Settings saved successfully');return;
	}

}
