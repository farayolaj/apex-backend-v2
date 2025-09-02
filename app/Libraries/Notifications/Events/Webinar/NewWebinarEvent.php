<?php

namespace App\Libraries\Notifications\Events\Webinar;

use App\Entities\Course_enrollment;
use App\Entities\Webinars;
use App\Libraries\EntityLoader;
use App\Libraries\Notifications\Events\EventInterface;
use App\Libraries\Notifications\Events\Recipient;
use CodeIgniter\I18n\Time;

class NewWebinarEvent implements EventInterface
{
  public function __construct(
    private string $webinarId,
    private string $title,
    private string $scheduledFor,
    private string $courseId
  ) {}

  public function getName(): string
  {
    return 'webinar.new';
  }

  public function getTitle(): string
  {
    return "New Webinar Scheduled";
  }

  public function getMessage(): string
  {
    $scheduledFor = new Time($this->scheduledFor);
    $formattedDate = $scheduledFor->format('l, jS F Y \a\t g:ia');
    return "{$this->title} has been scheduled for {$formattedDate}.";
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
    /** @var Webinars */
    $webinars = EntityLoader::loadClass(null, 'webinars');
    /** @var Course_enrollment */
    $courseEnrollment = EntityLoader::loadClass(null, 'course_enrollment');

    // Get the id of the course the webinar belongs to, and the session id;
    $webinar = $webinars->getDetails($this->webinarId);
    $courseId = $webinar['course_id'];
    $sessionId = $webinar['session_id'];

    // Get the students taking the course
    $studentIds = $courseEnrollment->getEnrolledStudents($courseId, $sessionId) ?? [];

    $recipients = [];

    foreach ($studentIds as $studentId) {
      $recipients[] = new Recipient('students', $studentId);
    }

    return $recipients;
  }
}
