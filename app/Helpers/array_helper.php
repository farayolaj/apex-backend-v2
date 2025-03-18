<?php
//this function checks if an array is sequential or not
//returns -1 if array is null 0 if not sequential and 1 if it is
function isSequential($array) {
	if (empty($array)) {
		return -1;
	}
	if (isset($array[0]) && isset($array[count($array) - 1])) {
		return 1;
	} else {
		return 0;
	}
}

//this function can get a range of element from a given array
//useful for asssociative array will work for non-associative but will be slower. user subArray instead.
function subArrayAssoc($array, $start, $len) {
	$count = count($array);
	$validity = $count - ($start + $len); //confirmed
	if ($validity < 0) {
		throw new \Exception("error occur validity=$validity count is $count", 1);

		return false;
	}
	//extract the array
	$keys = array_keys($array);
	$result = array();
	for ($i = $start; $i < ($start + $len); $i++) {
		$key = $keys[$i];
		$result[$key] = $array[$key];
	}
	return $result;
}

function loadChoices($scope, $table) {
	$oldTable = $table;
	$table = "App\\Entities\\" . $table;
	$displayField = isset($table::$displayField) ? $table::$displayField : false;
	$tableKey = getTableKey();
	if (!$displayField) {
		$displayField = 'name';
	}
	if ($oldTable == 'customer') {
		$displayField = " concat_ws(' ',firstname,middlename,lastname) ";
	}
	if (is_array($displayField)) {
		$tempAdd = implode(',', $displayField);
		$displayField = "concat_ws(' ',$tempAdd)";
	}
	$query = "select {$tableKey} as id,$displayField as value from $oldTable";
	$result = $scope->query($query);
	$result = $result->getResultArray();
	return $result;
}

function getStructure($scope, $entity) {
	$result = array();
	$entity = loadClass("$entity");
	$labels = $entity::$typeArray;
	$nullArray = $entity::$nullArray;
	$relation = $entity::$relation;
	$labelArray = $entity::$labelArray;
	$actions = $entity::$tableAction;
	$relationDictionary = getEntityDirectRelation($scope, $relation);
	foreach ($labels as $label => $value) {
		if ($label == 'ID' || $label == 'date_created' || $label == 'customer_order_id' || $label == 'delivery_id') {
			continue;
		}
		$param = array();

		if (!in_array($label, $nullArray)) {
			$param['required'] = 1;
		}
		$param['type'] = getFieldType($label, $value);
		$title = (array_key_exists($label, $labelArray) && trim($labelArray[$label])) ? $labelArray[$label] : $label;
		$param['label'] = $title;
		$param['relation'] = '';
		if (array_key_exists($label, $relationDictionary)) {
			$param['relation'] = $relationDictionary[$label];
			$param['choices'] = loadChoices($scope, $param['relation']);
		}
		// $param['relation']=$this->getAllDirectRelation($label,$relation);
		$param['value'] = '';
		// if ($param['type']=='select' && $param['relation']) {
		// 	# code...
		// }
		$result[$label] = $param;
	}
	return $result;
}

function getBulkUploadFields($scope, $entity) {
	$entity = loadClass("$entity");
	if (!property_exists($entity, 'bulkUploadField')) {
		return false;
	}
	return $entity::$bulkUploadField;
}

function getFieldType($label, $value) {
	if ($value == 'varchar' || $value == 'int') {
		if (endsWith($label, getForeignKeyAppend())) {
			return 'select';
		}
		if (strpos(strtolower($label), 'mail') !== false) {
			return 'email';
		}

		if (strpos(strtolower($label), 'phone') !== false) {
			return 'phone';
		}
		if (strpos(strtolower($label), 'date') !== false) {
			return 'date';
		}
		if (strpos(strtolower($label), 'path') !== false || strpos(strtolower($label), 'image') !== false || strpos(strtolower($label), 'file') !== false || strpos(strtolower($label), 'document') !== false) {
			return 'file';
		}
	}
	if ($value == 'timestamp') {
		return 'date';
	}
	if ($value == 'text') {
		return 'text';
	}

	return 'simple';
}

function getTableKey() {
	return 'ID';
}

function getForeignKeyAppend() {
	return '_id';
}
function getEntityDirectRelation($scope, $relations): array
{
	$result = array();
	$tableKey = getTableKey();
	foreach ($relations as $label => $relation) {
		$temp = @$relation[0];
		if ($temp && is_string($temp) && $temp != $tableKey) {
			$result[$temp] = $label;
		}
	}
	return $result;
}

