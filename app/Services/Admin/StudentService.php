<?php

namespace App\Services\Admin;

use App\Enums\CommonEnum;
use App\Enums\StudentStatusEnum;
use App\Libraries\EntityLoader;
use CodeIgniter\Database\BaseConnection;
use App\Models\WebSessionManager;

class StudentService
{
    public function __construct(private ?BaseConnection $db = null)
    {
        $this->db ??= db_connect();
    }

    private function loadStudent(int $studentId): object
    {
        $student = EntityLoader::loadClass(null, 'students');
        $student->id = $studentId;
        if (!$student->load()) {
            throw new \DomainException('Invalid student info');
        }
        return $student;
    }

    public function createStudent(array $in): array
    {
        $email = strtolower(trim($in['email'] ?? ''));
        $phone = trim($in['phone'] ?? '');
        $matric= trim($in['matric'] ?? '');

        if ($email && $this->exists('students','user_login',$email)) {
            throw new \DomainException("Email address '{$email}' is already in use by another person");
        }
        if ($phone && $this->exists('students','phone',$phone)) {
            throw new \DomainException("Phone number '{$phone}' is already in use by another person");
        }
        if ($matric && $this->exists('academic_record','matric_number',$matric)) {
            throw new \DomainException("Matric number '{$matric}' is already in use by another person");
        }

        $lastname = strtolower(trim($in['lastname']));
        $user_pass = encode_password($lastname);

        $dateNow  = date('Y-m-d H:i:s');
        $bioData = [
            'firstname'         => ucwords(strtolower($in['firstname'])),
            'othernames'        => ucwords(strtolower($in['othernames'] ?? '')),
            'lastname'          => ucwords(strtolower($in['lastname'])),
            'gender'            => $in['gender'],
            'DoB'               => $in['dob'],
            'phone'             => encryptData($phone) ?: '',
            'marital_status'    => $in['marital'],
            'religion'          => $in['religion'] ?? '',
            'contact_address'   => ucwords(strtolower($in['contact_address'] ?? '')),
            'postal_address'    => ucwords(strtolower($in['postal_address'] ?? '')),
            'profession'        => $in['profession'] ?? '',
            'state_of_origin'   => $in['state_of_origin'] ?? '',
            'lga'               => $in['lga'] ?? '',
            'nationality'       => $in['nationality'] ?? '',
            'passport'          => '',
            'full_image'        => '',
            'next_of_kin'       => $in['next_of_kin'] ?? '',
            'next_of_kin_phone' => $in['next_of_kin_phone'] ?? '',
            'active'            => $in['status'] ?? '0',
            'is_verified'       => $in['verification_status'] ?? '0',
            'user_login'        => $email,
            'alternative_email' => strtolower($in['alt_email'] ?? ''),
            'user_pass'         => $user_pass,
            'date_created'      => $dateNow,
            'password'          => $user_pass,
        ];

        $this->db->transException(true)->transStart();

        $this->db->table('students')->insert($bioData);
        $studentId = (int)$this->db->insertID();
        if (! $studentId) {
            throw new \RuntimeException('Insert students failed');
        }

        $academicData = [
            'student_id'             => $studentId,
            'programme_id'           => $in['programme'],
            'matric_number'          => ($matric ?: 'not allocated'),
            'has_matric_number'      => $in['has_matric_number'],
            'has_institution_email'  => $in['has_institution_email'],
            'programme_duration'     => $in['prog_duration'],
            'min_programme_duration' => $in['min_prog_duration'] ?? '',
            'max_programme_duration' => $in['max_prog_duration'] ?? '',
            'year_of_entry'          => $in['entry_year'],
            'entry_mode'             => $in['entry_mode'],
            'interactive_center'     => $in['interactive_center'] ?? '',
            'exam_center'            => $in['exam_center'] ?? '',
            'teaching_subject'       => $in['teaching_subject'] ?? '',
            'level_of_admission'     => $in['admission_level'],
            'current_level'          => $in['level'],
            'current_session'        => $in['current_session'],
            'application_number'     => $in['application_number'] ?? '',
            'session_of_admission'   => $in['session_of_admission'],
        ];
        $this->db->table('academic_record')->insert($academicData);

        $medicalData = [
            'student_id' => $studentId,
            'blood_group'=> $in['blood_grp'] ?? '',
            'genotype'   => $in['genotype'] ?? '',
            'height'     => $in['height'] ?? '',
            'weight'     => $in['weight'] ?? '',
            'allergy'    => $in['allergy'] ?? '',
            'others'     => $in['other_medical'] ?? '',
        ];
        $this->db->table('medical_record')->insert($medicalData);

        $user = WebSessionManager::currentAPIUser();
        logAction('create_student', $user->user_login, $studentId);

        $this->db->transComplete();

        return ['student_id' => $studentId];
    }

