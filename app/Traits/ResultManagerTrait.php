<?php

namespace App\Traits;

use Mpdf\Mpdf;
use Mpdf\MpdfException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Html;
use Config\Services;

trait ResultManagerTrait
{
    public static function sampleHeader(): string
    {
        return "<tr>
                    <th>matric_number</th>
                    <th>ca_scores</th>
                    <th>exam_scores</th>
                    </tr>";
    }

    public static function sampleBody(array $csvData): string
    {
        return "<tr>
			<td>{$csvData['matric_number']}</td>
			<td>{$csvData['ca_score']}</td>
			<td>{$csvData['exam_score']}</td>
		</tr>";
    }

    public static function downloadSample(string $filename, string $header, string $body, $download = true)
    {
        $html = "<table>$header $body</table>";
        $filename = FCPATH . "temp/{$filename}";
        $reader = new Html();
        $spreadsheet = $reader->loadFromString($html);

        $writer = IOFactory::createWriter($spreadsheet, 'Csv');
        $writer->save($filename);
        if ($download) {
            sendDownload($filename, "application/csv", $filename, 'binary');
            exit;
        }

        return $filename;
    }

    public static function generateLink(string $filename, string $folder = 'temp/logs'): string
    {
        return generateDownloadLink($filename, $folder, 'direct_link_logs', false);
    }

    public static function institutionLogo(): string
    {
        return FCPATH . "assets" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR . get_setting('institution_logo');
    }

    public static function generate2Pdf($html, $filename = null, $title = null, $slug = null, $format = 'A4-P')
    {
        $mpdf = new Mpdf([
            'tempDir' => FCPATH . 'temp',
            'mode' => 'utf-8',
            'format' => $format,
            'margin_top' => 7,
        ]);
        // $mpdf->showImageErrors = true;
        $mpdf->SetAuthor('Infosys');
        $mpdf->SetTitle($title);
        $mpdf->SetSubject($slug);
        $mpdf->SetKeywords('PDF');
        $mpdf->SetDisplayMode('fullpage');

        $mpdf->WriteHTML($html);
        $filename = $filename ?: $slug . "_document";
        return $mpdf->Output($filename . '.pdf', 'I');
    }

    public static function generateReportLink(string $filename, string $routes): string
    {
        $originalName = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $fid = rndEncode($originalName, 32);
        $ex = base64_encode($extension);
        $hid = hash('sha512', $originalName);
        $key = uniqid(time() . '-key', TRUE);

        return site_url("{$routes}?fid={$fid}&ex={$ex}&hid={$hid}&key={$key}");
    }

