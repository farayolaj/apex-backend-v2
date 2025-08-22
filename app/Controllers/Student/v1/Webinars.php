<?php

namespace App\Controllers\Student\v1;

use App\Controllers\Admin\v1\Presentation;
use App\Controllers\BaseController;
use App\Entities\Webinars as EntitiesWebinars;
use App\Enums\WebinarStatusEnum;
use App\Libraries\ApiResponse;
use App\Libraries\EntityLoader;
use App\Libraries\WebinarUtil;
use App\Models\BBBModel;
use App\Models\WebSessionManager;
use BigBlueButton\BigBlueButton;
use BigBlueButton\Parameters\GetRecordingsParameters;
use CodeIgniter\I18n\Time;

class Webinars extends BaseController
{
    private EntitiesWebinars $webinars;
    private BBBModel $bbbModel;

    public function __construct()
    {
        $this->webinars = EntityLoader::loadClass(null, 'webinars');
        $this->bbbModel = EntityLoader::loadClass(null, 'bbbModel', "App\\Models\\");
    }

    private function processWebinar(array $webinar): array
    {
        if ($webinar['presentation_id']) {
            $webinar['presentation_url'] = Presentation::getPublicUrl(base_url(), $webinar['presentation_id']);
        } else {
            $webinar['presentation_url'] = null;
        }

        unset($webinar['presentation_id']);
        unset($webinar['course_id']);

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
     * Get recordings for a specific webinar
     *
     * @param int $webinarId The id of the webinar to get recordings for
     */
    public function getWebinar(int $webinarId)
    {
        $webinar = $this->webinars->getDetails($webinarId);

        if (!$webinar) {
            return ApiResponse::error('Webinar not found', code: 404);
        }

        $recordings = array_map(fn($record) => [
            'id' => $record->getRecordId(),
            'date_recorded' => Time::createFromTimestamp($record->getStartTime() / 1000)->toDateTimeString(),
            'duration' => (int) (($record->getEndTime() - $record->getStartTime()) / 1000),
            'recording_url' => $record->getPlaybackFormats()[0]->getUrl(),
        ], $this->bbbModel->getRecordings($webinar['room_id']));

        $webinar = $this->processWebinar($webinar);
        $webinar['recordings'] = $recordings;

        return ApiResponse::success(data: $webinar);
    }

    public function getJoinUrl(int $webinarId): string
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
                    Presentation::getPublicUrl(base_url(), $webinar['presentation_id']),
                    $webinar['presentation_name']
                ) : null;

            if (!$this->bbbModel->createMeeting($webinar['room_id'], $webinar['title'], $bbbPresentation)) {
                return ApiResponse::error('Unable to get meeting url', code: 502);
            }
        }

        $currentUser = WebSessionManager::currentAPIUser();
        $fullName = trim($currentUser->firstname . ' ' . $currentUser->lastname);

        return ApiResponse::success(data: $this->bbbModel->getJoinUrl($webinar['room_id'], $fullName));
    }
}
