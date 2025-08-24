<?php
namespace App\Support\Csv;

use CodeIgniter\HTTP\Files\UploadedFile;
use SplFileObject;

final class CsvReader
{
    /**
     * Stream CSV rows as [rowNumber, assocArray].
     *
     * @param UploadedFile|string $file
     * @param array<string,string>|null $headerMap   One-time header remap: ['csv_name'=>'model_name']
     * @param string $delimiter
     * @param int|null $maxRows            Limit data rows processed (header not counted)
     * @param bool $lowercaseHeaders       Normalize headers to lowercase
     * @return \Generator<int,array{int,array}>
     */
    public static function readAssoc(
        UploadedFile|string $file,
        ?array $headerMap,
        string $delimiter = ',',
        ?int $maxRows = null,
        bool $lowercaseHeaders = true
    ): \Generator {
        $path = $file instanceof UploadedFile ? ($file->getTempName() ?: $file->getRealPath()) : $file;
        if (!is_string($path) || !is_file($path)) {
            throw new \InvalidArgumentException('CSV file not found or invalid upload.');
        }

        $csv = new \SplFileObject($path, 'r');
        $csv->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        $csv->setCsvControl($delimiter);

        // --- READ HEADER ONCE (ADVANCES POINTER) ---
        $headers = $csv->fgetcsv();
        if ($headers === false) {
            return;
        }
        $headers = array_map(function ($h) use ($lowercaseHeaders) {
            $h = is_string($h) ? trim(self::stripBom($h)) : '';
            return $lowercaseHeaders ? strtolower($h) : $h;
        }, (array) $headers);

        if ($headerMap) {
            $mapped = [];
            foreach ($headers as $h) {
                $mapped[] = $headerMap[$h] ?? $h;
            }
            $headers = $mapped;
        }

        // --- STREAM DATA ROWS ---
        $rowNo = 1;
        while (!$csv->eof()) {
            $line = $csv->fgetcsv();
            if ($line === false) {
                continue;
            }

            $cells = array_map(
                static fn($v) => is_string($v) ? trim($v) : ($v ?? ''),
                (array) $line
            );
            if (count($cells) < count($headers)) {
                $cells = array_pad($cells, count($headers), '');
            }

            $assoc = array_combine($headers, array_slice($cells, 0, count($headers)));
            // skip fully empty rows
            if (!array_filter($assoc, static fn($v) => $v !== '' && $v !== null)) {
                continue;
            }

            yield [$rowNo, $assoc];

            $rowNo++;
            if ($maxRows !== null && $rowNo - 1 >= $maxRows) {
                break;
            }
        }
    }

    private static function stripBom(string $s): string
    {
        return preg_replace('/^\xEF\xBB\xBF/', '', $s) ?? $s;
    }
}
