<?php

namespace App\Services\AdminAuthCMS;

use App\Models\ServicesSection;

class ServicesSectionService
{
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
        $section->update([
            'title' => $request->title,
            'description' => $request->description,
            'image_media_id' => $request->image_media_id,
            'button_text' => $request->button_text,
        ]);

        return [
            'data' => $section->load('image'),
            'message' => 'Services section updated successfully',
            'code' => 200,
        ];
    }
}