    public function updateStudent(int $id, array $in): void
    {
        $email = strtolower(trim($in['email'] ?? ''));
        $phone = trim($in['phone'] ?? '');
        $matric= trim($in['matric'] ?? '');
        $studentObj = EntityLoader::loadClass(null, 'students');
        $c = 0;
        $studentObj = $studentObj->getWhere(['id' => $id], $c, 0, 1, false);
        if(!$studentObj){
            throw new \DomainException("Unable to load student info, please try again");
        }
        $student = $studentObj[0];
        $id = $student->id;
        $status = $in['status'] ?? '0';
        $academicRecord = $student->academic_record->toArray();
        $medicalRecord = fetchSingle($this->db, 'medical_record', 'student_id', $id);

        if ($email && $this->existsOther('students','user_login',$email,'id',$id)) {
            throw new \DomainException("Email address '{$email}' is already in use by another person");
        }
        if ($phone && $this->existsOther('students','phone',$phone,'id',$id)) {
            throw new \DomainException("Phone number '{$phone}' is already in use by another person");
        }
        if ($matric && $this->existsOther('academic_record','matric_number',$matric,'student_id',$id)) {
            throw new \DomainException("Matric number '{$matric}' is already in use by another person");
        }

        if ($status == StudentStatusEnum::GRADUATED->value) {
            $response = $student->studentHasOutstanding(true);
            if ($response != null) {
                throw new \DomainException($response);
            }
        }

        $bioData = [
            'firstname'           => ucwords(strtolower($in['firstname'])),
            'othernames'          => ucwords(strtolower($in['othernames'] ?? '')),
            'lastname'            => ucwords(strtolower($in['lastname'])),
            'gender'              => $in['gender'],
            'DoB'                 => $in['dob'],
            'phone'               => encryptData($phone) ?: '',
            'marital_status'      => $in['marital'],
            'religion'            => $in['religion'] ?? '',
            'contact_address'     => ucwords(strtolower($in['contact_address'] ?? '')),
            'postal_address'      => ucwords(strtolower($in['postal_address'] ?? '')),
            'profession'          => $in['profession'] ?? '',
            'state_of_origin'     => $in['state_of_origin'] ?? '',
            'lga'                 => $in['lga'] ?? '',
            'nationality'         => $in['nationality'] ?? '',
            'next_of_kin'         => $in['next_of_kin'] ?? '',
            'next_of_kin_address' => $in['next_of_kin_addr'] ?? '',
            'next_of_kin_phone'   => $in['next_of_kin_phone'] ?? '',
            'active'              => $in['status'] ?? '0',
            'is_verified'         => $in['verification_status'] ?? '0',
            'user_login'          => $email,
            'alternative_email'   => strtolower($in['alt_email'] ?? ''),
        ];

        $this->db->transException(true)->transStart();

        $this->db->table('students')->where('id',$id)->update($bioData);

        $academicData = [
            'matric_number'          => $matric ?: '',
            'has_matric_number'      => $in['has_matric_number'] ?? null,
            'has_institution_email'  => $in['has_institution_email'] ?? null,
            'programme_duration'     => $in['prog_duration'],
            'min_programme_duration' => $in['min_prog_duration'] ?? '',
            'max_programme_duration' => $in['max_prog_duration'] ?? '',
            'year_of_entry'          => $in['entry_year'],
            'entry_mode'             => $in['entry_mode'],
            'interactive_center'     => $in['interactive_center'] ?? '',
            'exam_center'            => $in['exam_center'] ?? '',
            'teaching_subject'       => $in['teaching_subject'] ?? '',
            'level_of_admission'     => $in['admission_level'],
            'application_number'     => $in['application_number'] ?? '',
            'session_of_admission'   => $in['session_of_admission'],
             // 'current_session'       => $in['session'],
        ];

        if (($academicRecord['current_level'] == '1' && $academicRecord['entry_mode'] == CommonEnum::O_LEVEL->value) ||
            ($academicRecord['current_level'] == '1' && $academicRecord['entry_mode'] == CommonEnum::O_LEVEL_PUTME->value) ||
            ($academicRecord['current_level'] == '2' && $academicRecord['entry_mode'] == CommonEnum::DIRECT_ENTRY->value) ||
            ($academicRecord['current_level'] == '2' && $academicRecord['entry_mode'] == CommonEnum::FAST_TRACK->value)) {
            if($in['programme']) $academicData['programme_id'] = $in['programme'];
        }

        if ($in['level']) {
            $academicData['current_level'] = $in['level'] ?: $academicRecord['current_level'];
        }

        $medicalData = [
            'blood_group'=> $in['blood_grp'] ?? '',
            'genotype'   => $in['genotype'] ?? '',
            'height'     => $in['height'] ?? '',
            'weight'     => $in['weight'] ?? '',
            'allergy'    => $in['allergy'] ?? '',
            'others'     => $in['other_medical'] ?? '',
        ];

        $oldData = [
            'students' => $student->toArray(),
            'academic_record' => $academicRecord,
            'medical_record' => $medicalRecord,
        ];
        $newData = [
            'students' => $bioData,
            'academic_record' => $academicData,
            'medical_record' => $medicalData,
        ];
        $newData = json_encode($newData);
        $oldData = json_encode($oldData);
        $currentUser = WebSessionManager::currentAPIUser();

        $this->db->table('academic_record')->where('student_id',$id)->update($academicData);
        $this->db->table('medical_record')->where('student_id',$id)->update($medicalData);

        logAction('edit_student', $currentUser->user_login, $id, $oldData, $newData);
        $this->db->transComplete();
    }

