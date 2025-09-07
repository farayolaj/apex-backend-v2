<?php

namespace App\Controllers\Admin\V1;

use App\Controllers\BaseController;
use App\Entities\Courses;
use App\Exceptions\ValidationFailedException;
use App\Libraries\ApiResponse;
use App\Libraries\EntityLoader;
use App\Traits\Crud\EntityListTrait;
use App\Traits\ExportTrait;
use Config\Services;
use Throwable;

class CoursesController extends BaseController
{
    use EntityListTrait, ExportTrait;

    public Courses $courses;

    public function __construct()
    {
        $this->courses = EntityLoader::loadClass(null, 'courses');
    }

    public function index()
    {
        $payload = $this->listApiEntity('courses');
        return ApiResponse::success(data: $payload);
    }

    public function show(int $id)
    {
        $payload = $this->showListEntity('courses', $id);
        return ApiResponse::success(data: $payload);
    }

    /**
     * @throws Throwable
     */
    public function store()
    {
        $course = new \App\Entities\Courses();

        $payload = $this->request->getPost();

        $row = $course->insertSingle(
            $payload ?? [],
            $this->request->getFiles() ?? []
        );
        if (!$row) return ApiResponse::error("Unable to create course");

        return ApiResponse::success('Course inserted successfully', $payload);
    }

    /**
     * @throws Throwable
     */
    public function update($id)
    {
        $course = new \App\Entities\Courses();
        $payload = $this->request->getRawInput();

        $row = $course->updateSingle(
            $id,
            $payload ?? [],
        );
        if (!$row) return ApiResponse::error("Unable to update course");

        return ApiResponse::success('Course updated successfully', $payload);
    }

    /**
     * @throws Throwable
     */
    public function delete($id)
    {
        $course = new \App\Entities\Courses();
        $row = $course->deleteSingle($id);
        if (!$row) return ApiResponse::error("Unable to delete course");

        return ApiResponse::success('Course deleted successfully');
    }

    public function import()
    {
        $course = new \App\Entities\Courses();

        $rules = [
            'course_file' => [
                'label' => 'CSV file',
                'rules' => implode('|', [
                    'uploaded[course_file]',
                    'max_size[course_file,1024]',
                    'mime_in[course_file,text/plain,text/csv,application/csv,application/vnd.ms-excel,text/tab-separated-values]',
                    'ext_in[course_file,csv,txt,tsv]',
                ]),
            ],
        ];

        if (! $this->validate($rules)) {
            $errors = $this->validator->getErrors();
            return ApiResponse::error(reset($errors));
        }
        $file = $this->request->getFile('course_file');

        // Optional: preload a dictionary for fast upsert matching (CODE|DEPT_ID → id)
        $courseIdByKey = [];
        $rows = $this->db->table('courses')
            ->select('id, UPPER(code) as code')
            ->get()->getResultArray();
        foreach ($rows as $r) {
            $courseIdByKey[$r['code']] = (int)$r['id'];
        }

        $preprocessRow = function (array $row): array {
            static $dept = [];
            $db = $this->db;

            if (isset($row['code'])) $row['code'] = strtoupper(trim((string)$row['code']));
            if (!empty($row['department_code'])) {
                $c = strtoupper(trim((string)$row['department_code']));
                if (!isset($dept[$c])) {
                    $dept[$c] = (int)$db->table('department')->select('id')->where('code', $c)->get()->getRow('id');
                }
                if ($dept[$c] <= 0) {
                    throw new ValidationFailedException("department_code: unknown department '{$c}'.");
                }
                $row['department_id'] = $dept[$c];
            }
            unset($row['department_code']);
            return $row;
        };

        // Callback: fast match for update/upsert
        $finder = function (array $row) use (&$courseIdByKey): ?int {
            $code = strtoupper((string)($row['code'] ?? ''));
            if ($code === '') return null;
            return $courseIdByKey[$code] ?? null;
        };

        $logFile = 'bulk_courses_log_' . date('Y-mM-dl h:i:s') . '_' . time() . '.txt';
        $logPath = WRITEPATH . "temp/logs/$logFile";

        $result = $course->bulkUpload(
            $file ?? [],
            [
                'mode'             => 'upsert',
                '__authorize__'             => 'course_import',
                'headerMap'        => [
                    'course_code'       => 'code',
                    'course_title'      => 'title',
                    'course_description' => 'description',
                    'course_guide_url'  => 'course_guide_url',
                    'course_type'       => 'type',
                    'department_code'   => 'department_code', // still needs FK resolve
                ],
                'validateColumns'  => ['course_code', 'course_title', 'department_code', 'course_type'],
                'staticColumns'    => ['active' => 1],

                'batchSize'        => 1000,
                'preprocessRow'    => $preprocessRow,
                'finder'           => $finder,
                'processLogPath'     => $logPath,
                'processLogMessage' => function (array $row) {
                    return [
                        'insert' => "New Record has been inserted for Course Code {$row['code']}",
                        'update' => "Course Code {$row['code']} has been updated "
                    ];
                }
            ]
        );
        $result['process_log_link'] = generateDownloadLink($logPath, 'temp/logs', 'logs');

        return ApiResponse::success('Courses imported successfully. Please click the link for full process log', $result);
    }

