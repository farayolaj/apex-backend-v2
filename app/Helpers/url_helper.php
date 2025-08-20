<?php

function curlRequest(string $url, string $type, array $headers, ?string $param = null, $return = false, &$output = null, &$errorMessage = '')
{
    $res = curl_init($url);
    curl_setopt($res, CURLOPT_ENCODING, "");
    curl_setopt($res, CURLOPT_MAXREDIRS, 10);
    curl_setopt($res, CURLOPT_TIMEOUT, 30);
    curl_setopt($res, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($res, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

    if ($type == 'post') {
        curl_setopt($res, CURLOPT_POST, true);
        curl_setopt($res, CURLOPT_POSTFIELDS, $param);
    } else if ($type == 'get') {
        curl_setopt($res, CURLOPT_CUSTOMREQUEST, "GET");
    } else if ($type == 'patch') {
        curl_setopt($res, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($res, CURLOPT_POSTFIELDS, $param);
    } else if ($type == 'put') {
        curl_setopt($res, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($res, CURLOPT_POSTFIELDS, $param);
    } else if ($type == 'delete') {
        curl_setopt($res, CURLOPT_CUSTOMREQUEST, "DELETE");
    }

    //check if the quest is a secure one
    if (strtolower(substr($url, 0, 5)) == 'https') {
        curl_setopt($res, CURLOPT_SSL_VERIFYSTATUS, false);
        curl_setopt($res, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($res, CURLOPT_SSL_VERIFYPEER, false);
    }

    if ($return) {
        curl_setopt($res, CURLOPT_RETURNTRANSFER, true);
    }
    $formattedHeader = formatHeader($headers);
    curl_setopt($res, CURLOPT_HTTPHEADER, $formattedHeader);
    $result = curl_exec($res);
    $errorMessage = curl_error($res);
    $output = $result;
    curl_close($res);
    return !$errorMessage;
}

function formatHeader(array $header)
{
    if (!$header) {
        return $header;
    }
    $keys = array_keys($header);
    if (is_numeric($keys[0])) {
        //if has numberic index, should mean the header has already been formated inthe right way
        return $header;
    }
    $result = array();
    foreach ($header as $key => $value) {
        $temp = "$key: $value";
        $result[] = $temp;
    }
    return $result;
}
