<?php

namespace App\Services\AdminAuthCMS;

use App\Models\ProcessSection;
use App\Models\ProcessStep;

class ProcessSectionService
{
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
            $section = ProcessSection::create([
                'title' => $request->title,
                'image_media_id' => $request->image_media_id,
            ]);
        } else {
            $section->update([
                'title' => $request->title,
                'image_media_id' => $request->image_media_id,
            ]);
        }

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
