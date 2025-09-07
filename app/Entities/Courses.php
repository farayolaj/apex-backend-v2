<?php

namespace App\Entities;

use App\Enums\ClaimEnum as ClaimType;
use App\Enums\CommonEnum as CommonSlug;
use App\Libraries\EntityLoader;
use App\Models\Crud;
use App\Models\WebSessionManager;
use App\Support\DTO\ApiListParams;
use App\Traits\ResultManagerTrait;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the courses table.
 */
class Courses extends Crud
{
    use ResultManagerTrait;

    protected static string $tablename = 'Courses';
    /* this array contains the field that can be null*/
    static array $nullArray = array('course_guide_url', 'course_guide_id', 'date_created');
    static array $compositePrimaryKey = array();
    static array $uploadDependency = array();
    /*this array contains the fields that are unique*/
    static array $uniqueArray = array('code');
    /*this is an associative array containing the fieldname and the type of the field*/
    static array $typeArray = array(
        'code' => 'varchar',
        'title' => 'text',
        'description' => 'text',
        'course_guide_url' => 'text',
        'course_guide_id' => 'varchar',
        'active' => 'tinyint',
        'date_created' => 'varchar',
        'type' => 'varchar',
        'department_id' => 'int'
    );
    /*this is a dictionary that map a field name with the label name that will be shown in a form*/
    static array $labelArray = array(
        'id' => '',
        'code' => '',
        'title' => '',
        'description' => '',
        'course_guide_url' => '',
        'course_guide_id' => '',
        'active' => '',
        'date_created' => '',
        'type' => '',
        'department_id' => ''
    );
    /*associative array of fields that have default value*/
    static array $defaultArray = array('date_created' => '');
    // populate this array with fields that are meant to be displayed as document in the format
    // array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
    // the folder to save must represent a path from the basepath. it should be a relative path,
    // preserve filename will be either true or false. when true,the file will be uploaded with its default
    // filename else the system will pick the current user id in the session as the name of the file.
    static array $documentField = array(); //array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

    static array $relation = array();

    static array $tableAction = array('delete' => 'delete/courses', 'edit' => 'edit/courses');

    static array $apiSelectClause = ['id', 'title', 'code', 'active', 'course_guide_url', 'course_guide_id'];
    protected array  $searchable = ['a.title', 'a.code'];
    protected array  $sortable   = ['code' => 'a.code', 'title' => 'a.title'];

    protected bool $externalObserversFirst = true;

    protected ?string $updatedField = 'updated_at';

    function __construct($array = array())
    {
        parent::__construct($array);
    }

    public function getDetails($id)
    {
        $query = "SELECT distinct a.id as main_course_id, a.*, b.course_unit, b.course_status,
		c.course_lecturer_id, e.firstname, e.othernames, e.lastname 
        from courses a
        left join course_enrollment b on a.id = b.course_id 
		left join course_manager c on c.course_id = a.id 
		left join users_new d on d.id = c.course_lecturer_id and d.user_type = 'staff'
		left join staffs e on e.id=d.user_table_id where a.id =? ";
        $courses = $this->query($query, [$id]);
        return $courses[0];
    }

    public function getCourseById($courseId, $courseArray = false, $programmeId = null, $entryMode = null, $semester = null)
    {
        $query = "SELECT courses.id as course_id, courses.*, course_mapping.id as course_mapping_id, course_mapping.* from courses left join course_mapping on course_mapping.course_id = courses.id where courses.id = ?";
        if ($entryMode) {
            $query .= " and course_mapping.mode_of_entry = '$entryMode'";
        }
        if ($courseArray && $programmeId) {
            $query .= " and course_mapping.programme_id = '$programmeId'";
        }
        if ($courseArray && $semester) {
            $query .= " and course_mapping.semester = '$semester' ";
        }
        $courses = $this->query($query, [$courseId]);
        if (!$courses) {
            return false;
        }
        if (!$courseArray) {
            $courses = $courses[0];
            return '"' . $courses['code'] . ' - ' . $courses['title'] . '"';
        } else {
            foreach ($courses as $course) {
                return array(
                    'course_id' => $course['course_id'],
                    'course_code' => $course['code'],
                    'course_title' => $course['title'],
                    'course_unit' => $course['course_unit'],
                    'course_status' => $course['course_status'],
                    'semester' => $course['semester'],
                );
            }
        }
    }

