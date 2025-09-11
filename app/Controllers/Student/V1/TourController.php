<?php

namespace App\Controllers\Student\V1;

use App\Controllers\BaseController;
use App\Libraries\ApiResponse;

class TourController extends BaseController
{
    public function getTourSettings()
    {
        $tourCourseId = get_setting('tour_course_id');

        return ApiResponse::success('success', [
            'course_id' => $tourCourseId,
        ]);
    }
}
