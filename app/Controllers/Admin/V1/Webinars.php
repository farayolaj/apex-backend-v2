<?php

namespace App\Controllers\Admin\V1;

use App\Controllers\BaseController;
use App\Enums\WebinarStatusEnum;
use App\Libraries\ApiResponse;
use App\Libraries\EntityLoader;
use App\Libraries\WebinarUtil;
use App\Models\WebSessionManager;
use BigBlueButton\BigBlueButton;
use BigBlueButton\Parameters\CreateMeetingParameters;
use BigBlueButton\Parameters\GetRecordingsParameters;
use CodeIgniter\HTTP\Files\UploadedFile;
use CodeIgniter\I18n\Time;
use Exception;

class Webinars extends BaseController
{
    private object $webinars;
    private object $courseManager;
    private BigBlueButton $bbb;
    static string $PRESENTATION_DIR = 'presentations/';

    public function __construct()
    {
        $this->webinars = EntityLoader::loadClass(null, 'webinars');
        $this->courseManager = EntityLoader::loadClass(null, 'course_manager');
        $this->bbb = new BigBlueButton();
    }

    /**
     * List webinars for a specific course
     *
     * @param int $courseId
     */
    public function index(int $courseId)
    {
        $payload = $this->webinars->list($courseId);
        $currentUser = WebSessionManager::currentAPIUser();
        $fullName = trim($currentUser->title . ' ' . $currentUser->firstname . ' ' . $currentUser->lastname);

        $payload = array_map(function ($webinar) use ($fullName) {
            $webinar['status'] = WebinarUtil::getStatus($this->bbb, $webinar);
            $webinar['join_url'] = $webinar['status'] == WebinarStatusEnum::ENDED ?
                null :
                WebinarUtil::getJoinUrl($this->bbb, $webinar, $fullName);

            return $webinar;
        }, $payload);

        return ApiResponse::success(data: $payload);
    }

    /**
     * Get recordings for a specific webinar
     *
     * @param int $webinarId The id of the webinar to get recordings for
     */
    public function getRecordings(int $webinarId)
    {
        $webinar = $this->webinars->getDetails($webinarId);

        $getRecordingsParameters = new GetRecordingsParameters();
        $getRecordingsParameters->setMeetingID($webinar['room_id']);
        $getRecordingsResponse = $this->bbb->getRecordings($getRecordingsParameters);

        if (!$getRecordingsResponse->success()) {
            log_message('error', 'Failed to get recordings for webinar: ' . $getRecordingsResponse->getMessage());
            return ApiResponse::error('Failed to fetch recordings. ' . $getRecordingsResponse->getMessage(), code: 500);
        }

        $data = [];

        foreach (
            $getRecordingsResponse->getRecords() as $record
        ) {
            $startTime = Time::createFromTimestamp($record->getStartTime() / 1000, 'Africa/Lagos');
            $endTime = Time::createFromTimestamp($record->getEndTime() / 1000, 'Africa/Lagos');
            $duration = (int) (($record->getEndTime() - $record->getStartTime()) / 1000); // in seconds

            $data[] = [
                'id' => $record->getRecordId(),
                'date_recorded' => $startTime->toDateTimeString(),
                'duration' => $duration,
                'recording_url' => $record->getPlaybackFormats()[0]->getUrl(),
            ];
        }

        return ApiResponse::success(data: $data);
    }