    public function getCourseCodeById($course)
    {
        $query = "SELECT * FROM courses where id = ?";
        $courses = $this->query($query, [$course]);
        if ($courses) {
            return strtolower($courses[0]['code']);
        }
        return null;
    }

    public function delete($id = null, &$dbObject = null, $type = null): bool
    {
        $currentUser = WebSessionManager::currentAPIUser();
        $db = $dbObject ?? $this->db;
        if (parent::delete($id, $db)) {
            logAction('course_delete', $currentUser->user_login);
            return true;
        }
        return false;
    }

    protected function applyBaseFilters($b): void {}

    public function APIList($request, $filterList)
    {
        $generalCourse = request()->getGet('general_course') ?: null;
        // this is used to filter out existing courses in course_manager table
        if (isset($_GET['cm_filter'])) {
            return $this->loadUnassignCourses();
        }

        if (isset($_GET['cmc_filter'])) {
            return $this->loadOutsideDepartmentalCourses();
        }

        $response = $this->APIListNew($request, $filterList);
        $data = $response['table_data'];
        $total = $response['paging'];

        if (!$generalCourse) {
            $data = $this->processList($data);
        }

        return [
            'paging' => $total,
            'table_data' => $data
        ];
    }

    public function APIListNew($request, array $filterList)
    {
        $params = ApiListParams::fromArray($request, [
            'perPage'    => 1,
            'maxPerPage' => 20,
            'sort'       => 'code',
        ]);

        $params->filters = $filterList;

        return $this->listApi(
            static::$apiSelectClause,
            $params
        );
    }

    private function processList(array $items): array
    {
        for ($i = 0; $i < count($items); $i++) {
            $items[$i] = $this->loadExtras($items[$i]);
        }
        return $items;
    }

    private function loadExtras($item): array
    {
        return [
            'id' => $item['id'],
            'code' => $item['code'] . " - " . $item['title'],
        ];
    }

    private function loadOutsideDepartmentalCourses()
    {
        $department = isset($_GET['dashboard_department']) ? $_GET['dashboard_department'] : null;
        $departmentQuery = $department ? " a.department_id <> '$department' " : "";

        $query = "SELECT SQL_CALC_FOUND_ROWS a.id, a.title, a.code FROM courses a WHERE 
			{$departmentQuery} ORDER BY a.code ASC";

        $query2 = "SELECT FOUND_ROWS() as totalCount";
        $res = $this->db->query($query);
        $res = $res->getResultArray();
        $res2 = $this->db->query($query2);
        $res2 = $res2->getResultArray();
        $res = $this->processList($res);
        return [$res, $res2];
    }

    private function loadUnassignCourses()
    {
        $session = $_GET['cm_filter'];
        $department = isset($_GET['dashboard_department']) ? $_GET['dashboard_department'] : null;
        $departmentQuery = $department ? " a.department_id = '$department' and " : "";

        $query = "SELECT SQL_CALC_FOUND_ROWS a.id, a.title, a.code FROM courses a WHERE 
			{$departmentQuery} NOT EXISTS (
    		SELECT 1 FROM course_manager cm 
    		WHERE cm.course_id = a.id and cm.session_id = '$session'
    		) ORDER BY a.code ASC";

        $query2 = "SELECT FOUND_ROWS() as totalCount";
        $res = $this->db->query($query);
        $res = $res->getResultArray();
        $res2 = $this->db->query($query2);
        $res2 = $res2->getResultArray();
        $res = $this->processList($res);
        return [$res, $res2];
    }

