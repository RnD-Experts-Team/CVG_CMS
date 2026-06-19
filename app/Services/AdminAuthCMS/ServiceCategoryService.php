<?php

namespace App\Services\AdminAuthCMS;

use App\Models\ServiceCategory;
use App\Traits\UploadImage;

class ServiceCategoryService
{
    use UploadImage;

    public function getServiceCategories()
    {
        $categories = ServiceCategory::orderByRaw("FIELD(`key`, 'general', 'design')")->get();

        return [
            'data'    => $categories,
            'message' => 'Service categories retrieved successfully',
            'code'    => 200,
        ];
    }

    public function getServiceCategoryById($id)
    {
        $category = ServiceCategory::find($id);

        if (! $category) {
            return [
                'data'    => null,
                'message' => 'Service category not found',
                'code'    => 404,
            ];
        }

        return [
            'data'    => $category,
            'message' => 'Service category retrieved successfully',
            'code'    => 200,
        ];
    }

    public function updateServiceCategory($request, $id)
    {
        $category = ServiceCategory::find($id);

        if (! $category) {
            return [
                'data'    => null,
                'message' => 'Service category not found',
                'code'    => 404,
            ];
        }

        if ($request->hasFile('icon')) {
            $upload = $this->uploadImage($request, 'serviceCategories', 'icon');

            if (! $upload['success']) {
                return [
                    'data'    => null,
                    'message' => $upload['message'],
                    'code'    => 400,
                ];
            }

            $category->icon_path = $upload['data'];
        }

        $category->update([
            'title'       => $request->title ?? $category->title,
            'description' => $request->description ?? $category->description,
            'icon_path'   => $category->icon_path,
        ]);

        return [
            'data'    => $category->fresh(),
            'message' => 'Service category updated successfully',
            'code'    => 200,
        ];
    }
}
