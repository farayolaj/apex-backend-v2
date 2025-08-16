<?php

namespace App\Entities;

use App\Enums\ClaimEnum as ClaimType;
use App\Libraries\EntityLoader;
use App\Models\Crud;
use App\Traits\ResultManagerTrait;

/**
 * This class  is automatically generated based on the structure of the table. And it represent the model of the courses table.
 */
class Courses extends Crud
{
    use ResultManagerTrait;

    protected static $tablename = 'Courses';
    /* this array contains the field that can be null*/
    static $nullArray = array('course_guide_url', 'date_created');
    static $compositePrimaryKey = array();
    static $uploadDependency = array();
    /*this array contains the fields that are unique*/
    static $uniqueArray = array('code');
    /*this is an associative array containing the fieldname and the type of the field*/
    static $typeArray = array('code' => 'varchar', 'title' => 'text', 'description' => 'text', 'course_guide_url' => 'text', 'active' => 'tinyint', 'date_created' => 'varchar');
    /*this is a dictionary that map a field name with the label name that will be shown in a form*/
    static $labelArray = array('id' => '', 'code' => '', 'title' => '', 'description' => '', 'course_guide_url' => '', 'active' => '', 'date_created' => '');
    /*associative array of fields that have default value*/
    static $defaultArray = array('date_created' => '');
//populate this array with fields that are meant to be displayed as document in the format array('fieldname'=>array('filetype','maxsize',foldertosave','preservefilename'))
//the folder to save must represent a path from the basepath. it should be a relative path,preserve filename will be either true or false. when true,the file will be uploaded with it default filename else the system will pick the current user id in the session as the name of the file.
    static $documentField = array(); //array containing an associative array of field that should be regareded as document field. it will contain the setting for max size and data type.

    static $relation = array();
    static $tableAction = array('delete' => 'delete/courses', 'edit' => 'edit/courses');
    static $apiSelectClause = ['id', 'title', 'code'];

    function __construct($array = array())
    {
        parent::__construct($array);
    }

    function getCodeFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='code' >Code</label>
		<input type='text' name='code' id='code' value='$value' class='form-control' required />
</div> ";

    }

    function getTitleFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='title' >Title</label>
<textarea id='title' name='title' class='form-control' required>$value</textarea>
</div> ";

    }

    function getDescriptionFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='description' >Description</label>
<textarea id='description' name='description' class='form-control' required>$value</textarea>
</div> ";

    }

    function getCourse_guide_urlFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='course_guide_url' >Course Guide Url</label>
<textarea id='course_guide_url' name='course_guide_url' class='form-control' >$value</textarea>
</div> ";

    }

    function getActiveFormField($value = '')
    {

        return "<div class='form-group'>
	<label class='form-checkbox'>Active</label>
	<select class='form-control' id='active' name='active' >
		<option value='1'>Yes</option>
		<option value='0' selected='selected'>No</option>
	</select>
	</div> ";

    }

    function getDate_createdFormField($value = '')
    {

        return "<div class='form-group'>
	<label for='date_created' >Date Created</label>
		<input type='text' name='date_created' id='date_created' value='$value' class='form-control'  />
</div> ";

    }

