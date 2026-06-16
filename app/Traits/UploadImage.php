<?php

namespace App\Traits;

trait UploadImage
{
    public function uploadImage($request, $folderName, $requestFile)
    {

        if ($request->hasFile($requestFile)) {
            $file = $request->file($requestFile);
            $original = $file->getClientOriginalName();
            $ext = $file->getClientOriginalExtension();
            // Sanitize the base name and prepend a unique hash so concurrent uploads
            // of files with the same name don't overwrite each other (which previously
            // caused 403/404 on assets after a sibling project deleted the shared file).
            $baseName = pathinfo($original, PATHINFO_FILENAME);
            $safeBase = preg_replace('/[^A-Za-z0-9_-]+/', '-', $baseName);
            $safeBase = trim($safeBase, '-') ?: 'file';
            $unique = bin2hex(random_bytes(8));
            $filename = $ext
                ? "{$safeBase}-{$unique}.{$ext}"
                : "{$safeBase}-{$unique}";
            $path = $file->storeAs($folderName, $filename, 'public');

            return ['success' => true, 'data' => $path];  // Changed to return an array
        } else {
            return ['success' => false, 'message' => 'No image provided', 'data' => null];  // Corrected the return format
        }
    }
}
