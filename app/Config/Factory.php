<?php

namespace Config;

use CodeIgniter\Config\Factory as BaseFactory;
use App\Models\Crud;

class Factory extends BaseFactory
{
    public array $entities = [
        'instanceOf' => Crud::class,
        'path' => 'Entities',
    ];
}