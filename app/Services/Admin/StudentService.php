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
}