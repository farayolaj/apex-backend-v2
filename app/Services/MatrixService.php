<?php

namespace App\Services;

use Illuminate\Cache\CacheManager;
use Illuminate\Container\Container;
use Illuminate\Redis\RedisManager;
use Illuminate\Support\Facades\Facade;
use MatrixSdk\Exceptions\MatrixRequestException;
use MatrixSdk\MatrixClient;

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

        $apiUrl = env('MATRIX_API_URL');
        $accessToken = env('MATRIX_ACCESS_TOKEN');
        $this->client = new MatrixClient($apiUrl, $accessToken);
    }

    public function createCourseRoom(string $courseCode, string $courseTitle)
    {
        return $this->createRoom(
            $courseCode,
            $courseCode . ': ' . $courseTitle,
            'Course room for ' . $courseTitle
        );
    }

    public function createRoom(string $alias, string $roomName, string $roomTopic = '')
    {
        try {
            $room = $this->client->createRoom($alias);
            $room->setRoomName($roomName);
            $room->setRoomTopic($roomTopic);
            $room->setInviteOnly(true);

            return $room->getRoomId();
        } catch (\Exception $e) {
            log_message('error', 'Matrix room creation failed: ' . $e->getMessage(), $e->getTrace());
            return null;
        }
    }

    public function createUser(string $username, string $displayName, ?string $email = null)
    {
        try {
            $data = [
                'displayname' => $displayName
            ];

            if ($email) {
                $data['threepids'] = [
                    [
                        'medium' => 'email',
                        'address' => $email
                    ]
                ];
            }

            $userId = self::getUserId($username);
            $this->client->api()->adminSetUser($userId, $data);
            return true;
        } catch (\Exception $e) {
            log_message(
                'error',
                'Matrix user creation failed: ' . $e->getMessage() . ' for username (user_id): ' . $username . ' (' . self::getUserId($username) . ')',
                $e->getTrace()
            );
            return false;
        }
    }

    public function addUsersToRoom(string $roomId, array $userIds)
    {
        try {
            foreach ($userIds as $userId) {
                try {
                    $this->client->api()->inviteUser($roomId, $userId);
                } catch (MatrixRequestException $e) {
                    // Ignore if already in room by checking for 403 error code
                    if ($e->getHttpCode() === 403) {
                        continue;
                    }
                    throw $e;
                }
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'Matrix invite failed: ' . $e->getMessage(), $e->getTrace());
            return false;
        }
    }

    public function addUserToRoom(string $roomId, string $matrixId)
    {
        return $this->addUsersToRoom($roomId, [$matrixId]);
    }

    public static function getUserId(string $username): string
    {
        $username = str_replace('/', '.', strtolower(trim($username)));
        $username = str_replace(' ', '.', $username);
        if (is_numeric($username)) {
            $username = 'i' . $username;
        }
        return '@' . $username . ':' . env('MATRIX_HOMESERVER');
    }
}
