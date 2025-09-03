<?php

namespace Alatise\RelayQ\Config;

use CodeIgniter\Config\BaseConfig;

class RelayQ extends BaseConfig
{
    public string $clock = 'Africa/Lagos'; // single source of truth for time calc
    public int $maxSpawnWaitSeconds = 60;   // only spawn if delay ≤ (tweak as you like)

    public string $driver = 'background'; // 'background' | 'http'
    public string $defaultQueue = 'default';
    public int $maxAttempts = 3;

    /** @var int|int[] seconds per attempt */
    public int|array $backoff = [30, 120, 600];
    public int $visibilityTimeout = 60; // reclaim stuck jobs after this

    public string $backgroundPrefer = 'proc_open'; // 'exec' | 'proc_open'
    public string $phpBinary = '';
    public string $sparkPath = ROOTPATH . 'spark';

    public string $httpEndpoint = 'v1/web/_relayq/run'; // defaults to base_url('_relayq/run') if empty
    public string $httpToken = ''; // defaults to env('relayq.token') if empty

    public bool $redisEnabled = false;
    public string $redisHost = '127.0.0.1';
    public int $redisPort = 6380;
    public ?string $redisPassword = null;
    public int $redisDatabase = 0;
    public string $redisPrefix = 'relayq:';
    public bool $redisUseUniqueness = true;     // fast SETNX before DB insert
    public bool $redisPublishReadyList = false;  // LPUSH id to ready:<queue> (future workers)

    public bool $useBatchRun = false; // reserved for future: group IDs per spawn
}
