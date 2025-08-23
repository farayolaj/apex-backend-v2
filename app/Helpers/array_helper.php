<?php

/**
 * @param array $rows  // your raw rows (course_code, actual_amount, user_id, etc.)
 * @param float|null $defaultLogisticsAmount // fallback allowance if not set per-user
 * @param array $logisticsPerUser // e.g. ['1' => 20000.00, '5' => 15000.00]
 * @return array       // grouped structure ready to flatten for Excel
 */
function aggregateForReport(array $rows, ?float $defaultLogisticsAmount = null, array $logisticsPerUser = []): array
{
    $withUser = [];   // key: user_id
    $noUser   = [];   // list of singletons

    foreach ($rows as $r) {
        $uid = trim((string)($r['user_id'] ?? ''));
        $label = (string)($r['course_short'] ?? $r['course_code'] ?? '');
        $actual = (float)($r['actual_amount'] ?? 0);
        $estimate = (float)($r['new_intended_regime_estimate'] ?? 0);
        $fullname = isset($r['fullname']) ? trim((string)$r['fullname']) : null;
        $dept = isset($r['department_name']) ? trim((string)$r['department_name']) : null;

        if ($uid !== '') {
            if (!isset($withUser[$uid])) {
                $withUser[$uid] = [
                    'user_id'    => $uid,
                    'fullname'   => $fullname,
                    'department' => $dept,
                    'items'      => [], // each: ['label' => string, 'amount' => float]
                ];
            }
            // keep first non-empty fullname/department encountered
            if (!$withUser[$uid]['fullname'] && $fullname)   $withUser[$uid]['fullname'] = $fullname;
            if (!$withUser[$uid]['department'] && $dept)     $withUser[$uid]['department'] = $dept;

            $withUser[$uid]['items'][] = [
                'label'  => $label,
                'amount' => $actual,
            ];
        } else {
            // no user_id → standalone row (no logistics)
            $noUser[] = [
                'user_id'    => null,
                'fullname'   => null,          // or $fullname if you want to surface it for non-users
                'department' => $dept ?: null, // keep if provided
                'items'      => [
                    ['label' => $label, 'amount' => $estimate],
                ],
            ];
        }
    }

    // Append exactly one logistics_allowance item to each user group (if configured)
    foreach ($withUser as $uid => &$bucket) {
        $allow = $logisticsPerUser[(string)$uid] ?? $defaultLogisticsAmount;
        if ($allow !== null) {
            $bucket['items'][] = [
                'label'  => 'Logistics Allowance',
                'amount' => (float)$allow,
            ];
        }
    }
    unset($bucket);

    // Compute totals & shape final rows
    $final = [];

    foreach ($withUser as $bucket) {
        $total = 0.0;
        $itemStrs = [];
        foreach ($bucket['items'] as $it) {
            $total += (float)$it['amount'];
            $itemStrs[] = $it['label'] . ' - ' . number_format((float)$it['amount'], 2, '.', ',');
        }
        $final[] = [
            'fullname'   => $bucket['fullname'] ?: '',
            'department' => $bucket['department'] ?: '',
            'items'      => $itemStrs,     // keep array if you like; join for sheet below
            'total'      => $total,
        ];
    }

    foreach ($noUser as $bucket) {
        $amt = (float)$bucket['items'][0]['amount'];
        $label = $bucket['items'][0]['label'];
        $final[] = [
            'fullname'   => '', // per requirement: only show fullname if user_id exists
            'department' => $bucket['department'] ?: '',
            'items'      => [ $label . ' - ' . number_format($amt, 2, '.', ',') ],
            'total'      => $amt,
        ];
    }

    return $final;
}

/**
 * Flatten for PhpSpreadsheet:
 * Columns: fullname | department | items | total
 * (items joined with " | ")
 */
function flattenForSheetReport(array $aggregated): array
{
    $rows = [];
    $rows[] = ['fullname', 'department', 'items', 'total'];

    foreach ($aggregated as $r) {
        $rows[] = [
            $r['fullname'],
            $r['department'],
            implode(' | ', $r['items']),
            $r['total'], // keep numeric for Excel formatting
        ];
    }
    return $rows;
}

if (!function_exists('isSequential')) {
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
}


if (!function_exists('subArrayAssoc')) {
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
}

if (!function_exists('loadChoices')) {
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
}

if (!function_exists('getStructure')) {
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
}

if (!function_exists('getBulkUploadFields')) {
    function getBulkUploadFields($scope, $entity) {
        $entity = loadClass("$entity");
        if (!property_exists($entity, 'bulkUploadField')) {
            return false;
        }
        return $entity::$bulkUploadField;
    }
}

if (!function_exists('getFieldType')) {
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
}

