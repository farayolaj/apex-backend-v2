<?php

if (!function_exists('currentSession')) {
    function currentSession()
    {
        $db = db_connect();
        $session = $db->table('sessions')->getWhere(['status' => 1]);
        if ($session->getNumRows() == 0) {
            return null;
        }
        return $session->getRow();
    }
}

if (!function_exists('deleteFile')) {
    function deleteFile($filename): bool|string
    {
        if (file_exists($filename)) {
            $os = php_uname();
            if (stristr($os, "windows") !== FALSE) {
                return exec("del " . $filename);
            } else {
                return unlink($filename);
            }
        }
        return true;
    }
}

if (!function_exists('create_record')) {
    function create_record($db, $table_name, $data)
    {
        if ($db->table($table_name)->insert($data)) {
            return $db->insertID();
        } else {
            return false;
        }
    }
}

if (!function_exists('update_record')) {
    function update_record($db, $table_name, $where_field, $id, $data): bool
    {
        $builder = $db->table($table_name);
        $builder->where($where_field, $id);
        if ($builder->update($data)) {
            return true;
        } else {
            return false;
        }
    }
}

if(!function_exists('getSingleRecord')){
    function getSingleRecord(string $table, $where){
        $db = db_connect();
        $builder = $db->table($table)->where($where);
        $result = $builder->get();
        if($result->getNumRows() == 0){
            return null;
        }
        return $result->getRow();
    }
}

if (!function_exists('fetchSingle')) {
    function fetchSingle($db, $table, $column, $value)
    {
        $query = "SELECT * from $table where $column=?";
        $result = $db->query($query, array($value));
        $result = $result->getResultArray();
        if (!$result) {
            return null;
        }
        return $result[0];
    }
}

if (!function_exists('removeNonCharacter')) {
    function removeNonCharacter(string $string): string
    {
        $string = trim($string);
        return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $string);
    }
}

if (!function_exists('buildCustomWhereString')) {
    function buildCustomWhereString(?string $whereString, string $queryString = null, $orderBy = true): string
    {
        $filterQuery = $whereString ? " where {$whereString} " : "";
        if ($filterQuery || $queryString) {
            $queryString = ($filterQuery && $queryString) ? " and ({$queryString})" : "{$queryString}";
            $filterQuery .= ($filterQuery) ? "{$queryString}" : " where ({$queryString}) ";
        }

        if ($orderBy) {
            $filterQuery .= " order by id desc ";
        }
        return $filterQuery;
    }
}

if (!function_exists('buildCustomSearchString')) {
    function buildCustomSearchString(array $list, string $query): string
    {
        $result = '';
        $isFirst = true;
        $db = db_connect();
        foreach ($list as $value) {
            if (!$isFirst) {
                $result .= ' or ';
            }
            $isFirst = false;
            $result .= "$value like '%" . $db->escapeLikeString($query) . "%'";
        }
        return $result;
    }
}

/**
 * This load the setting values from the db
 * @param string $settings_name
 * @return  array
 */
if (!function_exists('get_setting')) {
    function get_setting(string $settings_name)
    {
        $db = db_connect();
        $query = $db->table('settings')->getWhere(array('setting_name' => $settings_name));
        $query = $query->getRow();
        return $query->setting_value;
    }
}

if (!function_exists('generateRandomRef')) {
    function generateRandomRef(string $prefix, $lastNumber = null): string
    {
        // If $lastNumber is not provided, generate the current date
        if ($lastNumber === null) {
            $lastNumber = date("ymd") . "0000";
        }
        $leadStr = $prefix;
        $currentDate = date("ymd");
        $lastDate = substr($lastNumber, strlen($leadStr), strlen($currentDate));

        if ($currentDate != $lastDate) {
            $lastNumber = $currentDate . "0000";
        }

        $datePart = date("ymd");
        // Get the last 4 digits
        $lastDigits = intval(substr($lastNumber, -4));
        $lastDigits += 2;

        // Ensure the last digit is even
        if ($lastDigits % 2 != 0) {
            $lastDigits += 1;
        }

        // Generate four odd digits, each incremented by 2
        return $leadStr . $datePart . str_pad(strval($lastDigits), 4, "0", STR_PAD_LEFT);
    }
}

