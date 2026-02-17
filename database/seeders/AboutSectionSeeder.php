<?php

namespace Database\Seeders;

use App\Models\AboutSection;
use Illuminate\Database\Seeder;

class AboutSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AboutSection::create([
            'title' => 'About Us',
            'description' => 'We are a company that provides CMS solutions.',
            'image_media_id' => 1, // Assuming an image is stored in the media table
        ]);
    }
}
