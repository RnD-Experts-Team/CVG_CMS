<?php

namespace App\Services\AdminAuthCMS;

use App\Models\Media;
use App\Models\Service;
use App\Traits\UploadImage;
use Illuminate\Support\Facades\Storage;

class ServiceService
{
    use UploadImage;

    /*
    |--------------------------------------------------------------------------
    | GET ALL
    |--------------------------------------------------------------------------
    */
    public function getAllServices()
    {
        $services = Service::with('image')->latest()->get();

        if ($services->isEmpty()) {
            return [
                'data' => null,
                'message' => 'No services found',
                'code' => 200,
            ];
        }

        return [
            'data' => $services,
            'message' => 'Services retrieved successfully',
            'code' => 200,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function createService($request)
    {
        // Start by creating the service
        $service = Service::create([
            'title' => $request->title,
            'description' => $request->description,
            'content' => $request->content,
            'featured' => $request->featured ?? 0,
        ]);

        // Check if an image is provided in the request
        if ($request->hasFile('image')) {
            // Use the UploadImage trait to handle the image upload
            $upload = $this->uploadImage($request, 'services', 'image');

            if (! $upload['success']) {
                // If image upload fails, return error response
                return [
                    'data' => null,
                    'message' => $upload['message'],
                    'code' => 400,
                ];
            }

            // Get the uploaded image path
            $newPath = $upload['data'];

            // Get the image size (width, height)
            $filePath = storage_path('app/public/'.$newPath);
            $imageSize = getimagesize($filePath);

            // Create a new media record for the uploaded image
            $media = Media::create([
                'path' => $newPath,
                'type' => 'image',
                'mime_type' => mime_content_type($filePath),
                'size_bytes' => filesize($filePath),
                'width' => $imageSize[0] ?? null,
                'height' => $imageSize[1] ?? null,
                'alt_text' => $request->alt_text ?? 'Service image',  // Default alt text
                'title' => $request->image_title ?? 'Service image',  // Default image title
            ]);

            // Associate the media (image) with the service
            $service->image_media_id = $media->id;
            $service->save();
        }

        // Return the newly created service with associated image (if any)
        return [
            'data' => $service->load('image'),  // Load the media (image) relationship
            'message' => 'Service created successfully',
            'code' => 201,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function updateService($request, $id)
    {
        $service = Service::find($id);

        if (! $service) {
            return [
                'data' => null,
                'message' => 'Service not found',
                'code' => 404,
            ];
        }

        // Check if the image file is provided
        if ($request->hasFile('image')) {
            // Upload the new image using the UploadImage trait
            $upload = $this->uploadImage($request, 'services', 'image');

            if (! $upload['success']) {
                return [
                    'data' => null,
                    'message' => $upload['message'],
                    'code' => 400,
                ];
            }

            // Path of the uploaded image
            $newPath = $upload['data'];

            // Get the file size and image dimensions
            $filePath = storage_path('app/public/'.$newPath);
            $imageSize = getimagesize($filePath);

            // Check if the service already has an image
            if ($service->image) {
                // Delete the old physical image file from storage
                if (Storage::disk('public')->exists($service->image->path)) {
                    Storage::disk('public')->delete($service->image->path);
                }

                // Update the media record with the new image details
                $service->image->update([
                    'path' => $newPath,
                    'mime_type' => mime_content_type($filePath),
                    'size_bytes' => filesize($filePath),
                    'width' => $imageSize[0] ?? null,
                    'height' => $imageSize[1] ?? null,
                    'alt_text' => $request->alt_text ?? $service->image->alt_text,
                    'title' => $request->image_title ?? $service->image->title,
                ]);
            } else {
                // If there's no existing image, create a new media record
                $media = Media::create([
                    'path' => $newPath,
                    'type' => 'image',
                    'mime_type' => mime_content_type($filePath),
                    'size_bytes' => filesize($filePath),
                    'width' => $imageSize[0] ?? null,
                    'height' => $imageSize[1] ?? null,
                    'alt_text' => $request->alt_text ?? 'Service image',
                    'title' => $request->image_title ?? 'Service image',
                ]);

                // Link the new media to the service
                $service->image_media_id = $media->id;
            }
        } elseif ($service->image) {
            // If no image is uploaded, update image metadata only
            $service->image->update([
                'alt_text' => $request->alt_text ?? $service->image->alt_text,
                'title' => $request->image_title ?? $service->image->title,
            ]);
        }

        // Update the service's main data
        $service->update([
            'title' => $request->title ?? $service->title,
            'description' => $request->description ?? $service->description,
            'content' => $request->content ?? $service->content,
            'featured' => $request->featured ?? 0,
        ]);

        // Return the updated service with its image data
        return [
            'data' => $service->load('image'),
            'message' => 'Service updated successfully',
            'code' => 200,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Get by id
    |--------------------------------------------------------------------------
    */
    public function getServiceById($id)
    {
        $service = Service::with('image')->find($id);

        if (! $service) {
            return [
                'data' => null,
                'message' => 'Service not found',
                'code' => 404,
            ];
        }

        return [
            'data' => $service,
            'message' => 'Service retrieved successfully',
            'code' => 200,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function deleteService($id)
    {
        $service = Service::find($id);

        if (! $service) {
            return [
                'data' => null,
                'message' => 'Service not found',
                'code' => 404,
            ];
        }

        $service->delete();

        return [
            'data' => null,
            'message' => 'Service deleted successfully',
            'code' => 200,
        ];
    }
}
