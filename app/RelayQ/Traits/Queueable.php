<?php

namespace Alatise\RelayQ\Traits;

trait Queueable
{
    protected ?string $queue = null;
    protected ?int $delaySeconds = null;
    protected ?int $maxAttempts = null;
    protected int|array|null $backoff = null;
    protected ?string $uniqueKey = null;
    protected ?int $uniqueTtl = null;    // seconds; if >0 -> bucketed key

    public function onQueue(string $name): static
    {
        $this->queue = $name;
        return $this;
    }

    public function delay(int $seconds): static
    {
        $this->delaySeconds = $seconds;
        return $this;
    }

    public function maxAttempts(int $n): static
    {
        $this->maxAttempts = $n;
        return $this;
    }

    /** @param int|int[] $seconds */
    public function backoff(array|int $seconds): static
    {
        $this->backoff = $seconds;
        return $this;
    }

    public function unique(string $key, int $ttlSeconds = 0): static
    {
        $this->uniqueKey = $key;
        $this->uniqueTtl = $ttlSeconds;
        return $this;
    }

    public function _queueName(): ?string
    {
        return $this->queue;
    }

    public function _delay(): ?int
    {
        return $this->delaySeconds;
    }

    public function _maxAttempts(): ?int
    {
        return $this->maxAttempts;
    }

    public function _backoff(): int|array|null
    {
        return $this->backoff;
    }

    public function _unique(): ?array
    {
        return $this->uniqueKey ? [$this->uniqueKey, $this->uniqueTtl] : null;
    }
}