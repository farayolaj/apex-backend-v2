<?php

namespace App\Services\Student;

use App\Enums\CacheEnum;
use App\Libraries\ApiResponse;
use App\Libraries\EntityLoader;
use App\Support\Cache\ShowCacheSupport;
use CodeIgniter\Database\BaseConnection;
use App\Models\WebSessionManager;

class CourseService
{

    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= db_connect();
    }

    private function student()
    {
        return WebSessionManager::currentAPIUser();
    }

    private function normalizeLevel($level): int
    {
        return (string)$level === '501' ? 5 : (int)$level;
    }

    private function activeSemester(): int
    {
        return (int)get_setting('active_semester');
    }

    private function ensurePassportUploaded($student): void
    {
        EntityLoader::loadClass($this, 'course_configuration');
        if (!$this->course_configuration->isPassportCheckValid($student)) {
            throw new \DomainException('Passport must be uploaded before registration');
        }
    }

    private function requirePaymentForCurrentSemester(array $record): void
    {
        $paid = (isset($record['payment_status']) && in_array($record['payment_status'], ['00','01'], true));
        if (!$paid) {
            throw new \DomainException('You must pay your school fees before you can register your courses');
        }
    }

    private function ensureActiveSessionMatches(array $record): void
    {
        if ($record && $record['current_session_code'] != get_setting('active_session_student_portal')) {
            throw new \DomainException('Course registration for the session is not available at this moment');
        }
    }

    public function getCourseConfig(): array
    {
        $student = $this->student();
        $record  = $student->academic_record;

        $status = trim(get_setting('global_course_reg_status'));
        if (!$status) {
            throw new \DomainException('Course registration is closed');
        }

        $level = $this->normalizeLevel($record->current_level);

        EntityLoader::loadClass($this, 'course_configuration');
        if (!$this->course_configuration->registrationIsOpened($record->programme_id, $level, $record->entry_mode)) {
            throw new \DomainException('Course configuration is not available at this moment');
        }

        $this->ensurePassportUploaded($student);

        $param = [
            'programme_id' => $record->programme_id,
            'level'        => $level,
            'entry_mode'   => $record->entry_mode,
        ];
        $config = $this->course_configuration->getWhere($param, $c, 0, null, false);

        if (!$config) {
            return [];
        }

        $out = [];
        foreach ($config as $temp) {
            $out[] = [
                'semester'               => $temp->semester,
                'semester_readable'      => $temp->semesters->name,
                'min_unit'               => $temp->min_unit,
                'max_unit'               => $temp->max_unit,
                'total_units_registered' => $record->getTotalRegisteredCourse($temp->semester),
            ];
        }
        return $out;
    }

    public function assertRegistrationOpen(?string $semester, bool $forDelete): void
    {
        $student = $this->student();
        $record  = $student->academic_record;

        // global check to open/close course reg
        $flag = $forDelete ? 'global_course_unreg_status' : 'global_course_reg_status';
        $status = trim(get_setting($flag));
        if (!$status) {
            throw new \DomainException('Course registration is closed');
        }

        $level = $this->normalizeLevel($record->current_level);
        EntityLoader::loadClass($this, 'course_configuration');
        if (!$this->course_configuration->registrationIsOpened($record->programme_id, $level, $record->entry_mode, $semester)) {
            throw new \DomainException('Course configuration is not available at this moment');
        }
    }

    public function registerCourses(array $courseIds): void
    {
        $student = $this->student();
        $activeSem = $this->activeSemester();

        // transaction-bound session/payment object (like CI3)
        $record = $student->getStudentCurrentSessionPayment($activeSem);
        if(!$record){
            throw new \DomainException("Cannot proceed with registration. No active session payment record found.");
        }

        $this->assertRegistrationOpen(null, false);
        $this->ensurePassportUploaded($student);
        $this->ensureActiveSessionMatches($record);

        // Outstanding-but-paid hard stop
        if ($student->academic_record->outstanding_but_paid === '1') {
            throw new \DomainException("You've an outstanding payment. Please return to dashboard to settle it.");
        }

        $this->requirePaymentForCurrentSemester($record);

        EntityLoader::loadClass($this, 'courses');
        $courseEnrollment = EntityLoader::loadClass(null, 'course_enrollment');

        $this->db->transException(true)->transStart();

        foreach ($courseIds as $courseId) {
            $course = $this->courses->getCourseById(
                $courseId,
                true,
                $record['programme_id_code'],
                null,
                $activeSem
            );
            if (!$course) {
                throw new \DomainException('No course found');
            }

            $semesters = ['first','second'];
            // this ensures only the semester set you can register a course for
            $semesterRegStatus = (int)get_setting('global_course_reg_semester_status'); // 0=both, else 1/2
            if ($semesterRegStatus !== 0 && (int)$course['semester'] !== $semesterRegStatus) {
                $name = $semesters[((int)$course['semester']) - 1] ?? '';
                throw new \DomainException("Course Registration is disabled for {$name} semester");
            }

            if ($student->checkEnrolledCourses($courseId, $record['current_session'], $record['current_level'], false, $activeSem)) {
                $courseName = '"' . $course['course_code'] . ' - ' . $course['course_title'] . '"';
                throw new \DomainException("You've previously registered for {$courseName}, unselect it and continue registration");
            }

            $date = date('Y-m-d H:i:s');
            $enrollment = [
                'student_id'     => $student->id,
                'course_id'      => $courseId,
                'course_unit'    => $course['course_unit'],
                'course_status'  => $course['course_status'],
                'semester'       => $course['semester'],
                'session_id'     => $record['current_session'],
                'student_level'  => $record['current_level'],
                'ca_score'       => null,
                'exam_score'     => null,
                'total_score'    => null,
                'is_approved'    => 1,
                'date_last_update'=> '',
                'date_created'   => $date,
            ];

            $courseEnrollment = new $courseEnrollment($enrollment);
            if (!$courseEnrollment->insert()) {
                throw new \DomainException('Course could not be registered at the moment, try again later');
            }

            if (!$student->checkExamRecord($record['current_session'], $record['current_level'])) {
                $exam = [
                    'student_id'    => $student->id,
                    'session_id'    => $record['current_session'],
                    'student_level' => $record['current_level'],
                    'gpa'           => '',
                    'cgpa'          => '',
                    'active'        => 1,
                    'date_created'  => $date,
                ];
                $examRecord = EntityLoader::loadClass(null, 'exam_record');
                $examRecord = new $examRecord($exam);
                if (!$examRecord->insert()) {
                    throw new \DomainException('Something went wrong while registering your course, try again later');
                }
            }
        }

        $this->db->transComplete();
    }

    public function unregisterCourses(array $courseIds): void
    {
        $student  = $this->student();
        $activeSem= $this->activeSemester();
        $record   = $student->getStudentCurrentSessionPayment($activeSem);
        if(!$record){
            throw new \DomainException("Cannot proceed with unregistration. No active session payment record found.");
        }

        $this->assertRegistrationOpen(null, true);
        $this->ensureActiveSessionMatches($record);
        $this->requirePaymentForCurrentSemester($record);

        EntityLoader::loadClass($this, 'courses');
        EntityLoader::loadClass($this, 'course_enrollment');

        $this->db->transException(true)->transStart();

        foreach ($courseIds as $courseId) {
            $course = $this->courses->getCourseById(
                $courseId,
                true,
                $record['programme_id_code'],
                null,
                $activeSem
            );
            if (!$course) {
                throw new \DomainException('No course found');
            }

            if ((int)$course['semester'] !== $activeSem) {
                throw new \DomainException('Course deletion is unavailable for the semester');
            }

            $enrolled = $student->checkEnrolledCourses($courseId, $record['current_session_code'], $record['current_level'], false, $activeSem);
            if (!$enrolled) {
                $courseName = '"' . $course['course_code'] . ' - ' . $course['course_title'] . '"';
                throw new \DomainException('You have previously deleted ' . $courseName . ' , unselect it and continue');
            }

            if (!$this->course_enrollment->deleteCourse($student->id, $courseId, $record['current_session_code'], $record['current_level'], $activeSem)) {
                $courseName = '"' . $course['course_code'] . ' - ' . $course['course_title'] . '"';
                throw new \DomainException('An error occurred while trying to delete' . $courseName);
            }
        }

        $this->db->transComplete();
    }

    public function getEnrollment($session, $semester): array
    {
        $session = $session ?: get_setting('active_session_student_portal');
        $student = $this->student();

        if (!$student->isValidSession($session, $semester)) {
            throw new \DomainException("It seems you've not completed payment for this semester");
        }

        return ShowCacheSupport::remember(
          CacheEnum::STUDENT_ENROLLMENT->value,
            600,
            fn() => $student->getCourseEnrollmentWithCourseManager($session, $semester),
            $student->id,
            [$session, $semester]
        );
    }

    public function getAllPaidSessionsWithActive(): array
    {
        $student = $this->student();
        $code    = get_setting('school_fees_code');
        $temp    = $student->getAllPaidSession($code);
        $current = (int)get_setting('active_session_student_portal');

        EntityLoader::loadClass($this, 'sessions');
        $activeSessionObj = $this->sessions->getSessionById($current);

        // Ensure active is included even if unpaid
        $result = [];
        $hasActive = false;
        foreach ($temp as $ses) {
            if ((int)$ses['id'] === $current) {
                $hasActive = true;
            }
            $result[] = $ses;
        }
        if (!$hasActive && $activeSessionObj) {
            $result = array_merge((array)$activeSessionObj, $result);
        }
        return $result;
    }

    public function getStats(?string $session, ?string $semester): array
    {
        $student = $this->student();
        $record  = $student->academic_record;

        $totalUnit = $record->getTotalRegisteredCourseUnit($session, $semester);
        $total     = $record->getTotalRegisteredCourses($session, $semester);
        $minMax    = $record->getMinMaxUnit($semester);

        if (!$minMax) {
            // throw new \DomainException("Your minimum/maximum courses units configuration is not available at this time. Contact Records Administrator.");
        }

        return [
            'min_unit'              => $minMax['min_unit'] ?? 0,
            'max_unit'              => $minMax['max_unit'] ?? 0,
            'total_registered'      => $total,
            'total_unit_registered' => $totalUnit,
        ];
    }

    public function listAllCourses(int $semester): array
    {
        $student = $this->student();
        $record  = $student->academic_record;
        $level = $this->normalizeLevel($record->current_level);

        return ShowCacheSupport::remember(
            CacheEnum::STUDENT_PRELOAD_LISTING->value,
            900,
            function () use ($record, $level, $semester) {
                EntityLoader::loadClass($this, 'course_mapping');
                $courses = $this->course_mapping->getCourseLists(
                    $record->programme_id,
                    $level,
                    $record->entry_mode,
                    $semester
                );

                if (!$courses) {
                    throw new \DomainException('No Course(s) available at the moment');
                }

                return $courses;
            },
            $student->id,
            [$record->programme_id, $level, $record->entry_mode, $semester]
        );
    }

    public function searchCourses(string $term): array
    {
        $student  = $this->student();
        $record   = $student->academic_record;
        $semester = (int)(get_setting('active_semester') ?: 1);
        $level = $this->normalizeLevel($record->current_level);
        $needle = mb_strtolower(trim($term));

        return ShowCacheSupport::remember(
            CacheEnum::STUDENT_COURSE_SEARCH->value,
            300,
            function () use ($needle, $level, $semester, $record) {
                EntityLoader::loadClass($this,'course_mapping');
                $courses = $this->course_mapping->searchCourseLists(
                    $needle,
                    $level,
                    $semester,
                    $record->programme_id
                );

                if (!$courses) {
                    throw new \DomainException('No course found');
                }
                return $courses;
            },
            $student->id,
            [$record->programme_id, $level, $semester, $needle]
        );
    }

    public function createTourEnrollment(): array
    {
        $tourCourseId = get_setting('tour_course_id');
        $tourSessionId = get_setting('tour_session_id');
        $student = $this->student();
        $record   = $student->academic_record;

        $course = fetchSingle($this->db, 'courses', 'id', $tourCourseId);
        if(!$course){
            throw new \DomainException('Course not found');
        }

        $activeSemester = get_setting('active_semester');
        $date = date('Y-m-d H:i:s');
        $enrollment = [
            'student_id'     => $student->id,
            'course_id'      => $tourCourseId,
            'course_unit'    => 3,
            'course_status'  => 'E',
            'semester'       => $activeSemester,
            'session_id'     => $tourSessionId,
            'student_level'  => $record->current_level,
            'ca_score'       => null,
            'exam_score'     => null,
            'total_score'    => null,
            'is_approved'    => 1,
            'date_last_update'=> '',
            'date_created'   => $date,
        ];
        if ($student->checkEnrolledCourses($tourCourseId, $tourSessionId, $record->current_level, false, $activeSemester)) {
            return $enrollment;
        }
        $this->db->transException(true)->transStart();

        $this->db->table('course_enrollment')->insert($enrollment);
        $id = (int)$this->db->insertID();
        if (! $id) {
            throw new \RuntimeException('Student enrollment failed');
        }
        $this->db->transComplete();
        $enrollment['id'] = $id;
        return $enrollment;
    }

    public function removeTourEnrollment(): void
    {
        $tourCourseId = get_setting('tour_course_id');
        $tourSessionId = get_setting('tour_session_id');
        $student = $this->student();

        $this->db->transException(true)->transStart();

        $this->db->table('course_enrollment')
            ->where('student_id', $student->id)
            ->where('course_id', $tourCourseId)
            ->where('session_id', $tourSessionId)
            ->delete();

        $this->db->transComplete();
    }

}