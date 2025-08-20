<?php

namespace App\Controllers\Admin\v1;

use App\Controllers\BaseController;
use App\Enums\CommonEnum as CommonSlug;
use App\Libraries\ApiResponse;
use App\Libraries\EntityLoader;
use App\Models\WebSessionManager;
use App\Traits\Crud\EntityListTrait;
use Redis;

class EmailBuilderController extends BaseController
{
    use EntityListTrait;

    public function storeApplicant()
    {
        permissionAccess('bulk_emailer_settings');
        $currentUser = WebSessionManager::currentAPIUser();
        $validation = \Config\Services::validation();
        $validation->setRules([
            'year_of_entry' => 'required|trim',
            'subject'       => 'required|trim',
            'message'       => 'required|trim',
        ]);

        if (! $validation->withRequest($this->request)->run())
        {
            $array = $validation->getErrors();
            return sendApiResponse(false, reset($array));
        }

        $entryYear = $this->request->getPost('year_of_entry') ?? null;
        $programme = $this->request->getPost('programme_of_interest') ?? null;
        $applicationDate = $this->request->getPost('application_date') ?? null;
        $steps = $this->request->getPost('steps') ?? null;
        $subject = $this->request->getPost('subject') ?? null;
        $message = $this->request->getPost('message') ?? null;
        $entryMode = $this->request->getPost('entry_mode') ?? null;
        $admitted = $this->request->getPost('admitted') ?? 'all';

        $builder = $this->db->table('applicants');
        $builder->distinct();
        $builder->select('firstname, lastname, email, applicant_id');

        if (!empty($admitted) && $admitted != "all") {
            $admitted = ($admitted == "yes") ? '1' : '0';
        }

        if (!empty($steps) && $steps != "all" && !is_array($steps)) {
            return sendApiResponse(false, 'Steps must be represented as an array');
        }

        if (!empty($programme) && $programme != "all") {
            $builder->where('programme_id', $programme);
        }
        if (!empty($entryYear) && $entryYear != "all") {
            $builder->where('session_id', $entryYear);
        }
        if (!empty($applicationDate)) {
            $builder->where("date(date_created)", $applicationDate);
        }
        if (!empty($steps) && $steps != "all") {
            $builder->whereIn('step', $steps);
        }
        if (!empty($entryMode) && $entryMode != "all") {
            $builder->where('entry_mode', $entryMode);
        }
        if ($admitted != "all") {
            $builder->where('is_admitted', $admitted);
        }
        $sql1 = $builder->getCompiledSelect();

        $builder2 = $this->db->table('applicant_post_utme');
        $builder2->distinct();
        $builder2->select('firstname, lastname, email, applicant_id');

        if (!empty($programme) && $programme != "all") {
            $builder2->where('programme_id', $programme);
        }
        if (!empty($entryYear) && $entryYear != "all") {
            $builder2->where('session_id', $entryYear);
        }
        if (!empty($applicationDate)) {
            $builder2->where("date(date_created)", $applicationDate);
        }
        if (!empty($steps) && $steps != "all") {
            $builder2->whereIn('step', $steps);
        }
        if (!empty($entryMode) && $entryMode != "all") {
            $builder2->where('entry_mode', $entryMode);
        }
        if ($admitted != "all") {
            $builder2->where('is_admitted', $admitted);
        }
        $sql2 = $builder2->getCompiledSelect();
        $query = $sql1 . ' UNION ' . $sql2;

        $actionType = CommonSlug::EMAIL_BUILDER_APPLICANT->value;
        $lastRefNo = fetchSingle($this->db, 'users_log', 'action_performed', $actionType, ' order by date_performed desc limit 1');
        $emailRef = generateBatchRef(@$lastRefNo['student_id'], 'BEM');
        $fullname = $currentUser->firstname . ' ' . $currentUser->lastname;

        logAction($this->db, $actionType, $fullname, $emailRef, null,
            json_encode([
                'query' => $query,
                'year_of_entry' => $entryYear,
                'programme_of_interest' => $programme,
                'application_date' => $applicationDate,
                'steps' => json_encode($steps),
                'entry_mode' => $entryMode,
                'admitted' => $admitted,
                'subject' => $subject,
                'message' => $message
            ]), $subject);

        $total = $this->processEmail($query, $subject, $message, $actionType, $emailRef);
        if ($total > 0) {
            logAction($this->db, $actionType . '_stats', $fullname, $emailRef, null, json_encode([
                'total' => $total
            ]), $subject);
            return sendApiResponse(true, 'Applicant email sent successfully', [
                'total' => $total
            ]);
        } else {
            return sendApiResponse(false, 'No email records found');
        }
    }

