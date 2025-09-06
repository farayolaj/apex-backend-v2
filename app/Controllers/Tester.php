<?php

namespace App\Controllers;

use App\Entities\Courses;
use App\Libraries\ApiResponse;
use App\Libraries\EntityLoader;
use App\Models\Mailer;
use App\Traits\AuthTrait;
use CodeIgniter\Config\Factories;
use Config\Services;
use Phinx\Migration\Manager\Environment;

class Tester extends BaseController
{
    use AuthTrait;

    public function boom()
    {
        throw new \App\Exceptions\ValidationFailedException('Testing JSON handler', 422);
    }
    private function mailTest(){
        $mailer = new Mailer;
        $parser = Services::parser();

        $receiptsData = ['menu_items' => []];
        $receiptsData['menu_items'][] = [
            'item_name' => 'Fee Category',
            'description' => 'Test Case',
            'start_date' => '2025-02-14',
            'end_date' => '2025-02-15',
            'amount' => number_format(500000, 2),
        ];

        $globalVariables = [
            'fullname' => 'John Doe',
            'address' => 'Address' ?? null,
            'contact' => 'Test Test',
            'contact_phone' => '08109994485',
            'RRR' => '12345678',
            'date_initiated' => '2025-02-14 09:32:00',
            'total_amount' => number_format(500000, 2),
        ];
        $variables = array(
            'course' => 'Course Dummy',
            'lecturer_name' => 'John Doe',
            'course_name' => 'Course title',
            'session' => '2023/2024',
            'semester' => 'First',
            'date_of_upload' => date('Y-m-d H:i:s'),
            'progressLog' => 'https://dlcportal.edu.ng',
        );
        $variables = $globalVariables + $receiptsData;
        $recipient = 'holynationdevelopment@gmail.com';
        $html = $parser->setData($variables)->render("print/custom_receipt.html");
        dddump($html);
        $subject = "UIDLC (Fee Category) - Invoice RRR [12345678]";
        if(!$mailer->sendMail('DLC', $recipient, $subject, $html)){
            return ApiResponse::error("Unable to send the invoice via email, please try again");
        }
        return ApiResponse::success("Email sent successfully");
    }

    public function mailTest1(){
        $mailer = new Mailer;
        $variables = array(
            'course' => 'Course Dummy',
            'lecturer_name' => 'John Doe',
            'course_name' => 'Course title',
            'session' => '2023/2024',
            'semester' => 'First',
            'date_of_upload' => date('Y-m-d H:i:s'),
            'progressLog' => 'https://dlcportal.edu.ng',
        );
        $recipient = 'holynationdevelopment@gmail.com';
        $subject = "ATTENTION Notification of result";
        if(!$mailer->sendUploadCopyEmailNotification($recipient, $variables, $subject, ['holynation667@gmail.com'])){
            return ApiResponse::error("Unable to send the invoice via email, please try again");
        }
        return ApiResponse::success("Email sent successfully");
    }

    public function backgroundWorker(): void
    {
        print('Before dispatching');
        $course = new Courses();
        relayQDispatch(
            (new \App\Jobs\SendNotification())
                ->onQueue('notifications')
                ->delay(5)
                ->maxAttempts(3)
                ->backoff([30,120,600])
                ->unique("welcome:1", 60) // unique per 5-minute bucket
        );
        dddump('Running after dispatch');
    }

    public function test(){
        $this->backgroundWorker();
    }
}