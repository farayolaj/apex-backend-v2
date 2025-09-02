<?php

namespace App\Controllers\Admin\V1;

use App\Controllers\BaseController;
use App\Entities\Course_manager;
use App\Entities\Webinars as EntitiesWebinars;
use App\Libraries\ApiResponse;
use App\Libraries\EntityLoader;
use App\Libraries\Notifications\Events\Webinar\NewWebinarEvent;
use App\Libraries\Notifications\Events\Webinar\WebinarStartedEvent;
use App\Libraries\WebinarPresentation;
use App\Models\BBBModel;
use App\Models\WebSessionManager;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
use Config\Services;

class WebinarController extends BaseController
{
    private EntitiesWebinars $webinars;
    private Course_manager $courseManager;
    private BBBModel $bbbModel;

    public function __construct()
    {
        $this->webinars = EntityLoader::loadClass(null, 'webinars');
        $this->courseManager = EntityLoader::loadClass(null, 'course_manager');
        $this->bbbModel = model('BBBModel');
    }

    private function processWebinar(array $webinar): array
    {
        if ($webinar['presentation_id']) {
            $webinar['presentation_url'] = WebinarPresentation::getPublicUrl(base_url(), $webinar['id']);
        } else {
            $webinar['presentation_url'] = null;
        }

        unset($webinar['presentation_id']);
        unset($webinar['course_id']);
        unset($webinar['room_id']);

        $webinar['enable_comments'] = $webinar['enable_comments'] ? true : false;
        $webinar['send_notifications'] = $webinar['send_notifications'] ? true : false;
        $webinar['join_count'] = (int) $webinar['join_count'];
        $webinar['playback_count'] = (int) $webinar['playback_count'];
        $webinar['comment_count'] = (int) ($webinar['comment_count'] ?? 0);

        return $webinar;
    }

