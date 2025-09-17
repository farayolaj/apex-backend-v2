<?php

namespace App\Controllers\Student\V1;

use App\Controllers\BaseController;
use App\Entities\Matrix_rooms;
use App\Libraries\ApiResponse;
use App\Libraries\EntityLoader;
use App\Models\Admin\CourseRoomModel;
use App\Models\WebSessionManager;

class ProfileController extends BaseController
{
    public function index()
    {
        EntityLoader::loadClass($this, 'student_verification_fee');
        $student = WebSessionManager::currentAPIUser();
        $academicRecord = $student->academic_record;
        $student->updatePassportPath();
        $student->orientation_attendance_date = $student->getFacultyAttendance($student, $academicRecord->programme_id);
        $result = $student->toArray() ?? null;
        $result['phone'] = decryptData($result['phone']);
        $academicRecord = $academicRecord->toArray() ?? null;
        $studentLevel = $academicRecord['current_level'];
        if ($academicRecord['current_level']) {
            $academicRecord['current_level'] = formatStudentLevel($academicRecord['current_level']);
        }
        unset($result['user_pass'], $result['password'], $result['id']);
        $currentSemester = get_setting('active_semester');
        $result['current_session'] = get_setting('active_session_student_portal');
        $result['current_semester'] = $currentSemester;
        $result['academicRecord'] = $academicRecord;
        $result['has_upload_verification_doc'] = strtolower($result['document_verification']) === 'pending';
        $result['has_paid_olevel_verification'] = $this->student_verification_fee->hasStudentPaidOlevelVerification($student->id);
        $result['programmeDetails'] = $student->getProgramDetails() ?? null;
        $result['is_finalist'] = isGraduate($studentLevel, $academicRecord['entry_mode']);
        $result['is_extraYear'] = isCarryOverGraduate($studentLevel);

        /**
         * @var Matrix_rooms $matrixRooms
         */
        $matrixRooms = EntityLoader::loadClass(null, 'matrix_rooms');
        $room = $matrixRooms->getByExternalId('university');
        $result['university_room_url'] = $room && isset($result['matrix_id']) && $result['matrix_id'] ? CourseRoomModel::getRoomLink($room['room_id']) : null;

        return ApiResponse::success('success', $result);
    }

    public function update()
    {
        $student = WebSessionManager::currentAPIUser();
        EntityLoader::loadClass($this, 'students');
        $postData = request()->getRawInput();

        // Define allowed fields
        $allowedFields = ['phone', 'alternative_mail', 'contact_address'];
        $data = [];

        // Validate and collect data
        foreach ($allowedFields as $field) {
            if (!isset($postData[$field])) {
                continue;
            }

            $key = $field === 'alternative_mail' ? 'alternative_email' : $field;
            $value = $postData[$field];

            if ($field === 'alternative_mail') {
                $count = 0;
                $studentId = $student->id;
                $existingEmail = $student->query(
                    "SELECT * FROM students WHERE alternative_email = ? AND id != ?",
                    [$value, $studentId]
                );

                if ($existingEmail) {
                    return ApiResponse::error("Personal email address is already in use by another user");
                }
            }

            $student->$key = $value;
            $data[$key] = $value;
        }

        // Only proceed if there's data to update
        if (empty($data)) {
            return ApiResponse::error("No valid fields to update");
        }

        $status = $student->update(null);

        if (!$status) {
            return ApiResponse::error("Cannot update information");
        }

        $payload = $student->toArray();
        unset($payload['password'], $payload['user_pass']);

        return ApiResponse::success("Information updated successfully", $payload);
    }

    public function dashboard()
    {
        $student = WebSessionManager::currentAPIUser();
        $dashboardInfo = $student->getDashboardData();
        return ApiResponse::success('success', $dashboardInfo);
    }

    public function updatePassword()
    {
        $student = WebSessionManager::currentAPIUser();
        $validation = \Config\Services::validation();

        $rules = [
            'current_password' => [
                'label' => 'Current password',
                'rules' => 'required|trim',
                'errors' => [
                    'required' => 'Current password is required'
                ]
            ],
            'user_pass' => [
                'label' => 'New password',
                'rules' => 'required|trim|min_length[6]',
                'errors' => [
                    'required' => 'New password is required',
                    'min_length' => 'Password must be at least 6 characters long'
                ]
            ]
        ];

        if (!$validation->setRules($rules)->run($this->request->getRawInput())) {
            $error = $validation->getErrors();
            return ApiResponse::error(reset($error));
        }

        $data = $this->request->getRawInput();
        $current = $data['current_password'];
        $newPassword = $data['user_pass'];

        if (!password_verify($current, $student->password)) {
            return ApiResponse::error('Incorrect current password');
        }
        $student->password = encode_password($newPassword);
        if (!$student->update()) {
            return ApiResponse::error('Unable to update password, please try again');
        }

        return ApiResponse::success('Password updated successfully');
    }
}
