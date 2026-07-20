<?php

namespace App\Services;

use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\UploadedFile;

class CloudinaryImageService
{
    public function upload(UploadedFile $file, string $folder): array
    {
        $baseFolder = trim((string) env('CLOUDINARY_UPLOAD_FOLDER', 'calaia'), '/');
        $targetFolder = trim($baseFolder.'/'.$folder, '/');
        $result = Cloudinary::uploadApi()->upload($file->getRealPath(), [
            'folder' => $targetFolder,
            'resource_type' => 'image',
        ]);

        return [
            'url' => $result['secure_url'],
            'public_id' => $result['public_id'],
            'width' => $result['width'] ?? null,
            'height' => $result['height'] ?? null,
        ];
    }

    public function destroy(?string $publicId): void
    {
        if (filled($publicId)) {
            Cloudinary::uploadApi()->destroy($publicId, ['resource_type' => 'image']);
        }
    }
}
