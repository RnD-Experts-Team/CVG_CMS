<?php

namespace App\Services\AdminAuthCMS;

use App\Models\ProjectsSection;

class ProjectsSectionService
{
    // Get the Projects Section (Singleton)
    public function getProjectsSection()
    {
        $projectSection = ProjectsSection::first();
        if (! $projectSection) {
            $message = 'project section not found';
            $code = 404;

            return ['data' => null, 'message' => $message, 'code' => $code];
        }

        return ['data' => $projectSection, 'message' => 'project section retrieved successfully', 'code' => 200];
    }

    // Update the Projects Section (Singleton)
    public function updateProjectsSection($request)
    {
        $projectSection = ProjectsSection::first();
        if (! $projectSection) {
            $message = 'project section not found';
            $code = 404;

            return ['data' => null, 'message' => $message, 'code' => $code];
        }
        $projectSection->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return ['data' => $projectSection, 'message' => 'project section updated successfully', 'code' => 200];
    }
}
