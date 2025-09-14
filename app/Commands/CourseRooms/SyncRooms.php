<?php

namespace App\Commands\CourseRooms;

use App\Entities\Courses;
use App\Libraries\EntityLoader;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

class SyncRooms extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Course Rooms';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'sync:rooms';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Create and sync course rooms in Matrix for all courses. Courses without a room will have one created. Courses with existing rooms will be skipped.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'sync:rooms [course_id_or_code]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'course_id' => 'Optional course id or course code to sync a specific course only',
    ];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $courseId = $params[0] ?? null;

        if ($courseId) {
            return $this->createCourseRoom($courseId);
        } else {
            return $this->createCourseRooms();
        }
    }

    private function createCourseRoom(string $courseId)
    {
        $courseRoomModel = Services::courseRoomModel();
        /**
         * @var Courses
         */
        $courses = EntityLoader::loadClass(null, 'courses');

        CLI::write("Fetching course with ID: {$courseId}", 'yellow');

        $course = $courses->getCourse($courseId);

        if (!$course) {
            CLI::write("Course with ID: {$courseId} not found.", 'red');
            return EXIT_USER_INPUT;
        }

        CLI::write('Creating and syncing course room... Please wait.', 'yellow');

        $res = $courseRoomModel->createCourseRoom($course['id'], $course['code'], $course['title']);

        if ($res) {
            CLI::write("Created new course room.", 'green');
        } else {
            CLI::write("Failed to create course room. Check logs for details.", 'red');
        }
    }

    private function createCourseRooms()
    {
        $courseRoomModel = Services::courseRoomModel();

        CLI::write('Creating and syncing course rooms... Please wait.', 'yellow');

        $res = $courseRoomModel->createCourseRooms();

        if ($res['created'] > 0) {
            CLI::write("Created {$res['created']} new course rooms.", 'green');
        } else {
            CLI::write("No new course rooms created.", 'yellow');
        }

        $failedLength = count($res['failed']);
        if ($failedLength > 0) {
            $failedRooms = implode(', ', $res['failed']);
            CLI::write("Failed to create {$failedLength} course rooms with course ids: {$failedRooms}. Check logs for details.", 'red');
        }
    }
}
