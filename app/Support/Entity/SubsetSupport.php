<?php

namespace App\Support\Entity;

/**
 * Label-driven payload builder (now returns BOTH persistable and extra inputs).
 * - Persistable: keys defined in static $labelArray (minus $exclude).
 * - Extra: keys present in input but not in $labelArray (kept for hooks/validation).
 * - Optional light casting using static $typeArray.
 */
final class SubsetSupport
{
    /**
     * Partition input into [persistable, extra].
     *
     * @param string $modelClass FQCN with static $labelArray (and optional $typeArray)
     * @param array  $input      raw payload
     * @param array  $exclude    keys to drop from persistable (e.g., ['id'])
     * @return array{0: array, 1: array} [$persist, $extra]
     */
    public static function partitionByModelLabel(
        string $modelClass,
        array $input,
        array $exclude = ['id']
    ): array {
        if (!class_exists($modelClass) || !property_exists($modelClass, 'labelArray')) {
            return [[], $input]; // nothing known; everything considered extra
        }

        $allowed = array_values(array_diff(array_keys($modelClass::$labelArray), $exclude));

        $persist = [];
        foreach ($allowed as $k) {
            if (array_key_exists($k, $input)) {
                $persist[$k] = $input[$k];
            }
        }

        // everything else is extra (unknown to labelArray)
        $extra = array_diff_key($input, array_flip($allowed));

        // light casting for persisted part only (optional can be skipped or removed entirely)
        if (property_exists($modelClass, 'typeArray')) {
            $persist = self::castTypes($persist, $modelClass::$typeArray);
        }

        return [$persist, $extra];
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
                $data[$k] = ($v === null) ? '' : trim((string)$v);
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