<?php

namespace App\Commands\CourseRooms;

use App\Entities\Staffs;
use App\Libraries\EntityLoader;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

class SyncStaffUsers extends BaseCommand
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
    protected $name = 'sync:staff';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Create user accounts in Matrix for all active staff members without one.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'sync:staff [staff_id]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'staff_id' => 'Optional school allocated staff ID to create user for a specific staff member only.'
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
        $staffId = $params[0] ?? null;

        if ($staffId) {
            return $this->createStaffUser($staffId);
        } else {
            return $this->createStaffUsers();
        }
    }

    private function createStaffUser(string $staffId)
    {
        $courseRoomModel = Services::courseRoomModel();
        /**
         * @var Staffs
         */
        $staffs = EntityLoader::loadClass(null, 'staffs');

        CLI::write("Fetching staff member with ID {$staffId}...", 'yellow');

        $staff = $staffs->getStaffByIdOrStaffId($staffId);

        CLI::write("Creating user account for staff member with ID {$staffId}... Please wait.", 'yellow');

        if (!$staff) {
            CLI::write("No staff member found with ID {$staffId}.", 'red');
            return EXIT_USER_INPUT;
        }

        $res = $courseRoomModel->createStaffUser(
            $staff['id'],
            $staff['staff_id'],
            "{$staff['firstname']} {$staff['lastname']}",
            $staff['email']
        );

        if ($res) {
            CLI::write("Created new staff user for staff ID {$staffId}.", 'green');
        } else {
            CLI::write("Failed to create staff user. Check logs for details.", 'red');
        }
    }

    private function createStaffUsers()
    {
        $courseRoomModel = Services::courseRoomModel();

        CLI::write('Creating user accounts for staff members... Please wait.', 'yellow');

        $res = $courseRoomModel->createStaffUsers();

        if ($res['created'] > 0) {
            CLI::write("Created {$res['created']} new staff users.", 'green');
        } else {
            CLI::write("No new staff user created.", 'yellow');
        }

        $failedLen = count($res['failed']);
        if ($failedLen > 0) {
            $failedStaffs = implode(', ', $res['failed']);
            CLI::write("Failed to create {$failedLen} staff users with ids: {$failedStaffs}. Check logs for details.", 'red');
        }
    }
}
