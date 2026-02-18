<?php

namespace App\Services\AdminAuthCMS;

use App\Models\ContactSection;

class ContactSectionService
{
    /*
    |--------------------------------------------------------------------------
    | GET
    |--------------------------------------------------------------------------
    */
    public function getContactSection()
    {
        $section = ContactSection::first();

        if (! $section) {
            return [
                'data' => null,
                'message' => 'No contact section found',
                'code' => 404,
            ];
        }

        return [
            'data' => $section,
            'message' => 'Contact section retrieved successfully',
            'code' => 200,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE (Create if not exists)
    |--------------------------------------------------------------------------
    */
    public function updateContactSection($request)
    {
        $section = ContactSection::first();

        if (! $section) {
            return [
                'data' => null,
                'message' => 'No contact section found',
                'code' => 404,
            ];
        }
        $section->update([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
        ]);

        return [
            'data' => $section,
            'message' => 'Contact section updated successfully',
            'code' => 200,
        ];
    }
}
