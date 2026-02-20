<?php

namespace App\Services\AdminAuthCMS;

use App\Models\Media;
use App\Models\SiteMetadata;
use App\Traits\UploadImage;
use Illuminate\Support\Facades\Storage;

class SiteMetadataService
{
    use UploadImage;

    // Get the first site metadata (Singleton)
    public function getSiteMetadata()
    {
        $siteMetadata = SiteMetadata::with('logo', 'favicon')->first();
        if ($siteMetadata) {
            return ['data' => $siteMetadata, 'message' => 'Site Metadata fetched successfully', 'code' => 200];
        }

        return ['data' => null, 'message' => 'site metadata not found', 'code' => 404];

    }

    public function updateSiteMetadata($request)
    {
        $siteMetadata = SiteMetadata::first();

        // Check if Site Metadata exists
        if (! $siteMetadata) {
            return [
                'data' => null,
                'message' => 'Site metadata not found',
                'code' => 404,
            ];
        }

        // Handle Logo Update
        if ($request->hasFile('logo')) {

            // Upload logo image using the UploadImage trait
            $upload = $this->uploadImage($request, 'site/logo', 'logo');

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

            // If logo already exists, update it
            if ($siteMetadata->logo) {

                // Delete the old logo file if exists
                if (Storage::disk('public')->exists($siteMetadata->logo->path)) {
                    Storage::disk('public')->delete($siteMetadata->logo->path);
                }

                // Update existing logo metadata
                $siteMetadata->logo->update([
                    'path' => $newPath,
                    'mime_type' => mime_content_type($filePath),
                    'size_bytes' => filesize($filePath),
                    'width' => $imageSize[0] ?? null,
                    'height' => $imageSize[1] ?? null,
                    'alt_text' => $request->logo_alt_text ?? $siteMetadata->logo->alt_text,
                    'title' => $request->logo_title ?? $siteMetadata->logo->title,
                ]);
            } else {
                // If logo doesn't exist, create new media
                $media = Media::create([
                    'path' => $newPath,
                    'type' => 'image',
                    'mime_type' => mime_content_type($filePath),
                    'size_bytes' => filesize($filePath),
                    'width' => $imageSize[0] ?? null,
                    'height' => $imageSize[1] ?? null,
                    'alt_text' => $request->logo_alt_text ?? 'Logo image',
                    'title' => $request->logo_title ?? 'Logo image',
                ]);

                // Associate the new logo media with the site metadata
                $siteMetadata->logo_media_id = $media->id;
            }
        }

        // Handle Favicon Update
        if ($request->hasFile('favicon')) {

            // Upload favicon image using the UploadImage trait
            $upload = $this->uploadImage($request, 'site/favicon', 'favicon');

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

            // If favicon already exists, update it
            if ($siteMetadata->favicon) {

                // Delete the old favicon file if exists
                if (Storage::disk('public')->exists($siteMetadata->favicon->path)) {
                    Storage::disk('public')->delete($siteMetadata->favicon->path);
                }

                // Update existing favicon metadata
                $siteMetadata->favicon->update([
                    'path' => $newPath,
                    'mime_type' => mime_content_type($filePath),
                    'size_bytes' => filesize($filePath),
                    'width' => $imageSize[0] ?? null,
                    'height' => $imageSize[1] ?? null,
                    'alt_text' => $request->favicon_alt_text ?? $siteMetadata->favicon->alt_text,
                    'title' => $request->favicon_title ?? $siteMetadata->favicon->title,
                ]);
            } else {
                // If favicon doesn't exist, create new media
                $media = Media::create([
                    'path' => $newPath,
                    'type' => 'image',
                    'mime_type' => mime_content_type($filePath),
                    'size_bytes' => filesize($filePath),
                    'width' => $imageSize[0] ?? null,
                    'height' => $imageSize[1] ?? null,
                    'alt_text' => $request->favicon_alt_text ?? 'Favicon image',
                    'title' => $request->favicon_title ?? 'Favicon image',
                ]);

                // Associate the new favicon media with the site metadata
                $siteMetadata->favicon_media_id = $media->id;
            }
        }

        // Update the text fields (title, description, keywords)
        $siteMetadata->update([
            'name' => $request->name ?? $siteMetadata->name,
            'description' => $request->description ?? $siteMetadata->description,
            'keywords' => $request->keywords ?? $siteMetadata->keywords,
        ]);

        // Return the updated site metadata
        return [
            'data' => $siteMetadata->load('logo', 'favicon'), // Load the updated media relationships
            'message' => 'Site metadata updated successfully',
            'code' => 200,
        ];
    }
}
