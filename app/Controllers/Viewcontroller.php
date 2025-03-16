<?php
namespace App\Controllers;

use App\Entities\Agent;
use App\Entities\Cashback;
use App\Entities\Cashback_log;
use App\Entities\Customer;
use App\Entities\Superagent;
use App\Models\Custom\AdminData;
use App\Models\Custom\InfluencerData;
use App\Models\Custom\NlrcData;
use App\Models\Custom\PromoterData;
use App\Models\Custom\SuperagentData;
use App\Models\FormConfig;
use App\Models\ModelFormBuilder;
use App\Models\QueryHtmlTableObjModel;
use App\Models\TableWithHeaderModel;
use App\Models\WebSessionManager;

class Viewcontroller extends BaseController {

	private $errorMessage; // the error message currently produced from this cal if it is set, it can be used to produce relevant error to the user.
	private $access = array();
	private $appData;
	private $webSessionManager;
	private $modelFormBuilder;
	private $tableWithHeaderModel;
	private $queryHtmlTableObjModel;
	private $adminData;
	private $superagentData;
	private $nlrcData;
	private $influencerData;
	private $promoterData;
	private $crudNameSpace = 'App\Models\Crud';

	public function __construct() {
		$this->webSessionManager = new WebSessionManager;
		$this->modelFormBuilder = new ModelFormBuilder;
		$this->tableWithHeaderModel = new TableWithHeaderModel;
		$this->queryHtmlTableObjModel = new QueryHtmlTableObjModel;
		$this->adminData = new AdminData;

		if (!$this->webSessionManager->isSessionActive()) {
			header("Location:" . base_url());exit;
		}
	}

// bootstrapping functions
	public function view($model, $page = 'index', $third = '', $fourth = '') {
		if (!(file_exists(APPPATH . "Views/$model/") && file_exists(APPPATH . "Views/$model/$page" . '.php'))) {
			throw new \CodeIgniter\Exceptions\PageNotFoundException($page);
		}
		// this number is the default arg that ID is the last arg i.e 3 = id

		$defaultArgNum = 4;
		if ($defaultArgNum < func_num_args()) {
			$data['extra'] = func_get_args();
			$data['entityName'] = ($fourth != '') ? $third : "";
		} else {
			$modelID = ($fourth == '') ? $third : $fourth;
			$data['id'] = urldecode($modelID);
			$data['entityName'] = ($fourth != '') ? $third : "";
		}
		$tempTitle = removeUnderscore($model);
		$title = $page == 'index' ? $tempTitle : ucfirst($page) . " $tempTitle";
		$exceptions = array(); // pages that does not need active session
		$data['model'] = $page;

		if (!in_array($page, $exceptions)) {
			if (!$this->webSessionManager->isSessionActive()) {
				redirect(base_url());exit;
			}
		}

		if (method_exists($this, $model)) {
			$this->$model($page, $data);
		}
		$methodName = $model . ucfirst($page);

		if (method_exists($this, $model . ucfirst($page))) {
			$this->$methodName($data);
		}

		$data['message'] = $this->webSessionManager->getFlashMessage('message');
		$data['webSessionManager'] = $this->webSessionManager;
		// sendPageCookie($model,$page);

		echo view("$model/$page", $data);
	}

	private function admin($page, &$data) {
		$role_id = $this->webSessionManager->getCurrentUserProp('role_id');
		if (!$role_id) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}

