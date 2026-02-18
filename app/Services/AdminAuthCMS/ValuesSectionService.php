<?php

namespace App\Services\AdminAuthCMS;

use App\Models\ValuesItem;
use App\Models\ValuesSection;

class ValuesSectionService
{
    /*
    |--------------------------------------------------------------------------
    | GET
    |--------------------------------------------------------------------------
    */
    public function getValuesSection()
    {
        $section = ValuesSection::with(['values.media'])
            ->first();

        if (! $section) {
            return [
                'data' => null,
                'message' => 'Values section not found',
                'code' => 404,
            ];
        }

        return [
            'data' => $section,
            'message' => 'Values section retrieved successfully',
            'code' => 200,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE (Create if not exists + Sync Items)
    |--------------------------------------------------------------------------
    */
    public function updateValuesSection($request)
    {
        $section = ValuesSection::first();

        if (! $section) {
            return [
                'data' => null,
                'message' => 'Values section not found',
                'code' => 404,
            ];
        } else {
            $section->update([
                'title' => $request->title,
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Sync Values Items
        |--------------------------------------------------------------------------
        */

        ValuesItem::Truncate();
        foreach ($request->values as $value) {

            ValuesItem::create([
                'values_section_id' => $section->id,
                'title' => $value['title'],
                'description' => $value['description'] ?? null,
                'media_id' => $value['media_id'] ?? null,
                'sort_order' => $value['sort_order'] ?? 0,
            ]);

        }

        return [
            'data' => $section->load(['values.media']),
            'message' => 'Values section updated successfully',
            'code' => 200,
        ];
    }
}
