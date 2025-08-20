<?php

namespace App\Enums;

enum WebinarStatusEnum: string
{
  case SCHEDULED = 'scheduled';

  case PENDING = 'pending';

  case IN_PROGRESS = 'in_progress';

  case ENDED = 'ended';
}
