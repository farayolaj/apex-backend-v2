# RelayQ — tiny, reliable background jobs for CodeIgniter 4

**RelayQ** lets you run small background tasks (like emails, notifications, webhooks) **without blocking the user**.
It’s simple, safe, and uses your **database** as the source of truth. No long-running workers needed.

---

## Why RelayQ?

Use RelayQ when you want to:

* send a few emails or notifications after a request
* trigger a webhook
* run a short task “later” (seconds to minutes)

Don’t use RelayQ for:

* massive bulk processing
* streaming, real-time queues
* heavy, long-running workers

---

## Features at a glance

* ✅ **Non-blocking:** user gets a response; job runs after
* ✅ **One-shot run:** each handoff runs exactly one job (no loops)
* ✅ **Delay, retries, backoff**
* ✅ **Unique jobs** (avoid duplicates)
* ✅ **Database-first** (durable), **optional Redis** for faster de-dup
* ✅ Two handoff modes: **Background (PHP CLI)** or **HTTP**
* ✅ **Sweeper** safety net (tiny cron)
* ✅ Clear error if your job’s constructor needs data but you didn’t provide it (via `toArray()/fromArray()` check)

---

## How it works (in simple words)

1. You **dispatch** a job → RelayQ saves it in the DB.
2. RelayQ **hands it off** to run once (Background or HTTP).
3. If anything gets stuck, the **Sweeper** gives it a nudge.
4. No always-on workers. No polling loops.

---

## Requirements

* PHP 8.1+ and CodeIgniter 4
* MySQL/MariaDB/PostgreSQL
* (Optional) Redis + php-redis
* For **Background** mode: PHP CLI available in your container/server

---

## Installation

### 1) Put the module in your app

Place the module under:

```
app/RelayQ/...
```

(Namespace is `Alatise\RelayQ\...`.)

### 2) Composer PSR-4 autoload

`composer.json`:

```json
{
  "autoload": {
    "psr-4": {
      "App\\": "app/",
      "Alatise\\RelayQ\\": "app/RelayQ/"
    }
  }
}
```

Then:

```bash
composer dump-autoload -o
```

### 3) Service factory

`app/Config/Services.php`:

```php
public static function relayq(bool $getShared = true): \Alatise\RelayQ\Services\RelayQ
{
    if ($getShared) return static::getSharedInstance('relayq');

    $config = config(\Alatise\RelayQ\Config\RelayQ::class);
    $repo   = new \Alatise\RelayQ\Repositories\JobRepository(db_connect());
    $bg     = new \Alatise\RelayQ\Services\BackgroundDispatcher($config); // used if driver=background
    $http   = new \Alatise\RelayQ\Services\HttpDispatcher($config);       // used if driver=http
    $redis  = new \Alatise\RelayQ\Services\RedisAdapter($config);         // optional; no-op if disabled

    return new \Alatise\RelayQ\Services\RelayQ($config, $repo, $bg, $http, $redis);
}
```

### 4) Route for HTTP handoff

`app/Config/Routes.php`:

```php
$routes->post('_relayq/run', '\Alatise\RelayQ\Controllers\RunController::runOne');
```

### 5) Run the migration

Make sure the migration file is under:

```
app/RelayQ/Database/Migrations/...
```

Then run:

```bash
php spark migrate -n "Alatise\RelayQ"
# or
php spark migrate --all
```

---

## Configuration (simple)

`app/RelayQ/Config/RelayQ.php` (key settings):

```php
public string $driver = 'background';  // 'background' or 'http'
public string $clock  = 'UTC';         // single clock for all timestamps

// Short delays the runner/controller will wait; long delays are handled by Sweeper
public int $maxSpawnWaitSeconds = 30;

// Background (PHP CLI)
public string $phpBinary  = '/usr/local/bin/php'; // set to your CLI path
public string $sparkPath  = ROOTPATH . 'spark';

// HTTP
// Put your token in .env: relayq.token=YOUR_SECRET
public string $httpEndpoint = ''; // default: base_url('_relayq/run')
public string $httpToken    = ''; // default: env('relayq.token')

// Redis (optional accelerator; DB remains the source of truth)
public bool   $redisEnabled = false;
public bool   $redisUseUniqueness = true; // fast dedupe hint
public string $redisHost = '127.0.0.1';
public int    $redisPort = 6379;
public ?string $redisPassword = null;
public int    $redisDatabase = 0;
public string $redisPrefix = 'relayq:';
```

