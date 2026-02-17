<?php

namespace Database\Seeders;

use App\Models\ContactSection;
use Illuminate\Database\Seeder;

class ContactSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ContactSection::create([
            'title' => 'Contact Us',
            'subtitle' => 'Get in touch with us',
        ]);
    }
}
