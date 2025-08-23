<?php

namespace App\Controllers\Admin\v1;

use App\Controllers\BaseController;
use App\Hooks\Contracts\Template;
use App\Hooks\Resolver\TemplateResolver;
use App\Libraries\ApiResponse;
use App\Traits\ExportTrait;

class SamplesController extends BaseController
{
    use ExportTrait;

    public function download($entity = null)
    {
        $entity = (string) ($entity ?? '');
        $class  = TemplateResolver::resolve($entity);
        if (!$class) {
            return ApiResponse::error("Sample template not found for entity '{$entity}'.");
        }

        /** @var Template $class */
        $headers = $class::columns();
        $rows    = $class::sampleRows();

        $prefix = method_exists($class, 'filenamePrefix') ? $class::filenamePrefix() : $entity . '_template';
        $filename = $prefix . '_' . date('dMy') . '_' . time() . '.csv';

        $csv = $this->buildSampleContent($headers, $rows);
        return self::downloadSample($filename, $csv);
    }

    private function buildSampleContent(array $cols, array $rows = []): string
    {
        $header = self::buildHeader($cols);
        $body   = self::buildBody($cols, $rows);
        return $header .' '. $body;
    }

    /**
     * Build the <tr> header string.
     */
    public static function buildHeader(array $columns): string
    {
        $ths = array_map(
            fn($c) => '<th>' . htmlspecialchars((string)$c, ENT_QUOTES, 'UTF-8') . '</th>',
            $columns
        );
        return "<tr>\n" . implode("\n", $ths) . "\n</tr>";
    }

    /**
     * Build the body rows string (one or many <tr>…</tr>).
     * Accepts assoc or indexed rows; assoc are aligned to $columns.
     */
    public static function buildBody(array $columns, array $rows): string
    {
        $out = [];
        foreach ($rows as $row) {
            $cells = [];
            if (self::isAssoc($row)) {
                foreach ($columns as $c) {
                    $val = $row[$c] ?? '';
                    $cells[] = '<td>' . htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8') . '</td>';
                }
            } else {
                foreach ($row as $val) {
                    $cells[] = '<td>' . htmlspecialchars((string)$val, ENT_QUOTES, 'UTF-8') . '</td>';
                }
            }
            $out[] = "<tr>\n" . implode("\n", $cells) . "\n </tr>";
        }
        return implode("\n", $out);
    }

    private static function isAssoc(array $arr): bool
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}