		$role = false;
		if ($this->webSessionManager->getCurrentUserProp('user_type') == 'admin') {
			$admin = loadClass('admin');
			$admin->ID = $this->webSessionManager->getCurrentUserProp('user_table_id');
			$admin->load();
			$data['admin'] = $admin;
			$role = $admin->role;
			if (!$role) {
				exit("Kindly ensure a role is assigned to this admin user");
			}
		}
		$data['currentRole'] = $role;
		if (!$role) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}
		$path = 'vc/admin/' . $page;
		$extraPage = ($data['entityName']) ? $data['entityName'] : $data['id'];
		if ($extraPage != '') {
			$path .= "/" . $extraPage;
		}

		if (isset($data['extra'][3])) {
			$path .= "/" . $data['extra'][3];
		}
		if ($this->request->getGet('type')) {
			$path .= "?type=" . $this->request->getGet('type');
		}

		// echo $path;exit;

		// this approach is use so as to allow this page pass through using a path
		// that is already permitted
		if ($page == 'permission') {
			$path = 'vc/create/role';
		}

		if (!$role->canView($path)) {
			echo show_access_denied();exit;
		}
		// caching this role pages
		$userCanview = $this->webSessionManager->getCurrentUserProp('user_table_id') . "_canView";
		// caching this role pages
		if (!$canView = cache($userCanview)) {
			$canView = $this->adminData->getCanViewPages($role);
			cache()->save($userCanview, $canView, 900); // cache for 15mins
		}

		$data['canView'] = $canView;
	}

	private function adminDashboard(&$data) {
		$data = array_merge($data, $this->adminData->loadDashboardData());
	}

	private function adminGraph(&$data) {
		$whereClause = null;
		$startDate = $this->request->getGet('startDate');
		$endDate = $this->request->getGet('endDate');
		$paymentStatus = $this->request->getGet('paymentStatus');

		if ($startDate && $endDate) {
			$whereClause = " where cast(date_created as date) >= '{$startDate}' and cast(date_created as date) <= '{$endDate}' ";
		}
		$data = array_merge($data, $this->adminData->loadGraphData($whereClause));
	}

	private function adminPermission(&$data) {
		$data['id'] = urldecode($data['id']);
		if (!isset($data['id']) || !$data['id'] || $data['id'] == 1) {
			$this->show_404();exit;
		}
		$role = loadClass('role');
		$newRole = new $role(array('ID' => $data['id']));
		$newRole->load();
		$data['role'] = $newRole;
		$data['allPages'] = $this->adminData->getAdminSidebar(true);
		$sidebarContent = $this->adminData->getCanViewPages($data['role'], true);
		// print_r($sidebarContent);exit;
		$data['permitPages'] = $sidebarContent;
		$data['allStates'] = $data['role']->getPermissionArray();
		$data['pageTitle'] = 'permission';
		$data['modelName'] = 'Permission';
	}

	private function adminProfile(&$data) {
		$admin = loadClass('admin');
		$admin = new $admin();
		$admin->ID = $this->webSessionManager->getCurrentUserProp('user_table_id');
		$admin->load();
		$data['admin'] = $admin;
	}

