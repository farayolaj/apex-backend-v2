<?php

namespace App\Controllers\Admin\v1;

use App\Controllers\BaseController;
use App\Libraries\ApiResponse;
use App\Traits\Crud\EntityListTrait;

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
}