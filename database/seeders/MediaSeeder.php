<?php

namespace Database\Seeders;

use App\Models\Media;
use Illuminate\Database\Seeder;

class MediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Media::create([
            'path' => 'path/to/logo.png',
            // 'url' => 'https://example.com/logo.png',
            'type' => 'image',
            'mime_type' => 'image/png',
            'width' => 150,
            'height' => 150,
            'size_bytes' => 123456,
            'alt_text' => 'Site Logo',
            'title' => 'Site Logo',
        ]);

        // Insert sample favicon media
        Media::create([
            'path' => 'path/to/favicon.ico',
            // 'url' => 'https://example.com/favicon.ico',
            'type' => 'image',
            'mime_type' => 'image/x-icon',
            'width' => 32,
            'height' => 32,
            'size_bytes' => 65432,
            'alt_text' => 'Site Favicon',
            'title' => 'Site Favicon',
        ]);
    }
}
