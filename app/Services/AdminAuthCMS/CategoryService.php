<?php

namespace App\Services\AdminAuthCMS;

use App\Models\Category;

class CategoryService
{
    /*
    |--------------------------------------------------------------------------
    | GET ALL
    |--------------------------------------------------------------------------
    */
    public function getAllCategories()
    {
        $categories = Category::latest()->get();

        return [
            'data' => $categories,
            'message' => 'Categories retrieved successfully',
            'code' => 200,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function createCategory($request)
    {
        $category = Category::create([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return [
            'data' => $category,
            'message' => 'Category created successfully',
            'code' => 201,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function updateCategory($request, $id)
    {
        $category = Category::find($id);

        if (! $category) {
            return [
                'data' => null,
                'message' => 'Category not found',
                'code' => 404,
            ];
        }

        $category->update([
            'title' => $request->title,
            'description' => $request->description,
        ]);

        return [
            'data' => $category,
            'message' => 'Category updated successfully',
            'code' => 200,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | GET by ID
    |--------------------------------------------------------------------------
    */
    public function getCategoryById($id)
    {
        $category = Category::find($id);

        if (! $category) {
            return [
                'data' => null,
                'message' => 'Category not found',
                'code' => 404,
            ];
        }

        return [
            'data' => $category,
            'message' => 'Category retrieved successfully',
            'code' => 200,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function deleteCategory($id)
    {
        $category = Category::find($id);

        if (! $category) {
            return [
                'data' => null,
                'message' => 'Category not found',
                'code' => 404,
            ];
        }

        $category->delete();

        return [
            'data' => null,
            'message' => 'Category deleted successfully',
            'code' => 200,
        ];
    }
}
