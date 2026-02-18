<?php

namespace App\Services\AdminAuthCMS;

use App\Models\AboutSection;
use App\Models\Media;
use App\Traits\UploadImage;
use Illuminate\Support\Facades\Storage;

class AboutSectionService
{
    use UploadImage;

    /*
    |--------------------------------------------------------------------------
    | GET
    |--------------------------------------------------------------------------
    */
    public function getAboutSection()
    {
        $section = AboutSection::with('image')->first();

        if (! $section) {
            return [
                'data' => null,
                'message' => 'No about section found',
                'code' => 404,
            ];
        }

        return [
            'data' => $section,
            'message' => 'About section retrieved successfully',
            'code' => 200,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE (Create if not exists)
    |--------------------------------------------------------------------------
    */
    public function updateAboutSection($request)
    {
        $section = AboutSection::first();

        if (! $section) {
            return [
                'data' => null,
                'message' => 'No about section found',
                'code' => 404,
            ];
        }
        if ($request->hasFile('image')) {

            $upload = $this->uploadImage($request, 'about', 'image');

            if (! $upload['success']) {
                return [
                    'data' => null,
                    'message' => $upload['message'],
                    'code' => 400,
                ];
            }

            $newPath = $upload['data'];

            $filePath = storage_path('app/public/'.$newPath);
            $imageSize = getimagesize($filePath);

            /*
            |--------------------------------------------------------------------------
            | If Media Exists → Update Same Media ID
            |--------------------------------------------------------------------------
            */
            if ($section->image) {

                // delete old physical file
                if (Storage::disk('public')->exists($section->image->path)) {
                    Storage::disk('public')->delete($section->image->path);
                }

                $section->image->update([
                    'path' => $newPath,
                    'mime_type' => mime_content_type($filePath),
                    'size_bytes' => filesize($filePath),
                    'width' => $imageSize[0] ?? null,
                    'height' => $imageSize[1] ?? null,
                    'alt_text' => $request->alt_text ?? $section->image->alt_text,
                    'title' => $request->image_title ?? $section->image->title,
                ]);

            } else {
                /*
                |--------------------------------------------------------------------------
                | First Time Image
                |--------------------------------------------------------------------------
                */
                $media = Media::create([
                    'path' => $newPath,
                    'type' => 'image',
                    'mime_type' => mime_content_type($filePath),
                    'size_bytes' => filesize($filePath),
                    'width' => $imageSize[0] ?? null,
                    'height' => $imageSize[1] ?? null,
                    'alt_text' => $request->alt_text ?? 'About image',
                    'title' => $request->image_title ?? 'About image',
                ]);

                $section->image_media_id = $media->id;
            }
        } elseif ($section->image) {
            /*
            |--------------------------------------------------------------------------
            | Update Only Media Meta (No New File)
            |--------------------------------------------------------------------------
            */
            $section->image->update([
                'alt_text' => $request->alt_text ?? $section->image->alt_text,
                'title' => $request->image_title ?? $section->image->title,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Update Text Fields
        |--------------------------------------------------------------------------
        */
        $section->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return [
            'data' => $section->load('image'),
            'message' => 'About section updated successfully',
            'code' => 200,
        ];
    }
}
