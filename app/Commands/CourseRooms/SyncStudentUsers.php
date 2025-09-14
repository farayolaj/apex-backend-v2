<?php

namespace App\Commands\CourseRooms;

use App\Entities\Students;
use App\Libraries\EntityLoader;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

class SyncStudentUsers extends BaseCommand
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
    protected $name = 'sync:students';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Create user accounts in Matrix for all active students without one.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'sync:students [matric_no]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'matric_no' => 'Optional matric number to create a single student user for that student only.',
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
        $matricNo = $params[0] ?? null;

        if ($matricNo) {
            return $this->createStudentUser($matricNo);
        } else {
            return $this->createStudentUsers();
        }
    }

    private function createStudentUser(string $matricNo)
    {
        $courseRoomModel = Services::courseRoomModel();
        /**
         * @var Students
         */
        $students = EntityLoader::loadClass(null, 'students');

        CLI::write("Looking up student with matric number: {$matricNo}...", 'yellow');

        $student = $students->getStudentsByIdOrMatricNo($matricNo);

        if (!$student) {
            CLI::write("No active student found with matric number: {$matricNo}.", 'red');
            return;
        }

        CLI::write("Creating student user for matric number: {$matricNo}... Please wait.", 'yellow');

        $res = $courseRoomModel->createStudentUser(
            $student['id'],
            $student['matric_number'],
            "{$student['firstname']} {$student['lastname']}",
            $student['email']
        );

        if ($res) {
            CLI::write("Created student user for matric number: {$matricNo}.", 'green');
        } else {
            CLI::write("Failed to create student user. Check logs for details.", 'red');
        }
    }

    private function createStudentUsers()
    {
        $courseRoomModel = Services::courseRoomModel();

        CLI::write('Creating student users... Please wait.', 'yellow');

        $res = $courseRoomModel->createStudentUsers();

        if ($res['created'] > 0) {
            CLI::write("Created {$res['created']} new student users.", 'green');
        } else {
            CLI::write("No new student user created.", 'yellow');
        }

        $failedLength = count($res['failed']);
        if ($failedLength > 0) {
            $failedStudents = implode(', ', $res['failed']);
            CLI::write("Failed to create {$failedLength} student users with ids: {$failedStudents}. Check logs for details.", 'red');
        }
    }
}
