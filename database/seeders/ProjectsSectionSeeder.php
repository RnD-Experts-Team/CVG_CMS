<?php

namespace Database\Seeders;

use App\Models\ProjectsSection;
use Illuminate\Database\Seeder;

class ProjectsSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        ProjectsSection::create([
            'title' => 'Our Projects',
            'description' => 'Showcase of our amazing projects',
        ]);
    }
}
