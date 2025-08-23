<?php
namespace App\Hooks\Resolver;

/**
 * Ultra-light observer resolver with per-entity static caching.
 * Observer class: App\Hooks\Observers\<StudlyEntity>
 * Optional methods on the observer:
 *   - beforeInsert(array &$data, array $extra): void
 *   - afterInsert(int $id, array &$data, array $extra): void
 *   - handleUploads(array &$data, array $files, array $extra): void
 *   - cleanupUploads(array $data, array $extra): void
 */
final class ObserverResolver
{
    /**
     * @var array<string,object|null>
     */
    private static array $instances = [];

    /** @var array<string,array{
     *   beforeCreate:bool, afterCreate:bool,
     *   beforeUpdate:bool, afterUpdate:bool,
     *   beforeDelete:bool, afterDelete:bool,
     *   handle:bool, cleanup:bool
     * }> */
    private static array $flags = [];

    /**
     * @return array{0: (object|null), 1: array{
     *     before:bool,
     *     after:bool,
     *     handle:bool,
     *     cleanup:bool
     * }}
     */
    public static function resolve(string $entity): array
    {
        $key = strtolower($entity);
        if (!array_key_exists($key, self::$instances)) {
            $fqcn = 'App\\Hooks\\Observers\\' . studly($entity);
            $inst = class_exists($fqcn) ? new $fqcn() : null;
            self::$instances[$key] = $inst;

            self::$flags[$key] = $inst
                ? [
                    'beforeCreate' => \is_callable([$inst, 'beforeCreating']),
                    'afterCreate'  => \is_callable([$inst, 'afterCreated']),
                    'beforeUpdate' => \is_callable([$inst, 'beforeUpdating']),
                    'afterUpdate'  => \is_callable([$inst, 'afterUpdated']),
                    'beforeDelete' => \is_callable([$inst, 'beforeDeleting']),
                    'afterDelete'  => \is_callable([$inst, 'afterDeleted']),
                    'handle'       => \is_callable([$inst, 'handleUploads']),
                    'cleanup'      => \is_callable([$inst, 'cleanupUploads']),
                ]
                : [
                    'beforeCreate'=>false, 'afterCreate'=>false,
                    'beforeUpdate'=>false, 'afterUpdate'=>false,
                    'beforeDelete'=>false, 'afterDelete'=>false,
                    'handle'=>false, 'cleanup'=>false,
                ];
        }

        return [self::$instances[$key], self::$flags[$key]];
    }
}