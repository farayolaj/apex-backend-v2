<?php

namespace App\Controllers\Admin\V1;

use App\Controllers\BaseController;
use App\Entities\Course_manager;
use App\Entities\Webinars as EntitiesWebinars;
use App\Libraries\ApiResponse;
use App\Libraries\EntityLoader;
use App\Libraries\Notifications\Events\Webinar\NewWebinarEvent;
use App\Libraries\Notifications\Events\Webinar\RecordingReadyEvent;
use App\Libraries\Notifications\Events\Webinar\WebinarCancelledEvent;
use App\Libraries\Notifications\Events\Webinar\WebinarRescheduledEvent;
use App\Libraries\Notifications\Events\Webinar\WebinarStartedEvent;
use App\Libraries\WebinarPresentation;
use App\Models\WebSessionManager;
use App\Services\BBBService;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;
use Config\Services;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;

class WebinarController extends BaseController
{
    private EntitiesWebinars $webinars;
    private Course_manager $courseManager;
    private BBBService $bbbService;

    // Duration in seconds to delay webinar end time (2 hours)
    const WEBINAR_END_DELAY_SECONDS = 7200;

    const presentationRules = [
        'label' => 'Presentation file',
        'rules' => [
            'permit_empty',
            'ext_in[presentation,pdf,doc,docx,ppt,pptx,xls,xlsx]',
            'max_size[presentation,10240]', // 10 MB
        ]
    ];

