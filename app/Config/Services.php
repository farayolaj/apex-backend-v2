<?php

namespace Config;

use App\Libraries\Notifications\NotificationManager;
use CodeIgniter\Config\BaseService;

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
    /*
     * public static function example($getShared = true)
     * {
     *     if ($getShared) {
     *         return static::getSharedInstance('example');
     *     }
     *
     *     return new \CodeIgniter\Example();
     * }
     */

    public static function redis($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('redis');
        }

        return new \App\Libraries\RealRedis(new Redis());
    }

    public static function relayq(bool $getShared = true)
    {
        if ($getShared) return static::getSharedInstance('relayq');

        $config = config(\Alatise\RelayQ\Config\RelayQ::class);
        $repo = new \Alatise\RelayQ\Repositories\JobRepository(db_connect());
        $bg = new \Alatise\RelayQ\Services\BackgroundDispatcher($config);
        $http = new \Alatise\RelayQ\Services\HttpDispatcher($config);
        $redis = new \Alatise\RelayQ\Services\RedisAdapter($config); // optional; no-op if disabled

        return new \Alatise\RelayQ\Services\RelayQ($config, $repo, $bg, $http, $redis);
    }

    public static function notificationManager($getShared = true): NotificationManager
    {
        if ($getShared) {
            return static::getSharedInstance('notificationManager');
        }

        return new NotificationManager();
    }

    public static function gDriveStorage($getShared = true): \App\Services\GoogleDriveStorageService
    {
        if ($getShared) {
            return static::getSharedInstance('gDriveStorage');
        }

        return new \App\Services\GoogleDriveStorageService();
    }

    public static function bbbService($getShared = true): \App\Services\BBBService
    {
        if ($getShared) {
            return static::getSharedInstance('bbbService');
        }

        return new \App\Services\BBBService();
    }

    public static function matrixService($getShared = true): \App\Services\MatrixService
    {
        if ($getShared) {
            return static::getSharedInstance('matrixService');
        }
        return new \App\Services\MatrixService();
    }
}
