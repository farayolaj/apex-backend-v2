<?php

namespace App\Entities;

use App\Enums\ClaimEnum as ClaimType;
use App\Enums\SettingSlugEnum as SettingSlug;
use App\Libraries\EntityLoader;
use App\Models\Crud;
use App\Models\WebSessionManager;
use App\Support\DTO\ApiListParams;
use CodeIgniter\Database\BaseBuilder;

/**
 * This class queries those who have paid for both RuS and SuS
 */
class Examination_courses extends Crud
{
    protected static $tablename = '';

    static $apiSelectClause = [];

    protected array  $searchable = ['b.code','b.title'];

    public function getSingleExaminationCourse($course, $session)
    {
        $sessionCondition = SettingSlug::SESSION_GRADED_START->value;
        $query = "SELECT b.id AS course_id, 
       		MAX(a.session_id) as session, b.code, b.title, 
			COALESCE(MAX(a.course_unit), 'N/A') AS course_unit,
    		COUNT(distinct a.student_id) AS enrollment,
    		CASE
 				WHEN c.session_id >= $sessionCondition THEN
					COALESCE(max(c.total_graded), 0)
				ELSE
					COUNT(DISTINCT IF(a.total_score IS NOT NULL, a.student_id, NULL))
			END AS scored,
    		COALESCE(MAX(a.student_level), 0) AS student_level, 
    		COALESCE(MAX(a.is_approved), 0) AS is_approved, 
    		b.type AS course_type FROM courses b 
    		LEFT JOIN course_enrollment a ON a.course_id = b.id AND a.session_id = ?
    		LEFT JOIN course_manager c ON c.course_id = b.id AND c.session_id = a.session_id
            WHERE b.id = ? GROUP BY b.id, b.code, b.title, b.type;";
        return $this->query($query, [$session, $course]);
    }

    public function getLecturersAssignCourses($session, $currentUser): array
    {
        $sessionCondition = SettingSlug::SESSION_GRADED_START->value;
        $query = "SELECT 
			c.course_id, c.session_id as session, 
			MAX(b.code) as code, 
			MAX(b.title) as title,
    		MAX(a.course_unit) as course_unit, 
    		COUNT(distinct a.student_id) as enrollment,
    		CASE
 				WHEN c.session_id >= $sessionCondition THEN
					COALESCE(max(c.total_graded), 0)
				ELSE
					COUNT(DISTINCT IF(a.total_score IS NOT NULL, a.student_id, NULL))
			END AS scored,
    		MAX(a.student_level) as student_level,
    		MAX(a.is_approved) as is_approved,
    		MAX(b.type) as course_type
    		 FROM course_manager c 
    		LEFT JOIN courses b ON b.id = c.course_id 
    		LEFT JOIN course_enrollment a ON a.course_id = c.course_id AND a.session_id = c.session_id 
			WHERE c.session_id = ? AND 
			(
		        c.course_manager_id = ? OR 
		        (JSON_VALID(c.course_lecturer_id) AND JSON_CONTAINS(c.course_lecturer_id, JSON_QUOTE(?), '$'))
		    )
			GROUP BY c.course_id, c.session_id ORDER BY b.code";

        $result = $this->query($query, [$session, $currentUser->id, $currentUser->id]);
        if (!$result) {
            return [];
        }
        return $this->processList($result);
    }

