<?php

namespace App\Jobs;

use Alatise\RelayQ\Contracts\JobInterface;
use Alatise\RelayQ\Traits\Queueable;
use App\Entities\Courses;
use CodeIgniter\CLI\CLI;

class SendNotification implements JobInterface
{
    use Queueable;

    public function __construct(
    ){}

    public function toArray(): array
    {
        return [];
    }

    public function handle(): void
    {
        $course = new Courses();
        $course->insertDummyData();
        CLI::write('RELAYQ::RUN:SUCCESS - RelayQ insertion');
        log_message('info', "RELAYQ::RUN:SUCCESS - RelayQ insertion");
    }

}