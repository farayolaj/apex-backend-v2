<?php

namespace App\Libraries\Notifications\Events\Webinar;

use App\Entities\Course_enrollment;
use App\Entities\Course_manager;
use App\Entities\Webinars;
use App\Libraries\EntityLoader;
use App\Libraries\Notifications\Events\EventInterface;
use App\Libraries\Notifications\Events\Recipient;

class RecordingReadyEvent implements EventInterface
{
    public function __construct(
        private string $webinarId,
        private string $title,
        private string $recordingUrl,
    ) {}

    public function getName(): string
    {
        return 'webinar.recording_ready';
    }

    public function getTitle(): string
    {
        return "Webinar Recording Ready";
    }

    public function getMessage(): string
    {
        return "Recording for {$this->title} is ready.";
    }

    public function getMetadata(): array
    {
        return [
            'webinarId' => $this->webinarId,
            'recordingUrl' => $this->recordingUrl,
        ];
    }

    public function getRecipients(): array
    {
        /** @var Webinars */
        $webinars = EntityLoader::loadClass(null, 'webinars');
        /** @var Course_enrollment */
        $courseEnrollment = EntityLoader::loadClass(null, 'course_enrollment');
        /** @var Course_manager */
        $courseManager = EntityLoader::loadClass(null, 'course_manager');

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

        foreach ($studentIds as $studentId) {
            $recipients[] = new Recipient('students', $studentId);
        }

        foreach ($lecturerIds as $lecturerId) {
            $recipients[] = new Recipient('users_new', $lecturerId);
        }

        return $recipients;
    }
}
