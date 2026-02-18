<?php

namespace App\Services\AdminAuthCMS;

use App\Models\HeroMedia;
use App\Models\HeroSection;

class HeroService
{
    // Get Hero Section with media
    public function getHero()
    {
        $heroSection = HeroSection::with('media')->first();
        if (! $heroSection) {
            $message = 'Hero Section not found';
            $data = null;
            $code = 404;

            return ['data' => $data, 'message' => $message, 'code' => $code];
        }

        return ['data' => $heroSection, 'message' => 'Hero Ssction feated successfully', 'code' => 200];
    }

    // Update Hero Section with media (support sort_order)
    public function updateHero($request)
    {
        // Update hero section
        $heroSection = HeroSection::first();
        if (! $heroSection) {
            $message = 'Hero Section not found';
            $data = null;
            $code = 404;

            return ['data' => $data, 'message' => $message, 'code' => $code];
        }
        $heroSection->update([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'button_text' => $request->button_text,
            'button_link' => $request->button_link,
        ]);

        // Update media with sort_order
        // Truncate current media and re-add new ones
        HeroMedia::where('hero_section_id', $heroSection->id)->delete();

        foreach ($request->media as $media) {
            HeroMedia::create([
                'hero_section_id' => $heroSection->id,
                'media_id' => $media['media_id'],
                'sort_order' => $media['sort_order'],
            ]);
        }

        return ['data' => $heroSection, 'message' => 'Hero Section updated successfully', 'code' => 200];
    }
}
