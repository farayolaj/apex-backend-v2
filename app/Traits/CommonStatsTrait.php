<?php

namespace App\Traits;

trait CommonStatsTrait
{
    public static function formatToMultipleBarChart(string $label, array $data): array
    {
        return [
            'name' => $label,
            'data' => $data
        ];
    }

}