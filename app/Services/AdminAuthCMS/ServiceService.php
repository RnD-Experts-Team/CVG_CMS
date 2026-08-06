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
            'type' => $request->type ?? 'general',
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

            // Get the file's real mime type, dimensions only apply to images
            $filePath = storage_path('app/public/'.$newPath);
            $mimeType = mime_content_type($filePath);
            $mediaType = str_starts_with($mimeType, 'video/') ? 'video' : 'image';

            $width = null;
            $height = null;
            if ($mediaType === 'image') {
                $imageSize = getimagesize($filePath);
                $width = $imageSize[0] ?? null;
                $height = $imageSize[1] ?? null;
            }

            // Create a new media record for the uploaded image/video
            $media = Media::create([
                'path' => $newPath,
                'type' => $mediaType,
                'mime_type' => $mimeType,
                'size_bytes' => filesize($filePath),
                'width' => $width,
                'height' => $height,
                'alt_text' => $request->alt_text ?? 'Service image',  // Default alt text
                'title' => $request->image_title ?? 'Service image',  // Default image title
            ]);

            // Associate the media (image/video) with the service
            $service->image_media_id = $media->id;
            $service->save();
        }
        $icon_path = $this->uploadImage($request, 'services/icon', 'icon');
        $service->icon_path = $icon_path['data'];
        $service->save();

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

            // Get the file's real mime type; dimensions only apply to images
            $filePath = storage_path('app/public/'.$newPath);
            $mimeType = mime_content_type($filePath);
            $mediaType = str_starts_with($mimeType, 'video/') ? 'video' : 'image';

            $width = null;
            $height = null;
            if ($mediaType === 'image') {
                $imageSize = getimagesize($filePath);
                $width = $imageSize[0] ?? null;
                $height = $imageSize[1] ?? null;
            }

            /*
            |--------------------------------------------------------------------------
            | Always Create a New Media Row
            |--------------------------------------------------------------------------
            | image_media_id can be shared with another record, so mutating the
            | existing Media row in place would silently change that other
            | record's image/video too. Always create a fresh row and just
            | repoint this service's image_media_id at it.
            */
            $media = Media::create([
                'path' => $newPath,
                'type' => $mediaType,
                'mime_type' => $mimeType,
                'size_bytes' => filesize($filePath),
                'width' => $width,
                'height' => $height,
                'alt_text' => $request->alt_text ?? ($service->image->alt_text ?? 'Service image'),
                'title' => $request->image_title ?? ($service->image->title ?? 'Service image'),
            ]);

            $service->image_media_id = $media->id;
        } elseif ($service->image) {
            // If no image is uploaded, update image metadata only
            $service->image->update([
                'alt_text' => $request->alt_text ?? $service->image->alt_text,
                'title' => $request->image_title ?? $service->image->title,
            ]);
        }
        $iconPath = $service->icon_path;

        if ($request->hasFile('icon')) {

            if ($service->icon_path && Storage::disk('public')->exists($service->icon_path)) {
                Storage::disk('public')->delete($service->icon_path);
            }

            $uploadIcon = $this->uploadImage($request, 'services/icon', 'icon');

            if (! $uploadIcon['success']) {
                return [
                    'data' => null,
                    'message' => $uploadIcon['message'],
                    'code' => 400,
                ];
            }

            $iconPath = $uploadIcon['data'];
        }
        // Update the service's main data
        $service->update([
            'title' => $request->title ?? $service->title,
            'description' => $request->description ?? $service->description,
            'content' => $request->content ?? $service->content,
            'featured' => $request->featured ?? 0,
            'type' => $request->type ?? $service->type,
            'icon_path' => $iconPath,
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
        if ($service->icon_path) {
            if (Storage::disk('public')->exists($service->icon_path)) {
                Storage::disk('public')->delete($service->icon_path);
            }
        }
        $service->delete();

        return [
            'data' => null,
            'message' => 'Service deleted successfully',
            'code' => 200,
        ];
    }
}
