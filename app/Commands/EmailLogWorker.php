<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Exception;

class EmailLogWorker extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'Email';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'email:logs';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Flushes buffered log entries from Redis into your email_logs table in batch, auto-cleaning expired keys.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'email:logs [arguments] [options]';

    /**
     * The Command's Arguments
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        $this->processLogs();
    }

    private function processLogs(): void
    {
        $redis = service('redis');
        $batchSize = 100;
        $queueKey = 'email:builder:log_queue';

        while (true) {
            $logs = [];
            $now = time();

            $logEntries = $redis->zRangeByScore($queueKey, '-inf', $now, ['limit' => [0, $batchSize]]);
            if (empty($logEntries)) {
                usleep(500000); // Sleep 0.5s
                continue;
            }

            foreach ($logEntries as $entry) {
                if (!is_string($entry)) continue;

                $logData = json_decode($entry, true);
                if (!$logData) continue;

                $logs[] = $logData;
                $redis->zRem($queueKey, $entry);
            }

            if (!empty($logs)) {
                try {
                    db_connect()->table('email_logs')->insertBatch($logs);
                } catch (Exception $e) {
                    log_message('error', '[LOG_FLUSH_FAILED] ' . $e->getMessage());
                }
            } else {
                usleep(500000); // Sleep 0.5s
            }
        }
    }

    /**
     * NOT IN USE AT THE MOMENT,
     * Cleanup old logs from Redis
     * This method removes logs older than 24 hours (1 day) from the Redis log queue.
     */
    public function cleanupOldLogs()
    {
        $redis = service('redis');
        // remove logs older than 24 hours (1 day)
        $expired = $redis->zRemRangeByScore('email:builder:log_queue', '-inf', time() - 86400);
        echo "Deleted $expired expired log(s) from Redis\n";
    }
}
