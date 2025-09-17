<?php
namespace App\Support\Cache;

use CodeIgniter\Cache\CacheInterface;

final class ShowCacheSupport
{
    public static function cache(): CacheInterface
    {
        /**
         * @var CacheInterface
         */
        return service('cache');
    }

    public static function verKeyEntity(string $ns): string
    {
        return Key::make('ver','ent',$ns);
    }

    public static function verKeyId(string $ns, int $id): string
    {
        return Key::make('ver','ent',$ns,'id',(string)$id);
    }

    public static function getVersion(CacheInterface $cache, string $scopeKey): int
    {
        $v = $cache->get($scopeKey);
        if (is_int($v) && $v > 0) return $v;
        $cache->save($scopeKey, 1, 0);
        return 1;
    }

    public static function bumpVersion(CacheInterface $cache, string $scopeKey): void
    {
        $inc = $cache->increment($scopeKey);
        if ($inc !== false) return;
        $cache->save($scopeKey, (int)time(), 0);
    }

    /**
     * Simple, fast, PSR-16-safe key.
     * If $selectTag is provided, no hashing is done at all.
     * Otherwise, use crc32->base36 (very cheap) for select signature.
     */
    public static function buildShowKey(
        string $prefix,
        string $ns,
        int $id,
        array $include,
        string $selectStr,
        bool $escape,
        string $extra = '',
        ?string $selectTag = null
    ): string {
        $cache  = self::cache();

        $entVer = self::getVersion($cache, self::verKeyEntity($ns));
        $idVer  = self::getVersion($cache, self::verKeyId($ns, $id));
        $incStr = $include ? implode('-', array_map([Key::class,'clean'], $include)) : 'none';

        // select signature: prefer caller-provided tag; else crc32 base36
        if ($selectTag !== null && $selectTag !== '') {
            $selSig = Key::clean($selectTag);
        } else {
            $crc   = sprintf('%u', crc32($selectStr . '|' . ($escape ? '1' : '0')));
            $selSig = base_convert($crc, 10, 36); // e.g. "k1z8d0"
        }

        $parts = [
            $prefix, 'show', $ns,
            'ev'.(string)$entVer, 'iv'.(string)$idVer, (string)$id,
            'inc', $incStr,
            'sel', $selSig,
        ];
        if ($extra !== '') {
            $parts[] = 'x';
            $parts[] = Key::clean($extra);
        }

        return Key::make(...$parts);
    }

    public static function buildCacheKey(
        string $ns,
        ?int $id = null,
        ?array $include = [],
        ?string $selectTag = null
    ): string {
        $cache  = self::cache();
        $id = $id ?? 11;
        $entVer = self::getVersion($cache, self::verKeyEntity($ns));
        $idVer  = self::getVersion($cache, self::verKeyId($ns, $id));
        $incStr = $include ? implode('-', array_map([Key::class,'clean'], $include)) : 'none';

        // select signature: prefer caller-provided tag; else crc32 base36
        if ($selectTag !== null && $selectTag !== '') {
            $selSig = Key::clean($selectTag);
        } else {
            $selectStr = 'str';
            $crc   = sprintf('%u', crc32($selectStr . '|' . '0'));
            $selSig = base_convert($crc, 10, 36);
        }

        $parts = [
            'cache', 'show', $ns,
            'ev'.(string)$entVer, 'iv'.(string)$idVer, (string)$id,
            'inc', $incStr,
            'sel', $selSig,
        ];

        return Key::make(...$parts);
    }

    public static function invalidateById(string $ns, int $id): void
    {
        $cache = self::cache();
        self::bumpVersion($cache, self::verKeyId($ns, $id));
    }

    public static function invalidateByKey(string $key, ?int $id = null): void
    {
        $cache = self::cache();
        self::bumpVersion($cache, self::verKeyId($key, $id ?? 11));
    }

    public static function invalidateAll(string $ns): void
    {
        $cache = self::cache();
        self::bumpVersion($cache, self::verKeyEntity($ns));
    }

    /**
     * "Laravel-style" remember(): return cached value if present,
     * otherwise compute via $producer(), cache it, and return it.
     *
     * @template T
     * @param string $key
     * @param int $ttl
     * @param callable():T $producer
     * @param array|null $parts Optional extra parts for the key
     * @return T
     */
    public static function remember(string $key, int $ttl, callable $producer, ?int $id=null, ?array $parts=[]){
        $cache = self::cache();
        $ns = self::buildCacheKey($key, $id, $parts);

        $has = $cache->get($ns);
        if ($has !== null) {
            return $has;
        }

        $value = $producer();
        $cache->save($ns, $value, $ttl);

        return $value;
    }

}
