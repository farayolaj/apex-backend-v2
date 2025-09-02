<?php

namespace App\Models;

use BigBlueButton\BigBlueButton;
use BigBlueButton\Enum\DocumentOption;
use BigBlueButton\Enum\GuestPolicy;
use BigBlueButton\Enum\Role;
use BigBlueButton\Parameters\Config\DocumentOptionsStore;
use BigBlueButton\Parameters\CreateMeetingParameters;
use BigBlueButton\Parameters\DeleteRecordingsParameters;
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
   * @param bool $isStudent
   * @return string|null
   */
  public function getJoinUrl(string $meetingId, string $fullName, string $logoutURL, ?string $userId = null, bool $isStudent = false): string
  {
    $joinMeetingParams = new JoinMeetingParameters($meetingId, $fullName, $isStudent ? Role::VIEWER : Role::MODERATOR);
    $joinMeetingParams
      ->setRedirect(true)
      ->setCustomParameter('logoutURL', $logoutURL);

    if ($isStudent) $joinMeetingParams->setGuest(true);
    if ($userId) $joinMeetingParams->setUserID($userId);

    $joinUrl = $this->bbb->getUrlBuilder()->getJoinMeetingURL($joinMeetingParams);

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
  public function createMeeting(string $meetingId, string $meetingName, string $meetingEndedUrl, string $recordingReadyUrl, ?BBBPresentation $presentation = null)
  {
    $createParams = new CreateMeetingParameters($meetingId, $meetingName);
    $createParams->setAutoStartRecording(true);
    $createParams->setRecord(true);
    $createParams->setAllowStartStopRecording(false);
    $createParams->setAllowModsToUnmuteUsers(true);
    $createParams->setGuestPolicy(GuestPolicy::ASK_MODERATOR);
    $createParams->setEndWhenNoModerator(true);
    $createParams->setEndWhenNoModeratorDelayInMinutes(120);
    $createParams->setMeetingEndedURL($meetingEndedUrl);
    $createParams->setRecordingReadyCallbackUrl($recordingReadyUrl);

    if ($presentation) {
      $documentOptionStore = new DocumentOptionsStore();
      $documentOptionStore->addAttribute(DocumentOption::CURRENT, 'true');
      $documentOptionStore->addAttribute(DocumentOption::DOWNLOADABLE, 'true');

      $createParams->addPresentation(
        nameOrUrl: $presentation->url,
        filename: $presentation->name,
        attributes: $documentOptionStore
      );
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
   * Get a particular recording
   */
  public function getRecording(string $id): ?string
  {
    $getRecordingsParams = new GetRecordingsParameters();
    $getRecordingsParams->setRecordId($id);
    $getRecordingsResponse = $this->bbb->getRecordings($getRecordingsParams);

    if ($getRecordingsResponse->success() && !empty($getRecordingsResponse->getRecords())) {
      $formats = $getRecordingsResponse->getRecords()[0]->getFormats();
      if (!empty($formats)) {
        return $formats[0]->getUrl();
      }
    }

    return null;
  }

  /**
   * Delete recordings
   * @param string[] $recordIngIds
   */
  public function deleteRecordings(array $recordIngIds)
  {
    $deleteRecordingsParams = new DeleteRecordingsParameters(implode(',', $recordIngIds));
    $deleteRecordingsResponse = $this->bbb->deleteRecordings($deleteRecordingsParams);

    return $deleteRecordingsResponse->success();
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
