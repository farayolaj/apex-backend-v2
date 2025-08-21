<?php

namespace App\Controllers\Student\v1;

use App\Controllers\BaseController;
use App\Enums\WebinarStatusEnum;
use App\Libraries\ApiResponse;
use App\Libraries\EntityLoader;
use App\Libraries\WebinarUtil;
use App\Models\WebSessionManager;
use BigBlueButton\BigBlueButton;
use BigBlueButton\Parameters\GetRecordingsParameters;
use CodeIgniter\I18n\Time;

class Webinars extends BaseController
{
    private object $webinars;
    private BigBlueButton $bbb;

    public function __construct()
    {
        $this->webinars = EntityLoader::loadClass(null, 'webinars');
        $this->bbb = new BigBlueButton();
    }

    /**
     * List webinars for a specific course
     *
     * @param int $courseId
     */
    public function listWebinars(int $courseId)
    {
        $payload = $this->webinars->list($courseId);
        $currentUser = WebSessionManager::currentAPIUser();

        $fullName = trim($currentUser->firstname . ' ' . $currentUser->lastname);

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
    public function getWebinar(int $webinarId)
    {
        $webinar = $this->webinars->getDetails($webinarId);

        $getRecordingsParameters = new GetRecordingsParameters();
        $getRecordingsParameters->setMeetingID($webinar['room_id']);
        $getRecordingsResponse = $this->bbb->getRecordings($getRecordingsParameters);

        if (!$getRecordingsResponse->success()) {
            log_message('error', 'Failed to get recordings for webinar: ' . $getRecordingsResponse->getMessage());
            return ApiResponse::error('Failed to fetch recordings. ' . $getRecordingsResponse->getMessage(), code: 500);
        }

        $recordings = [];

        foreach (
            $getRecordingsResponse->getRecords() as $record
        ) {
            $startTime = Time::createFromTimestamp($record->getStartTime() / 1000, 'Africa/Lagos');
            $endTime = Time::createFromTimestamp($record->getEndTime() / 1000, 'Africa/Lagos');
            $duration = (int) (($record->getEndTime() - $record->getStartTime()) / 1000); // in seconds

            $recordings[] = [
                'id' => $record->getRecordId(),
                'date_recorded' => $startTime->toDateTimeString(),
                'duration' => $duration,
                'recording_url' => $record->getPlaybackFormats()[0]->getUrl(),
            ];
        }

        $webinar['recordings'] = $recordings;

        return ApiResponse::success(data: $webinar);
    }
}