// center for custom functions
    public function getDetails($id)
    {
        $query = "SELECT distinct courses.id as main_course_id, courses.*, course_enrollment.course_unit, course_enrollment.course_status,
		course_manager.course_lecturer_id, staffs.firstname, staffs.othernames, staffs.lastname from courses left join course_enrollment on
		courses.id = course_enrollment.course_id left join course_manager on course_manager.course_id = courses.id left join users_new on
		users_new.id = course_manager.course_lecturer_id left join staffs on staffs.id=users_new.user_table_id where courses.id =? ";

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
            foreach ($courses as $courses) {
                return array(
                    'course_id' => $courses['course_id'],
                    'course_code' => $courses['code'],
                    'course_title' => $courses['title'],
                    'course_unit' => $courses['course_unit'],
                    'course_status' => $courses['course_status'],
                    'semester' => $courses['semester'],
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
    }

    public function APIList($filterList, $queryString, $start, $len, $orderBy)
    {
        $selectData = static::$apiSelectClause;
        $temp = $this->apiQueryListFiltered($selectData, $filterList, $queryString, $start, $len, $orderBy);
        $res = $this->processList($temp[0]);
        return [$res, $temp[1]];
    }

    private function processList($items)
    {
        for ($i = 0; $i < count($items); $i++) {
            $items[$i] = $this->loadExtras($items[$i]);
        }
        return $items;
    }

    private function loadExtras($item)
    {
        $payload = [
            'id' => $item['id'],
            'code' => $item['title'] . " [" . $item['code'] . "]",
        ];

        return $payload;
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
    }

    public function getCourseIdByCode($course)
    {
        $course = strtoupper($course);
        $query = "SELECT * FROM courses where code = ?";
        $courses = $this->query($query, [$course]);
        if ($courses) {
            return $courses[0]['id'];
        }
        return '';
    }

    public function getAllCoursesList($programme_id, $level, $semester = ''): array
    {
        $semester = strtolower($semester);
        $semester = ($semester != '' && $semester == 'first') ? 1 : 2;
        $query = "SELECT a.id as main_course_id, a.code, a.title, a.description, a.course_guide_url, a.active,
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
        $query = "SELECT a.course_id,ANY_VALUE(c.code) as code, ANY_VALUE(type) as course_type, GROUP_CONCAT(DISTINCT CONCAT(a.course_status, ':', status_count) ORDER BY a.course_status SEPARATOR ', ') as course_status,
    		SUM(status_count) as total FROM
    		(
    			SELECT course_id,course_status, COUNT(student_id) as status_count
    			FROM `course_enrollment` WHERE session_id = ? AND semester = ? GROUP BY course_id, course_status
    		) a JOIN courses c ON c.id = a.course_id GROUP BY a.course_id ORDER BY total DESC";
        if ($limit) {
            $query .= " limit {$limit}";
        }
        return $this->query($query, [$session, $semester]);
    }

    private function getDistinctCourseScore($course, $session, $semester)
    {
        $query = "SELECT code, b.date as session, count(DISTINCT student_id) as total FROM `course_enrollment` a join sessions b on b.id = a.session_id join courses c on c.id = a.course_id where a.course_id = ? and a.session_id = ? and a.semester = ? and total_score is not null";
        $result = $this->query($query, [$course, $session, $semester]);
        return ($result) ? $result[0] : null;
    }

    public function getListCourseEnrolled()
    {
        $session = $_GET['session'] ?? $this->currentTransactionSession();
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
        $result = useGenerators($result);
        $sumEstimate = $sumActual = 0;
        foreach ($result as $res) {
            $course = $this->getDistinctCourseScore($res['course_id'], $session, $semester);
            if ($course) {
                $isPaper = strtolower($res['course_type']) === ClaimType::EXAM_PAPER->value;
                $inferEstimate = self::calcTutorAmount($res['total'], $isPaper);
                $inferActual = self::calcTutorAmount($course['total'], $isPaper);

                $item = [
                    'course_code' => $res['code'],
                    'course_status' => $res['course_status'],
                    'enrolled' => $res['total'],
                    'scored' => $course['total'],
                    'estimate' => $inferEstimate['sumTotal'],
                    'actual' => $inferActual['sumTotal'],
                    'exam_type' => strtoupper($res['course_type']),
                ];

                if ($download == 'yes') {
                    $item['session'] = $course['session'];
                    $item['course_status'] = str_replace(',', ' -', $item['course_status']);
                }

                $sumEstimate += $inferEstimate['sumTotal'];
                $sumActual += $inferActual['sumTotal'];
                $contents[] = $item;
            }
        }

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

        return [
            'data' => $contents,
            'datetime' => $date,
            'sumEstimate' => $sumEstimate,
            'sumActual' => $sumActual,
        ];
    }

}
