<?php

namespace App\Entities;

use App\Enums\OutflowStatusEnum as OutflowStatus;
use App\Libraries\EntityLoader;
use App\Models\Crud;

/**
 * This class is automatically generated based on the structure of the table.
 * And it represent the model of the transaction_request table
 */
class Transaction_request extends Crud
{

    /**
     * This is the entity name equivalent to the table name
     * @var string
     */
    protected static $tablename = "Transaction_request";

    /**
     * This array contains the field that can be null
     * @var array
     */
    public static $nullArray = ['rrr_code', 'batch_ref', 'payment_status', 'payment_status_description', 'payment_status_message', 'deduction', 'withhold_tax', 'vat', 'stamp_duty', 'amount', 'total_amount', 'fee_amount', 'date_paid', 'updated_at'];

    /**
     * This are fields that must be unique across a row in a table.
     * Similar to composite primary key in sql(oracle,mysql)
     * @var array
     */
    public static $compositePrimaryKey = [];

    /**
     * This is to provided an array of fields that can be used for building a
     * template header for batch upload using csv format
     * @var array
     */
    public static $uploadDependency = [];

    /**
     * If there is a relationship between this table and another table, this display field properties is used as a column in the query.
     * A field in the other table that displays the connection between this name and this table's name,something along these lines
     * table_id. We cannot use a name similar to table id in the table that is displayed to the user, so the display field is used in
     * place of it. To ensure that the other model queries use that field name as a column to be fetched with the query rather than the
     * table id alone, the display field name provided must be a column in the table to replace the table id shown to the user.
     * @var array|string
     */
    public static $displayField = 'rrr_code';

    /**
     * This array contains the fields that are unique
     * @var array
     */
    public static $uniqueArray = [];

    /**
     * This is an associative array containing the fieldname and the datatype
     * of the field
     * @var array
     */
    public static $typeArray = ['request_type_id' => 'int', 'payment_description' => 'varchar', 'user_id' => 'int', 'session' => 'int', 'source_account_name' => 'varchar', 'source_account_number' => 'varchar', 'source_bank_code' => 'varchar', 'destination_account_name' => 'varchar', 'destination_account_number' => 'varchar', 'destination_bank_code' => 'varchar', 'transaction_ref' => 'varchar', 'rrr_code' => 'varchar', 'batch_ref' => 'varchar', 'payment_status' => 'varchar', 'payment_status_description' => 'varchar', 'payment_status_message' => 'varchar', 'deduction' => 'decimal', 'withhold_tax' => 'decimal', 'vat' => 'decimal', 'stamp_duty' => 'decimal', 'amount' => 'decimal', 'total_amount' => 'decimal', 'fee_amount' => 'decimal', 'date_paid' => 'datetime', 'created_at' => 'timestamp', 'updated_at' => 'timestamp'];

    /**
     * This is a dictionary that map a field name with the label name that
     * will be shown in a form
     * @var array
     */
    public static $labelArray = ['id' => '', 'request_type_id' => '', 'payment_description' => '', 'user_id' => '', 'session' => '', 'source_account_name' => '', 'source_account_number' => '', 'source_bank_code' => '', 'destination_account_name' => '', 'destination_account_number' => '', 'destination_bank_code' => '', 'transaction_ref' => '', 'rrr_code' => '', 'batch_ref' => '', 'payment_status' => '', 'payment_status_description' => '', 'payment_status_message' => '', 'deduction' => '', 'withhold_tax' => '', 'vat' => '', 'stamp_duty' => '', 'amount' => '', 'total_amount' => '', 'fee_amount' => '', 'date_paid' => '', 'created_at' => '', 'updated_at' => ''];

    /**
     * Associative array of fields in the table that have default value
     * @var array
     */
    public static $defaultArray = ['created_at' => 'current_timestamp()'];

    /**
     *  This is an array containing an associative array of field that should be regareded as document field.
     * it will contain the setting for max size and data type. Example: populate this array with fields that
     * are meant to be displayed as document in the format
     * array('fieldname'=>array('type'=>array('jpeg','jpg','png','gif'),'size'=>'1048576','directory'=>'directoryName/','preserve'=>false,'max_width'=>'1000','max_height'=>'500')).
     * the folder to save must represent a path from the basepath. it should be a relative path,preserve
     * filename will be either true or false. when true,the file will be uploaded with it default filename
     * else the system will pick the current user id in the session as the name of the file
     * @var array
     */
    public static $documentField = [];

    /**
     * This is an associative array of fields showing relationship between
     * entities
     * @var array
     */
    public static $relation = ['request_type' => array('request_type_id', 'id')
        , 'user' => array('user_id', 'id')
    ];

    /**
     * This are the action allowed to be performed on the entity and this can
     * be changed in the formConfig model file for flexibility
     * @var array
     */
    public static $tableAction = ['delete' => 'delete/transaction_request', 'edit' => 'edit/transaction_request'];

    public function __construct(array $array = [])
    {
        parent::__construct($array);
    }

