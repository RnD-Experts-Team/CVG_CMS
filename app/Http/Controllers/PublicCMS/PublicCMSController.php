<?php

namespace App\Http\Controllers\PublicCMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublicCMS\ContactFormRequest;
use App\Http\Responses\Response;
use App\Services\PublicCMS\PublicCMSService;
use Throwable;

class PublicCMSController extends Controller
{
    protected $publicCMSService;

    public function __construct(PublicCMSService $publicCMSService)
    {
        $this->publicCMSService = $publicCMSService;
    }

    // Get homepage data
    public function getHomeData()
    {
        try {
            $data = $this->publicCMSService->getHomeData();

            return Response::Success($data, 'Homepage data fetched successfully');
        } catch (Throwable $th) {
            return Response::Error('Error fetching homepage data', $th->getMessage());
        }
    }

    // Get categories
    public function getCategories()
    {
        try {
            $data = $this->publicCMSService->getCategories();

            return Response::Success($data, 'Categories fetched successfully');
        } catch (Throwable $th) {
            return Response::Error('Error fetching categories', $th->getMessage());
        }
    }

    // Get paginated projects
    public function getProjects()
    {
        try {
            $data = $this->publicCMSService->getProjects();

            return Response::Success($data, 'Projects fetched successfully');
        } catch (Throwable $th) {
            return Response::Error('Error fetching projects', $th->getMessage());
        }
    }

    // Get projects by category slug
    public function getProjectsByCategory($slug)
    {
        try {
            $data = $this->publicCMSService->getProjectsByCategory($slug);

            return Response::Success($data['data'], $data['message'], $data['code'] ?? 200);
        } catch (Throwable $th) {
            return Response::Error('Error fetching projects by category', $th->getMessage());
        }
    }

    // Get a single project by slug
    public function getProjectBySlug($slug)
    {
        try {
            $data = $this->publicCMSService->getProjectBySlug($slug);

            return Response::Success($data['data'], $data['message'], $data['code'] ?? 200);
        } catch (Throwable $th) {
            return Response::Error('Error fetching project details', $th->getMessage());
        }
    }

    // Get paginated services
    public function getServices()
    {
        try {
            $data = $this->publicCMSService->getServices();

            return Response::Success($data['data'], $data['message']);
        } catch (Throwable $th) {
            return Response::Error('Error fetching services', $th->getMessage());
        }
    }

    // Submit the contact form
    public function submitContactForm(ContactFormRequest $request)
    {
        try {
            $data = $this->publicCMSService->submitContactForm($request);

            return Response::Success($data['data'], $data['message']);
        } catch (Throwable $th) {
            return Response::Error('Error submitting contact form', $th->getMessage());
        }
    }
}
