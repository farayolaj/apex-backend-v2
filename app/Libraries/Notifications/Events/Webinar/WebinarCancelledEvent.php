<?php

namespace App\Libraries\Notifications\Events\Webinar;

use App\Entities\Course_enrollment;
use App\Entities\Webinars;
use App\Libraries\EntityLoader;
use App\Libraries\Notifications\Events\EventInterface;
use App\Libraries\Notifications\Events\Recipient;
use App\Libraries\Notifications\Events\Sender;
use CodeIgniter\I18n\Time;

class WebinarCancelledEvent implements EventInterface
{
    public function __construct(
        private string $courseId,
        private string $sessionId,
        private string $webinarTitle,
        private string $scheduledFor,
    ) {}

    public function getName(): string
    {
        return 'webinar.cancelled';
    }

    public function getTitle(): string
    {
        return "Webinar Cancelled";
    }

    public function getMessage(): string
    {
        $scheduledFor = new Time($this->scheduledFor);
        $formattedDate = $scheduledFor->format('l, jS F Y \a\t g:ia');
        return "Webinar ({$this->webinarTitle}) previously scheduled for {$formattedDate} has been cancelled.";
    }

    public function getMetadata(): array
    {
        return [
            'courseId' => $this->courseId,
            'sessionId' => $this->sessionId,
            'webinarTitle' => $this->webinarTitle,
        ];
    }

    public function getRecipients(): array
    {
        /** @var Webinars */
        $webinars = EntityLoader::loadClass(null, 'webinars');
        /** @var Course_enrollment */
        $courseEnrollment = EntityLoader::loadClass(null, 'course_enrollment');

        // Get the students taking the course
        $studentIds = $courseEnrollment->getEnrolledStudents($this->courseId, $this->sessionId) ?? [];

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
