<?php

require_once 'application/models/Crud.php';
require_once APPPATH . 'constants/ClaimType.php';

/**
 * This class queries those who have paid for both RuS and SuS
 */
class Examination_courses extends Crud {
	protected static $tablename = '';

	static $apiSelectClause = [];

	public function getSingleExaminationCourse($course, $session) {
		$query = "SELECT a.course_id, a.session_id as session,ANY_VALUE(b.code) as code, ANY_VALUE(b.title) as title,
			ANY_VALUE(a.course_unit) as course_unit, COUNT(a.student_id) as enrollment,count(total_score) as scored,
			ANY_VALUE(a.student_level) as student_level,ANY_VALUE(a.is_approved) as is_approved,b.type as course_type from course_enrollment a join
			courses b on b.id = a.course_id where a.course_id = ? and a.session_id = ? group by a.course_id, a.session_id";
		return $this->query($query, [$course, $session]);
	}

	public function getLecturersAssignCourses($session, $currentUser): array {
		$query = "SELECT distinct a.course_id, a.session_id as session,ANY_VALUE(b.code) as code, ANY_VALUE(b.title) as title,
		ANY_VALUE(a.course_unit) as course_unit, COUNT(a.student_id) as enrollment,count(total_score) as scored,
		ANY_VALUE(a.student_level) as student_level,ANY_VALUE(a.is_approved) as is_approved from course_enrollment a join
		courses b on b.id = a.course_id join course_manager c on c.course_id = a.course_id where a.session_id = c.session_id and
		a.session_id = ? and (c.course_manager_id = ? or JSON_SEARCH(c.course_lecturer_id,'one',?) is not null) group by a.course_id, a.session_id";
		$result = $this->query($query, [$session, $currentUser->id, $currentUser->id]);
		if (!$result) {
			return [];
		}
		return $this->processList($result);
	}

	/**
	 * @param mixed $filterList
	 * @param mixed $queryString
	 * @param mixed $start
	 * @param mixed $len
	 * @param mixed $orderBy
	 * @return array
	 */
	public function APIList($filterList, $queryString, $start, $len, $orderBy): array {
		$temp = getFilterQueryFromDict($filterList);
		$filterQuery = buildCustomWhereString($temp[0], $queryString, false);
		$filterValues = $temp[1];

		$filterQuery .= "group by a.course_id, a.session_id";

		if (isset($_GET['sortBy']) && $orderBy) {
			$filterQuery .= " order by $orderBy ";
		} else {
			$filterQuery .= " order by b.code asc ";
		}

		if (isset($_GET['start']) && $len) {
			$start = $this->db->conn_id->escape_string($start);
			$len = $this->db->conn_id->escape_string($len);
			$filterQuery .= " limit $start, $len";
		}
		if (!$filterValues) {
			$filterValues = [];
		}
		$query = "SELECT SQL_CALC_FOUND_ROWS a.course_id, a.session_id as session,ANY_VALUE(b.code) as code, ANY_VALUE(b.title) as title,
 			ANY_VALUE(a.course_unit) as course_unit, COUNT(a.course_id) as enrollment,count(total_score) as scored,
 			ANY_VALUE(a.student_level) as student_level,ANY_VALUE(a.is_approved) as is_approved,b.type as course_type from course_enrollment a join
 			courses b on b.id = a.course_id $filterQuery";
		$query2 = "SELECT FOUND_ROWS() as totalCount";
		$res = $this->db->query($query, $filterValues);
		$res = $res->result_array();
		$res2 = $this->db->query($query2);
		$res2 = $res2->result_array();
		$res = $this->processList($res);
		return [$res, $res2];
	}

	private function processList($items): array {
		loadClass($this->load, 'users_new');
		loadClass($this->load, 'sessions');
		$currentUser = $this->webSessionManager->currentAPIUser();
		for ($i = 0; $i < count($items); $i++) {
			$items[$i] = $this->loadExtras($items[$i], $currentUser);
		}
		return $items;
	}

