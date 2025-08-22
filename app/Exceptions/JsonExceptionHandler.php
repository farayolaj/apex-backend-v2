<?php
namespace App\Exceptions;

use CodeIgniter\Debug\BaseExceptionHandler;
use CodeIgniter\HTTP\RequestInterface;
use JetBrains\PhpStorm\NoReturn;
use Throwable;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Debug\ExceptionHandlerInterface;

class JsonExceptionHandler extends BaseExceptionHandler implements ExceptionHandlerInterface
{
    #[NoReturn] public function handle(
        Throwable $exception,
        RequestInterface $request,
        ResponseInterface $response,
        int $statusCode,
        int $exitCode,
    ): void
    {
        $message = $this->friendly($exception, $statusCode) ?: 'An error occurred';
        $response->setStatusCode($statusCode ?: 500)
            ->setJSON(['status' => false, 'message' => $message])
            ->send();

        exit($exitCode);
    }

    private function friendly(Throwable $e, int $status): string
    {
        if ($status === 422) {
            return $e->getMessage() ?: 'Validation failed';
        }
        return match ($status) {
            400 => 'Bad request',
            401 => 'Unauthenticated',
            403 => 'Forbidden',
            404 => 'Not found',
            default => 'Server error',
        };
    }
}