    protected function baseBuilder(): BaseBuilder
    {
        $sessionCondition = SettingSlug::SESSION_GRADED_START->value;
        return $this->db->table('course_enrollment a')
            ->join('courses b', 'b.id = a.course_id')
            ->join('course_manager c', 'c.course_id = a.course_id AND c.session_id = a.session_id', 'left')
            ->select("a.course_id, a.session_id as session,
			max(b.code) as code, 
			max(b.title) as title,
 			max(a.course_unit) as course_unit, COUNT(a.course_id) as enrollment,
 			CASE
 				WHEN a.session_id >= $sessionCondition THEN
					COALESCE(max(c.total_graded), 0)
				ELSE
					count(DISTINCT CASE WHEN a.total_score IS NOT NULL THEN a.student_id END) 
			END AS scored,
 			max(a.student_level) as student_level,
			max(a.is_approved) as is_approved, 
 			b.type as course_type");
    }

    protected function defaultSelect(): string|array
    {
        return '';
    }

    protected function applyDefaultOrder(BaseBuilder $builder): void
    {
        $builder->orderBy('b.code', 'asc');
    }

    protected function postProcess(array $rows): array
    {
        return $this->processList($rows);
    }

    public function APIList($request, $filterList){
        $params = ApiListParams::fromArray($request, [
            'start'    => 1,
            'len' => 20,
        ]);

        $params->filters = $filterList;
        $params->groupBy = " a.course_id, a.session_id, b.type ";

        return $this->listApi(null,
            $params
        );
    }

    /**
     * @param mixed $filterList
     * @param mixed $queryString
     * @param mixed $start
     * @param mixed $len
     * @param mixed $orderBy
     * @return array
     */
    public function APIListOld($filterList, $queryString, $start, $len, $orderBy): array
    {
        $temp = getFilterQueryFromDict($filterList);
        $filterQuery = buildCustomWhereString($temp[0], $queryString, false);
        $filterValues = $temp[1];

        $filterQuery .= "group by a.course_id, a.session_id, b.type";

        if (isset($_GET['sortBy']) && $orderBy) {
            $filterQuery .= " order by $orderBy ";
        } else {
            $filterQuery .= " order by b.code asc ";
        }

        if (isset($_GET['start']) && $len) {
            $start = $this->db->escapeString($start);
            $len = $this->db->escapeString($len);
            $filterQuery .= " limit $start, $len";
        }
        if (!$filterValues) {
            $filterValues = [];
        }
        $sessionCondition = SettingSlug::SESSION_GRADED_START->value;
        $query = "SELECT SQL_CALC_FOUND_ROWS a.course_id, a.session_id as session,
			max(b.code) as code, 
			max(b.title) as title,
 			max(a.course_unit) as course_unit, COUNT(a.course_id) as enrollment,
 			CASE
 				WHEN a.session_id >= $sessionCondition THEN
					COALESCE(max(c.total_graded), 0)
				ELSE
					count(DISTINCT CASE WHEN a.total_score IS NOT NULL THEN a.student_id END) 
			END AS scored,
 			max(a.student_level) as student_level,
			max(a.is_approved) as is_approved, 
 			b.type as course_type from course_enrollment a 
			join courses b on b.id = a.course_id 
			LEFT JOIN course_manager c ON c.course_id = a.course_id AND c.session_id = a.session_id $filterQuery";

        $query2 = "SELECT FOUND_ROWS() as totalCount";
        $res = $this->db->query($query, $filterValues);
        $res = $res->getResultArray();
        $res2 = $this->db->query($query2);
        $res2 = $res2->getResultArray();
        $res = $this->processList($res);
        return [$res, $res2];
    }

    private function processList($items): array
    {
        EntityLoader::loadClass($this, 'users_new');
        EntityLoader::loadClass($this, 'sessions');
        $currentUser = WebSessionManager::currentAPIUser();
        $payload = [];
        foreach(useGenerators($items) as $item) {
            $payload[] = $this->loadExtras($item, $currentUser);
        }
        return $payload;
    }

    public function loadExtras($item, $currentUser)
    {
        if($this->users_new === null || $this->sessions === null) {
            EntityLoader::loadClass($this, 'users_new');
            EntityLoader::loadClass($this, 'sessions');
        }

        if ($item['course_id']) {
            $courseManager = $this->getCourseManagerName($item['course_id'], $item['session']);
            if ($courseManager) {
                $item['course_lecturer_id'] = $courseManager[0];
                $item['course_manager_id'] = $courseManager[1];
                $item['course_manager'] = $courseManager[2];
            } else {
                $item['course_manager'] = null;
                $item['course_manager_id'] = 0;
                $item['course_lecturer_id'] = [];
            }
        }

        $item['can_upload'] = false;
        if (isset($item['course_lecturer_id'])) {
            $lecturers = !empty($item['course_lecturer_id']) ? json_decode($item['course_lecturer_id'], true) : null;
            $fullname = [];
            if ($lecturers) {
                if (in_array($currentUser->id, $lecturers)) {
                    $item['can_upload'] = true;
                }
                foreach ($lecturers as $lecturer) {
                    $lecturer = $this->users_new->getRealUserInfo($lecturer, 'staffs');
                    if ($lecturer) {
                        $fullname[] = $lecturer['title'] . ' ' . $lecturer['lastname'] . ' ' . $lecturer['firstname'];
                    }
                }
            }
            $item['course_lecturer'] = $fullname;
        } else {
            $item['course_lecturer'] = [];
        }

        if ($item['session']) {
            $session = $this->sessions->getSessionById($item['session']);
            $item['session_date'] = $session ? $session[0]['date'] : null;
        }

        $item['action_url'] = [
            'view_scores' => generateBaseUrl('result_manager/examination_scores_list/' . hashids_encrypt($item['course_id']) . '/' . hashids_encrypt($item['session']) . '/' . hashids_encrypt($item['course_manager_id'])),
        ];

        if ($item['course_manager_id'] == $currentUser->id || $item['can_upload']) {
            $item['can_upload'] = true;
            $item['action_url'] = [
                'view_scores' => generateBaseUrl('result_manager/examination_scores_list/' . hashids_encrypt($item['course_id']) . '/' . hashids_encrypt($item['session']) . '/' . hashids_encrypt($item['course_manager_id'])),
                'enter_scores' => generateBaseUrl('result_manager/examination_scores/' . hashids_encrypt($item['course_id']) . '/' . hashids_encrypt($item['session']) . '/' . hashids_encrypt($item['course_manager_id'])),
                'bulk_upload' => generateBaseUrl('result_manager/examination_scores/' . hashids_encrypt($item['course_id']) . '/' . hashids_encrypt($item['session']) . '/' . hashids_encrypt($item['course_manager_id'])),
                'bulk_sample_upload' => generateBaseUrl('result_manager/download_result_sample/' . hashids_encrypt($item['course_id']) . '/' . hashids_encrypt($item['session']) . '/' . hashids_encrypt($item['course_manager_id'])),
            ];
        }

        $item['exam_type'] = $item['course_type'] ?? null;
        $item['event_stats'] = $this->getCourseEventStats($item['course_id'], $item['session']);
        return $item;
    }

    private function getCourseEventStats($course, $session)
    {
        $query = "SELECT 
			COUNT(distinct eem.id) AS total_created,
    		COUNT(DISTINCT pie.events_exams_meta_id) AS total_confirmed
			FROM events_exams_meta eem
			JOIN events e ON e.id = eem.events_id
			LEFT JOIN physical_interactive_event pie ON pie.events_exams_meta_id = eem.id
  				AND pie.course_id = eem.courses_id
  				AND pie.session_id = e.session_id
			WHERE e.session_id = ? and eem.courses_id = ?
			GROUP BY eem.courses_id, e.session_id ORDER BY eem.courses_id";

        $result = $this->query($query, [$session, $course]);
        if (!$result) {
            return [
                'total_created' => '0',
                'total_confirmed' => '0'
            ];
        }
        return $result[0];
    }

    private function getCourseManagerName($course, $session): ?array
    {
        $query = "SELECT course_lecturer_id, course_manager_id,concat(c.title,' ',c.lastname,' ',c.firstname) as course_manager
			from course_manager a left join users_new b on b.id = a.course_manager_id 
			and b.user_type='staff' left join staffs c on c.id = b.user_table_id
			where a.course_id = ? and a.session_id = ? and a.active = ? ";
        $result = $this->query($query, [$course, $session, '1']);
        if (!$result) {
            return null;
        }
        $result = $result[0];
        return [$result['course_lecturer_id'], $result['course_manager_id'], $result['course_manager']];
    }

    public function getCourseLecturerName($course, $session): ?array
    {
        $query = "SELECT max(a.course_lecturer_id) as course_lecturer_id, 
			max(a.course_manager_id) as course_manager_id,
    		GROUP_CONCAT( DISTINCT CONCAT(c2.title, ' ', c2.lastname, ' ', c2.firstname) SEPARATOR ', ') AS lecturers,
    		GROUP_CONCAT( DISTINCT c2.email SEPARATOR ':') AS lecturers_email
				FROM 
    			course_manager a
				LEFT JOIN JSON_TABLE(
    				a.course_lecturer_id,
    				'$[*]' COLUMNS (lecturer_id INT PATH '$')
					) jt ON true
			LEFT JOIN users_new b ON b.id = jt.lecturer_id AND b.user_type = 'staff'
			LEFT JOIN staffs c2 ON c2.id = b.user_table_id
			WHERE a.course_id = ? AND a.session_id = ? AND a.active = ? 
			GROUP BY a.course_id, a.session_id";
        $result = $this->query($query, [$course, $session, '1']);
        if (!$result) {
            return null;
        }
        $result = $result[0];
        return [
            'course_lecturer_id' => $result['course_lecturer_id'],
            'course_manager_id' => $result['course_manager_id'],
            'lecturers_name' => $result['lecturers'],
            'lecturers_email' => $result['lecturers_email']
        ];
    }

    /**
     * @deprecated - not in used since we now know in course table
     */
    public function checkCourseClaims($course, $session)
    {
        $record = get_single_record('course_request_claims', [
            'course_id' => $course,
            'session_id' => $session,
            'exam_type' => ClaimType::EXAM_PAPER->value,
        ]);
        if (!$record) {
            return null;
        }
        return $record;
    }

    public function getScoreList($course, $session)
    {
        $query = "SELECT distinct a.student_id, a.course_id, a.session_id as session, a.student_level,a.ca_score, a.exam_score,
		a.total_score, b.firstname, b.lastname, b.othernames, b.gender, c.matric_number FROM course_enrollment a
        join students b on b.id = a.student_id join academic_record c on c.student_id = b.id where a.course_id = ?
        and a.session_id = ? order by c.matric_number asc";
        return $this->query($query, [$course, $session]);
    }

    public function updateScores($student, $course, $session, $data)
    {
        $builder = $this->db->table('course_enrollment')->where([
            'student_id' => $student,
            'course_id' => $course,
            'session_id' => $session,
        ]);

        if ($builder->update($data)) {
            return array('affected_rows' => $this->db->affectedRows(), 'status' => true);
        } else {
            return false;
        }
    }

    public function checkUniqueStudentEnrollment($student, $course, $session)
    {
        $query = "SELECT * from course_enrollment where student_id=? and course_id=? and session_id=?";
        $results = $this->query($query, [$student, $course, $session]);
        if (!$results) {
            return null;
        }

        foreach ($results as $result) {
            if (($result['student_id'] == $student && !validateScoreIsNull($result['ca_score'])) ||
                ($result['student_id'] == $student && !validateScoreIsNull($result['exam_score']))) {
                return [
                    'status' => true,
                    'data' => [
                        'student' => $result['student_id'],
                        'ca' => $result['ca_score'],
                        'exam' => $result['exam_score'],
                        'total' => $result['total_score']
                    ],
                ];
            } else {
                return [
                    'status' => false,
                    'data' => [
                        'student' => $result['student_id']
                    ],
                ];
            }
        }
    }

    public function getAllAssignedCourses($user, $session)
    {
        // I commented this out because of the active status on course due to an emergency

        // $query = "SELECT distinct a.id as course_id, a.code, a.title,b.session_id FROM courses a
        // 	join course_manager b on a.id = b.course_id where (b.course_manager_id = ? or
        // 	JSON_SEARCH(b.course_lecturer_id,'one',?) is not null) and b.active = ? and a.active = ? and b.session_id = ?
        // 	order by a.code asc";

        $query = "SELECT DISTINCT a.id AS course_id, a.code, a.title, b.session_id
			FROM courses a JOIN course_manager b ON a.id = b.course_id
			WHERE ( b.course_manager_id = ?
    		OR ( JSON_VALID(b.course_lecturer_id) AND JSON_SEARCH(b.course_lecturer_id, 'one', ?) IS NOT NULL
    		)) AND b.active = ? AND b.session_id = ? ORDER BY a.code ASC";
        return $this->query($query, [$user, $user, '1', $session]);
    }

    public function courseStatsEnrollment($course, $session)
    {
        $query = "SELECT COUNT(DISTINCT a.student_id) as enrollment,
       		count(DISTINCT CASE WHEN a.total_score IS NOT NULL THEN a.student_id END) as old_scored,
       		b.total_graded AS scored
			from course_enrollment a 
			JOIN course_manager b ON a.course_id = b.course_id AND a.session_id = b.session_id 
			where a.course_id = ? and a.session_id = ? 
       		group by a.course_id, a.session_id, b.total_graded";
        return $this->query($query, [$course, $session]);
    }

    public function getApprovalStats($session = false)
    {
        $extra = '';
        if ($session) {
            $extra = " where session_id=$session";
        }
        $total_scored = $extra ? ' and total_score is not null' : " where total_score is not null";
        $query = " SELECT COUNT(DISTINCT CASE WHEN total_score IS NOT NULL THEN course_enrollment.student_id END) as total_scored_result,
		(select count(distinct course_id,session_id) from course_enrollment $extra $total_scored) as total_scored_courses, 
		count(*) as total_registration,
		(select count(distinct course_id,session_id) from approved_courses $extra) as total_published_courses,
     	(select count(distinct course_id,session_id) from course_enrollment $extra) as total_courses_offerings,
     	(select count(distinct course_id) from approved_courses $extra) as total_distinct_approved_courses from course_enrollment $extra";
        $result = $this->query($query);
        $result = $result[0];

        $result['total_unscored_result'] = $result['total_registration'] - $result['total_scored_result'];
        $result['total_unscored_courses'] = $result['total_courses_offerings'] - $result['total_scored_courses'];
        // $result->total_unapproved_courses_offerings=$result->total_courses_offerings-$result->total_approved;
        $result['total_unpublished_courses'] = $result['total_courses_offerings'] - $result['total_published_courses'];
        return $result;
    }

}