if (!function_exists('getTableKey')) {
    function getTableKey() {
        return 'id';
    }
}

if (!function_exists('getForeignKeyAppend')) {
    function getForeignKeyAppend() {
        return '_id';
    }
}

if (!function_exists('getEntityDirectRelation')) {
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
}

if (!function_exists('subArray')) {
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
}

if (!function_exists('exception_error_handler')) {
    function exception_error_handler($errno, $errstr, $errfile, $errline) {
        throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
    }
}

if (!function_exists('replaceIndexWith')) {
    function replaceIndexWith(&$array, $index, $value): void
    {
        for ($i = 0; $i < count($array); $i++) {
            $array[$i][] = $value; // $array[$i][$index]=$value;
        }
    }
}

if (!function_exists('checkEmpty')) {
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
}

if (!function_exists('getLastInsert')) {
    function getLastInsert($db) {
        $query = 'SELECT last_insert_id() as last';
        $result = $db->query($query);
        $result = $result->getResultArray();
        return $result[0]['last'];
    }
}

if (!function_exists('query')) {
    function query($db, $query, $data = array()) {
        $result = $db->query($query, $data);
        if (is_bool($result)) {
            return $result;
        }
        return $result->getResultArray();
    }
}

if (!function_exists('loadStates')) {
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
}

if (!function_exists('loadLga')) {
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
}

if (!function_exists('removeDuplicateValues')) {
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
}

if (!function_exists('arrayToCsvString')) {
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
}

if (!function_exists('singleRowToCsvString')) {
    function singleRowToCsvString($row): string
    {
        $result = implode(',', $row);
        $result .= "\n";
        return $result;
    }
}

if (!function_exists('copyMultiArrayWithIndex')) {
    function copyMultiArrayWithIndex($indexArray, $data): array
    {
        $result = array();
        for ($i = 0; $i < count($data); $i++) {
            $current = $data[$i];
            $result[] = extractArrayPortion($indexArray, $current);
        }
        return $result;
    }
}

if (!function_exists('extractArrayPortion')) {
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
}

if (!function_exists('convertToAssoc')) {
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
}

if (!function_exists('removeEmptyAssoc')) {
    function removeEmptyAssoc($arr): array
    {
        return array_filter($arr, function ($value) {
            return trim($value) !== '';
        });
    }
}

if (!function_exists('removeEmptyArrayElement')) {
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
}

if (!function_exists('uniqueMultidimensionalArray')) {
    function uniqueMultidimensionalArray(array $array, string $key): array
    {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach ($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[] = $val;
            }
            $i++;
        }
        return $temp_array;
    }
}

if (!function_exists('initArray')) {
    function initArray($size, $default): array
    {
        $result = array();
        for ($i = 0; $i < $size; $i++) {
            $result[$i] = $default;
        }
        return $result;
    }
}

if (!function_exists('removeValue')) {
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
}

if (!function_exists('flattenArrayOld')) {
    function flattenArrayOld($array): array
    {
        return array_values(array_merge_recursive(...$array));
    }
}

if (!function_exists('resetArrayIndex')) {
    function resetArrayIndex(array $array): array
    {
        return array_values($array);
    }
}

if (!function_exists('flattenArray')) {
    function flattenArray(array $array): array
    {
        $return_array = array();
        foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($array)) as $value) {
            $return_array[] = $value;
        }
        return $return_array;
    }
}

if (!function_exists('flattenArrayWithKey')) {
    function flattenArrayWithKey(array $array): array
    {
        $return_array = array();
        foreach (new RecursiveIteratorIterator(new RecursiveArrayIterator($array)) as $key => $value) {
            $return_array[$key] = $value;
        }
        return $return_array;
    }
}

if (!function_exists('array_values_recursive')) {
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
}

if (!function_exists('getDeniedApiMethods')) {
    function getDeniedApiMethods()
    {
        $result = array('user', 'customer_login', '');
    }
}

if (!function_exists('getLevelID')) {
    function getLevelID($val)
    {
        $options = array('1' => '100', '2' => '200', '3' => '300', '4' => '400', '5' => '500', '6' => '600', '7' => '700',
            '8' => '800', '9' => '900');
        foreach ($options as $key => $value) {
            if ($value == $val) {
                return $key;
            }
        }
        return false;
    }
}

if (!function_exists('getFilterQueryFromDict')) {
    function getFilterQueryFromDict(array $filterList, ?string $alias = null): array
    {
        if (empty($filterList)) {
            return ['', []];
        }

        $filterConditions = [];
        $filterValues = [];

        foreach ($filterList as $key => $value) {
            $field = $alias ? "{$alias}.{$key}" : $key;
            $filterConditions[] = "{$field} = ?";
            $filterValues[] = $value;
        }

        $filterQuery = implode(' AND ', $filterConditions);

        return [$filterQuery, $filterValues];
    }
}

