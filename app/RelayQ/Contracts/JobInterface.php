<?php

namespace Alatise\RelayQ\Contracts;

interface JobInterface
{
    public function handle(): void;
}