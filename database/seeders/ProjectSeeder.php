<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Project::create([
            'title' => 'Project One',
            'description' => 'Description for project one',
            'content' => 'Full content of project one',
            'featured' => true,
            'slug' => 'project-one',
            'published_at' => now(),
            'category_id' => 1, // Assuming a category with ID 1 exists
        ]);

        Project::create([
            'title' => 'Project Two',
            'description' => 'Description for project two',
            'content' => 'Full content of project two',
            'featured' => false,
            'slug' => 'project-two',
            'published_at' => now(),
            'category_id' => 2, // Assuming a category with ID 2 exists
        ]);
    }
}
