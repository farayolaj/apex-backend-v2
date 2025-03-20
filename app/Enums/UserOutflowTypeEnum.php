<?php

namespace App\Enums;

enum UserOutflowTypeEnum: string
{
    case DIRECTOR = 'dir';

    case DEPUTY_BURSAR = 'db';

    case DB_STAFF = 'db-staff';

    case AUDITOR = 'aud';

    case PROCUREMENT = 'proc';
}
