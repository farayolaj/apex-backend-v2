<?php

namespace App\Controllers\Student\V1;

use App\Controllers\BaseController;
use App\Entities\Courses;
use App\Enums\CacheEnum;
use App\Libraries\ApiResponse;
use App\Libraries\EntityLoader;
use App\Models\WebSessionManager;
use App\Services\Admin\CourseSettingsService;
use App\Services\GoogleDriveStorageService;
use App\Services\Student\CourseService;
use App\Support\Cache\ShowCacheSupport;
use Config\Services;

class CoursesController extends BaseController
{
    private Courses $courses;
    private CourseService $svc;

    public function __construct()
    {
        $this->courses = EntityLoader::loadClass(null, 'courses');
        $this->svc = service('course');
    }

    public function courseDetails($id)
    {
        $result = $this->courses->getDetails($id);
        $currentSession = get_setting('active_session_student_portal');
        $courseSettingsService = new CourseSettingsService();
        $courseSettings = $courseSettingsService->getSettings($result['main_course_id'], $currentSession);
        $courseSettings['course_guide'] = $courseSettings['course_guide_url'];
        unset($courseSettings['course_guide_url']);
        $result = array_merge($result, $courseSettings);

        if (isset($result['main_course_id'])) {
            $result['course_room_url'] = Services::courseRoomModel()->getCourseRoomLink(
                $result['main_course_id']
            );
        }

        return ApiResponse::success('success', $result);
    }

    public function enrollment($session = null, $semester = null)
    {
        try {
            if (!in_array($semester, ['first', 'second'], true)) {
                return ApiResponse::error('Please provide a valid semester');
            }

            $payload = $this->svc->getEnrollment($session, $semester);
            return ApiResponse::success('success', $payload);
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    public function stats()
    {
        $semester = $this->request->getGet('semester');
        $session  = trim((string)$this->request->getGet('session') ?? '') ?: null;
        $student = WebSessionManager::currentAPIUser();
        try {
            $payload = ShowCacheSupport::remember(
                CacheEnum::STUDENT_STATS->value,
                600,
                fn() => $this->svc->getStats($session, $semester),
                $student->id,
                [$session, $semester]
            );

            return ApiResponse::success('success', $payload);
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    public function config()
    {
        try {
            $student = WebSessionManager::currentAPIUser();
            $payload = ShowCacheSupport::remember(
                CacheEnum::STUDENT_CONFIG->value,
                1800,
                fn() => $this->svc->getCourseConfig(),
                $student->id
            );

            return ApiResponse::success('success', $payload);
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    public function preload($semester = null)
    {

        if (!in_array($semester, ['first', 'second'], true)) {
            return ApiResponse::error('Please provide a valid semester');
        }

        // TODO: CAUTION:  The team lead said we should allow courses to show but
        // won't be able to register if payment for that session is not made
        $semester = ($semester == 'first') ? 1 : 2;
        try {
            $payload = $this->svc->listAllCourses($semester);
            return ApiResponse::success('success', $payload);
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        } catch (\Throwable $e) {
            log_message('error', 'course.preload: {m}', ['m' => $e->getMessage()]);
            return ApiResponse::error('Unable to load courses', null, 500);
        }
    }

    /**
     * This search for course less or equal to the student current level
     */
    public function search()
    {
        $q = trim((string)($this->request->getGet('course') ?? ''));
        if ($q === '') {
            return ApiResponse::error('Please provide a search name', null, 400);
        }

        try {
            $courses = $this->svc->searchCourses($q);
            return ApiResponse::success('success', $courses);
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        } catch (\Throwable $e) {
            log_message('error', 'course.search: {m}', ['m' => $e->getMessage()]);
            return ApiResponse::error('Unable to search courses', null, 500);
        }
    }

    public function isOpen()
    {
        $semester = $this->request->getGet('semester');
        try {
            $this->svc->assertRegistrationOpen($semester, false);
            return ApiResponse::success('Course configuration is now available');
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    public function isDeleteOpen()
    {
        $semester = $this->request->getGet('semester');
        try {
            $this->svc->assertRegistrationOpen($semester, true);
            return ApiResponse::success('Course configuration is now available');
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    public function register()
    {
        $payload = requestPayload();
        $courses = $payload['data'] ?? null;

        if (!$courses) {
            return ApiResponse::error('No course was selected', null, 422);
        }

        if (!is_array($courses)) {
            return ApiResponse::error('Invalid course format', null, 422);
        }

        try {
            $this->svc->registerCourses($courses);
            return ApiResponse::success('Your selected courses has been registered successfully');
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage());
        } catch (\Throwable $e) {
            log_message('error', 'coursereg.register: {m}', ['m' => $e->getMessage()]);
            return ApiResponse::error('Course could not be registered at the moment, try again later', null, 500);
        }
    }

    public function unregister()
    {
        $payload = requestPayload();
        $courses = $payload['data'] ?? null;

        if (!$courses) {
            return ApiResponse::error('No course was selected');
        }

        if (!is_array($courses)) {
            return ApiResponse::error('Invalid course format');
        }

        try {
            $this->svc->unregisterCourses($courses);
            return ApiResponse::success('Your selected courses has been deleted successfully');
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage());
        } catch (\Throwable $e) {
            log_message('error', 'coursereg.unregister: {m}', ['m' => $e->getMessage()]);
            return ApiResponse::error('Course could not be deleted at the moment, try again later', null, 500);
        }
    }

    public function sessions()
    {
        try {
            $payload = $this->svc->getAllPaidSessionsWithActive();
            return ApiResponse::success('success', $payload);
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        }
    }

    public function tourEnrollment()
    {
        try {
            $res = $this->svc->createTourEnrollment();
            return ApiResponse::success('Student enrolled successfully', $res);
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage());
        } catch (\Throwable $e) {
            log_message('error', 'students.tour.create: {m}', ['m' => $e->getMessage()]);
            return ApiResponse::error('Student cannot be enrolled, something went wrong!');
        }
    }

    public function tourEnrollmentRemoved()
    {
        try {
            $this->svc->removeTourEnrollment();
            return ApiResponse::success('Student unenrolled successfully');
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage());
        } catch (\Throwable $e) {
            log_message('error', 'students.tour.delete: {m}', ['m' => $e->getMessage()]);
            return ApiResponse::error('Student cannot be unenrolled, something went wrong!');
        }
    }
}
