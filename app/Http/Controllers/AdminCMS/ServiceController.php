<?php

namespace App\Http\Controllers\AdminCMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCMS\ServiceRequest;
use App\Http\Requests\AdminCMS\UpdateServiceRequest;
use App\Http\Responses\Response;
use App\Services\AdminAuthCMS\ServiceService;
use Throwable;

class ServiceController extends Controller
{
    protected $serviceService;

    public function __construct(ServiceService $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    public function getAllServices()
    {
        try {
            $data = $this->serviceService->getAllServices();

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error fetching services', $th->getMessage(), 500);
        }
    }

    public function createService(ServiceRequest $request)
    {
        try {
            $data = $this->serviceService->createService($request);

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error creating service', $th->getMessage(), 500);
        }
    }

    public function updateService(UpdateServiceRequest $request, $id)
    {
        try {
            $data = $this->serviceService->updateService($request, $id);

            if ($data['code'] !== 200) {
                return Response::Error($data['message'], null, $data['code']);
            }

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error updating service', $th->getMessage(), 500);
        }
    }

    public function getServiceById($id)
    {
        try {
            $data = $this->serviceService->getServiceById($id);

            if ($data['code'] !== 200) {
                return Response::Error($data['message'], null, $data['code']);
            }

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error fetching service', $th->getMessage(), 500);
        }
    }

    public function deleteService($id)
    {
        try {
            $data = $this->serviceService->deleteService($id);

            if ($data['code'] !== 200) {
                return Response::Error($data['message'], $data['code']);
            }

            return Response::Success(null, $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error deleting service', $th->getMessage(), 500);
        }
    }
}