    public function deleteCourseRegistration($student_id, $course_id, $session_id, $level)
    {
        // create a transaction making sure that the data is backup before being delete
        $this->db->transStart();
        $param = array($student_id, $course_id, $session_id, $level);
        $query = "INSERT into course_enrollment_archive(student_id,course_id,course_unit,course_status,semester,session_id,student_level,ca_score,exam_score,total_score,is_approved,date_last_update,date_created,date_deleted) select student_id,course_id,course_unit,course_status,semester,session_id,student_level,ca_score,exam_score,total_score,is_approved,date_last_update,date_created,current_timestamp from course_enrollment where student_id=? and course_id=? and session_id=? and student_level=?";
        $this->db->query($query, $param);
        $query2 = 'DELETE from course_enrollment where student_id=? and course_id=? and session_id=? and student_level=?';
        $this->db->query($query2, $param);
        $this->db->transComplete();
        return $this->db->transStatus();
    }

    public function getCourseByIdOnly($course)
    {
        $query = "SELECT * FROM courses where id = ?";
        $courses = $this->query($query, [$course]);
        if ($courses) {
            return $courses[0];
        }
        return null;
    }

    public function getCourseIdByCode($course, $all = false)
    {
        $course = strtoupper($course);
        $query = "SELECT * FROM courses where code = ?";
        $courses = $this->query($query, [$course]);
        if ($courses) {
            return $all ? $courses[0] : $courses[0]['id'];
        }
        return '';
    }

    public function getAllCoursesList($programme_id, $level, $semester = ''): array
    {
        $semester = strtolower($semester);
        $semester = ($semester != '' && $semester == 'first') ? 1 : 2;
        $query = "SELECT a.id as main_course_id, a.code, a.title, a.description, a.course_guide_url, a.course_guide_id, a.active,
        b.id as course_mapping_id, b.programme_id, b.semester, b.course_unit, b.course_status, b.level, b.mode_of_entry,
        b.pass_score, b.pre_select FROM courses a left join course_mapping b on a.id = b.course_id where b.programme_id = ?
    	and b.semester = ? and a.active = ? order by a.code asc";
        $courses = $this->query($query, [$programme_id, $semester, 1]);
        if (!$courses) {
            return [];
        }
        $result = [];
        foreach ($courses as $course) {
            $mappedLevel = json_decode($course['level'], true);
            foreach ($mappedLevel as $key => $levelMapped) {
                if ($levelMapped < $level) {
                    $courseData = array(
                        'course_id' => hashids_encrypt($course['main_course_id']),
                        'code' => $course['code'],
                        'title' => $course['title'],
                        'semester' => (int)$course['semester'],
                        'unit' => (int)$course['course_unit'],
                        'status' => $course['course_status'],
                        'pre_select' => (int)$course['pre_select'],
                    );
                    $result[] = $courseData;
                }
            }
        }

        return uniqueMultidimensionalArray($result, 'course_id');
    }

    private function getDistinctCourseList($session, $semester, $limit = null)
    {
        $query = "SELECT a.course_id,ANY_VALUE(c.code) as code,
			c.title as course_title,
       		ANY_VALUE(type) as course_type, 
       		GROUP_CONCAT(DISTINCT CONCAT(a.course_status, ':', status_count) 
       		ORDER BY a.course_status SEPARATOR ', ') as course_status,
    		SUM(status_count) as total FROM
    		(
    			SELECT course_id,course_status, COUNT(student_id) as status_count
    			FROM `course_enrollment` WHERE session_id = ? AND semester = ? GROUP BY course_id, course_status
    		) a JOIN courses c ON c.id = a.course_id 
    		GROUP BY a.course_id, c.title ORDER BY total DESC";
        if ($limit) {
            $query .= " limit {$limit}";
        }
        return $this->query($query, [$session, $semester]);
    }