    public function importCourseEnrollment()
    {
        $course = new \App\Entities\Course_enrollment();

        $rules = [
            'course_file' => [
                'label' => 'CSV file',
                'rules' => implode('|', [
                    'uploaded[course_file]',
                    'max_size[course_file,1024]',
                    'mime_in[course_file,text/plain,text/csv,application/csv,application/vnd.ms-excel,text/tab-separated-values]',
                    'ext_in[course_file,csv,txt,tsv]',
                ]),
            ],
        ];

        if (! $this->validate($rules)) {
            $errors = $this->validator->getErrors();
            return ApiResponse::error(reset($errors));
        }
        $file = $this->request->getFile('course_file');

        $existingMap = [];
        $db = $this->db;
        $courseEnrollment = $db->table('course_enrollment')
            ->select('id, student_id, course_id, session_id, student_level')
            ->get()->getResultArray();
        foreach ($courseEnrollment as $r) {
            $k = $r['student_id'] . '|' . $r['course_id'] . '|' . $r['session_id'] . '|' . $r['student_level'];
            $existingMap[$k] = (int)$r['id'];
        }
        $now = date('Y-m-d H:i:s');
        $preprocessRow = function (array $row) use ($db, $now): array {
            static $studentByMatric = [];
            static $courseByCode    = [];
            static $sessionByName   = [];

            // normalize
            $row['matric_number']   = strtoupper(trim((string)($row['matric_number'] ?? '')));
            $row['course_code']     = strtoupper(trim((string)($row['course_code'] ?? '')));
            $row['session']         = trim((string)($row['session'] ?? ''));
            $row['course_unit']     = (int)($row['course_unit'] ?? 0);
            $row['course_status']   = trim((string)($row['course_status'] ?? ''));
            $row['course_semester'] = trim((string)($row['course_semester'] ?? ''));
            $row['level']           = trim((string)($row['level'] ?? ''));

            if ($row['level'] !== '') {
                $row['student_level'] = (int) str_replace('00', '', $row['level']);
            }
            unset($row['level']);

            if ($row['matric_number'] === '') {
                throw new ValidationFailedException('Student does not exist.');
            }
            if (!isset($studentByMatric[$row['matric_number']])) {
                $studentByMatric[$row['matric_number']] = (int) $db->table('academic_record')
                    ->select('student_id')
                    ->where('matric_number', $row['matric_number'])
                    ->get()->getRow('student_id');
            }

            $row['student_id'] = $studentByMatric[$row['matric_number']] ?? 0;
            if ($row['student_id'] <= 0) {
                throw new ValidationFailedException("Student '{$row['matric_number']}' does not exist.");
            }
            unset($row['matric_number']);

            if ($row['course_code'] === '') {
                throw new ValidationFailedException("Course code does not exist.");
            }
            if (!isset($courseByCode[$row['course_code']])) {
                $courseByCode[$row['course_code']] = (int) $db->table('courses')
                    ->select('id')->where('code', $row['course_code'])->get()->getRow('id');
            }
            $row['course_id'] = $courseByCode[$row['course_code']] ?? 0;
            if ($row['course_id'] <= 0) {
                throw new ValidationFailedException("Course '{$row['course_code']}' does not exist.");
            }
            unset($row['course_code']);

            if ($row['session'] === '') {
                throw new ValidationFailedException("Session is required.");
            }
            if (!isset($sessionByName[$row['session']])) {
                $sessionByName[$row['session']] = (int) $db->table('sessions')
                    ->select('id')->where('date', $row['session'])->get()->getRow('id');
            }
            $row['session_id'] = $sessionByName[$row['session']] ?? 0;
            if ($row['session_id'] <= 0) {
                throw new ValidationFailedException("Session '{$row['session']}' does not exist.");
            }
            unset($row['session']);

            $sem = strtolower($row['course_semester'] ?? '');
            if ($sem === 'first') $row['semester'] = 1;
            elseif ($sem === 'second') $row['semester'] = 2;
            elseif (ctype_digit($sem)) $row['semester'] = (int)$sem;
            else $row['semester'] = 0;
            unset($row['course_semester']);

            $row['date_last_update'] = $now;
            return $row;
        };

        $finder = function (array $row) use (&$existingMap): ?int {
            $k = ($row['student_id'] ?? 0) . '|' . ($row['course_id'] ?? 0) . '|' . ($row['session_id'] ?? 0) . '|' . ($row['student_level'] ?? 0);
            return $existingMap[$k] ?? null;
        };

        $logFile = 'bulk_course_enrollment_log_' . date('Y-mM-dl h:i:s') . '_' . time() . '.txt';
        $logPath = WRITEPATH . "temp/logs/$logFile";

        $result = $course->bulkUpload(
            $file ?? [],
            [
                'mode'             => 'upsert',
                '__authorize__'    => 'course_reg_import',
                'headerMap'        => [
                    'matric_number'   => 'matric_number',
                    'course_code'     => 'course_code',
                    'session'         => 'session',
                    'course_unit'     => 'course_unit',
                    'course_status'   => 'course_status',
                    'course_semester' => 'course_semester',
                    'level'           => 'level',
                ],
                'validateColumns'  => ['matric_number', 'course_code', 'session', 'course_unit', 'course_status', 'course_semester', 'level'],
                'staticColumns'    => [
                    'date_created' => $now,
                ],
                'updateFields'     => ['course_unit', 'course_status', 'semester', 'date_last_update'],
                'batchSize'        => 1000,
                'preprocessRow'    => $preprocessRow,
                'finder'           => $finder,
                'processLogPath'     => $logPath,
                'processLogMessage' => function (array $row) {
                    return [
                        'insert' => "New Record has been inserted for Matric number {$row['matric_number']} - Course: {$row['course_code']}, Unit: {$row['course_unit']}, Status: {$row['course_status']}, Level: {$row['level']}, Session: {$row['session']}",
                        'update' => "Record updated for Matric number " . $row['matric_number']
                    ];
                }
            ]
        );
        $result['process_log_link'] = generateDownloadLink($logPath, 'temp/logs', 'logs');

        return ApiResponse::success('Courses enrollment imported successfully. Please click the link for full process log', $result);
    }

