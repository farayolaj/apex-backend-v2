<?php

namespace App\Traits;

use App\Libraries\EntityLoader;
use App\Enums\PaymentFeeDescriptionEnum as PaymentFeeDescription;
use App\Traits\CommonTrait;

trait StudentTrait
{
    use CommonTrait;

    protected function processSinglePaymentPrerequisite(object $payment, object $academic_record, $session, $level, bool $isVisible = false, $acceptanceSession = null): ?array
    {
        Entityloader::loadClass($this, 'sessions');
        Entityloader::loadClass($this, 'fee_description');
        Entityloader::loadClass($this, 'sessions');
        $isVisiblePassed = true;
        $showPayment = true;
        $acceptanceSession = $acceptanceSession ?: get_setting('session_semester_payment_start');
        $isPaymentFull = true;
        $studentID = $academic_record->student_id;

        if ($isVisible) {
            $isVisiblePassed = $payment->is_visible == 1;
        }
        if ($isVisiblePassed && $payment) {
            $newSession = $payment->session != 0 ? $payment->session : $session;
            $preqDesc = $this->transformPrerequisiteDesc($payment->description, $newSession);
            // check globally if acceptance fee had been paid irrespective of the paid session
            if (PaymentFeeDescription::ACCEPTANCE_FEE->value == $payment->description) {
                $session = null;
                $level = null;
            }
            $paidTransaction = false;
            $checkPaymentTransaction = $this->payment->getPaymentTransaction($payment->description, $studentID, $session, $level);
            $paidTransaction = $checkPaymentTransaction && $isPaymentFull;
            $paidTransactionID = $paidTransaction ? $checkPaymentTransaction->id : null;
            $prerequisiteFee = hashids_encrypt($payment->id);
            $preqDesc = $prerequisiteFee ? $preqDesc : null;

            if ($checkPaymentTransaction && !isPaymentComplete($checkPaymentTransaction->payment_option)) {
                $isPaymentFull = false;
                $preqDesc .= ($preqDesc) ? " Balance" : null;
                $paymentCode = inferPaymentCode($payment->description);
                $checkPaymentTransaction = $this->payment->getPaymentTransaction($payment->description, $studentID, $session, null, $paymentCode);
                $paidTransaction = $checkPaymentTransaction ? true : false;
                $paidTransactionID = $paidTransaction ? $checkPaymentTransaction->id : null;
            }

            if ($payment->description == PaymentFeeDescription::ACCEPTANCE_FEE->value && $acceptanceSession > $academic_record->year_of_entry) {
                $showPayment = false;
            }

            if ($showPayment) {
                return [
                    'prerequisite' => @$prerequisiteFee ?? 0,
                    'description' => $preqDesc,
                    'paid' => $paidTransaction,
                    'paid_id' => $paidTransactionID,
                ];
            }
        }

        return null;
    }

    /**
     * @param mixed $academic_record
     * @param mixed $prerequisite_fee
     * @param mixed $session
     * @param mixed $level
     * @param mixed $isVisible
     * @return array<int,array<string,mixed>>
     */
    public function transformPaymentPrerequisiteParam($academic_record, $prerequisite_fee, $session, $level = null, $isVisible = false): array
    {
        EntityLoader::loadClass($this, 'payment');
        $prerequisites = [];
        $acceptanceSession = get_setting('session_semester_payment_start');

        $c = 0;
        foreach ($prerequisite_fee as $preq) {
            $payment = $this->payment->getWhere(['id' => $preq], $c, 0, null, false);
            $payment = @$payment[0];
            $preqResult = $this->processSinglePaymentPrerequisite($payment, $academic_record, $session, $level, $isVisible, $acceptanceSession);
            if ($preqResult) {
                $prerequisites[] = $preqResult;
            }
        }

        return $prerequisites;
    }

    protected function transformPrerequisiteDesc($description, $session): ?string
    {
        $c = 0;
        $preqFee = $description != 0 ? $this->fee_description->getWhere(['id' => $description], $c, 0, null, false) : null;
        $prerequisiteDesc = $preqFee ? $preqFee[0]->description : null;
        $sess = $this->sessions->getWhere(['id' => $session], $c, 0, null, false);
        $session_name = $sess ? " - " . $sess[0]->date : '';
        return $prerequisiteDesc . $session_name;
    }

