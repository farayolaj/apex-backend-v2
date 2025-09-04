<?php

namespace App\Libraries\Notifications\Events;

class Sender
{
    public function __construct(
        public string $tableName,
        public int $id
    ) {}
}
