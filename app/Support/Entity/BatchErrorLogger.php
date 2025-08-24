<?php
namespace App\Support\Entity;

final class BatchErrorLogger
{
    private string $path;
    private int $flushEvery;

    /** @var string[] */
    private array $buf = [];
    private int $seq = 0;
    private bool $headerWritten = false;

    public function __construct(string $path, int $flushEvery = 200, $header = null)
    {
        $this->path = $path;
        $this->flushEvery = max(1, $flushEvery);

        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        if ($header !== null) {
            $this->writeHeader($header);
        }
    }

    public function writeHeader($header): void
    {
        if ($this->headerWritten) return;

        $text = is_array($header) ? implode('', $header) : (string)$header;
        file_put_contents($this->path, $text, FILE_APPEND | LOCK_EX);
        $this->headerWritten = true;
    }

    /** @param mixed $messages string|array (will be json-encoded if array) */
    public function add(int $row, mixed $messages): void
    {
        $this->seq++;
        $line = $this->format($this->seq, $row, $messages);
        $this->buf[] = $line;

        if (count($this->buf) >= $this->flushEvery) {
            $this->flush();
        }
    }

    public function flush(): void
    {
        if (!$this->buf) return;
        // atomic-ish appends; very fast in batches
        file_put_contents($this->path, implode('', $this->buf), FILE_APPEND | LOCK_EX);
        $this->buf = [];
    }

    public function close(): void
    {
        $this->flush();
    }

    private function format(int $i, int $row, $messages): string
    {
        $msg = is_array($messages)
            ? json_encode($messages, JSON_UNESCAPED_SLASHES)
            : (string)$messages;

        return "{$i}. row {$row}: {$msg}" . PHP_EOL;
    }
}
