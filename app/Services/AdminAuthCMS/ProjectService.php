<?php

namespace App\Services\AdminAuthCMS;

use App\Models\Media;
use App\Models\Project;
use App\Models\ProjectImage;
use App\Traits\UploadImage;

class ProjectService
{
    use UploadImage;

    private function formatProjectData($project)
    {
        // Format the data for the response
        return [
            'id' => $project->id,
            'title' => $project->title,
            'description' => $project->description,
            'content' => $project->content,
            'slug' => $project->slug,
            'published_at' => $project->published_at,
            'category' => $project->category, // Category details
            'featured' => $project->featured,
            'images' => $this->formatImages($project->images), // Images formatted with additional data
        ];
    }

    // Format images data to include necessary attributes
    private function formatImages($images)
    {
        return $images->map(function ($image) {
            $media = $image->media;

            return [
                'id' => $image->id,
                'path' => $media?->path,
                'url' => $media?->url,
                'type' => $media?->type, // 'image' | 'video' | 'file'
                'mime_type' => $media?->mime_type,
                'alt_text' => $media?->alt_text,
                'title' => $media?->title,
                'width' => $media?->width,
                'height' => $media?->height,
                'sort_order' => $image->sort_order,
            ];
        });
    }

    /**
     * Decide media type bucket from a MIME string.
     */
    private function detectMediaType(?string $mime): string
    {
        if (! $mime) {
            return 'file';
        }
        if (str_starts_with($mime, 'image/')) {
            return 'image';
        }
        if (str_starts_with($mime, 'video/')) {
            return 'video';
        }

        return 'file';
    }

    /**
     * Persist a single uploaded gallery item (image or video) and return Media row.
     * Returns the Media model on success, or [success=>false, message=>...] on failure.
     */
    private function storeGalleryUpload($request, string $requestKey, array $imageData)
    {
        $upload = $this->uploadImage($request, 'projects', $requestKey);
        if (! $upload['success']) {
            return $upload;
        }

        $relativePath = $upload['data'];
        $fullPath = storage_path('app/public/'.$relativePath);

        $mime = @mime_content_type($fullPath) ?: $request->file($requestKey)?->getMimeType();
        $type = $this->detectMediaType($mime);

        $width = null;
        $height = null;
        if ($type === 'image') {
            $size = @getimagesize($fullPath);
            if (is_array($size)) {
                $width = $size[0] ?? null;
                $height = $size[1] ?? null;
            }
        }

        return Media::create([
            'path' => $relativePath,
            'type' => $type,
            'mime_type' => $mime,
            'size_bytes' => @filesize($fullPath) ?: null,
            'width' => $width,
            'height' => $height,
            'alt_text' => $imageData['alt_text'] ?? null,
            'title' => $imageData['title'] ?? null,
        ]);
    }

    // =======================================================================
    // Project CRUD Operations

    // Get all Projects
    public function getAllProjects()
    {
        $projects = Project::with(['category', 'images.media'])->get();

        if ($projects->isEmpty()) {
            return [
                'data' => [],
                'message' => 'No projects found',
                'code' => 404,
            ];
        }

        $formattedProjects = $projects->map(function ($project) {
            return $this->formatProjectData($project);
        });

        return [
            'data' => $formattedProjects,
            'message' => 'Projects retrieved successfully',
            'code' => 200,
        ];
    }

    // Create a new Project
    public function createProject($request)
    {
        // Create the Project in the database
        $project = Project::create([
            'title' => $request->title,
            'description' => $request->description,
            'content' => $request->content,
            'featured' => $request->featured ?? 0,
            'published_at' => $request->published_at ?? now(),
            'category_id' => $request->category_id,
        ]);

        // Handle Image/Video Uploads and store in the media table
        if ($request->has('images')) {
            foreach ($request->images as $index => $imageData) {
                if (! $request->hasFile("images.$index.file")) {
                    continue;
                }
                $result = $this->storeGalleryUpload($request, "images.$index.file", (array) $imageData);
                if (is_array($result) && isset($result['success']) && ! $result['success']) {
                    return $result;
                }
                ProjectImage::create([
                    'project_id' => $project->id,
                    'media_id' => $result->id,
                    'sort_order' => isset($imageData['sort_order']) ? (int) $imageData['sort_order'] : 0,
                ]);
            }
        }

        return $this->formatProjectData(
            $project->fresh(['category', 'images.media'])
        );
    }

