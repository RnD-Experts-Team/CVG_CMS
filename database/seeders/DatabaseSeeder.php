<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            MediaSeeder::class,
            SiteMetadataSeeder::class,
            FooterContactSeeder::class,
            FooterSocialLinkSeeder::class,
            HeroSectionSeeder::class,
            CategorySeeder::class,
            ProjectSeeder::class,
            ServiceSectionSeeder::class,
            ValuesSectionSeeder::class,
            ProcessSectionSeeder::class,
            AboutSectionSeeder::class,
            ContactSectionSeeder::class,
            ProjectsSectionSeeder::class,
        ]);
    }
}