    private function buildFilenameFromLink(string $name, string $extension, string $hash, string $timeToken)
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
        if (isTimePassed($currentTime, $key[0], 30)) {
            exit("Oops an invalid or expired link was provided.");
        }
        return $ciphertext . "." . $extension;
    }

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

    public static function inferPaymentAmount($courseCode, $total, $isPaper, $cadreTitle): ?array
    {
        if ($cadreTitle && isGESCourse($courseCode)) {
            return self::calcGESTutorAmount($total, $isPaper, $cadreTitle);
        }
        return self::calcTutorAmount($total, $isPaper, $cadreTitle);
    }

    public static function calcGESTutorAmount($total, $paper = false, $cadre = null): ?array
    {
        if ($total >= 1000) {
            return [
                'total' => 600000,
                'extra' => 0,
                'perExtra' => 0,
                'extraTotal' => 0,
                'sumTotal' => 600000,
            ];
        }
        return self::calcTutorAmount($total, $paper, $cadre);
    }

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

    private static function financeProposedFormula(): array
    {
        return [
            [1, 25, 50000],
            [26, 50, 70000],
            [51, 100, 90000],
            [101, 150, 100000],
            [151, 200, 120000],
            [201, 250, 150000],
            [251, 50000, 180000],
        ];
    }

    private static function financeDepartmentalFormula(): array
    {
        return [
            [1, 50, 60000],
            [51, 200, 75000],
            [201, 500, 100000],
            [501, 750, 125000],
            [751, 1000, 150000],
            [1001, 1500, 175000],
            [1501, 2000, 200000],
            [2001, 3000, 250000],
            [3001, 50000, 275000],
        ];
    }

    public static function webinarExcessPerLearner(): array
    {
        return [
            'total' => 100,
            'extra' => 0,
            'perExtra' => 0,
            'extraTotal' => 0,
            'sumTotal' => 100,
        ];
    }

    private static function zeroTotalValue(): array
    {
        return [
            'total' => 0,
            'extra' => 0,
            'perExtra' => 0,
            'extraTotal' => 0,
            'sumTotal' => 0,
        ];
    }

    public static function totalAmountPayload($amount): array
    {
        return [
            'total' => $amount,
            'extra' => 0,
            'perExtra' => 0,
            'extraTotal' => 0,
            'sumTotal' => $amount,
        ];
    }

    private static function financeNewFormula(?string $type): array
    {
        if ($type === CommonSlug::PROF_RANK) {
            return [
                [1, 25, 80000],
                [26, 50, 90000],
                [51, 100, 100000],
                [101, 150, 120000],
                [151, 200, 140000],
                [201, 250, 175000],
                [251, 50000, 200000],
            ];
        }
        return [
            [1, 25, 50000],
            [26, 50, 70000],
            [51, 100, 90000],
            [101, 150, 100000],
            [151, 200, 120000],
            [201, 250, 150000],
            [251, 50000, 180000],
        ];
    }

    public static function calcTutorAmount($total, $paper = false, $cadre = null): ?array
    {
        return ($cadre) ? self::calcNewTutorAmount($total, $cadre) : self::calcOldTutorAmount($total, $paper);
    }

    private static function calcNewTutorAmount($total, $cadre = null)
    {
        if ($total == '0') {
            return self::zeroTotalValue();
        }

        $range = self::financeNewFormula($cadre);
        $perHead = 0;
        $extraTotal = 0;
        $newTotal = 0;

        foreach ($range as $val) {
            $min = $val[0];
            $max = $val[1];
            $sumTotal = $val[2];
            $lastAmount = $val[2];

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

    private static function calcOldTutorAmount($total, $paper = false)
    {
        if ($total == '0') {
            return self::zeroTotalValue();
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
        } else if ($total > $totalLimit && !$paper) {
            $extraCbt = false; // I was told to allow cbt work like written[23-04-2025]
            $extraPaper = true;
            $perHead = 100;
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

    public static function calcProposedTutorAmount($total)
    {
        if ($total == '0') {
            return self::zeroTotalValue();
        }

        $range = self::financeProposedFormula();
        $perHead = 0;
        $extraTotal = 0;
        $newTotal = 0;

        foreach ($range as $val) {
            $min = $val[0];
            $max = $val[1];
            $sumTotal = $val[2];
            $lastAmount = $val[2];

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

    public static function calcFacilitation($cadre = null): array
    {
        return ($cadre) ? self::calcFacilitationNew($cadre) : self::calcFacilitationOld();
    }

    private static function calcFacilitationNew(?string $type): array
    {
        if ($type === CommonSlug::PROF_RANK) {
            return self::totalAmountPayload(30000);
        }
        return self::totalAmountPayload(30000);
    }

    public static function calcFacilitationOld(): array
    {
        return self::totalAmountPayload(50000);
    }

    public static function calcInteraction($cadre = null): array
    {
        return ($cadre) ? self::calcInteractionNew($cadre) : self::calcInteractionOld();
    }

    public static function calcInteractionNew(?string $type): array
    {
        if ($type === CommonSlug::PROF_RANK) {
            return self::totalAmountPayload(10000);
        }
        return self::totalAmountPayload(10000);
    }

    public static function calcInteractionOld(): array
    {
        return self::totalAmountPayload(10000);
    }

    public static function calcInteractionProposed(): array
    {
        return self::totalAmountPayload(50000);
    }

    public static function calcDataAllowance($amount = null): array
    {
        $amount = $amount ?: 10000;
        return self::totalAmountPayload($amount);
    }

    public static function calcWebinarWorkLoadAllowance($total, $perLearner = 100): array
    {
        if ($total == '0') {
            return self::zeroTotalValue();
        }

        $amount = $perLearner * $total;
        return self::totalAmountPayload($amount);
    }

    public static function calcExamType(string $type, $isOldRegime = false): array
    {
        if ($isOldRegime) {
            return self::totalAmountPayload(10000);
        }

        if ($type === ClaimType::EXAM_CBT) {
            return self::totalAmountPayload(12500);
        }
        return self::totalAmountPayload(12500);
    }

    public static function calcDepartmentalRunningCost($total): array
    {
        if ($total == '0') {
            return self::zeroTotalValue();
        }

        $range = self::financeDepartmentalFormula();
        $perHead = 0;
        $extraTotal = 0;
        $newTotal = 0;

        foreach ($range as $val) {
            $min = $val[0];
            $max = $val[1];
            $sumTotal = $val[2];
            $lastAmount = $val[2];

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

    public static function calcCourseCommittee(): array
    {
        return self::totalAmountPayload(50000);
    }

    public static function calcCourseCBT(): array
    {
        return self::totalAmountPayload(25000);
    }

    public static function onlineAssessmentPercentage(): array
    {
        return [
            [
                'id' => 'online_interaction_with_course_guide',
                'label' => 'Online Interaction with Course Guide (in-person)',
                'value' => '0.2'
            ],
            [
                'id' => 'lms_continuous_assessments_with_rubrics',
                'label' => 'Continuous Assessments with Rubrics',
                'value' => '0.2'
            ],
            [
                'id' => 'examinations_with_marking_guide',
                'label' => 'Examinations with Marking Guide',
                'value' => '0.6'
            ]
        ];
    }

    public static function formatOnlineAssessmentPercentage($courseManager = null): string
    {
        $percentage = self::onlineAssessmentPercentage();
        $string = '<table style="width: 100%; border-collapse: collapse;margin-bottom: 10px;">';
        $essentialData = $courseManager ? json_decode($courseManager['waiver'], true) : [];
        foreach ($percentage as $val) {
            $per = $val['value'] * 100;
            $isWaiver = false;
            $label = '(---)';
            if (isset($essentialData[$val['id']]) && $essentialData[$val['id']] == CourseManagerStatus::WAIVER) {
                $isWaiver = true;
                $label = '(waiver)';
            } else if (isset($essentialData[$val['id']]) && $essentialData[$val['id']] == CourseManagerStatus::UNQUALIFIED) {
                $isWaiver = true;
            } else if (isset($essentialData[$val['id']]) && $essentialData[$val['id']] == CourseManagerStatus::APPROVED) {
                $label = '';
                $isWaiver = true;
            }
            $rightPadding = $isWaiver ? "0" : "10px";
            $string .= '<tr>';
            $string .= '<td style="width: 70%; text-align: left; padding: 0;"> - ' . $val['label'] . '</td>';
            $string .= "<td style='width: 15%; text-align: right; padding: 0;padding-right: {$rightPadding};'>" . $per . "%</td>";
            if ($isWaiver) {
                $string .= '<td style="width: 15%; text-align: left; padding: 0;padding-left:10px;">' . $label . '</td>';
            }
            $string .= '</tr>';
        }
        $string .= '</table>';
        return $string;
    }

    public static function calcCourseMaterial($isDiploma): array
    {
        return $isDiploma ? self::totalAmountPayload(150000) : self::totalAmountPayload(300000);
    }

    public static function calcCourseRevision($isDiploma): array
    {
        return $isDiploma ? self::totalAmountPayload(75000) : self::totalAmountPayload(150000);
    }

    public static function calcLogisticsAllowance(): array
    {
        return self::totalAmountPayload(50000);
    }

}