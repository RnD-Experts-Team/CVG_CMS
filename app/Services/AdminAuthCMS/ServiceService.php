<?php

namespace App\Services\AdminAuthCMS;

use App\Models\Service;

class ServiceService
{
    /*
    |--------------------------------------------------------------------------
    | GET ALL
    |--------------------------------------------------------------------------
    */
    public function getAllServices()
    {
        $services = Service::with('image')->latest()->get();

        if ($services->isEmpty()) {
            return [
                'data' => null,
                'message' => 'No services found',
                'code' => 200,
            ];
        }

        return [
            'data' => $services,
            'message' => 'Services retrieved successfully',
            'code' => 200,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */
    public function createService($request)
    {
        $service = Service::create([
            'title' => $request->title,
            'description' => $request->description,
            'image_media_id' => $request->image_media_id,
            'featured' => $request->featured ?? 0,
        ]);

        return [
            'data' => $service->load('image'),
            'message' => 'Service created successfully',
            'code' => 201,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */
    public function updateService($request, $id)
    {
        $service = Service::find($id);

        if (! $service) {
            return [
                'data' => null,
                'message' => 'Service not found',
                'code' => 404,
            ];
        }

        $service->update([
            'title' => $request->title,
            'description' => $request->description,
            'image_media_id' => $request->image_media_id,
            'featured' => $request->featured ?? 0,
        ]);

        return [
            'data' => $service->load('image'),
            'message' => 'Service updated successfully',
            'code' => 200,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Get by id
    |--------------------------------------------------------------------------
    */
    public function getServiceById($id)
    {
        $service = Service::with('image')->find($id);

        if (! $service) {
            return [
                'data' => null,
                'message' => 'Service not found',
                'code' => 404,
            ];
        }

        return [
            'data' => $service,
            'message' => 'Service retrieved successfully',
            'code' => 200,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */
    public function deleteService($id)
    {
        $service = Service::find($id);

        if (! $service) {
            return [
                'data' => null,
                'message' => 'Service not found',
                'code' => 404,
            ];
        }

        $service->delete();

        return [
            'data' => null,
            'message' => 'Service deleted successfully',
            'code' => 200,
        ];
    }
}
