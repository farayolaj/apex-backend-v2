<?php

namespace App\Controllers\Admin\V1;

use App\Controllers\BaseController;
use App\Libraries\ApiResponse;
use App\Models\WebSessionManager;
use ZipArchive;

class PhotoManagerController extends BaseController
{
    /**
     * @param mixed $value
     * @return void
     */
    public function photos()
    {
        $len   = (int) ($this->request->getGet('len') ?? 50);
        $start = (int) ($this->request->getGet('start') ?? 0);

        $pageSize = max(1, min($len, 200));
        $offset   = max(0, $start);

        $filters = [
            'target'     => $this->request->getGet('target') ?? 'student',
            'session'    => $this->request->getGet('session'),
            'level'      => $this->request->getGet('level'),
            'programme'  => $this->request->getGet('programme'),
            'course'     => $this->request->getGet('course'),
            'entry_year' => $this->request->getGet('entry_year'),
            'department' => $this->request->getGet('department'),
            'q'          => $this->request->getGet('q'),
        ];

        $model = model('Admin/PhotoModel');
        $result = $model->getPhotos($filters, $offset, $pageSize, true);

        return ApiResponse::success('success', [
            'paging'     => $result['total'],
            'table_data' => $result['data'],
        ]);
    }

    public function photoDownload(){
        $data  = $this->request->getJSON(true) ?? [];
        $filters = $data['filter'] ?? [];
        $selected = $data['selected'] ?? [];
        $target   = strtolower((string) ($data['target'] ?? 'student'));

        $model = model('Admin/PhotoModel');

        if (is_array($selected) && count($selected) > 0) {
            $paths = $model->listFilesByMatric($selected, $target);
        } elseif (is_array($filters) && count($filters) > 0) {
            $paths = $model->listFilesByFilter(array_merge($filters, ['target' => $target]));
        } else {
            $paths = [];
        }

        if (empty($paths)) {
            return ApiResponse::error('No data found');
        }

        helper(['filesystem']);

        $destPath = WRITEPATH . 'temp/download_passport';
        if (!is_dir($destPath)) {
            mkdir($destPath, 0775, true);
        }
        // cleanup old zips
        delete_files($destPath . '/');
        $currentUser = WebSessionManager::currentAPIUser();
        $zipName = $currentUser->firstname . '_' . $currentUser->lastname . '_students_passports.zip';
        $zipPath = $destPath . '/' . $zipName;

        $zip = new ZipArchive();
        $ok  = $zip->open($zipPath, ZipArchive::OVERWRITE);
        if ($ok !== true) {
            $zip->open($zipPath, ZipArchive::CREATE);
        }

        $total = 0;
        foreach ($paths as $row) {
            $passport = trim((string) ($row['passport'] ?? ''));
            if ($passport === '') {
                continue;
            }
            $full = studentImagePathDirectory($passport);
            if (!is_file($full)) {
                continue;
            }
            $ext = pathinfo($full, PATHINFO_EXTENSION) ?: 'jpg';
            $zip->addFile($full, ($row['matric_number'] ?? ('student_' . $total)) . '.' . $ext);
            $total++;
        }
        $zip->close();

        if ($total <= 0) {
            @unlink($zipPath);
            return ApiResponse::error('No passport data found');
        }

        $exportLink = generateDownloadLink($zipPath, 'temp/download_passport', 'direct_link_passport');
        return ApiResponse::success('Please do click/copy the link for download', [
            'export_link' => $exportLink
        ]);
    }
}