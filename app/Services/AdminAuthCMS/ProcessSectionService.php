<?php

namespace App\Services\AdminAuthCMS;

use App\Models\Media;
use App\Models\ProcessSection;
use App\Models\ProcessStep;
use App\Traits\UploadImage;
use Illuminate\Support\Facades\Storage;

class ProcessSectionService
{
    use UploadImage;

    /*
    |--------------------------------------------------------------------------
    | GET
    |--------------------------------------------------------------------------
    */
    public function getProcessSection()
    {
        $section = ProcessSection::with(['image', 'steps'])
            ->first();

        if (! $section) {
            return [
                'data' => null,
                'message' => 'Process section not found',
                'code' => 404,
            ];
        }

        return [
            'data' => $section,
            'message' => 'Process section retrieved successfully',
            'code' => 200,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE (Create if not exists + Sync Steps)
    |--------------------------------------------------------------------------
    */
    public function updateProcessSection($request)
    {
        $section = ProcessSection::first();

        if (! $section) {
            return [
                'data' => null,
                'message' => 'Process section not found',
                'code' => 404,
            ];
        }
        if ($request->hasFile('image')) {

            $upload = $this->uploadImage($request, 'process', 'image');

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
        | Case 2: Admin Sends image_media_id (Switch Existing Media)
        |--------------------------------------------------------------------------
        */
        elseif ($request->filled('image_media_id')) {

            // Optional: validate it exists
            $media = Media::find($request->image_media_id);

            if (! $media) {
                return [
                    'data' => null,
                    'message' => 'Selected image not found',
                    'code' => 404,
                ];
            }

            $section->image_media_id = $media->id;
        }

        $section->save();

        $section->update([
            'title' => $request->title,

        ]);

        /*
        |--------------------------------------------------------------------------
        | Sync Steps
        |--------------------------------------------------------------------------
        */

        ProcessStep::truncate();
        foreach ($request->steps as $step) {
            ProcessStep::create([
                'process_section_id' => $section->id,
                'title' => $step['title'],
                'description' => $step['description'] ?? null,
                'sort_order' => $step['sort_order'] ?? 0,
            ]);

        }

        return [
            'data' => $section->load(['image', 'steps']),
            'message' => 'Process section updated successfully',
            'code' => 200,
        ];
    }
}
