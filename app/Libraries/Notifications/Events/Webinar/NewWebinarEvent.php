<?php

namespace App\Libraries\Notifications\Events\Webinar;

use App\Entities\Course_enrollment;
use App\Entities\Webinars;
use App\Libraries\EntityLoader;
use App\Libraries\Notifications\Events\EventInterface;
use App\Libraries\Notifications\Events\Recipient;

class NewWebinarEvent implements EventInterface
{
  private Webinars $webinars;
  private Course_enrollment $courseEnrollment;

  public function __construct(
    private string $webinarId,
    private string $title,
    private string $scheduledFor,
    private string $courseId
  ) {
    $this->webinars = EntityLoader::loadClass(null, 'webinars');
    $this->courseEnrollment = EntityLoader::loadClass(null, 'course_enrollment');
  }

  public function getName(): string
  {
    return 'webinar.new';
  }

  public function getMetadata(): array
  {
    return [
      'webinarId' => $this->webinarId,
      'title' => $this->title,
      'scheduledFor' => $this->scheduledFor,
      'courseId' => $this->courseId,
    ];
  }

  public function getRecipients(): array
  {
    // Get the id of the course the webinar belongs to, and the session id;
    $webinar = $this->webinars->getDetails($this->webinarId);
    $courseId = $webinar['course_id'];
    $sessionId = $webinar['session_id'];

    // Get the students taking the course
    $studentIds = $this->courseEnrollment->getEnrolledStudents($courseId, $sessionId) ?? [];

    $recipients = [];

    foreach ($studentIds as $studentId) {
      $recipients[] = new Recipient('students', $studentId);
    }

    return $recipients;
  }
}
