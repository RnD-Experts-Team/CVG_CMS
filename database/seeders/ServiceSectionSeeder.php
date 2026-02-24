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
            'content' => 'we have best services in the market. Our team is dedicated to providing top-notch solutions to meet your needs.',
            'image_media_id' => 2, // Assuming an image is stored in the media table
            'button_text' => 'See Services',
        ]);
    }
}
