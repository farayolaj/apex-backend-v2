<?php

namespace App\Controllers\Admin\V1;

use App\Controllers\BaseController;
use App\Enums\CommonEnum;
use App\Libraries\ApiResponse;
use App\Models\WebSessionManager;
use App\Services\Admin\AdmissionService;

class AdmissionController extends BaseController
{
    private AdmissionService $admission;

    public function __construct()
    {
        $this->admission = service('admission');
    }

    public function singleAdmission()
    {
        $payload = requestPayload();

        $rules = [
            'programme'       => 'required|is_natural_no_zero',
            'adminStatus'     => 'required|string',
            'entryMode'       => 'required|string',
            'applicant_id'    => 'required|string',
            'applicant_type'  => 'required|in_list[applicant_post_utme,applicants]',
            'teaching_subject'=> 'permit_empty|string'
        ];

        if (!$this->validate($rules)) {
            $error = $this->validator->getErrors();
            return ApiResponse::error(reset($error));
        }

        try {
            $result = $this->admission->admitSingle([
                'programme'       => (int)$payload['programme'],
                'adminStatus'     => (string)$payload['adminStatus'],
                'entryMode'       => trim((string)$payload['entryMode']),
                'applicant_id'    => trim((string)$payload['applicant_id']),
                'teaching_subject'=> $payload['teaching_subject'] ?? null,
                'entity'          => $payload['applicant_type'] ?? 'applicants',
            ]);

            return ApiResponse::success($result['message'], $result['data']);
        } catch (\DomainException $e) {
            return ApiResponse::error($e->getMessage());
        } catch (\Throwable $e) {
            log_message('error', 'Admission error: {msg}', ['msg' => $e->getMessage()]);
            return ApiResponse::error('Something went wrong, please try again later', null, 500);
        }
    }

    public function uploadAdmission()
    {
        try {
            $file = $this->request->getFile('admissionList');
            if (! $file || ! $file->isValid()) {
                return ApiResponse::error('Upload a valid CSV file');
            }
            if (strtolower($file->getClientExtension()) !== 'csv') {
                return ApiResponse::error('Only CSV is allowed');
            }

            $headerMap = [
                'applicant_id'     => 'applicant_id',
                'programme'        => 'programme',
                'entry'            => 'entry',
                'level'            => 'admitted_level',
                'duration'         => 'programme_duration',
                'teaching_subject' => 'teaching_subject',
            ];

            [$ok, $reports, $stats] = $this->admission->admitBulkFromCsv($file, $headerMap);

            return ApiResponse::success($ok ? 'Admission successfully uploaded' : 'Processed with warnings', [
                'stats' => $stats,
                'data'    => $reports,
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Bulk admission error: {msg}', ['msg' => $e->getMessage()]);
            return ApiResponse::error('Failed to process file', null,500);
        }
    }


}