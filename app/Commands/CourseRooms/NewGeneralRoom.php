<?php

namespace App\Commands\CourseRooms;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

class NewGeneralRoom extends BaseCommand
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
    protected $name = 'rooms:new:general';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Create a new general room in Matrix for all student users.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'rooms:new:general';

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
        $alias = CLI::prompt('Enter the room alias (e.g. general):');
        $name = CLI::prompt('Enter the room name (e.g. General Room):');
        $topic = CLI::prompt('Enter the room topic (optional):', null);

        $courseRoomModel = Services::courseRoomModel();

        CLI::write("Creating general room in Matrix...", 'yellow');

        $roomId = $courseRoomModel->createGeneralRoom($alias, $name, $topic);

        if ($roomId) {
            CLI::write("General room created successfully with Room ID: $roomId", 'green');
        } else {
            CLI::error("Failed to create the general room. Please check the logs for details.");
        }
    }
}
