<?php

namespace Alatise\RelayQ\Services;

use Alatise\RelayQ\Config\RelayQ as RelayQConfig;
use Alatise\RelayQ\Contracts\JobInterface;
use Alatise\RelayQ\Repositories\JobRepository;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;
use Random\RandomException;

class RelayQ
{
    public function __construct(
        private readonly RelayQConfig          $config,
        private readonly JobRepository         $repo,
        private readonly ?BackgroundDispatcher $bg = null,
        private readonly ?HttpDispatcher       $http = null,
        private readonly ?RedisAdapter $redis = null
    )
    {
    }

    /**
     * @throws \Throwable
     */
    public function dispatch(JobInterface $job): string
    {
        $now = Time::now($this->config->clock)->toDateTimeString();
        $class = $job::class;

        $payload = method_exists($job, 'toArray') ? $job->toArray() : get_object_vars($job);
        $queue = method_exists($job, '_queueName') && $job->_queueName() ? $job->_queueName() : $this->config->defaultQueue;
        $delay = method_exists($job, '_delay') ? (int)($job->_delay() ?? 0) : 0;
        $availableAt = Time::now($this->config->clock)->addSeconds($delay)->toDateTimeString();

        $maxAttempts = method_exists($job, '_maxAttempts') && $job->_maxAttempts() ? $job->_maxAttempts() : $this->config->maxAttempts;
        $backoff = method_exists($job, '_backoff') ? ($job->_backoff() ?: $this->config->backoff) : $this->config->backoff;

        // Uniqueness (DB-enforced; bucket if TTL > 0)
        $uniqueKey = null;
        $uniqueUntil = null;
        if (method_exists($job, '_unique') && ($u = $job->_unique())) {
            [$baseKey, $ttl] = $u;
            $ttl = (int)$ttl;
            if ($baseKey) {
                $uniqueKey = $ttl > 0 ? ($baseKey . '|' . intdiv(time(), $ttl)) : $baseKey;
                if ($ttl > 0) $uniqueUntil = Time::now($this->config->clock)->addSeconds($ttl)->toDateTimeString();

                // Optional fast guard in Redis
                if ($this->config->redisEnabled && $this->config->redisUseUniqueness && $this->redis?->enabled()) {
                    $ok = $this->redis->setnx("uniq:{$uniqueKey}", '1', $ttl > 0 ? $ttl : 60);
                    // If it already exists, just return the existing DB id (or early return)
                    if (!$ok) {
                        $existingId = $this->repo->findIdByUnique($uniqueKey);
                        if ($existingId) return $existingId;
                    }
                }
            }
        }

        // Create a fresh ID (DB will enforce uniqueness on unique_key)
        $id = self::uuidV4();

        // Insert (race-proof: catch duplicate unique_key)
        try {
            $this->repo->insert([
                'id' => $id,
                'queue' => $queue,
                'job_class' => $class,
                'payload' => json_encode(['payload' => $payload, 'backoff' => $backoff]),
                'attempts' => 0,
                'max_attempts' => $maxAttempts,
                'available_at' => $availableAt,
                'reserved_at' => null,
                'last_error' => null,
                'unique_key' => $uniqueKey,
                'unique_until' => $uniqueUntil,
                'last_handoff_at' => null,
                'created_at' => $now,
                'updated_at' => $now
            ]);
        } catch (\Throwable $e) {
            if ($uniqueKey && $this->repo->isDuplicateKey($e)) {
                $existing = $this->repo->findIdByUnique($uniqueKey);
                return $existing ?: $id;
            }
            throw $e;
        }

        // (Optional) publish to Redis ready list (future worker)
        if ($this->config->redisEnabled && $this->config->redisPublishReadyList && $this->redis?->enabled()) {
            $this->redis->lpushReady($queue, $id);
        }

        // Single-shot handoff
        $this->handoff($id, $now, $availableAt);
        return $id;
    }

    /**
     * @throws \Exception
     */
    public function runOne(string $id): void
    {
        $now = Time::now($this->config->clock)->toDateTimeString();
        CLI::write("RelayQ Starting Job: {$id} at {$now}");
        $row = $this->repo->getForRun($id);
        if (!$row) return;

        if (!$this->repo->tryReserve($id, $now)) return;
        $data = json_decode($row['payload'], true) ?: [];
        $payload = $data['payload'] ?? [];
        $backoff = $data['backoff'] ?? $this->config->backoff;
        $attempt = (int)$row['attempts'];
        $max = (int)$row['max_attempts'];

        try {
            $job = $this->rehydrate($row['job_class'], $payload);
            if (!($job instanceof JobInterface)) {
                throw new \RuntimeException('Job does not implement JobInterface: ' . $row['job_class']);
            }
            $job->handle();
            $this->repo->markDone($id, $now);
        } catch (\Throwable $e) {
            $nextDelay = $this->nextBackoff($backoff, $attempt);
            if ($attempt + 1 >= $max) {
                $this->repo->markFailed($id, $row['job_class'], $payload, $e->getMessage(), $now);
                return;
            }
            $this->repo->bumpAttemptsAndRelease($id, $attempt, $nextDelay, $now);
        }
    }

    /**
     * @throws \Exception
     */
    public function rehandOff(string $id): void
    {
        $now = Time::now($this->config->clock)->toDateTimeString();
        $this->repo->markHandoff($id, $now);
        if ($this->config->driver === 'background' && $this->bg && $this->bg->spawn($id)) return;
        if ($this->config->driver === 'http' && $this->http) $this->http->post($id);
    }

    public function repo(): JobRepository
    {
        return $this->repo;
    }

    public function config(): RelayQConfig
    {
        return $this->config;
    }

    /**
     * @throws \Exception
     */
    private function handoff(string $id, string $now, ?string $runAt = null): void
    {
        $this->repo->markHandoff($id, $now);
        if ($this->config->driver === 'background' && $this->bg && $this->bg->dispatch($id, $runAt)) return;
        if ($this->config->driver === 'http' && $this->http) $this->http->dispatch($id, $runAt);
    }

    /**
     * @throws \ReflectionException
     */
    private function rehydrate(string $class, array $payload): object
    {
        $ref = new \ReflectionClass($class);
        $ctor = $ref->getConstructor();
        if ($ctor && $ctor->getNumberOfParameters() > 0) {
            $args = [];
            foreach ($ctor->getParameters() as $p) $args[] = $payload[$p->getName()] ?? null;
            return $ref->newInstanceArgs($args);
        }
        $obj = $ref->newInstance();
        foreach ($payload as $k => $v) if (property_exists($obj, $k)) $obj->$k = $v;
        return $obj;
    }

    private function nextBackoff($backoff, int $attempt): int
    {
        if (is_array($backoff)) return (int)($backoff[$attempt] ?? end($backoff));
        return (int)$backoff;
    }

    /**
     * @throws RandomException
     */
    private static function uuidV4(): string
    {
        $d = random_bytes(16);
        $d[6] = chr((ord($d[6]) & 0x0f) | 0x40);
        $d[8] = chr((ord($d[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($d), 4));
    }
}