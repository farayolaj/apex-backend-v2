<?php

namespace App\Libraries;

use App\Enums\WebinarStatusEnum;
use BigBlueButton\BigBlueButton;
use BigBlueButton\Enum\Role;
use BigBlueButton\Parameters\GetMeetingInfoParameters;
use BigBlueButton\Parameters\JoinMeetingParameters;

class WebinarUtil
{
  public static function getJoinUrl(BigBlueButton $bbb, array $webinar, string $fullName)
  {
    if (strtotime($webinar['scheduled_for']) > time()) {
      return null;
    }

    $joinMeetingParams = new JoinMeetingParameters($webinar['room_id'], $fullName, Role::MODERATOR);
    $joinMeetingParams->setRedirect(true);

    $joinUrl = $bbb->getJoinMeetingURL($joinMeetingParams);

    return $joinUrl;
  }

  public static function getStatus(BigBlueButton $bbb, array $webinar): WebinarStatusEnum
  {
    if (strtotime($webinar['scheduled_for']) > time()) {
      return WebinarStatusEnum::SCHEDULED;
    } else {
      $meetingInfoParams = new GetMeetingInfoParameters($webinar['room_id']);
      $meetingInfoResponse = $bbb->getMeetingInfo($meetingInfoParams);

      if ($meetingInfoResponse->failed() && $meetingInfoResponse->getMessageKey() === 'notFound') {
        return WebinarStatusEnum::ENDED;
      } else if ($meetingInfoResponse->success() && $meetingInfoResponse->getMeeting()->isRunning()) {
        return WebinarStatusEnum::IN_PROGRESS;
      } else {
        return WebinarStatusEnum::PENDING;
      }
    }
  }
}
