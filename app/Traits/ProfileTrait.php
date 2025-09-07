<?php
namespace App\Traits;

trait ProfileTrait
{
    public function updatePassportPath()
    {
        $passport = studentImagePath($this->passport);
        $this->passport = $passport;
        return $this->passport;
    }

    /**
     * @return array<string,mixed>
     */
    public function getDashboardData()
    {
        $result = [];
        $this->updatePassportPath();
        $biodata = $this->toArray();
        unset($biodata['user_pass']);
        unset($biodata['password']);
        unset($biodata['user_login']);
        unset($biodata['id']);

        $academic = $this->academic_record;
        $biodata['matric_number'] = $academic->matric_number;
        $biodata['exam_center'] = $academic->exam_center;
        $result['bioData'] = $biodata;
        $medicalRecord = $this->medical_record ?? null;
        $result['medicalRecord'] = $medicalRecord->toArray();
        $programDetails = $this->getProgramDetails() ?? null;
        if (@$programDetails['level']) {
            $programDetails['level'] = formatStudentLevel($programDetails['level']);
        }
        $result['programmeDetails'] = $programDetails;
        $session = $academic->current_session;
        $result['registered_course'] = $this->getCourseEnrollmentWithCourseManager($session, null, null, 10);
        $result['cgpa'] = null;
        return $result;
    }

    /**
     * @param mixed $examRecord
     * @param mixed $session
     * @param mixed $semester
     * @return bool|<missing>
     */
    public function getProgramDetails($examRecord = false, $session = false, $semester = null)
    {
        $data = [];
        if ($examRecord) {
            $query = "SELECT distinct entry_mode, sessions.date as entry_year, academic_record.current_level as level,
                (select date from sessions s2 where s2.id=course_enrollment.session_id) as current_session,
                academic_record.current_session as current_session_id, department.name as department, programme.name as programme,
                faculty.name as faculty, mode_of_study, entry_mode,course_enrollment.student_level from students left join
                academic_record on academic_record.student_id = students.id left join course_enrollment on course_enrollment.student_id = academic_record.student_id
                join programme on programme.id = academic_record.programme_id join department on department.id = programme.department_id
                join faculty on faculty.id=programme.faculty_id join sessions on sessions.id = academic_record.year_of_entry
                where students.id=? and course_enrollment.student_id = ? and
                course_enrollment.session_id = ?";
            $data = [$this->id, $this->id, $session];
            if ($semester) {
                $semesterName = ($semester && $semester == 'first') ? 1 : 2;
                $query .= " and course_enrollment.semester = ?";
                $data[] = $semesterName;
            }
            $query .= " order by course_enrollment.student_level desc";
        } else {
            $query = "SELECT entry_mode, sessions.date as entry_year, academic_record.current_level as level,
            (select date from sessions where id=academic_record.current_session) as current_session,academic_record.current_session as current_session_id,
            department.name as department, programme.name as programme, faculty.name as faculty, mode_of_study, entry_mode from
             academic_record  join programme on programme.id = academic_record.programme_id join department on department.id = programme.department_id
            join faculty on faculty.id=programme.faculty_id join sessions on sessions.id = academic_record.year_of_entry
             where academic_record.student_id=?";
            $data = [$this->id];
        }
        $result = $this->query($query, $data);
        if (!$result) {
            return false;
        }
        return $result[0];
    }

    public function getProgrammefaculty($id)
    {
        $query = "SELECT a.id,a.name as programme,a.code as programme_code,b.name as faculty,b.slug as faculty_code 
		FROM programme a join faculty b on a.faculty_id = b.id where a.id = ? and a.active = ?";
        return $this->query($query, [$id, '1']);
    }

    public function getFacultyAttendance($student, $programmeID): ?string
    {
        $content = [
            'f_ar' => 'Tues 19 Nov., 2024',
            'f_sc' => 'Tues 19 Nov., 2024',
            'f_so' => 'Mon 18 Nov., 2024',
            'f_ed' => 'Wed 20 Nov., 2024',
            'f_cl' => 'Wed 20 Nov., 2024'
        ];
        $result = $this->getProgrammefaculty($programmeID);
        if ($result) {
            return $content[$result[0]['faculty_code']] ?: null;
        } else {
            return null;
        }
    }

}