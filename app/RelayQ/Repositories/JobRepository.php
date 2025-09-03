<?php

namespace Alatise\RelayQ\Repositories;

use CodeIgniter\Database\BaseConnection;
use DateTimeImmutable;

class JobRepository
{
    public function __construct(private readonly BaseConnection $db)
    {
    }

    public function insert(array $row): string
    {
        $this->db->table('relayq_jobs')->insert($row);
        return $row['id'];
    }

    public function getForRun(string $id): ?array
    {
        return $this->db->table('relayq_jobs')
            ->where('id', $id)
            ->get()
            ->getRowArray();
    }

    public function tryReserve(string $id, string $now): bool
    {
        $builder = $this->db->table('relayq_jobs');
        $builder->set('reserved_at', $now)
            ->set('updated_at', $now)
            ->where('id', $id)
            ->where('reserved_at IS NULL', null, false)
            ->where('available_at <=', $now)
            ->update();
        return $this->db->affectedRows() > 0;
    }

    public function markHandoff(string $id, string $now): void
    {
        $this->db->table('relayq_jobs')
            ->where('id', $id)
            ->update(['last_handoff_at' => $now, 'updated_at' => $now]);
    }

    public function markDone(string $id, string $now): void
    {
        $this->db->table('relayq_jobs')
            ->where('id', $id)
            ->delete();
    }

    public function markFailed(string $id, string $class, array $payload, string $error, string $now): void
    {
        $this->db->transStart();
        $this->db->table('relayq_failed')->insert([
            'job_id' => $id,
            'job_class' => $class,
            'payload' => json_encode($payload),
            'error' => $error,
            'failed_at' => $now
        ]);
        $this->db->table('relayq_jobs')->where('id', $id)->delete();
        $this->db->transComplete();
    }

    /**
     * @throws \Exception
     */
    public function bumpAttemptsAndRelease(string $id, int $attempts, int $nextDelaySeconds, string $now): void
    {
        $available = (new DateTimeImmutable($now))
            ->modify("+{$nextDelaySeconds} seconds")
            ->format('Y-m-d H:i:s');
        $this->db->table('relayq_jobs')
            ->where('id', $id)
            ->update([
                'attempts' => $attempts + 1,
                'reserved_at' => null,
                'available_at' => $available,
                'updated_at' => $now
            ]);
    }

    // Uniqueness helpers
    public function findIdByUnique(string $key): ?string
    {
        $row = $this->db->table('relayq_jobs')
            ->select('id')
            ->where('unique_key', $key)
            ->get()->getRowArray();
        return $row['id'] ?? null;
    }

    public function isDuplicateKey(\Throwable $e): bool
    {
        $m = $e->getMessage();
        return str_contains($m, '1062') ||
            str_contains($m, 'Duplicate') ||
            str_contains($m, '23505');
    }

    // Sweeper helpers
    public function findStalePending(string $now, int $staleSeconds, int $limit, ?string $queue = null): array
    {
        $threshold = date('Y-m-d H:i:s', strtotime($now . " -{$staleSeconds} seconds"));
        $b = $this->db->table('relayq_jobs')->select('id')
            ->where('available_at <=', $now)
            ->where('reserved_at IS NULL', null, false)
            ->groupStart()
            ->where('last_handoff_at IS NULL', null, false)
            ->orWhere('last_handoff_at <', $threshold)
            ->groupEnd();
        if ($queue) $b->where('queue', $queue);
        return array_map(fn($r) => $r->id,
            $b->orderBy('available_at', 'ASC')
            ->limit($limit)->get()->getResult()
        );
    }

    public function findTimedOutReserved(string $now, int $vtSeconds, int $limit, ?string $queue = null): array
    {
        $threshold = date('Y-m-d H:i:s', strtotime($now . " -{$vtSeconds} seconds"));
        $b = $this->db->table('relayq_jobs')
            ->select('id')
            ->where('reserved_at <', $threshold);
        if ($queue) $b->where('queue', $queue);
        return array_map(fn($r) => $r->id,
            $b->orderBy('reserved_at', 'ASC')
                ->limit($limit)->get()->getResult()
        );
    }

    public function release(string $id, string $now): void
    {
        $this->db->table('relayq_jobs')
            ->where('id', $id)->update([
                'reserved_at' => null,
                'available_at' => $now,
                'updated_at' => $now
        ]);
    }
}