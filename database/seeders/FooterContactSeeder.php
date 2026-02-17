<?php

namespace Database\Seeders;

use App\Models\FooterContact;
use Illuminate\Database\Seeder;

class FooterContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FooterContact::create([
            'phone' => '+1 (123) 456-7890',
            'whatsapp' => '+1 (987) 654-3210',
            'email' => 'contact@example.com',
            'address' => '123 Main St, Springfield, IL',
        ]);
    }
}