    private function getDistinctCourseScore($course, $session, $semester)
    {
        $query = "SELECT code, b.date as session, count(DISTINCT student_id) as total FROM `course_enrollment` a 
			join sessions b on b.id = a.session_id 
			join courses c on c.id = a.course_id where a.course_id = ? and a.session_id = ? and a.semester = ? 
			and total_score is not null";
        $result = $this->query($query, [$course, $session, $semester]);
        return ($result) ? $result[0] : null;
    }

    public function getListCourseEnrolledOld()
    {
        $session = $_GET['session'] ?? get_setting('active_session_student_portal');
        $semester = $_GET['semester'] ?? 1;
        $nth = $_GET['page_size'] ?? null;
        $download = request()->getGet('download') ?? null;
        $date = date('Y-m-d H:i:s');
        $result = $this->getDistinctCourseList($session, $semester, $nth);
        if (!$result) {
            return [];
        }

        $contents = [];
        EntityLoader::loadClass($this, 'sessions');
        EntityLoader::loadClass($this, 'examination_courses');
        $result = useGenerators($result);
        $sumEstimate = $sumActual = 0;
        $sumNewEstimate = $sumNewActual = 0;
        $sumProposedEstimate = $sumProposedActual = 0;
        foreach ($result as $res) {
            $course = $this->getDistinctCourseScore($res['course_id'], $session, $semester);
            if ($course) {
                $isPaper = strtolower($res['course_type']) === ClaimType::EXAM_PAPER->value;
                $inferEstimate = self::calcTutorAmount($res['total'], $isPaper);
                $inferActual = self::calcTutorAmount($course['total'], $isPaper);
                $physicalOldInteractive = self::calcInteractionOld()['sumTotal'];
                $totalOldActualAmount = $inferActual['sumTotal'];
                $totalOldEstimateAmount = $inferEstimate['sumTotal'] + $physicalOldInteractive;

                // this handles the new regime
                $inferEstimateNew = self::inferPaymentAmount($res['code'], $res['total'], $isPaper, CommonSlug::PROF_RANK->value);
                $physicalAmount = self::calcFacilitation(CommonSlug::PROF_RANK->value)['sumTotal'];
                $dataAmount = self::calcDataAllowance()['sumTotal'];
                $excessAmount = self::calcWebinarWorkLoadAllowance($res['total'])['sumTotal'];
                $excessAmountFormer = self::calcWebinarWorkLoadAllowance($res['total'], 200)['sumTotal'];
                $cbtAmount = self::calcExamType(ClaimType::EXAM_CBT->value)['sumTotal'];
                $totalEstimateAmount = $inferEstimateNew['sumTotal'] + $physicalAmount + $dataAmount +
                    $excessAmount + $cbtAmount;

                $inferActualNew = self::inferPaymentAmount($res['code'], $course['total'], $isPaper, CommonSlug::PROF_RANK->value);
                $physicalAmount = self::calcFacilitation(CommonSlug::PROF_RANK->value)['sumTotal'];
                $dataAmount = self::calcDataAllowance()['sumTotal'];
                $excessAmountInfer = self::calcWebinarWorkLoadAllowance($course['total'])['sumTotal'];
                $excessAmountInferFormer = self::calcWebinarWorkLoadAllowance($course['total'], 200)['sumTotal'];
                $cbtAmount = self::calcExamType(ClaimType::EXAM_CBT->value)['sumTotal'];
                $totalActualAmount = $inferActualNew['sumTotal'] + $physicalAmount + $dataAmount +
                    $excessAmountInfer + $cbtAmount;

                // this handles the proposed
                $inferProposedEstimate = self::calcProposedTutorAmount($res['total'], $isPaper);
                $physicalProposedInteractive = self::calcInteractionProposed()['sumTotal'];
                $totalProposedEstimateAmount = $inferProposedEstimate['sumTotal'];

                $lecturerName = $this->examination_courses->getCourseLecturerName($res['course_id'], $session);
                $lecturerName = $lecturerName ? $lecturerName['lecturers_name'] : '';

                $item = [
                    'course_code' => $res['code'] . ' - ' . $res['course_title'],
                    'course_status' => $res['course_status'],
                    'enrolled' => $res['total'],
                    'scored' => $course['total'],
                    'exam_type' => strtoupper($res['course_type']),
                    'estimate' => $totalOldEstimateAmount,
                    'actual' => $totalOldActualAmount,
                    'new_regime_estimate' => $totalEstimateAmount,
                    'new_regime_actual' => $totalActualAmount,
                    'proposed_estimate' => $totalProposedEstimateAmount,
                    'lecturer_name' => $lecturerName,
                    'new_regime_estimate_webinar_workload_per_100' => $excessAmount,
                    'new_regime_estimate_webinar_workload_per_200' => $excessAmountFormer,
                    'new_regime_actual_webinar_workload_per_100' => $excessAmountInfer,
                    'new_regime_actual_webinar_workload_per_200' => $excessAmountInferFormer,
                ];

                if ($download == 'yes') {
                    $item['session'] = $course['session'];
                    $item['course_status'] = str_replace(',', ' -', $item['course_status']);
                }

                $sumEstimate += $totalOldEstimateAmount;
                $sumActual += $inferActual['sumTotal'];

                $sumNewEstimate += $totalEstimateAmount;
                $sumNewActual += $totalActualAmount;

                $sumProposedEstimate += $totalProposedEstimateAmount;

                $contents[] = $item;
            }
        }

        if (!empty($contents)) {
            usort($contents, function ($a, $b) {
                return intval($b['enrolled']) - intval($a['enrolled']);
            });

            $contents = array_slice($contents, 0, $nth);
            if ($download == 'yes') {
                $contents = array2csv($contents);
                $filename = "Courses_predictive_analysis_" . date('Y-m-d') . "_download.csv";
                $header = 'text/csv';
                return sendDownload($contents, $header, $filename);
            }
        }

        return [
            'data' => $contents,
            'datetime' => $date,
            'sumEstimate' => $sumEstimate,
            'sumActual' => $sumActual,
            'sumNewEstimate' => $sumNewEstimate,
            'sumNewActual' => $sumNewActual,
            'sumProposedEstimate' => $sumProposedEstimate
        ];
    }

