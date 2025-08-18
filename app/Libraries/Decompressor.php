<?php

namespace App\Libraries;

use RarArchive;
use ZipArchive;

class Decompressor {

    private array $error = [];

    private ?array $allowedExtensions = null;

    public function set_error($string): void
    {
        $this->error[] = $string;
    }

    public function get_error(): array
    {
        return $this->error;
    }

    public function allow($ext = NULL): void
    {
        $this->allowedExtensions = $ext;
    }

    public function extract(string $archive, string $extension, string $destination): ?bool
    {
        $res = null;
        switch ($extension) {
            case 'zip':
                $res = $this->extractZipArchive($archive, $destination);
                break;
            case 'gz':
                $res = $this->extractGzipFile($archive, $destination);
                break;
            case 'rar':
                $res = $this->extractRarArchive($archive, $destination);
                break;
        }
        return $res;
    }

    public function extractZipArchive(string $archive, string $destination): bool
    {
        // Check if webserver supports unzipping.
        if (!class_exists('ZipArchive')) {
            $this->set_error('Your PHP version does not support unzip functionality.');
            return false;
        }

        $zip = new ZipArchive;
        // Check if archive is readable.
        if ($zip->open($archive) === TRUE) {
            // Check if destination is writable
            if (!is_writable($destination . '/')) {
                $this->set_error('Directory not writeable by webserver.');
                return false;
            }

            $fileCount = $zip->count();
            $allowedFiles = [];
            for ($i = 0; $i < $fileCount; $i++) {
                $fileName = $zip->getNameIndex($i);

                // Skip any files that are not allowed
                $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                if (is_array($this->allowedExtensions) AND $extension AND !in_array($extension, $this->allowedExtensions)) {
                    continue;
                }

                if ($extension) {
                    $fp = $zip->getStream($fileName);
                    $ofp = fopen($destination . '/' . basename($fileName), 'w');
                    if (!$fp) {
                        $this->set_error("Unable to extract the file '{$fileName}'.");
                        continue;
                    }
                    while (!feof($fp)) {
                        fwrite($ofp, fread($fp, 8192));
                    }
                    fclose($fp);
                    fclose($ofp);
                }
            }

            $zip->close();
            return true;

        } else {
            $this->set_error('Cannot read .zip archive.');
            return false;
        }
    }

    public function extractGzipFile(string $archive, string $destination): bool
    {
        // Check if zlib is enabled
        if (!function_exists('gzopen')) {
            $this->set_error('Error: Your PHP has no zlib support enabled.');
            return false;
        }

        $filename = pathinfo($archive, PATHINFO_FILENAME);
        $gzipped = gzopen($archive, "rb");

        $file = fopen($filename, "w");

        while ($string = gzread($gzipped, 4096)) {
            fwrite($file, $string, strlen($string));
        }
        gzclose($gzipped);

        fclose($file);

        // Check if file was extracted.
        if (file_exists($destination . '/' . $filename)) {
            return true;
        } else {
            $this->set_error('Error unzipping file.');
            return false;
        }
    }

    public function extractRarArchive(string $archive, string $destination): bool
    {
        // Check if webserver supports unzipping.
        if (!class_exists('RarArchive')) {
            $this->set_error('Your PHP version does not support .rar archive functionality.');
            return false;
        }
        // Check if archive is readable.
        if ($rar = RarArchive::open($archive)) {

            // Check if destination is writable
            if (is_writeable($destination . '/')) {
                $entries = $rar->getEntries();
                foreach ($entries as $entry) {
                    $entry->extract($destination);
                }
                $rar->close();
                return true;
            } else {
                $this->set_error('Directory not writeable by webserver.');
                return false;
            }
        } else {
            $this->set_error('Cannot read .rar archive.');
            return false;
        }
    }

}