<?php

namespace App\Traits;

trait UploadTrait
{
    public static function loadUploadedFileContent($filename = null, $filePath = false, &$message = null, string $extension = 'csv'): bool|string
    {
        $filename = $filename ?? 'bulk-upload';
        $status = self::checkFile($filename, $message);
        if (!$status) {
            return false;
        }

        if (!endsWith($_FILES[$filename]['name'], ".{$extension}")) {
            $message = "Invalid file format";
            return false;
        }
        $path = $_FILES[$filename]['tmp_name'];
        $content = file_get_contents($path);
        if ($filePath) {
            $res = move_uploaded_file($_FILES[$filename]['tmp_name'], $filePath);
            if (!$res) {
                $message = "Error occured while performing file upload";
            }
        }
        return $content;
    }

    private static function checkFile(string $name, &$message = ''): bool
    {
        $error = !$_FILES[$name]['name'] || $_FILES[$name]['error'];
        if ($error) {
            if ((int)$error === 2) {
                $message = 'File larger than expected';
                return false;
            }
            $message = "Please do check the file [{$_FILES[$name]['name']}] complies with requirement";
            return false;
        }
        if (!is_uploaded_file($_FILES[$name]['tmp_name'])) {
            $message = 'Uploaded file not found';
            return false;
        }
        return true;
    }

}