    protected function prepPaymentGroupParam($payments, $session, $amount = null): array
    {
        EntityLoader::loadClass($this, 'payment');
        $result = [];
        $c = 0;

        $paymentsArr = is_array($payments) ? $payments : json_decode($payments, true);
        if (empty($paymentsArr)) {
            return $result;
        }

        foreach ($paymentsArr as $item) {
            $payment = $this->payment->getWhere(['id' => $item], $c, 0, null, false);
            $payment = $payment[0];
            $newSession = $payment->session != 0 ? $payment->session : $session;
            $paymentParam = $this->prepPaymentAmount($payment, $newSession);

            $payload = [
                'payment_id' => hashids_encrypt($payment->id),
                'description' => $paymentParam['description'],
                'payment_type' => ($payment->fee_category == 1) ? 'Main' : 'Sundry',
                'payment_option' => paymentOptionsType($payment->options, true),
                'amount' => $paymentParam['totalAmount'],
            ];

            if ($amount == null || $amount > $paymentParam['totalAmount']) {
                $result[] = $payload;
            }

        }

        return $result;
    }

    /**
     * @param mixed $prerequisite_fee
     * @param mixed $session
     * @param mixed $payment
     * @param mixed $isVisible
     * @param string|null $transactionRef
     * @param string|null $paymentDesc
     * @return array|null
     * @throws \Exception
     */
    public function transformDirectTransactionPaymentParam($prerequisite_fee, $session, array $payment, bool $isVisible = false, ?string $transactionRef = null, ?string $paymentDesc = null): ?array
    {
        EntityLoader::loadClass($this, 'sessions');
        $preqDesc = $paymentDesc ?: $this->transformPrerequisiteDesc($prerequisite_fee, $session);
        $isVisiblePassed = true;

        if ($isVisible) {
            $isVisiblePassed = $payment['is_visible'] == 1;
        }
        $prerequisite_fee = hashids_encrypt($payment['id']);

        if ($isVisiblePassed) {
            return [
                'transaction_ref' => $transactionRef ?: null,
                'payment_transaction' => "trans_normal",
                'prerequisite' => @$prerequisite_fee ?? 0,
                'description' => $prerequisite_fee ? $preqDesc : null,
                'paid' => false,
                'paid_id' => null,
            ];
        }
        return null;
    }

    public function transformDirectPreqTransactionParam(array $transaction, $prerequisite_fee, $session): array
    {
        EntityLoader::loadClass($this, 'sessions');
        $preqDesc = @$transaction['payment_description'] ?: $this->transformPrerequisiteDesc($prerequisite_fee, $session);
        $prerequisite_fee = hashids_encrypt($transaction['real_payment_id']);

        return [
            'transaction_ref' => @$transaction['transaction_ref'] ?: null,
            'payment_transaction' => "trans_normal",
            'prerequisite' => @$prerequisite_fee ?? 0,
            'description' => $prerequisite_fee ? $preqDesc : null,
            'paid' => false,
            'paid_id' => null,
        ];
    }

    /**
     * @param mixed $prerequisite_fee
     * @param mixed $session
     * @param mixed $transactionRef
     * @return array<string,mixed>
     */
    public function transformDirectPaymentParam($prerequisite_fee, $session, $transactionRef = null, $paymentDesc = null): array
    {
        EntityLoader::loadClass($this, 'sessions');
        $preqDesc = null;
        if ($paymentDesc) {
            $preqDesc = $paymentDesc;
        } else {
            $preqDesc = $this->transformPrerequisiteDesc($prerequisite_fee, $session);
        }
        $paymentId = $this->payment->getPaymentByDescription($prerequisite_fee);
        $prerequisite_fee = hashids_encrypt(@$paymentId['id']);

        return [
            'transaction_ref' => $transactionRef ?? null,
            'payment_transaction' => "trans_normal",
            'prerequisite' => @$prerequisite_fee ?? 0,
            'description' => $prerequisite_fee ? $preqDesc : null,
            'paid' => false,
            'paid_id' => null,
            'session' => $session
        ];
    }

