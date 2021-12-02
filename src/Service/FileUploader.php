<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Uid\Uuid;

final class FileUploader
{
    public function upload(UploadedFile $file, string $path): string
    {
        $filename = Uuid::v4() . '.' . $file->guessExtension();

        try {
            $file->move($path, $filename);
        } catch (FileException $e) {
            // TODO
        }

        return $filename;
    }
}