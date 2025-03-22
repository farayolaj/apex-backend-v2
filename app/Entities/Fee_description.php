<?php
namespace App\Entities;

use App\Models\Crud;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the fee_description table.
 */
class Fee_description extends Crud
{
	protected static $tablename = 'Fee_description';
	/* this array contains the field that can be null*/
	static $nullArray = array();
	static $compositePrimaryKey = array();
	static $uploadDependency = array();
	/*this array contains the fields that are unique*/
	static $uniqueArray = array();
	/*this is an associative array containing the fieldname and the type of the field*/
	static $typeArray = array('description' => 'text', 'category' => 'varchar', 'code' => 'varchar', 'active' => 'tinyint', 'date_created' => 'datetime');
	/*this is a dictionary that map a field name with the label name that will be shown in a form*/
	static $labelArray = array('id' => '', 'description' => '', 'category' => '', 'code' => '', 'active' => '', 'date_created' => '');
	/*associative array of fields that have default value*/
	static $defaultArray = array();
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
	static $documentField = array();//array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

	static $relation = array();
	static $tableAction = array('delete' => 'delete/fee_description', 'edit' => 'edit/fee_description');
	static $apiSelectClause = ['id', 'description', 'code', 'category'];

	function __construct($array = array())
	{
		parent::__construct($array);
	}

	function getDescriptionFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='description' >Description</label>
<textarea id='description' name='description' class='form-control' required>$value</textarea>
</div> ";

	}

	function getCategoryFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='category' >Category</label>
		<input type='text' name='category' id='category' value='$value' class='form-control' required />
</div> ";

	}

	function getCodeFormField($value = '')
	{

		return "<div class='form-group'>
	<label for='code' >Code</label>
		<input type='text' name='code' id='code' value='$value' class='form-control' required />
</div> ";

	}

	function getActiveFormField($value = '')
	{

		return "<div class='form-group'>
	<label class='form-checkbox'>Active</label>
	<select class='form-control' id='active' name='active' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

	}

	function getDate_createdFormField($value = '')
	{

		return " ";

	}

	public function getPaymentDescription($payment, $all = false)
	{
		$query = "SELECT * from fee_description where id = ? and active = '1'";
		$result = $this->query($query, [$payment]);
		if (!$result) {
			return null;
		}
		return ($all) ? $result[0] : $result[0]['description'];
	}

	public function getPaymentFeeDescriptionByCode($code, $payment = null)
	{
		$query = "SELECT a.*,b.id as payment_id,b.amount,b.subaccount_amount,b.service_charge,b.discount_amount from 
            fee_description a left join payment b on b.description = a.id where code = ? and a.active = '1'";
		if ($payment) {
			$query .= " and b.id = '$payment' ";
		}
		$result = $this->query($query, [$code]);
		if (!$result) {
			return null;
		}
		return $result[0];
	}

	public function APIList($filterList, $queryString, $start, $len, $orderBy)
	{
		$selectData = static::$apiSelectClause;
		$temp = $this->apiQueryListFiltered($selectData, $filterList, $queryString, $start, $len, $orderBy);
		$res = $this->processList($temp[0]);
		return [$res, $temp[1]];
	}

	private function processList($items)
	{
		for ($i = 0; $i < count($items); $i++) {
			$items[$i] = $this->loadExtras($items[$i]);
		}
		return $items;
	}

	private function loadExtras($item)
	{
		if ($item['category']) {
			$item['category'] = feeCategoryType($item['category'], true);
		}

		return $item;
	}


}

