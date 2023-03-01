<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class FileUploadService
{

    public function downloadFileFromUrlAndUploadIt(string $fileUrl, string $path, string $name): string
    {
        $downloadedFile = file_get_contents($fileUrl);
        $fileExtension = pathinfo($fileUrl, PATHINFO_EXTENSION);

        Storage::disk(env('FILESYSTEM_DISK'))->put(
            $path . '/' . $name . '.' . $fileExtension,
            $downloadedFile,
            'public'
        );

        return Storage::disk(env('FILESYSTEM_DISK'))->url($path . '/' . $name . '.' . $fileExtension);
    }
}
