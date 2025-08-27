<?php

namespace App\Controllers\Admin\V1;

use App\Controllers\BaseController;
use App\Entities\Examination_courses;
use App\Libraries\ApiResponse;
use App\Libraries\EntityLoader;
use App\Models\WebSessionManager;
use App\Traits\Crud\EntityListTrait;
use App\Traits\ResultManagerTrait;
use App\Traits\UploadTrait;

use App\Enums\AuthEnum as AuthType;
use App\Enums\ClaimEnum as ClaimType;
use App\Enums\CommonEnum as CommonSlug;
use App\Enums\RequestTypeEnum as RequestTypeSlug;
use App\Enums\CourseManagerStatusEnum as CourseManagerStatus;

class ResultManagerController extends BaseController
{
    use ResultManagerTrait, EntityListTrait;

    public function download_result_sample()
    {
        if ($this->input->method(true) !== 'GET') {
            return sendAPiResponse(false, 'Invalid request method');
        }
        loadClass($this->load, 'examination_courses');
        loadClass($this->load, 'courses');

        $course = hashids_decrypt($this->uri->segment(3));
        $session = hashids_decrypt($this->uri->segment(4));
        $level = hashids_decrypt($this->uri->segment(5));

        $prePopulate = $this->input->get('pre_populate');
        if (!$prePopulate) {
            return sendAPiResponse(false, 'Kindly provide the pre-populate parameter');
        }
        $courseCode = $this->courses->getCourseCodeById($course);
        $header = self::sampleHeader();
        if ($prePopulate == 'yes') {
            $result = $this->examination_courses->getScoreList($course, $session);
            $filename = $courseCode . "_pre_populated_" . date('dMy') . "_" . time() . ".csv";
            $body = "";
            foreach ($result as $csvData) {
                $body .= self::sampleBody($csvData);
            }
            return self::downloadSample($filename, $header, $body);
        } else if ($prePopulate == 'no') {
            $filename = "course_score_template_" . date('dMy') . "_" . time() . ".csv";
            $caScore = get_setting('obtainable_ca_score');
            $examScore = get_setting('obtainable_exam_score');
            $totalScore = $caScore + $examScore;
            $body = "
        			<tr>
						<td>000000</td>
						<td>{$caScore}</td>
						<td>{$examScore}</td>
						<td>{$totalScore}</td>
                    </tr>";
            return self::downloadSample($filename, $header, $body);
        }
    }

    public function sessionWithEnrollment()
    {
        EntityLoader::loadClass($this, 'sessions');
        $payload = $this->sessions->getSessionsWithResult();
        return ApiResponse::success('success', $payload);
    }

    public function examinationCourses(){
        $payload = $this->listApiEntity('examination_courses');
        return ApiResponse::success(data: $payload);
    }

    public function assignedListCourses()
    {
        $session = $this->request->getGet('session_id');
        $validation = $this->validateData([
            'session_id' => $session
        ],[
            'session_id' => 'required',
        ]);
        if(!$validation){
            return ApiResponse::error($this->validator->getError('session_id'));
        }

        EntityLoader::loadClass($this, 'examination_courses');
        $currentUser = WebSessionManager::currentAPIUser();
        $payload = $this->examination_courses->getLecturersAssignCourses($session, $currentUser);
        return ApiResponse::success('success', $payload);
    }

    public function examinationScoresList($course, $session, $courseManager)
    {
        // permissionAccess($this, 'exam_scores_list');
        EntityLoader::loadClass($this, 'examination_courses');
        EntityLoader::loadClass($this, 'course_manager');
        EntityLoader::loadClass($this, 'sessions');

        $currentUser = WebSessionManager::currentAPIUser();
        $course = hashids_decrypt($course);
        $session = hashids_decrypt($session);
        $courseManager = hashids_decrypt($courseManager);

        $isCourseManager = $this->course_manager->getCourseManagerByCourseId($course, $session);
        $result = $this->examination_courses->getScoreList($course, $session);
        $courseRow = $this->singleExaminationCourses($course, $session);
        $status = false;
        if (!empty($isCourseManager['course_lecturer_id'])) {
            $courseLecturers = json_decode($isCourseManager['course_lecturer_id'], true);
            if (in_array($currentUser->id, $courseLecturers)) {
                $status = true;
            }
        }
        if (!$status && ($isCourseManager && $isCourseManager['course_manager_id'] == $courseManager) && $currentUser->id == $courseManager) {
            $status = true;
        }
        $payload = [
            'allow_input_score' => $status,
            'ca_scores' => get_setting('obtainable_ca_score'),
            'exam_scores' => get_setting('obtainable_exam_score'),
            'exam_type' => $courseRow['exam_type'],
            'course' => $courseRow,
            'data' => $result ?: [],
        ];
        return ApiResponse::success('success', $payload);
    }

    private function singleExaminationCourses($course, $session)
    {
        EntityLoader::loadClass($this, 'examination_courses');
        $currentUser = WebSessionManager::currentAPIUser();
        if (!$course || !$session) {
            return null;
        }
        $results = $this->examination_courses->getSingleExaminationCourse($course, $session);
        if ($results) {
            $results = $results[0];
            return $this->examination_courses->loadExtras($results, $currentUser);
        }
        return null;
    }

