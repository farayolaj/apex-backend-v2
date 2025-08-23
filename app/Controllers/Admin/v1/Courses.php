<?php

namespace App\Controllers\Admin\v1;

use App\Controllers\BaseController;
use App\Libraries\ApiResponse;
use App\Traits\Crud\EntityListTrait;
use PHPUnit\Exception;

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

}