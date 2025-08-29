<?php

namespace App\Controllers\Admin\V1;

use App\Controllers\BaseController;
use App\Libraries\ApiResponse;
use App\Libraries\EntityLoader;
use CodeIgniter\Config\Factories;

class PaymentController extends BaseController
{

    public function prerequisitesFee(){
        EntityLoader::loadClass($this, 'payment');
        $model = model('Api/AdminModel');
        $payments = $model->getAllPayments();

        $content = [];
        if ($payments) {
            foreach ($payments as $payment) {
                $item = [
                    'id' => $payment['id'],
                    'value' => $payment['description'],
                ];
                $content[] = $item;
            }
        }
        return ApiResponse::success('success', $content);
    }
}