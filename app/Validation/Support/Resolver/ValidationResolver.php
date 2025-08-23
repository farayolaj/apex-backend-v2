<?php
namespace App\Validation\Support\Resolver;

use App\Exceptions\ForbiddenException;
use App\Exceptions\ValidationFailedException;
use App\Validation\Support\Contracts\RulesProvider;

final class ValidationResolver
{
    public static function run(string $entity, string $action, array $data, array $ctx = []): void
    {
        $class = self::classFor($entity, $action);
        if (!class_exists($class) || !is_subclass_of($class, RulesProvider::class)) {
            return;
        }

        // authorize() — optional, must allow before validation can run
        if (method_exists($class, 'authorize')) {
            $ok = (bool) $class::authorize($data, $ctx);
            if (!$ok) {
                $msg = method_exists($class, 'denyMessage') ? (string) $class::denyMessage() : 'Unauthorized to perform action';
                throw new ForbiddenException($msg);
            }
        }

        // precheck() — optional, can query DB & throw with a custom message
        if (method_exists($class, 'precheck')) {
            $class::precheck($data);
        }

        $rules    = $class::rules();
        $messages = $class::messages();

        if (!empty($rules)) {
            $validation = service('validation');
            $validation->setRules($rules, $messages);
            if (!$validation->run($data)) {
                $errors = $validation->getErrors();
                throw new ValidationFailedException(
                    reset($errors) ?: 'Validation failed'
                );
            }
        }

    }

    public static function classFor(string $entity, string $action): string
    {
        $e = studly($entity);
        $a = studly($action);
        return "App\\Validation\\Entities\\{$e}\\{$a}Rules";
    }
}