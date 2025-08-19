<?php

namespace App\Libraries;

class ApiResponse
{
    /**
     * Send a success response.
     *
     * @param string $message
     * @param mixed|null $data
     * @param int $code
     * @return mixed
     */
    public static function success(string $message = 'success', mixed $data = null, int $code = 200)
    {
        return self::formatResponse(true, $message, $data, $code);
    }

    /**
     * Send an error response.
     *
     * @param string $message
     * @param mixed|null $data
     * @param int $code
     * @return mixed
     */
    public static function error(string $message = '', mixed $data = null, int $code = 400)
    {
        return self::formatResponse(false, $message, $data, $code);
    }

    /**
     * Format the API response.
     *
     * @param bool $status
     * @param string $message
     * @param null $payload
     * @param int $code
     * @param string|null $statusCodeMessage
     * @return mixed
     */
    private static function formatResponse(bool $status, string $message = '', $payload = null,
                                           int $code=200, ?string $statusCodeMessage = '')
    {
        $defaultPhrases = [
            200 => 'OK',
            404 => 'Not Found',
            500 => 'Internal Server Error',
            403 => 'Forbidden'
        ];

        // Use custom reason or default one
        $reasonPhrase = $statusCodeMessage ?: ($defaultPhrases[$code] ?? 'Unknown Status Code');
        $response = [
            'status' => $status,
            'message' => $message,
            'payload' => $payload,
        ];
        return service('response')->setStatusCode($code, $reasonPhrase)->setJSON($response);
    }

}