<?php

namespace App\Validation\Contracts;

namespace App\Validation\Contracts;

interface RulesProvider
{
    public static function rules(): array;
    public static function messages(): array;
}