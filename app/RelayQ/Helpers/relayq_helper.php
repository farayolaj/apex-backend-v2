<?php
use Alatise\RelayQ\Contracts\JobInterface;

if (! function_exists('relayQDispatch')) {
    function relayQDispatch(JobInterface $job): string {
        return service('relayq')->dispatch($job);
    }
}

