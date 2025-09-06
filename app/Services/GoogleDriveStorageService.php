<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\Permission;
use Google\Service\Drive\DriveFile;
use Exception;

/**
 * GoogleDriveStorageService - Generic Google Drive Storage Service
 * 
 * A simplified, generic storage service for Google Drive that handles file uploads and deletions
 * to a shared drive using service account authentication. All uploaded files are made public
 * by default for easy access via URL.
 * 
 * 💡 USAGE EXAMPLES:
 * 
 * Upload File:
 * $storage = new GoogleDriveStorageService();
 * $fileId = $storage->uploadFile('/local/file.pdf', 'application/pdf', 'document.pdf');
 * echo "File URL: " . $storage->getPublicUrl($fileId);
 * 
 * Delete File:
 * $success = $storage->deleteFile($fileId);
 * 
 * 🎯 FEATURES:
 * - Simple file upload to shared drive
 * - Automatic public sharing for URL access
 * - File deletion support
 * - Service account authentication
 */
class GoogleDriveStorageService
{
    protected $client;
    protected $service;
    protected $defaultSharedDriveId;

    /**
     * Initialize GoogleDriveStorageService with service account credentials
     * 
     * Sets up the Google Drive client with service account authentication
     * and configures the default shared drive for all operations.
     */
    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuthConfig('edp-dlc-email-account-c3d9d54387b1.json');
        $this->client->addScope(Drive::DRIVE);
        $this->service = new Drive($this->client);
        $this->defaultSharedDriveId = '0ADcNOKrBiNpeUk9PVA';
    }

    /**
     * Upload a file to the shared Google Drive and make it public
     * 
     * @param string $filePath Local file path
     * @param string $mimeType MIME type of the file
     * @param string|null $name Custom name for the file (optional, defaults to original filename)
     * @return string File ID from Google Drive
     * @throws Exception If upload or sharing fails
     */
    public function uploadFile($filePath, $mimeType, $name = null)
    {
        if (!file_exists($filePath)) {
            throw new Exception("File not found: $filePath");
        }

        $fileMetadata = new DriveFile([
            'name' => $name ?: basename($filePath),
            'parents' => [$this->defaultSharedDriveId]
        ]);

        $content = file_get_contents($filePath);

        $optParams = [
            'data' => $content,
            'mimeType' => $mimeType,
            'uploadType' => 'multipart',
            'fields' => 'id',
            'supportsAllDrives' => true
        ];

        $file = $this->service->files->create($fileMetadata, $optParams);

        // Make the file public
        $this->makeFilePublic($file->getId());

        return $file->getId();
    }

    /**
     * Delete a file from Google Drive
     * 
     * @param string $fileId Google Drive file ID
     * @return bool True if deletion was successful
     * @throws Exception If deletion fails
     */
    public function deleteFile($fileId)
    {
        try {
            // First check if the file exists and get its metadata
            $file = $this->service->files->get($fileId, [
                'supportsAllDrives' => true,
                'fields' => 'id,name,parents,trashed'
            ]);

            // If file is already trashed, consider it deleted
            if ($file->getTrashed()) {
                return true;
            }

            // For shared drive files, we need to handle deletion differently
            $optParams = [
                'supportsAllDrives' => true,
                'supportsTeamDrives' => true // Legacy support
            ];

            // If the file is in a shared drive, we might need to move to trash instead of permanent delete
            $parents = $file->getParents();
            if ($parents && in_array($this->defaultSharedDriveId, $parents)) {
                // For shared drive files, move to trash first
                $this->service->files->update($fileId, new DriveFile(['trashed' => true]), $optParams);
            } else {
                // For regular files, delete directly
                $this->service->files->delete($fileId, $optParams);
            }

            return true;
        } catch (\Google\Service\Exception $e) {
            $error = json_decode($e->getMessage(), true);

            // If file is not found, consider it already deleted
            if ($e->getCode() === 404 || (isset($error['error']['code']) && $error['error']['code'] === 404)) {
                return true;
            }

            // If it's a permission error, provide more helpful message
            if ($e->getCode() === 403) {
                throw new Exception("Permission denied: Service account may not have delete permissions for this file");
            }

            throw new Exception("Failed to delete file: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Failed to delete file: " . $e->getMessage());
        }
    }

    /**
     * Make a file publicly accessible by anyone with the link
     * 
     * @param string $fileId Google Drive file ID
     * @return bool True if sharing was successful
     * @throws Exception If sharing fails
     */
    private function makeFilePublic($fileId)
    {
        try {
            $permission = new Permission([
                'type' => 'anyone',
                'role' => 'reader'
            ]);

            $this->service->permissions->create($fileId, $permission, [
                'supportsAllDrives' => true
            ]);
            return true;
        } catch (Exception $e) {
            throw new Exception("Failed to make file public: " . $e->getMessage());
        }
    }

    /**
     * Get the public URL for a file
     * 
     * @param string $fileId Google Drive file ID
     * @return string Public URL for direct access
     */
    public function getPublicUrl($fileId)
    {
        return "https://drive.google.com/file/d/{$fileId}/view";
    }
}