/**
 * This would ensure that custom query on table model is allowed based
 * on different type of the entity
 * @param  [type] &$data [description]
 * @return [type]        [description]
 */
	private function adminView_model(&$data) {
		$id = (is_numeric($data['id'])) ? $data['id'] : null;
		$model = ($data['entityName']) ? $data['entityName'] : $data['id'];
		$modelType = '';
		if ($model == 'payment_transaction') {
			$model = 'wallet_payment_history';
		}

		$newModel = loadClass($model);
		$modelType = $_GET['type'] ?? "";
		if ($this->request->getGet('super_code')) {
			$modelType = 'all_agent';
		}

		$data['customerCount'] = 0;
		$data['modelStatToolTip'] = null;
		$data['modelStatTitle'] = null;
		$data['showViewCount'] = false;
		$pageTitle = $this->getTitlePage($model) ?? ucfirst($modelType) . ' ' . removeUnderscore($model);
		if ($model == 'customer' && $modelType == '') {
			$data['modelStatTitle'] = "Total User Count";
			$data['modelStatToolTip'] = "Total User";
			$data['customerCount'] = Customer::totalCount();
			$data['showViewCount'] = true;
			$pageTitle = "All Registered Users";
		}

		if ($model == 'customer' && $modelType == 'verified') {
			$data['showViewCount'] = true;
			$data['modelStatTitle'] = "Total User Count";
			$data['modelStatToolTip'] = "Total Verified User";
			$data['customerCount'] = Customer::totalCount(" join user on user.user_table_id = customer.id left join user_kyc_details on user_kyc_details.user_id = user.id where user.user_type = 'customer' and user_kyc_details.bvn_status = '1' ");
			$pageTitle = "All Verified Users";
		}

		if ($model == 'customer' && $modelType == 'unverified') {
			$data['showViewCount'] = true;
			$data['modelStatTitle'] = "Total User Count";
			$data['modelStatToolTip'] = "Total Unverified User";
			$data['customerCount'] = Customer::totalCount(" join user on user.user_table_id = customer.id left join user_kyc_details on user_kyc_details.user_id = user.id where user.user_type = 'customer' and user_kyc_details.bvn_status is null ");
			$pageTitle = "All Unverified Users";
		}

		if ($model == 'cashback') {
			$pageTitle = "All Gameplay Page";
		}

		if ($model == 'cashback' && $modelType == 'customer') {
			$data['showViewCount'] = true;
			$data['modelStatTitle'] = "Total Users Gameplay Count";
			$data['modelStatToolTip'] = "Users Gameplay";
			$data['customerCount'] = Cashback::totalCount("where cashback_type = 'customer'") ?? 0;
			$pageTitle = "Users Gameplay Page";
		}

		if ($model == 'cashback' && $modelType == 'agent') {
			$data['showViewCount'] = true;
			$data['modelStatTitle'] = "Total Agents Gameplay Count";
			$data['modelStatToolTip'] = "Agents Gameplay";
			$data['customerCount'] = Cashback::totalCount("where cashback_type = 'agent'") ?? 0;
			$pageTitle = "Agents Gameplay Page";
		}

		if ($model == 'cashback' && $modelType == 'checkin') {
			$data['showViewCount'] = true;
			$data['modelStatTitle'] = "Total Check-In Gameplay";
			$data['modelStatToolTip'] = "All Gameplay";
			$data['customerCount'] = Cashback_log::totalCount("where game_type = 'check_in' ") ?? 0;
			$pageTitle = "Check-in Gameplay Page";
		}

		$result = $newModel->viewList($id, $modelType, 12000, false, $this->request);
		$data['modelName'] = $model;
		$data['pageTitle'] = $pageTitle;
		$data['queryString'] = $result;
		$data['dataParam'] = $id;
		$data['queryHtmlTableObjModel'] = $this->queryHtmlTableObjModel;
		$data['modelFormBuilder'] = $this->modelFormBuilder;
		$formConfig = new FormConfig(true);
		$data['configData'] = $formConfig->getInsertConfig($model);
		$data['model'] = $model;
		$data['modelObj'] = $newModel;
		$data['db'] = $this->db;
	}

	private function adminView_more(&$data) {
		if ($this->webSessionManager->getCurrentUserProp('user_type') == 'superagent' && isset($data['superagentID'])) {
			$modelName = 'superagent';
			$id = $data['superagentID'];
			$modelType = 'profile';
		} else {
			$id = $data['extra'][4];
			$modelType = $data['extra'][3]; // this is the index of the type
			$modelName = $data['entityName'];
			if ($modelName == 'profile') {
				$modelName = $this->request->getGet('made_by') ?? 'customer';
			}
		}

		$newModel = loadClass($modelName);

		$result = $newModel->viewList($id, $modelType, 1, true);
		if ($modelName == 'customer') {
			$customer = new Customer($result);
			$wallet = loadClass('wallet');
			$bonusWallet = loadClass('bonus_wallet');
			$userID = $customer->user->ID;

			$data['user_id'] = $userID;
			$data['user_kyc'] = $customer->user->user_kyc_details;
			$data['walletBalance'] = $wallet->getWalletBalance($userID);
			$data['bonusWalletBalance'] = $bonusWallet->getWalletBalance($userID);
		} else if ($modelName == 'agent') {
			$agent = new Agent($result);
			$wallet = loadClass('wallet');
			$bonusWallet = loadClass('bonus_wallet');
			$userID = $agent->user->ID;

			$data['user_id'] = $userID;
			$data['user_kyc'] = $agent->user->user_kyc_details;
			$data['walletBalance'] = $wallet->getWalletBalance($userID);
			$data['bonusWalletBalance'] = $bonusWallet->getWalletBalance($userID);
		} else if ($modelName == 'superagent') {
			$superagent = new Superagent($result);
			$wallet = loadClass('wallet');
			$bonusWallet = loadClass('bonus_wallet');
			$userID = $superagent->user->ID;

			$data['user_id'] = $userID;
			$data['modelEntityID'] = $result['ID'];
			$data['user_kyc'] = $superagent->user->user_kyc_details;
			$data['walletBalance'] = $wallet->getWalletBalance($userID);
			$data['bonusWalletBalance'] = $bonusWallet->getWalletBalance($userID);
		}

		$data['modelStatus'] = (!$result) ? false : true;
		unset($result['ID']);
		$data['modelPayload'] = $result;
		$data['modelName'] = $modelName;
		$data['pageTitle'] = $this->getTitlePage($modelName) ?? $modelType . ' ' . removeUnderscore($modelName);
	}

	private function getTitlePage(string $modelName) {
		$result = [
			'cashback' => 'cashback',
		];
		return array_key_exists($modelName, $result) ? $result[$modelName] : null;
	}

	private function adminVerify_payment(&$data) {
		$wallet_history = loadClass('wallet_payment_history');
		$user = loadClass('user');

		$whereClause = "where (payment_status = 'pending' or payment_status = 'success') and payment_channel = 'interswitch'";
		$startDate = $this->request->getGet('startDate');
		$endDate = $this->request->getGet('endDate');
		$paymentStatus = $this->request->getGet('paymentStatus');
		if ($paymentStatus) {
			$whereClause = "where payment_status = '{$paymentStatus}' and payment_channel = 'interswitch'";
		}

		if ($startDate && $endDate) {
			$whereClause .= " and cast(date_created as date) >= '{$startDate}' and cast(date_created as date) <= '{$endDate}' ";
		}

		$result = $wallet_history->allNonObject($count, false, 0, null, 'order by date_created desc,payment_status asc', $whereClause);
		$data['modelStatus'] = empty($result) ? false : true;
		$data['modelPayload'] = $result;
		$data['userObject'] = $user;
	}

	private function adminPayment_transaction(&$data) {
		$wallet_history = loadClass('wallet_payment_history');
		$modelType = request()->getGet('type');
		$requery = request()->getGet('requery');
		$result = $wallet_history->viewList(null, $modelType, 12000, false, $this->request);
		$data['pageTitle'] = ucwords($modelType . ' Wallet Payment History');

		if ($requery === 'yes' && $result) {
			$data['reportMessage'] = $wallet_history->reportMessage ?? [];
			$data['contents'] = [];
		} else {
			$result = $this->db->query($result);
			$result = $result->getResultArray();

			$data['contents'] = $result;
		}
	}

	private function adminUpload_timestamp(&$data) {
		$data['queryHtmlTableObjModel'] = $this->queryHtmlTableObjModel;
	}

	private function adminUpload_numbers(&$data) {
		$data['queryHtmlTableObjModel'] = $this->queryHtmlTableObjModel;
	}

	private function adminSettings(&$data) {
		$settings = $this->getAllSettings();
		$data['setting'] = $settings;
	}

	private function getAllSettings() {
		$query = $this->db->table('settings')->get();

		$settingsResult = array();

		foreach ($query->getResultArray() as $settingsName => $settingsValue) {
			$settingsResult[$settingsValue['settings_name']] = $settingsValue['settings_value'];
		}

		return $settingsResult;
	}

	private function adminWithdrawal_request(&$data) {
		$withdrawal = loadClass('withdrawal_request');
		$result = $withdrawal->getPendingWithdrawal('pending');
		$data['contents'] = $result;
	}

	private function adminDisputes(&$data) {
		$id = $data['id'];
		$disputes = loadClass('disputes');
		$disputes->ID = $id;
		$disputes->load();

		$user = loadClass('user');
		$user = $user->getRealUserData($disputes->user_id, true);
		if (!$user) {
			return null;
		}

		$data['customer'] = $user;
		$data['content'] = $disputes;
	}

	private function adminReferrals(&$data){
		$id = $data['id'];
		$customer = loadClass('customer');
		$customer = $customer->getRealUserInfo($id);
		$referral = loadClass('referral');
		$referral = $referral->getReferrerLists($customer['user_id']);
		$data['customer'] = $customer;
		$data['contents'] = $referral;
	}

	private function superagent($page, &$data) {
		$this->superagentData = new SuperagentData;
		$superagent = loadClass('superagent');
		$superagent->ID = $this->webSessionManager->getCurrentUserProp('user_type') == 'admin' ? $data['id'] : $this->webSessionManager->getCurrentUserProp('user_table_id');
		$superagent->load();
		$this->superagentData->setSuperagent($superagent);

		if ($this->webSessionManager->getCurrentUserProp('has_change_password') == 0) {
			$data['hasChangePassword'] = $this->webSessionManager->getCurrentUserProp('has_change_password');
		}
		$data['superagent'] = $superagent;
	}

	private function superagentDashboard(&$data) {
		$data = array_merge($data, $this->superagentData->loadDashboardInfo());
	}

	private function superagentAgent_network(&$data) {
		$data = array_merge($data, $this->superagentData->getAgents());
	}

	private function superagentRequest_report(&$data) {
		$data['db'] = $this->db;
		$startDate = isset($_GET['startDate']) && !empty($_GET['startDate']) ? $_GET['startDate'] : null;
		$endDate = isset($_GET['endDate']) && !empty($_GET['endDate']) ? $_GET['endDate'] : null;
		$id = !empty($data['id']) ? $data['id'] : (isset($_GET['agent']) && !empty($_GET['agent']) ? $_GET['agent'] : false);

		if (!$id) {
			redirect()->back()->with('error', 'Kindly choose an agent');
			return;
		}
		$data = array_merge($data, $this->superagentData->getReport($startDate, $endDate, $id));
	}

	private function superagentWallet(&$data) {
		$id = $this->webSessionManager->getCurrentUserProp('ID');
		$commission = loadClass('commission');
		$wallet = loadClass('wallet');
		$userKyc = loadClass('user_kyc_details');
		$wallet = $wallet->getWhere(['user_id' => $id], $count, 0, 1, false);
		$commission = $commission->getWhere(['user_id' => $id], $count, 0, 20, false, 'order by date_created desc');
		//using the three where parameter so that user must have had account setup already
		$userKyc = $userKyc->getwhere(['user_id' => $id, 'bvn_status' => '1', 'status' => '1'], $count, 0, 1, false);
		$userKyc = $userKyc ? true : false;

		$data['db'] = $this->db;
		$data['wallet'] = 0;
		$data['commissionData'] = [];
		if ($wallet) {
			$data['wallet'] = $wallet[0]->amount;
		}
		if ($commission) {
			$data['commission'] = $commission;
		}
		$data['userKycStatus'] = true; // since no need to validate account
	}

	private function superagentNotices(&$data) {
		$notices = loadClass('notices');
		$notices = $notices->getWhere(['status' => '1'], $count, 0, 20, false, 'order by date_created desc');

		$data['noticesData'] = [];

		if ($notices) {
			$data['noticesData'] = $notices;
		}
	}

	private function superagentProfile(&$data) {

	}

	private function nlrc($page, &$data) {
		$this->nlrcData = new NlrcData;
		$user = loadClass('user');
		$user->ID = $data['id'];
		$user->load();

		if ($this->webSessionManager->getCurrentUserProp('has_change_password') == 0) {
			$data['hasChangePassword'] = $this->webSessionManager->getCurrentUserProp('has_change_password');
		}
		$data['user'] = $user;
	}

	private function nlrcDashboard(&$data) {
		$data = array_merge($data, $this->nlrcData->loadDashboardData());
	}

	private function influencer($page, &$data) {
		$this->influencerData = new InfluencerData;
		$influencer = loadClass('influencer');
		$influencer->ID = $this->webSessionManager->getCurrentUserProp('user_type') == 'admin' ? $data['id'] : $this->webSessionManager->getCurrentUserProp('user_table_id');
		$influencer->load();

		if ($this->webSessionManager->getCurrentUserProp('has_change_password') == 0) {
			$data['hasChangePassword'] = $this->webSessionManager->getCurrentUserProp('has_change_password');
		}

		$this->influencerData->setInfluencer($influencer);

		if ($this->webSessionManager->getCurrentUserProp('has_change_password') == 0) {
			$data['hasChangePassword'] = $this->webSessionManager->getCurrentUserProp('has_change_password');
		}

		$data['influencer'] = $influencer;
	}

	private function influencerDashboard(&$data) {
		$data = array_merge($data, $this->influencerData->loadDashboardData());
	}

	private function influencerProfile(&$data) {
		$wallet = loadClass('wallet');
		$userID = $this->webSessionManager->getCurrentUserProp('ID');
		$balance = $wallet->getWalletBalance($userID);
		$data = array_merge($data, ['balance' => $balance]);
	}

	private function influencerWallet(&$data) {
		$id = $this->webSessionManager->getCurrentUserProp('ID');
		$wallet = loadClass('wallet');
		$wallet = $wallet->getWalletBalance($id);

		$data['db'] = $this->db;
		$data['wallet'] = $wallet ?? 0;
		$data['commission'] = [];
	}

	private function promoter($page, &$data) {
		$this->promoterData = new PromoterData;
		$promoter = loadClass('promoter');
		$promoter->ID = $this->webSessionManager->getCurrentUserProp('user_type') == 'admin' ? $data['id'] : $this->webSessionManager->getCurrentUserProp('user_table_id');
		$promoter->load();

		if ($this->webSessionManager->getCurrentUserProp('has_change_password') == 0) {
			$data['hasChangePassword'] = $this->webSessionManager->getCurrentUserProp('has_change_password');
		}

		$this->promoterData->setPromoter($promoter);

		if ($this->webSessionManager->getCurrentUserProp('has_change_password') == 0) {
			$data['hasChangePassword'] = $this->webSessionManager->getCurrentUserProp('has_change_password');
		}

		$data['promoter'] = $promoter;
	}

	private function promoterDashboard(&$data) {
		$data = array_merge($data, $this->promoterData->loadDashboardData());
	}

	private function promoterProfile(&$data) {
		$wallet = loadClass('wallet');
		$userID = $this->webSessionManager->getCurrentUserProp('ID');
		$balance = $wallet->getWalletBalance($userID);
		$data = array_merge($data, ['balance' => $balance]);
	}

	private function promoterWallet(&$data) {
		$id = $this->webSessionManager->getCurrentUserProp('ID');
		$wallet = loadClass('wallet');
		$wallet = $wallet->getWalletBalance($id);

		$data['db'] = $this->db;
		$data['wallet'] = $wallet ?? 0;
		$data['commission'] = [];
	}