    public function stats()
    {
        EntityLoader::loadClass($this, 'courses');
        $result = $this->courses->getCourseStats();
        return ApiResponse::success('Course stats', $result);
    }

    public function uploadCourseGuide($id)
    {
        $course = $this->showListEntity('courses', $id);

        if (empty($course)) {
            return ApiResponse::error("Course not found", null, 404);
        }

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

        if (! $this->validate($rules)) {
            $errors = $this->validator->getErrors();
            return ApiResponse::error(reset($errors));
        }
        $file = $this->request->getFile('course_guide');

        if ($file->isValid() && !$file->hasMoved()) {
            // Move the file to a temporary location
            $tempPath = WRITEPATH . 'uploads/temp/';
            if (!is_dir($tempPath)) {
                mkdir($tempPath, 0755, true);
            }
            $newName = $course['code'] . '_course_guide.' . $file->getExtension();
            $file->move($tempPath, $newName);
            $fullPath = $tempPath . $newName;

            // Upload to Google Drive
            $storage = Services::gDriveStorage();
            try {
                if ($course['course_guide_id']) {
                    // Delete the old file if exists
                    $storage->deleteFile($course['course_guide_id']);
                }

                $fileId = $storage->uploadFile(
                    $fullPath,
                    $file->getMimeType(),
                    $newName
                );
                $updateData = [
                    'course_guide_id'  => $fileId,
                ];
                $this->courses->updateSingle($id, $updateData);

                return ApiResponse::success('Course guide uploaded successfully', $updateData);
            } catch (Throwable $e) {
                return ApiResponse::error('Error uploading to Google Drive: ' . $e->getMessage());
            } finally {
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
        } else {
            return ApiResponse::error('Invalid file upload');
        }
    }

    public function deleteCourseGuide($id)
    {
        $course = $this->showListEntity('courses', $id);

        if (empty($course)) {
            return ApiResponse::error("Course not found", null, 404);
        }

        if (empty($course['course_guide_id'])) {
            return ApiResponse::error("No course guide to delete", null, 400);
        }

        // Delete from Google Drive
        $storage = Services::gDriveStorage();
        try {
            $storage->deleteFile($course['course_guide_id']);

            // Update the course record
            $updateData = [
                'course_guide_id' => null,
            ];
            $this->courses->updateSingle($id, $updateData);

            return ApiResponse::success('Course guide deleted successfully');
        } catch (Throwable $e) {
            return ApiResponse::error('Error deleting from Google Drive: ' . $e->getMessage());
        }
    }
}
