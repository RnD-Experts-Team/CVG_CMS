<?php

namespace Database\Seeders;

use App\Models\SiteMetadata;
use Illuminate\Database\Seeder;

class SiteMetadataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SiteMetadata::create([
            'name' => 'My CMS',
            'description' => 'This is the description of My CMS',
            'keywords' => 'cms, laravel, website',
            'logo_media_id' => 1, // Assuming a logo is stored in the media table
            'favicon_media_id' => 2, // Assuming a favicon is stored in the media table
        ]);
    }
}
