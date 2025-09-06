<?php

namespace App\Libraries\Notifications\Events\Webinar;

use App\Entities\Course_enrollment;
use App\Entities\Course_manager;
use App\Entities\Webinars;
use App\Libraries\EntityLoader;
use App\Libraries\Notifications\Events\EventInterface;
use App\Libraries\Notifications\Events\Recipient;
use App\Libraries\Notifications\Events\Sender;

class NewWebinarCommentEvent implements EventInterface
{
  public function __construct(
    private string $webinarId,
    private string $webinarTitle,
    private string $courseId,
    private string $content,
    private string $author,
    private Sender $sender,
  ) {}

  public function getName(): string
  {
    return 'webinar.new_comment';
  }

  public function getTitle(): string
  {
    return "New comment on webinar ({$this->webinarTitle}) by {$this->author}";
  }

  public function getMessage(): string
  {
    return $this->content;
  }

  public function getMetadata(): array
  {
    return [
      'webinarId' => $this->webinarId,
      'webinarTitle' => $this->webinarTitle,
      'courseId' => $this->courseId,
      'content' => $this->content,
      'author' => $this->author,
    ];
  }

  public function getRecipients(): array
  {
    /** @var Webinars */
    $webinars = EntityLoader::loadClass(null, 'webinars');
    /** @var Course_manager */
    $courseManager = EntityLoader::loadClass(null, 'course_manager');
    /** @var Course_enrollment */
    $courseEnrollment = EntityLoader::loadClass(null, 'course_enrollment');

    // Get the id of the course the webinar belongs to, and the session id;
    $webinar = $webinars->getDetails($this->webinarId);
    $courseId = $webinar['course_id'];
    $sessionId = $webinar['session_id'];

    // Get the lecturers assigned to the course in the session
    $courseManagerRes = $courseManager->getCourseManagerByCourseId($courseId, $sessionId);
    $lecturerIds = $courseManagerRes ? (json_decode($courseManagerRes['course_lecturer_id'], true) ?? []) : [];

    // Get the students taking the course
    $studentIds = $courseEnrollment->getEnrolledStudents($courseId, $sessionId) ?? [];

    $recipients = [];

    foreach ($lecturerIds as $lecturerId) {
      $recipients[] = new Recipient('users_new', $lecturerId);
    }

    foreach ($studentIds as $studentId) {
      $recipients[] = new Recipient('students', $studentId);
    }

    return $recipients;
  }

  public function getSender(): ?Sender
  {
    return $this->sender;
  }
}
