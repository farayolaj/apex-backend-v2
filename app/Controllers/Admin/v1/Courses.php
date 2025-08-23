<?php

namespace App\Controllers\Admin\v1;

use App\Controllers\BaseController;
use App\Libraries\ApiResponse;
use App\Traits\Crud\EntityListTrait;
use Throwable;

class Courses extends BaseController
{
    use EntityListTrait;

    public function index(){
        $payload = $this->listApiEntity('courses');
        return ApiResponse::success(data: $payload);
    }

    public function show(int $id){
        $payload = $this->showListEntity('courses', $id);
        return ApiResponse::success(data: $payload);
    }

    /**
     * @throws Throwable
     */
    public function store(){
        $course = new \App\Entities\Courses();

        $payload = $this->request->getPost();

        $row = $course->insertSingle(
            $payload ?? [],
            $this->request->getFiles() ?? []
        );
        if(!$row) return ApiResponse::error("Unable to create course");

        return ApiResponse::success('Course inserted successfully', $payload);
    }

    /**
     * @throws Throwable
     */
    public function update($id){
        $course = new \App\Entities\Courses();
        $payload = $this->request->getRawInput();

        $row = $course->updateSingle(
            $id, $payload ?? [],
        );
        if(!$row) return ApiResponse::error("Unable to update course");

        return ApiResponse::success('Course updated successfully', $payload);
    }

    /**
     * @throws Throwable
     */
    public function delete($id){
        $course = new \App\Entities\Courses();
        $row = $course->deleteSingle($id);
        if(!$row) return ApiResponse::error("Unable to delete course");

        return ApiResponse::success('Course deleted successfully');
    }

}