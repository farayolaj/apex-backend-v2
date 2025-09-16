<?php

namespace App\Controllers\Admin\V1;

use App\Controllers\BaseController;
use App\Libraries\ApiResponse;
use CodeIgniter\API\ResponseTrait;
use App\Services\Admin\StudentService;

class StudentsController extends BaseController
{
    use ResponseTrait;

    private StudentService $svc;

    public function __construct()
    {
        $this->svc = service('student');
    }

    public function store()
    {
        $in = requestPayload();

        $rules = [
            'entry_mode'           => 'required|string',
            'level'                => 'required|string',
            'current_session'      => 'required|string',
            'lastname'             => 'required|string',
            'firstname'            => 'required|string',
            'gender'               => 'required|string',
            'dob'                  => 'required|string',
            'marital'              => 'required|string',
            'email'                => 'required|valid_email',
            'status'               => 'required',
            'verification_status'  => 'required',
            'entry_year'           => 'required',
            'session_of_admission' => 'required',
            'admission_level'      => 'required',
            'programme'            => 'required',
            'prog_duration'        => 'required',
            'has_matric_number'    => 'required',
            'has_institution_email'=> 'required',
        ];

        if (! $this->validate($rules)) {
            $error = $this->validator->getErrors();
            return ApiResponse::error(reset($error));
        }

        try {
            $res = $this->svc->createStudent($in);
            return ApiResponse::success('Student added successfully', $res);
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage());
        } catch (\Throwable $e) {
            log_message('error','students.create: {m}', ['m'=>$e->getMessage()]);
            return ApiResponse::error('Student cannot be added, something went wrong!');
        }
    }

    public function update(string $id)
    {
        $in = requestPayload();

        $rules = [
            'entry_mode'           => 'required|string',
            'current_session'      => 'required|string',
            'lastname'             => 'required|string',
            'firstname'            => 'required|string',
            'gender'               => 'required|string',
            'dob'                  => 'required|string',
            'marital'              => 'required|string',
            'email'                => 'required|valid_email',
            'status'               => 'required',
            'verification_status'  => 'required',
            'entry_year'           => 'required',
            'session_of_admission' => 'required',
            'admission_level'      => 'required',
            'prog_duration'        => 'required',
        ];

        if (! $this->validate($rules)) {
            $error = $this->validator->getErrors();
            return ApiResponse::error(reset($error));
        }

        try {
            $this->svc->updateStudent((int)$id, $in);
            return ApiResponse::success('Student record updated successfully');
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage());
        } catch (\Throwable $e) {
            log_message('error','students.update: {m}', ['m'=>$e->getMessage()]);
            dddump($e->getMessage());
            return ApiResponse::error('Student cannot be added, something went wrong!', null, 500);
        }
    }
}
