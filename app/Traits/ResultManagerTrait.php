<?php

namespace App\Traits;

use Mpdf\Mpdf;
use Mpdf\MpdfException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Html;
use Config\Services;

trait ResultManagerTrait
{

    /**
     * Returns a sample HTML table header.
     *
     * @return string
     */
    public static function sampleHeader(): string
    {
        return "<tr>
                    <th>matric_number</th>
                    <th>ca_scores</th>
                    <th>exam_scores</th>
                </tr>";
    }

    /**
     * Returns a sample HTML table row.
     *
     * @param array $csvData The data to populate the row.
     * @return string
     */
    public static function sampleBody(array $csvData): string
    {
        return "<tr>
                    <td>{$csvData['matric_number']}</td>
                    <td>{$csvData['ca_score']}</td>
                    <td>{$csvData['exam_score']}</td>
                </tr>";
    }

    /**
     * Downloads a sample CSV file.
     *
     * @param string $filename The name of the file.
     * @param string $header The table header.
     * @param string $body The table body.
     * @param bool $download Whether to download the file or save it.
     */
    public static function downloadSample(string $filename, string $header, string $body, bool $download = true)
    {
        $html = "<table>$header $body</table>";
        $filename = WRITEPATH . "temp/{$filename}";
        $reader = new Html();
        $spreadsheet = $reader->loadFromString($html);

        $writer = IOFactory::createWriter($spreadsheet, 'Csv');
        $writer->save($filename);

        if ($download) {
            return response()->download($filename, null, true);
        }

        return $filename;
    }

    /**
     * Generates a download link for a file.
     *
     * @param string $filename The name of the file.
     * @param string $folder The folder where the file is stored.
     * @return string
     */
    public static function generateLink(string $filename, string $folder = 'temp/logs'): string
    {
        return generateDownloadLink($filename, $folder, 'direct_link_logs', false);
    }

    /**
     * Returns the path to the institution logo.
     *
     * @return string
     */
    public static function institutionLogo(): string
    {
        return ROOTPATH . "public". DIRECTORY_SEPARATOR. "assets" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . get_setting('institution_logo');
    }

    /**
     * Generates a PDF from HTML content.
     *
     * @param string $html The HTML content.
     * @param string|null $filename The name of the PDF file.
     * @param string|null $title The title of the PDF.
     * @param string|null $slug The slug for the PDF.
     * @param string $format The page format (e.g., 'A4-P').
     * @return void
     * @throws MpdfException
     */
    public static function generate2Pdf(string $html, ?string $filename = null, ?string $title = null, ?string $slug = null, string $format = 'A4-P')
    {
        $mpdf = new Mpdf([
            'tempDir' => WRITEPATH . 'temp',
            'mode' => 'utf-8',
            'format' => $format,
            'margin_top' => 7,
        ]);

        $mpdf->SetAuthor('Infosys');
        $mpdf->SetTitle($title);
        $mpdf->SetSubject($slug);
        $mpdf->SetKeywords('PDF');
        $mpdf->SetDisplayMode('fullpage');

        $mpdf->WriteHTML($html);
        $filename = $filename ?: $slug . "_document";
        $mpdf->Output($filename . '.pdf', 'I');
    }

    /**
     * Generates a report download link.
     *
     * @param string $filename The name of the file.
     * @param string $routes The route to the download endpoint.
     * @return string
     */
    public static function generateReportLink(string $filename, string $routes): string
    {
        $originalName = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $fid = rndEncode($originalName, 32);
        $ex = base64_encode($extension);
        $hid = hash('sha512', $originalName);
        $key = uniqid(time() . '-key', true);

        return base_url("{$routes}?fid={$fid}&ex={$ex}&hid={$hid}&key={$key}");
    }

    /**
     * Builds the filename from the download link parameters.
     *
     * @param string $name The encoded filename.
     * @param string $extension The encoded file extension.
     * @param string $hash The hash for validation.
     * @param string $timeToken The time token for validation.
     * @return string
     */
    private function buildFilenameFromLink(string $name, string $extension, string $hash, string $timeToken): string
    {
        $name = urldecode(trim($name));
        $extension = urldecode(trim($extension));
        $hash = urldecode(trim($hash));
        $key = urldecode(trim($timeToken));
        $currentTime = time();
        $ciphertext = rndDecode($name, 32);
        $extension = base64_decode($extension);

        if (hash('sha512', $ciphertext) !== $hash) {
            exit("It appears that the link is broken, please try again");
        }

        $key = explode('-', $key);
        if (isTimePassed($currentTime, $key[0])) {
            exit("Oops an invalid or expired link was provided.");
        }

        return $ciphertext . "." . $extension;
    }

    /**
     * Validates the URL link and returns the filename.
     *
     * @return string
     */
    public function validateUrlLink(): string
    {
        $request = Services::request();
        $name = $request->getGet('fid', true);
        if (!$name) {
            exit("Oops, unable to download link");
        }
        $hash = $request->getGet('hid', true);
        if (!$hash) {
            exit("Oops, unable to download link");
        }
        $timeToken = $request->getGet('key', true);
        $extension = $request->getGet('ex', true);
        return $this->buildFilenameFromLink($name, $extension, $hash, $timeToken);
    }

    /**
     * Returns the finance formula.
     *
     * @return array
     */
    private static function financeFormula(): array
    {
        return [
            [1, 50, 75000],
            [51, 100, 100000],
            [101, 150, 125000],
            [151, 200, 150000],
            [201, 250, 200000],
            [251, 300, 250000],
        ];
    }

