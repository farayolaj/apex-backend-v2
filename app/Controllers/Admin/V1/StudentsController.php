<?php

namespace App\Controllers\Admin\V1;

use App\Controllers\BaseController;
use App\Libraries\ApiResponse;
use App\Traits\Crud\EntityListTrait;
use App\Services\Admin\StudentService;

class StudentsController extends BaseController
{
    use EntityListTrait;

    private StudentService $svc;

    public function __construct()
    {
        $this->svc = service('student');
    }

    public function index(){
        $payload = $this->listApiEntity('students');
        return ApiResponse::success(data: $payload);
    }

    public function show(int $id)
    {
        $payload = $this->showListEntity('students', $id);
        return ApiResponse::success(data: $payload);
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

    public function studentAllRegistered()
    {
        $studentId = trim((string)($this->request->getGet('student_id') ?? ''));

        if ($studentId === '') {
            return ApiResponse::error('Please choose a student', null, 400);
        }

        try {
            $payload = $this->svc->getAllRegisteredCourses((int)$studentId);
            return ApiResponse::success('Student courses fetched successfully', $payload);
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        } catch (\Throwable $e) {
            log_message('error', 'admin.studentAllRegistered: {m}', ['m'=>$e->getMessage()]);
            return ApiResponse::error('Unable to fetch courses', null, 500);
        }
    }

    // GET /admin/v1/courses/student/paid-sessions?student_id=123
    public function studentAllPaidSessions()
    {
        $studentId = trim((string)($this->request->getGet('student_id') ?? ''));

        if ($studentId === '') {
            return ApiResponse::error('Please choose a student', null, 400);
        }

        try {
            $sessions = $this->svc->getAllPaidSessions((int)$studentId);
            return ApiResponse::success('success', $sessions);
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        } catch (\Throwable $e) {
            log_message('error', 'admin.studentAllPaidSessions: {m}', ['m'=>$e->getMessage()]);
            return ApiResponse::error('Unable to fetch sessions', null, 500);
        }
    }

    // POST /admin/v1/courses/student/register
    // body: { student_id: int, session: int, courses: [int,int,...] }
    public function registerForStudent()
    {
        $in = $this->request->getJSON(true) ?? $this->request->getPost();

        $studentId = (int)($in['student_id'] ?? 0);
        $session   = (int)($in['session'] ?? 0);
        $courses   = $in['courses'] ?? null;

        if ($studentId <= 0) return ApiResponse::error('Please choose a student', null, 400);
        if ($session   <= 0) return ApiResponse::error('session is required', null, 400);
        if (!is_array($courses)) return ApiResponse::error('Courses cannot be registered, something went wrong!', null, 400);

        try {
            $this->svc->adminRegisterCourses($studentId, $session, $courses);
            return ApiResponse::success('Course registration was successfully', null);
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        } catch (\Throwable $e) {
            log_message('error', 'admin.registerForStudent: {m}', ['m'=>$e->getMessage()]);
            return ApiResponse::error('Courses cannot be registered, something went wrong!', null, 500);
        }
    }

    // DELETE /admin/v1/courses/student/registered
    // body: { student_id, course_id, course_session, course_level }
    public function deleteStudentRegistered()
    {
        $in = $this->request->getJSON(true) ?? $this->request->getPost();

        $studentId = (int)($in['student_id'] ?? 0);
        $courseId  = (int)($in['course_id'] ?? 0);
        $sessionId = (int)($in['course_session'] ?? 0);
        $levelId   = (int)($in['course_level'] ?? 0);

        if ($studentId <= 0) return ApiResponse::error('Please choose a student', null, 400);
        if ($courseId  <= 0) return ApiResponse::error('course is required', null, 400);
        if ($sessionId <= 0) return ApiResponse::error('course session is required', null, 400);
        if ($levelId   <= 0) return ApiResponse::error('course level is required', null, 400);

        try {
            $msg = $this->svc->adminDeleteRegisteredCourse($studentId, $courseId, $sessionId, $levelId);
            return ApiResponse::success($msg, null);
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        } catch (\Throwable $e) {
            log_message('error', 'admin.deleteStudentRegistered: {m}', ['m'=>$e->getMessage()]);
            return ApiResponse::error('An error has occured, course could not be unregistered', null, 500);
        }
    }

}
