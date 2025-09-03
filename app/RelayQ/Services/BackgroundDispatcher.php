<?php

namespace Alatise\RelayQ\Services;

use Alatise\RelayQ\Config\RelayQ;
use Alatise\RelayQ\Contracts\DispatcherInterface;
use CodeIgniter\I18n\Time;

class BackgroundDispatcher implements DispatcherInterface
{
    public function __construct(private readonly RelayQ $config)
    {
    }

    /**
     * @throws \Exception
     */
    public function dispatch(string $jobId, ?string $runAt = null): bool
    {
        // If runAt is far in the future, skip spawning to avoid long sleeps
        if ($runAt) {
            $tz    = new \DateTimeZone($this->config->clock);
            $nowTs = (new \DateTimeImmutable('now', $tz))->getTimestamp();
            $dueTs = (new \DateTimeImmutable($runAt, $tz))->getTimestamp();
            $diff  = $dueTs - $nowTs;

            if ($diff > $this->config->maxSpawnWaitSeconds) {
                log_message('info', "RelayQ: skip spawn (delay {$diff}s > cap {$this->config->maxSpawnWaitSeconds}s) for {$jobId}");
                return false;
            }
        }

        $php = $this->resolvePhpBinary();
        $spark = $this->resolveSparkPath();

        $parts = [
            escapeshellarg($php),
            escapeshellarg($spark),
            'relayq:run',
            '--id=' . escapeshellarg($jobId),
        ];
        if ($runAt) {
            $parts[] = '--run-at=' . escapeshellarg($runAt);
        }

        $cmd = implode(' ', $parts);

        // Log the exact command once while debugging (comment out later)
        // @log_message('info', 'RelayQ spawn cmd: ' . $cmd);

        if ($this->config->backgroundPrefer === 'proc_open') {
            return $this->spawnWithProcOpen($cmd);
        }

        $idArg = '--id=' . escapeshellarg($jobId);
        $cd = 'cd ' . escapeshellarg(ROOTPATH);
        $cmd = $cd . ' && ' . escapeshellarg($php) . ' ' . escapeshellarg($spark) . ' relayq:run ' . $idArg;

        return $this->spawnWithExec($cmd);
    }

    private function spawnWithExec(string $cmd): bool
    {
        if (stripos(PHP_OS, 'WIN') === 0) {
            // Windows: start a detached background process
            // Use cmd.exe to interpret && and quoting
            $win = 'cmd /c start /B "" ' . $cmd;
            @pclose(@popen($win, 'r'));
            return true;
        }

        // *nix: background with & and redirect to keep it detached
        @exec($cmd . " > /dev/null 2>&1 &");
        return true;
    }

    private function spawnWithProcOpen(string $cmd): bool
    {
        $logFile = WRITEPATH . 'logs/' . 'relayq-'. date('Y-m-d') .'.log';
        $desc = [
            0 => ['pipe', 'r'],
            1 => ['file', $logFile, 'a'],
            2 => ['file', $logFile, 'a']
        ];
        $proc = @proc_open($cmd, $desc, $pipes, ROOTPATH, null, [
            'bypass_shell' => true
        ]);
        if (is_resource($proc)) {
            foreach ($pipes as $p) {
                if (is_resource($p)) @fclose($p);
            }
            return true;
        }
        @error_log("RelayQ: proc_open failed for cmd: {$cmd}");
        return false;
    }

    private function resolvePhpBinary(): string
    {
        $candidates = [];
        if (!empty($this->config->phpBinary)) {
            $candidates[] = $this->config->phpBinary;
        }
        $candidates[] = PHP_BINDIR . DIRECTORY_SEPARATOR . 'php';
        $candidates[] = '/usr/bin/php';
        $candidates[] = '/usr/local/bin/php';
        // PATH lookup (may be disabled; swallow safely)
        $which = @shell_exec('command -v php') ?: @shell_exec('which php');
        if ($which) $candidates[] = trim($which);

        foreach ($candidates as $path) {
            if ($path && @is_executable($path)) {
                return $path;
            }
        }
        return 'php';
    }

    private function resolveSparkPath(): string
    {
        // Use a configured path if provided; otherwise ROOTPATH . 'spark'
        return $this->config->sparkPath ?: (ROOTPATH . 'spark');
    }
}