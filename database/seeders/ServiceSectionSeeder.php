<?php

namespace Database\Seeders;

use App\Models\ServicesSection;
use Illuminate\Database\Seeder;

class ServiceSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ServicesSection::create([
            'title' => 'Our Services',
            'description' => 'We offer the best services to our customers.',
            'image_media_id' => 2, // Assuming an image is stored in the media table
            'button_text' => 'See Services',
        ]);
    }
}
