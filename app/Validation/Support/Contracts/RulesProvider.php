<?php

namespace App\Validation\Contracts;

namespace App\Validation\Support\Contracts;

interface RulesProvider
{
    public static function authorize(array $data, array $ctx): bool;
    public static function rules(): array;
    public static function messages(): array;
    public static function denyMessage(): string;
}