//function for loading edit page for general application
	public function edit($model, $id) {
		$userType = $this->webSessionManager->getCurrentUserProp('user_type');
		if ($userType == 'admin') {
			$admin = loadClass('admin');
			$admin->ID = $this->webSessionManager->getCurrentUserProp('user_table_id');
			$admin->load();
			$role = $admin->role;
			$role->checkWritePermission();
			$role = true;
		}

		$ref = @$_SERVER['HTTP_REFERER'];
		if ($ref && !startsWith($ref, base_url())) {
			$this->show_404();
		}
		$exceptionList = array();
		if (empty($id) || in_array($model, $exceptionList)) {
			$this->show_404();
		}
		$formConfig = new FormConfig($role);
		$configData = $formConfig->getUpdateConfig($model);
		$exclude = ($configData && array_key_exists('exclude', $configData)) ? $configData['exclude'] : [];

		$formContent = $this->modelFormBuilder->start($model . '_edit')
			->appendUpdateForm($model, true, $id, $exclude, '')
			->addSubmitLink(null, false)
			->appendSubmitButton('Update', 'btn btn-success')
			->build();
		$result = $formContent;
		displayJson(true, $result);
		return;
	}

	private function show_404() {
		throw new \CodeIgniter\Exceptions\PageNotFoundException();
	}

