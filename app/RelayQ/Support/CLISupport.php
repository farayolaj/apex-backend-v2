<?php

namespace Alatise\RelayQ\Support;

class CLISupport
{
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

            // --key value
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

}