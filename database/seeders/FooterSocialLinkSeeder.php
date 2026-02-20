<?php

namespace Database\Seeders;

use App\Models\FooterSocialLink;
use Illuminate\Database\Seeder;

class FooterSocialLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FooterSocialLink::create([
            'platform' => 'facebook',
            'url' => 'https://facebook.com',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        FooterSocialLink::create([
            'platform' => 'instagram',
            'url' => 'https://instagram.com',
            'sort_order' => 2,
            'is_active' => true,
        ]);
    }
}
