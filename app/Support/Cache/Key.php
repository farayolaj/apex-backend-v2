<?php
namespace App\Support\Cache;

/**
 * Ultra-light key composer (PSR-16 safe).
 * - Uses only "." as separator.
 * - Replaces reserved chars via strtr (no regex).
 * - Skips empty parts.
 */
final class Key
{
    private const MAP = [
        '{'=>'-','}'=>'-','('=>'-',')'=>'-','['=>'-',']'=>'-',
        '/'=>'-','\\'=>'-','@'=>'-',':'=>'-',
        ' '=>'-',"\t"=>'-',"\n"=>'-',"\r"=>'-',
    ];

    public static function make(string ...$parts): string
    {
        $out = [];
        foreach ($parts as $p) {
            $p = self::clean($p);
            if ($p !== '') $out[] = $p;
        }
        return implode('.', $out);
    }

    public static function clean(string $s): string
    {
        $s = trim($s);
        return $s === '' ? '' : strtr($s, self::MAP);
    }
}
