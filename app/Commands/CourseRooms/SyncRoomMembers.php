<?php

namespace App\Commands\CourseRooms;

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
    protected $usage = 'sync:members';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

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
        $courseRoomModel = Services::courseRoomModel();

        CLI::write('Adding members to course rooms... Please wait.', 'yellow');

        $courseRoomModel->addMembersToCourseRooms();

        CLI::write('Members added to course rooms successfully.', 'green');
    }
}
