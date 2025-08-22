<?php

namespace App\Models;

use BigBlueButton\BigBlueButton;
use BigBlueButton\Enum\Role;
use BigBlueButton\Parameters\CreateMeetingParameters;
use BigBlueButton\Parameters\GetMeetingInfoParameters;
use BigBlueButton\Parameters\GetRecordingsParameters;
use BigBlueButton\Parameters\JoinMeetingParameters;

/**
 * Model for interacting with BigBlueButton API.
 */
class BBBModel
{
  private BigBlueButton $bbb;

  public function __construct()
  {
    $this->bbb = new BigBlueButton();
  }

  /**
   * Get the join URL for a meeting.
   *
   * @param string $meetingId
   * @param string $fullName
   * @return string|null
   */
  public function getJoinUrl(string $meetingId, string $fullName): ?string
  {
    $joinMeetingParams = new JoinMeetingParameters($meetingId, $fullName, Role::MODERATOR);
    $joinMeetingParams->setRedirect(true);

    $joinUrl = $this->bbb->getJoinMeetingURL($joinMeetingParams);

    return $joinUrl;
  }

  /**
   * Check if a meeting exists.
   */
  public function meetingExists(string $meetingId): bool
  {
    $meetingInfoParams = new GetMeetingInfoParameters($meetingId);
    $meetingInfoResponse = $this->bbb->getMeetingInfo($meetingInfoParams);

    return $meetingInfoResponse->success();
  }

  /**
   * Create a new meeting.
   *
   * @return bool Returns true if meeting was created successfully, else returns false.
   */
  public function createMeeting(string $meetingId, string $meetingName, ?BBBPresentation $presentation = null)
  {
    $createParams = new CreateMeetingParameters($meetingId, $meetingName);
    $createParams->setAutoStartRecording(true);
    $createParams->setRecord(true);
    $createParams->setAllowStartStopRecording(false);
    $createParams->setAllowModsToUnmuteUsers(true);

    if ($presentation) {
      $createParams->addPresentation($presentation->url, null, $presentation->name);
    }

    $createMeetingResponse = $this->bbb->createMeeting($createParams);

    return $createMeetingResponse->success();
  }

  /**
   * Get recordings for a meeting.
   */
  public function getRecordings(string $meetingId)
  {
    $getRecordingsParams = new GetRecordingsParameters();
    $getRecordingsParams->setMeetingID($meetingId);
    $getRecordingsResponse = $this->bbb->getRecordings($getRecordingsParams);

    if ($getRecordingsResponse->success()) {
      return $getRecordingsResponse->getRecords();
    }

    return [];
  }

  /**
   * Create a BBBPresentation instance.
   *
   * @param string $url Publicly accessible url to get presentation file
   * @param string $name Presentation file name
   * @return BBBPresentation
   */
  public static function createPresentation(string $url, string $name): BBBPresentation
  {
    return new BBBPresentation($url, $name);
  }
}

/**
 * Represents a presentation for BigBlueButton meetings.
 */
class BBBPresentation
{
  /**
   * BBBPresentation constructor.
   *
   * @param string $url Publicly accessible url to get presentation file
   * @param string $name Presentation file name
   */
  public function __construct(public string $url, public string $name) {}
}