if (!function_exists('getLastTableData')) {
    function getLastTableData(object $db, string $table, string $column, string $queryClause = null)
    {
        $query = "SELECT {$column} from {$table} {$queryClause} order by date_created desc limit 1";
        $result = $db->query($query)->getRow();
        return $result?->$column;
    }
}

if (!function_exists('sendApiResponse')) {
    function sendApiResponse(bool $status, string $message, $payload = null)
    {
        return displayJson($status, $message, $payload);
    }
}

if (!function_exists('ddd')) {
    function dddump($data)
    {
        print_r($data);
        exit;
    }
}

if (!function_exists('ddump')) {
    function ddump($data): void
    {
        echo $data;
    }
}

if (!function_exists('get_setting')) {
    function get_setting(string $settings_name): string
    {
        $db = db_connect();
        $builder = $db->table('settings');
        $query = $builder->getWhere(array('settings_name' => $settings_name));

        foreach ($query->getResultArray() as $row) {
            return $row['settings_value'];
        }
    }
}

if (!function_exists('getUserInfo')) {
    function getUserInfo(object $db, string $type, int $id)
    {
        $query = "SELECT * FROM user WHERE user_table_id=? and user_type='$type' ";
        $result = $db->query($query, [$id]);
        if (!$result) {
            return false;
        }
        return $result[0];
    }
}

/**
 *  This generate pair of random tokens called selector and validator
 * @return [type] [description]
 */
if (!function_exists('generate_me_tokens')) {
    function generate_me_tokens(): array
    {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));

        return [$selector, $validator, $selector . ':' . $validator];
    }
}

/**
 * function splits the token stored in the cookie into selector and validator
 * @param string $token [description]
 * @return [type]        [description]
 */
if (!function_exists('parse_me_token')) {
    function parse_me_token(string $token): ?array
    {
        $parts = explode(':', $token);

        if ($parts && count($parts) == 2) {
            return [$parts[0], $parts[1]];
        }
        return null;
    }
}

if (!function_exists('token_me_valid')) {
    function token_me_valid(string $token)
    {
        return parse_me_token($token);
    }
}

if (!function_exists('getUserRealInfo')) {
    function getUserRealInfo($user_id): ?string
    {
        if (!$user_id) {
            return '';
        }

        $user = loadClass('user');
        $user = $user->getRealUserData($user_id);
        if (!$user) {
            return null;
        }
        return "{$user['fullname']} ({$user['user_type']})";
    }
}

if (!function_exists('convertImageToBase64')) {
    function convertImageToBase64($path, $extension): string
    {
        // get the file data
        $img_data = file_get_contents($path);
        // get base64 encoded code of the image
        $base64_code = base64_encode($img_data);
        // create base64 string of image
        $base64_str = 'data:image/' . $extension . ';base64,' . $base64_code;
        return $base64_str;

    }
}

if (!function_exists('generateNumericRef')) {
    function generateNumericRef(object $db, string $table, string $dbColumn, string $prefix = 'REF'): string
    {
        $orderStart = '10000011';
        $query = "select {$dbColumn} as code from {$table} order by ID desc limit 1";
        $result = $db->query($query);
        if ($result->getNumRows() > 0) {
            $result = $result->getResultArray()[0];
            $explode = explode($prefix, $result['code']);
            if (!empty($explode) && count($explode) >= 2) {
                [$label, $temp] = $explode;
                $orderStart = ($temp) ? $temp + 1 : $orderStart;
            }
        }
        return $prefix . $orderStart;
    }
}

function getMacAddress()
{
    return getMacAddr1() ?? getMacAddr2() ?? null;
}

function getMacAddr1()
{
    ob_start();
    system('getmac');
    $Content = ob_get_contents();
    ob_clean();
    return substr($Content, strpos($Content, '\\') - 20, 17);
}

