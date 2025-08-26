<?php

namespace App\Controllers\Admin\V1;

use App\Controllers\BaseController;
use App\Libraries\ApiResponse;
use App\Traits\Crud\EntityListTrait;
use App\Traits\ExportTrait;
use Throwable;

class CoursesController extends BaseController
{
    use EntityListTrait, ExportTrait;

    public function index(){
        $payload = $this->listApiEntity('courses');
        return ApiResponse::success(data: $payload);
    }

    public function show(int $id){
        $payload = $this->showListEntity('courses', $id);
        return ApiResponse::success(data: $payload);
    }

    /**
     * @throws Throwable
     */
    public function store(){
        $course = new \App\Entities\Courses();

        $payload = $this->request->getPost();

        $row = $course->insertSingle(
            $payload ?? [],
            $this->request->getFiles() ?? []
        );
        if(!$row) return ApiResponse::error("Unable to create course");

        return ApiResponse::success('Course inserted successfully', $payload);
    }

    /**
     * @throws Throwable
     */
    public function update($id){
        $course = new \App\Entities\Courses();
        $payload = $this->request->getRawInput();

        $row = $course->updateSingle(
            $id, $payload ?? [],
        );
        if(!$row) return ApiResponse::error("Unable to update course");

        return ApiResponse::success('Course updated successfully', $payload);
    }

    /**
     * @throws Throwable
     */
    public function delete($id){
        $course = new \App\Entities\Courses();
        $row = $course->deleteSingle($id);
        if(!$row) return ApiResponse::error("Unable to delete course");

        return ApiResponse::success('Course deleted successfully');
    }

    public function import(){
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

        $preprocessRow = function(array $row): array {
            static $dept = [];
            $db = $this->db;

            if (isset($row['code'])) $row['code'] = strtoupper(trim((string)$row['code']));
            if (!empty($row['department_code'])) {
                $c = strtoupper(trim((string)$row['department_code']));
                if (!isset($dept[$c])) {
                    $dept[$c] = (int)$db->table('department')->select('id')->where('code', $c)->get()->getRow('id');
                }
                if ($dept[$c] <= 0) {
                    throw new \App\Exceptions\ValidationFailedException("department_code: unknown department '{$c}'.");
                }
                $row['department_id'] = $dept[$c];
            }
            unset($row['department_code']);
            return $row;
        };

        // Callback: fast match for update/upsert
        $finder = function(array $row) use (&$courseIdByKey): ?int {
            $code = strtoupper((string)($row['code'] ?? ''));
            if ($code === '') return null;
            return $courseIdByKey[$code] ?? null;
        };

        $logFile = 'bulk_courses_mapping_log_' . date('Y-mM-dl h:i:s') . '_' . time() . '.txt';
        $logPath = WRITEPATH . "temp/logs/$logFile";

        $result = $course->bulkUpload(
            $file ?? [],
            [
                'mode'             => 'update',
                '__authorize__'             => 'course_imports',
                'headerMap'        => [
                    'course_code'       => 'code',
                    'course_title'      => 'title',
                    'course_description' => 'description',
                    'course_guide_url'  => 'course_guide_url',
                    'course_type'       => 'type',
                    'department_code'   => 'department_code', // still needs FK resolve
                ],
                'validateColumns'  => ['course_code','course_title','department_code','course_type'],
                'staticColumns'    => ['active' => 1],

                'batchSize'        => 1000,
                'preprocessRow'    => $preprocessRow,
                'finder'           => $finder,
                'errorLogPath'     => $logPath,
            ]
        );
        $result['process_log_link'] = generateDownloadLink($logPath, 'temp/logs', 'logs');

        return ApiResponse::success('Courses imported successfully. Please click the link for full process log', $result);
    }

}