	public function loadExtras($item, $currentUser) {
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

		if (isset($item['course_lecturer_id'])) {
			$lecturers = !empty($item['course_lecturer_id']) ? json_decode($item['course_lecturer_id'], true) : null;
			$fullname = [];
			if ($lecturers) {
				foreach ($lecturers as $lecturer) {
					$lecturer = $this->users_new->getRealUserInfo($lecturer, 'staffs', 'staff');
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

		$item['can_upload'] = false;
		$item['action_url'] = [
			'view_scores' => site_url('web/examination_scores_list/' . hashids_encrypt($item['course_id']) . '/' . hashids_encrypt($item['session']) . '/' . hashids_encrypt($item['student_level']) . '/' . hashids_encrypt($item['course_manager_id'])),
		];
		if ($item['course_manager_id'] == $currentUser->id) {
			$item['can_upload'] = true;
			$item['action_url'] = [
				'view_scores' => site_url('web/examination_scores_list/' . hashids_encrypt($item['course_id']) . '/' . hashids_encrypt($item['session']) . '/' . hashids_encrypt($item['student_level']) . '/' . hashids_encrypt($item['course_manager_id'])),
				'enter_scores' => site_url('web/examination_scores/' . hashids_encrypt($item['course_id']) . '/' . hashids_encrypt($item['session']) . '/' . hashids_encrypt($item['student_level']) . '/' . hashids_encrypt($item['course_manager_id'])),
				'bulk_upload' => site_url('web/examination_scores/' . hashids_encrypt($item['course_id']) . '/' . hashids_encrypt($item['session']) . '/' . hashids_encrypt($item['student_level']) . '/' . hashids_encrypt($item['course_manager_id'])),
				'bulk_sample_upload' => site_url('web/download_result_sample/' . hashids_encrypt($item['course_id']) . '/' . hashids_encrypt($item['session']) . '/' . hashids_encrypt($item['student_level']) . '/' . hashids_encrypt($item['course_manager_id'])),
			];
		}

		$item['exam_type'] = $item['course_type'] ?: null;
		return $item;
	}

	private function getCourseManagerName($course, $session): ?array {
		$query = "SELECT course_lecturer_id, course_manager_id,concat(c.title,' ',c.lastname,' ',c.firstname) as course_manager
			from course_manager a left join users_new b on b.id = a.course_manager_id left join staffs c on c.id = b.user_table_id
			where a.course_id = ? and a.session_id = ? and a.active = ? and b.user_type='staff' ";
		$result = $this->query($query, [$course, $session, '1']);
		if (!$result) {
			return null;
		}
		$result = $result[0];
		return [$result['course_lecturer_id'], $result['course_manager_id'], $result['course_manager']];
	}

	/**
	 * @deprecated - not in used since we now know in course table
	 */
	public function checkCourseClaims($course, $session) {
		$record = get_single_record($this, 'course_request_claims', [
			'course_id' => $course,
			'session_id' => $session,
			'exam_type' => ClaimType::EXAM_PAPER,
		]);
		if (!$record) {
			return null;
		}
		return $record;
	}

	public function getScoreList($course, $session) {
		$query = "SELECT distinct a.student_id, a.course_id, a.session_id as session, a.student_level,a.ca_score, a.exam_score,
		a.total_score, b.firstname, b.lastname, b.othernames, b.gender, c.matric_number FROM course_enrollment a
        join students b on b.id = a.student_id join academic_record c on c.student_id = b.id where a.course_id = ?
        and a.session_id = ? order by c.matric_number asc";
		return $this->query($query, [$course, $session]);
	}

	public function updateScores($student, $course, $session, $data) {
		$this->db->where([
			'student_id' => $student,
			'course_id' => $course,
			'session_id' => $session,
		]);

		if ($this->db->update('course_enrollment', $data)) {
			return array('affected_rows' => $this->db->affected_rows(), 'status' => true);
		} else {
			return false;
		}
	}

	public function checkUniqueStudentEnrollment($student, $course, $session) {
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
					'data' => ['student' => $result['student_id'], 'ca' => $result['ca_score'], 'exam' => $result['exam_score'], 'total' => $result['total_score']],
				];
			} else {
				return [
					'status' => false,
					'data' => ['student' => $result['student_id']],
				];
			}
		}
	}

	public function getAllAssignedCourses($user, $session) {
		$query = "SELECT distinct a.id as course_id, a.code, a.title,b.session_id FROM courses a
			join course_manager b on a.id = b.course_id where (b.course_manager_id = ? or
			JSON_SEARCH(b.course_lecturer_id,'one',?) is not null) and b.active = ? and a.active = ? and b.session_id = ?
			order by a.code asc";
		return $this->query($query, [$user, $user, '1', '1', $session]);
	}

	public function courseStatsEnrollment($course, $session) {
		$query = "SELECT COUNT(a.student_id) as enrollment,count(total_score) as scored from course_enrollment a join courses b on
			b.id = a.course_id where a.course_id = ? and a.session_id = ? group by a.course_id, a.session_id";
		return $this->query($query, [$course, $session]);
	}

	public function getApprovalStats($session = false) {
		$extra = '';
		if ($session) {
			$extra = " where session_id=$session";
		}
		$total_scored = $extra ? ' and total_score is not null' : " where total_score is not null";
		$query = " select count(total_score) as total_scored_result,(select count(distinct course_id,session_id) from
		course_enrollment $extra $total_scored) as total_scored_courses,count(*) as total_registration,
		(select count(distinct course_id,session_id) from approved_courses $extra) as total_published_courses,
     	(select count(distinct course_id,session_id) from course_enrollment $extra) as total_courses_offerings,
     	(select count(distinct id)  from courses) as total_courses,(select count(distinct course_id) from approved_courses $extra)
     	as total_distinct_approved_courses  from course_enrollment $extra";
		$result = $this->query($query);
		$result = $result[0];

		$result['total_unscored_result'] = $result['total_registration'] - $result['total_scored_result'];
		$result['total_unscored_courses'] = $result['total_courses_offerings'] - $result['total_scored_courses'];
		// $result->total_unapproved_courses_offerings=$result->total_courses_offerings-$result->total_approved;
		$result['total_unpublished_courses'] = $result['total_courses_offerings'] - $result['total_published_courses'];
		return $result;
	}

}