    public function storeStudent()
    {
        permissionAccess('bulk_emailer_settings');
        $currentUser = WebSessionManager::currentAPIUser();

       $validation = \Config\Services::validation();
       $validation->setRules([
           'subject' => 'required|trim',
           'message' => 'required|trim',
       ]);
       if (! $validation->withRequest($this->request)->run()) {
           $errors = $validation->getErrors();
           return ApiResponse::error(reset($errors));
       }

        $entryYear = $this->request->getPost('year_of_entry') ?? null;
        $programme = $this->request->getPost('programme') ?? null;
        $level = $this->request->getPost('level') ?? null;
        $course = $this->request->getPost('course') ?? null;
        $subject = $this->request->getPost('subject') ?? null;
        $message = $this->request->getPost('message') ?? null;
        $session = $this->request->getPost('session') ?? null;
        $entryMode = $this->request->getPost('entry_mode') ?? null;
        $excludeSession = false;
        $activeSession = get_setting('active_session_student_portal');

        $builder = $this->db->table('students');
        $builder->distinct();
        $builder->select('students.firstname, students.lastname, students.user_login as email');
        $builder->join('academic_record', 'academic_record.student_id = students.id');
        if (!empty($course) && $course != "all") {
            if (!$session) {
                $session = $activeSession;
            }
            $excludeSession = true;
            $builder->join('course_enrollment', "course_enrollment.student_id = students.id AND course_enrollment.course_id = '$course' AND course_enrollment.session_id = '$session'");
        }

        if (!empty($session) && !$excludeSession) {
            $builder->join('course_enrollment', "course_enrollment.student_id = students.id AND course_enrollment.session_id = '$session'");
        }

        if (!empty($programme) && $programme != "all") {
            $builder->where('academic_record.programme_id', $programme);
        }
        if (!empty($entryYear) && $entryYear != "all") {
            $builder->where('academic_record.year_of_entry', $entryYear);
        }
        if (!empty($level) && $level != "all") {
            $builder->where('academic_record.current_level', $level);
        }
        if (!empty($entryMode) && $entryMode != "all") {
            $builder->where('academic_record.entry_mode', $entryMode);
        }

        $query = $builder->getCompiledSelect();

        $actionType = CommonSlug::EMAIL_BUILDER_STUDENT->value;
        $lastRefNo = fetchSingle($this->db, 'users_log', 'action_performed', $actionType, ' order by date_performed desc limit 1');
        $emailRef = generateBatchRef(@$lastRefNo['student_id'], 'BEM');
        $fullname = $currentUser->firstname . ' ' . $currentUser->lastname;

        logAction($this->db, $actionType, $fullname, $emailRef, null,
            json_encode([
                'query' => $query,
                'year_of_entry' => $entryYear,
                'programme' => $programme,
                'course' => $course,
                'level' => $level,
                'entry_mode' => $entryMode,
                'subject' => $subject,
                'message' => $message
            ]), $subject);

        $total = $this->processEmail($query, $subject, $message, $actionType, $emailRef);
        if ($total > 0) {
            logAction($this->db, $actionType . '_stats', $fullname, $emailRef, null, json_encode([
                'total' => $total
            ]), $subject);

            return ApiResponse::success('Student email sent successfully', [
                'total' => $total
            ]);
        } else {
            return ApiResponse::error('No email records found');
        }
    }

