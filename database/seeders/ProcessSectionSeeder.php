<?php

namespace Database\Seeders;

use App\Models\ProcessSection;
use Illuminate\Database\Seeder;

class ProcessSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProcessSection::create([
            'title' => 'Our Process',
            'image_media_id' => 2, // Assuming an image is stored in the media table
        ]);
    }
}
