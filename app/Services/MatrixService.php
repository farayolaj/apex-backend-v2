<?php

namespace App\Services;

use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Facades\Facade;
use Vocphone\LaravelMatrixSdk\MatrixClient;

class MatrixService
{
    protected $client;

    public function __construct()
    {
        $app = new Container();
        $app['config'] = [
            'cache.default' => 'redis',
            'cache.stores.redis' => [
                'driver' => 'redis',
                'connection' => 'default'
            ],
            'cache.prefix' => 'illuminate_non_laravel',
            'database.redis' => [
                'cluster' => false,
                'default' => [
                    'host' => env('redis.host'),
                    'port' => env('redis.port'),
                    'database' => env('redis.database'),
                ],
            ]
        ];

        $app['redis'] = new RedisManager($app, 'predis', $app['config']['database.redis']);
        $app['cache'] = new CacheManager($app);

        Facade::setFacadeApplication($app);

        $homeserver = env('MATRIX_HOMESERVER');
        $accessToken = env('MATRIX_ACCESS_TOKEN');
        $this->client = new MatrixClient($homeserver, $accessToken);
    }

    public function createCourseRoom(string $courseCode, string $courseTitle)
    {
        try {
            $room = $this->client->createRoom($courseCode);
            $room->setRoomName($courseCode . ': ' . $courseTitle);
            $room->setRoomTopic('Course room for ' . $courseTitle);
            $room->setInviteOnly(true);

            return $room->getRoomId();
        } catch (\Exception $e) {
            log_message('error', 'Matrix room creation failed: ' . $e->getMessage(), $e->getTrace());
            return null;
        }
    }

    public function createUser(string $username, string $displayName)
    {
        try {
            $response = $this->client->api()->adminRegisterUser($username, bin2hex(random_bytes(8)), $displayName);
            return !!$response['user_id'];
        } catch (\Exception $e) {
            log_message('error', 'Matrix user creation failed: ' . $e->getMessage(), $e->getTrace());
            return false;
        }
    }

    public function addUsersToRoom(string $roomId, array $usernames)
    {
        try {
            $userIds = array_map(
                fn($username) => self::getUserId($username),
                $usernames
            );

            foreach ($userIds as $userId) {
                $this->client->api()->inviteUser($roomId, $userId);
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'Matrix invite failed: ' . $e->getMessage(), $e->getTrace());
            return false;
        }
    }

    public function addUserToRoom(string $roomId, string $username)
    {
        return $this->addUsersToRoom($roomId, [$username]);
    }

    public static function getUserId(string $username): string
    {
        if (is_numeric($username)) {
            $username = 'i' . $username;
        }
        return '@' . $username . ':' . parse_url(env('MATRIX_HOMESERVER'), PHP_URL_HOST);
    }
}
