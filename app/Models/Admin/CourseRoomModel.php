<?php

namespace App\Models\Admin;

use App\Entities\Course_enrollment;
use App\Entities\Course_manager;
use App\Entities\Courses;
use App\Entities\Matrix_rooms;
use App\Entities\Staffs;
use App\Entities\Students;
use App\Libraries\EntityLoader;
use App\Services\MatrixService;
use App\Support\Cache\ShowCacheSupport;
use Config\Services;

class CourseRoomModel
{

    private Course_enrollment $courseEnrollment;
    private Course_manager $courseManager;
    private Courses $courses;
    private Matrix_rooms $matrixRooms;
    private MatrixService $matrixService;
    private Staffs $staffs;
    private Students $students;

    public function __construct()
    {
        $this->courseEnrollment = EntityLoader::loadClass(null, 'course_enrollment');
        $this->courseManager = EntityLoader::loadClass(null, 'course_manager');
        $this->courses = EntityLoader::loadClass(null, 'courses');
        $this->matrixRooms = EntityLoader::loadClass(null, 'matrix_rooms');
        $this->matrixService = Services::matrixService();
        $this->staffs = EntityLoader::loadClass(null, 'staffs');
        $this->students = EntityLoader::loadClass(null, 'students');
    }

    private function getCourseLecturers(string $courseId, string $sessionId): array
    {
        // Get the lecturers assigned to the course in the session
        $courseManagerRes = $this->courseManager->getCourseManagerByCourseId($courseId, $sessionId);
        $lecturerIds = $courseManagerRes ? (json_decode($courseManagerRes['course_lecturer_id'], true) ?? []) : [];

        return $this->staffs->getStaffsByUserIds($lecturerIds);
    }

    private function getCourseStudents(string $courseId, string $sessionId): array
    {
        $studentIds = $this->courseEnrollment->getEnrolledStudents($courseId, $sessionId) ?? [];
        return $this->students->getStudentsByIds($studentIds);
    }

    /**
     * 
     */
    public function createCourseRooms()
    {
        // Get all courses without course rooms
        $courses = $this->courses->getCoursesWithoutRooms();

        // For each course, create a Matrix room and push the room id and course id into an array
        $createdRooms = 0;
        $failedCourses = [];

        foreach ($courses as $course) {
            $roomId = $this->createCourseRoom($course['id'], $course['code'], $course['title']);

            if ($roomId) {
                $createdRooms++;
            } else {
                $failedCourses[] = $course['id'];
            }
        }

        return ['created' => $createdRooms, 'failed' => $failedCourses];
    }

    public function createStudentUsers()
    {
        // Get all active students without matrix accounts
        $students = $this->students->getStudentsWithoutMatrixId();

        // For each student, create a Matrix account and push their user_id and student id into an array
        $userIds = [];
        $failedStudents = [];

        foreach ($students as $student) {
            $username = $student['matric_no'];
            $name = trim($student['firstname'] . ' ' . $student['lastname']);
            $email = $student['email'] ?? null;
            $userCreated = $this->matrixService->createUser($username, $name, $email);

            if ($userCreated) {
                $userIds[] = [
                    'matrix_id' => MatrixService::getUserId($username),
                    'id' => $student['id'],
                ];
            } else {
                $failedStudents[] = $student['id'];
            }
        }

        // Update the matrix_id field in the students table
        $this->students->updateMatrixIds($userIds);

        return ['created' => count($userIds), 'failed' => $failedStudents];
    }

    public function createStaffUsers()
    {
        // Get all active staffs without matrix accounts
        $staffs = $this->staffs->getAllStaffsWithoutMatrixId();

        // For each staff, create a Matrix account and push their user_id and staff id into an array
        $userIds = [];
        $failedStaffs = [];

        foreach ($staffs as $staff) {
            $username = $this->getStaffUsername($staff['firstname'], $staff['lastname']);
            $name = trim($staff['title'] . ' ' . $staff['firstname'] . ' ' . $staff['lastname']);
            $email = $staff['email'] ?? null;
            $userCreated = $this->matrixService->createUser($username, $name, $email);

            if ($userCreated) {
                $userIds[] = [
                    'matrix_id' => MatrixService::getUserId($username),
                    'id' => $staff['id'],
                ];
            } else {
                $failedStaffs[] = $staff['id'];
            }
        }

        // Update the matrix_id field in the staffs table
        $this->staffs->updateMatrixIds($userIds);

        return ['created' => count($userIds), 'failed' => $failedStaffs];
    }

