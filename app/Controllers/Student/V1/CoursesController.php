<?php

namespace App\Controllers\Student\V1;

use App\Controllers\BaseController;
use App\Entities\Courses;
use App\Libraries\ApiResponse;
use App\Libraries\EntityLoader;
use App\Services\GoogleDriveStorageService;
use Config\Services;

class CoursesController extends BaseController
{
    private Courses $courses;

    public function __construct()
    {
        $this->courses = EntityLoader::loadClass(null, 'courses');
    }

    public function courseDetails($id)
    {
        $result = $this->courses->getDetails($id);
        $result['course_guide'] = GoogleDriveStorageService::getPublicUrl(
            $result['course_guide_id']
        );
        $result['course_room_url'] = Services::courseRoomModel()->getCourseRoomLink(
            $result['main_course_id']
        );
        return ApiResponse::success('success', $result);
    }
}