`.env` (if using HTTP):

```
relayq.token=YOUR_SECRET
```

---

## Quick start (3 steps)

### 1) Create a Job

```php
namespace App\Jobs;

use Alatise\RelayQ\Contracts\JobInterface;
use Alatise\RelayQ\Traits\Queueable;

class SendNotification implements JobInterface
{
    use Queueable;

    public function __construct(
        public int $userId,           // public scalars serialize automatically
        public string $title,
        public string $body
    ) {}

    public function handle(): void
    {
        $user = model(\App\Models\UserModel::class)->find($this->userId);
        // send email/push/etc...
        log_message('info', "Notified {$user->email}: {$this->title}");
    }
}
```

### 2) Dispatch it

```php
relayq_dispatch(
  (new \App\Jobs\SendNotification($userId, 'Welcome!', 'Thanks for joining'))
    ->onQueue('notifications')
    ->delay(5)                 // run after 5 seconds
    ->maxAttempts(3)           // retries
    ->backoff([30,120,600])    // wait 30s, then 120s, then 600s between retries
    ->unique("welcome:$userId", 300) // avoid duplicates for 5 minutes
);
```

### 3) See it run

Check your app logs for the job output.
If using **Background**, make sure `phpBinary` points to your CLI (e.g. `/usr/local/bin/php`).

---

## Examples (copy-paste)

### A) **Recommended:** scalar constructor (no `toArray()` needed)

```php
class PostWebhook implements \Alatise\RelayQ\Contracts\JobInterface
{
    use \Alatise\RelayQ\Traits\Queueable;

    public function __construct(public string $url, public array $payload) {}

    public function handle(): void
    {
        $client = \Config\Services::curlrequest();
        $client->post($this->url, ['json' => $this->payload, 'timeout' => 5]);
    }
}
```

### B) Constructor expects an **object** → implement `toArray()` + `fromArray()` (new)

```php
class NotifyUser implements \Alatise\RelayQ\Contracts\JobInterface
{
    use \Alatise\RelayQ\Traits\Queueable;

    public function __construct(protected \App\Entities\User $user, protected string $message) {}

    public function toArray(): array
    {
        return ['userId' => $this->user->id, 'message' => $this->message];
    }

    public static function fromArray(array $d): self
    {
        $user = (new \App\Models\UserModel())->find($d['userId']);
        return new self($user, $d['message']);
    }

    public function handle(): void
    {
        // use $this->user ...
    }
}
```

> If the constructor has required parameters and your payload doesn’t provide them, RelayQ throws a clear error at **dispatch** time with what to fix.

### C) Stateless **action object** (pass class name; build inside)

```php
interface ActionInterface { public function run(): void; }

class InsertWelcome implements ActionInterface
{
    public function run(): void { /* insert row... */ }
}

class RunAction implements \Alatise\RelayQ\Contracts\JobInterface
{
    use \Alatise\RelayQ\Traits\Queueable;
    public function __construct(protected string $actionClass) {}

    public function toArray(): array { return ['actionClass' => $this->actionClass]; }
    public static function fromArray(array $d): self { return new self($d['actionClass']); }

    public function handle(): void { (new ($this->actionClass))->run(); }
}
```

### D) Empty constructor (build everything in `handle()`)

```php
class RebuildIndex implements \Alatise\RelayQ\Contracts\JobInterface
{
    use \Alatise\RelayQ\Traits\Queueable;

    public function handle(): void
    {
        $service = \Config\Services::searchIndexer();
        $service->rebuildAll();
    }
}
```

### E) Multiple queues

```php
(new PostWebhook($url, $payload))->onQueue('webhooks');
(new SendNotification($userId,'Thanks','…'))->onQueue('notifications');
(new RebuildIndex())->onQueue('maintenance');
```

