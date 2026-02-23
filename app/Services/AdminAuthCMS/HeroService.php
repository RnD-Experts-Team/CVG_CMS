<?php

namespace App\Services\AdminAuthCMS;

use App\Models\HeroMedia;
use App\Models\HeroSection;
use App\Models\Media;
use App\Traits\UploadImage;

class HeroService
{
    use UploadImage;

    // Get Hero Section with media
    public function getHero()
    {
        $heroSection = HeroSection::with('media', 'media.media')->first();
        if (! $heroSection) {
            $message = 'Hero Section not found';
            $data = null;
            $code = 404;

            return ['data' => $data, 'message' => $message, 'code' => $code];
        }

        return ['data' => $heroSection, 'message' => 'Hero Section fetched successfully', 'code' => 200];
    }

    // Update Hero Section with media (support sort_order)
    public function updateHero($request)
    {
        // Retrieve the Hero Section
        $heroSection = HeroSection::first();
        if (! $heroSection) {
            return [
                'data' => null,
                'message' => 'Hero Section not found',
                'code' => 404,
            ];
        }

        // Update Hero Section basic data (text fields)
        $heroSection->update([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'button_text' => $request->button_text,
            'button_link' => $request->button_link,
        ]);

        // Handle Image Media (Upload New and Remove Old)
        if ($request->has('media')) {
            // Remove current media associated with the hero section
            foreach ($heroSection->media as $heroMedia) {
                $heroMedia->delete();
                // Delete old image files from the storage
                $filePath = storage_path('app/public/'.$heroMedia->media->path);
                if (file_exists($filePath)) {
                    unlink($filePath);  // Delete the old image file
                }

                // Delete media and associated record from HeroMedia
                $heroMedia->media->delete();
            }

            // Loop through the provided media to upload new images
            foreach ($request->media as $index => $mediaData) {
                if ($request->hasFile("media.$index.file")) {
                    // Upload image using the UploadImage trait
                    $imagePath = $this->uploadImage($request, 'hero', "media.$index.file");

                    if (! $imagePath['success']) {
                        return $imagePath;  // Return error if image upload failed
                    }

                    // Get image full path and its dimensions
                    $fullPath = storage_path('app/public/'.$imagePath['data']);
                    $mimeType = mime_content_type($fullPath);

                    $width = null;
                    $height = null;
                    $type = 'image';

                    if (str_starts_with($mimeType, 'image/')) {
                        $size = getimagesize($fullPath);

                        if ($size !== false) {
                            $width = $size[0];
                            $height = $size[1];
                        }
                    } elseif (str_starts_with($mimeType, 'video/')) {
                        $type = 'video';
                    }

                    // Create new media record in the database
                    $media = Media::create([
                        'path' => $imagePath['data'],
                        'type' => $type,
                        'mime_type' => mime_content_type($fullPath),
                        'size_bytes' => filesize($fullPath),
                        'width' => $width ?? 0,
                        'height' => $height ?? 0,
                        'alt_text' => $mediaData['alt_text'] ?? 'Hero image',
                        'title' => $mediaData['title'] ?? 'Hero image title',
                    ]);

                    // Associate the new media with the Hero Section
                    HeroMedia::create([
                        'hero_section_id' => $heroSection->id,
                        'media_id' => $media->id,
                        'sort_order' => $mediaData['sort_order'] ?? 0,
                    ]);
                }
            }
        }

        return [
            'data' => $heroSection,
            'message' => 'Hero Section updated successfully',
            'code' => 200,
        ];
    }
}
