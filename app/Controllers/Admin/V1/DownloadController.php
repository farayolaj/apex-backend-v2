<?php

namespace App\Controllers\Admin\V1;

use App\Controllers\BaseController;
use App\Libraries\EntityLoader;
use ZipArchive;

class DownloadController extends BaseController
{

    public function directDownloadLink()
    {
        return $this->streamSignedFile('temp/export', true, 15);
    }

    public function directDownloadLinkLogs()
    {
        return $this->streamSignedFile('temp/logs', false, 60);
    }

    public function directDownloadLinkPassport()
    {
        return $this->streamSignedFile('temp/download_passport', true, 15);
    }

    /**
     * Validate signed params, stream the file, optionally delete after send.
     */
    private function streamSignedFile(string $folder, bool $unlink = true, int $ttlMinutes = 15)
    {
        $fid = $this->request->getGet('fid');
        $hid = $this->request->getGet('hid');
        $key = $this->request->getGet('key');
        $ex = $this->request->getGet('ex');

        $safeBasename = $this->validateLink($fid, $ex, $hid, $key, $ttlMinutes);
        if ($safeBasename === null) {
            return $this->response->setStatusCode(410)->setBody('Oops, invalid or expired link.');
        }

        $path = WRITEPATH . trim($folder, '/') . '/' . $safeBasename;
        if (!is_file($path)) {
            return $this->response->setStatusCode(404)->setBody('File not found.');
        }

        if ($unlink) {
            register_shutdown_function(static function ($p) {
                @is_file($p) && @unlink($p);
            }, $path);
        }

        return $this->response->download($path, null);
    }

    /**
     * Returns "name.ext" if valid, otherwise null.
     */
    private function validateLink(?string $fid, ?string $ex, ?string $hash, ?string $timeToken, int $ttlMinutes): ?string
    {
        if (!$fid || !$ex || !$hash || !$timeToken) {
            return null;
        }

        $decodedName = rndDecode(urldecode(trim($fid)), 32);
        $extension = base64_decode(urldecode(trim($ex))) ?: '';
        $hash = urldecode(trim($hash));
        $keyParts = explode('-', urldecode(trim($timeToken)));

        if (!$decodedName || hash('sha512', $decodedName) !== $hash) {
            return null;
        }

        $issuedAt = (int)($keyParts[0] ?? 0);
        $now = time();
        if (isTimePassed($now, $issuedAt, $ttlMinutes)) {
            return null;
        }

        $extension = preg_replace('/[^A-Za-z0-9._-]/', '', $extension);
        return $decodedName . ($extension ? ('.' . $extension) : '');
    }

    public function export_document_complete()
    {
        helper(['filesystem']);

        $destPath = WRITEPATH . 'temp/document_complete';
        if (!is_dir($destPath)) {
            mkdir($destPath, 0775, true);
        }
        delete_files($destPath . '/', false);

        EntityLoader::loadClass($this, 'student_verification_fee');
        $payload = $this->student_verification_fee->getStudentUploadDocumentComplete();

        if (!is_array($payload) || empty($payload)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No matching files found.',
                'data'    => [],
            ]);
        }

        foreach ($payload as $student) {
            $this->copyStudentDocs($student, $destPath);
        }

        $zipName = 'student_document_complete_' . date('Y-m-d-H_i_s') . '.zip';
        $zipPath = $destPath . '/' . $zipName;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Unable to prepare archive.',
                'data' => [],
            ]);
        }

        $added = 0;
        foreach (glob($destPath . '/*.*') as $file) {
            if (is_file($file) && basename($file) !== $zipName) {
                $zip->addFile($file, basename($file));
                $added++;
            }
        }
        $zip->close();

        if ($added === 0) {
            @unlink($zipPath);
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No matching files found.',
                'data' => [],
            ]);
        }

        register_shutdown_function(static function ($p) {
            @is_file($p) && @unlink($p);
        }, $zipPath);

        return $this->response->download($zipPath, null)->setFileName($zipName);
    }

    private function copyStudentDocs(array $student, string $destPath): void
    {
        $uploads = WRITEPATH . 'uploads/verification_documents';
        foreach (glob($uploads . '/*.*') as $file) {
            $nameOnly = pathinfo($file, PATHINFO_FILENAME);
            $basename = pathinfo($file, PATHINFO_BASENAME);
            $studentKey = $student['application_number'] ?? $student['matric_number'] ?? null;

            if (!$studentKey) {
                continue;
            }

            [$prefix] = explode('_', (string)$nameOnly);
            if ($prefix && $prefix == $studentKey) {
                @copy($uploads . '/' . $basename, $destPath . '/' . $basename);
            }
        }
    }

}