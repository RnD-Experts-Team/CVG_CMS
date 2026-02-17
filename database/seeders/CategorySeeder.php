<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::create([
            'title' => 'Technology',
            'description' => 'All tech-related projects',
            'slug' => 'technology',
        ]);

        Category::create([
            'title' => 'Design',
            'description' => 'Design-focused projects',
            'slug' => 'design',
        ]);
    }
}