    public function getRequest_type_idFormField($value = '')
    {
        $fk = null;
        //change the value of this variable to array('table'=>'request_type','display'=>'request_type_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'request_type_name' as value from 'request_type' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('request_type', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

        if (is_null($fk)) {
            return $result = "<input type='hidden' name='request_type_id' id='request_type_id' value='$value' class='form-control' />";
        }

        if (is_array($fk)) {

            $result = "<div class='form-group'>
		<label for='request_type_id'>Request Type</label>";
            $option = $this->loadOption($fk, $value);
            //load the value from the given table given the name of the table to load and the display field
            $result .= "<select name='request_type_id' id='request_type_id' class='form-control'>
					$option
				</select>";
            $result .= "</div>";
            return $result;
        }

    }

    public function getPayment_descriptionFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='payment_description'>Payment Description</label>
		<input type='text' name='payment_description' id='payment_description' value='$value' class='form-control' required />
	</div>";
    }

    public function getUser_idFormField($value = '')
    {
        $fk = null;
        //change the value of this variable to array('table'=>'user','display'=>'user_name'); if you want to preload the value from the database where the display key is the name of the field to use for display in the table.[i.e the display key is a column name in the table specify in that array it means select id,'user_name' as value from 'user' meaning the display name must be a column name in the table model].It is important to note that the table key can be in this format[array('table' => array('user', 'another table name'))] provided that their is a relationship between these tables. The value param in the function is set to true if the form model is used for editing or updating so that the option value can be selected by default;

        if (is_null($fk)) {
            return $result = "<input type='hidden' name='user_id' id='user_id' value='$value' class='form-control' />";
        }

        if (is_array($fk)) {

            $result = "<div class='form-group'>
		<label for='user_id'>User</label>";
            $option = $this->loadOption($fk, $value);
            //load the value from the given table given the name of the table to load and the display field
            $result .= "<select name='user_id' id='user_id' class='form-control'>
					$option
				</select>";
            $result .= "</div>";
            return $result;
        }

    }

    public function getSessionFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='session'>Session</label>
		<input type='text' name='session' id='session' value='$value' class='form-control' required />
	</div>";
    }

    public function getSource_account_nameFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='source_account_name'>Source Account Name</label>
		<input type='text' name='source_account_name' id='source_account_name' value='$value' class='form-control' required />
	</div>";
    }

    public function getSource_account_numberFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='source_account_number'>Source Account Number</label>
		<input type='text' name='source_account_number' id='source_account_number' value='$value' class='form-control' required />
	</div>";
    }

    public function getSource_bank_codeFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='source_bank_code'>Source Bank Code</label>
		<input type='text' name='source_bank_code' id='source_bank_code' value='$value' class='form-control' required />
	</div>";
    }

    public function getDestination_account_nameFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='destination_account_name'>Destination Account Name</label>
		<input type='text' name='destination_account_name' id='destination_account_name' value='$value' class='form-control' required />
	</div>";
    }

    public function getDestination_account_numberFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='destination_account_number'>Destination Account Number</label>
		<input type='text' name='destination_account_number' id='destination_account_number' value='$value' class='form-control' required />
	</div>";
    }

    public function getDestination_bank_codeFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='destination_bank_code'>Destination Bank Code</label>
		<input type='text' name='destination_bank_code' id='destination_bank_code' value='$value' class='form-control' required />
	</div>";
    }

    public function getTransaction_refFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='transaction_ref'>Transaction Ref</label>
		<input type='text' name='transaction_ref' id='transaction_ref' value='$value' class='form-control' required />
	</div>";
    }

    public function getRrr_codeFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='rrr_code'>Rrr Code</label>
		<input type='text' name='rrr_code' id='rrr_code' value='$value' class='form-control' required />
	</div>";
    }

    public function getBatch_refFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='batch_ref'>Batch Ref</label>
		<input type='text' name='batch_ref' id='batch_ref' value='$value' class='form-control' required />
	</div>";
    }

    public function getPayment_statusFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='payment_status'>Payment Status</label>
		<input type='text' name='payment_status' id='payment_status' value='$value' class='form-control' required />
	</div>";
    }

    public function getPayment_status_descriptionFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='payment_status_description'>Payment Status Description</label>
		<input type='text' name='payment_status_description' id='payment_status_description' value='$value' class='form-control' required />
	</div>";
    }

    public function getPayment_status_messageFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='payment_status_message'>Payment Status Message</label>
		<input type='text' name='payment_status_message' id='payment_status_message' value='$value' class='form-control' required />
	</div>";
    }

    public function getDeductionFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='deduction'>Deduction</label>
		<input type='text' name='deduction' id='deduction' value='$value' class='form-control' required />
	</div>";
    }

    public function getWithhold_taxFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='withhold_tax'>Withhold Tax</label>
		<input type='text' name='withhold_tax' id='withhold_tax' value='$value' class='form-control' required />
	</div>";
    }

    public function getVatFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='vat'>Vat</label>
		<input type='text' name='vat' id='vat' value='$value' class='form-control' required />
	</div>";
    }

    public function getStamp_dutyFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='stamp_duty'>Stamp Duty</label>
		<input type='text' name='stamp_duty' id='stamp_duty' value='$value' class='form-control' required />
	</div>";
    }

    public function getAmountFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='amount'>Amount</label>
		<input type='text' name='amount' id='amount' value='$value' class='form-control' required />
	</div>";
    }

    public function getTotal_amountFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='total_amount'>Total Amount</label>
		<input type='text' name='total_amount' id='total_amount' value='$value' class='form-control' required />
	</div>";
    }

    public function getFee_amountFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='fee_amount'>Fee Amount</label>
		<input type='text' name='fee_amount' id='fee_amount' value='$value' class='form-control' required />
	</div>";
    }

    public function getDate_paidFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='date_paid'>Date Paid</label>
		<input type='text' name='date_paid' id='date_paid' value='$value' class='form-control' required />
	</div>";
    }

    public function getCreated_atFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='created_at'>Created At</label>
		<input type='text' name='created_at' id='created_at' value='$value' class='form-control' required />
	</div>";
    }

    public function getUpdated_atFormField($value = '')
    {
        return "<div class='form-group'>
		<label for='updated_at'>Updated At</label>
		<input type='text' name='updated_at' id='updated_at' value='$value' class='form-control' required />
	</div>";
    }


    protected function getRequest_type()
    {
        $query = 'SELECT * FROM request_type WHERE id=?';
        if (!isset($this->array['request_type_id'])) {
            return null;
        }
        $id = $this->array['request_type_id'];
        $result = $this->query($query, [$id]);
        if (!$result) {
            return false;
        }
        return new \App\Entities\Request_type($result[0]);
    }

    public function getPendingFundTransaction()
    {
        $query = "SELECT batch_ref from transaction_request where payment_status = ? and payment_status_description <> ? 
        and (rrr_code is not null or rrr_code != '') group by batch_ref";
        return $this->query($query, ['00', OutflowStatus::SUCCESSFUL->value]);
    }

    public function getUserRequestByTransaction(string $batchRef)
    {
        EntityLoader::loadClass($this, 'user_requests');
        $query = "SELECT distinct a.id,a.request_no,a.title,a.user_id,a.request_id,a.amount,a.description,a.beneficiaries,
            a.deduction,a.withhold_tax,a.vat,a.stamp_duty,a.total_amount,a.request_status,a.project_task_id,a.feedback,
            a.date_approved,a.created_at,a.updated_at,a.action_timeline,a.stage,a.deduction_amount,a.retire_advance_doc,
            a.voucher_document,a.admon_reference from user_requests a join transaction_request b on b.user_request_id = a.id where 
            b.batch_ref = ?";
        $results = $this->query($query, [$batchRef]);
        $payload = [];
        if ($results) {
            foreach ($results as $result) {
                $result = $this->user_requests->loadExtras($result);
                $payload[] = $result;
            }
        }

        return $payload;
    }

    public function getLast12MonthTransaction()
    {
        $query = "SELECT DATE_FORMAT(months.month, '%M') AS label, 
			COALESCE(sum(a.total_amount), 0) AS total 
			FROM 
			(
				SELECT DATE_FORMAT(CURDATE() - INTERVAL seq MONTH, '%Y-%m-01') AS month FROM 
				(SELECT 0 AS seq UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 
				 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11
				 ) AS seq_table
			) AS months
			LEFT JOIN transaction_request a ON DATE_FORMAT(a.created_at, '%Y-%m') = DATE_FORMAT(months.month, '%Y-%m')
			and a.payment_status_description = ?
			GROUP BY months.month ORDER BY months.month ASC
		";
        $query = $this->db->query($query, [OutflowStatus::PENDING_CREDIT->value]);
        $result = [];
        if ($query->getNumRows() <= 0) {
            return $result;
        }
        return $query->getResultArray();
    }

    public function getLast7DaysTransaction()
    {
        $query = "SELECT DATE_FORMAT(days.day, '%d-%b') AS label, 
			COALESCE(sum(a.total_amount), 0) AS total 
			FROM 
			(
				SELECT CURDATE() - INTERVAL seq DAY AS day FROM 
				(SELECT 0 AS seq UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 
				 UNION ALL SELECT 6
				 ) AS seq_table
			) AS days
			LEFT JOIN transaction_request a ON DATE_FORMAT(a.created_at, '%Y-%m-%d') = DATE_FORMAT(days.day, '%Y-%m-%d')
			and a.payment_status_description = ?
			GROUP BY days.day ORDER BY days.day ASC
		";
        $query = $this->db->query($query, [OutflowStatus::PENDING_CREDIT->value]);
        $result = [];
        if ($query->getNumRows() <= 0) {
            return $result;
        }
        return $query->getResultArray();
    }


}