    public function __construct()
    {
        $this->webinars = EntityLoader::loadClass(null, 'webinars');
        $this->courseManager = EntityLoader::loadClass(null, 'course_manager');
        $this->bbbService = Services::bbbService();
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
     * Create a new webinar
     */
    public function create()
    {
        $data = $this->request->getPost();
        $rules = [
            'title' => 'required|string|max_length[255]',
            'description' => 'permit_empty|string',
            'course_id' => 'required|integer',
            'scheduled_for' => 'required|future_datetime[scheduled_for]',
            'planned_duration' => 'required|integer|greater_than[0]',
            'enable_comments' => 'permit_empty|in_list[0,1]',
            'send_notifications' => 'permit_empty|in_list[0,1]',
            'presentation' => self::presentationRules,
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
            'planned_duration' => 'permit_empty|integer|greater_than[0]',
        ];

        if (!$this->validateData($data, $rules)) {
            $errors = $this->validator->getErrors();
            return ApiResponse::error(implode(' ', $errors));
        }

        $webinar = $this->webinars->getDetails($webinarId);

        if (!$this->canAccessCourse($webinar['course_id'])) {
            return ApiResponse::error('User does not have access to update webinar', code: 403);
        }

        // only allow updating scheduled_for if the new date is in the future
        if (
            isset($data['scheduled_for']) &&
            !Time::parse($data['scheduled_for'])->equals(Time::parse($webinar['scheduled_for']))
        ) {
            if (\DateTime::createFromFormat(
                "Y-m-d H:i:s",
                Time::parse($data['scheduled_for'])->toDateTimeString()
            )->format('U') < time()) {
                return ApiResponse::error('Cannot update webinar. New scheduled time cannot be in the past.', code: 400);
            }
        }

        $this->webinars->updateWebinar($webinarId, $data);

        // If send_notifications is being enabled and scheduled_for is changed to a future date, send notification
        $toSendNotification = $data['send_notifications'] ?? $webinar['send_notifications'];
        if (
            $toSendNotification && (
                isset($data['scheduled_for']) &&
                !Time::parse($data['scheduled_for'])->equals(Time::parse($webinar['scheduled_for']))
            )
        ) {
            Services::notificationManager()->sendNotifications(
                new WebinarRescheduledEvent(
                    $webinarId,
                    $data['title'] ?? $webinar['title'],
                    $data['scheduled_for'],
                    $webinar['course_id']
                )
            );
        }

        return ApiResponse::success();
    }

    /**
     * Update the presentation file for a specific webinar
     *
     * @param int $webinarId The id of the webinar to update
     */
    public function updatePresentation(int $webinarId)
    {
        $webinar = $this->webinars->getDetails($webinarId);

        $rules = [
            'presentation' => self::presentationRules,
        ];

        if (!$this->validateData($this->request->getPost(), $rules)) {
            $errors = $this->validator->getErrors();
            return ApiResponse::error(implode(' ', $errors), code: 400);
        }

        if (!$webinar) {
            return ApiResponse::error('Webinar not found', code: 404);
        }

        if (!$this->canAccessCourse($webinar['course_id'])) {
            return ApiResponse::error('User does not have access to update webinar', code: 403);
        }

        $presentationFile = $this->request->getFile('presentation');
        if ($presentationFile && $presentationFile->isValid()) {
            $presentation = new WebinarPresentation($presentationFile);
            $data = [
                'presentation_id' => $presentation->getId(),
                'presentation_name' => $presentation->getName()
            ];

            if ($webinar['presentation_id']) {
                WebinarPresentation::deletePresentation($webinar['presentation_id']);
            }

            $this->webinars->updateWebinar($webinarId, $data);

            return ApiResponse::success(message: "Webinar presentation updated.");
        } else {
            return ApiResponse::error('No valid presentation file uploaded', code: 400);
        }

        if ($webinar['presentation_id']) {
            WebinarPresentation::deletePresentation($webinar['presentation_id']);
        }

        $this->webinars->updateWebinar($webinarId, $data);

        return ApiResponse::success(message: "Webinar presentation updated.");
    }

    /**
     * Delete the presentation file for a specific webinar
     *
     * @param int $webinarId The id of the webinar to update
     */
    public function deletePresentation(int $webinarId)
    {
        $webinar = $this->webinars->getDetails($webinarId);

        if (!$webinar) {
            return ApiResponse::error('Webinar not found', code: 404);
        }

        if (!$this->canAccessCourse($webinar['course_id'])) {
            return ApiResponse::error('User does not have access to update webinar', code: 403);
        }

        if (!$webinar['presentation_id']) {
            return ApiResponse::error('No presentation file to delete', code: 400);
        }

        WebinarPresentation::deletePresentation($webinar['presentation_id']);

        $this->webinars->updateWebinar($webinarId, [
            'presentation_id' => null,
            'presentation_name' => null
        ]);

        return ApiResponse::success(message: "Webinar presentation deleted.");
    }

    /**
     * Marks a webinar as deleted, recording the deletion date and the user (from the staffs table) who deleted it.
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

        $this->webinars->markAsDeleted($webinarId, WebSessionManager::currentAPIUser()->user_table_id);
        if (
            $webinar['send_notifications'] &&
            ($scheduledDate = \DateTime::createFromFormat('Y-m-d H:i:s', $webinar['scheduled_for'])) && time() < $scheduledDate->format('U')
        ) {
            Services::notificationManager()->sendNotifications(
                new WebinarCancelledEvent(
                    $webinar['course_id'],
                    $webinar['session_id'],
                    $webinar['title'],
                    $webinar['scheduled_for'],
                )
            );
        }

        return ApiResponse::success();
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

        if (!$this->bbbService->meetingExists($webinar['room_id'])) {
            $bbbPresentation = $webinar['presentation_id'] ?
                $this->bbbService->createPresentation(
                    WebinarPresentation::getPublicUrl(base_url(), $webinar['id']),
                    $webinar['presentation_name']
                ) : null;

            $meetingEndedUrl = getMeetingEndedUrl(encodeRoomId($webinar['room_id']));
            $recordingReadyUrl = getRecordingReadyUrl();

            if (!$this->bbbService->createMeeting(
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
            if ($webinar['send_notifications']) {
                Services::notificationManager()->sendNotifications(
                    new WebinarStartedEvent($webinar['id'], $webinar['title'])
                );
            }
        }

        $this->webinars->updateWebinar($webinarId, ['end_time' => null]);

        return ApiResponse::success(data: $this->bbbService->getJoinUrl(
            meetingId: $webinar['room_id'],
            fullName: $fullName,
            logoutURL: $redirectURL,
            userId: $currentUser->id,
        ));
    }

    public function endWebinar(string $hash)
    {
        $decodedRoomId = decodeRoomId($hash);

        $webinar = $this->webinars->getDetailsByRoomId($decodedRoomId);

        if (!$webinar) {
            return ApiResponse::error('Webinar not found', code: ResponseInterface::HTTP_NOT_FOUND);
        }

        if (!$webinar['start_time']) {
            // Do nothing if webinar has not started
            return ApiResponse::success();
        }

        $endTimeTimestamp = time() + self::WEBINAR_END_DELAY_SECONDS;
        $this->webinars->updateWebinar($webinar['id'], ['end_time' => date('Y-m-d H:i:s', $endTimeTimestamp)]);

        return ApiResponse::success();
    }

    public function recordingReadyCallback()
    {
        $signedParams = $this->request->getPost('signed_parameters');

        try {
            $params = JWT::decode($signedParams, new Key(env('BBB_SECRET'), 'HS256'));
        } catch (SignatureInvalidException $e) {
            return ApiResponse::error('Invalid signed parameters', code: ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $roomId = $params->meeting_id;
        $recordId = $params->record_id;

        // If webinar not found, return 410
        $webinar = $this->webinars->getDetailsByRoomId($roomId);
        if (!$webinar) {
            return ApiResponse::error(code: ResponseInterface::HTTP_GONE);
        }

        // Store recording id and url
        $recording_url = $this->bbbService->getRecording($recordId);
        $recording_date = date('Y-m-d H:i:s');
        $recordings = array_merge($webinar['recordings'], [[
            'id' => $recordId,
            'url' => $recording_url,
            'date' => $recording_date
        ]]);

        $this->webinars->updateWebinar($webinar['id'], [
            'recordings' => $recordings
        ]);

        // Send notifications
        if ($webinar['send_notifications']) {
            Services::notificationManager()->sendNotifications(
                new RecordingReadyEvent($webinar['id'], $webinar['title'], $recording_url)
            );
        }

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
