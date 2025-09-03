<?php

namespace Alatise\RelayQ\Commands;

use Alatise\RelayQ\Support\CLISupport;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\I18n\Time;

class Run extends BaseCommand
{
    protected $group = 'RelayQ';
    protected $name = 'relayq:run';
    protected $description = 'Run a single RelayQ job by ID.';
    protected $usage = 'relayq:run --id <uuid>';

    /**
     * @throws \Exception
     */
    public function run(array $params): void
    {
        $id = CLISupport::option('id');
        $runAt = CLISupport::option('run-at');
        if (!$id) {
            CLI::error('Missing --id');
            return;
        }

        if ($runAt) {
            $cfg = config(\Alatise\RelayQ\Config\RelayQ::class);
            $tz  = new \DateTimeZone($cfg->clock);

            $dueTs = (new \DateTimeImmutable($runAt, $tz))->getTimestamp();
            $nowTs = (new \DateTimeImmutable('now', $tz))->getTimestamp();

            $diff  = $dueTs - $nowTs;
            if ($diff > 0) {
                CLI::write("RelayQ sleeping {$diff}s until run-at {$runAt}");
                sleep($diff);
            }
        }

        service('relayq')->runOne($id);
        CLI::write("RelayQ done: {$id}");
    }

}