---

## Handoff modes

* **Background (PHP CLI)**
  RelayQ spawns:

  ```
  php spark relayq:run --id=<uuid> --run-at="<UTC time>"
  ```

  If the delay is short (≤ `maxSpawnWaitSeconds`), the runner sleeps that many seconds and runs once.
  If the delay is longer, RelayQ **skips spawning** (so you don’t keep PHP sleeping). The Sweeper will pick it up later.

* **HTTP**
  RelayQ POSTs to `/_relayq/run`. The controller reads the job’s `available_at`, waits up to the same cap, or returns **202 Accepted** so the Sweeper can pick it up when due.

Pick **Background** if you can run PHP CLI easily.
Pick **HTTP** if your host blocks `proc_open/exec` or you prefer pure HTTP.

---

## Uniqueness & idempotency

* Set a unique key: `->unique('payment:tx123', 600)`
  RelayQ uses a DB unique index (no duplicates). With a TTL (5–10 minutes), you can say “only once per window”.
* When the same unique job is dispatched twice, the **first** wins; the second returns the existing job ID.

---

## Retries & backoff

* `->maxAttempts(3)` means try up to 3 times total.
* `->backoff([30,120,600])` waits 30s, then 120s, then 600s before each retry.
* If all attempts fail, the row goes to `relayq_failed` with the error.

---

## Redis (optional)

You don’t need Redis.
If enabled, RelayQ can use Redis as a **fast de-dup hint** before the DB insert. The DB is still the source of truth.

---

## Sweeper (cron)

The Sweeper re-hands off **stale** or **timed-out** jobs.

**Linux host:**

```cron
*/2 * * * * flock -n /tmp/relayq-sweep.lock /usr/local/bin/php /var/www/html/spark relayq:sweep --stale=30 --vt=60 --limit=200 >> /var/www/html/writable/logs/relayq.sweep.log 2>&1
```

**Docker host → container:**

```cron
*/2 * * * * docker exec -t <php_container_name> php /var/www/html/spark relayq:sweep --stale=30 --vt=60 --limit=200 >/dev/null 2>&1
```

**Inside container:**

```cron
*/2 * * * * /usr/local/bin/php /var/www/html/spark relayq:sweep --stale=30 --vt=60 --limit=200 >> /var/www/html/writable/logs/relayq.sweep.log 2>&1
```

Flags:

* `--stale=30` → pending jobs not handed off in 30s get nudged
* `--vt=60` → reserved jobs older than 60s are released
* `--limit=200` → max jobs per sweep
* (Optional) `--queue=notifications` → only that queue

---

## Troubleshooting

* **Migration not running**
  Put the file under `app/RelayQ/Database/Migrations/…` and use namespace `Alatise\RelayQ\Database\Migrations`.
  Check with: `php spark migrate:status -n "Alatise\RelayQ"`

* **Background not running**
  Set the correct PHP CLI path in config (`$phpBinary`). We spawn with `proc_open` from your project root.
  Check `writable/logs/relayq.bg.log` and app logs.

* **Time zones**
  Set `clock = 'UTC'`. RelayQ writes and reads times in that same clock.

* **HTTP 403**
  Set `.env` `relayq.token=YOUR_SECRET` and send that header on internal POST.

* **Constructor needs data but payload is empty**
  RelayQ will **fail fast** at dispatch with a clear error.
  Fix by using **public scalars**, or add **`toArray()` + `fromArray()`**.

* **Nothing happens**
  Run a sweep:
  `php spark relayq:sweep --stale=30 --vt=60 --limit=200`

---

## FAQ

**Do I need Redis?** No. It’s optional.
**Will this block my user response?** No. Handoff is non-blocking.
**How many jobs is this for?** Great for small, bursty tasks (not bulk processing).
**Can I schedule for later?** Yes: use `->delay(seconds)`. Long delays are picked by the Sweeper.
**Can I pass objects to the constructor?** Yes—use `toArray()` + `fromArray()` so RelayQ can rebuild them.

---

## Contributing

PRs and issues are welcome. Please keep code small, clear, and well-commented.

---

## License

MIT
