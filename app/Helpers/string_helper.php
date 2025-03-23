<?php

use App\Enums\CommonEnum as CommonSlug;

if (!function_exists('removeIntlOnPhoneNumber')) {
    function removeIntlOnPhoneNumber($phone): string
    {
        if (str_starts_with($phone, '+234')) {
            $phone = '0' . substr($phone, 4);
        }

        if (str_starts_with($phone, '234')) {
            $phone = '0' . substr($phone, 3);
        }
        return $phone;
    }
}

if (!function_exists('padPhoneNumber')) {
    function padPhoneNumber($phone): string
    {
        if ($phone && !str_starts_with($phone, '0')) {
            $phone = '0' . $phone;
        }
        return $phone;
    }
}

if (!function_exists('padDigitNumber')) {
    function padDigitNumber($number, $length = 2): string
    {
        return str_pad($number, $length, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('validateScoreIsNull')) {
    function validateScoreIsNull($score): bool
    {
        if ($score === '' || $score === null || $score === false) {
            return true;
        }
        return false;
    }
}

if (!function_exists('stripQueryString')) {
    function stripQueryString($url)
    {
        $position = strpos($url, '?');
        if ($position === false) {
            return $url;
        }
        return substr($url, 0, $position);
    }
}

if (!function_exists('formatStudentLevel')) {
    function formatStudentLevel($level): string
    {
        $level = (string)$level;
        return (strlen($level) < 3) ? $level . "00" : $level;
    }
}

if (!function_exists('isGraduate')) {
    function isGraduate(string $level, string $entryMode): bool
    {
        if ($entryMode === CommonSlug::O_LEVEL->value || $entryMode === CommonSlug::DIRECT_ENTRY->value) {
            if (!isNonGraduate($level)) {
                return true;
            }
        }

        if ($entryMode === CommonSlug::FAST_TRACK->value) {
            if (!isProperNonGraduate($level)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('isCarryOverGraduate')) {
    function isCarryOverGraduate(string $level): bool
    {
        if (in_array($level, ['401', '402', '501', '502'])) {
            return true;
        }

        return false;
    }
}

if (!function_exists('isNonGraduate')) {
    function isNonGraduate(string $level): bool
    {
        $content = ['1', '2', '3', '4'];
        return in_array($level, $content);
    }
}

if (!function_exists('isProperNonGraduate')) {
    function isProperNonGraduate(string $level): bool
    {
        $content = ['1', '2', '3'];
        return in_array($level, $content);
    }
}

if (!function_exists('setStatusCode')) {
    function setStatusCode($code = 200, $reason = ''): void
    {
        // Default reason phrases
        $defaultPhrases = [
            200 => 'OK',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            403 => 'Forbidden'
        ];

        // Use custom reason or default one
        $reasonPhrase = $reason ?: ($defaultPhrases[$code] ?? 'Unknown Status Code');

        // Set the header
        header("HTTP/1.1 $code $reasonPhrase");
    }
}

if (!function_exists('sendApiResponse')) {
    function sendApiResponse(bool $status, string $message, $payload = null)
    {
        $param = [
            'status' => $status,
            'message' => $message,
            'payload' => $payload
        ];
        echo json_encode($param);
        return true;
    }
}

if (!function_exists('check_unique_permission')) {
    function check_unique_permission(string $permission): bool
    {
        if (check_unique('roles_permission', $permission, 'permission')) {
            return false;
        } else {
            return true;
        }
    }
}

if (!function_exists('valid_date')) {
    function valid_date($date, $format = 'Y-m-d'): bool
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}

if (!function_exists('generateNumberWithOdd')) {
    function generateNumberWithOdd($lastNumber = null): string
    {
        if ($lastNumber === null) {
            // For fresh entries, include microseconds to reduce collision chance
            $datePart = date("Ymd");
            $microTime = sprintf("%04d", (microtime(true) - floor(microtime(true))) * 10000);
            return $datePart . $microTime;
        }

        $datePart = date("Ymd");
        $lastDigits = intval(substr($lastNumber, -4));

        // Ensure we're always incrementing, even across date boundaries
        if ($datePart . "0000" > $lastNumber) {
            $lastDigits = -1; // Will become 1 after increment
        }

        // Increment to next odd number
        $lastDigits = ($lastDigits % 2 == 0) ? $lastDigits + 1 : $lastDigits + 2;
        $newNumber = $datePart . str_pad(strval($lastDigits), 4, "0", STR_PAD_LEFT);

        // Final check to ensure new number is greater than last number
        return (strval($newNumber) > strval($lastNumber)) ? $newNumber : generateNumberWithOdd($newNumber);
    }
}

if (!function_exists('generateBatchRef')) {
    function generateBatchRef($lastNumber = null): string
    {
        // If $lastNumber is not provided, generate the current date
        if ($lastNumber === null) {
            $lastNumber = date("Ymd") . "0000";
        }
        $leadStr = "DLC";
        $currentDate = date("Ymd");
        $lastDate = substr($lastNumber, strlen($leadStr), strlen($currentDate));

        if ($currentDate != $lastDate) {
            $lastNumber = $currentDate . "0000";
        }

        $datePart = date("Ymd");

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

if (!function_exists('getLastInsertID')) {
    function getLastInsertID($db)
    {
        $query = "SELECT LAST_INSERT_ID() AS last";
        $result = $db->query($query);
        $result = $result->getResultArray();
        return $result[0]['last'];
    }
}

if (!function_exists('monthNumberToName')) {
    function monthNumberToName(int $num)
    {
        $dateObj = DateTime::createFromFormat('!m', $num);
        return $dateObj->format('F');
    }
}

if (!function_exists('monthNameToNumber')) {
    function monthNameToNumber(string $name)
    {
        return date('m', strtotime($name));
    }
}

if (!function_exists('isValidBase64Image')) {
    function isValidBase64Image($string): bool
    {
        if (strpos($string, 'data:') !== 0) {
            return false;
        }

        $mimeType = explode(';', substr($string, 5))[0];

        if (strpos($mimeType, 'image/') !== 0) {
            return false;
        }

        $base64Data = explode(',', $string)[1];
        // Check if the data is properly base64 encoded
        if (!preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $base64Data)) {
            return false;
        }

        $decodedData = base64_decode($base64Data, true);
        if ($decodedData === false) {
            return false;
        }

        // This is a more thorough check but may be resource-intensive for large images
        if (@getimagesizefromstring($decodedData) === false) {
            return false;
        }
        return true;
    }
}

if (!function_exists('convertBase64ToImage')) {
    function convertBase64ToImage($data): ?array
    {
        $temp = explode(',', $data);
        if (count($temp) == 2) {
            $extension = explode(';', $temp[0]);
            if (count($extension) == 2) {
                $extension = explode('/', $extension[0]);
                if (count($extension) == 2) {
                    $extension = $extension[1];
                    return array($temp[1], $extension);
                }
            }
        }
        return null;
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
        return 'data:image/' . $extension . ';base64,' . $base64_code;
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

if (!function_exists('formatToUTC')) {
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
}

if (!function_exists('is_decimal')) {
    function is_decimal($val)
    {
        return is_numeric($val) && floor($val) != $val;
    }
}

if (!function_exists('movePassport')) {
    function movePassport($object, $studentID, $passport)
    {
        $passport = basename($passport);
        $imagePath = returnFormalDirectory('applicants') . $passport;
        $path = FCPATH . $object->config->item('student_passport_path');
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        $newPath = $path . $passport;

        if (file_exists($imagePath)) {
            if (copy($imagePath, $newPath)) {
                $details = ['passport' => $passport];
                return update_record($object, 'students', 'id', $studentID, $details); // fail gracefully
            }
        }
        return false;
    }
}

if (!function_exists('applicantImagePath')) {
    function applicantImagePath($passport): string
    {
        if (str_contains($passport, 'assets')) {
            return ENVIRONMENT !== 'production' ? base_url($passport) : "https://dlcoffice.ui.edu.ng/{$passport}";
        } else if (str_contains($passport, 'uploads')) {
            return ENVIRONMENT !== 'production' ? base_url($passport) : "https://dlcoffice.ui.edu.ng/{$passport}";
        }
        return ENVIRONMENT !== 'production' ? base_url($passport) : "https://dlcoffice.ui.edu.ng/assets/images/applicants/{$passport}";
    }
}

if (!function_exists('studentImagePath')) {
    function studentImagePath($passport): string
    {
        $config = config('ImagePath');
        if (str_contains($passport, 'passport')) {
            return "https://apex.ui.edu.ng/" . $config->studentPassportPath . $passport;
        }

        if (str_contains($passport, 'assets')) {
            return "https://dlcoffice.ui.edu.ng/{$passport}";
        }

        if (!str_contains($passport, 'passport')) {
            return "https://apex.ui.edu.ng/" . $config->studentPassportPath . $passport;
        } else {
            return "https://dlcoffice.ui.edu.ng/assets/images/student/passports/{$passport}";
        }
    }
}

if (!function_exists('studentImagePathDirectory')) {
    function studentImagePathDirectory($passport): string
    {
        if (str_contains($passport, 'passport')) {
            return returnFormalDirectory('students', 'students') . 'student' . DIRECTORY_SEPARATOR . 'passports' . DIRECTORY_SEPARATOR . $passport;
        }

        if (str_contains($passport, 'assets')) {
            $passport = basename($passport);
            return returnFormalDirectory('applicants') . $passport;
        }

        if (!str_contains($passport, 'passport')) {
            return returnFormalDirectory('students', 'students') . 'student' . DIRECTORY_SEPARATOR . 'passports' . DIRECTORY_SEPARATOR . $passport;
        } else {
            return returnFormalDirectory('applicants') . $passport;
        }
    }
}

if (!function_exists('returnUploadDirectory')) {
    function returnUploadDirectory(string $type)
    {
        $result = [
            'applicants' => ENVIRONMENT !== 'production' ? 'uploads' . DIRECTORY_SEPARATOR . "applicants" . DIRECTORY_SEPARATOR : "assets" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "applicants" . DIRECTORY_SEPARATOR,
        ];

        return $result[$type];
    }
}

if (!function_exists('returnFormalDirectory')) {
    function returnFormalDirectory($parentName = 'applicants', $type = 'applicants'): string
    {
        $applicantPath = "/var" . DIRECTORY_SEPARATOR . "www" . DIRECTORY_SEPARATOR . "dlcoffice.ui.edu.ng" . DIRECTORY_SEPARATOR . "public_html" . DIRECTORY_SEPARATOR . "current" . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . "{$parentName}" . DIRECTORY_SEPARATOR;
        $studentPath = "/var" . DIRECTORY_SEPARATOR . "www" . DIRECTORY_SEPARATOR . "apex" . DIRECTORY_SEPARATOR . "public_html" . DIRECTORY_SEPARATOR . "uploads" . DIRECTORY_SEPARATOR;
        $applicantLocalPath = "uploads" . DIRECTORY_SEPARATOR . "applicants" . DIRECTORY_SEPARATOR;
        $result = [
            'applicants' => ENVIRONMENT !== 'production' ? FCPATH . $applicantLocalPath : $applicantPath,
            'students' => $studentPath,
        ];
        return $result[$type];
    }
}

if (!function_exists('userImagePath')) {
    function userImagePath(string $passport, string $path = null): string
    {
        if ($passport) {
            $config = config('ImagePath');
            $configPath = $config->userPassportPath;

            if ($path === 'retire_advance_path') {
                $configPath = $config->retireAdvancePath;
            } else if ($path === 'cac_certificate_path') {
                $configPath = $config->cacCertificatePath;
            } else if ($path === 'payment_voucher_path') {
                $configPath = $config->paymentVoucherPath;
            }
            return base_url($configPath . $passport);
        }
        return $passport;
    }
}

if (!function_exists('calcAge')) {
    function calcAge($dob)
    {
        return (date('Y') - date('Y', strtotime($dob)));
    }
}

if (!function_exists('get_age')) {
    /**
     * @throws DateMalformedStringException
     */
    function get_age($dob)
    {
        if (!$dob) {
            return '';
        }

        $formatDate = explode("/", $dob);

        $dob = $formatDate[0] . "." . $formatDate[1] . "." . $formatDate[2];
        $bday = new DateTime($dob); // Your date of birth
        $today = new Datetime(date('d.m.Y'));
        $diff = $today->diff($bday);

        return $diff->y;
    }
}

if (!function_exists('getFirstString')) {
    function getFirstString($str, $uppercase = false)
    {
        if ($str) {
            $value = substr($str, 0, 1);
            return ($uppercase) ? strtoupper($value) : strtolower($value);
        }
        return false;
    }
}

if (!function_exists('formatToNameLabel')) {
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
}

if (!function_exists('dddump')) {
    function dddump($data)
    {
        print_r($data);
        exit;
    }
}

if (!function_exists('ddump')) {
    function ddump($data)
    {
        print_r($data);
    }
}

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

if (!function_exists('generateCode')) {
    function generateCode($len): string
    {
        return randStrGen($len);
    }
}

if (!function_exists('rowExists')) {
    function rowExists($db, $table, $columns): bool
    {
        $where = '';
        foreach ($columns as $key => $value) {
            $value = $db->escapeString($value);
            $temp = "$key='$value'";
            if ($where) {
                $where .= " and $temp";
            } else {
                $where = " where $temp ";
            }
        }
        $query = "select * from $table $where";
        $result = $db->query($query);
        return $result->getNumRows() > 0;
    }
}

if (!function_exists('removeNonCharacter')) {
    function removeNonCharacter($string)
    {
        $string = trim($string);
        return preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $string);
    }
}

if (!function_exists('cleanCell')) {
    function cleanCell($data)
    {
        // Remove any whitespace, newlines, tabs and replace with single space
        $cleaned = preg_replace('/[\r\n\t\s]+/', ' ', $data);
        return trim($cleaned);
    }
}

if (!function_exists('get_user_role_id')) {
    function get_user_role_id($userID)
    {
        $db = db_connect();
        $query = $db->table('roles_user')->getWhere(array('user_id' => $userID));
        if ($query->getNumRows() > 0) {
            return $query->getRow()->role_id;
        } else {
            return null;
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

if (!function_exists('generate_me_tokens')) {
    function generate_me_tokens(): array
    {
        $selector = bin2hex(random_bytes(16));
        $validator = bin2hex(random_bytes(32));

        return [$selector, $validator, $selector . ':' . $validator];
    }
}

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

if (!function_exists('permissionDenied')) {
    function permissionDenied($message = null)
    {
        return $message ?? "It looks like you do not have access to view this page.";
    }
}

if (!function_exists('transaction_status')) {
    function transaction_status($transaction_status_code, $format_html = 1): string
    {
        if ($format_html == 1) {
            switch ($transaction_status_code) {
                case '0':
                    return "<i style='color:#c8de51; font-weight:bold;'>pending</i>";
                case '100':
                    return "<i style='color:#c8de51; font-weight:bold;'>pending</i>";
                case '00':
                    return "<i style='color:green; font-weight:bold;'>success</i>";
                case '01':
                    return "<i style='color:green; font-weight:bold;'>success</i>";
                case '02':
                    return "<i style='color:red; font-weight:bold;'>failed</i>";
                case '012':
                    return "<i style='color:red; font-weight:bold;'>aborted</i>";
                case '021':
                    return "<i style='color:#c8de51; font-weight:bold;'>pending</i>";
                case '026':
                    return "<i style='color:red; font-weight:bold;'>No such order</i>";
                default:
                    return "<i style='color:red; font-weight:bold;'>failed</i>";
            }
        } else {
            switch ($transaction_status_code) {
                case '0':
                    return "pending";
                case '100':
                    return "pending";
                case '00':
                    return "success";
                case '01':
                    return "success";
                case '02':
                    return "failed";
                case '012':
                    return "aborted";
                case '021':
                    return "pending";
                case '026':
                    return "No such order";
                default:
                    return "failed";
            }
        }
    }
}

if (!function_exists('feeCategoryType')) {
    function feeCategoryType(?string $label, bool $flip = false)
    {
        $result = [
            'main' => 1,
            'others' => 2,
            'custom' => 4,
        ];

        if ($flip) {
            $result = array_flip($result);
        }

        if (array_key_exists($label, $result) === false) {
            return null;
        }

        return $result[$label];
    }
}

if (!function_exists('paymentCategoryType')) {
    function paymentCategoryType(?string $label, bool $flip = false)
    {
        $result = [
            'main_fees' => 1,
            'sundry_fees' => 2,
            'verification_fees' => 3,
        ];

        if ($flip) {
            $result = array_flip($result);
        }

        if (array_key_exists($label, $result) === false) {
            return null;
        }

        return $result[$label];
    }
}

if (!function_exists('paymentOptionsType')) {
    function paymentOptionsType(?string $label, bool $flip = false)
    {
        $result = [
            'full_first_sem' => '1',
            'part_first_sem_a' => '1A',
            'part_first_sem_b' => '1B',
            'full_second_sem' => '2',
            'part_second_sem_a' => '2A',
            'part_second_sem_b' => '2B',
        ];

        if ($flip) {
            $result = array_flip($result);
        }

        if (array_key_exists($label, $result) === false) {
            return null;
        }

        return $result[$label];
    }
}

if (!function_exists('isPartPayment')) {
    function isPartPayment(string $code): bool
    {
        return strpos($code, 'part') !== false ? true : false;
    }
}

if (!function_exists('isPaymentComplete')) {
    function isPaymentComplete(string $code, bool $flip = true): bool
    {
        $content = paymentOptionsType($code, $flip);
        return !isPartPayment($content);
    }
}

if (!function_exists('inferPaymentOption')) {
    function inferPaymentOption($code, $flip = true)
    {
        $result = '1B';
        if ($code == '1') {
            $result = '1B';
        } else if ($code == '2') {
            $result = '2B';
        }

        return paymentOptionsType($result, $flip);
    }
}

if (!function_exists('inferPaymentCode')) {
    function inferPaymentCode($value)
    {
        $paymentTypeOption = inferPaymentOption($value);
        return paymentOptionsType($paymentTypeOption);
    }
}

if (!function_exists('isSundryPayment')) {
    function isSundryPayment($value): bool
    {
        return $value != '1' && $value != '2';
    }
}

if (!function_exists('paymentEntryModeType')) {
    function paymentEntryModeType($label): ?string
    {
        $result = array('-' => 'none', 'O\' Level' => 'O\' Level', 'Direct Entry' => 'Direct Entry',
            'Fast Track' => 'Fast Track', 'Diploma' => 'Diploma',
            'Remedial Application Form' => 'Remedial Application Form',
            'Remedial Application Form (Special)' => 'Remedial Application Form (Special)',
            'PG Application Form' => 'PG Application Form',
        );
        if (array_key_exists($label, $result) === false) {
            return null;
        }

        return $result[$label];
    }
}

if (!function_exists('levelModeType')) {
    function levelModeType($label): ?string
    {
        $result = array(
            '0' => '0', '1' => '100', '2' => '200', '3' => '300',
            '4' => '400', '401' => '401', '402' => '402', '5' => '500',
            '501' => '501', '502' => '502', '6' => '600', '7' => '700',
            '8' => '800', '9' => '900',
        );
        if (array_key_exists($label, $result) === false) {
            return null;
        }

        return $result[$label];
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

if (!function_exists('buildCustomWhereString')) {
    function buildCustomWhereString(?string $whereString = null, ?string $queryString = null, bool $orderBy = true): string
    {
        $filterQuery = '';
        if ($whereString || $queryString) {
            $conditions = [];
            if ($whereString) {
                $conditions[] = $whereString;
            }
            if ($queryString) {
                $conditions[] = "({$queryString})";
            }
            $filterQuery = ' WHERE ' . implode(' AND ', $conditions);
        }

        if ($orderBy) {
            $filterQuery .= ' ORDER BY id DESC';
        }
        return $filterQuery;
    }
}

if (!function_exists('number_to_words')) {
    function number_to_words($number)
    {
        $hyphen = '-';
        $conjunction = ' and ';
        $separator = ', ';
        $negative = 'negative ';
        $decimal = ' point ';
        $dictionary = array(
            0 => 'zero',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen',
            20 => 'twenty',
            30 => 'thirty',
            40 => 'fourty',
            50 => 'fifty',
            60 => 'sixty',
            70 => 'seventy',
            80 => 'eighty',
            90 => 'ninety',
            100 => 'hundred',
            1000 => 'thousand',
            1000000 => 'million',
            1000000000 => 'billion',
            1000000000000 => 'trillion',
            1000000000000000 => 'quadrillion',
            1000000000000000000 => 'quintillion',
        );

        if (!is_numeric($number)) {
            return false;
        }

        if (($number >= 0 && (int)$number < 0) || (int)$number < 0 - PHP_INT_MAX) {
            // overflow
            trigger_error(
                'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
                E_USER_WARNING
            );
            return false;
        }

        if ($number < 0) {
            return $negative . number_to_words(abs($number));
        }

        $string = $fraction = null;

        if (strpos($number, '.') !== false) {
            list($number, $fraction) = explode('.', $number);
        }

        switch (true) {
            case $number < 21:
                $string = $dictionary[$number];
                break;
            case $number < 100:
                $tens = ((int)($number / 10)) * 10;
                $units = $number % 10;
                $string = $dictionary[$tens];
                if ($units) {
                    $string .= $hyphen . $dictionary[$units];
                }
                break;
            case $number < 1000:
                $hundreds = $number / 100;
                $remainder = $number % 100;
                $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                if ($remainder) {
                    $string .= $conjunction . number_to_words($remainder);
                }
                break;
            default:
                $baseUnit = pow(1000, floor(log($number, 1000)));
                $numBaseUnits = (int)($number / $baseUnit);
                $remainder = $number % $baseUnit;
                $string = number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                if ($remainder) {
                    $string .= $remainder < 100 ? $conjunction : $separator;
                    $string .= number_to_words($remainder);
                }
                break;
        }

        if (null !== $fraction && is_numeric($fraction)) {
            $string .= $decimal;
            $words = array();
            foreach (str_split((string)$fraction) as $number) {
                $words[] = $dictionary[$number];
            }
            $string .= implode(' ', $words);
        }

        return $string;
    }
}

if (!function_exists('extract_json')) {
    function extract_json($values): string
    {
        $pattern = "/\{[^{}]*}/i";
        preg_match($pattern, $values, $output);
        return $output ? $output[0] : '{}';
    }
}

if (!function_exists('removeUnderscore')) {
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
}

if (!function_exists('uniqueString')) {
    function uniqueString($size = 20): string
    {
        $bytes = random_bytes($size);
        return bin2hex($bytes);
    }
}

if (!function_exists('generateReceipt')) {
    function generateReceipt(): string
    {
        $rand = mt_rand(0x000000, 0xffffff); // generate a random number between 0 and 0xffffff
        $rand = dechex($rand & 0xffffff); // make sure we're not over 0xffffff, which shouldn't happen anyway
        $rand = str_pad($rand, 6, '0', STR_PAD_LEFT); // add zeroes in front of the generated string
        $code = date('Y') . "" . $rand;
        return strtoupper($code);
    }
}

if (!function_exists('orderID')) {
    function orderID(): string
    {
        $order_id = '';
        $salt = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $i = 0;
        while ($i <= 10) {
            $num = rand() % 36;
            $tmp = substr($salt, $num, 1);
            $order_id = $order_id . $tmp;
            $i++;
        }
        return $order_id;
    }
}

if (!function_exists('createJsonMessage')) {
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
}

if (!function_exists('isNotEmpty')) {
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
}

if (!function_exists('stringToCsv')) {
    function stringToCsv($string, $delimiter = ',')
    {
        $result = array();
        $lines = explode("\n", trim($string));
        for ($i = 0; $i < count($lines); $i++) {
            $current = $lines[$i];
            $result[] = explode($delimiter, trim($current));
        }
        return $result;
    }
}

if (!function_exists('array2csv')) {
    function array2csv($array, $header = false)
    {
        $content = '';
        if ($array) {
            $content = strtoupper(implode(',', $header ?: array_keys($array[0]))) . "\n";
        }
        foreach ($array as $value) {
            $content .= implode(',', $value) . "\n";
        }
        return $content;
    }
}

if (!function_exists('makeHash')) {
    function makeHash($string, $salt = ''): string
    {
        return hash('sha256', $string . $salt);
    }
}

if (!function_exists('endsWith')) {
    function endsWith($string, $end)
    {
        $temp = substr($string, strlen($string) - strlen($end));
        return $end == $temp;
    }
}

if (!function_exists('verifyPassword')) {
    function verifyPassword($password)
    {
        $minLength = 8;
        $numPattern = "/[0-9]/";
        $upperCasePattern = "/[A-Z]/";
        $lowerCasePattern = '/[a-z]/';
        return preg_match($numPattern, $password) && preg_match($upperCasePattern, $password) && preg_match($lowerCasePattern, $password) && strlen($password) >= $minLength;
    }
}

if (!function_exists('isUniqueEmailAndPhone')) {
    function isUniqueEmailAndPhone($db, $email, $phone)
    {
        $query = "select * from customer where email=? or phone_number=?";
        $result = $db->query($query, array($email, $phone));
        $result = $result->getResultArray();
        return count($result) == 0;
    }
}

if (!function_exists('encode_password')) {
    function encode_password($password)
    {
        $options = [
            'cost' => 12,
        ];
        return password_hash($password, PASSWORD_DEFAULT, $options);
    }
}

if (!function_exists('decode_password')) {
    function decode_password($userData, $fromDb)
    {
        if ($userData != NULL) {
            return password_verify($userData, $fromDb);
        }
        return false;
    }
}

if (!function_exists('getPasswordOTP')) {
    function getPasswordOTP($scope)
    {
        $result = '';
        do {
            $result = random_int(100000, 999999);
        } while (!isValidPasswordOTP($scope, $result));
        return $result;
    }
}

if (!function_exists('isValidPasswordOTP')) {
    function isValidPasswordOTP($db, $otp)
    {
        $query = "select * from password_otp where otp=? and status=1";
        $result = $db->query($query, array($otp));
        $result = $result->getResultArray();
        return !$result;
    }
}

if (!function_exists('isValidState')) {
    function isValidState($state): bool
    {
        $state = strtolower($state);
        $states = array('abia', 'adamawa', 'akwa ibom', 'awka', 'bauchi', 'bayelsa', 'benue', 'borno', 'cross river', 'delta', 'ebonyi', 'edo', 'ekiti', 'enugu', 'gombe', 'imo', 'jigawa', 'kaduna', 'kano', 'katsina', 'kebbi', 'kogi', 'kwara', 'lagos', 'nasarawa', 'niger', 'ogun', 'ondo', 'osun', 'oyo', 'plateau', 'rivers', 'sokoto', 'taraba', 'yobe', 'zamfara');
        return in_array($state, $states);
    }
}

if (!function_exists('formatToNgPhone')) {
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
                return preg_replace("/([0-9])([0-9]{3})([0-9]{3})([0-9]{4})/", "+234$2$3$4", $phone);
                break;
            default:
                return "+" . $phone;
                break;
        }
    }
}

if (!function_exists('cleanPhone')) {
    function cleanPhone($phone)
    {
        $justNums = preg_replace("/[^0-9]/", '', $phone);
        if (strlen($justNums) == 13) {
            $justNums = preg_replace("/^234/", '0', $justNums);
        }

        return $justNums;
    }
}

if (!function_exists('isValidPhone')) {
    function isValidPhone($phone)
    {
        $pattern = "/\+?0?[789][01]\d{8}$/i";
        return preg_match($pattern, $phone);
    }
}

if (!function_exists('extractDbField')) {
    function extractDbField($dbType)
    {
        $index = strpos($dbType, '(');
        if ($index) {
            return substr($dbType, 0, $index);
        }
        return $dbType;
    }
}

if (!function_exists('extractDbTypeLength')) {
    function extractDbTypeLength($dbType)
    {
        $index = strpos($dbType, '(');
        if ($index) {
            $len = strlen($dbType) - ($index + 2);
            return substr($dbType, $index + 1, $len);
        }
        return '';
    }
}

if (!function_exists('getPhpType')) {
    function getPhpType($dbType)
    {
        $type = array('varchar' => 'string', 'text' => 'string', 'int' => 'integer', 'year' => 'integer', 'real' => 'double', 'float' => 'float', 'double' => 'double', 'timestamp' => 'date', 'date' => 'date', 'datetime' => 'date', 'time' => 'time', 'varbinary' => 'byte_array', 'blob' => 'byte_array', 'boolean' => 'boolean', 'tinyint' => 'boolean', 'bit' => 'boolean');
        $dbType = extractDbField($dbType);
        $dbType = strtolower($dbType);
        return $type[$dbType];
    }
}

if (!function_exists('buildOption')) {
    function buildOption($array, $val = '')
    {
        if (empty($array)) {
            return '';
        }
        $result = '';
        for ($i = 0; $i < count($array); $i++) {
            $current = $array[$i];
            $id = $current['id'];
            $value = $current['value'];
            $selected = $val == $id ? "selected='selected'" : '';
            $result .= "<option value='$id' $selected>$value</option> \n";
        }
        return $result;
    }
}

if (!function_exists('buildOptionUnassoc2')) {
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
}

if (!function_exists('getMediaType')) {
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
}

if (!function_exists('getRoleIdByName')) {
    function getRoleIdByName($db, $name)
    {
        $query = "select id from role where role_name=?";
        $result = $db->query($query, array($name));
        $result = $result->getResultArray();
        return $result[0]['id'];
    }
}

if (!function_exists('buildOptionFromQuery')) {
    function buildOptionFromQuery($db, $query, $data = null, $val = '', $defaultValue = ''): string
    {
        $result = $db->query($query, $data);
        if ($result->getNumRows() == 0) {
            return '';
        }
        $result = $result->getResultArray();
        return buildOption($result, $val, $defaultValue);
    }
}

if (!function_exists('buildOptionUnassoc')) {
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
}

if (!function_exists('startsWith')) {
    function startsWith($str, $sub)
    {
        $len = strlen($sub);
        $temp = substr($str, 0, $len);
        return $temp === $sub;
    }
}

if (!function_exists('showUploadErrorMessage')) {
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
}

if (!function_exists('loadClass')) {
    function loadClass($classname, $namespace = null)
    {
        if (!class_exists($classname)) {
            $modelName = is_null($namespace) ? "App\\Entities\\" . ucfirst($classname) : $namespace . "\\" . ucfirst($classname);
            return new $modelName;
        }
    }
}

if (!function_exists('getDateDifference')) {
    function getDateDifference($first, $second)
    {
        $interval = date_diff(date_create($first), date_create($second));
        return $interval;
    }
}

if (!function_exists('isDateGreater')) {
    function isDateGreater($first, $second)
    {
        $interval = getDateDifference($first, $second);
        return $interval->invert;
    }
}

if (!function_exists('formatPaymentDate')) {
    function formatPaymentDate(?string $date, string $format = 'M. d, Y')
    {
        if (!$date) {
            return null;
        }

        return date_format(date_create($date), $format);
    }
}

if (!function_exists('dateFormatter')) {
    function dateFormatter($date)
    {
        if ($date) {
            return date_format(date_create($date), "d F, Y");
        }
        return $date;
    }
}

if (!function_exists('isTimePassed')) {
    function isTimePassed($start, $end, $limit = 15): bool
    {
        $expiration = "+$limit minutes";
        $expTime = strtotime($expiration, $end);
        if ($start <= $expTime) {
            return false; // means the first is less than the second
        }
        return true;
    }
}

if (!function_exists('calc_size')) {
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
}

if (!function_exists('sendDownload')) {
    function sendDownload($content, $header, $filename, $type = 'text')
    {
        ob_get_clean();
        if ($type == 'binary') {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($content) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($content));
            readfile($content);
            while (ob_get_level() > 0) {
                @ob_end_flush();
            }
            unlink($content);
            exit;
        }
        header("Content-Type:$header");
        header("Content-disposition: attachment;filename=$filename");
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        // If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $content = trim($content);
        echo $content;
        exit;
    }
}

if (!function_exists('padNumber')) {
    function padNumber($n, $value)
    {
        $value = '' . $value; //convert the type to string
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
}

if (!function_exists('getFileExtension')) {
    function getFileExtension($filename)
    {
        $index = strripos($filename, '.', -1); //start from the back
        if ($index === -1) {
            return '';
        }
        return substr($filename, $index + 1);
    }
}

if (!function_exists('isFilePath')) {
    function isFilePath($str)
    {
        $recognisedExtension = array('doc', 'docx', 'pdf', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'csv');
        $extension = getFileExtension($str);
        return (startsWith($str, 'uploads') && strpos($str, '/') && in_array($extension, $recognisedExtension));
    }
}

if (!function_exists('padwithZeros')) {
    function padwithZeros($str, $len)
    {
        $str .= '';
        $count = $len - strlen($str);
        for ($i = 0; $i < $count; $i++) {
            $str = '0' . $str;
        }
        return $str;
    }
}

if (!function_exists('generatePassword')) {
    function generatePassword(): string
    {
        return randStrGen(10);
    }
}

if (!function_exists('generateUniqueChars')) {
    function generateUniqueChars($length = 4): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }
}

if (!function_exists('makeRandomPassword')) {
    function makeRandomPassword()
    {
        $pass = '';
        $salt = "abchefghjkmnpqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ.-+=_,!@$#*%<>[]{}0123456789";
        $i = 0;
        while ($i <= 10) {
            $num = rand() % 77;
            $tmp = substr($salt, $num, 1);
            $pass = $pass . $tmp;
            $i++;
        }
        return $pass;
    }
}

if (!function_exists('randStrGen')) {
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
}

if (!function_exists('getPageCookie')) {
    function getPageCookie()
    {
        $result = array();
        if (isset($_COOKIE['edu_per'])) {
            $content = $_COOKIE['edu_per'];
            $result = explode('-', $content);
        }
        return $result;
    }
}

if (!function_exists('sendPageCookie')) {
    function sendPageCookie($module, $page)
    {
        $content = $module . '-' . $page;
        setcookie('edu_per', $content, 0, '/', '', false, true);
    }
}

if (!function_exists('show_access_denied')) {
    function show_access_denied()
    {
        $viewName = "App\\Views\\access_denied";
        return view($viewName);
    }
}

if (!function_exists('show_operation_denied')) {
    function show_operation_denied()
    {
        $viewName = "App\\Views\\operation_denied";
        view($viewName);
    }
}

if (!function_exists('replaceFirst')) {
    function replaceFirst($toReplace, $replacement, $string)
    {
        $pos = stripos($string, $toReplace);
        if ($pos === false) {
            return $string;
        }
        $len = strlen($toReplace);
        return substr_replace($string, $replacement, $pos, $len);
    }
}

if (!function_exists('displayJson')) {
    function displayJson($status, $message, $payload = false): void
    {
        $param = array('status' => $status, 'message' => $message, 'payload' => $payload);
        echo json_encode($param);
        exit;
    }
}

if (!function_exists('formatToLocalCurrency')) {
    function formatToLocalCurrency($value = null)
    {
        return "&#8358;$value"; // this is a naira currency
    }
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

if (!function_exists('rndEncode')) {
    function rndEncode($data, $len = 16)
    {
        return urlencode(base64_encode(randStrGen($len) . $data));
    }
}

if (!function_exists('rndDecode')) {
    function rndDecode($data, $len = 16)
    {
        $hash = base64_decode(urldecode($data));
        return substr($hash, $len);
    }
}

if (!function_exists('getUserOption')) {
    function getUserOption($value = '')
    {
        $user = loadClass('user');
        return $user->getUserIdOption($value);
    }
}

if (!function_exists('sendResponse')) {
    function sendResponse($param, $status = 200, $headers = [])
    {
        http_response_code($status);
        if ($headers) {
            foreach ($headers as $header) {
                header($header);
            }
        }
        echo json_encode($param);
        exit;
    }
}

if (!function_exists('getIDByName')) {
    function getIDByName($db, $table, $column, $value)
    {
        $query = "select id from $table where $column=?";
        $result = $db->query($query, array($value));
        $result = $result->getResultArray();
        if (!$result) {
            return false;
        }
        return $result[0]['id'];
    }
}

if (!function_exists('getUploadText')) {
    function getUploadText($uploadName, $maxSize, $allowed)
    {
        if (!array_key_exists($uploadName, $_FILES)) {
            return [false, "File does not exist"];
        }

        $file = $_FILES[$uploadName];
        if ($file['error']) {
            return [false, "An unknown error occured while upload file. Please try again"];
        }

        $extension = explode('.', $file['name']);
        $extension = $extension[count($extension) - 1];
        $allowedExtension = explode('|', strtolower($allowed));
        if (!in_array($extension, $allowedExtension)) {
            return [false, 'Invalid file type uploaded'];
        }
        if ($file['size'] > $maxSize) {
            return [false, 'Invalid file size'];
        }
        $result = file_get_contents($file['tmp_name']);
        return $result;
    }
}

if (!function_exists('getUploadedFileContent')) {
    function getUploadedFileContent($uploadName, $maxSize, $allowedType)
    {
        $content = getUploadText($uploadName, $maxSize, $allowedType);
        if (is_array($content)) {
            displayJson(false, $content[1]);
        }

        if (!trim($content)) {
            displayJson(false, $content[1]);
        }
        return $content;
    }
}

if (!function_exists('fetchSingleField')) {
    function fetchSingleField($db, $tablename, $WhereField, $value, $field = 'id')
    {
        $query = "select $field from $tablename where $WhereField=?";
        $result = $db->query($query, array($value));
        $result = $result->getResultArray();
        if (!$result) {
            return false;
        }
        return $result[0][$field];
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

if (!function_exists('check_unique')) {
    function check_unique(string $table_name, $value_field, string $db_field, bool $convert_to_lower = true)
    {
        if (!$convert_to_lower) {
            $value_field = strtolower($value_field);
        }
        $db = db_connect();
        $query = $db->table($table_name)->getWhere(array($db_field => $value_field));

        foreach ($query->getResultArray() as $row) {
            if (strtolower($row[$db_field]) == strtolower($value_field)) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('check_unique_multiple')) {
    function check_unique_multiple(string $table_name, array $where)
    {
        $db = db_connect();
        $query = $db->table($table_name)->getWhere($where);
        if ($query->getNumRows() > 0) {
            return true;
        }

        return false;
    }
}

if (!function_exists('get_single_record')) {
    function get_single_record(string $table, $where)
    {
        $db = db_connect();
        $result = $db->table($table)->getWhere($where);
        if ($result->getNumRows() == 0) {
            return null;
        }
        return $result->getRow();
    }
}

if (!function_exists('getSingleRecordExclude')) {
    function getSingleRecordExclude(string $table_name, string $where_clause)
    {
        $db = db_connect();
        $query = $db->table($table_name)->getWhere($where_clause);

        if ($query->getNumRows() > 0) {
            return $query->getRow();
        } else {
            return false;
        }
    }
}

if (!function_exists('update_record')) {
    function update_record($db, $table_name, $where_field, $id, $data): bool
    {
        $builder = $db->table($table_name)->where($where_field, $id);
        if ($builder->update($data)) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('update_record_array')) {
    function update_record_array($db, $table_name, $where_field, $data): bool
    {
        $db = $db->table($table_name)->where($where_field);
        if ($db->update($data)) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('create_record')) {
    function create_record($db, $table_name, $data)
    {
        if ($db->table($table_name)->insert($data)) {
            return $db->insertID();
        } else {
            return null;
        }
    }
}

if (!function_exists('create_record_batch')) {
    function create_record_batch($db, $table_name, $data): bool
    {
        if ($db->insert_batch($table_name, $data)) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('get_setting')) {
    function get_setting(string $settings_name)
    {
        $db = db_connect();
        $query = $db->table('settings')->getWhere(array('settings_name' => $settings_name));
        $query = $query->getRow();
        return $query->settings_value;
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

if (!function_exists('isLive')) {
    function isLive(string $settingType = 'inflow'): bool
    {
        $settingType = $settingType === 'inflow' ? 'remita_gateway_status' : 'remita_gateway_status_outflow';
        return 'production' == get_setting($settingType);
    }
}

if (!function_exists('logAction')) {
    function logAction($db, $action, $user = null, $student = null, $oldData = null, $newData = null)
    {
        $data = array(
            'username' => $user ?? null,
            'action_performed' => $action,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'user_ip' => $_SERVER['REMOTE_ADDR'],
            'user_long' => '',
            'user_lat' => '',
            'date_performed' => date('Y-m-d H:i:s'),
            'student_id' => $student,
            'old_data' => $oldData,
            'new_data' => $newData,
        );
        return create_record($db, 'users_log', $data);
    }
}

if (!function_exists('hashids_createobject')) {
    function hashids_createobject($salt_ov = NULL, $min_hash_length_ov = NULL, $alphabet_ov = NULL)
    {
        $CI = null;

        $salt = (!$salt_ov) ? $CI->config->item('hashids_salt') : $salt_ov;
        $min_hash_length = (!$min_hash_length_ov) ? $CI->config->item('hashids_min_hash_length') : $min_hash_length_ov;
        $alphabet = (!$alphabet_ov) ? $CI->config->item('hashids_alphabet') : $alphabet_ov;

        return new Hashids\Hashids($salt, $min_hash_length, $alphabet);
    }
}

if (!function_exists('hashids_encrypt')) {
    function hashids_encrypt($input, $salt = NULL, $min_hash_length = NULL, $alphabet = NULL)
    {
        $CI = &get_instance();

        if (!is_array($input)) {
            $input = array(intval($input));
        }

        $hashids = hashids_createobject($salt, $min_hash_length, $alphabet);
        return call_user_func_array(array($hashids, "encrypt"), $input);
    }
}

if (!function_exists('hashids_decrypt')) {
    function hashids_decrypt($hash, $salt = '', $min_hash_length = 0, $alphabet = '')
    {
        $hashids = hashids_createobject($salt, $min_hash_length, $alphabet);
        $output = $hashids->decrypt($hash);
        if (count($output) < 1) {
            return NULL;
        }

        return (count($output) == 1) ? reset($output) : $output;
    }
}