    /**
     * List webinars for a specific course
     *
     * @param int $sessionId
     * @param int $courseId
     */
    public function index(int $sessionId, int $courseId)
    {
        $payload = $this->webinars->listWithCommentCount($sessionId, $courseId);
        $payload = array_map([$this, 'processWebinar'], $payload);

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

        if (!$webinar) {
            return ApiResponse::error('Webinar not found', code: 404);
        }

        $recordings = $this->bbbModel->getRecordings($webinar['room_id']);

        $data = array_map(fn($record) => [
            'id' => $record->getRecordId(),
            'date_recorded' => Time::createFromTimestamp($record->getStartTime() / 1000)->toDateTimeString(),
            'duration' => (int) (($record->getEndTime() - $record->getStartTime()) / 1000),
            'recording_url' => $record->getFormats()[0]->getUrl(),
        ], $recordings);

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
            'description' => 'permit_empty|string',
            'course_id' => 'required|integer',
            'scheduled_for' => 'required|valid_datetime[scheduled_for]',
            'enable_comments' => 'permit_empty|in_list[0,1]',
            'send_notifications' => 'permit_empty|in_list[0,1]',
            'presentation' => [
                'label' => 'Presentation file',
                'rules' => [
                    'permit_empty',
                    'ext_in[presentation,pdf,doc,docx,ppt,pptx,xls,xlsx]',
                    'max_size[presentation,10240]', // 10 MB
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
            return ApiResponse::error('User does not have access to create webinar', code: 403);
        }

        $presentationFile = $this->request->getFile('presentation');
        if ($presentationFile) {
            if (!$presentationFile->isValid()) {
                return ApiResponse::error($presentationFile->getErrorString());
            }

            $presentation = new WebinarPresentation($presentationFile);
            $data['presentation_id'] = $presentation->getId();
            $data['presentation_name'] = $presentation->getName();
        }

        $currentSession = get_setting('active_session_student_portal');
        $currentSemester = get_setting('active_semester');

        $data['session_id'] = $currentSession;
        $data['semester'] = $currentSemester;
        $data['scheduled_for'] = Time::parse($data['scheduled_for'])->toDateTimeString();
        // Generate random room id for the webinar
        $data['room_id'] = bin2hex(random_bytes(16));

        $webinarId = $this->webinars->create($data);

        if ($data['send_notifications']) {
            Services::notificationManager()->sendNotifications(
                new NewWebinarEvent(
                    $webinarId,
                    $data['title'],
                    $data['scheduled_for'],
                    $data['course_id']
                )
            );
        }

        return ApiResponse::success(message: "Webinar created.");
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
            'enable_comments' => 'permit_empty|in_list[0,1]',
            'send_notifications' => 'permit_empty|in_list[0,1]',
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
        if (
            isset($data['scheduled_for']) &&
            !Time::parse($data['scheduled_for'])->equals(Time::parse($webinar['scheduled_for']))
        ) {
            if (\DateTime::createFromFormat(
                "Y-m-d H:i:s",
                Time::parse($data['scheduled_for'])->toDateTimeString()
            )->format('U') < time()) {
                return ApiResponse::error('Cannot update webinar. New scheduled time is in the past.', code: 400);
            }

            if (\DateTime::createFromFormat("Y-m-d H:i:s", $webinar['scheduled_for'])->format('U') < time()) {
                return ApiResponse::error('Cannot update webinar. Previous scheduled time has already passed.', code: 400);
            }
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

        if (!$webinar) {
            return ApiResponse::error(
                message: 'Webinar not found',
                code: ResponseInterface::HTTP_NOT_FOUND
            );
        }

        if (!$this->canAccessCourse($webinar['course_id'])) {
            return ApiResponse::error(
                message: 'User does not have access to delete webinar',
                code: ResponseInterface::HTTP_FORBIDDEN
            );
        }

        $recordingsList = $this->bbbModel->getRecordings($webinar['room_id']);
        if (!empty($recordingsList)) { // if there are recordings, prevent webinar deletion.
            return ApiResponse::error(
                message: 'Cannot delete webinar. There are existing recordings for this webinar.',
                code: ResponseInterface::HTTP_FORBIDDEN
            );
        }

        if ($webinar['presentation_id']) {
            WebinarPresentation::deletePresentation($webinar['presentation_id']);
        }

        $this->webinars->delete($webinarId);
        return ApiResponse::success();
    }

    /**
     * Delete webinar recordings.
     * 
     */
    public function deleteRecordings(int $webinarId)
    {
        $recordingIds = $this->request->getGet('ids');

        if (empty($recordingIds)) {
            return ApiResponse::error(
                message: 'No recording IDs provided.',
                code: ResponseInterface::HTTP_BAD_REQUEST
            );
        }

        $webinar = $this->webinars->getDetails($webinarId);

        if (!$webinar) {
            return ApiResponse::error(
                message: 'Webinar not found',
                code: ResponseInterface::HTTP_NOT_FOUND
            );
        }

        if (!$this->canAccessCourse($webinar['course_id'])) {
            return ApiResponse::error(
                message: 'User does not have access to delete webinar recordings',
                code: ResponseInterface::HTTP_FORBIDDEN
            );
        }

        if ($this->bbbModel->deleteRecordings(explode(',', $recordingIds))) {
            return ApiResponse::success();
        }

        return ApiResponse::error(
            message: 'Failed to delete recordings',
            code: ResponseInterface::HTTP_INTERNAL_SERVER_ERROR
        );
    }

    public function getPresentation(string $webinarId)
    {
        $webinar = $this->webinars->getDetails($webinarId);

        if (!$webinar || !$webinar['presentation_id']) {
            throw PageNotFoundException::forPageNotFound("Presentation file not found for given webinar.");
        }

        $filePath = WebinarPresentation::getFilePath($webinar['presentation_id']);

        if (!file_exists($filePath)) {
            throw PageNotFoundException::forPageNotFound("Presentation file not found.");
        }

        return $this->response->download($filePath, null)->setFileName($webinar['presentation_name']);
    }

    public function getJoinUrl(int $webinarId)
    {
        $webinar = $this->webinars->getDetails($webinarId);

        if (!$webinar) {
            return ApiResponse::error('Webinar not found', code: 404);
        }

        if (time() < \DateTime::createFromFormat('Y-m-d H:i:s', $webinar['scheduled_for'])->format('U')) {
            return ApiResponse::error('Webinar has not started yet', code: 403);
        }

        if (!empty($webinar['end_time']) && time() > \DateTime::createFromFormat('Y-m-d H:i:s', $webinar['end_time'])->format('U')) {
            return ApiResponse::error('Webinar has already ended', code: 403);
        }

        if (!$this->bbbModel->meetingExists($webinar['room_id'])) {
            $bbbPresentation = $webinar['presentation_id'] ?
                $this->bbbModel->createPresentation(
                    WebinarPresentation::getPublicUrl(base_url(), $webinar['id']),
                    $webinar['presentation_name']
                ) : null;

            $meetingEndedUrl = base_url('/v1/webinars/' . encryptData($webinar['room_id']) . '/end');
            $recordingReadyUrl = base_url('/v1/webinars/recordings');

            if (!$this->bbbModel->createMeeting(
                $webinar['room_id'],
                $webinar['title'],
                $meetingEndedUrl,
                $recordingReadyUrl,
                $bbbPresentation
            )) {
                return ApiResponse::error('Unable to get meeting url', code: 502);
            }
        }

        $currentUser = WebSessionManager::currentAPIUser();
        $fullName = trim($currentUser->title . ' ' . $currentUser->firstname . ' ' . $currentUser->lastname);
        $redirectURL = $this->request->getGet('redirect_url') ??
            $this->request->header('origin')->getValue();

        if (!$webinar['start_time']) { // Set webinar startTime
            $this->webinars->updateWebinar($webinarId, ['start_time' => date('Y-m-d H:i:s')]);

            // Send webinar started notification
            Services::notificationManager()->sendNotifications(
                new WebinarStartedEvent($webinar['id'], $webinar['title'])
            );
        }

        return ApiResponse::success(data: $this->bbbModel->getJoinUrl(
            meetingId: $webinar['room_id'],
            fullName: $fullName,
            logoutURL: $redirectURL,
            userId: $currentUser->id,
        ));
    }

    public function endWebinar(string $hash)
    {
        $decodedRoomId = encryptData($hash);
        $roomId = $this->request->getGet('meetingID');

        if ($decodedRoomId !== $roomId) {
            return ApiResponse::error('Unauthorised access', code: ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $webinar = $this->webinars->getDetailsByRoomId($roomId);

        if (!$webinar) {
            return ApiResponse::error('Webinar not found', code: ResponseInterface::HTTP_NOT_FOUND);
        }

        $this->webinars->updateWebinar($webinar['id'], ['end_time' => date('Y-m-d H:i:s')]);

        return ApiResponse::success();
    }

    /**
     * Check if the user can access a specific course
     */
    private function canAccessCourse(int $courseId): bool
    {
        $currentSession = get_setting('active_session_student_portal');
        $currentUser = WebSessionManager::currentAPIUser();

        return $this->courseManager->isCourseManagerAssign($currentUser->id, $courseId, $currentSession);
    }
}
