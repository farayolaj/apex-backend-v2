<?php

namespace Alatise\RelayQ\Commands;

use Alatise\RelayQ\Support\CLISupport;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;

class Sweep extends BaseCommand
{
    protected $group = 'RelayQ';
    protected $name = 'relayq:sweep';
    protected $description = 'One-shot sweeper: re-hand off stale pending and timed-out reserved jobs.';
    protected $usage = 'relayq:sweep [--stale 30] [--vt 60] [--limit 100] [--queue default]';

    /**
     * @throws \Exception
     */
    public function run(array $params): void
    {
        $stale = (int) (CLISupport::option('stale') ?? 30);
        $vt    = (int) (CLISupport::option('vt') ?? 60);
        $limit = (int) (CLISupport::option('limit') ?? 100);
        $queue = CLISupport::option('queue');

        $svc  = service('relayq');
        $jobs = $svc->repo();
        $config = $svc->config();
        $now  = Time::now($config->clock)->toDateTimeString();

        $touched = 0;

        foreach ($jobs->findTimedOutReserved($now, $vt, $limit, $queue) as $id) {
            $jobs->release($id, $now);
            $svc->rehandOff($id);
            if (++$touched >= $limit) break;
        }

        if ($touched < $limit) {
            foreach ($jobs->findStalePending($now, $stale, $limit - $touched, $queue) as $id) {
                $svc->rehandOff($id);
                if (++$touched >= $limit) break;
            }
        }

        CLI::write("RelayQ swept: {$touched} job(s).");
    }
}