<?php
namespace App\Traits;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Html;

trait ExportTrait
{
     private static $_map = array();

    public static function get($col, $row = null): string
    {
        if (!in_array($col, self::$_map)) {
            self::$_map[] = $col;
        }
        $index = array_search($col, self::$_map);
        $columnLetter = Coordinate::stringFromColumnIndex($index + 1);
        return $columnLetter . ($row ? $row : null);
    }

    public static function getLast(): string
    {
        return Coordinate::stringFromColumnIndex(count(self::$_map));
    }

    public static function reset(): void
    {
        self::$_map = array();
    }

    private static function generateDownloadLink(string $filename, string $folder = 'temp/export'): string
    {
        return generateDownloadLink($filename, $folder);
    }

    public static function initSpreadsheet(): array
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()
            ->setCreator('University of Ibadan')
            ->setLastModifiedBy('University of Ibadan')
            ->setTitle('Exported Data')
            ->setSubject('Exported Data')
            ->setDescription('This file contains exported data from the University of Ibadan system.')
            ->setKeywords('export, university, data')
            ->setCategory('Exported Data');

        $activeWorksheet = $spreadsheet->setActiveSheetIndex(0);
        return [
            $spreadsheet,
            $activeWorksheet
        ];
    }

    public static function initBody(array $data): array
    {
        self::reset();
        $i = 1;
        $sheets = self::initSpreadsheet();
        $activeWorksheet = $sheets[1];

        foreach($data as $item){
            $origData = $item;
            $item = str_replace(" ", '_', $item);
            $activeWorksheet->setCellValue(self::get('s/n', $i), 'S/N');
            $activeWorksheet->setCellValue(self::get($item, $i), $origData);
            $i++;
        }

        return [
            'spreadsheet' => $sheets[0],
            'activeWorksheet' => $activeWorksheet,
            'rowIndex' => $i
        ];
    }

    public static function generateBodyExportLink(object $spreadsheet, array $dataToShow, string $title): string
    {
        $spreadsheet->getActiveSheet()->fromArray($dataToShow, null, 'A2');
        $spreadsheet->getActiveSheet()->setTitle($title);
        $filename = FCPATH . "temp/export/" . $title . "_" . date('Y_m_d') . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);
        return self::generateDownloadLink($filename);
    }

    public static function generateExportLink(object $spreadsheet, string $title): string
    {
        $filename = FCPATH . "temp/export/" . $title . "_" . date('Y_m_d_H_i_s') . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);
        return self::generateDownloadLink($filename);
    }

    /**
     * @param string $filename
     * @param string $content
     * @param bool $download
     * @return \CodeIgniter\HTTP\DownloadResponse|string|null
     */
    public static function downloadSample(string $filename, string $content, bool $download = true)
    {
        $html = "<table> $content</table>";
        $filename = WRITEPATH . "temp/{$filename}";
        $reader = new Html();
        $spreadsheet = $reader->loadFromString($html);

        $writer = IOFactory::createWriter($spreadsheet, 'Csv');
        $writer->save($filename);
        if ($download) {
            return sendNewDownload($filename, null);
        }

        return $filename;
    }

}
