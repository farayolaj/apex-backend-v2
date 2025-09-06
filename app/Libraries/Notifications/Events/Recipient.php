<?php

namespace App\Libraries\Notifications\Events;

class Recipient
{
  public function __construct(public string $tableName, public int $id) {}
}
