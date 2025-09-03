<?php
namespace App\Support\Entity;
use CodeIgniter\CLI\CLI;

class GeneratorSupport
{
    /** New: PascalCase (e.g., "course_mapping" => "CourseMapping") */
    public static function pascal(string $name): string
    {
        $name = trim($name);
        if ($name === '') return '';
        $name = str_replace(['-', '_'], ' ', $name);
        $name = ucwords(strtolower($name));
        return str_replace(' ', '', $name);
    }

    public static function option(string $key): ?string
    {
        $val = \CodeIgniter\CLI\CLI::getOption($key);
        if (is_string($val) && $val !== '') {
            return $val;
        }

        $argv = $_SERVER['argv'] ?? [];
        $needleEq = "--{$key}=";
        for ($i = 0, $n = count($argv); $i < $n; $i++) {
            $arg = $argv[$i];

            // --key=value
            if (strncmp($arg, $needleEq, strlen($needleEq)) === 0) {
                return (string) substr($arg, strlen($needleEq));
            }

            // --key  value
            if ($arg === "--{$key}") {
                $next = $argv[$i + 1] ?? null;
                if (is_string($next) && strncmp($next, '--', 2) !== 0) {
                    return $next;
                }
                // boolean flag case: treat as present but empty value
                return '';
            }
        }
        return null;
    }

    public static function studly(string $name, bool $classic = false): string
    {
        return studly($name, $classic);
    }

    public static function kebab(string $name): string
    {
        $name = preg_replace('/([a-z])([A-Z])/', '$1-$2', $name);
        $name = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        return trim($name, '-');
    }

    public static function ensureDir(string $path): void
    {
        if (!is_dir($path)) {
            @mkdir($path, 0775, true);
        }
    }

    public static function safeWrite(string $path, string $contents, bool $force, bool $dryRun): void
    {
        self::ensureDir(dirname($path));

        if (file_exists($path) && !$force) {
            CLI::error("Exists: {$path} (use --force to overwrite)");
            return;
        }

        if ($dryRun) {
            CLI::write("Would write: {$path}");
            return;
        }

        file_put_contents($path, self::normalizeEOL($contents));
        CLI::write("Created: {$path}", 'green');
    }

    public static function normalizeEOL(string $s): string
    {
        // Normalize to \n to avoid mixed endings in repo
        $s = str_replace(["\r\n", "\r"], "\n", $s);
        // Ensure trailing newline
        return rtrim($s, "\n") . "\n";
    }

    /**
     * Parse --columns="a, b,c" -> ['a','b','c'] (trimmed, lowercase preserved)
     */
    public static function parseColumnsOption(?string $opt): array
    {
        if ($opt === null || trim($opt) === '') return [];
        return array_values(array_filter(array_map('trim', explode(',', $opt)), fn($v) => $v !== ''));
    }

    /**
     * Parse --sample as JSON or key=value CSV (quotes supported).
     * Returns assoc array. Unknown/invalid format returns [].
     *
     * Examples:
     *  --sample='{"code":"BUS101","title":"Business Intelligence"}'
     *  --sample='code=BUS101,title="Business Intelligence",department_code=ECO'
     */
    public static function parseSampleOption(?string $opt): array
    {
        if ($opt === null || trim($opt) === '') return [];

        $s = trim($opt);
        // JSON?
        if (strlen($s) > 1 && ($s[0] === '{' || $s[0] === '[')) {
            $decoded = json_decode($s, true);
            return is_array($decoded) ? (array)$decoded : [];
        }

        // key=value, key="value with, comma"
        $out = [];
        $len = strlen($s);
        $key = '';
        $val = '';
        $inKey = true;
        $inQuote = false;
        $buf = '';

        $flushPair = function () use (&$out, &$key, &$val) {
            $k = trim($key);
            if ($k !== '') {
                $out[$k] = trim($val, " \t\"'");
            }
            $key = '';
            $val = '';
        };

        for ($i = 0; $i < $len; $i++) {
            $ch = $s[$i];

            if ($ch === '"') {
                $inQuote = !$inQuote;
                continue;
            }

            if (!$inQuote) {
                if ($inKey && $ch === '=') {
                    $key = $buf;
                    $buf = '';
                    $inKey = false;
                    continue;
                }
                if ($ch === ',') {
                    if ($inKey) {
                        // dangling key without value; treat as empty
                        $key = $buf;
                        $val = '';
                    } else {
                        $val = $buf;
                    }
                    $flushPair();
                    $buf = '';
                    $inKey = true;
                    continue;
                }
            }

            $buf .= $ch;
        }

        // tail
        if ($buf !== '') {
            if ($inKey) {
                $key = $buf;
                $val = '';
            } else {
                $val = $buf;
            }
            $flushPair();
        }

        return $out;
    }
}
