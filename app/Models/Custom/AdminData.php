<?php
/**
 * This is the class that manages all information and data retrieval needed by the admin section of this application.
 */
namespace App\Models\Custom;

use App\Entities\Agent;
use App\Entities\Cashback;
use App\Entities\Cashback_log;
use App\Entities\Customer;
use App\Entities\Daily_winner;
use App\Entities\Disputes;
use App\Entities\Fastest_finger_game;
use App\Entities\Superagent;
use App\Entities\Transaction_history;
use App\Entities\User_kyc_details;
use App\Entities\Wallet;
use App\Entities\Wallet_payment_history;
use App\Entities\Withdrawal_request;
use App\Models\WebSessionManager;
use CodeIgniter\Model;

class AdminData extends Model {
	protected $db;
	private $webSessionManager;

	public function __construct() {
		$this->db = db_connect();
		$this->webSessionManager = new WebSessionManager;
	}
	/**
	 * @return array<string,mixed>
	 */
	public function loadDashboardData(): array {
		$result = [];
		$totalCustomer = Customer::totalCount();
		$totalVerifiedCustomer = User_kyc_details::init()->totalVerifiedUsers('customer');
		$totalUnverifiedCustomer = $totalCustomer - $totalVerifiedCustomer;

		$totalAgent = Agent::totalCount();
		$totalVerifiedAgent = User_kyc_details::init()->totalVerifiedUsers('agent');
		$totalUnverifiedAgent = $totalAgent - $totalVerifiedAgent;
		$withdrawal = loadClass('withdrawal_request');
		$withdrawalContent = $withdrawal->getPendingWithdrawal('pending', 20);

		$result['countData'] = [
			'customer' => $totalCustomer,
			'verifiedCustomer' => $totalVerifiedCustomer,
			'unverifiedCustomer' => $totalUnverifiedCustomer,
			'superagent' => Superagent::totalCount() ?? 0,
			'agent' => $totalAgent,
			'verifiedAgent' => $totalVerifiedAgent,
			'unverifiedAgent' => $totalUnverifiedAgent,
			'walletBalance' => Wallet::totalSum('amount') ?? 0,
			'customerWallet' => Wallet::init()->totalSumWallet('customer') ?? 0,
			'superagentWallet' => Wallet::init()->totalSumWallet('superagent') ?? 0,
			'agentWallet' => Wallet::init()->totalSumWallet('agent') ?? 0,
			'influencerWallet' => Wallet::init()->totalSumWallet('influencer') ?? 0,
			'promoterWallet' => Wallet::init()->totalSumWallet('promoter') ?? 0,
			'customerCashback' => Cashback::totalCount("where cashback_type = 'customer'") ?? 0,
			'agentCashback' => Cashback::totalCount("where cashback_type = 'agent'") ?? 0,
			'alert_winners' => Daily_winner::totalCount(" where match_sequence = 'three_unseq' ") ?? 0,
			'jackpot_winners' => Daily_winner::totalCount(" where match_sequence = 'four_consec' ") ?? 0,
			'stakecashback' => Cashback::totalSum('deducted_amount') ?? 0,
			'pendingPayout' => Withdrawal_request::totalCount("where request_status = 'pending' or request_status = 'processing'"),
			'pendingDisputes' => Disputes::totalCount("where dispute_status = 'pending'"),
			'salesCount' => Cashback::totalCount("where date(date_created) = curdate()") ?? 0,
			'salesAmount' => Cashback::totalSum('deducted_amount', "where date(date_created) = curdate()") ?? 0,
			'withdrawalAmount' => Withdrawal_request::totalSum('amount', "where request_status = 'approved'"),
			'monnifyAmount' => Wallet_payment_history::totalSum('amount', "where payment_status = 'success' and payment_channel='monnify' "),
			'paystackAmount' => Wallet_payment_history::totalSum('amount', "where payment_status = 'success' and payment_channel='paystack' "),
			'checkinAmountDaily' => Cashback_log::totalSum('checkin_amount', "where game_type = 'check_in' and date(date_created) = curdate()") ?? 0,
			'checkinAmount' => Cashback_log::totalSum('checkin_amount', "where game_type = 'check_in' ") ?? 0,
			'dailyCheckinCount' => Cashback_log::totalCount("where game_type = 'check_in' and date(date_created) = curdate() ") ?? 0,
			'fastestFingerAmountDaily' => Fastest_finger_game::totalSum('stake_amount', "where date(date_created) = curdate()") ?? 0,
			'fastestFingerAmount' => Fastest_finger_game::totalSum('stake_amount') ?? 0,
			'dailyfastestFingerCount' => Fastest_finger_game::totalCount("where date(date_created) = curdate() ") ?? 0,
		];
		$result['cashbackDistrix'] = Cashback::init()->getCashbackDistrixByDay();
		$result['fundDistrix'] = Transaction_history::init()->getFundDistrixByMonth('credit');
		$result['withdrawalDistrix'] = Transaction_history::init()->getFundDistrixByMonth('debit');
		$result['withdrawalContents'] = $withdrawalContent;

		// print_r($result);exit;
		return $result;
	}
	/**
	 * @return array
	 */
	public function loadGraphData(?string $whereClause): array {
		$result = [];

		$result['cashbackDistrix'] = Cashback::init()->getCashbackDistrixByDay($whereClause);
		$result['fundDistrix'] = Transaction_history::init()->getFundDistrixByMonth('credit', $whereClause);
		$result['withdrawalDistrix'] = Transaction_history::init()->getFundDistrixByMonth('debit', $whereClause);
		// print_r($result);exit;
		return $result;
	}
	/**
	 * @param mixed $combine
	 */
	public function getAdminSidebar($combine = false) {
		$role = loadClass('role');
		$role = new $role();
		// using $combine parameter to take into consideration path that're not captured in the admin sidebar
		$output = ($combine) ? array_merge($role->getModules(), $role->getExtraModules()) : $role->getModules();
		return $output;
	}
	/**
	 * @param mixed $merge
	 */
	public function getCanViewPages(object $role, $merge = false) {
		$result = array();
		$allPages = $this->getAdminSidebar($merge);
		$permissions = $role->getPermissionArray();

		foreach ($allPages as $module => $pages) {
			$has = $this->hasModule($permissions, $pages, $inter);
			$allowedModule = $this->getAllowedModules($inter, $pages['children']);
			$allPages[$module]['children'] = $allowedModule;
			$allPages[$module]['state'] = $has;
		}
		return $allPages;
	}
	/**
	 * @param mixed $includesPermission
	 * @param mixed $children
	 * @return array
	 */
	private function getAllowedModules($includesPermission, $children): array {
		$result = $children;
		$result = array();
		foreach ($children as $key => $child) {
			if (is_array($child)) {
				foreach ($child as $childKey => $childValue) {
					if (in_array($childValue, $includesPermission)) {
						$result[$key] = $child;
					}
				}
			} else {
				if (in_array($child, $includesPermission)) {
					$result[$key] = $child;
				}
			}

		}
		return $result;
	}
	/**
	 * @param mixed $permission
	 * @param mixed $module
	 * @param mixed $res
	 * @return int
	 */
	private function hasModule($permission, $module, &$res): int {
		if (is_array(array_values($module['children']))) {
			$res = array_intersect(array_keys($permission), array_values_recursive($module['children']));
		} else {
			$res = array_intersect(array_keys($permission), array_values($module['children']));
		}

		if (count($res) == count($module['children'])) {
			return 2;
		}
		if (count($res) == 0) {
			return 0;
		} else {
			return 1;
		}
	}

}

?>