    private function isValidEmail($email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function processQuery(string $query, $pageSize, $offset): array
    {
        $query = "{$query} LIMIT {$pageSize} OFFSET {$offset}";
        $result = $this->db->query($query);
        return $result->getResultArray();
    }

    private function processQueryTest(string $query, $pageSize, $offset): array
    {
        return [
            [
                'email' => 'holynation667@gmail.com',
                'firstname' => 'John',
                'lastname' => 'holynation',
                'applicant_id' => '123456',
            ],
            [
                'email' => 'holynationdevelopment@gmail.com',
                'firstname' => 'John',
                'lastname' => 'development',
                'applicant_id' => '123456'
            ],
            [
                'email' => 'oluwaseunalatise667@gmail.com',
                'firstname' => 'John',
                'lastname' => 'Alatise',
                'applicant_id' => '123456'
            ],
            [
                'email' => 'farayolajoshua@gmail.com',
                'firstname' => 'John',
                'lastname' => 'Farayola',
                'applicant_id' => '123456'
            ],
            [
                'email' => 'areotimileyin1@gmail.com',
                'firstname' => 'John',
                'lastname' => 'Areo',
                'applicant_id' => '123456'
            ],
            // [
            // 	'email' => 'edutechportal.org@gmail.com',
            // 	'firstname' => 'John',
            // 	'lastname' => 'Edutech',
            // 	'applicant_id' => '123456'
            // ]

        ];
    }

    private function processEmail(string $query, string $subject, string $message, string $emailType, string $emailRef): int
    {
        $redis = service('redis');

        $offset = 0;
        $pageSize = 100;
        $total_enqueued = 0;
        $counter = 1;
        $queueKey = $emailType == CommonSlug::EMAIL_BUILDER_APPLICANT->value ?
            'email:builder:email_queue' : 'email:builder:email_queue_student';

        do {
            $users = $this->processQuery($query, $pageSize, $offset);
            $user_count = count($users);

            log_message('info', "[MAILER_BUILDER_PROGRESS] Enqueued {$counter} emails | Page Size: {$pageSize} | Offset: {$offset}");

            if ($user_count === 0) break; // Exit when there are no more records

            $redis->multi(Redis::PIPELINE);
            foreach ($users as $user) {
                $payload = [];
                $email = $user['email'];
                // validate the email address
                if (!$this->isValidEmail($email)) {
                    log_message('error', '[MAILER_BUILDER_INVALID] Invalid email address: ' . $email);
                    continue;
                }

                $lastname = ucfirst(strtolower($user['lastname']));
                $newMessage = str_replace('{lastname}', $lastname, $message);
                $payload = [
                    'to' => $email,
                    'subject' => $subject,
                    'message' => $newMessage,
                    'attempts' => 0,
                    'max_attempts' => 3,
                    'email_type' => $emailType,
                    'email_ref' => $emailRef
                ];
                $redis->rPush($queueKey, json_encode($payload));
                log_message('info', '[MAILER_BUILDER_QUEUED_SUCCESS] Email queued: ' . $email);
            }
            $redis->exec();

            $total_enqueued += $user_count;
            $offset += $pageSize;
            $counter++;
            if ((ENVIRONMENT === 'development') && $user_count >= 5) break;
        } while ($user_count > 0);

        return $total_enqueued;
    }

    public function show($id)
    {
        EntityLoader::loadClass($this, 'email_logs');
        $start = $this->request->getGet('start') ?? 0;
        $len = $this->request->getGet('len') ?? 20;
        $q = $this->request->getGet('q') ?? false;
        $queryString = null;

        $filterList = [
            'email_ref' => $id,
        ];
        if ($q) {
            $queryString = $this->buildWhereSearchString('email_logs', $q);
        }
        $result = $this->email_logs->APIListLog($filterList, $queryString, $start, $len);
        $result = $this->buildApiListResponse($result);
        return ApiResponse::success('Success', $result);
    }

    //TODO: I want to do this in batch of 100
    public function cleanupStaleEmail()
    {
        $redis = service('redis');

        $emailType = $this->request->getGet('email_type');
        $emailRef = $this->request->getGet('email_ref');
        $queueKey = $this->request->getGet('queue_key') ?? 'email:builder:email_queue';
        $dryRun = filter_var($this->request->getGet('dry_run'), FILTER_VALIDATE_BOOLEAN);
        if (!$emailType || !$emailRef) {
            return ApiResponse::error('Invalid request parameters');
        }

        $allItems = $redis->lrange($queueKey, 0, -1);
        $filteredItems = [];
        $removedItems = [];
        $removedCount = 0;

        foreach ($allItems as $item) {
            $decoded = json_decode($item, true);
            if (!isset($decoded['email_type'], $decoded['email_ref'])) {
                $filteredItems[] = $item;
                continue;
            }

            if ($decoded['email_type'] === $emailType && $decoded['email_ref'] === $emailRef) {
                $removedCount++;
                $removedItems[] = $decoded;
                log_message('info', "Removed from queue: {$decoded['to']}");
                continue;
            }

            $filteredItems[] = $item;
        }

        if (!$dryRun) {
            $redis->del($queueKey);

            foreach ($filteredItems as $entry) {
                $redis->rpush($queueKey, $entry);
            }
        }

        return ApiResponse::success('Stale emails cleaned up', [
            'modified' => !$dryRun,
            'dry_run' => $dryRun,
            'removed_count' => $removedCount,
            'removed_items' => $removedItems,
        ]);

    }
}