    private function exists(string $table, string $col, string $val): bool
    {
        return (bool)$this->db->table($table)->select('1')->where($col,$val)->get()->getFirstRow();
    }
    private function existsOther(string $table, string $col, string $val, string $pk, int $id): bool
    {
        return (bool)$this->db->table($table)->select('1')->where($col,$val)->where("$pk !=",$id)->get()->getFirstRow();
    }

    public function getAllRegisteredCourses(int $studentId): array
    {
        $students = $this->loadStudent($studentId);
        $academic = $students->academic_record;

        return [
            'has_payment' => $students->hasPayment($studentId, $academic->current_session),
            'course_registration_log' => $students->getCourseRegistrationLog($studentId),
            'registered_courses' => $students->getAllStudentRegisteredCourses($studentId),
        ];
    }

    public function getAllPaidSessions(int $studentId): array
    {
        $students = $this->loadStudent($studentId);
        return $students->getAllPaidTransactionSession() ?? [];
    }

    private function normalizeLevel($level): int
    {
        return (string)$level === '501' ? 5 : (int)$level;
    }

    public function getRegistrationCoursesForSemester(int $studentId, int $sessionId, int $semester): array
    {
        $students = $this->loadStudent($studentId);
        $paymentRecord = $students->hasPayment($studentId, $sessionId, $semester, true);
        if (! $paymentRecord) {
            throw new \DomainException("It appears the student has not paid school fee for the semester");
        }

        $academic = $students->academic_record;
        $level = $paymentRecord[0]['level'] ?? $academic->current_level;
        $effectiveSession = $sessionId ?: $academic->current_session;

        $courses = $students->getRegistrationCourses(
            $studentId,
            $level,
            $academic->programme_id,
            $academic->entry_mode,
            $effectiveSession,
            $semester
        );

        return $courses ?? [];
    }

