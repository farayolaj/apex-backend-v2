<?php

namespace App\Entities;

use App\Enums\CommonEnum as CommonSlug;
use App\Enums\PaymentFeeDescriptionEnum as PaymentFeeDescription;
use App\Libraries\ApiResponse;
use App\Libraries\RemitaResponse;
use App\Models\Crud;
use App\Models\WebSessionManager;
use App\Traits\CommonTrait;
use CodeIgniter\Config\Factories;
use Exception;

/**
 * This class  is automatically generated based on the structure of the table. And it represents the model of the transaction table.
 */
class Transaction extends Crud
{
    use CommonTrait;

    protected static $tablename = 'Transaction';
    /* this array contains the field that can be null*/
    static $nullArray = array('payment_url', 'is_third_party');
    static $compositePrimaryKey = array();
    static $uploadDependency = array();
    /*this array contains the fields that are unique*/
    static $uniqueArray = array();
    /*this is an associative array containing the fieldname and the type of the field*/
    static $typeArray = array('payment_id' => 'varchar', 'real_payment_id' => 'int', 'payment_description' => 'varchar', 'payment_option' => 'int', 'student_id' => 'int', 'programme_id' => 'int', 'session' => 'int', 'level' => 'tinyint', 'transaction_ref' => 'varchar', 'rrr_code' => 'varchar', 'payment_status' => 'varchar', 'beneficiary_1' => 'varchar', 'beneficiary_2' => 'varchar', 'payment_status_description' => 'varchar', 'amount_paid' => 'varchar', 'penalty_fee' => 'varchar', 'service_charge' => 'varchar', 'total_amount' => 'varchar', 'payment_url' => 'text', 'is_third_party' => 'tinyint', 'merchant_name' => 'varchar', 'preselected_payment' => 'int', 'transaction_id' => 'varchar', 'subaccount_amount' => 'varchar', 'mainaccount_amount' => 'varchar', 'date_performed' => 'datetime', 'date_completed' => 'datetime', 'date_payment_communicated' => 'datetime');
    /*this is a dictionary that map a field name with the label name that will be shown in a form*/
    static $labelArray = array('id' => '', 'payment_id' => '', 'real_payment_id' => '', 'payment_description' => '', 'payment_option' => '', 'student_id' => '', 'programme_id' => '', 'session' => '', 'level' => '', 'transaction_ref' => '', 'rrr_code' => '', 'payment_status' => '', 'beneficiary_1' => '', 'beneficiary_2' => '', 'payment_status_description' => '', 'amount_paid' => '', 'penalty_fee' => '', 'service_charge' => '', 'total_amount' => '', 'payment_url' => '', 'is_third_party' => '', 'merchant_name' => '', 'preselected_payment' => '', 'transaction_id' => '', 'subaccount_amount' => '', 'mainaccount_amount' => '', 'date_performed' => '', 'date_completed' => '', 'date_payment_communicated' => '');
    /*associative array of fields that have default value*/
    static $defaultArray = array('is_third_party' => '0');
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
    static $documentField = array(); //array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

    static $relation = array('payment' => array('payment_id', 'ID')
    , 'programme' => array('programme_id', 'ID')
    , 'transaction_archive' => array(array('ID', 'transaction_id', 1)),
    );
    static $tableAction = array('delete' => 'delete/transaction', 'edit' => 'edit/transaction');

    function __construct($array = array())
    {
        parent::__construct($array);
    }

    /**
     * @param mixed $value
     * @return <missing>|string
     */
    function getPayment_idFormField($value = '')
    {
        $fk = null; //change the value of this variable to array('table'=>'payment','display'=>'payment_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

        if (is_null($fk)) {
            return $result = "<input type='hidden' value='$value' name='payment_id' id='payment_id' class='form-control' />
			";
        }
        if (is_array($fk)) {
            $result = "<div class='form-group'>
		<label for='payment_id'>Payment Id</label>";
            $option = $this->loadOption($fk, $value);
            //load the value from the given table given the name of the table to load and the display field
            $result .= "<select name='payment_id' id='payment_id' class='form-control'>
			$option
		</select>";
        }
        $result .= "</div>";
        return $result;

    }

    /**
     * @param mixed $value
     * @return <missing>|string
     */
    function getReal_payment_idFormField($value = '')
    {
        $fk = null; //change the value of this variable to array('table'=>'real_payment','display'=>'real_payment_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

        if (is_null($fk)) {
            return $result = "<input type='hidden' value='$value' name='real_payment_id' id='real_payment_id' class='form-control' />
			";
        }
        if (is_array($fk)) {
            $result = "<div class='form-group'>
		<label for='real_payment_id'>Real Payment Id</label>";
            $option = $this->loadOption($fk, $value);
            //load the value from the given table given the name of the table to load and the display field
            $result .= "<select name='real_payment_id' id='real_payment_id' class='form-control'>
			$option
		</select>";
        }
        $result .= "</div>";
        return $result;

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getPayment_descriptionFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='payment_description' >Payment Description</label>
		<input type='text' name='payment_description' id='payment_description' value='$value' class='form-control' required />
</div> ";

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getPayment_optionFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='payment_option' >Payment Option</label><input type='number' name='payment_option' id='payment_option' value='$value' class='form-control' required />
</div> ";

    }

    /**
     * @param mixed $value
     * @return <missing>|string
     */
    function getStudent_idFormField($value = '')
    {
        $fk = null; //change the value of this variable to array('table'=>'student','display'=>'student_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

        if (is_null($fk)) {
            return $result = "<input type='hidden' value='$value' name='student_id' id='student_id' class='form-control' />
			";
        }
        if (is_array($fk)) {
            $result = "<div class='form-group'>
		<label for='student_id'>Student Id</label>";
            $option = $this->loadOption($fk, $value);
            //load the value from the given table given the name of the table to load and the display field
            $result .= "<select name='student_id' id='student_id' class='form-control'>
			$option
		</select>";
        }
        $result .= "</div>";
        return $result;

    }

