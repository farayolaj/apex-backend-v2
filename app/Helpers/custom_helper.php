<?php

if(!function_exists('generateDownloadLink')){
    function generateDownloadLink(string $filename, string $folder = 'temp/download_passport', string $downloadRoute = 'direct_link', bool $removeOldFiles = true): string
    {
        $originalName = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $fid = rndEncode($originalName, 32);
        $ex = base64_encode($extension);
        $hid = hash('sha512', $originalName);
        $key = uniqid(time() . '-key', TRUE);

        if ($removeOldFiles) {
            removeOldExportFiles(FCPATH . $folder);
        }
        return base_url("download/{$downloadRoute}?fid={$fid}&ex={$ex}&hid={$hid}&key={$key}");
    }
}

if(!function_exists('removeOldExportFiles')){
    function removeOldExportFiles(string $folder): void
    {
        // remove files older than 2hours
        $files = glob("$folder/*"); // get all file names
        foreach ($files as $file) {
            $lastModifiedTime = filemtime($file);
            $currentTime = time();
            $timeDiff = abs($currentTime - $lastModifiedTime) / (3600); // in hours
            if (is_file($file) && $timeDiff > 2) // check if file not modified after 2hours
            {
                unlink($file);
            }
        }
    }
}




