<?php

namespace App\Controllers\Student\V1;

use App\Controllers\BaseController;
use App\Entities\Webinars as EntitiesWebinars;
use App\Libraries\ApiResponse;
use App\Libraries\EntityLoader;
use App\Libraries\WebinarPresentation;
use App\Models\BBBModel;
use App\Models\WebSessionManager;
use CodeIgniter\I18n\Time;

class WebinarController extends BaseController
{
    private EntitiesWebinars $webinars;
    private BBBModel $bbbModel;

    public function __construct()
    {
        $this->webinars = EntityLoader::loadClass(null, 'webinars');
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
        unset($webinar['recording_id']);

        $webinar['enable_comments'] = $webinar['enable_comments'] ? true : false;
        $webinar['send_notifications'] = $webinar['send_notifications'] ? true : false;

        return $webinar;
    }

    /**
     * List webinars for a specific course
     *
     * @param int $sessionId
     * @param int $courseId
     */
    public function listWebinars(int $sessionId, int $courseId)
    {
        $payload = $this->webinars->list($sessionId, $courseId);
        $payload = array_map([$this, 'processWebinar'], $payload);

        return ApiResponse::success(data: $payload);
    }

    /**
     * Get the details for a given webinar.
     */
    public function getWebinar(int $webinarId)
    {
        $webinar = $this->webinars->getDetails($webinarId);

        if (!$webinar) {
            return ApiResponse::error('Webinar not found', code: 404);
        }

        $webinar = $this->processWebinar($webinar);

        return ApiResponse::success(data: $webinar);
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

        if (!$this->bbbModel->meetingExists($webinar['room_id'])) {
            $bbbPresentation = $webinar['presentation_id'] ?
                $this->bbbModel->createPresentation(
                    WebinarPresentation::getPublicUrl(base_url(), $webinar['id']),
                    $webinar['presentation_name']
                ) : null;

            $meetingEndedUrl = getMeetingEndedUrl(encodeRoomId($webinar['room_id']));
            $recordingReadyUrl = getRecordingReadyUrl();

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
        $fullName = trim($currentUser->firstname . ' ' . $currentUser->lastname);
        $redirectURL = $this->request->getGet('redirect_url') ??
            $this->request->header('origin')->getValue();

        // Increment join_count for webinar
        $this->webinars->incrementJoinCount($webinar['id']);

        return ApiResponse::success(data: $this->bbbModel->getJoinUrl(
            meetingId: $webinar['room_id'],
            fullName: $fullName,
            logoutURL: $redirectURL,
            userId: $currentUser->id,
            isStudent: true
        ));
    }

    public function logPlayback(int $webinarId)
    {
        $this->webinars->incrementPlaybackCount($webinarId);
        return ApiResponse::success();
    }
}
