<?php

namespace App\Validation;

class CustomRules
{
    /**
     * Validates that the value is a valid datetime string (Y-m-d H:i:s)
     * Accepts ISO 8601 and common formats.
     *
     * @param string $value
     * @param string|null &$error
     * @return bool
     */
    public function valid_datetime($value, string $params, array $data, ?string &$error = null): bool
    {
        $params = explode(',', $params);
        $field = $params[0];

        // Accepts formats like 'Y-m-d H:i:s', 'Y-m-d\TH:i:s', etc.
        $formats = [
            'Y-m-d H:i:s',
            'Y-m-d\TH:i:s',
            'Y-m-d',
            'Y-m-d H:i',
            'Y-m-d\TH:i',
            \DateTime::ATOM,
        ];
        foreach ($formats as $format) {
            $dt = \DateTime::createFromFormat($format, $value);
            if ($dt && $dt->format($format) === $value) {
                return true;
            }
        }
        // Fallback: try strtotime
        if (strtotime($value) !== false) {
            return true;
        }
        $error = "The {$field} field must contain a valid datetime.";
        return false;
    }
}
