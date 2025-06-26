<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use Config\Redis as RedisConfig;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    public static function redis(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('redis');
        }

        $config = config(RedisConfig::class);

        $config->host     = env('REDIS_HOST')     ?: $config->host;
        $config->password = env('REDIS_PASSWORD') ?: $config->password;
        $config->port     = (int) (env('REDIS_PORT')   ?: $config->port);
        $config->timeout  = (float)(env('REDIS_TIMEOUT')?: $config->timeout);
        $config->database = (int) (env('REDIS_DB')     ?: $config->database);

        return new \App\Libraries\RealRedis($config);
    }
}