    public function getListCourseEnrolled()
    {
        $session = $_GET['session'] ?? get_setting('active_session_student_portal');
        $semester = $_GET['semester'] ?? 1;
        $nth = $_GET['page_size'] ?? null;
        $download = request()->getGet('download') ?? null;
        $date = date('Y-m-d H:i:s');
        $result = $this->getDistinctCourseList($session, $semester, $nth);
        if (!$result) {
            return [];
        }

        $contents = [];
        EntityLoader::loadClass($this, 'sessions');
        EntityLoader::loadClass($this, 'examination_courses');
        EntityLoader::loadClass($this, 'course_request_claims');
        $result = useGenerators($result);
        $sumEstimate = 0;
        $sumNewEstimate = 0;
        $sumProposedEstimate = 0;
        foreach ($result as $res) {
            $course = $this->getDistinctCourseScore($res['course_id'], $session, $semester);
            if ($course) {
                $isPaper = strtolower($res['course_type']) === ClaimType::EXAM_PAPER->value;
                $excessAmount = self::calcWebinarWorkLoadAllowance($res['total'], 100)['sumTotal'];
                $excessAmountPer200 = self::calcWebinarWorkLoadAllowance($res['total'], 200)['sumTotal'];

                // this handles the old regime
                if (isGESCourse($res['code'])) {
                    $totalOldEstimateAmount = self::totalAmountPayload(600000)['sumTotal'];
                } else {
                    $inferEstimate = self::calcTutorAmount($res['total'], $isPaper)['sumTotal'];
                    $physicalOldInteractive = self::calcInteractionOld()['sumTotal'];
                    $examTypeAmount = self::calcExamType(ClaimType::EXAM_CBT->value, true)['sumTotal'];
                    $totalOldEstimateAmount = $inferEstimate + $physicalOldInteractive + $examTypeAmount;
                }

                // this handles the new regime
                if (isGESCourse($res['code'])) {
                    $totalEstimateAmount = self::totalAmountPayload(600000)['sumTotal'];
                } else {
                    $inferEstimateNew = self::inferPaymentAmount($res['code'], $res['total'], $isPaper, CommonSlug::PROF_RANK->value)['sumTotal'];
                    $physicalAmount = self::calcFacilitation(CommonSlug::PROF_RANK->value)['sumTotal'];
                    $dataAmount = self::calcDataAllowance(15000)['sumTotal'];
                    $excessAmountFormer = self::calcWebinarWorkLoadAllowance($res['total'], 200)['sumTotal'];
                    $examTypeAmount = self::calcExamType($res['course_type'])['sumTotal'];
                    $totalEstimateAmount = $inferEstimateNew + $physicalAmount + $dataAmount +
                        $excessAmountFormer + $examTypeAmount;
                }

                // this handles the newly intended regime
                if (isGESCourse($res['code'])) {
                    $totalProposedEstimateAmount = self::totalAmountPayload(600000)['sumTotal'];
                } else {
                    $inferProposedEstimate = self::calcProposedTutorAmount($res['total'])['sumTotal'];
                    $physicalAmount = self::calcFacilitation(CommonSlug::PROF_RANK->value)['sumTotal'];
                    $dataAmount = self::calcDataAllowance()['sumTotal'];
                    $excessAmountFormer = self::calcWebinarWorkLoadAllowance($res['total'], 100)['sumTotal'];
                    $examTypeAmount = self::calcExamType(ClaimType::EXAM_CBT->value)['sumTotal'];
                    $logisticsAmount = self::calcLogisticsAllowance()['sumTotal'];
                    $totalProposedEstimateAmount = $inferProposedEstimate + $physicalAmount + $dataAmount +
                        $excessAmountFormer + $examTypeAmount + $logisticsAmount;
                }

                $lecturerName = $this->examination_courses->getCourseLecturerName($res['course_id'], $session);
                $lecturerName = $lecturerName ? $lecturerName['lecturers_name'] : '';
                $courseCode = $res['code'] . ' - ' . $res['course_title'];
                $actualSubmittedAmount = $this->course_request_claims->getCourseClaimsAmountOnly($res['course_id'], $session);
                $userSubmitted = $this->course_request_claims->getExistingCourseClaims($session, $res['course_id']);
                $fullname = null;
                $departmentName = null;
                if ($userSubmitted) {
                    $userSubmitted = $userSubmitted[0];
                    $userData = $this->users_new->getUserInfoWithDepartment($userSubmitted['course_manager_id'], 'staffs');
                    $fullname = ucwords(strtolower($userData['title'] . ' ' . $userData['firstname'])) . ' ' . strtoupper($userData['lastname']);
                    $departmentName = $userData['department_name'];
                }

                $item = [
                    'course_code' => $courseCode,
                    'course_short' => $res['code'],
                    'course_status' => $res['course_status'],
                    'enrolled' => $res['total'] ?? 0,
                    'scored' => $course['total'] ?? 0,
                    'exam_type' => strtoupper($res['course_type']),
                    'old_regime_estimate' => $totalOldEstimateAmount,
                    'revised_regime_estimate' => $totalEstimateAmount,
                    'new_intended_regime_estimate' => $totalProposedEstimateAmount,
                    'actual_amount' => $actualSubmittedAmount,
                    'lecturer_name' => $lecturerName,
                    'user_id' => $userSubmitted ? $userSubmitted['course_manager_id'] : '',
                    'department_name' => $departmentName,
                    'fullname' => $fullname
                ];

                if ($download == 'yes') {
                    $item['course_code'] = str_replace(',', ';', $item['course_code']);
                    $item['session'] = $course['session'];
                    $item['course_status'] = str_replace(',', ' -', $item['course_status']);
                }

                $sumEstimate += $totalOldEstimateAmount;
                $sumNewEstimate += $totalEstimateAmount;
                $sumProposedEstimate += $totalProposedEstimateAmount;

                $contents[] = $item;
            }
        }

        if (!empty($contents)) {
            usort($contents, function ($a, $b) {
                return intval($b['enrolled']) - intval($a['enrolled']);
            });

            $contents = array_slice($contents, 0, $nth);
            if ($download == 'yes') {
                // $contents = array2csv($contents);
                // $header = 'text/csv';
                // return sendDownload($contents, $header, $filename);

                $tempname = "Courses_predictive_analysis_" . date('Y-m-d') . "_download.xlsx";
                $filename = FCPATH . "temp/export/" . $tempname;

                $aggregated = aggregateForReport($contents, 50000, []);
                $rows = flattenForSheetReport($aggregated);

                $ss = new Spreadsheet();
                $sheet = $ss->getActiveSheet();
                $sheet->fromArray($rows, null, 'A1');

                $highestRow = $sheet->getHighestRow();
                $sheet->getStyle('D2:D' . $highestRow)
                    ->getNumberFormat()->setFormatCode('#,##0.00');
                foreach (range(1, 4) as $colIdx) {
                    $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($colIdx))->setAutoSize(true);
                }

                $writer = new Xlsx($ss);
                $writer->save($filename);
                $exportLink = generateDownloadLink($filename, "temp/export");

                $payload = [
                    'export_link' => $exportLink,
                ];

                return sendAPiResponse(true, 'link', $payload);
            }
        }

