<?php

namespace Alatise\RelayQ\Services;

use Alatise\RelayQ\Config\RelayQ;
use Alatise\RelayQ\Contracts\DispatcherInterface;
use Config\Services;

class HttpDispatcher implements DispatcherInterface
{
    public function __construct(private readonly RelayQ $config)
    {
    }

    public function dispatch(string $jobId, ?string $runAt = null): bool
    {
        $endpoint = base_url($this->config->httpEndpoint) ?: base_url('_relayq/run');
        $token = $this->config->httpToken ?: (env('relayq.token') ?? '');
        $client = Services::curlrequest();
        try {
            $client->post($endpoint, [
                'headers' => ['X-RelayQ-Token' => $token],
                'json' => [
                    'id' => $jobId,
                    'run_at' => $runAt
                ],
                'timeout' => 0.3,
                'connect_timeout' => 0.2,
                'http_errors' => false,
            ]);

            return true;
        } catch (\Throwable $e) { /* swallow; job is safe in DB */
            return false;
        }
    }
}