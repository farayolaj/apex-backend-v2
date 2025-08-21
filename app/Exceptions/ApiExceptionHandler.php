<?php
namespace App\Exceptions;

use CodeIgniter\Debug\BaseExceptionHandler;
use CodeIgniter\Debug\ExceptionHandlerInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use JetBrains\PhpStorm\NoReturn;
use Throwable;

class ApiExceptionHandler extends BaseExceptionHandler implements ExceptionHandlerInterface
{
    #[NoReturn] public function handle(
        Throwable $exception,
        RequestInterface $request,
        ResponseInterface $response,
        int $statusCode,
        int $exitCode,
    ): void {

        $message = (ENVIRONMENT === 'production')
            ? $this->friendly($exception, $statusCode)
            : ($exception->getMessage() ?: 'An error occurred');

        $response->setStatusCode($statusCode)
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