function getMacAddr2()
{
    ob_start(); // Turn on output buffering
    system('ipconfig /all'); //Execute external program to display output
    $mycom = ob_get_contents(); // Capture the output into a variable
    ob_clean(); // Clean (erase) the output buffer

    $findme = "Physical";
    $pmac = strpos($mycom, $findme); // Find the position of Physical text
    $mac = substr($mycom, ($pmac + 36), 17); // Get Physical Address
}

if (!function_exists('toUserAgent')) {
    function toUserAgent(object $agent)
    {
        if ($agent->isBrowser()) {
            $currentAgent = $agent->getBrowser() . ' ' . $agent->getVersion();
        } elseif ($agent->isRobot()) {
            $currentAgent = $agent->getRobot();
        } elseif ($agent->isMobile()) {
            $currentAgent = $agent->getMobile();
        } elseif ($agent->getAgentString() != '') {
            $currentAgent = $agent->getAgentString();
        } else {
            $currentAgent = 'Unidentified User Agent';
        }
        return $currentAgent;
    }
}

/**
 * [formatToUTC description]
 * @param string|null $date [description]
 * @param null $timezone
 * @param bool $isTime
 * @return string [type]            [description]
 * @throws Exception
 */
function formatToUTC(string $date = null, $timezone = null, bool $isTime = false)
{
    $date = $date ?? "now";
    $date = new \CodeIgniter\I18n\Time($date, $timezone);
    if (!$isTime) {
        return $date->format('Y-m-d H:i:s');
    } else {
        return $date->toTimeString();
    }
}

function discountOffPriceAmount($discPercentage, $sellPrice, &$discountAmount): float
{
    if (!is_decimal($discPercentage)) {
        $discPercentage = $discPercentage / 100;
    }

    $discountAmount = round($sellPrice * $discPercentage, 2);
    return $sellPrice - $discountAmount;
}

function is_decimal($val)
{
    return is_numeric($val) && floor($val) != $val;
}

function removeUnderscore($fieldname): string
{
    $result = '';
    if (empty($fieldname)) {
        return $result;
    }
    $list = explode("_", $fieldname);

    for ($i = 0; $i < count($list); $i++) {
        $current = ucfirst($list[$i]);
        $result .= $i == 0 ? $current : " $current";
    }
    return $result;
}

function uniqueString($size = 20): string
{
    $bytes = random_bytes($size);
    return bin2hex($bytes);
}

function withdrawalStringRef($size = 17): string
{
    return bin2hex(randStrGen($size));
}

function generateReceipt(): string
{
    $rand = mt_rand(0x000000, 0xffffff); // generate a random number between 0 and 0xffffff
    $rand = dechex($rand & 0xffffff); // make sure we're not over 0xffffff, which shouldn't happen anyway
    $rand = str_pad($rand, 6, '0', STR_PAD_LEFT); // add zeroes in front of the generated string
    $code = date('Y') . "" . $rand;
    return strtoupper($code);
}

//this function returns the json encoded string based on the key pair paremter saved on it.
function createJsonMessage()
{
    $argNum = func_num_args();
    if ($argNum % 2 != 0) {
        throw new Exception('argument must be a key-pair and therefore argument length must be even');
    }
    $argument = func_get_args();
    $result = array();
    for ($i = 0; $i < count($argument); $i += 2) {
        $key = $argument[$i];
        $value = $argument[$i + 1];
        $result[$key] = $value;
    }
    return json_encode($result);
}

//the function to get the currently logged on use from the sessions
/**
 * check that non of the given parameter is empty
 * @return boolean [description]
 */
function isNotEmpty()
{
    $args = func_get_args();
    for ($i = 0; $i < count($args); $i++) {
        if (empty($args[$i])) {
            return false;
        }
    }
    return true;
}

//function to build csv file into a multidimensional array
function stringToCsv($string): array
{
    $result = array();
    $lines = explode("\n", trim($string));
    for ($i = 0; $i < count($lines); $i++) {
        $current = $lines[$i];
        $result[] = explode(',', trim($current));
    }
    return $result;
}

