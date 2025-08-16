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

    public static function isPaymentValid($value): bool
    {
        return $value == '00' || $value == '01';
    }
}