<?php

namespace App\Libraries;

use CodeIgniter\Config\Factories;
use InvalidArgumentException;
use RuntimeException;

class EntityLoader
{
    /**
     * Loads a class dynamically and assigns it to the given object context.
     *
     * @param object $context The object context (e.g., $this).
     * @param string $classname The name of the class to load.
     * @param string|null $namespace The namespace of the class (optional).
     * @return object The instantiated class.
     * @throws InvalidArgumentException If the class name is invalid.
     * @throws RuntimeException If the class does not exist.
     */
    public static function loadClass(object $context, string $classname, string $namespace = null): object
    {
        $modelName = is_null($namespace)
            ? "App\\Entities\\" . ucfirst($classname)
            : $namespace . "\\" . ucfirst($classname);

        if (!class_exists($modelName)) {
            throw new RuntimeException("Class '$modelName' does not exist.");
        }
        $modelName = Factories::entities($modelName);

        $context->$classname = $modelName;
        return $context->$classname;
    }

}