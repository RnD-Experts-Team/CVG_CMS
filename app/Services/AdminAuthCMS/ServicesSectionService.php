<?php

namespace App\Services\AdminAuthCMS;

use App\Models\Media;
use App\Models\ServicesSection;
use App\Traits\UploadImage;
use Illuminate\Support\Facades\Storage;

class ServicesSectionService
{
    use UploadImage;

    /*
    |--------------------------------------------------------------------------
    | GET (Singleton)
    |--------------------------------------------------------------------------
    */
    public function getServicesSection()
    {
        $section = ServicesSection::with('image')->first();

        if (! $section) {
            return [
                'data' => null,
                'message' => 'Services section not found',
                'code' => 404,
            ];
        }

        return [
            'data' => $section,
            'message' => 'Services section retrieved successfully',
            'code' => 200,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function updateServicesSection($request)
    {
        $section = ServicesSection::first();

        if (! $section) {
            return [
                'data' => null,
                'message' => 'Services section not found',
                'code' => 404,
            ];
        }

        // Check if the request has a file for image upload
        if ($request->hasFile('image')) {

            // Upload the new image using the UploadImage trait
            $upload = $this->uploadImage($request, 'servicesSection', 'image');

            if (! $upload['success']) {
                return [
                    'data' => null,
                    'message' => $upload['message'],
                    'code' => 400,
                ];
            }

            // New image path
            $newPath = $upload['data'];
            $filePath = storage_path('app/public/'.$newPath);
            $imageSize = getimagesize($filePath);

            // If the service section has an existing image, delete the old one
            if ($section->image) {

                // Delete old image from storage
                if (Storage::disk('public')->exists($section->image->path)) {
                    Storage::disk('public')->delete($section->image->path);
                }

                // Update existing media record with new image details
                $section->image->update([
                    'path' => $newPath,
                    'mime_type' => mime_content_type($filePath),
                    'size_bytes' => filesize($filePath),
                    'width' => $imageSize[0],
                    'height' => $imageSize[1],
                    'alt_text' => $request->alt_text ?? $section->image->alt_text,
                    'title' => $request->image_title ?? $section->image->title,
                ]);

            } else {
                // First-time image upload if no previous image exists
                $media = Media::create([
                    'path' => $newPath,
                    'type' => 'image',
                    'mime_type' => mime_content_type($filePath),
                    'size_bytes' => filesize($filePath),
                    'width' => $imageSize[0],
                    'height' => $imageSize[1],
                    'alt_text' => $request->alt_text ?? 'Service image',
                    'title' => $request->image_title ?? 'Service image',
                ]);

                $section->image_media_id = $media->id;
            }
        } elseif ($section->image) {
            // If no image is uploaded, update image metadata only
            $section->image->update([
                'alt_text' => $request->alt_text ?? $section->image->alt_text,
                'title' => $request->image_title ?? $section->image->title,
            ]);
        }
        $section->save();

        // Update the other fields of the services section
        $section->update([
            'title' => $request->title,
            'description' => $request->description,
            'button_text' => $request->button_text,
        ]);

        return [
            'data' => $section->load('image'),
            'message' => 'Services section updated successfully',
            'code' => 200,
        ];
    }
}
