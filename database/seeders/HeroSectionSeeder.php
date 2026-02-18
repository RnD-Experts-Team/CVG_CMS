<?php

namespace Database\Seeders;

use App\Models\HeroMedia;
use App\Models\HeroSection;
use App\Models\Media;
use Illuminate\Database\Seeder;

class HeroSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Create a Hero Section
        $heroSection = HeroSection::create([
            'title' => 'Welcome to My CMS',
            'subtitle' => 'The best CMS for your website',
            'button_text' => 'Get Started',
            'button_link' => 'https://example.com/start',
        ]);

        // Create some media for testing
        $media1 = Media::create([
            'type' => 'image',
            'path' => 'path/to/image1.jpg',
            'url' => 'https://example.com/image1.jpg',
            'mime_type' => 'image/jpeg',
            'width' => 800,
            'height' => 600,
            'size_bytes' => 102400,
            'alt_text' => 'Hero Image 1',
            'title' => 'Hero Image 1',
        ]);

        $media2 = Media::create([
            'type' => 'image',
            'path' => 'path/to/image2.jpg',
            'url' => 'https://example.com/image2.jpg',
            'mime_type' => 'image/jpeg',
            'width' => 800,
            'height' => 600,
            'size_bytes' => 102400,
            'alt_text' => 'Hero Image 2',
            'title' => 'Hero Image 2',
        ]);

        // Attach media to Hero Section
        HeroMedia::create([
            'hero_section_id' => $heroSection->id,
            'media_id' => $media1->id,
            'sort_order' => 1,
        ]);

        HeroMedia::create([
            'hero_section_id' => $heroSection->id,
            'media_id' => $media2->id,
            'sort_order' => 2,
        ]);
    }
}
