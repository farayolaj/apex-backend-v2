<?php

namespace App\Libraries\Notifications\Events\Webinar;

use App\Entities\Course_enrollment;
use App\Entities\Webinars;
use App\Libraries\EntityLoader;
use App\Libraries\Notifications\Events\EventInterface;
use App\Libraries\Notifications\Events\Recipient;

class WebinarStartedEvent implements EventInterface
{
    public function __construct(
        private string $webinarId,
        private string $title,
    ) {}

    public function getName(): string
    {
        return 'webinar.started';
    }

    public function getTitle(): string
    {
        return "Webinar ({$this->title}) has started.";
    }

    public function getMessage(): string
    {
        return "Join the webinar now.";
    }

    public function getMetadata(): array
    {
        return [
            'webinarId' => $this->webinarId,
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
