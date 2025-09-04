<?php

namespace App\Libraries\Notifications\Events\Webinar;

use App\Entities\Course_enrollment;
use App\Entities\Webinars;
use App\Libraries\EntityLoader;
use App\Libraries\Notifications\Events\EventInterface;
use App\Libraries\Notifications\Events\Recipient;
use App\Libraries\Notifications\Events\Sender;
use CodeIgniter\I18n\Time;

class WebinarRescheduledEvent implements EventInterface
{
    public function __construct(
        private string $webinarId,
        private string $title,
        private string $newScheduledFor,
        private string $courseId,
    ) {}

    public function getName(): string
    {
        return 'webinar.rescheduled';
    }

    public function getTitle(): string
    {
        return "Webinar Rescheduled";
    }

    public function getMessage(): string
    {
        $newScheduledFor = new Time($this->newScheduledFor);
        $formattedDate = $newScheduledFor->format('l, jS F Y \a\t g:ia');
        return "{$this->title} has been rescheduled to {$formattedDate}.";
    }

    public function getMetadata(): array
    {
        return [
            'webinarId' => $this->webinarId,
            'title' => $this->title,
            'newScheduledFor' => $this->newScheduledFor,
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

    public function getSender(): ?Sender
    {
        return null;
    }
}
