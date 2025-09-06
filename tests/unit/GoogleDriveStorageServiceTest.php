<?php

use App\Services\GoogleDriveStorageService;
use CodeIgniter\Test\CIUnitTestCase;
use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Google\Service\Drive\Permission;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test case for GoogleDriveStorageService
 * 
 * @internal
 */
final class GoogleDriveStorageServiceTest extends CIUnitTestCase
{
    private $service;
    private MockObject $mockClient;
    private MockObject $mockDriveService;
    private MockObject $mockFilesResource;
    private MockObject $mockPermissionsResource;

    protected function setUp(): void
    {
        parent::setUp();

        // Create mocks for Google API components
        $this->mockClient = $this->createMock(Client::class);
        $this->mockDriveService = $this->createMock(Drive::class);
        $this->mockFilesResource = $this->createMock(Drive\Resource\Files::class);
        $this->mockPermissionsResource = $this->createMock(Drive\Resource\Permissions::class);

        // Configure the mock drive service
        $this->mockDriveService->files = $this->mockFilesResource;
        $this->mockDriveService->permissions = $this->mockPermissionsResource;

        // Create a partial mock of GoogleDriveStorageService to override constructor behavior
        $this->service = $this->getMockBuilder(GoogleDriveStorageService::class)
            ->onlyMethods([])
            ->getMock();

        // Use reflection to set the protected properties
        $reflection = new ReflectionClass($this->service);

        $clientProperty = $reflection->getProperty('client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($this->service, $this->mockClient);

        $serviceProperty = $reflection->getProperty('service');
        $serviceProperty->setAccessible(true);
        $serviceProperty->setValue($this->service, $this->mockDriveService);

        $driveIdProperty = $reflection->getProperty('defaultSharedDriveId');
        $driveIdProperty->setAccessible(true);
        $driveIdProperty->setValue($this->service, '0ADcNOKrBiNpeUk9PVA');
    }

    public function testUploadFileSuccess(): void
    {
        // Create a temporary test file
        $testFilePath = tempnam(sys_get_temp_dir(), 'test_upload');
        file_put_contents($testFilePath, 'Test file content');

        // Mock the DriveFile response
        $mockFile = $this->createMock(DriveFile::class);
        $mockFile->method('getId')->willReturn('test_file_id_123');

        // Configure the files resource mock
        $this->mockFilesResource
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->callback(function ($fileMetadata) {
                    return $fileMetadata instanceof DriveFile &&
                        $fileMetadata->getName() === 'test_document.pdf' &&
                        $fileMetadata->getParents() === ['0ADcNOKrBiNpeUk9PVA'];
                }),
                $this->callback(function ($optParams) {
                    return $optParams['data'] === 'Test file content' &&
                        $optParams['mimeType'] === 'application/pdf' &&
                        $optParams['uploadType'] === 'multipart' &&
                        $optParams['fields'] === 'id' &&
                        $optParams['supportsAllDrives'] === true;
                })
            )
            ->willReturn($mockFile);

        // Configure the permissions resource mock for making file public
        $this->mockPermissionsResource
            ->expects($this->once())
            ->method('create')
            ->with(
                'test_file_id_123',
                $this->callback(function ($permission) {
                    return $permission instanceof Permission &&
                        $permission->getType() === 'anyone' &&
                        $permission->getRole() === 'reader';
                }),
                ['supportsAllDrives' => true]
            );

        // Test the upload
        $fileId = $this->service->uploadFile($testFilePath, 'application/pdf', 'test_document.pdf');

        // Assertions
        $this->assertEquals('test_file_id_123', $fileId);

        // Clean up
        unlink($testFilePath);
    }

    public function testUploadFileWithDefaultName(): void
    {
        // Create a temporary test file with a specific name
        $testFilePath = tempnam(sys_get_temp_dir(), 'test_upload');
        rename($testFilePath, $testFilePath . '.pdf');
        $testFilePath = $testFilePath . '.pdf';
        file_put_contents($testFilePath, 'Test file content');

        // Mock the DriveFile response
        $mockFile = $this->createMock(DriveFile::class);
        $mockFile->method('getId')->willReturn('test_file_id_456');

        // Configure the files resource mock
        $this->mockFilesResource
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->callback(function ($fileMetadata) use ($testFilePath) {
                    return $fileMetadata instanceof DriveFile &&
                        $fileMetadata->getName() === basename($testFilePath);
                }),
                $this->anything()
            )
            ->willReturn($mockFile);

        // Configure the permissions resource mock
        $this->mockPermissionsResource
            ->expects($this->once())
            ->method('create');

        // Test the upload without custom name
        $fileId = $this->service->uploadFile($testFilePath, 'application/pdf');

        // Assertions
        $this->assertEquals('test_file_id_456', $fileId);

        // Clean up
        unlink($testFilePath);
    }

    public function testUploadFileNotFound(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('File not found: /nonexistent/file.pdf');

        $this->service->uploadFile('/nonexistent/file.pdf', 'application/pdf', 'test.pdf');
    }

    public function testUploadFileApiException(): void
    {
        // Create a temporary test file
        $testFilePath = tempnam(sys_get_temp_dir(), 'test_upload');
        file_put_contents($testFilePath, 'Test file content');

        // Configure the files resource mock to throw an exception
        $this->mockFilesResource
            ->expects($this->once())
            ->method('create')
            ->willThrowException(new Exception('API Error'));

        $this->expectException(Exception::class);

        try {
            $this->service->uploadFile($testFilePath, 'application/pdf', 'test.pdf');
        } finally {
            // Clean up
            unlink($testFilePath);
        }
    }

    public function testDeleteFileSuccess(): void
    {
        // Configure the files resource mock
        $this->mockFilesResource
            ->expects($this->once())
            ->method('delete')
            ->with('test_file_id_123', ['supportsAllDrives' => true]);

        // Test the deletion
        $result = $this->service->deleteFile('test_file_id_123');

        // Assertions
        $this->assertTrue($result);
    }

    public function testDeleteFileException(): void
    {
        // Configure the files resource mock to throw an exception
        $this->mockFilesResource
            ->expects($this->once())
            ->method('delete')
            ->willThrowException(new Exception('Delete failed'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to delete file: Delete failed');

        $this->service->deleteFile('test_file_id_123');
    }

    public function testGetPublicUrl(): void
    {
        $fileId = 'test_file_id_123';
        $expectedUrl = "https://drive.google.com/file/d/{$fileId}/view";

        $actualUrl = $this->service->getPublicUrl($fileId);

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function testMakeFilePublicException(): void
    {
        // Create a temporary test file
        $testFilePath = tempnam(sys_get_temp_dir(), 'test_upload');
        file_put_contents($testFilePath, 'Test file content');

        // Mock the DriveFile response
        $mockFile = $this->createMock(DriveFile::class);
        $mockFile->method('getId')->willReturn('test_file_id_123');

        // Configure the files resource mock to succeed
        $this->mockFilesResource
            ->expects($this->once())
            ->method('create')
            ->willReturn($mockFile);

        // Configure the permissions resource mock to throw an exception
        $this->mockPermissionsResource
            ->expects($this->once())
            ->method('create')
            ->willThrowException(new Exception('Permission failed'));

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to make file public: Permission failed');

        try {
            $this->service->uploadFile($testFilePath, 'application/pdf', 'test.pdf');
        } finally {
            // Clean up
            unlink($testFilePath);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up any remaining temporary files
        $tempDir = sys_get_temp_dir();
        $files = glob($tempDir . '/test_upload*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
