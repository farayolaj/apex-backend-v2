<?php

namespace App\Services\Admin;

use App\Entities\Course_settings;
use App\Entities\Courses;
use App\Libraries\EntityLoader;
use App\Services\GoogleDriveStorageService;
use CodeIgniter\HTTP\Files\UploadedFile;
use Config\Services;
use RuntimeException;

class CourseSettingsService
{
    private Course_settings $courseSettings;
    private Courses $courses;

    public function __construct()
    {
        $this->courseSettings = EntityLoader::loadClass(null, 'course_settings');
        $this->courses = EntityLoader::loadClass(null, 'courses');
    }

    /**
     * Get course settings by course ID and session ID
     */
    public function getSettings(int $courseId, int $sessionId): ?array
    {
        $settings = $this->courseSettings->getByCourseAndSession($courseId, $sessionId);

        $settings['course_guide_url'] = $settings && $settings['course_guide_id'] ?
            GoogleDriveStorageService::getPublicUrl($settings['course_guide_id']) :
            null;
        unset($settings['course_guide_id']);

        return $settings;
    }

    /**
     * Create or update course settings
     * @throws \DomainException if validation fails
     */
    public function upsertSettings(int $courseId, int $sessionId, array $data): bool
    {
        // Validate required fields exist in the request
        $allowedFields = ['overview', 'mission', 'objectives'];
        $filteredData = array_intersect_key($data, array_flip($allowedFields));

        if (empty($filteredData)) {
            throw new \DomainException('At least one field (overview, mission, or objectives) must be provided');
        }

        return $this->courseSettings->upsertSettings($courseId, $sessionId, $filteredData);
    }

    /**
     * Upload course guide document
     * @throws \DomainException if validation fails
     * @throws \RuntimeException on upload or save failure
     */
    public function uploadCourseGuide(int $courseId, int $sessionId, UploadedFile $file): bool
    {
        // Validate file
        if (!$file->isValid() || $file->hasMoved()) {
            throw new \DomainException('Invalid file upload');
        }

        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!in_array($file->getMimeType(), $allowedTypes)) {
            throw new \DomainException('Invalid file type. Only PDF, DOC, and DOCX files are allowed');
        }

        // Move the file to a temporary location
        $tempPath = WRITEPATH . 'uploads/temp/';
        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0755, true);
        }

        $courseCode = $this->courses->getCourseCodeById($courseId);

        $newName = $courseCode . '_course_guide.' . $file->getExtension();
        $mimeType = $file->getMimeType();
        $file->move($tempPath, $newName);
        $fullPath = $tempPath . $newName;

        // Upload to Google Drive
        $storage = Services::gDriveStorage();
        $success = false;

        try {
            $existing = $this->courseSettings->getByCourseAndSession($courseId, $sessionId);
            if ($existing && $existing['course_guide_id']) {
                // Delete the old file if exists
                $storage->deleteFile($existing['course_guide_id']);
            }

            $fileId = $storage->uploadFile(
                $fullPath,
                $mimeType,
                $newName
            );

            $data = ['course_guide_id' => $fileId];
            $success = $this->courseSettings->upsertSettings($courseId, $sessionId, $data);
            if (!$success) {
                // Clean up uploaded file if database update fails
                $storage->deleteFile($fileId);
                throw new \RuntimeException('Failed to save course guide information');
            }
        } catch (\Throwable $e) {
            throw new RuntimeException('Error uploading to Google Drive: ' . $e->getMessage());
        } finally {
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }

        return $success;
    }

    /**
     * Delete course guide document
     */
    public function deleteCourseGuide(int $courseId, int $sessionId): void
    {
        $settings = $this->courseSettings->getByCourseAndSession($courseId, $sessionId);

        if (!$settings || empty($settings['course_guide_id'])) {
            return;
        }

        // Delete file from Google Drive
        $storage = Services::gDriveStorage();
        $storage->deleteFile($settings['course_guide_id']);

        // Update database to remove file ID
        $this->courseSettings->upsertSettings($courseId, $sessionId, ['course_guide_id' => null]);
    }
}
