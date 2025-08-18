<?php

namespace App\Traits;

use App\Libraries\ApiResponse;
use App\Libraries\EntityLoader;
use Config\Services;

trait ApiModelTrait
{
    /**
     * Fetches the result for a student.
     */
    public function studentListResult()
    {
        helper('custom');
        $request = Services::request();
        $validation = Services::validation();

        permissionAccess('student_view_result');
        $studentID = $request->getGet('student');

        $data = [
            'student' => $studentID,
        ];

        $validation->setRules([
            'student' => [
                'label' => 'student',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Please choose a student',
                ],
            ],
        ]);

        if (!$validation->run($data)) {
            $errors = $validation->getErrors();
            return ApiResponse::error(reset($errors));
        }

        EntityLoader::loadClass($this,'students');
        $this->students->id = $studentID;
        if (!$this->students->load()) {
            return ApiResponse::error('Invalid student info');
        }

        if (!$this->students->getClosestSessionId()) {
            return ApiResponse::error('Student has no year of entry');
        }

        $record = $this->students->getStudentResults();
        return ApiResponse::success('Result fetched successfully', $record);
    }

    /**
     * Fetches the result statement for a student.
     */
    public function studentStatementResult()
    {
        helper('custom');
        $request = Services::request();
        $validation = Services::validation();

        permissionAccess('student_view_result');
        $studentID = $request->getGet('student');

        $data = [
            'student' => $studentID,
        ];

        $validation->setRules([
            'student' => [
                'label' => 'student',
                'rules' => 'required',
                'errors' => [
                    'required' => 'Please choose a student',
                ],
            ],
        ]);

        if (!$validation->run($data)) {
            $errors = $validation->getErrors();
            return ApiResponse::error(reset($errors));
        }

        EntityLoader::loadClass($this,'students');
        $this->students->id = $studentID;
        if (!$this->students->load()) {
            return ApiResponse::error('Invalid student info');
        }

        if (!$this->students->getClosestSessionId()) {
            return ApiResponse::error('Student has no year of entry');
        }

        $record = $this->students->getStudentViewRecord();
        $result = $this->students->getStudentResultStatement();
        $record['passport'] = $this->students->updatePassportPath();;
        $payload = [
            'details' => $record,
            'result_record' => $result,
        ];

        return ApiResponse::success('Result Statement fetched successfully', $payload);
    }

}