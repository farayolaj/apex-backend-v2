<?php

namespace App\Controllers\Student\V1;

use App\Controllers\BaseController;
use App\Libraries\ApiResponse;
use App\Libraries\EntityLoader;

class CoursesController extends BaseController
{
    public function courseDetails($id)
    {
        EntityLoader::loadClass($this, 'courses');
        $result = $this->courses->getDetails($id);
        return ApiResponse::success('success', $result);
    }
}