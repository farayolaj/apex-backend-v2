<?php
namespace App\Hooks\Contracts;

interface Template
{
    /** Column headers in the exact order you want in the CSV */
    public static function columns(): array;

    /**
     * One or more sample rows.
     * Each row may be:
     *   - indexed array (must match columns order), or
     *   - associative array keyed by column name (missing keys become empty)
     */
    public static function sampleRows(): array;

    /** Optional prefix for the filename (default falls back to entity name) */
    public static function filenamePrefix(): string;
}
