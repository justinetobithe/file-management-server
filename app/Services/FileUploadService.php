<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class FileUploadService
{
    public function uploadFiles($model, $files, $file_type = 'uploaded_files')
    {
        foreach ($files as $file) {
            $path = $file->store('public/uploads');

            $pathInDb = str_replace('public/', '', $path);

            $model->fileUploads()->create([
                'filename' => $file->getClientOriginalName(),
                'path' => $pathInDb,
                'file_type' => $file_type,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }
    }

    public function deleteFiles($model, $deletedFiles)
    {
        if (!empty($deletedFiles)) {
            $model->fileUploads()->whereIn('id', $deletedFiles)->delete();

            $paths = $model->fileUploads()->whereIn('id', $deletedFiles)->pluck('path')->toArray();

            foreach ($paths as $path) {
                $fullPath = 'public/' . $path;
                Storage::delete($fullPath);
            }
        }
    }
}
