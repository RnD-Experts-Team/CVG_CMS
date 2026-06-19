<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            [
                'key'         => 'general',
                'title'       => 'General Construction',
                'description' => 'Commercial build-outs, renovations, and fit-outs across restaurants, cafes, and retail spaces delivered on time.',
            ],
            [
                'key'         => 'design',
                'title'       => 'Design Services',
                'description' => 'Interior design, architectural drafting, 3D visualization, and space planning to bring your vision to life.',
            ],
        ];

        foreach ($defaults as $row) {
            ServiceCategory::firstOrCreate(['key' => $row['key']], $row);
        }
    }
}
