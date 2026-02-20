<?php

namespace App\Http\Controllers\AdminCMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCMS\CategoryRequest;
use App\Http\Responses\Response;
use App\Services\AdminAuthCMS\CategoryService;
use Throwable;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function getAllCategories()
    {
        try {
            $data = $this->categoryService->getAllCategories();

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error fetching categories', null, 500);
        }
    }

    public function createCategory(CategoryRequest $request)
    {
        try {
            $data = $this->categoryService->createCategory($request);

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error creating category', null, 500);
        }
    }

    public function updateCategory(CategoryRequest $request, $id)
    {
        try {
            $data = $this->categoryService->updateCategory($request, $id);

            if ($data['code'] !== 200) {
                return Response::Error($data['message'], null, $data['code']);
            }

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error updating category', null, 500);
        }
    }

    public function getCategoryById($id)
    {
        try {
            $data = $this->categoryService->getCategoryById($id);

            if ($data['code'] !== 200) {
                return Response::Error($data['message'], null, $data['code']);
            }

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error fetching category', null, 500);
        }
    }

    public function deleteCategory($id)
    {
        try {
            $data = $this->categoryService->deleteCategory($id);

            if ($data['code'] !== 200) {
                return Response::Error($data['message'], null, $data['code']);
            }

            return Response::Success(null, $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error deleting category', null, 500);
        }
    }
}