    /**
     * Create a new webinar
     */
    public function create()
    {
        $data = $this->request->getPost();
        $rules = [
            'title' => 'required|string|max_length[255]',
            'description' => 'string|permit_empty',
            'course_id' => 'required|integer',
            'scheduled_for' => 'required|valid_datetime[scheduled_for]',
            'presentation' => [
                'label' => 'Presentation file',
                'rules' => [
                    'permit_empty',
                    'ext_in[presentation,pdf,doc,docx,ppt,pptx,xls,xlsx]',
                    'max_size[presentation,30720]', // 30 MB
                ],
            ],
        ];

        if (!$this->validateData($data, $rules)) {
            $errors = $this->validator->getErrors();
            return ApiResponse::error(implode(' ', $errors));
        }

        if (!fetchSingle($this->db, "courses", "id", $data['course_id'])) {
            return ApiResponse::error('Invalid course ID');
        }

        if (!$this->canAccessCourse($data['course_id'])) {
            return ApiResponse::error('User does not have access to update webinar', code: 403);
        }

        $presentationFile = $this->request->getFile('presentation');
        if ($presentationFile) {
            if (!$presentationFile->isValid()) {
                return ApiResponse::error($presentationFile->getErrorString());
            }

            $presentation = new Presentation($presentationFile);
        }


        // Generate random room id for the webinar
        $roomId = bin2hex(random_bytes(16));
        $data['room_id'] = $roomId;

        $createParams = new CreateMeetingParameters($roomId, $data['title']);
        $createParams->setAutoStartRecording(true);
        $createParams->setRecord(true);
        $createParams->setAllowStartStopRecording(false);
        $createParams->setAllowModsToUnmuteUsers(true);

        if (isset($presentation)) {
            $createParams->addPresentation(
                $this->request->getServer('HTTP_HOST') . '/v1/web/webinars/presentations/' . $presentation->getId(),
                null,
                $presentation->getName()
            );
        }

        try {
            $webinarId = $this->webinars->create($data);
            $createMeetingResponse = $this->bbb->createMeeting($createParams);

            if (!$createMeetingResponse->success()) {
                throw new Exception($createMeetingResponse->getMessage());
            }
        } catch (\Throwable $th) {
            // Log the error
            log_message('error', 'Failed to create BigBlueButton meeting: ' . $th->getMessage());
            // delete the webinar record
            if ($webinarId) $this->webinars->delete($webinarId);

            return ApiResponse::error('Failed to create webinar room. Please try again later.', code: 500);
        }

        return ApiResponse::success();
    }

    /**
     * Update a specific webinar
     *
     * @param int $webinarId The id of the webinar to update
     */
    public function update(int $webinarId)
    {
        $data = $this->request->getJSON(assoc: true);
        $rules = [
            'title' => 'permit_empty|string|max_length[255]',
            'description' => 'permit_empty|string',
            'scheduled_for' => 'permit_empty|valid_datetime[scheduled_for]',
        ];

        if (!$this->validateData($data, $rules)) {
            $errors = $this->validator->getErrors();
            return ApiResponse::error(implode(' ', $errors));
        }

        $webinar = $this->webinars->getDetails($webinarId);

        if (!$this->canAccessCourse($webinar['course_id'])) {
            return ApiResponse::error('User does not have access to update webinar', code: 403);
        }

        // if new scheduled_for has passed or old scheduled_for has passed, prevent update
        if (strtotime($data['scheduled_for']) < time() || strtotime($webinar['scheduled_for']) < time()) {
            return ApiResponse::error('Cannot update webinar. Scheduled time has already passed.', code: 400);
        }

        $this->webinars->updateWebinar($webinarId, $data);
        return ApiResponse::success();
    }

    /**
     * Delete a specific webinar
     *
     * @param int $webinarId The id of the webinar to delete
     */
    public function delete(int $webinarId)
    {
        $webinar = $this->webinars->getDetails($webinarId);

        if (!$this->canAccessCourse($webinar['course_id'])) {
            return ApiResponse::error('User does not have access to delete webinar', code: 403);
        }

        $this->webinars->delete($webinarId);
        return ApiResponse::success();
    }

    public function getPresentation(string $presentationId)
    {
        $filePath = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . self::$PRESENTATION_DIR . $presentationId;

        if (!file_exists($filePath)) {
            return ApiResponse::error('Presentation file not found', code: 404);
        }

        return $this->response->download($filePath, null);
    }

    /**
     * Check if the user can access a specific course
     */
    private function canAccessCourse(int $courseId): bool
    {
        $currentSession = get_setting('active_session_student_portal');
        $currentUser = WebSessionManager::currentAPIUser();

        return !!$this->courseManager->isCourseManagerAssign($currentUser->id, $courseId, $currentSession);
    }
}

class Presentation
{
    private string $presentationId;
    private string $presentationName;
    private string $presentationPath;

    public function __construct(UploadedFile $file)
    {
        $this->presentationId = bin2hex(random_bytes(16)) . '.' . $file->getExtension();
        $this->presentationName = $file->getName();
        $this->presentationPath = $file->store(Webinars::$PRESENTATION_DIR, $this->presentationId);
    }

    public function getId(): string
    {
        return $this->presentationId;
    }

    public function getName(): string
    {
        return $this->presentationName;
    }

    public function getPath(): string
    {
        return $this->presentationPath;
    }

    public function __destruct()
    {
        // Clean up the file if needed
        if (file_exists($this->getPath())) {
            unlink($this->getPath());
        }
    }
}
