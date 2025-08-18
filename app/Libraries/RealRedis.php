<?php

namespace App\Libraries;

use Config\Redis as RedisConfig;
use Config\Services;

class RealRedis
{

    protected \Redis       $client;

    protected RedisConfig  $config;

    public function __construct(RedisConfig $config)
    {
        $this->config = $config;

        if (! extension_loaded('redis')) {
            Services::logger()->error('Redis extension not loaded');
            return;
        }

        $this->client = new \Redis();

        try {
            $this->client->connect(
                $config->host,
                is_file($config->host) ? 0 : $config->port,
                $config->timeout
            ) or Services::logger()->error('Redis: connect failed');

            if ($config->password !== null) {
                $this->client->auth($config->password)
                or Services::logger()->error('Redis: auth failed');
            }

            if ($config->database > 0) {
                $this->client->select($config->database)
                or Services::logger()->error('Redis: select DB failed');
            }
        }
        catch (\RedisException $e) {
            Services::logger()->error("Redis exception: {$e->getMessage()}");
        }
    }

    public function __call(string $method, array $args)
    {
        return $this->client->{$method}(...array_values($args));
    }

    public function __destruct()
    {
        if (isset($this->client)) {
            $this->client->close();
        }
    }
}