//this function will return a subArray of an array(most useful if the array is not associative)
function subArray($array, $start, $len) {
	$count = count($array);
	echo "the count is $count";
	$validity = $count - ($start + $len);
	if ($validity < 0) {
		return false;
	}
	$result = array();
	for ($i = $start; $i < ($start + $len); $i++) {
		$result[] = $array[$i];
	}
	return $result;
}

function exception_error_handler($errno, $errstr, $errfile, $errline) {
	throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
}
function replaceIndexWith(&$array, $index, $value): void
{
	for ($i = 0; $i < count($array); $i++) {
		$array[$i][] = $value; // $array[$i][$index]=$value;
	}
}
//this function check if an element is empty in
function checkEmpty($array, $except = array()): bool|int|string
{
	if (empty($array)) {
		return false;
	}
	foreach ($array as $key => $value) {
		if (!in_array($value, $except) && empty($value)) {
			return $key;
		}
	}
	return false;
}

//function to get get the last insert index given the database object
function getLastInsert($db) {
	$query = 'select last_insert_id() as last';
	$result = $db->query($query);
	$result = $result->getResultArray();
	return $result[0]['last'];
}

function query($db, $query, $data = array()) {
	$result = $db->query($query, $data);
	if (is_bool($result)) {
		return $result;
	}
	return $result->getResultArray();
}
//function to help return to the previous page while setting error message

//function to load states
function loadStates(): array
{
	$list = scandir('assets/states');
	$result = array();
	//process the list well before they are returned
	helper('string');
	for ($i = 0; $i < count($list); $i++) {
		$current = $list[$i];
		if (startsWith($current, '.')) {
			continue;
		}
		$result[] = trim($current);
	}
	return $result;
}
function loadLga($state): array|string
{
	if (!file_exists("public/assets/states/$state")) {
		return '';
	}
	$content = file_get_contents("public/assets/states/$state");
	$content = trim($content);
	$result = explode("\n", $content);
	for ($i = 0; $i < count($result); $i++) {
		$result[$i] = trim($result[$i]);
	}
	return $result;
}

function removeDuplicateValues($array): array
{
	$result = array();
	foreach ($array as $value) {
		if (in_array($value, $result)) {
			continue;
		}
		$result[] = $value;
	}
	return $result;
}

function arrayToCsvString($array, $header = null): string
{
	$result = "";
	$key = $header == null ? array_keys($array[0]) : $header;
	array_unshift($array, $key);
	for ($i = 0; $i < count($array); $i++) {
		$current = $array[$i];
		$result .= singleRowToCsvString($current);
	}
	return $result;
}
function singleRowToCsvString($row): string
{
    $result = implode(',', $row);
	$result .= "\n";
	return $result;
}

//function to extract a section of columns from a two dimsntional array
function copyMultiArrayWithIndex($indexArray, $data): array
{
	$result = array();
	for ($i = 0; $i < count($data); $i++) {
		$current = $data[$i];
		$result[] = extractArrayPortion($indexArray, $current);
	}
	return $result;
}

function extractArrayPortion($index, $data) {
	if (max($index) >= count($data)) {
		# there will be an error just throw exception or exit
		exit('error occur while performing operation');
	}
	$result = array();
	for ($i = 0; $i < count($index); $i++) {
		$result[] = $data[$index[$i]];
	}
	return $result;
}

function convertToAssoc($data, $first, $second): array
{
	$result = array();
	for ($i = 0; $i < count($data); $i++) {
		$key = $data[$i][$first];
		$value = $data[$i][$second];
		$result[$key] = $value;
	}
	return $result;
}
function removeEmptyAssoc($arr): array
{
	$result = array();
	foreach ($arr as $key => $value) {
		if (trim($value) !== '') {
			$result[$key] = $value;
		}
	}
	return $result;
}
function removeEmptyArrayElement($arr): array
{
	$result = array();
	for ($i = 0; $i < count($arr); $i++) {
		if (trim($arr[$i]) == '') {
			continue;
		}
		$result[] = $arr[$i];
	}
	return $result;
}

//this function initialize n number of array with the
function initArray($size, $default): array
{
	$result = array();
	for ($i = 0; $i < $size; $i++) {
		$result[$i] = $default;
	}
	return $result;
}

function removeValue($arr, $val): array
{
	$result = array();
	foreach ($arr as $value) {
		if ($value == $val) {
			continue;
		}
		$result[] = $value;
	}
	return $result;
}
function array_values_recursive($array) {
	$arrayValues = array();
	foreach ($array as $value) {
		if (is_scalar($value) OR is_resource($value)) {
			$arrayValues[] = $value;
		} elseif (is_array($value)) {
			$arrayValues = array_merge($arrayValues, array_values_recursive($value));
		}
	}
	return $arrayValues;
}

