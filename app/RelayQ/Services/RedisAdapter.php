<?php

namespace Alatise\RelayQ\Services;

use Alatise\RelayQ\Config\RelayQ;

class RedisAdapter
{
    private ?\Redis $r = null;
    private bool $available = false;
    private string $prefix;

    public function __construct(private RelayQ $config)
    {
        $this->prefix = rtrim($config->redisPrefix, ':') . ':';
        if (!$config->redisEnabled || !extension_loaded('redis')) return;

        $this->r = new \Redis();
        $ok = @$this->r->connect($config->redisHost, $config->redisPort, 0.2);
        if ($ok && $config->redisPassword) {
            @$this->r->auth($config->redisPassword);
        }
        if ($ok) {
            @$this->r->select($config->redisDatabase);
        }
        $this->available = (bool)$ok;
    }

    public function enabled(): bool
    {
        return $this->available;
    }

    public function setnx(string $key, string $value, int $ttlSeconds): bool
    {
        if (!$this->available) return true;
        $k = $this->prefix . $key;
        $ok = $this->r->set($k, $value, ['nx', 'ex' => max(1, $ttlSeconds)]);
        return $ok === true;
    }

    public function lpushReady(string $queue, string $jobId): void
    {
        if (!$this->available) return;
        $this->r->lPush($this->prefix . "ready:{$queue}", $jobId);
    }
}