    // // Update Project by ID
    public function updateProject($request, $id)
    {
        $project = Project::with('images.media')->find($id);

        if (! $project) {
            return [
                'status' => false,
                'message' => 'Project not found',
                'code' => 404,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Update Main Fields
        |--------------------------------------------------------------------------
        */
        $project->update([
            'title' => $request->title,
            'description' => $request->description,
            'content' => $request->content,
            'featured' => $request->featured ?? 0,
            'published_at' => $request->published_at ?? $project->published_at,
            'category_id' => $request->category_id,
        ]);

        /*
        |--------------------------------------------------------------------------
        | REMOVE ONLY EXPLICITLY REQUESTED IMAGES
        |--------------------------------------------------------------------------
        */
        $removedIds = $request->input('removed_image_ids', []);
        if (! empty($removedIds)) {
            $imagesToRemove = ProjectImage::with('media')
                ->where('project_id', $project->id)
                ->whereIn('id', $removedIds)
                ->get();

            foreach ($imagesToRemove as $img) {
                if ($img->media) {
                    $filePath = storage_path('app/public/'.$img->media->path);
                    if (file_exists($filePath)) {
                        @unlink($filePath);
                    }
                }
                $mediaId = $img->media_id;
                $img->delete();
                if ($mediaId) {
                    Media::where('id', $mediaId)->delete();
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | UPDATE METADATA / SORT ORDER OF KEPT IMAGES
        |--------------------------------------------------------------------------
        */
        $existing = $request->input('existing_images', []);
        if (! empty($existing)) {
            foreach ($existing as $row) {
                if (empty($row['id'])) {
                    continue;
                }
                $img = ProjectImage::with('media')
                    ->where('project_id', $project->id)
                    ->where('id', $row['id'])
                    ->first();
                if (! $img) {
                    continue;
                }

                if (array_key_exists('sort_order', $row)) {
                    $img->sort_order = (int) $row['sort_order'];
                    $img->save();
                }

                if ($img->media && (array_key_exists('alt_text', $row) || array_key_exists('title', $row))) {
                    $img->media->fill(array_filter([
                        'alt_text' => $row['alt_text'] ?? null,
                        'title' => $row['title'] ?? null,
                    ], fn ($v) => $v !== null))->save();
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | ADD NEW IMAGES
        |--------------------------------------------------------------------------
        */
        if ($request->has('images')) {
            foreach ($request->images as $index => $imageData) {
                if (! $request->hasFile("images.$index.file")) {
                    continue;
                }

                $media = $this->storeGalleryUpload($request, "images.$index.file", (array) $imageData);
                if (is_array($media) && isset($media['success']) && ! $media['success']) {
                    return $media;
                }

                ProjectImage::create([
                    'project_id' => $project->id,
                    'media_id' => $media->id,
                    'sort_order' => isset($imageData['sort_order']) ? (int) $imageData['sort_order'] : 0,
                ]);
            }
        }

        return $this->formatProjectData(
            $project->fresh(['category', 'images.media'])
        );
    }

    public function getProjectById($id)
    {
        $project = Project::with(['category', 'images.media'])->find($id);

        if (! $project) {
            return [
                'status' => false,
                'message' => 'Project not found',
                'code' => 404,
            ];
        }

        return $this->formatProjectData($project);
    }

    // // Delete a Project by ID
    public function deleteProject($id)
    {
        $project = Project::with('images.media')->find($id);

        if (! $project) {
            return [
                'data' => null,
                'message' => 'Project not found',
                'code' => 404,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Delete Images + Media + Physical Files
        |--------------------------------------------------------------------------
        */
        foreach ($project->images as $image) {
            if ($image->media) {
                $filePath = storage_path('app/public/'.$image->media->path);
                if (file_exists($filePath)) {
                    @unlink($filePath);
                }
            }
            $mediaId = $image->media_id;
            $image->delete();
            if ($mediaId) {
                Media::where('id', $mediaId)->delete();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Delete Project
        |--------------------------------------------------------------------------
        */
        $project->delete();

        return [
            'data' => null,
            'message' => 'Project deleted successfully',
            'code' => 200,
        ];
    }
}
