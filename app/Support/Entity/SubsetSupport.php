<?php

namespace App\Support\Entity;

/**
 * Label-driven payload builder.
 * - Reads static $labelArray (allowed fields) and optional $typeArray (light casting).
 * - Excludes keys like 'id' by default.
 * - Can run in strict mode
 */
final class SubsetSupport
{
    /**
     * @param string $modelClass FQCN with static $labelArray (and optional $typeArray)
     * @param array $input incoming payload
     * @param array $exclude keys to drop (e.g., ['id'])
     * @param bool $strict if true, throw on unknown keys; if false, ignore unknown
     * @param array|null $unknownOut (by-ref) unknown keys if any (for logging)
     * @return array
     */
    public static function fromModelLabel(
        string $modelClass,
        array $input,
        array $exclude = ['id'],
        bool $strict = false,
        ?array &$unknownOut = null
    ): array {
        if (!class_exists($modelClass) || !property_exists($modelClass, 'labelArray')) {
            return [];
        }

        $allowed = array_values(array_diff(array_keys($modelClass::$labelArray), $exclude));
        $allowedFlip = array_flip($allowed);

        $unknown = array_diff(array_keys($input), $allowed);
        if ($unknownOut !== null) $unknownOut = array_values($unknown);

        if ($strict && !empty($unknown)) {
            throw new \InvalidArgumentException('Unknown fields: ' . implode(', ', $unknown));
        }

        $out = [];
        foreach ($allowed as $k) {
            if (array_key_exists($k, $input)) {
                $out[$k] = $input[$k];
            }
        }

        if (property_exists($modelClass, 'typeArray')) {
            $out = self::castTypes($out, $modelClass::$typeArray);
        }
        return $out;
    }

    private static function castTypes(array $data, array $typeMap): array
    {
        foreach ($data as $k => $v) {
            $t = strtolower((string)($typeMap[$k] ?? ''));
            if ($t === 'int' || $t === 'integer') {
                $data[$k] = (int) $v;
            } elseif ($t === 'tinyint') {
                $data[$k] = self::toTinyInt($v);
            } elseif ($t === 'varchar' || $t === 'text') {
                $data[$k] = ($v === null) ? '' : trim((string) $v);
            }
        }
        return $data;
    }

    private static function toTinyInt(mixed $v): int
    {
        if (is_bool($v)) return $v ? 1 : 0;
        $s = is_string($v) ? strtolower(trim($v)) : $v;
        if (in_array($s, ['true','yes','on','1'], true))  return 1;
        if (in_array($s, ['false','no','off','0'], true)) return 0;
        return (int)$v ? 1 : 0;
    }
}