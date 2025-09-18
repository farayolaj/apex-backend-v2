<?php
namespace App\Libraries;

use CodeIgniter\Database\BaseBuilder;

final class DictFilters
{
    /**
     * Apply internal dict to the builder:
     *   ['a.code' => 'eco', 'department_id' => 4, 'status' => ['active','pending'], 'deleted_at' => null]
     */
    public static function apply(BaseBuilder $b, array $map, ?string $defaultAlias = 'a'): void
    {
        if (empty($map)) return;

        foreach ($map as $col => $val) {
            $qualified = (!str_contains($col, '.') && $defaultAlias)
                ? "{$defaultAlias}.{$col}"
                : $col;

            if (is_array($val)) {
                if (!empty($val)) {
                    $b->whereIn($qualified, $val);
                }
                continue;
            }

            if ($val === null) {
                $b->where("{$qualified} IS NULL", null, false);
                continue;
            }

            $b->where($qualified, $val);
        }
    }
}
