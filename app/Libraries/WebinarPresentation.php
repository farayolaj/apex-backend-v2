<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;

class WebinarPresentation
{
  static string $PRESENTATION_DIR = 'presentations/';

  private string $presentationId;
  private string $presentationName;

  public function __construct(UploadedFile $file)
  {
    $this->presentationId = bin2hex(random_bytes(16)) . '.' . $file->getExtension();
    $this->presentationName = $file->getName();
    $file->store(self::$PRESENTATION_DIR, $this->presentationId);
  }

  public function getId(): string
  {
    return $this->presentationId;
  }

  public function getName(): string
  {
    return $this->presentationName;
  }

  public static function getFilePath(string $presentationId): string
  {
    return WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . self::$PRESENTATION_DIR . $presentationId;
  }

  public static function getPublicUrl(string $hostName, string $webinarId): string
  {
    $hostName = rtrim($hostName, '/');
    return $hostName . '/v1/webinars/' . $webinarId . '/presentations';
  }

  public static function deletePresentation(string $presentationId): void
  {
    $filePath = self::getFilePath($presentationId);

    if (file_exists($filePath)) {
      unlink($filePath);
    }
  }
}
