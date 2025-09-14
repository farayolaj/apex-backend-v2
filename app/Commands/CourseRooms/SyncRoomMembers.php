<?php

namespace App\Commands\CourseRooms;

use App\Entities\Courses;
use App\Libraries\EntityLoader;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

class SyncRoomMembers extends BaseCommand
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
    protected $name = 'sync:members';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Add all course participants to their respective course rooms in Matrix.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'sync:members [course_code]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'course_code' => 'Optional course code to sync members for a specific course only.'
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
        $courseCode = $params[0] ?? null;

        if ($courseCode) {
            return $this->syncMembersForCourse($courseCode);
        } else {
            return $this->syncCoursesMembers();
        }
    }

    private function syncMembersForCourse(string $courseCode)
    {
        $courseRoomModel = Services::courseRoomModel();
        /** @var Courses $courses */
        $courses = EntityLoader::loadClass(null, 'courses');

        CLI::write("Adding members to course room for course code: {$courseCode}... Please wait.", 'yellow');

        $res = $courseRoomModel->addMembersToCourseRoom($courseCode);

        if ($res === null) {
            CLI::write("Course with code {$courseCode} not found or without room. Please check the course code or create the room first.", 'red');
            return EXIT_USER_INPUT;
        } elseif ($res === false) {
            CLI::write("Members could not be added to course room for course code: {$courseCode}.", 'red');
            return EXIT_ERROR;
        } else {
            CLI::write("Members added to course room for course code: {$courseCode}.", 'green');
            return;
        }
    }

    private function syncCoursesMembers()
    {
        $courseRoomModel = Services::courseRoomModel();

        CLI::write('Adding members to course rooms... Please wait.', 'yellow');

        $courseRoomModel->addMembersToCourseRooms();

        CLI::write('Members added to course rooms successfully.', 'green');
    }
}
