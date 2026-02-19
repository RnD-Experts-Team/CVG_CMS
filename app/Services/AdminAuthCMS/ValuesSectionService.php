<?php

namespace App\Services\AdminAuthCMS;

use App\Models\Media;
use App\Models\ValuesItem;
use App\Models\ValuesSection;
use App\Traits\UploadImage;

class ValuesSectionService
{
    use UploadImage;

    /*
    |--------------------------------------------------------------------------
    | GET
    |--------------------------------------------------------------------------
    */
    public function getValuesSection()
    {
        $section = ValuesSection::with(['values.media'])
            ->first();

        if (! $section) {
            return [
                'data' => null,
                'message' => 'Values section not found',
                'code' => 404,
            ];
        }

        return [
            'data' => $section,
            'message' => 'Values section retrieved successfully',
            'code' => 200,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE (Create if not exists + Sync Items)
    |--------------------------------------------------------------------------
    */
    public function updateValuesSection($request)
    {
        $section = ValuesSection::first();

        if (! $section) {
            return [
                'data' => null,
                'message' => 'Values section not found',
                'code' => 404,
            ];
        } else {
            $section->update([
                'title' => $request->title,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Sync Values Items with Image Update Logic
        |--------------------------------------------------------------------------
        */

        ValuesItem::truncate(); // Clear existing values items

        foreach ($request->values as $index => $value) {
            // Check if an image is provided for the value
            if (isset($value['image']) && $request->hasFile("values.{$index}.image")) {
                // Upload the new image using the UploadImage trait
                $upload = $this->uploadImage($request, 'values', "values.{$index}.image");

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

                // Save the uploaded media to the media table
                $media = Media::create([
                    'path' => $newPath,
                    'type' => 'image',
                    'mime_type' => mime_content_type($filePath),
                    'size_bytes' => filesize($filePath),
                    'width' => $imageSize[0] ?? null,
                    'height' => $imageSize[1] ?? null,
                    'alt_text' => $value['alt_text'] ?? 'Value image',
                    'title' => $value['title'] ?? 'Value image title',
                ]);

                $value['media_id'] = $media->id; // Set the media ID to the newly uploaded image
            }

            // Create a new value item for each value provided
            ValuesItem::create([
                'values_section_id' => $section->id,
                'title' => $value['title'],
                'description' => $value['description'] ?? null,
                'media_id' => $value['media_id'] ?? null,
                'sort_order' => $value['sort_order'] ?? 0,
            ]);
        }

        // Return the updated section with its media info
        return [
            'data' => $section->load(['values.media']),
            'message' => 'Values section updated successfully',
            'code' => 200,
        ];
    }
}
