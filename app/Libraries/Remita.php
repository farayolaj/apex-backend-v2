<?php

namespace App\Libraries;

use App\Enums\PaymentFeeDescriptionEnum as PaymentFeeDescription;
use App\Traits\CommonTrait;

/**
 *
 */
class Remita
{

    public function remitaTransactionDetails($url, $header)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    public function remitaTransactionPost($url, $header, $jsonData = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        $responseString = curl_exec($ch);
        curl_close($ch);

        $jsonData = substr($responseString, 6, -1);
        $jsonResponse = extract_json($jsonData);
        $response = json_decode($jsonResponse, true);
        return $response;
    }

    public function remitaServicePost($url, $header = null, $jsonData = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        ($header) ? curl_setopt($ch, CURLOPT_HTTPHEADER, $header) : null;
        ($jsonData) ? curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData) : null;

        $response = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($response, true);
        return $response;
    }

    public function remitaServiceGet(string $url, $header)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    private function buildSplitAccount($total, $serviceCharge, $subAccount): array
    {
        $live = isLive();
        return [
            array(
                'lineItemsId' => 'edp1_' . time(),
                'beneficiaryName' => $live ? $this->config->item('beneficiary_name_1') : $this->config->item('demo_beneficiary_name_1'),
                'beneficiaryAccount' => $live ? $this->config->item('beneficiary_account_1') : $this->config->item('demo_beneficiary_account_1'),
                'bankCode' => $live ? $this->config->item('beneficiary_bank_code_1') : $this->config->item('demo_beneficiary_bank_code_1'),
                'beneficiaryAmount' => $total,
                'deductFeeFrom' => '1',
            ),
            array(
                'lineItemsId' => 'edp2_' . time(),
                'beneficiaryName' => $live ? $this->config->item('beneficiary_name_2') : $this->config->item('demo_beneficiary_name_2'),
                'beneficiaryAccount' => $live ? $this->config->item('beneficiary_account_2') : $this->config->item('demo_beneficiary_account_2'),
                'bankCode' => $live ? $this->config->item('beneficiary_bank_code_2') : $this->config->item('demo_beneficiary_bank_code_2'),
                'beneficiaryAmount' => $serviceCharge,
                'deductFeeFrom' => '0',
            ),
            array(
                'lineItemsId' => 'edp3_' . time(),
                'beneficiaryName' => $live ? $this->config->item('beneficiary_name_3') : $this->config->item('demo_beneficiary_name_1'),
                'beneficiaryAccount' => $live ? $this->config->item('beneficiary_account_3') : $this->config->item('demo_beneficiary_account_1'),
                'bankCode' => $live ? $this->config->item('beneficiary_bank_code_3') : $this->config->item('demo_beneficiary_bank_code_1'),
                'beneficiaryAmount' => $subAccount,
                'deductFeeFrom' => '0',
            ),
        ];
    }

    private function getFinalizePayment($rrr, $transaction_reference, $totalAmount, $service_type = null): array
    {
        $this->load->model('remita');
        $order_id = $transaction_reference;

        $live = isLive();
        $mert = $live ?
            get_setting('remita_merchant_id') :
            $this->config->item('demo_merchant');

        $api_key = $live ?
            get_setting('remita_api_key') :
            $this->config->item('remita_api_key');
        $publicKey = $live ?
            get_setting('remita_public_key') :
            $this->config->item('remita_public_key');

        $finalize_url = $live ? $this->config->item('remita_finalize_url') : $this->config->item('demo_remita_finalize_url');

        $service_type = ($this->config->item('remita_go_live')) ? $service_type : $this->config->item('demo_service_type');

        $url = $live ? $this->config->item('transaction_url') : $this->config->item('demo_transaction_url');
        $new_hash_string = $rrr . $api_key . $mert;
        $new_hash = hash('sha512', $new_hash_string);
        $authString = "remitaConsumerKey=$mert,remitaConsumerToken=$new_hash";
        $header = array(
            'Content-Type: application/json',
            "Authorization:$authString",
        );
        return array('payment_channel' => 'remita', 'channel_name' => 'Remita', 'rrr' => $rrr, 'order_id' => $transaction_reference, 'hash' => $new_hash, 'merchant_id' => $mert, 'finalize_url' => $finalize_url);
    }

    public function getRemitaData($order_id, $student = null, $payment = null, $transaction = null, $userType = 'student', $amount = null, $serviceCharge = null, $serviceType = null): array
    {
        $live = isLive();
        $mert = $live ?
            get_setting('remita_merchant_id') :
            $this->config->item('demo_merchant');

        $api_key = $live ?
            get_setting('remita_api_key') :
            $this->config->item('remita_api_key');
        $publicKey = $live ?
            get_setting('remita_public_key') :
            $this->config->item('remita_public_key');

        $finalize_url = $live ? $this->config->item('remita_finalize_url') : $this->config->item('demo_remita_finalize_url');

        $url = $live ?
            $this->config->item('split_payment_url') :
            $this->config->item('demo_split_payment_url');

        $curlStatus = true;
        $data = [];
        $aRecord = null;
        $description = null;

        // prepping for transaction initiation
        if ($payment) {
            $service_type = null;
            if ($serviceType) {
                $service_type = $serviceType;
            } else {
                $service_type = $live ? $payment->service_type_id : $this->config->item('demo_service_type');
            }

            $totalAmount = $amount ?: $payment->amount + $payment->service_charge;
            $serviceCharge = $serviceCharge ?: $payment->service_charge;
            $concatString = $mert . $service_type . $order_id . $totalAmount . $api_key;
            $hash = hash('sha512', $concatString);
            $authString = "remitaConsumerKey=$mert,remitaConsumerToken=$hash";
            $header = array(
                'Content-Type: application/json',
                "Authorization:$authString",
            );

            if ($userType == 'student') {
                $aRecord = $student->academic_record;
                $matricLabel = "matric number";
                $matric = removeNonCharacter($aRecord->matric_number);
                $description = trim($payment->getFeeDescription());
            } else {
                // this is for applicant
                $matricLabel = "applicant ID";
                $matric = removeNonCharacter($student->applicant_id);
                $description = trim($payment->getFeeDescription($payment->description));
            }
            $fullname = removeNonCharacter($student->lastname) . ' Remita.php' . removeNonCharacter($student->firstname);
            $payerEmail = $student->user_login ?? $student->alternative_email;
            $phone = decryptData($this, removeNonCharacter($student->phone));
            $data = array(
                "serviceTypeId" => $service_type,
                'amount' => round($totalAmount),
                'orderId' => $order_id,
                'payerName' => $fullname,
                'payerEmail' => removeNonCharacter($payerEmail),
                'payerPhone' => $phone,
                'description' => $description,
            );
        }

        // using this for curl GET method if transaction exists
        if ($transaction != null) {
            $retry_hash = $order_id . $api_key . $mert; // for existing transaction
            $transactionUrl = $live ? $this->config->item('transaction_url') : $this->config->item('demo_transaction_url');
            $hash = hash('sha512', $retry_hash);
            $authString = "remitaConsumerKey=$mert,remitaConsumerToken=$hash";
            $header = array(
                'Content-Type: application/json',
                "Authorization:$authString",
            );
            $url = $transactionUrl . $mert . '/' . $order_id . '/' . $hash . '/' . $this->config->item('trans_ref_endpoint');
            $data = [
                'url' => $url,
            ];
            $curlStatus = false;
        }

        $transactionId = uniqid('', true);
        $extraData = [
            'url' => $url,
            'mert' => $mert,
            'api_key' => $api_key,
            'finalize_url' => $finalize_url,
            'transaction_id' => $transactionId,
            'info' => [
                'matric_number' => @$aRecord->matric_number ?: '',
                'student_id' => @$student->id ?: '',
            ],
            'user_type' => 'students',
            'description' => $description,
            'orderID' => $order_id,
        ];

        return [
            'data' => $data,
            'header' => $header,
            'public_api' => $publicKey,
            'extraData' => $extraData,
            'curlStatus' => $curlStatus,
        ];
    }

    public function getCustomRemitaData($order_id, $users, $param = null): array
    {
        $live = isLive();
        $mert = $live ?
            get_setting('remita_merchant_id') :
            $this->config->item('demo_merchant');
        $api_key = $live ?
            get_setting('remita_api_key') :
            $this->config->item('remita_api_key');
        $publicKey = $live ?
            get_setting('remita_public_key') :
            $this->config->item('remita_public_key');

        $finalize_url = $live ? $this->config->item('remita_finalize_url') : $this->config->item('demo_remita_finalize_url');
        $url = $live ?
            $this->config->item('split_payment_url') :
            $this->config->item('demo_split_payment_url');
        $curlStatus = true;
        $data = [];
        $header = [];
        $description = null;

        if ($param) {
            $serviceType = @$users->service_type_id ?: $this->config->item('custom_service_type');
            $service_type = $live ? $serviceType : $this->config->item('demo_service_type');
            $totalAmount = $param['total'];
            $serviceCharge = $param['serviceCharge'];
            $concatString = $mert . $service_type . $order_id . $totalAmount . $api_key;
            $hash = hash('sha512', $concatString);
            $authString = "remitaConsumerKey=$mert,remitaConsumerToken=$hash";
            $header = array(
                'Content-Type: application/json',
                "Authorization:$authString",
            );

            $matricLabel = (isset($users->matric)) ? ' matric number' : "User Email";
            $matric = (isset($users->matric)) ? removeNonCharacter($users->matric) : removeNonCharacter($users->email);
            $description = $param['description'];
            $splitAccountTotal = $param['splitAccountTotal'];
            $subAccount = $param['subAccount'];
            $phone = decryptData($this, removeNonCharacter($users->phone_number));
            $data = array(
                "serviceTypeId" => $service_type,
                'amount' => round($totalAmount),
                'orderId' => $order_id,
                'payerName' => removeNonCharacter($users->name),
                'payerEmail' => removeNonCharacter($users->email),
                'payerPhone' => $phone,
                'description' => $description,
            );
        }

        // using this for curl GET method
        if ($param == null) {
            $retry_hash = $order_id . $api_key . $mert; // for existing transaction
            $transactionUrl = $live ? $this->config->item('transaction_url') : $this->config->item('demo_transaction_url');
            $hash = hash('sha512', $retry_hash);
            $authString = "remitaConsumerKey=$mert,remitaConsumerToken=$hash";
            $header = array(
                'Content-Type: application/json',
                "Authorization:$authString",
            );
            $url = $transactionUrl . $mert . '/' . $order_id . '/' . $hash . '/' . $this->config->item('trans_ref_endpoint');
            $data = [
                'url' => $url,
            ];
            $curlStatus = false;
        }

        $transactionId = uniqid('', true);
        $extraData = [
            'url' => $url,
            'mert' => $mert,
            'api_key' => $api_key,
            'finalize_url' => $finalize_url,
            'transaction_id' => $transactionId,
            'info' => [
                'matric_number' => removeNonCharacter($users->email) ?: '',
                'student_id' => @$users->id ?: '',
            ],
            'user_type' => 'non-students',
            'description' => $description,
            'orderID' => $order_id,
        ];

        return [
            'data' => $data,
            'header' => $header,
            'public_api' => $publicKey,
            'extraData' => $extraData,
            'curlStatus' => $curlStatus,
        ];
    }

    public function getRemitaDetails($student, $payment, $transaction)
    {
        $order_id = $transaction->transaction_ref;
        $transactionRef = $transaction->transaction_ref;

        // this should always return curlStatus as false, useful for just querying
        $temp = $this->getRemitaData($order_id, $student, $payment, $transactionRef);
        $extraData = $temp['extraData'];
        $transactionId = $extraData['transaction_id'];
        if (!$temp['curlStatus']) {
            $response = $this->remitaTransactionDetails($temp['data']['url'], $temp['header']);
            if ($response['status'] == '021' || $response['status'] != '01' || $response['status'] != '00' || $response['status'] != '023') {
                $rrr = (isset($response[RemitaResponse::RRR])) ? trim($response[RemitaResponse::RRR]) : '';

                if ($rrr == '') {
                    return [
                        "status" => false,
                        "message" => "Error generating Remita Retrieval Reference (RRR Not present in request), Please try again later",
                    ];
                } else {
                    if ($transaction) {
                        if (isset($response[RemitaResponse::RRR]) && $response[RemitaResponse::RRR] != '') {
                            $transaction->rrr_code = $response[RemitaResponse::RRR];
                        }
                        $transaction->transaction_id = $transactionId;
                        if (!$transaction->update()) {
                            return [
                                "status" => false,
                                "message" => "Error occured while processing. Please try again",
                            ];
                        }
                        $datePerformed = formatPaymentDate($transaction->date_performed);
                        $dateCompleted = formatPaymentDate($transaction->date_completed);
                    }
                    return [
                        "status" => true,
                        'rrr' => $rrr,
                        'order_id' => $order_id,
                        'transaction_id' => $transactionId,
                        'date_performed' => $datePerformed,
                        'date_completed' => $dateCompleted,
                    ];
                }
            } else {
                return [
                    "status" => false,
                    "message" => "Error generating Remita Retrieval Reference, Please try again later",
                ];
            }
        }
    }

    private function getRemitaTransactionResponse(string $order_id, array $temp, array $extraData, ?object $transaction): array
    {
        $response = $this->remitaTransactionDetails($temp['data']['url'], $temp['header']);
        if ($response['status'] == '021' || $response['status'] != '01' || $response['status'] != '00' || $response['status'] != '023') {
            $rrr = (isset($response[RemitaResponse::RRR])) ? trim($response[RemitaResponse::RRR]) : '';
            $new_hash_string = $extraData['mert'] . $rrr . $extraData['api_key'];
            $new_hash = hash('sha512', $new_hash_string);
            $datePerformed = null;
            $dateCompleted = null;

            if ($rrr == '') {
                $errorMessage = " 'Apex::Remita:Error' -> " . @$response['responseCode'] . ":" . @$response['responseMsg'];
                $error = "Remita Service Unavailable: Error generating Remita Retrieval Reference, Please try again later";
                log_message('info', $errorMessage);

                if (!empty($extraData['user_type'])) {
                    $academic = $extraData['info'];
                    $userType = $extraData['user_type'];
                    $description = $extraData['description'];
                    $orderID = $extraData['orderID'];
                    $errorData = json_encode([
                        'response_error' => $error,
                        'response_code' => @$response['responseCode'],
                        'response_msg' => @$response['responseMsg'],
                        'user_type' => $userType,
                        'payment_description' => $description ?? '',
                        'transaction_ref' => $orderID,
                        'raw_data' => $response,
                    ]);
                    logAction($this, 'remita_payment_error', $academic['matric_number'], $academic['student_id'], null, $errorData);
                }

                return [
                    "status" => false,
                    "message" => $error,
                ];
            } else {
                if ($transaction) {
                    if (isset($response[RemitaResponse::RRR]) && $response[RemitaResponse::RRR] != '') {
                        $transaction->rrr_code = $response[RemitaResponse::RRR];
                    }
                    $transaction->transaction_id = $extraData['transaction_id'];
                    if (!$transaction->update()) {
                        return [
                            "status" => false,
                            "message" => "Error occurred while processing. Please try again",
                        ];
                    }
                    $datePerformed = formatPaymentDate($transaction->date_performed);
                    $dateCompleted = formatPaymentDate($transaction->date_completed);
                }
                return [
                    "status" => true,
                    'rrr' => $rrr,
                    'order_id' => $order_id,
                    'transaction_id' => $extraData['transaction_id'],
                    'date_performed' => $datePerformed,
                    'date_completed' => $dateCompleted,
                    'transaction_obj' => $transaction,
                ];
            }
        } else {
            return [
                "status" => false,
                "message" => "Error generating Remita Retrieval Reference, Please try again later",
            ];
        }
    }

    /**
     * DO NOT REMOVE - THIS IS A REDUNDANCY METHOD USEFUL WHEN YOU WANNA VERIFY
     * TRANSACTION USING JUST THE TRANSACTION_REF ALONE
     */
    public function getcustomRemitaDetails($users, $transaction)
    {
        $order_id = $transaction->transaction_ref;
        $transactionRef = $transaction->transaction_ref;

        // this should always return curlStatus as false, useful for just querying
        $temp = $this->getCustomRemitaData($order_id, $users);
        $extraData = $temp['extraData'];
        $transactionId = $extraData['transaction_id'];
        if (!$temp['curlStatus']) {
            return $this->getRemitaTransactionResponse($order_id, $temp, $extraData, $transaction);
        }
    }

    /**
     * This initiate payment transaction if not exist and return transaction if
     * it already existed
     * @param $student
     * @param $payment
     * @param $transaction
     * @param null $amount
     * @param null $serviceCharge
     * @param null $preselectedPack
     * @return array [type]                [description]
     */
    public function initPayment($student, $payment, $transaction, $amount = null, $serviceCharge = null, $preselectedPack = null): array
    {
        // it is important to know that for the CURL GET METHOD to be called, transaction_ref must exist
        // else a fresh one would be generated and create another transaction

        $order_id = $transaction ? $transaction->transaction_ref : orderID();
        $transactionRef = $transaction ? $transaction->transaction_ref : null;

        $temp = $this->getRemitaData($order_id, $student, $payment, $transactionRef, 'student', $amount, $serviceCharge);

        // validate the data to know when to use curl GET method and return the
        // transaction that already existed on REMITA
        $extraData = $temp['extraData'];
        $transactionId = $extraData['transaction_id'];
        if (!$temp['curlStatus']) {
            return $this->getRemitaTransactionResponse($order_id, $temp, $extraData, $transaction);
        }

        // this would mean that the transaction doesn't exist
        $data = $temp['data'];
        $header = $temp['header'];
        $url = $temp['extraData']['url'];
        $jsonData = json_encode($data);
        $response = $this->remitaTransactionPost($url, $header, $jsonData);
        if (empty($response) || $response['statuscode'] != '025') {
            return [
                "status" => false,
                "message" => "Error generating Remita Retrieval Reference, Please try again later",
            ];
        }

        // it means there is no transaction and only then should a new one be created
        if (!$transaction) {
            $datePerformed = date('Y-m-d H:i:s');
            $result = $this->createTransaction($data, $payment, $student, $response, $datePerformed, $serviceCharge, $preselectedPack, $transactionId);
            if (!$result) {
                return [
                    "status" => false,
                    'message' => 'Unable to initiate transaction, please try again later',
                ];
            }
        }
        $rrr = trim($response['RRR']);
        $datePerformed = date_format(date_create($datePerformed), "M. d, Y");
        $dateCompleted = null;
        return [
            "status" => true,
            'rrr' => $rrr,
            'order_id' => $order_id,
            'transaction_id' => $transactionId,
            'date_performed' => $datePerformed,
            'date_completed' => $dateCompleted,
        ];
    }

    public function applicantInit($applicant, $payment, $transaction, $order_id): array
    {
        $transactionRef = $transaction ? $transaction->transaction_ref : null;

        $temp = $this->getRemitaData($order_id, $applicant, $payment, $transactionRef, 'applicants');
        // validate the data to know when to use curl post method
        $extraData = $temp['extraData'];
        if (!$temp['curlStatus']) {
            $response = $this->remitaTransactionDetails($temp['data']['url'], $temp['header']);
            if ($response['status'] == '021' || $response['status'] != '01' || $response['status'] != '00' || $response['status'] != '023') {
                $rrr = (isset($response[RemitaResponse::RRR])) ? trim($response[RemitaResponse::RRR]) : '';
                $new_hash_string = $extraData['mert'] . $rrr . $extraData['api_key'];
                $new_hash = hash('sha512', $new_hash_string);

                if ($rrr == '') {
                    return ["status" => false, "message" => "Error generating Remita Retrieval Reference (RRR Not present in request), Please try again later"];
                } else {
                    if ($transaction) {
                        $transaction->rrr_code = $response[RemitaResponse::RRR];
                        if (!$transaction->update()) {
                            return ["status" => false, "message" => "Error occurred while processing. Please try again"];
                        }
                    }

                    return ["status" => true, 'payment_channel' => 'remita', 'channel_name' => 'Remita', 'rrr' => $rrr, 'order_id' => $order_id, 'hash' => $new_hash, 'merchant_id' => $extraData['mert'], 'finalize_url' => $extraData['finalize_url']];
                }
            } else {
                return ["status" => false, "message" => "Error generating Remita Retrieval Reference, Please try again later"];
            }
        }
        $data = $temp['data'];
        $header = $temp['header'];
        $url = $temp['extraData']['url'];
        $jsonData = json_encode($data);

        $response = $this->remitaTransactionPost($url, $header, $jsonData);
        if ($response['statuscode'] != '025') {
            return ["status" => false, "message" => "Error generating Remita Retrieval Reference, Please try again later"];
        }

        $rrr = trim($response['RRR']);
        $result = $this->updateTransaction($rrr, $order_id);
        if (!$result) {
            return ["status" => false, 'message' => 'Error updating your transaction'];
        }
        $new_hash_string = $rrr . $extraData['api_key'] . $extraData['mert'];
        $new_hash = hash('sha512', $new_hash_string);
        return ["status" => true, 'payment_channel' => 'remita', 'channel_name' => 'Remita', 'rrr' => $rrr, 'order_id' => $order_id, 'hash' => $new_hash, 'merchant_id' => $extraData['mert'], 'finalize_url' => $extraData['finalize_url']];
    }

    /**
     * @throws Exception
     */
    public function customInitPayment(object $users, array $param, ?object $transaction = null): array
    {
        $order_id = ($param['requery']) ? $transaction->transaction_ref : orderID();
        if (!$param['requery']) {
            $totalAmount = $param['amount'];
            $splitAccountTotal = ($totalAmount * PaymentPercentage::MAIN_ACCOUNT);
            $subAccount = ($totalAmount * PaymentPercentage::SUB_ACCOUNT);
        } else {
            $totalAmount = $transaction->mainaccount_amount + $transaction->subaccount_amount;
            $splitAccountTotal = $transaction->mainaccount_amount;
            $subAccount = $transaction->subaccount_amount;
        }

        if ($param) {
            $param['splitAccountTotal'] = $splitAccountTotal;
            $param['subAccount'] = $subAccount;
            $param['paymentOption'] = 1;
        }

        $transactionParam = $param['requery'] ? null : $param;
        $temp = $this->getCustomRemitaData($order_id, $users, $transactionParam);
        $extraData = $temp['extraData'];
        $transactionId = $extraData['transaction_id'];

        if (!$temp['curlStatus']) {
            return $this->getRemitaTransactionResponse($order_id, $temp, $extraData, $transaction);
        }
        $data = $temp['data'];
        $header = $temp['header'];
        $url = $temp['extraData']['url'];
        $jsonData = json_encode($data);
        $realTransactionId = null;

        $response = $this->remitaTransactionPost($url, $header, $jsonData);
        if (empty($response) || $response['statuscode'] != '025') {
            return ["status" => false, "message" => "Error generating Remita Retrieval Reference, Please try again later"];
        }

        if ($transaction) {
            $transaction->rrr_code = $response['RRR'];
            $transaction->transaction_id = $transactionId;
            if (!$transaction->update()) {
                return ["status" => false, "message" => "Error occured while processing transaction. Please try again"];
            }
        } else {
            $datePerformed = date('Y-m-d H:i:s');
            $serviceCharge = $param['serviceCharge'];

            if ($param['transaction_type'] == 'non-student') {
                $result = $this->createTransactionCustom($data, $param, $users, $response, $datePerformed, $serviceCharge, $transactionId);
                if (!$result) {
                    return ["status" => false, 'message' => 'Error creating the transaction'];
                }
                $realTransactionId = $result->id;
            } else if ($param['transaction_type'] == 'top_up') {
                $result = $this->createTopTransaction($data, $param, $users, $response, $datePerformed, $serviceCharge, $transactionId);
                if (!$result) {
                    return ["status" => false, 'message' => 'Error creating the transaction'];
                }
                $realTransactionId = $result->id;
                $transactionObj = $result;
            }
        }
        $rrr = trim($response['RRR']);
        $datePerformed = date_format(date_create($datePerformed), "M. d, Y");
        $dateCompleted = null;
        return ["status" => true, 'rrr' => $rrr, 'order_id' => $order_id, 'transaction_id' => $transactionId,
            'date_performed' => $datePerformed, 'date_completed' => $dateCompleted,
            'orig_transaction_id' => $realTransactionId, 'transaction_obj' => $transactionObj ?? null];
    }

    public function partInitPayment(object $student, object $payment, array $paymentParam, $transaction = null): array
    {
        // it is important to know that for the CURL GET METHOD to be called, transaction_ref must exist
        // else a fresh one would be generated and create another transaction
        $order_id = $transaction ? $transaction->transaction_ref : orderID();
        $transactionRef = $transaction ? $transaction->transaction_ref : null;

        $amount = $paymentParam['amount'];
        $serviceCharge = $paymentParam['serviceCharge'];
        $paymentOption = $paymentParam['paymentOption'];
        $serviceTypeId = isLive() ? $this->config->item('custom_service_type') : $this->config->item('demo_service_type');

        $temp = $this->getRemitaData($order_id, $student, $payment, $transactionRef, 'student', $amount, $serviceCharge, $serviceTypeId);

        // validate the data to know when to use curl GET method and return the
        // transaction that already existed on REMITA
        $extraData = $temp['extraData'];
        $transactionId = $extraData['transaction_id'];
        if (!$temp['curlStatus']) {
            return $this->getRemitaTransactionResponse($order_id, $temp, $extraData, $transaction);
        }
        // this would mean that the transaction doesn't exist
        $data = $temp['data'];
        $data['description'] = $paymentParam['payment_description'];

        $header = $temp['header'];
        $url = $temp['extraData']['url'];
        $jsonData = json_encode($data);
        $response = $this->remitaTransactionPost($url, $header, $jsonData);

        if (empty($response) || $response['statuscode'] != '025') {
            return ["status" => false, "message" => "Error generating Remita Retrieval Reference, Please try again later"];
        }

        // it means there is no transaction and only then should a new one be created
        if (!$transaction) {
            $datePerformed = date('Y-m-d H:i:s');
            $result = $this->createTransactionPart($data, $payment, $student, $response, $datePerformed, $paymentParam, $transactionId);
            if (!$result) {
                return ["status" => false, 'message' => 'Error creating your transaction'];
            }
        }
        $rrr = trim($response['RRR']);
        $datePerformed = date_format(date_create($datePerformed), "M. d, Y");
        $dateCompleted = null;
        return ["status" => true, 'rrr' => $rrr, 'order_id' => $order_id, 'transaction_id' => $transactionId, 'date_performed' => $datePerformed, 'date_completed' => $dateCompleted];
    }

    private function createTransaction($data, $payment, $student, $response, $datePerformed, $serviceCharge = null, $preselectedPack = null, $transaction = null)
    {
        loadClass($this->load, 'transaction');
        loadClass($this->load, 'sessions');
        $aRecord = $student->academic_record;
        $session = $payment->session != 0 ? $payment->session : $aRecord->current_session;
        $paymentID = $payment->description;
        $paymentLevel = $aRecord->current_level;
        $activeSession = get_setting('active_session_student_portal');
        $excludeSundry = [
            PaymentFeeDescription::ACCEPTANCE_FEE,
            PaymentFeeDescription::OUTSTANDING_22
        ];
        if (isSundryPayment($paymentID) && !in_array($paymentID, $excludeSundry)) {
            $session = $activeSession;
        }

        if ((PaymentFeeDescription::SCH_FEE_FIRST == $paymentID || PaymentFeeDescription::SCH_FEE_SECOND == $paymentID) &&
            $session != $activeSession) {
            $paymentLevel = CommonTrait::inferPreviousLevel($aRecord->entry_mode, $aRecord->current_level);
        }

        $sess = $this->sessions->getWhere(['id' => $session], $c, 0, null, false);
        $session_name = $sess ? $sess[0]->date : '';
        $subAccount = $payment->subaccount_amount ?? 0;
        $serviceCharge = $serviceCharge ?: $payment->service_charge;
        $mainAmount = $data['amount'] - $serviceCharge - $subAccount;
        $description = ($payment->fee_category == 1 && !isSundryPayment($paymentID)) ? $payment->getFeeDescription() . ' Remita.php' . $session_name : $payment->getFeeDescription();
        $param = [
            'payment_id' => $paymentID,
            'real_payment_id' => $payment->id,
            'payment_description' => $description,
            'payment_option' => $payment->options,
            'student_id' => $student->id,
            'programme_id' => $aRecord->programme_id,
            'session' => $session,
            'level' => $paymentLevel,
            'transaction_ref' => $data['orderId'],
            'rrr_code' => $response['RRR'],
            'service_charge' => $serviceCharge,
            'total_amount' => $data['amount'],
            'subaccount_amount' => $subAccount,
            'date_performed' => $datePerformed,
            'preselected_payment' => ($preselectedPack && $preselectedPack != 0) ? $preselectedPack : '',
            'transaction_id' => $transaction,
            'mainaccount_amount' => $mainAmount,
            'payment_status' => '021',
        ];
        $trans = new Transaction($param);
        if (!$trans->insert()) {
            return false;
        }
        $result = $this->transaction->getWhere(['transaction_ref' => $data['orderId']], $c, 0, null, false);
        return $result[0];
    }

    private function createTransactionPart($data, $payment, $student, $response, $datePerformed, $paymentParam, $transaction = null)
    {
        loadClass($this->load, 'transaction');
        $aRecord = $student->academic_record;
        $session = $payment->session != 0 ? $payment->session : $aRecord->current_session;
        $paymentLevel = $aRecord->current_level;
        $activeSession = get_setting('active_session_student_portal');

        $serviceCharge = $paymentParam['serviceCharge'];
        $totalAmount = $data['amount'] - $serviceCharge;
        $mainAmount = ($totalAmount * PaymentPercentage::MAIN_ACCOUNT);
        $subAccount = ($totalAmount * PaymentPercentage::SUB_ACCOUNT);

        $description = $paymentParam['payment_description'];
        $paymentID = $payment->description;

        if ((PaymentFeeDescription::SCH_FEE_FIRST == $paymentID || PaymentFeeDescription::SCH_FEE_SECOND == $paymentID) &&
            $session != $activeSession) {
            $paymentLevel = CommonTrait::inferPreviousLevel($aRecord->entry_mode, $aRecord->current_level);
        }

        $param = [
            'payment_id' => $paymentID,
            'real_payment_id' => $payment->id,
            'payment_description' => $description,
            'payment_option' => paymentOptionsType($paymentParam['paymentOption']),
            'student_id' => $student->id,
            'programme_id' => $aRecord->programme_id,
            'session' => $session,
            'level' => $paymentLevel,
            'transaction_ref' => $data['orderId'],
            'rrr_code' => $response['RRR'],
            'service_charge' => $serviceCharge,
            'total_amount' => $data['amount'],
            'subaccount_amount' => $subAccount,
            'date_performed' => $datePerformed,
            'preselected_payment' => '',
            'transaction_id' => $transaction,
            'mainaccount_amount' => $mainAmount,
            'payment_status' => '021',
        ];
        $trans = new Transaction($param);
        if (!$trans->insert()) {
            return false;
        }
        $result = $this->transaction->getWhere(['transaction_ref' => $data['orderId']], $c, 0, null, false);
        return $result[0];
    }

    /**
     * @throws Exception
     */
    private function createTransactionCustom($data, $param, $users, $response, $datePerformed, $serviceCharge, $transaction)
    {
        loadClass($this->load, 'transaction_custom');
        $subAccount = $param['subAccount'];
        $mainAmount = $param['splitAccountTotal'];
        $currentSession = get_setting('active_session_student_portal');

        $param = [
            'payment_description' => $param['description'],
            'payment_option' => $param['paymentOption'],
            'custom_users_id' => $users->id,
            'transaction_ref' => $data['orderId'],
            'rrr_code' => $response['RRR'],
            'service_charge' => $serviceCharge,
            'total_amount' => $param['total'],
            'subaccount_amount' => $subAccount,
            'date_performed' => $datePerformed,
            'transaction_id' => $transaction,
            'mainaccount_amount' => $mainAmount,
            'payment_status' => '021',
            'payment_id' => $param['payment_id'],
            'start_date' => $param['startDate'],
            'end_date' => $param['endDate'],
            'session' => $currentSession,
        ];
        $trans = new Transaction_custom($param);
        if (!$trans->insert()) {
            return false;
        }
        $result = $this->transaction_custom->getWhere(['transaction_ref' => $data['orderId']], $c, 0, null, false);
        return $result[0];
    }

    private function createTopTransaction($data, $param, $users, $response, $datePerformed, $serviceCharge, $transaction)
    {
        loadClass($this->load, 'transaction');
        $subAccount = $param['subAccount'];
        $mainAmount = $param['splitAccountTotal'];
        $paymentOption = @$param['paymentOption'] ?: 2;
        $param = [
            'payment_id' => $param['payment_id'],
            'real_payment_id' => $param['real_payment_id'],
            'payment_description' => $param['description'],
            'payment_option' => $paymentOption,
            'student_id' => $users->id,
            'programme_id' => $users->programme_id,
            'session' => $users->session,
            'level' => $users->current_level,
            'transaction_ref' => $data['orderId'],
            'rrr_code' => $response['RRR'],
            'service_charge' => $serviceCharge,
            'total_amount' => $data['amount'],
            'subaccount_amount' => $subAccount,
            'date_performed' => $datePerformed,
            'preselected_payment' => null,
            'transaction_id' => $transaction,
            'mainaccount_amount' => $mainAmount,
            'payment_status' => '021',
        ];
        $trans = new Transaction($param);
        if (!$trans->insert()) {
            return false;
        }
        $result = $this->transaction->getWhere(['transaction_ref' => $data['orderId']], $c, 0, null, false);
        return $result[0];
    }

    private function updateTransaction($rrr, $ref)
    {
        $query = "update applicant_transaction set rrr_code=? where transaction_ref=?";
        $result = $this->db->query($query, array($rrr, $ref));
        return $result;
    }

    private function getCacheAuthToken()
    {
        $this->load->driver('cache', array('adapter' => 'file'));
        if ($token = $this->cache->get('rits_token_auth')) {
            return $token;
        }

        return null;
    }

    private function cacheAuthToken($data, $expiresIn = 3600)
    {
        $this->load->driver('cache', array('adapter' => 'file'));

        if (!$token = $this->cache->get('rits_token_auth')) {
            $this->cache->save('rits_token_auth', $data, $expiresIn); // 1hr
        }
        return $token;
    }

    private function prepRemitaInterHeader(string $headerType): array
    {
        $live = isLive('outflow');
        $mert = $live ? get_setting('remita_merchant_id') : $this->config->item('rits_demo_merchantId');
        $requestID = generateCode(17);
        $apiKey = $live ? get_setting('apiKey') : $this->config->item('rits_demo_apiKey');
        $apiToken = $live ? get_setting('apiToken') : $this->config->item('rits_demo_apiToken');
        $api_details_hash = $apiKey . $requestID . $apiToken;
        $new_hash = hash('sha512', $api_details_hash);
        // $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $timestamp = str_replace('+00:00', '+0000', gmdate('c'));
        $header = [];
        if ($headerType == 'bank') {
            $header = [
                'Content-Type: application/json',
                "API_KEY: $apiKey",
                "REQUEST_ID: $requestID",
                "REQUEST_TS: $timestamp",
                "API_DETAILS_HASH: $new_hash",
            ];
        } else if ($headerType == 'acccount') {
            $header = [
                'Content-Type: application/json',
                "MERCHANT_ID:  $mert",
                "API_KEY: $apiKey",
                "REQUEST_ID: $requestID",
                "REQUEST_TS: $timestamp",
                "API_DETAILS_HASH: $new_hash",
            ];
        }

        $url = $live ? $this->config->item('rits_live_url') : $this->config->item('rits_demo_url');

        return [
            'url' => $url,
            'header' => $header,
        ];
    }

    private function getAuthToken()
    {
        $live = isLive('outflow');
        $url = $live ? $this->config->item('remita_fund_live_url') : $this->config->item('remita_fund_demo_url');
        $url = $url . "/uaasvc/uaa/token";
        $username = $live ? get_setting('remita_outflow_username') : $this->config->item('demo_rits_username');
        $password = $live ? get_setting('remita_outflow_password') : $this->config->item('demo_rits_password');
        $param = [
            'username' => $username,
            'password' => $password,
        ];
        $response = $this->remitaServicePost($url, [
            'Content-Type: application/json',
        ], json_encode($param));

        if (empty($response) || @$response['status'] != '00') {
            return ["status" => false, "message" => "Error occurred while validating account"];
        }

        $this->cacheAuthToken($response['data'][0]['accessToken'], $response['data'][0]['expiresIn']);
        return $response['data'][0]['accessToken'];
    }

    private function prepRemitaAuthToken(): array
    {
        $live = isLive('outflow');
        $accessToken = $this->getCacheAuthToken() ?: $this->getAuthToken();
        $header = [
            'Content-Type: application/json',
            "Authorization: Bearer $accessToken",
        ];

        $url = $live ? $this->config->item('remita_fund_live_url') : $this->config->item('remita_fund_demo_url');

        return [
            'url' => $url,
            'header' => $header,
        ];
    }

    private function aesEncryption($data)
    {
        $live = isLive('outflow');
        $cipher = "aes-128-cbc";

        // Generate a 256-bit encryption key
        $encryption_key = $live ? openssl_random_pseudo_bytes(32) : "nbzjfdiehurgsxct";

        // Generate an initialization vector
        $iv_size = openssl_cipher_iv_length($cipher);
        $iv = $live ? openssl_random_pseudo_bytes($iv_size) : "sngtmqpfurxdbkwj";

        return openssl_encrypt($data, $cipher, $encryption_key, 0, $iv);
    }

    public function getActiveBanks()
    {
        $headerData = $this->prepRemitaAuthToken();
        $url = $headerData['url'];
        $header = $headerData['header'];

        $url = $url . "/rpgsvc/v3/rpg/banks";
        $response = $this->remitaServiceGet($url, $header);
        if (empty($response) || $response['status'] != '00') {
            return ["status" => false, "message" => "Error fetching active banks"];
        }

        return $response['data']['banks'];
    }

    public function getAccountNameEnquiry($accountNumber, $code)
    {
        $headerData = $this->prepRemitaAuthToken();
        $url = $headerData['url'];
        $header = $headerData['header'];

        $url = $url . "/rpgsvc/v3/rpg/account/lookup";
        $param = [
            'sourceAccount' => $accountNumber,
            'sourceBankCode' => strlen($code) < 3 ? "0" . $code : $code,
        ];
        $response = $this->remitaServicePost($url, $header, json_encode($param));
        $message = null;
        if (!empty($response)) {
            if (isset($response['responseData'])) {
                $message = json_decode($response['responseData'], true);
                $message = @$message['error'] . ':' . @$message['error_description'];
            }

            if (isset($response['status']) && in_array($response['status'], ['02', '07', '16', '99'])) {
                $message = "Invalid account details";
            } else if ($response['status'] != '00') {
                $message = @$response['message'] ?: "Unable to validate your account. Please try again later";
            }

            if (isset($response['status']) && $response['status'] == '00') {
                return $response['data'];
            }
        }
        $message = $message ?: "Unable to process your request. Please try again later";
        return ['status' => false, 'message' => $message];
    }

    public function remitaTransactionFundTransfer(array $data)
    {
        $headerData = $this->prepRemitaAuthToken();
        $url = $headerData['url'];
        $header = $headerData['header'];

        $url = $url . "/rpgsvc/v3/rpg/bulk/payment";
        $response = $this->remitaServicePost($url, $header, json_encode($data));
        $message = null;
        if (!empty($response)) {
            if (isset($response['responseData'])) {
                $message = json_decode($response['responseData'], true);
                $message = $message['error'] . ':' . $message['error_description'];
            }

            if (isset($response['status'])) {
                if (empty($response) || $response['status'] != '00') {
                    $message = @$response['message'] ?? "Error occurred while processing transfer operation";
                }
            }

            if (isset($response['status']) && $response['status'] == '00') {
                return $response;
            }
        }

        $message = $message ?: "Error occurred while performing operation, please try again later";
        return ["status" => false, "message" => $message];
    }

    public function remitaTransactionStatus(string $batchRef): array
    {
        $headerData = $this->prepRemitaAuthToken();
        $url = $headerData['url'];
        $header = $headerData['header'];

        $url = $url . "/rpgsvc/v3/rpg/bulk/payment/status/" . $batchRef;
        $response = $this->remitaServiceGet($url, $header);
        if (empty($response) || @$response['status'] != '00') {
            return ["status" => false, "message" => "Error occurred while processing transfer"];
        }

        return ['status' => true, 'message' => $response];
    }

    public function tester()
    {
        print('load this method');
    }

}