        return [
            'data' => $contents,
            'datetime' => $date,
            'sumEstimate' => $sumEstimate,
            'sumNewEstimate' => $sumNewEstimate,
            'sumProposedEstimate' => $sumProposedEstimate
        ];
    }

    public function teachingPracticeEligibility($student_id, $observationLetter = false)
    {
        $currentSession = get_setting('active_session_student_portal');
        $code1 = 'TEE305';
        $code2 = 'TEE405';
        $code3 = 'ASE205';
        if ($observationLetter) {
            $query = "SELECT a.id,b.code from course_enrollment a 
			join courses b on b.id = a.course_id 
         	where b.code in ('{$code3}') and session_id = ? 
         	and a.student_id = ?";
            $result = $this->db->query($query, [$currentSession, $student_id]);
        } else {
            $query = "SELECT a.id,b.code from course_enrollment a 
			join courses b on b.id = a.course_id 
         	where b.code in ('{$code1}', '{$code2}') and session_id = ? 
         	and a.student_id = ?";
            $result = $this->db->query($query, [$currentSession, $student_id]);
        }
        return $result->getNumRows() > 0 ? $result->getRow() : false;
    }

    public function getCourseStats()
    {
        $query = " SELECT count(a.id) as total_course,
		(select count(b.id) from courses b where b.active='1') as total_course_active,
     	(select count(c.id) as total_courses_offerings from courses c where c.course_guide_url is not null) as total_course_manual
     	 from courses a ";
        return $this->query($query)[0];
    }

    public function insertDummyData()
    {
        $this->db->table('courses')->insert([
            'code' => 'TEST305',
            'title' => 'Test case',
            'description' => 'Test case',
            'course_guide_url' => 'https://www.google.com',
            'active' => '1',
            'type' => 'written',
            'department_id' => '1',
        ]);
    }
}
