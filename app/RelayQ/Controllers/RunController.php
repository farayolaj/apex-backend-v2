<?php

namespace Alatise\RelayQ\Controllers;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Controller;

class RunController extends Controller
{
    /**
     * @throws \DateInvalidTimeZoneException
     * @throws \Exception
     */
    public function runOne()
    {
        $token = $this->request->getHeaderLine('X-RelayQ-Token');
        if ($token !== (env('relayq.token') ?? '')) return $this->response->setStatusCode(403);

        $data = $this->request->getJSON(true) ?? [];
        $id = $data['id'] ?? null;
        if (!$id) return $this->response->setStatusCode(422);
        $runAt = $data['run_at'] ?? null;

        $svc  = service('relayq');
        $cfg = $svc->config();

        $tz    = new \DateTimeZone($cfg->clock);
        $nowTs = (new \DateTimeImmutable('now', $tz))->getTimestamp();
        $dueTs = (new \DateTimeImmutable($runAt, $tz))->getTimestamp();
        $diff  = $dueTs - $nowTs;

        if ($diff > 0) {
            // Only wait if the delay is small; otherwise let the sweeper nudge later
            $cap = (int) ($cfg->maxSpawnWaitSeconds ?? 30);
            if ($diff > $cap) {
                log_message('info', "RelayQ HTTP: defer {$id}, delay {$diff}s > cap {$cap}s");
                return $this->response->setStatusCode(202);
            }
            sleep($diff);
        }

        $svc->runOne($id);
        return $this->response->setStatusCode(204);
    }
}