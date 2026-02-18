<?php

namespace App\Services\AdminAuthCMS;

use App\Models\AboutSection;

class AboutSectionService
{
    /*
    |--------------------------------------------------------------------------
    | GET
    |--------------------------------------------------------------------------
    */
    public function getAboutSection()
    {
        $section = AboutSection::with('image')->first();

        if (! $section) {
            return [
                'data' => null,
                'message' => 'No about section found',
                'code' => 404,
            ];
        }

        return [
            'data' => $section,
            'message' => 'About section retrieved successfully',
            'code' => 200,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE (Create if not exists)
    |--------------------------------------------------------------------------
    */
    public function updateAboutSection($request)
    {
        $section = AboutSection::first();

        if (! $section) {
            return [
                'data' => null,
                'message' => 'No about section found',
                'code' => 404,
            ];
        }
        $section->update([
            'title' => $request->title,
            'description' => $request->description,
            'image_media_id' => $request->image_media_id,
        ]);

        return [
            'data' => $section->load('image'),
            'message' => 'About section updated successfully',
            'code' => 200,
        ];
    }
}