    public function registerCourses(int $studentId, int $session, array $courseIds): void
    {
        EntityLoader::loadClass($this, 'courses');

        $students = $this->loadStudent($studentId);
        $academic = $students->academic_record;

        $currentUser = WebSessionManager::currentAPIUser();
        $paidLevel   = $students->hasPayment($studentId, $session, null, true);
        $level       = $paidLevel ? $paidLevel[0]['level'] : $academic->current_level;

        $coursesToAdd = [];
        $logsToAdd    = [];

        foreach ($courseIds as $cid) {
            $courseMap = $students->getCourseMappingDetails($academic->programme_id, $cid, $level);
            $code      = $this->courses->getCourseCodeById($cid);

            if (!$courseMap) {
                throw new \DomainException("Courses cannot be registered, cannot find course mapping details for student, course code: '{$code}'!");
            }

            $courseSemester = (int)$courseMap['semester'];
            $semesterName   = ['first','second'][$courseSemester - 1] ?? '';

            if (!$students->hasPayment($studentId, $session, $courseSemester)) {
                $courseName = $this->courses->getCourseById($cid);
                throw new \DomainException("{$courseName} is a {$semesterName} semester course, and sch-fee for {$semesterName} semester not paid");
            }

            if ($students->alreadyHasRegistration($studentId, $cid, $session, $level)) {
                $courseName = $this->courses->getCourseById($cid);
                throw new \DomainException('You have previously registered for ' . $courseName);
            }

            $date = date('Y-m-d H:i:s');
            $coursesToAdd[] = [
                'student_id'      => $studentId,
                'course_id'       => $cid,
                'session_id'      => $session,
                'student_level'   => $level,
                'is_approved'     => '0',
                'date_last_update'=> '',
                'date_created'    => $date,
                'course_unit'     => $courseMap['course_unit'],
                'course_status'   => $courseMap['course_status'],
                'semester'        => $courseMap['semester'],
            ];

            $logsToAdd[] = [
                'student_id'    => $studentId,
                'course_id'     => $cid,
                'session_id'    => $session,
                'level'         => $level,
                'username'      => $currentUser->user_login,
                'date_created'  => $date,
                'operation'     => 'add_course_registration',
                'course_unit'   => $courseMap['course_unit'],
                'course_status' => $courseMap['course_status'],
            ];
        }

        $this->db->transException(true)->transStart();

        $this->db->table('course_enrollment')->insertBatch($coursesToAdd);
        $this->db->table('course_registration_log')->insertBatch($logsToAdd);

        if ($students->checkExamRecord($session, $level)) {
            update_record($this->db, 'exam_record', 'student_id', $studentId, [
                'student_id' => $studentId,
                'session_id' => $session,
            ]);
        } else {
            $date = date('Y-m-d H:i:s');
            create_record($this->db, 'exam_record', [
                'student_id'    => $studentId,
                'session_id'    => $session,
                'student_level' => $level,
                'gpa'           => '',
                'cgpa'          => '',
                'active'        => 0,
                'date_created'  => $date,
            ]);
        }

        logAction('course_registration', $currentUser->user_login);

        $this->db->transComplete();
    }

    public function deleteRegisteredCourse(int $studentId, int $courseId, int $sessionId, int $levelId): string
    {
        EntityLoader::loadClass($this, 'courses');
        $students = $this->loadStudent($studentId);
        $courseName = $this->courses->getCourseById($courseId);
        if ($students->courseHasScore($studentId, $courseId, $sessionId, $levelId)) {
            throw new \DomainException("An error has occurred, {$courseName} could not be unregistered");
        }

        $ok = $this->courses->deleteCourseRegistration($studentId, $courseId, $sessionId, $levelId);
        if (!$ok) {
            throw new \DomainException('An error has occurred, ' . $courseName . ' could not be unregistered');
        }

        $academic = $students->academic_record;
        $courseMap = $students->getCourseMappingDetails($academic->programme_id, $courseId, $levelId);

        $date = date('Y-m-d H:i:s');
        $user = WebSessionManager::currentAPIUser();
        create_record($this->db, 'course_registration_log', [
            'student_id'   => $studentId,
            'course_id'    => $courseId,
            'session_id'   => $sessionId,
            'level'        => $levelId,
            'username'     => $user->user_login,
            'date_created' => $date,
            'operation'    => 'remove_course_registration',
            'course_unit'  => $courseMap['course_unit'] ?? null,
            'course_status'=> $courseMap['course_status'] ?? null,
        ]);

        return $courseName . ' had been unregistered successfully';
    }

}