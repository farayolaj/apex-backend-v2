<?php

namespace App\Commands;

use App\Libraries\Mail;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class EmailWorker extends BaseCommand
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
    protected $name = 'email:worker';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Continuously processes any named Redis queue, handles retries via a prefixed ZADD, and enqueues logs with TTL';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'email:worker [arguments] [options]';

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
        $queueKey = $this->getQueueName($param[0] ?? 'applicant');
        $batchSize = (int) ($params[1] ?? 50);
        $this->processQueue($queueKey, $batchSize);
    }

    private function getQueueName(string $queueKey): string
    {
        $content = [
            'applicant' => 'email:builder:email_queue',
            'student' => 'email:builder:email_queue_student',
        ];
        return $content[$queueKey] ?? 'email:builder:email_queue';
    }

    private function processQueue($queueName, $batchSize)
    {
        $redis = service('redis');
        dddump($redis);

        while (true) {
            $processed = 0;

            for ($i = 0; $i < $batchSize; $i++) {
                CLI::write(date('Y-m-d H:i:s') . " [MAIL_BUILDER_START] Processing count: " . ($i + 1));
                log_message('info', '[MAIL_BUILDER_START] Processing email queue...');

                $payload = $redis->lPop($queueName);
                if (!$payload) {
                    sleep(2); // Wait before retrying if queue is empty
                    break;
                }

                $email = json_decode($payload, true);
                if (!$email || !isset($email['to'], $email['subject'], $email['message'])) {
                    log_message('error', '[MAIL_BUILDER_ERROR] Invalid email payload: ' . $payload);
                    continue;
                }

                $success = Mail::sendMailBuilder($email['to'], $email['subject'], $email['message']);
                $processed++;
                $date = date('Y-m-d H:i:s');

                if (!$success) {
                    log_message('error', '[MAIL_BUILDER_FAILED] Failed to send email: ' . $email['to']);
                    CLI::write("[MAIL_BUILDER_FAILED] Failed to send: {$email['to']}", 'red');

                    $email['attempts']++;
                    if ($email['attempts'] < $email['max_attempts']) {
                        $retryTime = time() + 60; // Retry in 1 minute
                        $retryQueue = 'retry:' . $queueName;
                        $redis->zadd($retryQueue, $retryTime, json_encode($email));
                    }
                } else {
                    $log = json_encode([
                        'type' => $email['email_type'],
                        'to_email' => $email['to'],
                        'message' => $email['message'],
                        'subject' => $email['subject'],
                        'attempts' => $email['attempts'],
                        'sent_at' => $date,
                        'email_ref' => $email['email_ref']
                    ]);
                    $redis->zadd('email:builder:log_queue', time(), $log);
                }

                if (ENVIRONMENT === 'development' && $i == 0) break;
            }
            // log_message('info', '[MAIL_BUILDER_END] Finished processing email queue.');
            CLI::write('[MAIL_BUILDER_END] Finished processing email queue.');

            // Retry logic
            $this->processRetries($queueName);

            CLI::write("-------------------------------------------------------");
            CLI::newLine();

            if ($processed === 0) {
                // No emails processed in this cycle — sleep to avoid busy-loop
                CLI::write("[QUEUE_WORKER_SLEEPING] Queue empty. Sleeping 0.5s...", 'yellow');
                usleep(500000); // 0.5 seconds
            }
        }
    }

    private function processRetries($queueName): void
    {
        $redis = service('redis');
        $now = time();
        $retryQueue = 'retry:' . $queueName;

        // log_message('info', '[MAIL_BUILDER_RETRY_START] Retrying queue process...');
        CLI::write('[MAIL_BUILDER_RETRY_START] Retrying queue process...');
        $failed = $redis->zRangeByScore($retryQueue, '-inf', $now);

        if (!empty($failed)) {
            foreach ($failed as $emailJson) {
                $redis->zRem($retryQueue, $emailJson);
                $redis->rPush($queueName, $emailJson);
                CLI::write("[MAIL_BUILDER_REQUEUING] Requeued: {$emailJson}");
            }
        }
    }
}
