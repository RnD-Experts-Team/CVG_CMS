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
            return [
                'id' => $image->id,
                'path' => $image->media->path, // Assuming 'media' is a relation on ProjectImage
                'url' => $image->media->url, // Full URL to the image
                'alt_text' => $image->media->alt_text,
                'title' => $image->media->title,
                'width' => $image->media->width,
                'height' => $image->media->height,
                'sort_order' => $image->sort_order,
            ];
        });
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

        // Handle Image Uploads and store in the media table
        if ($request->has('images')) {
            foreach ($request->images as $index => $imageData) {
                if ($request->hasFile("images.$index.file")) {

                    $imagePath = $this->uploadImage(
                        $request,
                        'projects',
                        "images.$index.file"
                    );

                    if (! $imagePath['success']) {
                        return $imagePath;
                    }

                    $fullPath = storage_path('app/public/'.$imagePath['data']);
                    $size = getimagesize($fullPath);

                    $media = Media::create([
                        'path' => $imagePath['data'],
                        'type' => 'image',
                        'mime_type' => mime_content_type($fullPath),
                        'size_bytes' => filesize($fullPath),
                        'width' => $size[0],
                        'height' => $size[1],
                        'alt_text' => $imageData['alt_text'] ?? 'Project image',
                        'title' => $imageData['title'] ?? 'Project image title',
                    ]);

                    ProjectImage::create([
                        'project_id' => $project->id,
                        'media_id' => $media->id,
                        'sort_order' => $imageData['sort_order'] ?? 0,
                    ]);
                }
            }
        }

        return $this->formatProjectData($project);
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
        | REMOVE ALL OLD IMAGES
        |--------------------------------------------------------------------------
        */
        if ($request->has('images')) {
            foreach ($project->images as $oldImage) {

                // Optional: delete physical file
                $filePath = storage_path('app/public/'.$oldImage->media->path);
                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                // Delete project image relation
                $oldImage->delete();

                // Delete media record
                $oldImage->media->delete();

            }
        }

        /*
        |--------------------------------------------------------------------------
        | CREATE NEW IMAGES
        |--------------------------------------------------------------------------
        */
        if ($request->has('images')) {

            foreach ($request->images as $index => $imageData) {

                if ($request->hasFile("images.$index.file")) {

                    $imagePath = $this->uploadImage(
                        $request,
                        'projects',
                        "images.$index.file"
                    );

                    if (! $imagePath['success']) {
                        return $imagePath;
                    }

                    $fullPath = storage_path('app/public/'.$imagePath['data']);
                    $size = getimagesize($fullPath);

                    $media = Media::create([
                        'path' => $imagePath['data'],
                        'type' => 'image',
                        'mime_type' => mime_content_type($fullPath),
                        'size_bytes' => filesize($fullPath),
                        'width' => $size[0],
                        'height' => $size[1],
                        'alt_text' => $imageData['alt_text'] ?? 'Project image',
                        'title' => $imageData['title'] ?? 'Project image',
                    ]);

                    ProjectImage::create([
                        'project_id' => $project->id,
                        'media_id' => $media->id,
                        'sort_order' => $imageData['sort_order'] ?? 0,
                    ]);
                }
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

            $filePath = storage_path('app/public/'.$image->media->path);

            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $image->delete();
            $image->media->delete();
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
