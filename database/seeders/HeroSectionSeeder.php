<?php

namespace Database\Seeders;

use App\Models\HeroSection;
use Illuminate\Database\Seeder;

class HeroSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HeroSection::create([
            'title' => 'Welcome to My CMS',
            'subtitle' => 'The best CMS for your website',
            'button_text' => 'Get Started',
            'button_link' => 'https://example.com/start',
        ]);
    }
}
