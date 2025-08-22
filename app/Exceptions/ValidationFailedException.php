<?php

namespace App\Exceptions;

use CodeIgniter\Exceptions\HTTPExceptionInterface;
use RuntimeException;

class ValidationFailedException extends RuntimeException implements HTTPExceptionInterface
{
    public function __construct(string $message, int $code = 422, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}