    public function update_examination_type()
    {
        $this->form_validation->set_rules('exam_type', 'exam type', 'trim|required|in_list[cbt,written]', [
            'in_list' => 'Please provide the valid exam type',
        ]);
        $this->form_validation->set_rules('course_id', 'course', 'trim|required');
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                return sendAPiResponse(false, $error);
            }
        }
        $examType = $this->input->post('exam_type', true);
        $courseID = $this->input->post('course_id', true);
        if (!update_record($this, 'courses', 'id', $courseID, [
            'type' => $examType
        ])) {
            return sendAPiResponse(false, "Unable to update course exam type, please try again");
        }
        return sendAPiResponse(true, "Successfully update course exam type");
    }

    /**
     * TODO: Refactor this method to smaller methods and compactible with CI4
     * @param $course
     * @param $session
     * @param $level
     * @param $courseManager
     * @return mixed
     * @throws \Exception
     */
    public function examinationScores($course, $session, $level, $courseManager)
    {
        return ApiResponse::error("Unable to upload result at the moment, please try again later");

        $this->load->library('user_agent');
        EntityLoader::loadClass($this, 'course_manager');
        EntityLoader::loadClass($this, 'examination_courses');
        EntityLoader::loadClass($this, 'sessions');
        EntityLoader::loadClass($this, 'users_new');
        EntityLoader::loadClass($this, 'courses');
        EntityLoader::loadClass($this, 'course_request_claims');
        $currentUser = WebSessionManager::currentAPIUser();
        if (!$currentUser->email) {
            return sendAPiResponse(false, "Kindly update your email in your profile");
        }
        $course = hashids_decrypt($course);
        $session = hashids_decrypt($session);
        $level = hashids_decrypt($level);
        $courseManager = hashids_decrypt($courseManager);

        $isCourseManager = $this->course_manager->getCourseManagerByCourseId($course, $session);
        $isUploadStatus = false;
        if (!empty($isCourseManager['course_lecturer_id'])) {
            $courseLecturers = json_decode($isCourseManager['course_lecturer_id'], true);
            if (in_array($currentUser->id, $courseLecturers)) {
                $isUploadStatus = true;
            }
        }
        if (!$isUploadStatus && (!$isCourseManager || $isCourseManager['course_manager_id'] != $courseManager || $currentUser->id != $courseManager)) {
            return ApiResponse::error( "You are not authorized to upload result");
        }
        $processType = $this->input->post('type', true) ?: 'file';
        if ($processType === 'direct') {
            $this->form_validation->set_rules('ca_score[]', 'ca score', 'trim|numeric|required|is_natural');
            $this->form_validation->set_rules('exam_score[]', 'exam score', 'trim|numeric|required|is_natural');
            $this->form_validation->set_rules('total_score', 'total score', 'trim|numeric|is_natural');
        }
        $this->form_validation->set_rules('type', 'type', 'trim|required|in_list[direct,file]', [
            'in_list' => 'Please provide the valid type',
        ]);
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            return sendAPiResponse(false, reset($errors));
        }
        $caScore = $this->input->post('ca_score', true);
        $examScore = $this->input->post('exam_score', true);
        $studentId = $this->input->post('student_id', true);
        $matricNumber = $this->input->post('matric_number', true);
        $courseRow = $this->courses->getCourseByIdOnly($course);
        $examType = $courseRow['type'];
        $sessionName = $this->sessions->getSessionById($session);
        $sessionName = $sessionName ? $sessionName[0]['date'] : null;
        $logAction = 'exam_score_input';
        $payload = null;

        $courseStats = $this->examination_courses->courseStatsEnrollment($course, $session);
        $enrolled = @$courseStats[0]['enrollment'] ?: 0;
        $courseClaims = [
            'course_id' => $course,
            'session_id' => $session,
            'course_manager_id' => $courseManager,
            'exam_type' => $examType,
            'enrolled' => $enrolled,
            'with_score' => 0,
        ];

        $result = null;
        if ($processType === 'file') {
            $config['upload_path'] = FCPATH . 'temp/result_csv/';
            $config['allowed_types'] = 'csv';
            $config['max_size'] = '2048';
            $this->load->library('upload', $config);
            $this->upload->initialize($config);
            if (!$this->upload->do_upload('score_file')) {
                $error = $this->upload->display_errors('', '');
                return sendAPiResponse(false, $error);
            }

            if (isset($_FILES['score_file']['name']) && !empty($_FILES['score_file']['name'])) {
                $uploaded_data = $this->upload->data();
                $filePath = $uploaded_data['full_path'];
                $logFile = $courseRow['code'] . '_log_' . date('Y-mM-dl h:i:s') . '_' . time() . '.txt';
                $logPath = FCPATH . "temp/logs/$logFile";
                $logPathLink = self::generateLink($logPath);

                $content = UploadTrait::loadUploadedFileContent('score_file', false, $message);
                if ($content === false) {
                    deleteFile($filePath);
                    return sendAPiResponse(false, $message);
                }
                $content = trim($content);
                $array = stringToCsv($content);
                $csv_headers = array_shift($array);
                if (empty($array)) {
                    deleteFile($filePath);
                    return sendAPiResponse(false, "Dataset cannot be empty");
                }

                if ($csv_headers[0] != 'matric_number') {
                    deleteFile($filePath);
                    return sendAPiResponse(false, "Matric Number (matric_number) column is not present in the csv file, Please download the sample data as a guide to build your csv");
                } else if ($csv_headers[1] != 'ca_scores') {
                    deleteFile($filePath);
                    return sendAPiResponse(false, "Continuous Assessment (ca_scores) column is not present in the csv file, Please download the sample data as a guide to build your csv");
                } else if ($csv_headers[2] != 'exam_scores') {
                    deleteFile($filePath);
                    return sendAPiResponse(false, "Examination Score (exam_scores) column is not present in the csv file, Please download the sample data as a guide to build your csv");
                } else {
                    $payload = [
                        'currentUser' => $currentUser,
                        'courseManager' => $courseManager,
                        'sessionName' => $sessionName,
                        'courseRow' => $courseRow,
                        'course' => $course,
                        'session' => $session,
                        'level' => $level,
                        'fileInCsv' => $filePath,
                        'courseClaims' => $courseClaims,
                        'logPath' => $logPath,
                    ];

                    $this->db->trans_begin();
                    $result = $this->bulkExaminationScoreUpload($array, $payload);
                    if (isset($result['status']) && !$result['status']) {
                        $this->db->trans_rollback();
                        deleteFile($filePath);
                        return sendAPiResponse(false, $result['message']);
                    }

                    if ($filePath) {
                        $attachment = [$filePath, $logPath];
                        if (!$this->sendUploadEmailCopy($currentUser, $courseRow, $sessionName, $attachment, $logPathLink)) {
                            $this->db->trans_rollback();
                            return sendAPiResponse(false, 'Error sending notification, please try again later.');
                        }
                    }

                    $logAction = "bulk_exam_score_input";
                    $payload = [
                        'url_log_link' => $logPathLink,
                    ];
                    deleteFile($filePath);
                }
            }
        } else {
            // NOTE: If this would be used, kindly check the code because this was later
            // abandoned, thus code might be outdated and not applicable for present usecase
            if (is_array($caScore) && is_array($examScore) && is_array($studentId) && is_array($matricNumber)) {
                $this->db->trans_begin();
                $caTotal = get_setting('obtainable_ca_score');
                $examTotal = get_setting('obtainable_exam_score');
                $withScore = 0;
                foreach ($studentId as $key => $student) {
                    $date = date('Y-m-d H:i:s');
                    $caScoreRow = $caScore[$key];
                    $examScoreRow = $examScore[$key];
                    $matricNumber = $matricNumber[$key];

                    if ((int)$caScoreRow > (int)$caTotal) {
                        $this->db->trans_rollback();
                        return sendAPiResponse(false, "Continuous Assessment score for {$matricNumber} is greater than the max obtainable score");
                    } else if ((int)$examScoreRow > (int)$examTotal) {
                        $this->db->trans_rollback();
                        return sendAPiResponse(false, "Exam score for {$matricNumber} is greater than the max obtainable score");
                    } else {
                        $checkStudent = $this->examination_courses->checkUniqueStudentEnrollment($student, $course, $session);
                        if (!$checkStudent) {
                            return sendAPiResponse(false, "Student with Matric Number: " . $matricNumber . " not found in the course enrollment");
                        }

                        $totalScore = (int)($caScoreRow) + (int)($examScoreRow);
                        $payloadData = [
                            'ca_score' => $caScoreRow,
                            'exam_score' => $examScoreRow,
                            'total_score' => $totalScore,
                            'date_last_update' => $date,
                        ];
                        if ($checkStudent['status']) {
                            $result = $this->examination_courses->updateScores($student, $course, $session, $payloadData);
                        } else {
                            $withScore++;
                            $result = $this->examination_courses->updateScores($student, $course, $session, $payloadData);
                        }
                    }
                }
                $courseClaims['with_score'] = $withScore;
                if ($withScore > 0) {
                    $this->processCourseClaims($courseClaims);
                }
            } else {
                return sendAPiResponse(false, "Something went wrong, one or more field is not an array");
            }
        }

        if ($result['status']) {
            $this->db->trans_commit();
            logAction($this, $logAction, $currentUser->user_login);
            return sendAPiResponse(true, 'Scores updated successfully', $payload);
        }
        $this->db->trans_rollback();
        return sendAPiResponse(false, 'Scores cannot be updated, something went wrong!');
    }

    private function bulkExaminationScoreUpload(array $array, array $payload): array
    {
        $progressLog = array();
        $currentUser = $payload['currentUser'];
        $courseManager = $payload['courseManager'];
        $courseRow = $payload['courseRow'];
        $sessionName = $payload['sessionName'];
        $course = $payload['course'];
        $session = $payload['session'];
        $level = $payload['level'];
        $fileInCsv = $payload['fileInCsv'];
        $courseClaims = $payload['courseClaims'];
        $logPath = $payload['logPath'];

        $lecturer = $this->users_new->getRealUserInfo($courseManager, 'staffs', 'staff');
        $fullname = null;
        if ($lecturer) {
            $fullname = $lecturer['title'] . ' ' . $lecturer['lastname'] . ' ' . $lecturer['firstname'];
        }
        $courseName = '"' . $courseRow['code'] . ' - ' . $courseRow['title'] . '"';

        $progressLog[] = "Process started " . date('l F d, Y h:i:s') . PHP_EOL;
        $progressLog[] = "Course Manager: " . $fullname . PHP_EOL;
        $progressLog[] = "Username: " . $currentUser->user_login . PHP_EOL;
        $progressLog[] = "User Agent: " . $this->agent->agent_string() . PHP_EOL;
        $progressLog[] = "Browser: " . $this->agent->browser() . " Version: " . $this->agent->version() . PHP_EOL;
        $progressLog[] = "IP Address: " . $this->input->ip_address() . PHP_EOL;
        $progressLog[] = "Platform: " . $this->agent->platform() . PHP_EOL;
        $progressLog[] = "Hostname: " . gethostname() . PHP_EOL;
        $progressLog[] = "Course Code: " . strtoupper($courseRow['code']) . " - " . $courseName . PHP_EOL;
        $progressLog[] = "Academic Session: " . $sessionName . PHP_EOL;
        $progressLog[] = "_____________________________________________________________________________" . PHP_EOL . PHP_EOL . PHP_EOL;

        $withScore = 0;
        $uploadedCount = 0;
        $errorCount = 0;
        $successCount = 0;
        $updatedCount = 0;
        $insertionCount = 0;

        loadClass($this->load, 'students');
        foreach ($array as $key => $row) {
            $matricNumber = $row[0];
            $caScore = $row[1];
            $examScore = $row[2];
            $totalScore = (int)($caScore) + (int)($examScore);

            if ($totalScore > 100) {
                return ['status' => false, 'message' => "Total score for {$matricNumber} cannot be greater than 100"];
            } else {
                $uploadedCount++;
                if (validateScoreIsNull($caScore) && validateScoreIsNull($examScore)) {
                    $errorCount++;
                    $progressLog[] = "Error incomplete data for Matric number " . $matricNumber . PHP_EOL;
                    continue;
                }

                $student = $this->students->getStudentIdByMatricNumber($matricNumber);
                if (!$student) {
                    $errorCount++;
                    $progressLog[] = "Student with Matric Number: " . $matricNumber . " not found" . PHP_EOL;
                    continue;
                }
                $checkStudent = $this->examination_courses->checkUniqueStudentEnrollment($student, $course, $session);
                if (!$checkStudent) {
                    $errorCount++;
                    $progressLog[] = "Student with Matric Number: " . $matricNumber . " not found in the course enrollment" . PHP_EOL;
                    continue;
                }
                $date = date('Y-m-d H:i:s');
                $payload = [
                    'ca_score' => $caScore,
                    'exam_score' => $examScore,
                    'total_score' => $totalScore,
                    'date_last_update' => $date,
                ];

                if ($checkStudent['status']) {
                    if (!$this->examination_courses->updateScores($student, $course, $session, $payload)) {
                        $errorCount++;
                        $progressLog[] = "Error updating Matric number " . $matricNumber . " with scores CA - " . $checkStudent['data']['ca'] . " Exam - " . $checkStudent['data']['exam'] . " Total - " . $checkStudent['data']['total'] . PHP_EOL;
                        continue;
                    } else {
                        $successCount++;
                        $updatedCount++;
                        $progressLog[] = "Matric number " . $matricNumber . " with scores CA - " . $checkStudent['data']['ca'] . " Exam - " . $checkStudent['data']['exam'] . " Total - " . $checkStudent['data']['total'] . " has been updated with CA - " . $caScore . " Exam - " . $examScore . " Total - " . $totalScore . PHP_EOL;
                    }
                } else {
                    if (!$this->examination_courses->updateScores($student, $course, $session, $payload)) {
                        $errorCount++;
                        $progressLog[] = "Error inserting new record for Matric number " . $matricNumber . " as follows: CA - " . $caScore . " Exam - " . $examScore . " Total - " . $totalScore . PHP_EOL;
                        continue;
                    } else {
                        $withScore++;
                        $successCount++;
                        $insertionCount++;
                        $progressLog[] = "New Record has been inserted for Matric number " . $matricNumber . " as follows: CA - " . $caScore . " Exam - " . $examScore . " Total - " . $totalScore . PHP_EOL;
                    }
                }
            }
        }

        $progressLog[] = "_____________________________________________________________________________" . PHP_EOL . PHP_EOL . PHP_EOL;
        $progressLog[] = "Total Uploaded: {$uploadedCount}" . PHP_EOL;
        $progressLog[] = "Total Insertion: {$insertionCount}" . PHP_EOL;
        $progressLog[] = "Total Updated: {$updatedCount}" . PHP_EOL;
        $progressLog[] = "Total Error: {$errorCount}" . PHP_EOL;
        $progressLog[] = "Total Success: {$successCount}" . PHP_EOL;
        $progressLog[] = "_____________________________________________________________________________" . PHP_EOL . PHP_EOL . PHP_EOL;

        $courseClaims['with_score'] = $withScore;
        if ($insertionCount > 0) {
            $this->processCourseClaims($courseClaims);
        }
        $logPath = $this->buildProcessLog($logPath, $progressLog);
        return ['status' => true, 'message' => "Scores uploaded successfully", 'logLink' => $logPath];
    }

    /**
     * @param array|null $courseClaims
     * @return void
     * @deprecated - This would no longer be needed once external RMS integration is pushed and live
     */
    private function processCourseClaims(?array $courseClaims)
    {
        $record = get_single_record('course_request_claims', [
            'course_id' => $courseClaims['course_id'],
            'session_id' => $courseClaims['session_id'],
            'status' => 0,
            'exam_type' => @$courseClaims['exam_type'] ?: ClaimType::EXAM_PAPER,
        ]);
        if ($record) {
            $formerScore = $record->with_score;
            $newScoreCount = $formerScore + $courseClaims['with_score'];
            if ($newScoreCount <= $record->enrolled) {
                update_record($this, 'course_request_claims', 'id', $record->id, [
                    'with_score' => $newScoreCount,
                ]);
            }
        } else {
            $enrolled = $courseClaims['enrolled'];
            $totalClaims = $this->course_request_claims->getSumCourseScoreClaims($courseClaims['course_id'], $courseClaims['session_id']);
            // total already claims + new claims
            $total = $totalClaims + $courseClaims['with_score'];
            // this should not be possible under normal circumstances
            if ($total > $enrolled) {
                $courseClaims['with_score'] = $enrolled - $totalClaims;
            }
            $this->db->insert('course_request_claims', $courseClaims);
        }
    }

    private function buildProcessLog(string $logPath, array $progressLog): string
    {
        // open a temporary file handle in memory
        $tmpLogHandle = fopen($logPath, 'a+');
        foreach ($progressLog as $progress_log) {
            fwrite($tmpLogHandle, $progress_log);
        }
        fclose($tmpLogHandle);

        return $logPath;
    }

    private function sendUploadEmailCopy(object $currentUser, ?array $course, string $sessionName, $filename = null, string $progressLog = null)
    {
        $this->load->model('Mailer');
        $courseName = '"' . $course['code'] . ' - ' . $course['title'] . '"';
        $ccList = ENVIRONMENT !== 'production' ? [] : array(
            'edutechportal.org@gmail.com',
            'edutechportal@dlc.ui.edu.ng',
            'ebomobowale@yahoo.com'
        );
        $subject = 'ATTENTION! ' . $course['code'] . ' Notification of Student Results Upload';
        $recipient = ENVIRONMENT !== 'production' ? 'holynationdevelopment@gmail.com' : $currentUser->email;
        $lecturerName = $currentUser->title . " " . $currentUser->lastname . " " . $currentUser->firstname . " " . $currentUser->othernames;
        $semester = get_setting('active_semester');
        $semester = ($semester != '' && $semester == '1') ? 'First' : 'Second';

        $variables = array(
            'course' => $courseName,
            'lecturer_name' => $lecturerName,
            'course_name' => $course['title'],
            'session' => $sessionName,
            'semester' => $semester,
            'date_of_upload' => date('Y-m-d H:i:s'),
            'progressLog' => $progressLog,
        );
        return Mail::sendUploadCopyEmailNotification($recipient, $variables, $subject, $ccList, $filename);
    }

    /**
     * @param array $matricNumber
     * @param array $caScores
     * @param array $examScores
     * @param $courseCode
     * @return string|null
     * @deprecated - Not in use again since the FE is now sending the raw CSV
     */
    private function buildResultCsv(array $matricNumber, array $caScores, array $examScores, $courseCode)
    {
        $header = self::sampleHeader();
        $body = "";
        loadClass($this->load, 'courses');
        $filename = $courseCode . "_pre_populated_" . date('dMy') . "_" . time() . ".csv";
        foreach ($matricNumber as $key => $student) {
            $caScore = $caScores[$key];
            $examScore = $examScores[$key];
            $totalScore = (int)($caScore) + (int)($examScore);
            $payload = [
                'ca_score' => $caScore,
                'exam_score' => $examScore,
                'total_score' => $totalScore,
                'matric_number' => $student,
            ];
            $body .= self::sampleBody($payload);
        }
        return self::downloadSample($filename, $header, $body, false);
    }

    public function examination_result_export()
    {
        permissionAccess($this, 'result_report');
        loadClass($this->load, 'students');
        loadClass($this->load, 'programme');
        loadClass($this->load, 'sessions');
        loadClass($this->load, 'grades');
        loadClass($this->load, 'course_enrollment');
        loadClass($this->load, 'courses');

        $programme = $this->input->post('programme', TRUE);
        $session = $this->input->post('session', TRUE);
        $previousSession = $this->input->post('previous_session', TRUE);
        $level = $this->input->post('level', TRUE);
        $semester = $this->input->post('semester', TRUE);
        $reportFormat = $this->input->post('report_format', TRUE);
        $report_type = $this->input->post('report_type', TRUE);
        $course = $this->input->post('course', TRUE);

        $this->form_validation->set_rules('programme', 'programme', 'trim|required');
        $this->form_validation->set_rules('session', 'session', 'trim|required');
        $senateCover = array('senate_summary', 'gradesheet', 'senate_cover');
        if (in_array($reportFormat, $senateCover)) {
            $this->form_validation->set_rules('previous_session', 'previous session', 'trim|required');
        }
        $this->form_validation->set_rules('level', 'level', 'trim|required');
        $this->form_validation->set_rules('semester', 'semester', 'trim');
        $this->form_validation->set_rules('report_format', 'report format', 'trim|required');
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                return sendAPiResponse(false, $error);
            }
        }

        if ($reportFormat === 'senate_summary') {
            $url = 'web/senate_export_html/' . hashids_encrypt($programme) . '/' . hashids_encrypt($session) . '/' . hashids_encrypt($previousSession) . '/' . hashids_encrypt($level) . '/' . hashids_encrypt($semester);
            $url = self::generateReportLink('senate_summary.html', $url);
            sendAPiResponse(true, 'success', ['report_link' => $url]);
        } else if ($reportFormat === 'gradesheet') {
            $url = 'web/senate_gradesheet_export_html/' . hashids_encrypt($programme) . '/' . hashids_encrypt($session) . '/' . hashids_encrypt($level) . '/' . hashids_encrypt($semester);
            $url = self::generateReportLink('gradesheet.html', $url);
            sendAPiResponse(true, 'success', ['report_link' => $url]);
        } else if ($reportFormat === 'senate_cover') {
            $url = 'web/senate_cover_export_html/' . hashids_encrypt($programme) . '/' . hashids_encrypt($session) . '/' . hashids_encrypt($level) . '/' . hashids_encrypt($semester);
            $url = self::generateReportLink('senate_cover.html', $url);
            sendAPiResponse(true, 'success', ['report_link' => $url]);
        } else if ($reportFormat === 'ui_result_of_course') {
            $url = 'web/ui_result_course_export_html/' . hashids_encrypt($programme) . '/' . hashids_encrypt($session) . '/' . hashids_encrypt($semester) . '/' . hashids_encrypt($course);
            $url = self::generateReportLink('ui_result_of_course.html', $url);
            sendAPiResponse(true, 'success', ['report_link' => $url]);
        }
    }

    public function assigned_course_list($sessionOnly = null)
    {
        loadClass($this->load, 'examination_courses');
        $currentUser = $this->webSessionManager->currentAPIUser();
        $session = $sessionOnly ?: $this->input->get('session');
        $result = $this->examination_courses->getAllAssignedCourses($currentUser->id, $session);
        if ($sessionOnly) {
            return $result;
        }
        return sendAPiResponse(true, 'success', $result ?: []);
    }

    private function inferCadreTitle(): string
    {
        $currentUser = $this->webSessionManager->currentAPIUser();
        $title = strtolower($currentUser->title);
        $title = rtrim($title, '.');
        $professorTitles = ['prof', 'professor', 'reader'];

        foreach ($professorTitles as $profTitle) {
            if (strpos($title, $profTitle) === 0) {  // Check if title starts with prof title
                return CommonSlug::PROF_RANK;
            }
        }
        return CommonSlug::LECTURER_RANK;
    }

    private function isGESCourse($courseCode): bool
    {
        return isGESCourse($courseCode);
    }

    private function calcEssentialAmountPercentage($courseManager, $total): array
    {
        $percentageRemove = [];
        $tempTotal = $total;
        if ($courseManager && $courseManager['essential_inline_waiver']) {
            $essentialInlineWaiver = json_decode($courseManager['essential_inline_waiver'], true);
            if ($essentialInlineWaiver) {
                foreach ($essentialInlineWaiver as $key => $value) {
                    $percentageInfo = self::onlineAssessmentPercentage();
                    $percentageData = array_filter($percentageInfo, function ($item) use ($key) {
                        return $item['id'] == $key;
                    });
                    $percentageData = reset($percentageData);
                    if ($percentageData && $value == CourseManagerStatus::UNQUALIFIED) {
                        $percentageRemove[$key] = [
                            'percentage' => $percentageData['value'],
                            'amount' => $tempTotal * $percentageData['value']
                        ];
                        $tempTotal -= $percentageRemove[$key]['amount'];
                    }
                }
                $percentageRemove = count($percentageRemove) > 0 ? json_encode($percentageRemove) : null;
            }
        } else {
            $percentageRemove = null;
        }

        return ['percentage_remove' => $percentageRemove, 'sum_total' => $tempTotal];
    }

    public function preview_request_claim()
    {
        ini_set('max_execution_time', 120);

        loadClass($this->load, 'course_request_claims');
        loadClass($this->load, 'courses');
        loadClass($this->load, 'examination_courses');
        loadClass($this->load, 'course_manager');
        loadClass($this->load, 'course_committee');
        $session = $this->input->get('session', true);
        $isCadre = $this->input->get('cadre', true) ?: 'no';

        $this->form_validation->set_data([
            'session' => $session,
        ]);
        $this->form_validation->set_rules('session', 'session', 'trim|required');
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            return sendAPiResponse(false, reset($errors));
        }
        $currentUser = $this->webSessionManager->currentAPIUser();
        $courses = $this->assigned_course_list($session);
        $cadreTitle = $isCadre === 'yes' ? $this->inferCadreTitle() : null;
        if (!$courses) {
            return sendAPiResponse(false, "No course assigned to you yet for this session");
        }

        $result = [];
        $allowedAllowance = false;
        if ($courses) {
            $courses = useGenerators($courses);
            foreach ($courses as $item) {
                $facilitation = 'yes';
                $interaction = 'yes';
                $course = $item['course_id'];
                $temp = [];
                if (!$course) {
                    return sendAPiResponse(false, 'Course not found');
                }

                $calculatedClaimsData = $this->calculateClaimScore($course, $session);
                $enrolled = $calculatedClaimsData['enrolled'];
                // $courseWithScore = $calculatedClaimsData['with_score'];
                $courseWithScore = $enrolled;

                $courseRow = $this->courses->getCourseByIdOnly($course);
                $courseManager = $this->course_manager->getCourseManagerClaims($course, $session, $currentUser->id);
                if ($courseManager) {
                    $courseTitle = "{$courseRow['code']}: {$courseRow['title']}";
                    $formerClaimsScript = $this->course_request_claims->getNewestCourseClaims($session, $course);
                    if (!$formerClaimsScript) {
                        // this means a fresh claims
                        $isPaper = $courseRow['type'] === ClaimType::EXAM_PAPER;
                        $inferPayment = self::inferPaymentAmount($courseRow['code'], $courseWithScore, $isPaper, $cadreTitle);
                        $percentageData = $this->calcEssentialAmountPercentage($courseManager, $inferPayment['sumTotal']);
                        $inferPayment['sumTotal'] = $percentageData['sum_total'];
                        $payload = $this->previewDataPayload($courseTitle, $enrolled, $courseWithScore, $inferPayment, $cadreTitle);
                    } else {
                        $formerClaimsScript = $formerClaimsScript[0];
                        $isPaper = $formerClaimsScript['exam_type'] === ClaimType::EXAM_PAPER;
                        $enrolled = $formerClaimsScript['enrolled'];
                        $scored = $formerClaimsScript['with_score'];
                        $courseWithScore = ($courseWithScore > 0) ? $courseWithScore - $scored : $scored;
                        $inferPayment = self::inferPaymentAmount($courseRow['code'], $courseWithScore, $isPaper, $cadreTitle);
                        $payload = $this->previewDataPayload($courseTitle, $enrolled, $courseWithScore, $inferPayment, $cadreTitle);
                    }

                    $temp['course_id'] = $course;
                    $temp['script'] = $payload;
                    $temp['script']['no_essential_waiver'] = !empty($courseManager['essential_inline_waiver'])
                        ? json_decode($courseManager['essential_inline_waiver'], true)
                        : [];
                    if (!$cadreTitle) {
                        $temp['physical_interactive_amount'] = ($interaction === 'yes') ? self::calcInteraction()['sumTotal'] : 0;
                        $temp['online_interactive_amount'] = ($facilitation === 'yes') ? self::calcFacilitation()['sumTotal'] : 0;
                    } else {
                        // this denotes the new regime of payment
                        if ($courseManager['physical_interaction'] == CourseManagerStatus::ACCEPTED) {
                            $temp['physical_interactive_amount'] = ($interaction === 'yes') ? self::calcFacilitation($cadreTitle)['sumTotal'] : 0;
                        }

                        if ($courseManager['data_allowance'] == CourseManagerStatus::APPROVED) {
                            $temp['data_allowance_amount'] = self::calcDataAllowance()['sumTotal'];
                        }

                        if ($courseManager['webinar_excess_work_load'] == CourseManagerStatus::APPROVED) {
                            $perLearner = self::webinarExcessPerLearner()['sumTotal'];
                            $webinarExcessAmount = self::calcWebinarWorkLoadAllowance($courseWithScore, $perLearner)['sumTotal'];
                            $temp['webinar_excess_work_amount'] = [
                                'per_learner' => $perLearner,
                                'amount' => $webinarExcessAmount
                            ];
                        }

                        if (($courseManager['exam_type'] == CourseManagerStatus::APPROVED ||
                            $courseManager['exam_type'] == CourseManagerStatus::WAIVER)) {
                            $temp['course_exam_type'] = [
                                'type' => ucfirst($courseRow['type']),
                                'amount' => self::calcExamType($courseRow['type'])['sumTotal'],
                                'status' => $courseManager['exam_type']
                            ];
                        }

                        // if (isset($courseManager['course_question_tutor_id']) && $courseManager['course_question_tutor_id'] == $currentUser->id) {
                        // 	$temp['course_cbt_question_amount'] = self::calcCourseCBT()['sumTotal'];
                        // }
                    }

                    if ($courseManager['writing_course_material'] == CourseManagerStatus::APPROVED) {
                        $temp['course_material_amount'] = self::calcCourseMaterial(false)['sumTotal'];
                    }

                    if ($courseManager['review_course_material'] == CourseManagerStatus::APPROVED) {
                        $temp['course_revision_amount'] = self::calcCourseRevision(false)['sumTotal'];
                    }

                    $result[] = $temp;
                    if (!$allowedAllowance && $courseManager['logistics_allowance'] == CourseManagerStatus::APPROVED) {
                        $allowedAllowance = true;
                    }
                }
            }
        }

        if (isDepartmentalCoordinator($currentUser)) {
            loadClass($this->load, 'department');
            $semester = get_setting('active_semester');
            $total = $this->department->totalDepartmentStudent($currentUser->user_department['id'], $session, $semester);
            $runningTotal = self::calcDepartmentalRunningCost($total)['sumTotal'];
            $result[] = [
                'is_department_running_cost' => true,
                'total_scored_student' => (int)$total,
                'running_cost' => $runningTotal,
            ];
        }

        if ($currentUser->department_id && $this->course_committee->isCourseCommittee($currentUser->id, $session, $currentUser->department_id)) {
            $result[] = [
                'is_course_committee' => true,
                'total_amount' => self::calcCourseCommittee()['sumTotal']
            ];
        }

        if ($allowedAllowance && $currentUser) {
            $result[] = [
                'logistics_allowance' => true,
                'total_amount' => self::calcLogisticsAllowance()['sumTotal']
            ];
        }

        return sendApiResponse(true, "success", $result);
    }

    private function previewDataPayload($courseTitle, $enrolled, $scored, $inferPayment, $cadreTitle): array
    {
        return [
            'course_title' => $courseTitle,
            'total_enrolled' => (int)$enrolled,
            'total_no_student' => (int)$scored,
            'total_amount' => $inferPayment['sumTotal'],
            'fixed_amount' => $inferPayment['total'],
            'fixed_no_student' => ((int)$scored - (int)$inferPayment['extra']),
            'excess_per_unit' => $inferPayment['perExtra'],
            'excess_no_of_student' => $inferPayment['extra'],
            'excess_amount' => $inferPayment['extraTotal'],
            'percentage_info' => $cadreTitle ? self::onlineAssessmentPercentage() : []
        ];
    }

    private function calculateClaimScore($course, $session): array
    {
        // this is banking on that the necessary entity object is already loaded
        $courseStats = $this->examination_courses->courseStatsEnrollment($course, $session);
        $enrolled = @$courseStats[0]['enrollment'] ?: 0;
        $courseWithScore = @$courseStats[0]['scored'] ?: 0;
        $totalClaims = $this->course_request_claims->getSumCourseScoreClaims($course, $session);
        $totalClaims = ($totalClaims > 0) ? $totalClaims : 0;
        $courseWithScore = ($courseWithScore > 0) ? $courseWithScore - $totalClaims : 0;

        return [
            'enrolled' => $enrolled,
            'with_score' => $courseWithScore,
        ];
    }

    private function hasAtLeastOneYes($data): bool
    {
        if ($data['exam_facilitation'] !== 'no') {
            return false;
        }

        // Exclude 'id' and 'exam_facilitation' from the check
        $excludedKeys = ['id', 'exam_facilitation'];
        $filteredData = array_diff_key($data, array_flip($excludedKeys));

        return in_array('yes', $filteredData, true);
    }

    public function submit_request_claim()
    {
        $currentUser = $this->webSessionManager->currentAPIUser();
        loadClass($this->load, 'course_request_claims');
        loadClass($this->load, 'courses');
        loadClass($this->load, 'request_type');
        loadClass($this->load, 'user_banks');
        loadClass($this->load, 'user_requests');
        loadClass($this->load, 'sessions');
        loadClass($this->load, 'examination_courses');
        loadClass($this->load, 'department');
        loadClass($this->load, 'course_committee');
        loadClass($this->load, 'course_manager');

        $session = $this->input->post('session', true);
        $isCadre = $this->input->post('cadre', true) ?: 'no';
        $courses = $this->input->post('courses', true);
        $courseCommittee = $this->input->post('course_committee', true) ?: 'no';
        $departmentExamCost = $this->input->post('department_exam_cost', true) ?: 'no';
        $logisticAllowance = $this->input->post('logistics_allowance', true) ?: 'no';

        $this->form_validation->set_rules('session', 'session', 'trim|required');
        $this->form_validation->set_rules('courses[]', 'course id', 'trim|required');
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            foreach ($errors as $error) {
                return sendAPiResponse(false, $error);
            }
        }
        $cadreTitle = $isCadre === 'yes' ? $this->inferCadreTitle() : null;
        $requestType = $this->request_type->getWhere(['slug' => RequestTypeSlug::CLAIM], $count, 0, 1, false);
        if (!$requestType) {
            return sendApiResponse(false, 'Unable to find claim type, kindly reach out to the administrator');
        }
        if (!$currentUser->email) {
            return sendAPiResponse(false, "Kindly update your email in your profile");
        }
        $userBank = $this->user_banks->getWhere(['users_id' => $currentUser->id, 'is_primary' => '1'], $count, 0, 1, false);
        if (!$userBank) {
            return sendApiResponse(false, 'Please set up a primary bank account in your account details (Request -> Account details).');
        }
        $userBank = $userBank[0];
        $sessionName = $this->sessions->getSessionById($session);
        $sessionName = $sessionName ? $sessionName[0]['date'] : null;
        $lastRequestNo = $this->user_requests->getLastRequestNo('user_requests', 'request_no');
        $requestNo = generateNumberWithOdd($lastRequestNo);
        $actionTimeline = User_requests::emptyActionTimeline();
        $title = "Claim request for {$sessionName} [#{$requestNo}]";
        $insertData = [
            'request_no' => $requestNo,
            'title' => $title,
            'user_id' => $currentUser->id,
            'request_id' => $requestType[0]->id,
            'amount' => 0,
            'description' => "Claim request by {$currentUser->user_login}[#{$requestNo}]",
            'beneficiaries' => json_encode([]),
            'project_task_id' => null,
            'request_from' => 'staff',
            'action_timeline' => $actionTimeline
        ];

        $sumTotal = 0;
        $inferPayload = [];
        $inferPayload1 = [];
        $allowedAllowance = false;
        $this->db->trans_begin();
        foreach ($courses as $item) {
            $examScript = $item['exam_facilitation'] ?: 'no';
            $facilitation = $item['physical_facilitation'] ?: 'no';
            $interaction = $item['online_facilitation'] ?: 'no';
            $courseExamType = $item['exam_type'] ?: 'no';
            $dataAllowance = $item['data_allowance'] ?: 'no';
            $webinarExcess = $item['webinar_excess_work'] ?: 'no';
            $courseCbtQuestion = $item['course_cbt_question'] ?: 'no';
            $courseMaterial = $item['course_material'] ?: 'no';
            $courseRevision = $item['course_revision'] ?: 'no';
            $course = $item['id'];

            $courseRow = $this->courses->getCourseByIdOnly($course);
            $courseTitle = $courseRow['title'];
            $calculatedClaimsData = $this->calculateClaimScore($course, $session);
            $enrolled = $calculatedClaimsData['enrolled'];
            // $courseWithScore = $calculatedClaimsData['with_score'];
            $courseWithScore = $enrolled;
            $calculatedClaimsData['with_score'] = $courseWithScore;
            $courseManager = $this->course_manager->getCourseManagerClaims($course, $session, $currentUser->id);

            $insertItemData = [];
            $claimFormerID = null;
            $subItemTotal = 0;

            if ($courseManager) {
                if ($examScript === 'yes' && $courseManager['essential_inline_waiver']) {
                    if ($courseWithScore <= 0) {
                        return sendAPiResponse(false, "No new scores found for {$courseRow['code']}, please remove and try again");
                    }
                    $courseRow['currentUser'] = $currentUser;
                    $payload = $this->processExamFacilitation($course, $session, $courseRow, $calculatedClaimsData, $cadreTitle);
                    if (!$payload[0]) {
                        $this->db->trans_rollback();
                        return sendAPiResponse(false, $payload[1]);
                    }
                    $inferPayment = $payload[1];
                    $claimFormerID = $inferPayment['formerClaimsID'];
                    $percentageData = $this->calcEssentialAmountPercentage($courseManager, $inferPayment['sumTotal']);
                    $inferPayment['sumTotal'] = $percentageData['sum_total'];
                    $percentageRemove = $percentageData['percentage_remove'] ?: '';

                    $insertItemData[] = $this->prepClaimItems($inferPayment, $claimFormerID, ClaimType::SCRIPT, $percentageRemove);
                    $subTotal = @$inferPayment['sumTotal'] ?: 0;
                    $subItemTotal += $subTotal;
                    $sumTotal += $subTotal;
                } else {
                    // insertion of a new claim request when exam facilitation[exam_script = no] is not submitted
                    // this is used to handle requests that are under courses but without
                    // examination[online interaction new regime] request
                    if ($this->hasAtLeastOneYes($item)) {
                        if (!$claimFormerID = create_record($this, 'course_request_claims', [
                            'course_id' => $course,
                            'session_id' => $session,
                            'course_manager_id' => $currentUser->id,
                            'exam_type' => $courseRow['type'],
                            'enrolled' => $enrolled,
                            'with_score' => $courseWithScore,
                            'status' => 0,
                            'department_id' => $courseRow['department_id'] ?: ''
                        ])) {
                            $this->db->trans_rollback();
                            return sendAPiResponse(false, "Unable to create new claim request on course: {$courseTitle}, please try again later.");
                        }
                    }
                }

                // this is for the old regime
                if (!$cadreTitle) {
                    if ($interaction === 'yes') {
                        $payload = $this->processOnlineInteractive($course, $session, $courseRow, $cadreTitle);
                        if (!$payload[0]) {
                            $this->db->trans_rollback();
                            return sendAPiResponse(false, $payload[1]);
                        }
                        $inferPayment = $payload[1];
                        $insertItemData[] = $this->prepClaimItems($inferPayment, $claimFormerID, ClaimType::FACILITATION);

                        $subTotal = @$inferPayment['sumTotal'] ?: 0;
                        $subItemTotal += $subTotal;
                        $sumTotal += $subTotal;
                    }

                    if ($facilitation === 'yes') {
                        $payload = $this->processPhysicalInteraction($course, $session, $courseRow);
                        if (!$payload[0]) {
                            $this->db->trans_rollback();
                            return sendAPiResponse(false, $payload[1]);
                        }
                        $inferPayment = $payload[1];
                        $insertItemData[] = $this->prepClaimItems($inferPayment, $claimFormerID, ClaimType::INTERACTION);

                        $subTotal = @$inferPayment['sumTotal'] ?: 0;
                        $subItemTotal += $subTotal;
                        $sumTotal += $subTotal;
                    }
                }

                // this is for the new regime
                if ($cadreTitle) {
                    // this is equiv to physical interaction
                    if ($facilitation === 'yes' && $courseManager['physical_interaction'] == CourseManagerStatus::ACCEPTED) {
                        $payload = $this->processPhysicalFacilitation($course, $session, $courseRow, $cadreTitle);
                        if (!$payload[0]) {
                            $this->db->trans_rollback();
                            return sendAPiResponse(false, $payload[1]);
                        }
                        $inferPayment = $payload[1];
                        $insertItemData[] = $this->prepClaimItems($inferPayment, $claimFormerID, ClaimType::FACILITATION);

                        $subTotal = @$inferPayment['sumTotal'] ?: 0;
                        $subItemTotal += $subTotal;
                        $sumTotal += $subTotal;
                    }

                    if ($courseExamType === 'yes' && (
                            $courseManager['exam_type'] == CourseManagerStatus::APPROVED ||
                            $courseManager['exam_type'] == CourseManagerStatus::WAIVER
                        )
                    ) {
                        $payload = $this->processCourseExamType($course, $session, $courseRow);
                        if (!$payload[0]) {
                            $this->db->trans_rollback();
                            return sendAPiResponse(false, $payload[1]);
                        }
                        $inferPayment = $payload[1];
                        $insertItemData[] = $this->prepClaimItems($inferPayment, $claimFormerID, ClaimType::COURSE_EXAM_TYPE);

                        $subTotal = @$inferPayment['sumTotal'] ?: 0;
                        $subItemTotal += $subTotal;
                        $sumTotal += $subTotal;
                    }

                    if ($dataAllowance === 'yes' && $courseManager['data_allowance'] == CourseManagerStatus::APPROVED) {
                        $payload = $this->processDataAllowance($course, $session, $courseRow);
                        if (!$payload[0]) {
                            $this->db->trans_rollback();
                            return sendAPiResponse(false, $payload[1]);
                        }
                        $inferPayment = $payload[1];
                        $insertItemData[] = $this->prepClaimItems($inferPayment, $claimFormerID, ClaimType::DATA_ALLOWANCE);

                        $subTotal = @$inferPayment['sumTotal'] ?: 0;
                        $subItemTotal += $subTotal;
                        $sumTotal += $subTotal;
                    }

                    if ($webinarExcess === 'yes' && $courseManager['webinar_excess_work_load'] == CourseManagerStatus::APPROVED) {
                        if ($courseWithScore <= 0) {
                            $this->db->trans_rollback();
                            return sendAPiResponse(false, "No new scores found for {$courseRow['code']}, please remove webinar excess workload and try again");
                        }
                        $payload = $this->processWebinarExcessWork($course, $session, $courseRow, $courseWithScore);
                        if (!$payload[0]) {
                            $this->db->trans_rollback();
                            return sendAPiResponse(false, $payload[1]);
                        }
                        $inferPayment = $payload[1];
                        $insertItemData[] = $this->prepClaimItems($inferPayment, $claimFormerID, ClaimType::WEBINAR_EXCESS_WORK);

                        $subTotal = @$inferPayment['sumTotal'] ?: 0;
                        $subItemTotal += $subTotal;
                        $sumTotal += $subTotal;
                    }

                    if ($courseCbtQuestion === 'yes') {
                        // $payload = $this->processCourseCBTQuestion($course, $session, $courseRow);
                        // if (!$payload[0]) {
                        // 	$this->db->trans_rollback();
                        // 	return sendAPiResponse(false, $payload[1]);
                        // }
                        // $inferPayment = $payload[1];
                        // $insertItemData[] = $this->prepClaimItems($inferPayment, $claimFormerID, ClaimType::COURSE_CBT_QUESTION);

                        // $subTotal = @$inferPayment['sumTotal'] ?: 0;
                        // $subItemTotal += $subTotal;
                        // $sumTotal += $subTotal;
                    }

                    if (!$allowedAllowance && $courseManager['logistics_allowance'] == CourseManagerStatus::APPROVED) {
                        $allowedAllowance = true;
                    }
                }

                if ($courseMaterial === 'yes' && $courseManager['writing_course_material'] == CourseManagerStatus::APPROVED) {
                    $payload = $this->processCourseMaterial($course, $session, $courseRow);
                    if (!$payload[0]) {
                        $this->db->trans_rollback();
                        return sendAPiResponse(false, $payload[1]);
                    }
                    $inferPayment = $payload[1];
                    $insertItemData[] = $this->prepClaimItems($inferPayment, $claimFormerID, ClaimType::COURSE_MATERIAL);

                    $subTotal = @$inferPayment['sumTotal'] ?: 0;
                    $subItemTotal += $subTotal;
                    $sumTotal += $subTotal;
                }

                if ($courseRevision === 'yes' && $courseManager['review_course_material'] == CourseManagerStatus::APPROVED) {
                    $payload = $this->processCourseRevision($course, $session, $courseRow);
                    if (!$payload[0]) {
                        $this->db->trans_rollback();
                        return sendAPiResponse(false, $payload[1]);
                    }
                    $inferPayment = $payload[1];
                    $insertItemData[] = $this->prepClaimItems($inferPayment, $claimFormerID, ClaimType::COURSE_REVISION);

                    $subTotal = @$inferPayment['sumTotal'] ?: 0;
                    $subItemTotal += $subTotal;
                    $sumTotal += $subTotal;
                }

                // these are items per course
                if (!empty($insertItemData)) {
                    // insertion of new claim items details
                    if (!create_record_batch($this, 'course_request_claim_items', $insertItemData)) {
                        $this->db->trans_rollback();
                        return sendAPiResponse(false, "An error occurred while creating claims on {$courseTitle}. Please try again later.");
                    }
                    $inferPayload[] = [
                        'formerClaimsID' => $claimFormerID,
                        'sumTotal' => $subItemTotal,
                    ];
                }
            }
        }

        if ($departmentExamCost === 'yes') {
            $semester = get_setting('active_semester');
            if ($currentUser->user_department) {
                // check if departmental running cost hasn't be claimed already
                $departmentExist = $this->department->checkDepartmentClaim($currentUser->user_department['id'], $session, ClaimType::DEPARTMENTAL_RUN_COST);
                if ($departmentExist && $departmentExist[0]['status'] == '0') {
                    $this->db->trans_rollback();
                    return sendApiResponse(false, "There is a pending claim request for departmental running cost. Please wait for the approval.");
                }
                if ($departmentExist && count($departmentExist) >= 2) {
                    $this->db->trans_rollback();
                    return sendApiResponse(false, "Departmental running costs can only be claimed twice per session.");
                }

                if (empty($currentUser->user_department) || empty($currentUser->user_department['id'])) {
                    $this->db->trans_rollback();
                    return sendAPiResponse(false, "Department coordinator account not properly set. Please update and try again.");
                }

                $total = $this->department->totalDepartmentStudent($currentUser->user_department['id'], $session, $semester);
                if ($total <= 0) {
                    $this->db->trans_rollback();
                    return sendAPiResponse(false, "The department has no recorded student scores for the semester.");
                }
                $amountTotal = self::calcDepartmentalRunningCost($total)['sumTotal'];
                // this should only send if the department has scored the exam they run
                if ($amountTotal > 0) {
                    if (!$claimNewID = create_record($this, 'course_request_claims', [
                        'course_id' => 0,
                        'session_id' => $session,
                        'course_manager_id' => $currentUser->id,
                        'exam_type' => ClaimType::DEPARTMENTAL_RUN_COST,
                        'enrolled' => $total,
                        'with_score' => $total,
                        'status' => 0,
                        'total_amount' => $amountTotal,
                        'created_at' => date('Y-m-d H:i:s'),
                        'department_id' => $currentUser->user_department['id']
                    ])) {
                        $this->db->trans_rollback();
                        return sendAPiResponse(false, "Unable to create new claim request for department exam running cost, please try again later.");
                    }

                    $inferPayload1[] = [
                        'newClaimsID' => $claimNewID,
                    ];
                    $sumTotal += $amountTotal;
                }
            }
        }

        if ($courseCommittee === 'yes') {
            if ($currentUser->department_id && $this->course_committee->isCourseCommittee($currentUser->id, $session, $currentUser->department_id)) {
                $committeeExist = get_single_record($this, 'course_request_claims', [
                    'session_id' => $session,
                    'exam_type' => ClaimType::COURSE_AUTHOR_COMMITTEE,
                    'course_manager_id' => $currentUser->id,
                ]);
                if ($committeeExist) {
                    $this->db->trans_rollback();
                    return sendApiResponse(false, "You can only claim the course author committee once per session.");
                }
                $amountTotal = self::calcCourseCommittee()['sumTotal'];
                if (!$claimNewID = create_record($this, 'course_request_claims', [
                    'course_id' => 0,
                    'session_id' => $session,
                    'course_manager_id' => $currentUser->id,
                    'exam_type' => ClaimType::COURSE_AUTHOR_COMMITTEE,
                    'enrolled' => 0,
                    'with_score' => 0,
                    'status' => 0,
                    'total_amount' => $amountTotal,
                    'created_at' => date('Y-m-d H:i:s'),
                ])) {
                    $this->db->trans_rollback();
                    return sendAPiResponse(false, "Unable to create new claim request for question authoring committee, please try again later.");
                }
                $inferPayload1[] = [
                    'newClaimsID' => $claimNewID,
                ];
                $sumTotal += $amountTotal;
            }
        }

        if ($logisticAllowance === 'yes' && $allowedAllowance) {
            $logisticsExist = get_single_record($this, 'course_request_claims', [
                'session_id' => $session,
                'exam_type' => ClaimType::LOGISTICS_ALLOWANCE,
                'course_manager_id' => $currentUser->id,
            ]);
            if ($logisticsExist) {
                $this->db->trans_rollback();
                return sendApiResponse(false, "You can only claim the logistics allowance once per session.");
            }
            $amountTotal = self::calcLogisticsAllowance()['sumTotal'];
            if (!$claimNewID = create_record($this, 'course_request_claims', [
                'course_id' => 0,
                'session_id' => $session,
                'course_manager_id' => $currentUser->id,
                'exam_type' => ClaimType::LOGISTICS_ALLOWANCE,
                'enrolled' => 0,
                'with_score' => 0,
                'status' => 0,
                'total_amount' => $amountTotal,
                'created_at' => date('Y-m-d H:i:s'),
            ])) {
                $this->db->trans_rollback();
                return sendAPiResponse(false, "Unable to create new claim request for logistics allowance, please try again later.");
            }
            $inferPayload1[] = [
                'newClaimsID' => $claimNewID,
            ];
            $sumTotal += $amountTotal;
        }

        $insertData['amount'] = $sumTotal;
        $insertData['total_amount'] = $sumTotal;

        if ($sumTotal <= 0) {
            $this->db->trans_rollback();
            return sendAPiResponse(false, "Unable to process the claim(s). Please try again later.");
        }
        $insertData['beneficiaries'] = json_encode([User_banks::formatRequestBeneficiaries($userBank, $sumTotal)]);
        // insertion of a new claim into user_request for outflow processing
        if (!$insertID = create_record($this, 'user_requests', $insertData)) {
            $this->db->trans_rollback();
            return sendApiResponse(false, 'Unable to create new claim request');
        }

        if (!empty($inferPayload)) {
            foreach ($inferPayload as $item) {
                if (!update_record($this, 'course_request_claims', 'id', $item['formerClaimsID'], [
                    'user_request_id' => $insertID,
                    'total_amount' => $item['sumTotal'],
                    'status' => 1,
                    'claim_no' => $requestNo,
                    'is_new_regime' => $cadreTitle ? '1' : '0'
                ])) {
                    $this->db->trans_rollback();
                    return sendAPiResponse(false, 'Unable to submit claims at the moment, please try again later.');
                }
            }
        }

        if (!empty($inferPayload1)) {
            foreach ($inferPayload1 as $item) {
                if (!update_record($this, 'course_request_claims', 'id', $item['newClaimsID'], [
                    'user_request_id' => $insertID,
                    'status' => 1,
                    'claim_no' => $requestNo,
                    'is_new_regime' => $cadreTitle ? '1' : '0'
                ])) {
                    $this->db->trans_rollback();
                    return sendAPiResponse(false, 'Unable to submit claims at the moment, please try again later.');
                }
            }
        }

        $this->db->trans_commit();
        logAction($this, 'create_claims_request', $currentUser->user_login);
        return sendApiResponse(true, " You have successfully submitted your claims ");
    }

    private function prepClaimItems($inferPayment, $claimFormerID, $type, $percentageRemoved = ''): array
    {
        $date = date('Y-m-d H:i:s');
        return [
            'course_request_claim_id' => $claimFormerID,
            'with_score_amount' => $inferPayment['total'],
            'with_score_extra' => $inferPayment['extra'],
            'with_score_extra_unit' => $inferPayment['perExtra'],
            'with_score_extra_amount' => $inferPayment['extraTotal'],
            'claim_type' => $type,
            'created_at' => $date,
            'updated_at' => $date,
            'sum_total' => $inferPayment['sumTotal'],
            'percentage_removed' => $percentageRemoved
        ];
    }

    private function processExamFacilitation($course, $session, $courseRow, $calculatedClaimsData, $cadreTitle = null): array
    {
        $courseTitle = $courseRow['title'];
        $courseCode = $courseRow['code'];
        $countClaims = $this->course_request_claims->getCountCourseClaims($course, $session);
        if ($countClaims > 0) {
            return [false, "A pending claim for examination facilitation exists on course[{$courseTitle}]. Please remove it and try again"];
        }

        // insert fresh course request claims
        if (!create_record($this, 'course_request_claims', [
            'course_id' => $course,
            'session_id' => $session,
            'course_manager_id' => $courseRow['currentUser']->id,
            'exam_type' => $courseRow['type'],
            'enrolled' => $calculatedClaimsData['enrolled'],
            'with_score' => $calculatedClaimsData['with_score'],
            'status' => 0,
            'department_id' => $courseRow['department_id'] ?: ''
        ])) {
            return [false, "Unable to create new claim request on course: {$courseTitle}, please try again later."];
        }

        $formerClaims = $this->course_request_claims->getOldestCourseClaims($course, $session);
        if (!$formerClaims) {
            return [false, "No new result uploaded for '{$courseCode}: {$courseTitle}'. Please remove it from the list."];
        }
        $formerClaims = $formerClaims[0];
        $isPaper = $formerClaims['exam_type'] === ClaimType::EXAM_PAPER;
        $inferPayment = self::inferPaymentAmount($courseCode, $formerClaims['with_score'], $isPaper, $cadreTitle);
        $inferPayment['formerClaimsID'] = $formerClaims['id'];

        return [true, $inferPayment];
    }

    private function processPhysicalFacilitation($course, $session, $courseRow, $cadreTitle): array
    {
        $courseTitle = $courseRow['code'];
        $countClaims = $this->course_request_claims->getExistingCourseClaims($session, $course, ClaimType::FACILITATION);
        if ($countClaims) {
            return [false, "A claim already exists for physical interaction facilitation on {$courseTitle}. Please remove it and try again."];
        }
        $inferPayment = self::calcFacilitation($cadreTitle);
        return [true, $inferPayment];
    }

    private function processOnlineInteractive($course, $session, $courseRow, $cadreTitle): array
    {
        return $this->processPhysicalFacilitation($course, $session, $courseRow, $cadreTitle);
    }

    private function processOnlineFacilitation($course, $session, $courseRow): array
    {
        $courseTitle = $courseRow['code'];
        $countClaims = $this->course_request_claims->getExistingCourseClaims($session, $course, ClaimType::INTERACTION);
        if ($countClaims) {
            return [false, "A claim already exists for online interaction facilitation on {$courseTitle}. Please remove it and try again."];
        }
        $inferPayment = self::calcInteraction();
        return [true, $inferPayment];
    }

    private function processPhysicalInteraction($course, $session, $courseRow): array
    {
        return $this->processOnlineFacilitation($course, $session, $courseRow);
    }

    private function processCourseExamType($course, $session, $courseRow): array
    {
        $courseTitle = $courseRow['code'];
        $countClaims = $this->course_request_claims->getExistingCourseClaims($session, $course, ClaimType::COURSE_EXAM_TYPE);
        if ($countClaims) {
            return [false, "A claim already exists for course exam type on {$courseTitle}. Please remove it and try again."];
        }
        $inferPayment = self::calcExamType($courseRow['type']);
        return [true, $inferPayment];
    }

    private function processDataAllowance($course, $session, $courseRow): array
    {
        $courseTitle = $courseRow['code'];
        $countClaims = $this->course_request_claims->getExistingCourseClaims($session, $course, ClaimType::DATA_ALLOWANCE);
        if ($countClaims) {
            return [false, "A claim already exists for data allowance on {$courseTitle}. Please remove it and try again."];
        }
        $inferPayment = self::calcDataAllowance();
        return [true, $inferPayment];
    }

    private function processCourseCBTQuestion($course, $session, $courseRow): array
    {
        // TODO: I have to validate ensuring that this is only claimed every 2 years
        $courseTitle = $courseRow['code'];
        $countClaims = $this->course_request_claims->getExistingCourseClaims($session, $course, ClaimType::COURSE_CBT_QUESTION);
        if ($countClaims) {
            return [false, "A claim already exists for course CBT question on {$courseTitle}. Please remove it and try again."];
        }
        $inferPayment = self::calcCourseCBT();
        return [true, $inferPayment];
    }

    private function processCourseMaterial($course, $session, $courseRow): array
    {
        $courseTitle = $courseRow['code'];
        $countClaims = $this->course_request_claims->getExistingCourseClaims($session, $course, ClaimType::COURSE_MATERIAL);
        if ($countClaims) {
            return [false, "A claim already exists for course material on {$courseTitle}. Please remove it and try again."];
        }
        $inferPayment = self::calcCourseMaterial(false);
        return [true, $inferPayment];
    }

    private function processCourseRevision($course, $session, $courseRow): array
    {
        $courseTitle = $courseRow['code'];
        $countClaims = $this->course_request_claims->getExistingCourseClaims($session, $course, ClaimType::COURSE_REVISION);
        if ($countClaims) {
            return [false, "A claim already exists for course revision on {$courseTitle}. Please remove it and try again."];
        }
        $inferPayment = self::calcCourseRevision(false);
        return [true, $inferPayment];
    }

    private function processWebinarExcessWork($course, $session, $courseRow, $withScore): array
    {
        $courseTitle = $courseRow['code'];
        $countClaims = $this->course_request_claims->getExistingCourseClaims($session, $course, ClaimType::WEBINAR_EXCESS_WORK);
        if ($countClaims) {
            return [false, "A claim already exists for webinar/excess work load on {$courseTitle}. Please remove it and try again."];
        }
        $perLearner = self::webinarExcessPerLearner()['sumTotal'];
        $inferPayment = self::calcWebinarWorkLoadAllowance($withScore, $perLearner);
        return [true, $inferPayment];
    }

    public function examination_approval_action()
    {
        permissionAccess($this, 'view_score_approval');
        if ($this->input->method(true) !== 'POST') {
            return sendAPiResponse(false, 'Invalid request method');
        }
        $session = @$this->input->post('session_id', true);
        $course = trim(@$this->input->post('course_id', true));
        $flag = trim(@$this->input->post('flag', true));
        $this->form_validation->set_rules('session_id', 'session', 'trim|required');
        $this->form_validation->set_rules('course_id', 'course', 'trim|required');
        $this->form_validation->set_rules('flag', 'flag', 'trim|required|in_list[approve,disapprove]', [
            'in_list' => 'Please provide the valid flag',
        ]);
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            sendAPiResponse(false, reset($errors));
        }

        loadClass($this->load, 'examination_approval');
        if (!$this->examination_approval->processSingleApproval($course, $session, $flag)) {
            return sendAPiResponse(false, "Unable to {$flag} the result, please try again later");
        }
        return sendAPiResponse(true, "Result has been {$flag} successfully");
    }

    public function examination_bulk_approval_action()
    {
        permissionAccess($this, 'view_score_approval');
        ini_set('max_execution_time', '300');

        if ($this->input->method(true) !== 'POST') {
            return sendAPiResponse(false, 'Invalid request method');
        }

        $flag = trim(@$this->input->post('flag', true));
        $datas = @$this->input->post('data', true);
        $this->form_validation->set_rules('flag', 'flag', 'trim|required|in_list[approve,disapprove]', [
            'in_list' => 'Please provide the valid flag',
        ]);
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            sendAPiResponse(false, reset($errors));
        }

        if (empty($datas)) {
            return sendAPiResponse(false, 'No data provided');
        }

        loadClass($this->load, 'examination_approval');
        try {
            $this->db->trans_begin();
            foreach ($datas as $item) {
                if (!$this->examination_approval->processSingleApproval($item['course_id'], $item['session_id'], $flag)) {
                    throw new Exception("Result {$flag} failed");
                }
            }

            if ($this->db->trans_status() === FALSE) {
                $this->db->trans_rollback();
                throw new Exception('Result update failed');
            }
            $this->db->trans_commit();
        } catch (Exception $e) {
            $this->db->trans_rollback();
            return sendAPiResponse(false, 'Unable to process the request, please try again later');
        }
        return sendAPiResponse(true, "Result has been {$flag} successfully");
    }

    public function examination_bulk_approval_all()
    {
        permissionAccess($this, 'view_score_approval');
        ini_set('memory_limit', '1048M');
        ini_set('max_execution_time', '300');

        if ($this->input->method(true) !== 'POST') {
            return sendAPiResponse(false, 'Invalid request method');
        }

        $flag = $this->input->post('flag', true) ?: null;
        $this->form_validation->set_rules('flag', 'flag', 'trim|required|in_list[approve,disapprove]', [
            'in_list' => 'Please provide the valid flag',
        ]);
        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            sendAPiResponse(false, reset($errors));
        }

        loadClass($this->load, 'examination_approval');
        if ($flag) {
            if ($this->examination_approval->processBulkApproval($flag)) {
                return sendAPiResponse(true, "Result has been {$flag} successfully");
            }
        }
        return sendAPiResponse(false, "Unable to {$flag} the result, please try again later");
    }


    public function result_approval_stats()
    {
        loadClass($this->load, 'examination_courses');
        $session = $this->input->get('session') ?: false;
        $payload = $this->examination_courses->getApprovalStats($session);
        return sendAPiResponse(true, 'success', $payload);
    }

    public function course_predictive_analysis()
    {
        loadClass($this->load, 'courses');
        $result = $this->courses->getListCourseEnrolled();
        return sendAPiResponse(true, 'success', $result);
    }

    private function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public function upload_result_grade()
    {
        $currentUser = $this->webSessionManager->currentAPIUser();

        $this->form_validation->set_rules('session_id', 'session', 'trim|required');
        $this->form_validation->set_rules('course_id', 'course', 'trim|required');
        $this->form_validation->set_rules('total_grade', 'total grade', 'trim|required|numeric|is_natural_no_zero', [
            'numeric' => 'Total grade must be a number',
            'is_natural_no_zero' => 'Total grade must be greater than 0',
        ]);

        if (!$this->form_validation->run()) {
            $errors = $this->form_validation->error_array();
            return sendAPiResponse(false, reset($errors));
        }

        loadClass($this->load, 'course_manager');
        loadClass($this->load, 'courses');
        loadClass($this->load, 'sessions');
        loadClass($this->load, 'users_new');
        $session = $this->input->post('session_id', true);
        $course = $this->input->post('course_id', true);
        $totalGrade = $this->input->post('total_grade', true);

        $count = 0;
        $courseManagerList = $this->course_manager->getWhere(['course_id' => $course, 'session_id' => $session], $count, 0, 1, false);
        if (!$courseManagerList) {
            return sendAPiResponse(false, "Kindly confirm you are assigned to this course");
        }
        $staff = $this->users_new->getRealUserInfo($currentUser->id, 'staffs');
        if (!$staff) {
            return sendAPiResponse(false, "Unable to get user information");
        }

        $email = $staff['email'];
        if (!$email || !$this->validateEmail($email)) {
            return sendAPiResponse(false, "Invalid email address, kindly update user email");
        }

        $courseManager = $courseManagerList[0];
        $totalGradeLogs = ($courseManager->total_graded_logs != null) ? json_decode($courseManager->total_graded_logs, true) : [];
        if (!is_array($totalGradeLogs)) {
            $totalGradeLogs = [];
        }
        $previousGrade = $this->pluckTotalGrade($totalGradeLogs);
        $totalGradeLogs[] = [
            'grade' => $totalGrade,
            'date' => date('Y-m-d H:i:s'),
            'user_id' => $currentUser->id,
            'username' => $currentUser->user_login,
        ];

        $updateData = [
            'total_graded' => $totalGrade + $previousGrade,
            'total_graded_logs' => json_encode($totalGradeLogs),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if (!update_record($this, 'course_manager', 'id', $courseManager->id, $updateData)) {
            return sendAPiResponse(false, "Unable to update total grade, please try again later");
        }

        $this->sendGradedMailNotification($staff, $course, $session, $totalGrade);
        logAction($this, 'update_total_grade', $currentUser->user_login);
        return sendAPiResponse(true, "Total grade updated successfully");
    }

    private function pluckTotalGrade($totalGradeLogs)
    {
        return array_sum(array_column((array)$totalGradeLogs, 'grade'));
    }

    private function sendGradedMailNotification($staff, $course, $session, $totalGrade)
    {
        $courseRow = $this->courses->getCourseByIdOnly($course);
        if (!$courseRow) {
            return null;
        }
        $sessionName = $this->sessions->getSessionById($session)[0]['date'];
        $lecturerName = ucwords(strtolower($staff['title'] . ' ' . $staff['firstname'] . ' ' . $staff['lastname']));
        $email = $staff['email'];

        $param = [
            'code' => $courseRow['code'],
            'title' => $courseRow['title'],
            'fullname' => $lecturerName,
            'session_name' => $sessionName,
            'total_graded' => $totalGrade,
        ];

        if (ENVIRONMENT === 'production') {
            Queue::dispatch(Mail_events::EVENT_GRADE_NOTIFICATION, [
                $email, $param
            ]);
        }
    }

}