function array2csv($array, $header = false): string
{
    $content = '';
    if ($array) {
        $content = strtoupper(implode(',', $header ? $header : array_keys($array[0]))) . "\n";
    }
    foreach ($array as $value) {
        $content .= implode(',', $value) . "\n";
    }
    return $content;
}

function endsWith($string, $end): bool
{
    $temp = substr($string, strlen($string) - strlen($end));
    return $end == $temp;
}

function verifyPassword($password): bool
{
    $minLength = 8;
    $numPattern = "/[0-9]/";
    $upperCasePattern = "/[A-Z]/";
    $lowerCasePattern = '/[a-z]/';
    return preg_match($numPattern, $password) && preg_match($upperCasePattern, $password) && preg_match($lowerCasePattern, $password) && strlen($password) >= $minLength;
}

function makeHash($string, $salt = ''): string
{
    return hash('sha256', $string . $salt);
}

function encode_password($password): string
{
    return password_hash($password, PASSWORD_BCRYPT, array(
        'cost' => 10,
    ));
}

function decode_password($userData, $fromDb): bool
{
    if ($userData != NULL) {
        return password_verify($userData, $fromDb);
    }
    return false;
}

function unique()
{
    return makeHash(uniqid());
}

function isValidEmail($string): bool
{
    if (!filter_var($string, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    return true;
}

function getFirstString($str, $uppercase = false): bool|string
{
    if ($str) {
        $value = substr($str, 0, 1);
        return ($uppercase) ? strtoupper($value) : strtolower($value);
    }
    return false;
}

function formatToNameLabel($string, $uppercase = false): bool|string
{
    if (!$string) {
        return '';
    }

    $splitName = explode(' ', $string);
    if (count($splitName) < 2) {
        return getFirstString($string, $uppercase);
    } else {
        $firstname = $splitName[0];
        $lastname = $splitName[1];
        return getFirstString($firstname, $uppercase) . '' . getFirstString($lastname, $uppercase);
    }
}

function isUniquePhone(object $scope, $phone, string $userType): bool
{
    $query = "select * from $userType where phone_number=?";
    $result = $scope->query($query, array($phone));
    $result = $result->getResultArray();
    return count($result) == 0;
}

function isUniqueEmail(object $scope, $phone, string $userType): bool
{
    $query = "select * from $userType where email=?";
    $result = $scope->query($query, array($phone));
    $result = $result->getResultArray();
    return count($result) == 0;
}

function isUniqueColumn(object $scope, string $table, string $column, $data): bool
{
    $query = "select * from $table where $column=?";
    $result = $scope->query($query, array($data));
    $result = $result->getResultArray();
    return count($result) == 0;
}

function getPasswordOTP(object $scope)
{
    $result = '';
    do {
        $result = random_int(100000, 999999);
    } while (!isValidPasswordOTP($scope, $result));
    return $result;
}

function isValidPasswordOTP($scope, $otp)
{
    $query = "select * from password_otp where otp=? and status=1";
    $result = $scope->query($query, array($otp));
    $result = $result->getResultArray();
    return !$result;
}

function isValidState($state): bool
{
    $state = strtolower($state);
    $states = array('abia', 'adamawa', 'akwa ibom', 'awka', 'bauchi', 'bayelsa', 'benue', 'borno', 'cross river', 'delta',
        'ebonyi', 'edo', 'ekiti', 'enugu', 'gombe', 'imo', 'jigawa', 'kaduna', 'kano', 'katsina', 'kebbi', 'kogi', 'kwara',
        'lagos', 'nasarawa', 'niger', 'ogun', 'ondo', 'osun', 'oyo', 'plateau', 'rivers', 'sokoto', 'taraba', 'yobe', 'zamfara');
    return in_array($state, $states);
}

function formatToNgPhone($phone): array|string|null
{
    // note: making sure we have something
    if (!isset($phone)) {
        return '';
    }
    // note: strip out everything but numbers
    $phone = preg_replace("/[^0-9]/", "", $phone);
    $length = strlen($phone);
    switch ($length) {
        case 7:
            return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
            break;
        case 10:
            return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
            break;
        case 11:
            return preg_replace("/([0-9]{1})([0-9]{3})([0-9]{3})([0-9]{4})/", "+234$2$3$4", $phone);
            break;
        default:
            return "+" . $phone;
            break;
    }
}

function cleanPhone($phone)
{
    $justNums = preg_replace("/[^0-9]/", '', $phone);
    if (strlen($justNums) == 13) {
        $justNums = preg_replace("/^234/", '0', $justNums);
    }

    return $justNums;
}

function isValidPhone($phone): bool
{
    $justNums = preg_replace("/[^0-9]/", '', $phone);
    if (strlen($justNums) == 13) {
        $justNums = preg_replace("/^234/", '0', $justNums);
    }

    //if we have 10 digits left, it's probably valid.
    return strlen($justNums) == 11;
}

function getLastInsertId($db)
{
    $query = "SELECT LAST_INSERT_ID() AS last"; //sud specify the table
    $result = $db->query($query);
    $result = $result->getResultArray();
    return $result[0]['last'];
}

//function migrated from  crud.php
function extractDbField($dbType)
{
    $index = strpos($dbType, '(');
    if ($index) {
        return substr($dbType, 0, $index);
    }
    return $dbType;
}

function extractDbTypeLength($dbType): string
{
    $index = strpos($dbType, '(');
    if ($index) {
        $len = strlen($dbType) - ($index + 2);
        return substr($dbType, $index + 1, $len);
    }
    return '';
}

function getPhpType($dbType): string
{
    $type = array('varchar' => 'string', 'text' => 'string', 'int' => 'integer', 'year' => 'integer', 'real' => 'double',
        'float' => 'float', 'double' => 'double', 'timestamp' => 'date', 'date' => 'date', 'datetime' => 'date',
        'time' => 'time', 'varbinary' => 'byte_array', 'blob' => 'byte_array', 'boolean' => 'boolean',
        'tinyint' => 'boolean', 'bit' => 'boolean');
    $dbType = extractDbField($dbType);
    $dbType = strtolower($dbType);
    return $type[$dbType];
}

//function to build select option from array object with id and value key
function buildOption($array, $val = '', $defaultValue = ''): string
{
    if (empty($array)) {
        return '';
    }
    $result = '';
    if ($defaultValue) {
        $result = "<option>$defaultValue</option>";
    }

    for ($i = 0; $i < count($array); $i++) {
        $current = $array[$i];
        $id = $current['id'];
        $value = $current['value'];
        $selected = $val == $id ? "selected='selected'" : '';
        $result .= "<option value='$id' $selected>$value</option> \n";
    }
    return $result;
}

function getRoleIdByName($db, $name)
{
    $query = "select id from role where role_name=?";
    $result = $db->query($query, array($name));
    $result = $result->getResultArray();
    return $result[0]['id'];
}

function buildOptionFromQuery($db, $query, $data = null, $val = '', $defaultValue = ''): string
{
    $result = $db->query($query, $data);
    if ($result->getNumRows() == 0) {
        return '';
    }
    $result = $result->getResultArray();
    return buildOption($result, $val, $defaultValue);
}

//function to build select option from an array of numerical keys
function buildOptionUnassoc($array, $val = ''): string
{
    if (empty($array) || !is_array($array)) {
        return '';
    }
    $val = trim($val);
    $result = '';
    foreach ($array as $key => $value) {
        $current = trim($value);
        $selected = $val == $current ? "selected='selected'" : '';
        $result .= "<option $selected >$current</option>";
    }

    return $result;
}

function buildOptionUnassoc2($array, $val = '', $defaultValue = ''): string
{
    if (empty($array) || !is_array($array)) {
        return '';
    }
    $val = trim($val);
    $optionValue = ($defaultValue != '') ? "$defaultValue" : "---choose option---";
    $result = "<option>$optionValue</option>";
    foreach ($array as $key => $value) {
        $current = trim($key);
        $selected = $val == $current ? "selected='selected'" : '';
        $result .= "<option value='$current' $selected >" . ucfirst($value) . "</option>";
    }
    return $result;
}

//function to tell if a string start with another string
function startsWith($str, $sub): bool|string
{
    if (!$str) {
        return '';
    }

    $len = strlen($sub);
    $temp = substr($str, 0, $len);
    return $temp === $sub;
}

function getMediaType($path, $arr = false): array|string|null
{
    $config = config('Mimes');
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    $type = $config::guessTypeFromExtension($ext);

    if ($type) {
        $media = explode('/', $type);
        return ($arr) ? $media : $media[0];
    }
    return null;
}

function createSymlink(string $link, string $target): string
{
    $link = "uploads/{$link}";
    if (!is_link($link)) {
        symlink($target, $link);
    }
    return $link;
}

function removeModelImage(object $db, string $modelName, string $fieldName, int $id): bool
{
    $result = $db->table($modelName)->getWhere([$fieldName => $id]);
    if ($result->getNumRows() > 0) {
        $modelPath = $modelName . "_path";
        $result = $result->getResultArray()[0][$modelPath];
        removeSymlinkWithImage($result);
        return true;
    }
    return false;
}

function removeSymlinkWithImage(string $image): void
{
    if (startsWith($image, base_url())) {
        $image = str_replace(base_url(), '', $image);
    }
    $image = ltrim($image, "/"); # remove image from the public directory
    if (file_exists($image)) {
        @chmod($image, 0777);
        @unlink($image);
    }
    $image = ROOTPATH . ltrim("writable/" . $image); # remove image from the writable directory
    if (file_exists($image)) {
        @chmod($image, 0777);
        @unlink($image);
    }
}

function showUploadErrorMessage($webSessionManager, $message, $isSuccess = true, $ajax = false)
{
    if ($ajax) {
        echo $message;
        exit;
    }
    $referer = $_SERVER['HTTP_REFERER'];
    $base = base_url();
    if (startsWith($referer, $base)) {
        $webSessionManager->setFlashMessage('flash_status', $isSuccess);
        $webSessionManager->setFlashMessage('message', $message);
        header("location:$referer");
        exit;
    }
    echo $message;
    exit;
}

function loadClass($classname, $namespace = null)
{
    if (!class_exists($classname)) {
        $modelName = is_null($namespace) ? "App\\Entities\\" . ucfirst($classname) : $namespace . "\\" . ucfirst($classname);
        return new $modelName;
    }
}

// function to get date difference
function getDateDifference($first, $second)
{
    $interval = date_diff(date_create($first), date_create($second));
    return $interval;
}

//function to get is first function is greater than the second
function isDateGreater($first, $second)
{
    $interval = getDateDifference($first, $second);
    return $interval->invert;
}

// function to send download request of a file to the browser
function sendDownload($content, $header, $filename)
{
    $content = trim($content);
    $header = trim($header);
    $filename = trim($filename);
    header("Content-Type:$header");
    header("Content-disposition: attachment;filename=$filename");
    echo $content;
    exit;
}

function padNumber($n, $value)
{
    $value = '' + $value; //convert the type to string
    $prevLen = strlen($value);
    // if ($prevLen > $n) {
    // 	throw new Exception("Error occur while processing");

    // }
    $num = $n - $prevLen;
    for ($i = 0; $i < $num; $i++) {
        $value = '0' . $value;
    }
    return $value;
}

function getFileExtension($filename)
{
    if (!$filename) {
        return '';
    }

    $index = strripos($filename, '.', 0); //start from the back
    if ($index === -1) {
        return '';
    }
    return substr($filename, $index + 1);
}

//function to determine if a string is a file path
function isFilePath($str)
{
    $recognisedExtension = array('doc', 'docx', 'pdf', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'csv');
    $extension = getFileExtension($str);
    return (startsWith($str, 'uploads') && strpos($str, '/') && in_array($extension, $recognisedExtension));
}

//function to pad a string by a number of zeros
function padwithZeros($str, $len)
{
    $str .= '';
    $count = $len - strlen($str);
    for ($i = 0; $i < $count; $i++) {
        $str = '0' . $str;
    }
    return $str;
}

function generatePassword()
{
    return randStrGen(10);
}

function randStrGen($len)
{
    $result = "";
    $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
    $charArray = str_split($chars);
    for ($i = 0; $i < $len; $i++) {
        $randItem = array_rand($charArray);
        $ra = mt_rand(0, 10);
        $result .= "" . $ra > 5 ? $charArray[$randItem] : strtoupper($charArray[$randItem]);
    }
    return $result;
}

//function to get the recent page cookie information
function getPageCookie()
{
    $result = array();
    if (isset($_COOKIE['nairaboom'])) {
        $content = $_COOKIE['nairaboom'];
        $result = explode('-', $content);
    }
    return $result;
}

//function to save the page cookie
function sendPageCookie($module, $page)
{
    $content = $module . '-' . $page;
    setcookie('nairaboom', $content, 0, '/', '', false, true);
}

function show_access_denied()
{
    $viewName = "App\\Views\\access_denied";
    return view($viewName);
}

function show_operation_denied()
{
    $viewName = "App\\Views\\operation_denied";
    view($viewName);
}

//function to replace the first occurrence of a string
function replaceFirst($toReplace, $replacement, $string)
{
    $pos = stripos($string, $toReplace);
    if ($pos === false) {
        return $string;
    }
    $len = strlen($toReplace);
    return substr_replace($string, $replacement, $pos, $len);
}

function displayJson(bool $status, string $message, $payload = null, $return = false)
{
    $param = array(
        'status' => $status,
        'message' => $message,
        'payload' => $payload,
    );
    $result = json_encode($param);
    if ($return) {
        return $result;
    }
    echo $result;
}

function formatToLocalCurrency($value = null)
{
    return "&#8358;$value"; // this is a naira currency
}

if (!function_exists('attrToString')) {
    function attrToString($attributes = array())
    {
        if (is_array($attributes)) {
            $atts = '';
            foreach ($attributes as $key => $val) {
                $atts .= ' ' . $key . '="' . $val . '"';
            }

            return $atts;
        }
    }
}

if (!function_exists('attrToSepString')) {
    function attrToSepString($attributes = array(), string $sep = ',')
    {
        if (is_array($attributes)) {
            $atts = '';
            foreach ($attributes as $key => $val) {
                $atts .= ($atts) ? "{$sep}" : '';
                $atts .= ' ' . $key . ' ' . $val;
            }

            return $atts;
        }
    }
}

function getCustomerOption($value = '')
{
    $customer = loadClass('customer');
    return $customer->getCustomerOption($value);
}

function getUserOption($value = '')
{
    $user = loadClass('user');
    return $user->getUserIdOption($value);
}

function getTitlePage($page = '')
{
    $formatted = " $page | Nairaboom ";
    return ($page != '') ? " $formatted " : " Nairaboom";
}

function getIDByName($scope, $table, $column, $value)
{
    $query = "select ID from $table where $column=?";
    $result = $scope->query($query, [$value]);
    $result = $result->getResultArray();
    if (!$result) {
        return false;
    }
    return $result[0]['ID'];
}

function rndEncode($data, $len = 16)
{
    return urlencode(base64_encode(randStrGen($len) . $data));
}

function rndDecode($data, $len = 16)
{
    $hash = base64_decode(urldecode($data));
    return substr($hash, $len);
}

function refEncode($data = 29)
{
    // the ref code should not be more than 30 characters
    return randStrGen($data);
}

function generateHashRef($type, $max = 17)
{
    $hash = randStrGen(8) . randStrGen(10) . date("s"); //  the total should be 20 in character
    $ref = randStrGen($max);
    $result = array('receipt' => $hash, 'reference' => $ref);
    return $result[$type];
}

/**
 * This is to generate random character on a model
 * @param string $table [description]
 * @param string $column [description]
 * @return [type]         [description]
 */
function generateNumber(object $db, string $table, string $column)
{
    $orderStart = '1000000011';
    $query = "select max($column) as model_hash from $table";
    $result = $db->query($query);
    if ($result->getNumRows() > 0) {
        $result = $result->getResultArray()[0];
        $temp = $result['model_hash'];
        return ($temp) ? $temp + 1 : $orderStart;
    } else {
        return $orderStart;
    }
}

function formatToDateOnly($dateTime)
{
    $date = new DateTime($dateTime);
    return $date->format('Y-m-d');
}

function isTimePassed($start, $end, $limit = 30): bool
{
    $expiration = "+$limit minutes";
    $expTime = strtotime($expiration, $end);
    if ($start <= $expTime) {
        return false; // means the first is less than the second
    }
    return true;
}

function dateFormatter($posted): ?string
{
    if ($posted) {
        return date_format(date_create($posted), "d F Y");
    }
    return null;
}

function dateTimeFormatter($posted, $hourFormat = 24): ?string
{
    if ($posted) {
        $date = date_format(date_create($posted), "d F Y");
        return $date . ', ' . localTimeRead($posted, $hourFormat);
    }
    return null;
}

function formatDate(): string
{
    $d = new DateTime();
    return $d->format("Y-m-d H:i:s");
}

function calc_size($file_size): ?string
{
    $_size = '';
    $kb = 1024;
    $mb = 1048576;
    $gb = 1073741824;

    if (empty($file_size)) {
        return '';
    } else if ($file_size < $kb) {
        return $_size . "B";
    } elseif ($file_size > $kb and $file_size < $mb) {
        $_size = round($file_size / $kb, 2);
        return $_size . "KB";
    } elseif ($file_size >= $mb and $file_size < $gb) {
        $_size = round($file_size / $mb, 2);
        return $_size . "MB";
    } else if ($file_size >= $gb) {
        $_size = round($file_size / $gb, 2);
        return $_size . "GB";
    } else {
        return NULL;
    }
}

// '%y Year %m Month %d Day %h Hours %i Minute %s Seconds'        =>  1 Year 3 Month 14 Day 11 Hours 49 Minute 36 Seconds
// '%y Year %m Month %d Day'                                    =>  1 Year 3 Month 14 Days
// '%m Month %d Day'                                            =>  3 Month 14 Day
// '%d Day %h Hours'                                            =>  14 Day 11 Hours
// '%d Day'                                                        =>  14 Days
// '%h Hours %i Minute %s Seconds'                                =>  11 Hours 49 Minute 36 Seconds
// '%i Minute %s Seconds'                                        =>  49 Minute 36 Seconds
// '%h Hours                                                    =>  11 Hours
// '%a Days                                                        =>  468 Days
function dateDiffFormat($date_1, $date_2, $differenceFormat = '%a')
{
    $datetime1 = date_create($date_1);
    $datetime2 = date_create($date_2);
    $interval = date_diff($datetime1, $datetime2);
    return $interval->format($differenceFormat);
}

function localTimeRead($dateTime, $hourFormat = 24)
{
    $format = ($hourFormat == 24) ? "G" : "g";
    $date = date_create($dateTime);
    return date_format($date, "$format:i a");
}

function calcPercentageDiff($startVal, $endVal)
{
    // using percentage decrease formula
    // percentage decrease = ((starting value-ending value)/starting value) * 100
    // if ans is negative, it expresses a rate of increase, otherwise a decrease
    $diff = (($startVal - $endVal) / $startVal);
    return round(($diff * 100), 2);
}

function appConfig(string $configKey)
{
    $mailLink = array(
        'salt' => '_~2y~12~T31xd7x7b67FO',
        'type' => array(
            1 => 'verify_account',
            2 => 'verify_success',
            3 => 'forget',
            4 => 'forget_success',
            5 => 'password_forget_token',
        ),
    );
    return $mailLink[$configKey];
}
