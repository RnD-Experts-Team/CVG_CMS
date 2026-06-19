<?php

namespace App\Http\Controllers\AdminCMS;

use App\Http\Controllers\Controller;
use App\Http\Responses\Response;
use App\Services\AdminAuthCMS\ServiceCategoryService;
use Illuminate\Http\Request;
use Throwable;

class ServiceCategoryController extends Controller
{
    protected $service;

    public function __construct(ServiceCategoryService $service)
    {
        $this->service = $service;
    }

    public function getServiceCategories()
    {
        try {
            $data = $this->service->getServiceCategories();

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error fetching service categories', $th->getMessage(), 500);
        }
    }

    public function getServiceCategoryById($id)
    {
        try {
            $data = $this->service->getServiceCategoryById($id);

            if ($data['code'] !== 200) {
                return Response::Error($data['message'], null, $data['code']);
            }

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error fetching service category', $th->getMessage(), 500);
        }
    }

    public function updateServiceCategory(Request $request, $id)
    {
        try {
            $request->validate([
                'title'       => 'nullable|string|max:255',
                'description' => 'nullable|string',
                'icon'        => 'nullable|file|image|max:4096',
            ]);

            $data = $this->service->updateServiceCategory($request, $id);

            if ($data['code'] !== 200) {
                return Response::Error($data['message'], null, $data['code']);
            }

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error updating service category', $th->getMessage(), 500);
        }
    }
}
