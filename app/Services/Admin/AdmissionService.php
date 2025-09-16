<?php

namespace App\Services\Admin;

use App\Enums\CommonEnum;
use App\Libraries\EntityLoader;
use App\Models\WebSessionManager;
use App\Support\Csv\CsvReader;
use CodeIgniter\Database\BaseConnection;
use Config\Services;

class AdmissionService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
    }

    /**
     * Main entry for single admission.
     * @throws \DomainException on business rule failure
     */
    public function admitSingle(array $data): array
    {
        $programme       = (int)$data['programme'];
        $adminStatus     = (string)$data['adminStatus'];
        $entryMode       = (string)$data['entryMode'];
        $applicantID     = (string)$data['applicant_id'];
        $teachingSubject = $data['teaching_subject'] ?? null;
        $entity          = $data['entity'] ?? 'applicants';

        $applicant = $this->fetchApplicant($entity, $applicantID);
        if (! $applicant) {
            throw new \DomainException('Applicant record not found');
        }
        if ((int)$applicant['is_admitted'] === 1) {
            throw new \DomainException('Record has been previously moved');
        }

        if ($entity === 'applicants' && !$this->applicantPaidForm((int)$applicant['id'])) {
            throw new \DomainException("Applicant {$applicantID} has not returned a successful transaction for form");
        }

        // For post-UTME entity, require olevel_details
        if ($entity === 'applicant_post_utme' && empty($applicant['olevel_details'])) {
            throw new \DomainException("A minimum of one(1) O'level sitting is required");
        }

        [$admittedLevel, $programmeDuration] = $this->mapEntryMode($entryMode);

        if ($this->alreadyMoved($applicantID)) {
            throw new \DomainException('Record has been previously moved');
        }

        $currentUser = WebSessionManager::currentAPIUser();

        $this->db->transException(true)->transStart();

        $this->updateApplicantAdmission($entity, $applicantID, [
            'programme_given'    => $programme,
            'admission_status'   => $adminStatus,
            'is_admitted'        => ($adminStatus === 'Admitted') ? 1 : 0,
            'admitted_level'     => $admittedLevel,
            'programme_duration' => $programmeDuration,
        ]);

        // Only move data when admitted
        if ($adminStatus === 'Admitted') {
            $studentId = $this->moveApplicantBiodata($entity, $applicantID, $applicant);
            $this->moveApplicantAcademicRecord(
                $studentId,
                $applicantID,
                $programme,
                $admittedLevel,
                $programmeDuration,
                $teachingSubject,
                $entryMode,
                $entity
            );
            $this->moveApplicantMedicalData($studentId, $applicantID, $entity);
            $this->movePassportIfProd($applicant['passport'] ?? null, $studentId);
            logAction('applicant_admit', $currentUser->user_login, $studentId, null, json_encode($applicant));
        } else {
            logAction('changed_applicants_admission_status', $currentUser->user_login);
        }

        $this->db->transComplete();

        $message = ($adminStatus === 'Admitted')
            ? 'You have successfully admitted the applicant'
            : 'Update was successful';

        return ['status' => true, 'message' => $message, 'data' => ['programme' => $programme]];
    }

    /**
     * Bulk CSV: header + rows of:
     * applicant_id, programme, entry, level, duration, teaching_subject
     */
    public function admitBulkFromCsv(
        \CodeIgniter\HTTP\Files\UploadedFile|string $file,
        ?array $headerMap = null,
        int $maxRows = null
    ): array
    {
        $ok = true;
        $reports = [];
        $success  = 0;
        $failed   = 0;
        $total    = 0;

        // Required keys after header remap
        $required = ['applicant_id', 'programme', 'entry'];

        foreach (CsvReader::readAssoc($file, $headerMap, ',', $maxRows, true) as [$rowNo, $row]) {
           $total++;

            try {
                $csvLine = (int)$rowNo + 1;
                $missing = array_values(
                    array_diff($required, array_keys(
                        array_filter($row, fn($v,$k) => $v !== '' && $v !== null, ARRAY_FILTER_USE_BOTH))
                    )
                );
                if ($missing) {
                    $ok = false; $failed++;
                    $reports[] = ['index' => $csvLine, 'message' => "Incomplete row {$csvLine}: missing ".implode(', ', $missing)];
                    continue;
                }

                $programmeId = $this->normalizeProgramme($row['programme']);
                $payload = [
                    'programme'        => $programmeId,
                    'adminStatus'      => 'Admitted',
                    'entryMode'        => $row['entry'],
                    'applicant_id'     => $row['applicant_id'],
                    'teaching_subject' => $row['teaching_subject'] ?? null,
                    'entity'           => 'applicants',
                ];

                $this->admitSingle($payload);
                $success++;
            } catch (\DomainException $e) {
                $ok = false; $failed++;
                $reports[] = ['index' => $csvLine, 'message' => $e->getMessage()];
            } catch (\Throwable $e) {
                $ok = false; $failed++;
                $reports[] = ['index' => $csvLine, 'message' => 'Unexpected error'];
                log_message('error', 'Bulk CSV row {row} failed: {msg}', ['row' => $csvLine, 'msg' => $e->getMessage()]);
            }
        }

        $summary = ['total' => $total, 'success' => $success, 'failed' => $failed];
        return [$ok, $reports, $summary];
    }

    private function mapEntryMode(string $entryMode): array
    {
        if ($entryMode === CommonEnum::O_LEVEL->value || $entryMode === CommonEnum::O_LEVEL_PUTME->value) {
            return ['1', '5'];
        }
        if ($entryMode === CommonEnum::DIRECT_ENTRY->value || $entryMode === CommonEnum::FAST_TRACK->value) {
            return ['2', '4'];
        }
        return ['1', '5'];
    }

    private function fetchApplicant(string $entity, string $applicantID): ?array
    {
        $row = $this->db->table($entity)
            ->select('*')
            ->where('applicant_id', $applicantID)
            ->get()
            ->getRowArray();

        if ($row) return $row;

        if ($entity === 'applicants') {
            return $this->db->table('applicant_post_utme')
                ->select('*')
                ->where('applicant_id', $applicantID)
                ->get()
                ->getRowArray();
        }

        return null;
    }

    private function applicantPaidForm(int $applicantId): bool
    {
        $applicants = EntityLoader::loadClass(null, 'applicants');
        $result = $applicants->applicantPayment($applicantId);
        if (!$result) {
            return false;
        }
        return true;
    }

    private function alreadyMoved(string $applicantID): bool
    {
        return (bool)$this->db->table('academic_record')
            ->groupStart()
            ->where('matric_number', $applicantID)
            ->orWhere('application_number', $applicantID)
            ->groupEnd()
            ->get()
            ->getFirstRow();
    }

    private function updateApplicantAdmission(string $entity, string $applicantID, array $details): void
    {
        $ok = $this->db->table($entity)
            ->where('applicant_id', $applicantID)
            ->update($details);

        if (! $ok) {
            throw new \DomainException('Something went wrong, please try again later');
        }
    }

    private function moveApplicantBiodata(string $entity, string $applicantID, array $applicant): int
    {
        // pass here is for redundancy since we're not using it again
        $salt   = '12345678';
        $pass   = hash('SHA256', $salt . trim(strtolower($applicant['lastname'])));
        $newPass= encode_password(removeNonCharacter(strtolower($applicant['lastname'])));

        // insert-select with bindings
        $sql = "
            INSERT INTO students (
                firstname, othernames, lastname, gender, DoB, phone, marital_status, religion, contact_address,
                postal_address, profession, state_of_origin, lga, nationality, passport, referee, alternative_email,
                user_login, user_pass, active, is_verified, date_created, password
            )
            SELECT firstname, othernames, lastname, gender, dob, phone, marital_status, '', contact_address, '', '',
                   state_of_origin, lga, nationality, passport, referee, LOWER(email),
                   LOWER(email), ?, 1, 0, NOW(), ?
            FROM {$entity} WHERE applicant_id = ?
        ";

        $this->db->query($sql, [$pass, $newPass, $applicantID]);

        return (int)$this->db->insertID();
    }

    private function moveApplicantAcademicRecord(
        int $studentId,
        string $applicantId,
        int $programmeId,
        string $admittedLevel,
        string $durationYears,
        ?string $teachingSubject,
        ?string $entryMode,
        string $entity
    ): void {
        $minMonths = (int)$durationYears * 12;
        $maxMonths = (int)$durationYears * 12;

        $sql = "
            INSERT INTO academic_record(
                student_id, jamb_details, olevel_details, alevel_details, nce_nd_hnd, institutions_attended,
                programme_id, matric_number, has_matric_number, has_institution_email,
                programme_duration, min_programme_duration, max_programme_duration,
                year_of_entry, entry_mode, mode_of_study, interactive_center, exam_center,
                teaching_subject, level_of_admission, session_of_admission, current_level,
                current_session, application_number, applicant_type
            )
            SELECT ?, jamb_details, olevel_details, alevel_details, nce_nd_hnd, institutions_attended,
                   ?, applicant_id, 0, 0, ?, ?, ?, session_id, ?, '', '', '',
                   ?, ?, session_id, ?, session_id, applicant_id, ?
            FROM {$entity}
            WHERE applicant_id = ?
        ";

        $this->db->query($sql, [
            $studentId,
            $programmeId,
            $durationYears,
            $minMonths,
            $maxMonths,
            $entryMode,
            $teachingSubject,
            $admittedLevel,
            $admittedLevel,
            $entity,
            $applicantId
        ]);
    }

    private function moveApplicantMedicalData(int $studentId, string $applicantId, string $entity): void
    {
        $sql = "
            INSERT INTO medical_record (student_id, blood_group, genotype, height, weight, allergy, disabilities, others)
            SELECT ?, '', '', '', '', '', disabilities, ''
            FROM {$entity} WHERE applicant_id = ?
        ";
        $this->db->query($sql, [$studentId, $applicantId]);
    }

    private function movePassportIfProd(?string $passport, int $studentId): void
    {
        if (ENVIRONMENT !== 'production') {
            return;
        }
        movePassport($this->db, $studentId, $passport);
    }

    private function normalizeProgramme($programmeRaw): int
    {
        $row = $this->db->table('programme')
            ->select('id')
            ->where('name', trim((string)$programmeRaw))
            ->get()
            ->getRowArray();

        if (! $row) {
            throw new \DomainException("Cannot find programme: {$programmeRaw}");
        }
        return (int)$row['id'];
    }
}