function getFilterQueryFromDict($filterList, $alias = null): array
{
    $filterQuery = "";
    $filterValues = [];
    if ($filterList) {
        foreach ($filterList as $key => $value) {
            if ($filterQuery) {
                $filterQuery .= "AND";
            }
            $filterQuery .= ($alias) ? " {$alias}.{$key} = ? " : " $key = ? ";
            $filterValues[] = $value;
        }
    }
    return [$filterQuery, $filterValues];
}

function buildApiClause(?array $apiSelectClause = [], $alias = null): string
{
    $clause = ($alias) ? " {$alias}.* " : " * ";
    $selectQuery = "";
    if (isset($apiSelectClause) && $apiSelectClause) {
        foreach ($apiSelectClause as $key) {
            if ($selectQuery) {
                $selectQuery .= ",";
            }
            $selectQuery .= ($alias) ? " {$alias}.{$key} " : " $key ";
        }
        $clause = $selectQuery;
    }
    return "SQL_CALC_FOUND_ROWS " . $clause;
}

function getEntityFilterStructure(object $formConfig, object $db, string $entity): array
{
    $filters = $formConfig->getInsertConfig($entity);
    return getFilterContent($filters, $db);
}

/**
 * This would run the query for the preload filters
 * @param $db
 * @param string $query [description]
 * @return array [description]
 */
function getSelectItemFromQuery($db, string $query): array
{
    if (!$query) {
        return [];
    }
    $result = $db->query($query);
    return $result->getResultArray();
}

/**
 * [getFilterContent description]
 * @param array $filters [description]
 * @param object $db
 * @return array [type]          [description]
 */
function getFilterContent(array $filters, object $db): array
{
    $result = array();
    if (!$filters) {
        return $result;
    }
    if (!array_key_exists('filter', $filters)) {
        return $filters;
    }
    $mainFilter = $filters['filter'];
    $tempFilter = array();
    foreach ($mainFilter as $filterItem) {
        $temp = array();
        $temp['title'] = $filterItem['filter_display'] ? $filterItem['filter_display'] : $filterItem['filter_label'];
        $temp['name'] = $filterItem['filter_label'];
        if (array_key_exists('select_items', $filterItem)) {
            $temp['filter_item'] = $filterItem['select_items'];
        } else {
            $temp['filter_item'] = getSelectItemFromQuery($db, $filterItem['preload_query']);
        }
        $tempFilter[] = $temp;
    }
    if (array_key_exists('search', $filters)) {
        $result['search'] = $filters['search'];
    }
    $result['filters'] = $tempFilter;
    return $result;
}

function listAPIEntities($db): array
{
	// this is to exempt some table to be accessed from the mobile end
	$exemptions = array('user', 'role', 'admin', 'permission', 'password_otp', 'activity_log');
	$query = 'show tables';
	$dbResult = $db->query($query);
	$dbResult = $dbResult->getResultArray();
	$result = array();
	foreach ($dbResult as $res) {
		$temp = reset($res);
		if (in_array($temp, $exemptions)) {
			continue;
		}
		$result[] = $temp;
	}
	return $result;
}

function listEntities($db): array
{
	if (!$dbResult = cache('entity_api_tables')) {
        $dbResult = $db->listTables();
		// save into cache
		cache()->save('entity_api_tables', $dbResult, 3600); // save for 1hr
	}

	$result = [];
	foreach ($dbResult as $res) {
		$result[] = $res;
	}
	return $result;
}

function getAPIEntityTranslation(): array
{
	// this gets the mobile translation from the database
	return array(
		'reset_password' => 'requestPasswordReset',
	);
}

function getEntityTranslation(): array
{
	// this gets the web translation from the database
	return [
        'create_users' => 'users_custom',
        'transaction_invoice' => 'transaction_custom',
        'payment_description' => 'fee_description',
        'audit_logs' => 'users_log',
        'finance_transaction' => 'transaction',
        'finance_archive_transaction' => 'transaction_archive',
        'admission_payment' => 'applicant_payment',
        'admission_create' => 'admission',
        'banks' => 'bank_lists',
        'bank_to_user' => 'user_banks',
        'outflow_transaction' => 'transaction_outflow',
        'app_setting' => 'settings',
        'sundry_defer_finance' => 'sundry_finance_transaction',
        'finance_projects' => 'projects',
        'finance_project_task' => 'project_tasks',
        'invoice_requests' => 'user_requests',
        'staff_requests' => 'user_requests',
        'create_new_users' => 'staffs',
        'all_roles' => 'roles',
        'app_role' => 'roles',
        'app_role_permission' => 'roles_permission',
	];
}

if (!function_exists('useGenerators')) {
	function useGenerators(array $data = []): Generator
    {
		foreach ($data as $item) {
			yield $item;
		}
	}
}