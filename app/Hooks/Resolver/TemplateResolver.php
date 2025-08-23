<?php

namespace App\Hooks\Resolver;

use App\Hooks\Contracts\Template;

final class TemplateResolver
{
    /** @var array<string, class-string<Template>|null> */
    private static array $cache = [];

    /** @return class-string<Template>|null */
    public static function resolve(string $entity): ?string
    {
        $key = strtolower($entity);
        if (array_key_exists($key, self::$cache)) return self::$cache[$key];

        $fqcn = 'App\\Hooks\\Templates\\' . studly($entity);
        if (class_exists($fqcn) && is_subclass_of($fqcn, Template::class)) {
            return self::$cache[$key] = $fqcn;
        }
        return self::$cache[$key] = null;
    }
}