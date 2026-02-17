<?php

namespace Database\Seeders;

use App\Models\ValuesSection;
use Illuminate\Database\Seeder;

class ValuesSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ValuesSection::create([
            'title' => 'Our Values',
        ]);
    }
}
