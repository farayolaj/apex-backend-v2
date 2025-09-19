<?php

namespace App\Commands\CourseRooms;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

class SyncGeneralRoomMembers extends BaseCommand
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
    protected $name = 'rooms:sync:general:members';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Synchronize active students as members of a general Matrix room.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'rooms:sync:general:members [arguments]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'roomId' => 'The room identifier (e.g. !abcdefg:matrix.org)',
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
        $roomId = $params[0] ?? CLI::prompt('Enter the room identifier (e.g. !abcdefg:matrix.org):');

        if (!$roomId) {
            CLI::error('Room identifier is required.');
            return;
        }

        CLI::write("Syncing members for room: $roomId", 'yellow');

        $courseRoomModel = Services::courseRoomModel();
        $res = $courseRoomModel->addMembersToGeneralRoom($roomId);

        if ($res) {
            CLI::write("Members synced successfully for room: $roomId", 'green');
        } else {
            CLI::error("Failed to sync members for room: $roomId");
        }
    }
}