// this method is for creation of form either in single or combine based on the page desire
	public function create($model, $type = 'add', $data = null) {
		if (!empty($type)) {
			if ($type == 'add') {
				// this is useful for a page that doesn't follow normal procedure of a modal page
				$this->add($model, 'add');
				return;
			} else {
				// this uses modal to show it content
				$this->add($model, $type, $data);
				return;
			}
		}
		return "please specify a type to be created (single page or combine page with view inclusive...)";
	}

	private function add($model, $type, $param = null) {
		if (!$this->webSessionManager->isSessionActive()) {
			header("Location:" . base_url());exit;
		}
		$role_id = $this->webSessionManager->getCurrentUserProp('role_id');
		$userType = $this->webSessionManager->getCurrentUserProp('user_type');
		if ($userType == 'admin') {
			if (!$role_id) {
				$this->show_404();
			}
		}
		$role = false;
		if ($userType == 'admin') {
			$admin = loadClass('admin');
			$admin->ID = $this->webSessionManager->getCurrentUserProp('user_table_id');
			$admin->load();
			$role = $admin->role;
			$data['admin'] = $admin;
			$data['currentRole'] = $role;
			$type = ($type == 'add') ? 'create' : $type;
			$path = "vc/$type/" . $model;

			if (!$role->canView($path)) {
				echo show_access_denied();exit;
			}
			$type = ($type == 'create') ? 'add' : $type;
			$sidebarContent = $this->adminData->getCanViewPages($role);
			$data['canView'] = $sidebarContent;
			$role = true;
		} else if ($userType == 'staff') {
			$staff = loadClass('staff');
			$staff->ID = $this->webSessionManager->getCurrentUserProp('user_table_id');
			$staff->load();
			$role = true;
			$data['staff'] = $staff;
		}

		if ($model == false) {
			$this->show_404();
		}

		$newModel = loadClass($model);
		$modelClass = new $newModel;
		if (!is_subclass_of($modelClass, $this->crudNameSpace)) {
			throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
		}
		$formConfig = new FormConfig($role);
		$data['configData'] = $formConfig->getInsertConfig($model);
		$data['model'] = $model;
		$data['modelObj'] = $newModel;
		$data['appConfig'] = $this->appData;

		// defining some object parameters
		$data['db'] = $this->db;
		$data['webSessionManager'] = $this->webSessionManager;
		$data['queryHtmlTableObjModel'] = $this->queryHtmlTableObjModel;
		$data['tableWithHeaderModel'] = $this->tableWithHeaderModel;
		$data['modelFormBuilder'] = $this->modelFormBuilder;

		echo view($type, $data);
	}

	public function changePassword() {
		if (isset($_POST) && count($_POST) > 0 && !empty($_POST)) {
			$curr_password = trim($_POST['current_password']);
			$new = trim($_POST['password']);
			$confirm = trim($_POST['confirm_password']);

			if (!isNotEmpty($curr_password, $new, $confirm)) {
				echo createJsonMessage('status', false, 'message', "Empty field detected.please fill all required field and try again");
				return;
			}

			$id = $this->webSessionManager->getCurrentUserProp('ID');
			$user = loadClass('user');
			if ($user->findUserProp($id)) {
				$check = decode_password(trim($curr_password), $user->data()[0]['password']);
				if (!$check) {
					echo createJsonMessage('status', false, 'message', 'Please type-in your password correctly.', 'flagAction', false);
					return;
				}
			}

			if ($new !== $confirm) {
				echo createJsonMessage('status', false, 'message', 'New password does not match with the confirmation password', 'flagAction', false);exit;
			}
			$new = encode_password($new);
			$passDate = date('Y-m-d H:i:s');
			$query = "update user set password = '$new',has_change_password = '1' where ID=?";
			if ($this->db->query($query, array($id))) {
				$this->webSessionManager->setContent('has_change_password', '1');
				$arr['status'] = true;
				$arr['message'] = 'Operation successfull';
				$arr['flagAction'] = true;
				echo json_encode($arr);
				return;
			} else {
				$arr['status'] = false;
				$arr['message'] = 'Error occured during operation...';
				$arr['flagAction'] = false;
				echo json_encode($arr);
				return;
			}
		}
		return false;
	}

}
