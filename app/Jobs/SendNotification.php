<?php

namespace App\Jobs;

use Alatise\RelayQ\Contracts\JobInterface;
use Alatise\RelayQ\Traits\Queueable;

class SendNotification implements JobInterface
{
    use Queueable;

    public function __construct(
        public int    $userId,
        public string $title,
        public string $body
    )
    {
    }

    public function handle(): void
    {
        log_message('info', "RELAYQ::RUN:SUCCESS - RelayQ notify {$this->userId}: {$this->title}");
    }

    public function toArray(): array
    {
        return ['userId' => $this->userId, 'title' => $this->title, 'body' => $this->body];
    }
}