    public function addMembersToCourseRooms()
    {
        // Get all courses with course rooms
        $currentSession = get_setting('active_session_student_portal');
        $courses = $this->courses->getCoursesWithRooms();

        // For each course, get the lecturers and students
        foreach ($courses as $course) {
            $matrixIds = [];
            $lecturers = $this->getCourseLecturers($course['id'], $currentSession);
            $students = $this->getCourseStudents($course['id'], $currentSession);

            $lecturerMatrixIds = array_filter(array_column($lecturers, 'matrix_id'), fn($id) => !empty($id));
            $studentMatrixIds = array_filter(array_column($students, 'matrix_id'), fn($id) => !empty($id));
            $matrixIds = array_merge($matrixIds, $lecturerMatrixIds, $studentMatrixIds);

            // Add the lecturers and students to the course room
            $this->matrixService->addUsersToRoom($course['room_id'], $matrixIds);
        }
    }

    /**
     * @return bool|null Returns true on success, false on failure, and null if the course or room is not found.
     */
    public function addMembersToCourseRoom(string $courseIdOrCode)
    {
        // Get all courses with course rooms
        $currentSession = get_setting('active_session_student_portal');
        $course = $this->courses->getCourse($courseIdOrCode);

        if (!$course || !$course['room_id']) {
            return null;
        }

        // For each course, get the lecturers and students
        $matrixIds = [];
        $lecturers = $this->getCourseLecturers($course['id'], $currentSession);
        $students = $this->getCourseStudents($course['id'], $currentSession);

        $lecturerMatrixIds = array_filter(array_column($lecturers, 'matrix_id'), fn($id) => !empty($id));
        $studentMatrixIds = array_filter(array_column($students, 'matrix_id'), fn($id) => !empty($id));
        $matrixIds = array_merge($matrixIds, $lecturerMatrixIds, $studentMatrixIds);

        // Add the lecturers and students to the course room
        return $this->matrixService->addUsersToRoom($course['room_id'], $matrixIds);
    }

    public function createStudentUser(int $studentId, string $matricNo, string $name, ?string $email = null) // Todo: include picture later
    {
        // Create a Matrix account for the student and update the matrix_id field in the students table
        $userCreated = $this->matrixService->createUser($matricNo, $name, $email);
        if ($userCreated) {
            $matrixId = MatrixService::getUserId($matricNo);
            $this->students->updateMatrixId($studentId, $matrixId);
            return $matrixId;
        } else {
            return null;
        }
    }

    public function createStaffUser(int $id, string $title, string $firstName, string $lastName, ?string $email = null)
    {
        // Create a Matrix account for the staff and update the matrix_id field in the staffs table
        $username = $this->getStaffUsername($firstName, $lastName);
        $name = trim($title . ' ' . $firstName . ' ' . $lastName);
        $userCreated = $this->matrixService->createUser($username, $name, $email);
        if ($userCreated) {
            $matrixId = MatrixService::getUserId($username);
            $this->staffs->updateMatrixId($id, $matrixId);
            return $matrixId;
        } else {
            return null;
        }
    }

    public function createCourseRoom(string $courseId, string $courseCode, string $courseTitle)
    {
        // Create a Matrix room for the course and insert into the matrix_rooms table
        $roomId = $this->matrixService->createCourseRoom($courseCode, $courseTitle);

        if ($roomId) {
            $this->matrixRooms->create($roomId, 'course', (int)$courseId);
            return $roomId;
        } else {
            return null;
        }
    }

    public function addMemberToCourseRoom(string $courseId, string $matrixId)
    {
        // Add a member to the course room
        $room = $this->matrixRooms->getByEntityId($courseId, 'course');

        if ($room) {
            return $this->matrixService->addUserToRoom($room['room_id'], $matrixId);
        }

        return null;
    }

    private function getStaffUsername(string $firstName, string $lastName): string
    {
        return $firstName . '.' . $lastName . '.' . random_int(1000, 9999);
    }

    public function getCourseRoomLink(string $courseId)
    {
        $cache = ShowCacheSupport::cache();
        $cacheTtl = 3600;
        $key = ShowCacheSupport::buildCacheKey('course_room_link-' . $courseId);
        $cached = $cache->get($key);

        if ($cached !== null && is_string($cached)) {
            return $cached;
        }

        $room = $this->matrixRooms->getByEntityId($courseId, 'course');

        if (!$room) {
            return null;
        }

        $roomLink = rtrim(env('MATRIX_CLIENT_URL'), '/') . '/#/room/' . urlencode($room['room_id']);
        $ssoLink = env('MATRIX_HOMESERVER')
            . '/_matrix/client/v3/login/sso/redirect/'
            . env('MATRIX_OIDC_PROVIDER')
            . '?redirectUrl='
            . urlencode($roomLink)
            . '&org.matrix.msc3824.action=login';

        $cache->save($key, $ssoLink, $cacheTtl);

        return $ssoLink;
    }
}