    /**
     * @param mixed $value
     * @return <missing>|string
     */
    function getProgramme_idFormField($value = '')
    {
        $fk = null; //change the value of this variable to array('table'=>'programme','display'=>'programme_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.

        if (is_null($fk)) {
            return $result = "<input type='hidden' value='$value' name='programme_id' id='programme_id' class='form-control' />
			";
        }
        if (is_array($fk)) {
            $result = "<div class='form-group'>
		<label for='programme_id'>Programme Id</label>";
            $option = $this->loadOption($fk, $value);
            //load the value from the given table given the name of the table to load and the display field
            $result .= "<select name='programme_id' id='programme_id' class='form-control'>
			$option
		</select>";
        }
        $result .= "</div>";
        return $result;

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getSessionFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='session' >Session</label><input type='number' name='session' id='session' value='$value' class='form-control' required />
</div> ";

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getLevelFormField($value = '')
    {

        return "<div class='form-group'>
	<label class='form-checkbox'>Level</label>
	<select class='form-control' id='level' name='level' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getTransaction_refFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='transaction_ref' >Transaction Ref</label>
		<input type='text' name='transaction_ref' id='transaction_ref' value='$value' class='form-control' required />
</div> ";

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getRrr_codeFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='rrr_code' >Rrr Code</label>
		<input type='text' name='rrr_code' id='rrr_code' value='$value' class='form-control' required />
</div> ";

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getPayment_statusFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='payment_status' >Payment Status</label>
		<input type='text' name='payment_status' id='payment_status' value='$value' class='form-control' required />
</div> ";

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getBeneficiary_1FormField($value = '')
    {

        return "<div class='form-group'>
	<label for='beneficiary_1' >Beneficiary 1</label>
		<input type='text' name='beneficiary_1' id='beneficiary_1' value='$value' class='form-control' required />
</div> ";

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getBeneficiary_2FormField($value = '')
    {

        return "<div class='form-group'>
	<label for='beneficiary_2' >Beneficiary 2</label>
		<input type='text' name='beneficiary_2' id='beneficiary_2' value='$value' class='form-control' required />
</div> ";

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getPayment_status_descriptionFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='payment_status_description' >Payment Status Description</label>
		<input type='text' name='payment_status_description' id='payment_status_description' value='$value' class='form-control' required />
</div> ";

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getAmount_paidFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='amount_paid' >Amount Paid</label>
		<input type='text' name='amount_paid' id='amount_paid' value='$value' class='form-control' required />
</div> ";

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getPenalty_feeFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='penalty_fee' >Penalty Fee</label>
		<input type='text' name='penalty_fee' id='penalty_fee' value='$value' class='form-control' required />
</div> ";

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getService_chargeFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='service_charge' >Service Charge</label>
		<input type='text' name='service_charge' id='service_charge' value='$value' class='form-control' required />
</div> ";

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getTotal_amountFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='total_amount' >Total Amount</label>
		<input type='text' name='total_amount' id='total_amount' value='$value' class='form-control' required />
</div> ";

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getPayment_urlFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='payment_url' >Payment Url</label>
<textarea id='payment_url' name='payment_url' class='form-control' >$value</textarea>
</div> ";

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getIs_third_partyFormField($value = '')
    {

        return "<div class='form-group'>
	<label class='form-checkbox'>Is Third Party</label>
	<select class='form-control' id='is_third_party' name='is_third_party' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getMerchant_nameFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='merchant_name' >Merchant Name</label>
		<input type='text' name='merchant_name' id='merchant_name' value='$value' class='form-control' required />
</div> ";

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getPreselected_paymentFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='preselected_payment' >Preselected Payment</label>
		<input type='text' name='preselected_payment' id='preselected_payment' value='$value' class='form-control' required />
</div> ";

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getTransaction_idFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='transaction_id' >Transaction ID</label>
		<input type='text' name='transaction_id' id='transaction_id' value='$value' class='form-control' required />
</div> ";

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getSubaccount_amountFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='subaccount_amount' >Subaccount Amount</label>
		<input type='text' name='subaccount_amount' id='subaccount_amount' value='$value' class='form-control' required />
</div> ";

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getMainaccount_amountFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='mainaccount_amount' >Mainaccount Amount</label>
		<input type='text' name='mainaccount_amount' id='mainaccount_amount' value='$value' class='form-control' required />
</div> ";

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getDate_performedFormField($value = '')
    {

        return " ";

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getDate_completedFormField($value = '')
    {

        return " ";

    }

    /**
     * @param mixed $value
     * @return string
     */
    function getDate_payment_communicatedFormField($value = '')
    {
        return " ";
    }

    /**
     * @return bool|Fee_description
     * @throws \Exception
     */
    protected function getFee_description()
    {
        $query = 'SELECT * FROM fee_description WHERE id=?';
        $result = $this->query($query, [$this->payment_id]);
        if (!$result) {
            return false;
        }
        return new \App\Entities\Fee_description($result[0]);
    }

    /**
     * @return null|bool|Payment
     */
    protected function getPayment()
    {
        $query = 'SELECT * FROM payment WHERE id=?';
        if (!isset($this->array['id'])) {
            return null;
        }
        $id = $this->array['id'];
        $result = $this->db->query($query, array($id));
        $result = $result->getResultArray();
        if (empty($result)) {
            return false;
        }
        return new \App\Entities\Payment($result[0]);
    }

    /**
     * @return null|bool|Programme
     */
    protected function getProgramme()
    {
        $query = 'SELECT * FROM programme WHERE id=?';
        if (!isset($this->array['id'])) {
            return null;
        }
        $id = $this->array['id'];
        $result = $this->db->query($query, array($id));
        $result = $result->getResultArray();
        if (empty($result)) {
            return false;
        }
        return new \App\Entities\Programme($result[0]);
    }

    /**
     * @return bool|<missing>
     */
    protected function getTransaction_archive()
    {
        $query = 'SELECT * FROM transaction_archive WHERE transaction_id=?';
        $id = $this->array['id'];
        $result = $this->db->query($query, array($id));
        $result = $result->getResultArray();
        if (empty($result)) {
            return false;
        }
        $resultObjects = array();
        foreach ($result as $value) {
            $resultObjects[] = new \App\Entities\Transaction_archive($value);
        }
        return $resultObjects;
    }

    public function delete($id = NULL, &$dbObject = NULL, $type = null)
    {
        if ($type == 'student_trans') {
            $currentUser = WebSessionManager::currentAPIUser();
            permissionAccess('transaction_delete', 'delete');

            $this->db->transBegin();
            $getTransactionStatus = $this->checkTransactionPaymentStatus($id, 'transaction');
            if (isset($getTransactionStatus['status']) && !$getTransactionStatus['status']) {
                $this->db->transRollback();
                return ApiResponse::error($getTransactionStatus['message']);
            }

            if (!$this->moveTransactionToArchive($id)) {
                $this->db->transRollback();
                return ApiResponse::error("An error occurred, Transaction cannot be deleted at the moment");
            }

            if (!parent::delete($id)) {
                $this->db->transRollback();
                return ApiResponse::error("An error occurred, Transaction cannot be deleted at the moment");
            }

            logAction( 'delete_transaction', $currentUser->user_login);
            $this->db->transCommit();
            return ApiResponse::success("Transaction deleted successfully");
        }

        if ($type == 'admission_trans') {
            $currentUser = WebSessionManager::currentAPIUser();
            permissionAccess('transaction_delete', 'delete');

            $getTransactionStatus = $this->checkTransactionPaymentStatus($id, 'applicant_transaction');
            if (isset($getTransactionStatus['status']) && !$getTransactionStatus['status']) {
                $this->db->transRollback();
                return ApiResponse::error($getTransactionStatus['message']);
            }

            if (!$this->moveApplicantTransactionToArchive($id)) {
                $this->db->transRollback();
                return ApiResponse::error("An error occurred, applicant transaction cannot be deleted at the moment");
            }

            if (!$this->deleteTransaction($id, 'applicant_transaction')) {
                $this->db->transRollback();
                return ApiResponse::error("An error occurred, applicant transaction cannot be deleted at the moment");
            }

            logAction( 'delete_applicant_transaction', $currentUser->user_login);
            $this->db->transCommit();
            return ApiResponse::success("Applicant transaction deleted successfully");
        }

        if ($type == 'custom_trans') {
            $currentUser = WebSessionManager::currentAPIUser();
            permissionAccess('transaction_delete', 'delete');

            $getTransactionStatus = $this->checkTransactionPaymentStatus($id, 'transaction_custom');
            if (isset($getTransactionStatus['status']) && !$getTransactionStatus['status']) {
                $this->db->transRollback();
                return ApiResponse::error($getTransactionStatus['message']);
            }

            if (!$this->moveTransactionCustomToArchive($id)) {
                $this->db->transRollback();
                return ApiResponse::error("An error occurred, non-student transaction cannot be deleted at the moment");
            }

            if (!$this->deleteTransaction($id, 'transaction_custom')) {
                $this->db->transRollback();
                return ApiResponse::error("An error occurred, non-student transaction cannot be deleted at the moment");
            }

            logAction( 'delete_transaction_custom', $currentUser->user_login);
            $this->db->transCommit();
            return ApiResponse::success("Non-student transaction deleted successfully");
        }

        return false;
    }

    /**
     * @param mixed $id
     * @param mixed $entity
     */
    public function deleteTransaction($id, $entity)
    {
        $query = "DELETE from $entity where id=?";
        return $this->query($query, [$id]);
    }

    /**
     * @param mixed $transactionID
     * @param mixed $entity
     * @return array<string,mixed>|bool
     */
    public function checkTransactionPaymentStatus($transactionID, $entity)
    {
        $query = "SELECT * from $entity where id = ?";
        $result = $this->query($query, [$transactionID]);
        if (!$result) {
            return ['status' => false, 'message' => 'No transaction found'];
        }
        $result = $result[0];
        if (!$result['rrr_code']) {
            return true;
        }

        $payment_channel = get_setting('payment_gateway');
        if ($payment_channel == 'remita') {
            $remita = Factories::libraries('Remita');
            $transactionRef = $result['transaction_ref'] ?: null;
            if (!$transactionRef) {
                return ['status' => false, 'message' => 'Invalid transaction reference'];
            }
            $temp = $remita->getRemitaData($result['transaction_ref'], null, null, $transactionRef);
            // when $temp['curlStatus'] is false, the curl method is GET method
            if (!$temp['curlStatus']) {
                $extraData = $temp['extraData'];
                $response = $remita->remitaTransactionDetails($extraData['url'], $temp['header']);
                if (ENVIRONMENT === 'production' || ENVIRONMENT === 'development') {
                    if (!isset($response['status'])) {
                        return ['status' => false, 'message' => 'Transaction RRR status not found'];
                    }
                }

                if (self::isPaymentValid($response['status'])) {
                    return ['status' => false, 'message' => "Transaction could not be deleted, transaction status might have changed to success"];
                } else {
                    return true;
                }
            }
        }
    }

    /**
     * @param mixed $transactionID
     * @return bool
     * @throws Exception
     */
    private function moveTransactionToArchive($transactionID)
    {
        $query = "SELECT * FROM transaction where id = ?";
        $result = $this->query($query, [$transactionID]);
        if (!$result) {
            return false;
        }
        $get_details = new Transaction($result[0]);
        if (@$get_details->transaction_ref != '') {
            $transaction_details = array(
                'transaction_id' => $get_details->id,
                'real_payment_id' => $get_details->real_payment_id,
                'payment_id' => $get_details->payment_id,
                'payment_description' => $get_details->payment_description,
                'payment_option' => $get_details->payment_option,
                'student_id' => $get_details->student_id,
                'programme_id' => $get_details->programme_id,
                'session' => $get_details->session,
                'level' => $get_details->level,
                'transaction_ref' => $get_details->transaction_ref,
                'rrr_code' => $get_details->rrr_code,
                'payment_status' => $get_details->payment_status,
                'beneficiary_1' => $get_details->beneficiary_1,
                'beneficiary_2' => $get_details->beneficiary_2,
                'payment_status_description' => $get_details->payment_status_description,
                'amount_paid' => $get_details->amount_paid,
                'penalty_fee' => $get_details->penalty_fee,
                'service_charge' => $get_details->service_charge,
                'total_amount' => $get_details->total_amount,
                'payment_url' => $get_details->payment_url,
                'date_performed' => $get_details->date_performed,
                'date_completed' => $get_details->date_completed,
                'date_payment_communicated' => $get_details->date_payment_communicated,
                'preselected_payment' => $get_details->preselected_payment,
                'transaction_ref_id' => $get_details->transaction_id,
                'subaccount_amount' => $get_details->subaccount_amount,
                'mainaccount_amount' => $get_details->mainaccount_amount,
                'beneficiary_3' => $get_details->beneficiary_3,
                'source_table' => 'transaction',
            );
            $this->db->table('transaction_archive')->insert($transaction_details);
            return true;
        }
        return false;
    }

    /**
     * @param mixed $transactionID
     * @return bool
     */
    private function moveApplicantTransactionToArchive($transactionID)
    {
        $query = "SELECT * FROM applicant_transaction where id = ?";
        $result = $this->query($query, [$transactionID]);
        if (!$result) {
            return false;
        }

        $get_details = new \App\Entities\Applicant_transaction($result[0]);
        if (@$get_details->transaction_ref != '') {
            $transaction_details = array(
                'transaction_id' => $get_details->id,
                'real_payment_id' => 0,
                'payment_id' => $get_details->payment_id,
                'payment_description' => $get_details->payment_description,
                'payment_option' => 0,
                'student_id' => $get_details->applicant_id,
                'programme_id' => 0,
                'session' => $get_details->session,
                'level' => 0,
                'transaction_ref' => $get_details->transaction_ref,
                'rrr_code' => $get_details->rrr_code,
                'payment_status' => $get_details->payment_status,
                'beneficiary_1' => $get_details->beneficiary_1,
                'beneficiary_2' => $get_details->beneficiary_2,
                'payment_status_description' => $get_details->payment_status_description,
                'amount_paid' => $get_details->amount_paid,
                'penalty_fee' => '',
                'service_charge' => $get_details->service_charge,
                'total_amount' => $get_details->total_amount,
                'payment_url' => '',
                'date_performed' => $get_details->date_performed,
                'date_completed' => $get_details->date_completed,
                'date_payment_communicated' => $get_details->date_payment_communicated,
                'preselected_payment' => 0,
                'transaction_ref_id' => $get_details->transaction_id,
                'subaccount_amount' => $get_details->subaccount_amount,
                'mainaccount_amount' => $get_details->mainaccount_amount,
                'beneficiary_3' => $get_details->beneficiary_3,
                'source_table' => 'applicant_transaction',
            );
            $this->db->table('transaction_archive')->insert($transaction_details);
            return true;
        }
        return false;
    }

    public function moveTransactionCustomToArchive($transactionID)
    {
        $query = "SELECT * FROM transaction_custom where id = ?";
        $result = $this->query($query, [$transactionID]);
        if (!$result) {
            return false;
        }
        $get_details = new \App\Entities\Transaction_custom($result[0]);
        if (@$get_details->transaction_ref != '') {
            $transaction_details = array(
                'transaction_id' => $get_details->id,
                'real_payment_id' => 0,
                'payment_id' => $get_details->payment_id,
                'payment_description' => $get_details->payment_description,
                'payment_option' => 0,
                'student_id' => $get_details->custom_users_id,
                'programme_id' => 0,
                'session' => $get_details->session,
                'level' => 0,
                'transaction_ref' => $get_details->transaction_ref,
                'rrr_code' => $get_details->rrr_code,
                'payment_status' => $get_details->payment_status,
                'beneficiary_1' => $get_details->beneficiary_1,
                'beneficiary_2' => $get_details->beneficiary_2,
                'payment_status_description' => $get_details->payment_status_description,
                'amount_paid' => $get_details->amount_paid,
                'penalty_fee' => '',
                'service_charge' => $get_details->service_charge,
                'total_amount' => $get_details->total_amount,
                'payment_url' => '',
                'date_performed' => $get_details->date_performed,
                'date_completed' => $get_details->date_completed,
                'date_payment_communicated' => $get_details->date_payment_communicated,
                'preselected_payment' => 0,
                'transaction_ref_id' => $get_details->transaction_id,
                'subaccount_amount' => $get_details->subaccount_amount,
                'mainaccount_amount' => $get_details->mainaccount_amount,
                'beneficiary_3' => $get_details->beneficiary_3,
                'source_table' => 'transaction_custom',
            );
            $this->db->table('transaction_archive')->insert($transaction_details);
            return true;
        }
        return false;
    }

    /**
     * @param mixed $transactionID
     * @return bool|Transaction_archive
     */
    public function checkArchivePaymentStatus($transactionID)
    {
        $query = "SELECT * from transaction_archive where id = ?";
        $result = $this->query($query, [$transactionID]);
        if (!$result) {
            return false;
        }

        return new \App\Entities\Transaction_archive($result[0]);
    }

    /**
     * @param mixed $transactionID
     * @param mixed $entity
     * @return bool
     */
    public function restoreTransactionToArchive($transactionID, $entity)
    {
        $query = "SELECT * FROM transaction_archive where id = ? and source_table = ?";
        $result = $this->query($query, [$transactionID, $entity]);
        if (!$result) {
            return false;
        }

        $get_details = new \App\Entities\Transaction_archive($result[0]);
        if (@$get_details->transaction_id != '') {
            $transaction_details = null;
            if ($entity == 'transaction') {
                $transaction_details = array(
                    'id' => $get_details->transaction_id,
                    'real_payment_id' => $get_details->real_payment_id,
                    'payment_id' => $get_details->payment_id,
                    'payment_description' => $get_details->payment_description,
                    'payment_option' => $get_details->payment_option,
                    'student_id' => $get_details->student_id,
                    'programme_id' => $get_details->programme_id,
                    'session' => $get_details->session,
                    'level' => $get_details->level,
                    'transaction_ref' => $get_details->transaction_ref,
                    'rrr_code' => $get_details->rrr_code,
                    'payment_status' => $get_details->payment_status,
                    'beneficiary_1' => $get_details->beneficiary_1,
                    'beneficiary_2' => $get_details->beneficiary_2,
                    'payment_status_description' => $get_details->payment_status_description,
                    'amount_paid' => $get_details->amount_paid,
                    'penalty_fee' => $get_details->penalty_fee,
                    'service_charge' => $get_details->service_charge,
                    'total_amount' => $get_details->total_amount,
                    'payment_url' => $get_details->payment_url,
                    'date_performed' => $get_details->date_performed,
                    'date_completed' => $get_details->date_completed,
                    'date_payment_communicated' => $get_details->date_payment_communicated,
                    'preselected_payment' => $get_details->preselected_payment,
                    'transaction_id' => $get_details->transaction_ref_id,
                    'subaccount_amount' => $get_details->subaccount_amount,
                    'mainaccount_amount' => $get_details->mainaccount_amount,
                    'beneficiary_3' => $get_details->beneficiary_3,
                );
            } else if ($entity == 'applicant_transaction') {
                $transaction_details = array(
                    'id' => $get_details->transaction_id,
                    'payment_id' => $get_details->payment_id,
                    'payment_description' => $get_details->payment_description,
                    'applicant_id' => $get_details->student_id,
                    'session' => $get_details->session,
                    'transaction_ref' => $get_details->transaction_ref,
                    'rrr_code' => $get_details->rrr_code,
                    'payment_status' => $get_details->payment_status,
                    'beneficiary_1' => $get_details->beneficiary_1,
                    'beneficiary_2' => $get_details->beneficiary_2,
                    'payment_status_description' => $get_details->payment_status_description,
                    'amount_paid' => $get_details->amount_paid,
                    'service_charge' => $get_details->service_charge,
                    'total_amount' => $get_details->total_amount,
                    'date_performed' => $get_details->date_performed,
                    'date_completed' => $get_details->date_completed,
                    'date_payment_communicated' => $get_details->date_payment_communicated,
                    'transaction_id' => $get_details->transaction_ref_id,
                    'subaccount_amount' => $get_details->subaccount_amount,
                    'mainaccount_amount' => $get_details->mainaccount_amount,
                    'beneficiary_3' => $get_details->beneficiary_3,
                );
            }

            $this->db->table($entity)->insert($transaction_details);
            return true;
        }
        return false;
    }

    /**
     * @param mixed $filterList
     * @param mixed $queryString
     * @param mixed $start
     * @param mixed $len
     * @param mixed $orderBy
     * @return array|string
     */
    public function APIList($filterList, $queryString, $start, $len, $orderBy = null)
    {

        $paymentStatus = request()->getGet('payment_status') ?? null;
        $paymentType = request()->getGet('payment_type') ?? null;
        $department = request()->getGet('department') ?? null;
        $session = request()->getGet('session') ?? null;
        $from = request()->getGet('start_date') ?? null;
        $to = request()->getGet('end_date') ?? null;
        $q = request()->getGet('q') ?? null;
        $export = request()->getGet('export') ?? null;

        $limit = '';
        if (isset($_GET['start']) && $len) {
            $limit = " limit $start, $len";
        }

        $where = '';
        $where1 = '';
        $where2 = '';
        $skipCustom = false;

        if ($paymentType) {
            $paymentType = $this->db->escapeString($paymentType);
            $where .= ($where ? ' and ' : ' where ') . " a.payment_id='{$paymentType}'";
            $where2 .= ($where2 ? ' and ' : ' where ') . " ap.description='{$paymentType}'";
            $where1 .= ($where1 ? ' and ' : ' where ') . " a.payment_id='{$paymentType}'";
        }

        if ($department) {
            $department = $this->db->escapeString($department);
            $skipCustom = true;
            $where .= ($where ? ' and ' : ' where ') . " e.department_id='{$department}'";
            $where2 .= ($where2 ? ' and ' : ' where ') . " e.department_id='{$department}'";
            $where1 .= ($where1 ? ' and ' : ' where ') . " e.department_id='{$department}'";
        }

        if ($session) {
            $session = $this->db->escapeString($session);
            $skipCustom = true;
            $where .= ($where ? ' and ' : ' where ') . " a.session='{$session}'";
            $where2 .= ($where2 ? ' and ' : ' where ') . " a.session='{$session}'";
            $where1 .= ($where1 ? ' and ' : ' where ') . " a.session='{$session}'";
        }

        if ($from && $to) {
            $from = $this->db->escapeString($from);
            $to = $this->db->escapeString($to);
            $where .= ($where ? " and " : " where ") . " date(a.date_performed) between date('$from') and date('$to') ";
            $where2 .= ($where2 ? " and " : " where ") . " date(a.date_performed) between date('$from') and date('$to') ";
            $where1 .= ($where1 ? " and " : " where ") . " date(a.date_performed) between date('$from') and date('$to') ";
        } else if ($from) {
            $from = $this->db->escapeString($from);
            $where .= ($where ? " and " : " where ") . " date(a.date_performed) = date('$from') ";
            $where2 .= ($where2 ? " and " : " where ") . " date(a.date_performed) = date('$from') ";
            $where1 .= ($where1 ? " and " : " where ") . " date(a.date_performed) = date('$from') ";
        }

        if ($paymentStatus == 'paid') {
            $where .= ($where ? ' and ' : ' where ') . " a.payment_status in ('00', '01') ";
            $where2 .= ($where2 ? ' and ' : ' where ') . " a.payment_status in ('00', '01') ";
            $where1 .= ($where1 ? ' and ' : ' where ') . " a.payment_status in ('00', '01') ";
        } else if ($paymentStatus == 'pending') {
            $where .= ($where ? ' and ' : ' where ') . " a.payment_status not in ('00', '01') ";
            $where2 .= ($where2 ? ' and ' : ' where ') . " a.payment_status not in ('00', '01') ";
            $where1 .= ($where1 ? ' and ' : ' where ') . " a.payment_status not in ('00', '01') ";
        }

        if ($q) {
            $where1 = $where1 ?: $where;
            $where2 = $where2 ?: $where;
            $searchList = [
                'firstname', 'lastname', 'matric_number', 'rrr_code', 'payment_description', 'transaction_ref', 'amount_paid',
                'e.name', 'd.application_number',
            ];
            $searchList1 = [
                'name', 'rrr_code', 'payment_description', 'transaction_ref', 'amount_paid',
            ];
            $searchList2 = [
                'firstname', 'lastname', 'b.applicant_id', 'rrr_code', 'payment_description', 'transaction_ref', 'amount_paid',
                'e.name',
            ];
            $queryString = buildCustomSearchString($searchList, $q);
            $queryString1 = buildCustomSearchString($searchList1, $q);
            $queryString2 = buildCustomSearchString($searchList2, $q);
            $where .= ($where ? ' and ' : ' where ') . " ($queryString) ";
            $where1 .= ($where1 ? ' and ' : ' where ') . " ($queryString1) ";
            $where2 .= ($where2 ? ' and ' : ' where ') . " ($queryString2) ";
        }

        if ($export == 'transaction') {
            $query = "SELECT SQL_CALC_FOUND_ROWS concat(firstname, ' ',lastname) as fullname,matric_number as application_number,
       		payment_status_description, payment_description as descrip,transaction_ref,rrr_code,payment_status,a.mainaccount_amount as ui_amount,
       		a.subaccount_amount as dlc_amount,(a.mainaccount_amount+a.subaccount_amount) as total_amount,a.amount_paid,a.total_amount as cum_amount,
			timestamp(date_completed) as orderBy,f.name as department_name,a.service_charge as debit_note,b.reg_num,e.name as programme_name,
			g.date as session_text,a.real_payment_id,a.date_performed from transaction a left join payment h on h.id = a.real_payment_id
			join students b on b.id = a.student_id join academic_record d on d.student_id = b.id join programme e on e.id = d.programme_id
			join department f on f.id = e.department_id join sessions g on g.id= a.session {$where}
			UNION
		 	(SELECT concat(firstname, ' ',lastname) as fullname,b.applicant_id as application_number,payment_status_description,
		 	     payment_description as descrip,transaction_ref,rrr_code,payment_status,a.mainaccount_amount as ui_amount,a.subaccount_amount as dlc_amount,
		 	     (a.mainaccount_amount+a.subaccount_amount) as total_amount,a.amount_paid,a.total_amount as cum_amount,
		 	     timestamp(date_completed) as orderBy,f.name as department_name,a.service_charge as debit_note,b.applicant_id as reg_num,
		 	     e.name as programme_name,g.date as session_text,a.payment_id as real_payment_id,a.date_performed from applicant_transaction a
		 	    join applicants b on b.id = a.applicant_id join programme e on e.id = b.programme_id join department f on f.id = e.department_id
		 	    join sessions g on g.id= a.session {$where} ) ";

            if (!$skipCustom) {
                $where = $where1 ?: $where;
                $query .= " UNION
				(SELECT name as fullname, b.phone_number as application_number, payment_status_description, payment_description as descrip,
				transaction_ref,rrr_code,payment_status,a.mainaccount_amount as ui_amount,a.subaccount_amount as dlc_amount,
				(a.mainaccount_amount+a.subaccount_amount) as total_amount,a.amount_paid,a.total_amount as cum_amount,
				timestamp(date_completed) as orderBy,'' as department_name,a.service_charge as debit_note,b.email as reg_num,'' as programme_name,
				'' as session_text,'' as real_payment_id,a.date_performed from transaction_custom a join users_custom b on b.id = a.custom_users_id
				{$where} )";
            }
        } else if ($export == 'journal') {
            $query = "SELECT SQL_CALC_FOUND_ROWS a.id as id, concat(firstname, ' ',lastname) as fullname,matric_number as application_number, 
				payment_description as descrip,transaction_ref,rrr_code,date_performed,payment_status,a.mainaccount_amount as ui_amount,
				a.subaccount_amount as dlc_amount,(a.mainaccount_amount+a.subaccount_amount) as total_amount,a.total_amount as cum_amount,
				timestamp(date_completed) as orderBy,f.name as department_name,a.service_charge as debit_note,
				b.reg_num,e.name as programme_name,g.date as session_text,c.description as payment_type,a.real_payment_id,
				'student_trans' as trans_type,a.level as level,a.amount_paid,a.penalty_fee,d.entry_mode,s2.date as year_of_entry,
				p2.name as transaction_programme,d.programme_id as aca_prog_id,d.current_level as aca_level,
				payment_status_description,b.gender from transaction a left join payment h on h.id = a.real_payment_id 
				join students b on b.id = a.student_id join academic_record d on d.student_id = b.id join fee_description c 
				on c.id = a.payment_id join programme e on e.id = d.programme_id join department f on f.id = e.department_id 
				join sessions g on g.id = a.session join sessions s2 on s2.id = d.year_of_entry join programme p2 on p2.id = a.programme_id 
				{$where} ";

            if ($where) {
                $where = $where2 ?: $where;
                $query .= " UNION
		 		(SELECT a.id as id, concat(firstname, ' ',lastname) as fullname,b.applicant_id as application_number, 
		 			payment_description as descrip,transaction_ref,rrr_code,date_performed,payment_status,a.mainaccount_amount as ui_amount,
		 			a.subaccount_amount as dlc_amount,(a.mainaccount_amount+a.subaccount_amount) as total_amount,a.total_amount as cum_amount,
		 			timestamp(date_completed) as orderBy,f.name as department_name,a.service_charge as debit_note,b.applicant_id as reg_num,
		 			e.name as programme_name,g.date as session_text,c.description as payment_type,a.payment_id as real_payment_id,
		 			'admission_trans' as trans_type,'' as level,a.amount_paid,'' as penalty_fee,b.entry_mode,s2.date as year_of_entry,
		 			p2.name as transaction_programme,b.programme_given as aca_prog_id,'' as aca_level,payment_status_description,
		 			b.gender from applicant_transaction a join applicants b on b.id = a.applicant_id join applicant_payment ap on ap.id = a.payment_id 
		 			join fee_description c on c.id = ap.description join programme e on e.id = b.programme_id join department f 
		 			on f.id = e.department_id join sessions g on g.id = a.session join sessions s2 on s2.id = b.session_id join 
		 			programme p2 on p2.id = b.programme_given {$where} ) ";
            }

            if (!$skipCustom) {
                $where = $where1 ?: $where;
                $query .= " UNION
		 		(SELECT a.id as id, name as fullname,b.phone_number as application_number, payment_description as descrip,
		 			transaction_ref,rrr_code,date_performed,payment_status,a.mainaccount_amount as ui_amount,a.subaccount_amount as dlc_amount,
		 			(a.mainaccount_amount+a.subaccount_amount) as total_amount,a.total_amount as cum_amount,timestamp(date_completed) as orderBy,
		 			'' as department_name,a.service_charge as debit_note,b.email as reg_num,'' as programme_name,'' as session_text,
		 			payment_description as payment_type,'' as real_payment_id,'custom_trans' as trans_type,'' as level,a.amount_paid,
		 			'' as penalty_fee,'' as entry_mode,'' as year_of_entry,'' as transaction_programme,'' as aca_prog_id,'' as aca_level,
		 			payment_status_description,'' as gender from transaction_custom a join users_custom b on b.id = a.custom_users_id join 
		 			fee_description c on c.id = a.payment_id {$where} )";
            }
        } else {
            $query = "SELECT SQL_CALC_FOUND_ROWS a.id as id,a.student_id, concat(firstname, ' ',lastname) as fullname,
       			matric_number as application_number, payment_description as descrip,transaction_ref,rrr_code,date_performed,
       			payment_status,a.mainaccount_amount as ui_amount,a.subaccount_amount as dlc_amount,
       			(a.mainaccount_amount+a.subaccount_amount) as total_amount,a.total_amount as cum_amount,UNIX_TIMESTAMP(IFNULL(date_completed, '1970-01-01')) as orderBy,
				f.name as department_name,a.service_charge as debit_note,b.reg_num,e.name as programme_name,g.date as session_text,
				c.description as payment_type,a.real_payment_id,'student_trans' as trans_type,a.payment_option,a.level from
				transaction a left join payment h on h.id = a.real_payment_id join students b on b.id = a.student_id join
				academic_record d on d.student_id = b.id join fee_description c on c.id = a.payment_id join programme e on
				e.id = a.programme_id join department f on f.id = e.department_id join sessions g on g.id = a.session {$where} ";

            if ($where) {
                $where = $where2 ?: $where;
                $query .= " UNION
		 		(SELECT a.id as id,a.applicant_id as student_id, concat(firstname, ' ',lastname) as fullname,b.applicant_id as application_number,
		 		payment_description as descrip,transaction_ref,rrr_code,date_performed,payment_status,a.mainaccount_amount as ui_amount,
		 		a.subaccount_amount as dlc_amount,(a.mainaccount_amount+a.subaccount_amount) as total_amount,a.total_amount as cum_amount,
		 		UNIX_TIMESTAMP(IFNULL(date_completed, '1970-01-01')) as orderBy,f.name as department_name,a.service_charge as debit_note,b.applicant_id as reg_num,
		 		e.name as programme_name,g.date as session_text,c.description as payment_type,a.payment_id as real_payment_id,
		 		'admission_trans' as trans_type,'' as payment_option,'' as level from applicant_transaction a join applicants b on
		 		b.id = a.applicant_id join applicant_payment ap on ap.id = a.payment_id join fee_description c on c.id = ap.description join
		 		programme e on e.id = b.programme_id join department f on f.id = e.department_id join sessions g on g.id = a.session {$where} ) ";
            }

            if (!$skipCustom) {
                $where = $where1 ?: $where;
                $query .= " UNION
		 		(SELECT a.id as id,'' as student_id, name as fullname,b.phone_number as application_number, payment_description as descrip,
		 		transaction_ref,rrr_code,date_performed,payment_status,a.mainaccount_amount as ui_amount,a.subaccount_amount as dlc_amount,
		 		(a.mainaccount_amount+a.subaccount_amount) as total_amount,a.total_amount as cum_amount,UNIX_TIMESTAMP(IFNULL(date_completed, '1970-01-01')) as orderBy,
		 		'' as department_name,a.service_charge as debit_note,b.email as reg_num,'' as programme_name,'' as session_text,
		 		payment_description as payment_type,'' as real_payment_id,'custom_trans' as trans_type,a.payment_option,'' as level from
		 		transaction_custom a join users_custom b on b.id = a.custom_users_id join fee_description c on c.id = a.payment_id {$where} )";
            }
        }

        if ($export == 'transaction') {
            $query .= "order by date_performed asc, programme_name asc, descrip asc {$limit}";
            return $query;
        } else if ($export == 'journal') {
            $query .= " order by programme_name asc, orderBy asc, level asc {$limit}";
            return $query;
        } else {
            if (isset($_GET['sortBy']) && $orderBy) {
                $query .= " order by $orderBy {$limit}";
            } else {
                $query .= " order by orderBy desc {$limit}";
            }
        }

        $query2 = "SELECT FOUND_ROWS() as totalCount";
        $query = $this->db->query($query);
        $query2 = $this->db->query($query2);
        $result = $query->getResultArray();
        $res2 = $query2->getResultArray();
        return [$result, $res2];
    }

    /**
     * @return bool|Sessions
     */
    public function getSessions()
    {
        $query = "SELECT * from sessions where id=?";
        $result = $this->query($query, [$this->session]);
        if (!$result) {
            return false;
        }
        return new \App\Entities\Sessions($result[0]);
    }

    /**
     * @param mixed $rrrCode
     * @param mixed $channel
     * @param mixed $student
     * @return array<string,mixed>
     * @throws Exception
     */
    public function verify_transaction($rrrCode, $channel, $student = null): array
    {
        if ($channel == 'remita') {
            $remita = Factories::models('Remita');
            $transactionRef = $this->transaction_ref ?: null;
            if (!$transactionRef) {
                return ['status' => false, 'message' => 'Invalid transaction reference'];
            }
            $temp = $remita->getRemitaData($this->transaction_ref, $student, null, $transactionRef);

            // when $temp['curlStatus'] is false, the curl method is GET method
            if (!$temp['curlStatus']) {
                $extraData = $temp['extraData'];
                $response = $remita->remitaTransactionDetails($extraData['url'], $temp['header']);
                if (@$response[RemitaResponse::RRR] == $rrrCode && self::isPaymentValid($response['status'])) {
                    $date_payment_communicated = date('Y-m-d H:i:s');
                    // update transaction data
                    $record = array(
                        'payment_status' => $response['status'],
                        'beneficiary_1' => (isset($response['lineitems']) && array_key_exists('lineitems', $response)) ? $response['lineitems'][0]['status'] : '100',
                        'beneficiary_2' => (isset($response['lineitems']) && array_key_exists('lineitems', $response)) ? $response['lineitems'][1]['status'] : '100',
                        'beneficiary_3' => (isset($response['lineitems']) && array_key_exists('lineitems', $response)) ? $response['lineitems'][2]['status'] : '100',
                        'payment_status_description' => $response['message'],
                        'amount_paid' => $response['amount'],
                        'date_completed' => (isset($response['paymentDate'])) ? $response['paymentDate'] : $response['transactiontime'],
                        'date_payment_communicated' => $date_payment_communicated,
                    );
                    if (isset($response[RemitaResponse::RRR]) && $response[RemitaResponse::RRR] != '') {
                        $record['rrr_code'] = $response[RemitaResponse::RRR];
                    }
                    $id = $this->id;
                    $payment_id = $this->payment_id;
                    $studentID = $this->student_id;

                    if (($payment_id == PaymentFeeDescription::SCH_FEE_FIRST->value ||
                            $payment_id == PaymentFeeDescription::PART_FIRST_SCH_FEE->value) && $this->level == '1') {
                        $this->updatePutmeAcademicRecord($studentID);
                    }

                    if ($payment_id == PaymentFeeDescription::LAGOS_CENTRE_FIRST_ONLY_SEM->value) {
                        $this->updatePutmeAcademicRecord($studentID, 'exam_centre');
                    }

                    if ($description = $this->mapTopupToSchFee($payment_id)) {
                        $record['payment_id'] = $description;
                    }

                    $this->setArray($record);
                    if (!$this->update($id)) {
                        return ["status" => false, 'message' => "An error occurred while processing payment"];
                    }
                    $record['payment_id'] = $payment_id;
                    $record['student_id'] = $studentID;
                    $this->setArray($record);
                    return ['status' => true, 'rrr_code' => $rrrCode];
                } else {
                    $record = array(
                        'payment_status' => $response['status'] ?? '021',
                        'payment_status_description' => $response['message'] ?? 'pending',
                        'amount_paid' => $response['amount'] ?? null,
                        'date_completed' => $response['paymentDate'] ?? @$response['transactiontime'],
                    );
//					if (isset($response[RemitaResponse::RRR]) && $response[RemitaResponse::RRR] != '') {
//						$record['rrr_code'] = $response[RemitaResponse::RRR];
//					}
                    $id = $this->id;
                    $this->setArray($record);
                    if (!$this->update($id)) {
                        return ["status" => false, 'message' => "An error occurred while processing payment"];
                    }
                    return ['status' => false, 'message' => (isset($response['message']) && $response['message'] != '') ? 'Transaction status: ' . strtolower($response['message']) : 'Transaction status does not exist or pending payment'];
                }
            }
        }
        return ["status" => false, 'message' => "An error occurred while processing payment"];
    }

    public function updatePutmeAcademicRecord($student, $task = null): void
    {
        $academicRecord = fetchSingle($this->db, 'academic_record', 'student_id', $student);
        if ($academicRecord && $academicRecord['entry_mode'] === CommonSlug::O_LEVEL_PUTME->value) {
            update_record($this->db, 'academic_record', 'id', $academicRecord['id'],
                ['entry_mode' => CommonSlug::O_LEVEL->value]
            );
        }

        if ($academicRecord && $task == 'exam_centre') {
            update_record($this->db, 'academic_record', 'id', $academicRecord['id'],
                ['exam_center' => 'Lagos']
            );
        }
    }

    /**
     * This is to map topup to sch fee since it payment[description]
     * was not originally mapped to sch fee
     * @param  [type] $paymentId [description]
     * @return int|null [type]            [description]
     */
    public function mapTopupToSchFee($paymentId)
    {
        if ($paymentId == PaymentFeeDescription::OUTSTANDING_22->value) {
            return PaymentFeeDescription::SCH_FEE_SECOND->value;
        }

        if ($paymentId == PaymentFeeDescription::TOPUP_FEE_22->value) {
            return get_setting('active_semester') == 1 ? PaymentFeeDescription::SCH_FEE_FIRST->value : PaymentFeeDescription::SCH_FEE_SECOND->value;
        }

        if ($paymentId == PaymentFeeDescription::TOPUP_FEE_21->value) {
            return get_setting('active_semester') == 1 ? PaymentFeeDescription::SCH_FEE_FIRST->value : PaymentFeeDescription::SCH_FEE_SECOND->value;
        }

        // this is for first semester part payment
        if ($paymentId == PaymentFeeDescription::PART_FIRST_SCH_FEE->value) {
            return get_setting('active_semester') == 1 ? PaymentFeeDescription::SCH_FEE_FIRST->value : PaymentFeeDescription::SCH_FEE_SECOND->value;
        }

        // this is for second semester part payment
        if ($paymentId == PaymentFeeDescription::PART_SECOND_SCH_FEE->value) {
            return get_setting('active_semester') == 1 ? PaymentFeeDescription::SCH_FEE_FIRST->value : PaymentFeeDescription::SCH_FEE_SECOND->value;
        }
        return null;
    }

    /**
     * @return bool|<missing>
     */
    public function checkTransactionByRRR(string $code)
    {
        $query = "SELECT * FROM transaction where transaction.rrr_code=? and transaction.payment_status in ('00', '01') ";
        $result = $this->query($query, [$code]);
        if (!$result) {
            return false;
        }
        return $result[0];
    }

    /**
     * @param mixed $table
     * @param mixed $dateFrom
     * @param mixed $dateTo
     * @return bool|<missing>
     */
    public function getAllPendingTransaction($table, $dateFrom, $dateTo)
    {
        $query = "SELECT * from $table where (payment_status not in ('00','01') or payment_status = '') and 
            date_performed >= ? and date_performed <= ? order by date_performed asc";
        $dateFrom .= ' 00:00:00.000000';
        $dateTo .= ' 23:59:59.999999';

        $result = $this->db->query($query, [$dateFrom, $dateTo]);
        if ($result->getNumRows() <= 0) {
            return null;
        }
        return $result;
    }

    public function getLast12MonthTransaction()
    {
        $query = "SELECT label, sum(total) as total_amount from
                (
                    SELECT DATE_FORMAT(months.month, '%M') AS label,
                    COALESCE(sum(a.mainaccount_amount + a.subaccount_amount), 0) AS total,
                    YEAR(months.month) as year,
                    MONTH(months.month) as month_number
					FROM
					(
						SELECT CURDATE() - INTERVAL seq MONTH AS month FROM
						(SELECT 0 AS seq UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
                		 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11
                		 ) AS seq_table
					) AS months
					LEFT JOIN applicant_transaction a ON DATE_FORMAT(a.date_completed, '%Y-%m') = DATE_FORMAT(months.month, '%Y-%m')
					and a.payment_status in ('01','00')
					GROUP BY months.month

					UNION ALL
						SELECT DATE_FORMAT(months.month, '%M') AS label,
						COALESCE(sum(b.mainaccount_amount + b.subaccount_amount), 0) AS total,
						YEAR(months.month) as year,
                    	MONTH(months.month) as month_number
						FROM
						(
							SELECT CURDATE() - INTERVAL seq MONTH AS month FROM
							(SELECT 0 AS seq UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
							 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11
							 ) AS seq_table
						) AS months
						LEFT JOIN transaction b ON DATE_FORMAT(b.date_completed, '%Y-%m') = DATE_FORMAT(months.month, '%Y-%m')
						and b.payment_status in ('01','00')
						GROUP BY months.month

					UNION ALL
						SELECT DATE_FORMAT(months.month, '%M') AS label,
						COALESCE(sum(c.mainaccount_amount + c.subaccount_amount), 0) AS total,
						YEAR(months.month) as year,
                    	MONTH(months.month) as month_number
							FROM
							(
								SELECT CURDATE() - INTERVAL seq MONTH AS month FROM
								(SELECT 0 AS seq UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
								 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11
								 ) AS seq_table
							) AS months
							LEFT JOIN transaction_custom c ON DATE_FORMAT(c.date_completed, '%Y-%m') = DATE_FORMAT(months.month, '%Y-%m')
							and c.payment_status in ('01','00')
						GROUP BY months.month
                ) as combined group by label, year, month_number order by year asc, month_number asc
        ";
        $query = $this->db->query($query);
        $result = [];
        if ($query->getNumRows() <= 0) {
            return $result;
        }
        return $query->getResultArray();
    }

    public function getLast7DaysTransaction()
    {
        $query = "SELECT label, sum(total) as total_amount from
                (
                    SELECT DATE_FORMAT(days.day, '%d-%b') AS label,
                    COALESCE(sum(a.mainaccount_amount + a.subaccount_amount), 0) AS total,
                    days.day as day
					FROM
					(
						SELECT CURDATE() - INTERVAL seq DAY AS day FROM
						(SELECT 0 AS seq UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
                		 UNION ALL SELECT 6
                		 ) AS seq_table
					) AS days
					LEFT JOIN applicant_transaction a ON DATE_FORMAT(a.date_completed, '%Y-%m-%d') = DATE_FORMAT(days.day, '%Y-%m-%d')
					and a.payment_status in ('01','00')
					GROUP BY days.day

					UNION ALL
						SELECT DATE_FORMAT(days.day, '%d-%b') AS label,
						COALESCE(sum(b.mainaccount_amount + b.subaccount_amount), 0) AS total,
						days.day as day
						FROM
						(
							SELECT CURDATE() - INTERVAL seq DAY AS day FROM
							(SELECT 0 AS seq UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
							 UNION ALL SELECT 6
							 ) AS seq_table
						) AS days
						LEFT JOIN transaction b ON DATE_FORMAT(b.date_completed, '%Y-%m-%d') = DATE_FORMAT(days.day, '%Y-%m-%d')
						and b.payment_status in ('01','00')
						GROUP BY days.day

					UNION ALL
						SELECT DATE_FORMAT(days.day, '%d-%b') AS label,
						COALESCE(sum(c.mainaccount_amount + c.subaccount_amount), 0) AS total,
						days.day as day
							FROM
							(
								SELECT CURDATE() - INTERVAL seq DAY AS day FROM
								(SELECT 0 AS seq UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
								 UNION ALL SELECT 6
								 ) AS seq_table
							) AS days
							LEFT JOIN transaction_custom c ON DATE_FORMAT(c.date_completed, '%Y-%m-%d') = DATE_FORMAT(days.day, '%Y-%m-%d')
							and c.payment_status in ('01','00')
						GROUP BY days.day
                ) as combined group by label, day order by day asc
        ";
        $query = $this->db->query($query);
        $result = [];
        if ($query->getNumRows() <= 0) {
            return $result;
        }
        return $query->getResultArray();
    }

}
