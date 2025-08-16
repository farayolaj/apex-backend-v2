<?php
namespace Config;

use CodeIgniter\Config\BaseConfig;

class Redis extends BaseConfig
{
    /**
     * @var string Hostname or UNIX socket path
     */
    public string $host     = '127.0.0.1';

    /**
     * @var string|null Password (optional)
     */
    public ?string $password = null;

    /**
     * @var int TCP port (ignored if host is a socket)
     */
    public int    $port     = 6379;

    /**
     * @var float Connection timeout (seconds)
     */
    public float  $timeout  = 0.0;

    /**
     * @var int Database index
     */
    public int    $database = 0;
}