    public function prepStudentPartPaymentParam($payment, $transaction): array
    {
        $paidTransaction = false;
        $paymentTypeOption = paymentOptionsType($transaction ? $transaction['payment_option'] : $payment->options, true);
        if ($transaction && self::isPaymentValid($transaction['payment_status'])) {
            $paidTransaction = true;
        }

        $preselectedFee = null;
        $preselectedFeeAmount = 0;
        $totalAmount = $transaction['total_amount'];

        $paymentType = ($payment->fee_category == 1) ? 'Main' : 'Sundry';
        $enablePayment = 0;
        if ($paymentType == 'Main') {
            $enablePayment = (get_setting('disable_all_school_fees') == 0) ? 1 : 0;
        }

        if ($paymentType == 'Sundry') {
            $enablePayment = (get_setting('disable_all_sundry_fees') == 0) ? 1 : 0;
        }

        if ($enablePayment == 1) {
            $enablePayment = $payment->status;
        }

        return [
            'payment_id' => hashids_encrypt($payment->id),
            'transaction_ref' => $transaction['transaction_ref'],
            'payment_transaction' => "trans_normal",
            'payment_code' => $transaction['payment_description'],
            'payment_code2' => $transaction['payment_id'],
            'level' => $transaction['level'],
            'session' => $transaction['session'],
            'prerequisites' => [],
            'preselected' => 0,
            'preselected_fee_readable' => $preselectedFee,
            'preselected_amount' => $preselectedFeeAmount,
            'fee_category' => $payment->fee_category,
            'fee_category_readable' => $paymentType,
            'description' => $transaction['payment_description'],
            'amount' => $totalAmount,
            'penalty_fee' => 0,
            'service_charge' => $transaction['service_charge'],
            'total_fee_service_charge' => $totalAmount,
            'total' => $totalAmount,
            'date_due' => null,
            'paid' => $paidTransaction,
            'paid_id' => $paidTransaction ? $transaction['id'] : null,
            'is_active' => $enablePayment,
            'is_visible' => $payment->is_visible,
            'payment_category' => strtolower($paymentType),
            'transaction_payment_id' => $paidTransaction ? $transaction['payment_id'] : null,
            'date_performed' => $transaction['date_performed'] ?? null,
            'date_completed' => $transaction['date_completed'] ?? null,
            'transactionID' => $transaction['transaction_id'] ?? null,
            'transaction_rrr' => $transaction['rrr_code'] ?? null,
            'payment_type_option' => $paymentTypeOption,
            'payment_group' => $this->prepPaymentGroupParam($payment->payment_group, $transaction['session'], $totalAmount),
        ];
    }


    /**
     * @param mixed $transactionPaymentSession
     * @return array<string,mixed>
     */
    public function prepPaymentAmount(object $payment, $transactionPaymentSession = null): array
    {
        $toReturn = [];
        $c = 0;

        $feeDesc = $this->fee_description->getWhere(['id' => $payment->description], $c, 0, null, false);
        $sess = $this->sessions->getWhere(['id' => $transactionPaymentSession], $c, 0, null, false);

        $description = $feeDesc ? $feeDesc[0]->description : null;
        $session_name = $sess ? $sess[0]->date : null;

        $date_due = null;
        $penalty_fee = 0;
        if (@$payment->date_due != '') {
            $dueDateParam = $payment->getFormatDueDateParam($payment);
            $penalty_fee = (int)$dueDateParam[0];
            $date_due = $dueDateParam[1];
        }
        $serviceCharge = (int)$payment->service_charge;
        $originalAmount = (int)$payment->amount;

        // if($discountAmount = $payment->validateVerificationFee($academic_record,$payment->id)){
        // 	if($discountAmount){
        // 		$originalAmount = $discountAmount;
        // 	}
        // }

        if ($payment->subaccount_amount) {
            $originalAmount += $payment->subaccount_amount;
        }

        $originalAmountService = $originalAmount + $serviceCharge;
        $totalAmount = ($originalAmountService + $penalty_fee);

        $toReturn['description'] = $transactionPaymentSession ? $description . " - " . $session_name : $description;
        $toReturn['penalty_fee'] = $penalty_fee;
        $toReturn['date_due'] = $date_due;
        $toReturn['serviceCharge'] = $serviceCharge;
        $toReturn['originalAmount'] = $originalAmount;
        $toReturn['originalAmountService'] = $originalAmountService;
        $toReturn['totalAmount'] = $totalAmount;
        $toReturn['descriptionCode'] = $feeDesc ? $feeDesc[0]->code : null;

        return $toReturn;
    }

    public function studentHasOutstanding($isGraduated = false){
        $outstanding = $this->getOutstandingFees($isGraduated);
        if (!empty($outstanding)) {
            foreach ($outstanding as $item) {
                if (!$item['paid']) {
                    return isset($item['description']) ? 'Action required [outstanding fee]: ' . $item['description'] : "Complete payment without any outstanding is required for this action";
                }
            }
        }
    }


}