if (!function_exists('buildApiClause')) {
    function buildApiClause(?array $apiSelectClause = [], ?string $alias = null, bool $sqlCalc = true): string
    {
        $clause = $alias ? "{$alias}.*" : "*";
        if (!empty($apiSelectClause)) {
            $fields = array_map(
                fn($key) => $alias ? "{$alias}.{$key}" : $key,
                $apiSelectClause
            );
            $clause = implode(', ', $fields);
        }
        return $sqlCalc ? "SQL_CALC_FOUND_ROWS {$clause}" : $clause;
    }
}

if (!function_exists('getEntityFilterStructure')) {
    function getEntityFilterStructure(object $formConfig, object $db, string $entity): array
    {
        $filters = $formConfig->getInsertConfig($entity);
        return getFilterContent($filters, $db);
    }
}

if (!function_exists('getSelectItemFromQuery')) {
    function getSelectItemFromQuery($db, string $query): array
    {
        if (!$query) {
            return [];
        }
        $result = $db->query($query);
        return $result->getResultArray();
    }
}

if (!function_exists('prefixArrayAndCommaSeparate')) {
    function prefixArrayAndCommaSeparate(array $data, string $prefix)
    {
        $prefixedFields = array_map(function ($item) use ($prefix) {
            return $prefix . $item;
        }, $data);

        return implode(',', $prefixedFields);
    }
}

if (!function_exists('getSessionValue')) {
    function getSessionValue(string $label): int
    {
        $result = [
            'session_2020_special' => 21,
            'session_22_special' => 34,
        ];
        return $result[$label];
    }
}

if (!function_exists('exemptAdmissionFromSession')) {
    function exemptAdmissionFromSession(string $slug): bool
    {
        if (strpos($slug, '2022-2023-admission-letter') !== false || strpos($slug, 'admission-letter') !== false
            || strpos($slug, '2020-2021-admission-letter-special-window') !== false
            || strpos($slug, '2021-2022-admission-letter') !== false) {
            return true;
        }
        return false;
    }
}

if (!function_exists('getFilterContent')) {
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
}

if (!function_exists('listAPIEntities')) {
    function listAPIEntities($db): array
    {
        $dbTablesCacheKey = 'apex_entity_api_tables';
        $exemptions = ['user'];

        if (!$dbResult = cache($dbTablesCacheKey)) {
            $dbResult = $db->listTables();
            // Save database tables into cache for 1 hour
            cache()->save($dbTablesCacheKey, $dbResult, 3600);
        }

        $result = array_filter($dbResult, function ($table) use ($exemptions) {
            return !in_array($table, $exemptions);
        });

        // Reindex the array to ensure sequential keys
        return array_values($result);
    }
}

if (!function_exists('listWebCustomEntities')) {
    function listWebCustomEntities(): array
    {
        return [
            'student_verification_fee',
            'student_transaction_change_programme',
            'admissions_programme_list',
            'sundry_finance_transaction',
            'staff_department',
            'student_pwd_list',
            'mandate_requests',
            'examination_courses',
            'examination_approval',
            'student_orientation_list',
        ];
    }
}

if (!function_exists('listEntities')) {
    function listEntities($db): array
    {
        $dbTablesCacheKey = 'apex_entity_web_tables';
        if (!$dbResult = cache($dbTablesCacheKey)) {
            $dbResult = $db->listTables();
            // Save database tables into cache for 1 hour
            cache()->save($dbTablesCacheKey, $dbResult, 3600);
        }

        $customEntities = listWebCustomEntities();
        return array_merge($dbResult, $customEntities);
    }
}

if (!function_exists('getAPIEntityTranslation')) {
    function getAPIEntityTranslation(): array
    {
        // this gets the translation from the database for API
        return array(
            'users' => 'admin',
            'request_change' => 'requestPasswordReset',
            'change_pass' => 'changePassword',
            'verification_documents' => 'verification_documents_requirement',
            'upload_verification_document' => 'student_verification_documents',

        );
    }
}

if (!function_exists('getEntityTranslation')) {
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
            'email_builder' => 'email_logs',
        ];
    }
}

if (!function_exists('useGenerators')) {
    function useGenerators(array $data = []): Generator
    {
        foreach ($data as $item) {
            yield $item;
        }
    }
}

if (!function_exists('removeRedundantArrayKey')) {
    function removeRedundantArrayKey(array $data, array $fieldsToRemove): ?array
    {
        return array_map(function ($item) use ($fieldsToRemove) {
            return array_diff_key($item, array_flip($fieldsToRemove));
        }, $data);
    }
}