<?php

namespace Alatise\RelayQ\Contracts;

interface DispatcherInterface
{
 public function dispatch(string $jobId, ?string $runAt = null): bool;
}