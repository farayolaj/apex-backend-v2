<?php
namespace App\Support\Query;

final class SelectSupport
{
    public static function normalizeSelect(array|string $select): string
    {
        if (is_array($select)) {
            $select = implode(',', array_filter(array_map('trim', $select), static fn($s) => $s !== ''));
        }
        return trim($select);
    }

    public static function ensureIdInSelect(string $select): string
    {
        if ($select === '') return 'a.id';
        if (preg_match('/\ba\.\*\b/i', $select)) return $select;
        if (preg_match('/\ba\.id\b/i', $select)) return $select;
        return 'a.id,' . $select;
    }

    public static function normalizeInclude(array $include): array
    {
        $include = array_values(array_unique(array_filter($include, static fn($s) => $s !== '')));
        sort($include);
        return $include;
    }
}