    /**
     * Calculates the tutor amount.
     *
     * @param int $total The total number of students.
     * @param bool $paper Whether it's for paper marking.
     * @return array
     */
    public static function calcTutorAmount(int $total, bool $paper = false): array
    {
        if ($total == 0) {
            return [
                'total' => 0,
                'extra' => 0,
                'perExtra' => 0,
                'extraTotal' => 0,
                'sumTotal' => 0,
            ];
        }

        $range = self::financeFormula();
        $extraPaper = false;
        $extraCbt = false;
        $perHead = 0;
        $totalLimit = 300;
        $extraTotal = 0;
        $newTotal = 0;

        if ($total > $totalLimit && $paper) {
            $extraPaper = true;
            $perHead = 100;
        } elseif ($total > $totalLimit && !$paper) {
            $extraCbt = true;
        }

        foreach ($range as $val) {
            $min = $val[0];
            $max = $val[1];
            $sumTotal = $val[2];
            $lastAmount = $val[2];

            // using an early return approach
            if ($extraPaper) {
                // get the last array from the range
                $last = end($range);
                $lastMax = $last[1];
                $lastAmount = $last[2];
                $extraTotal = $total - $lastMax;
                $newTotal = $extraTotal * $perHead;
                $sumTotal = $newTotal + $lastAmount;
                return [
                    'total' => $lastAmount,
                    'extra' => $extraTotal,
                    'perExtra' => $perHead,
                    'extraTotal' => $newTotal,
                    'sumTotal' => $sumTotal,
                ];
            }

            if ($extraCbt) {
                $last = end($range);
                $lastAmount = $last[2];
                return [
                    'total' => $lastAmount,
                    'extra' => 0,
                    'perExtra' => 0,
                    'extraTotal' => 0,
                    'sumTotal' => $lastAmount,
                ];
            }

            if ($total >= $min && $total <= $max) {
                return [
                    'total' => $lastAmount,
                    'extra' => $extraTotal,
                    'perExtra' => $perHead,
                    'extraTotal' => $newTotal,
                    'sumTotal' => $sumTotal,
                ];
            }
        }
    }

    /**
     * Calculates the facilitation amount.
     *
     * @return array
     */
    public static function calcFacilitation(): array
    {
        return [
            'total' => 50000,
            'extra' => 0,
            'perExtra' => 0,
            'extraTotal' => 0,
            'sumTotal' => 50000,
        ];
    }

    /**
     * Calculates the interaction amount.
     *
     * @return array
     */
    public static function calcInteraction(): array
    {
        return [
            'total' => 10000,
            'extra' => 0,
            'perExtra' => 0,
            'extraTotal' => 0,
            'sumTotal' => 10000,
        ];
    }

    /**
     * Returns the script table data.
     *
     * @return string
     */
    public static function scriptTableData(): string
    {
        return '
			<tr>
			  <td
				width="40"
				height="20"
				align="center"
				style="border: 1px solid #000000";border-collapse: collapse"
			  >
				<span>2. (a) <br/> &nbsp;&nbsp;&nbsp; (b) <br/> &nbsp;&nbsp;&nbsp; (c)  </span>
			  </td>
			  <td width="180" style="border: 1px solid #000000;padding-left:10px;">
				<span>Teaching/Marking of Scripts <br/> Excess Marking of Scripts <br/> CBT</span>
			  </td>
			  <td width="100" align="center" style="border: 1px solid #000000">
				<span>{title}</span>
			  </td>
			  <td width="80" align="center" style="border: 1px solid #000000">
				<span>{code}</span>
			  </td>
			  <td
				width="80"
				height="70"
				align="center"
				style="border: 1px solid #000000"
			  >
				<span>{total_student}</span>
			  </td>
			  <td width="45" align="center" style="border: 1px solid #000000">
				<span></span>
			  </td>
			  <td width="40" align="center" style="border: 1px solid #000000">
				<span>{total_amount}</span>
			  </td>
			  <td width="40" align="center" style="border: 1px solid #000000">
				<span></span>
			  </td>
			</tr>
		';
    }

    /**
     * Returns the interaction table data.
     *
     * @return string
     */
    public static function interactionTableData(): string
    {
        return '
			<tr>
			  <td
				width="40"
				height="20"
				align="center"
				style="border: 1px solid #000000";border-collapse: collapse"
			  >
				<span>1.</span>
			  </td>
			  <td width="180" style="border: 1px solid #000000;padding-left:10px;">
				<span>Interactive Session/Revision <br /> * Face to Face <br/ > * Online</span>
			  </td>
			  <td width="100" align="center" style="border: 1px solid #000000">
				<span>{title}</span>
			  </td>
			  <td width="80" align="center" style="border: 1px solid #000000">
				<span>{code}</span>
			  </td>
			  <td
				width="80"
				height="70"
				align="center"
				style="border: 1px solid #000000"
			  >
				<span>{total_student}</span>
			  </td>
			  <td width="45" align="center" style="border: 1px solid #000000">
				<span></span>
			  </td>
			  <td width="40" align="center" style="border: 1px solid #000000">
				<span>{total_amount}</span>
			  </td>
			  <td width="40" align="center" style="border: 1px solid #000000">
				<span></span>
			  </td>
			</tr>
		';
    }
}