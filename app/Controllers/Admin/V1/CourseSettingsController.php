<?php

namespace App\Controllers\Admin\V1;

use App\Controllers\BaseController;
use App\Libraries\ApiResponse;
use App\Services\Admin\CourseSettingsService;

class CourseSettingsController extends BaseController
{
    private CourseSettingsService $settingsService;

    public function __construct()
    {
        $this->settingsService = new CourseSettingsService();
    }

    /**
     * Get course settings for a specific course and session
     * GET /v1/web/courses/{sessionId}/{courseId}/settings
     */
    public function getSettings(int $sessionId, int $courseId)
    {
        $settings = $this->settingsService->getSettings($courseId, $sessionId);

        return ApiResponse::success('Course settings retrieved successfully', $settings);
    }

    /**
     * Create or update course settings
     * PUT /v1/web/courses/{sessionId}/{courseId}/settings
     */
    public function upsertSettings(int $sessionId, int $courseId)
    {
        $payload = requestPayload();

        $rules = [
            'overview' => 'permit_empty|string|max_length[2000]',
            'mission' => 'permit_empty|string|max_length[2000]',
            'objectives' => 'permit_empty|string|max_length[2000]',
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            return ApiResponse::error(reset($errors), null, 400);
        }

        try {
            $success = $this->settingsService->upsertSettings($courseId, $sessionId, $payload);

            if (!$success) {
                return ApiResponse::error('Failed to save course settings', null, 500);
            }

            $settings = $this->settingsService->getSettings($courseId, $sessionId);
            return ApiResponse::success('Course settings saved successfully', $settings);
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        } catch (\Throwable $e) {
            log_message('error', 'CourseSettings.upsertSettings: {message}', ['message' => $e->getMessage()]);
            return ApiResponse::error('Unable to save course settings', null, 500);
        }
    }

    /**
     * Upload course guide document
     * POST /v1/web/courses/{sessionId}/{courseId}/course_guide
     */
    public function uploadCourseGuide(int $sessionId, int $courseId)
    {
        // Validate file upload
        $rules = [
            'course_guide' => [
                'label' => 'Course Guide Document',
                'rules' => implode('|', [
                    'uploaded[course_guide]',
                    'max_size[course_guide,10240]', // 10MB
                    'mime_in[course_guide,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document]',
                    'ext_in[course_guide,pdf,doc,docx]',
                ]),
            ],
        ];

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            return ApiResponse::error(reset($errors), null, 400);
        }

        $file = $this->request->getFile('course_guide');

        try {
            $result = $this->settingsService->uploadCourseGuide($courseId, $sessionId, $file);
            return ApiResponse::success('Course guide uploaded successfully', $result);
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage(), null, 400);
        } catch (\Throwable $e) {
            log_message('error', 'CourseSettings.uploadCourseGuide: {message}', ['message' => $e->getMessage()]);
            return ApiResponse::error('Unable to upload course guide', null, 500);
        }
    }

    /**
     * Delete course guide document
     * DELETE /v1/web/courses/{sessionId}/{courseId}/course_guide
     */
    public function deleteCourseGuide(int $sessionId, int $courseId)
    {
        $this->settingsService->deleteCourseGuide($courseId, $sessionId);
        return ApiResponse::success('Course guide deleted successfully');
    }
}
