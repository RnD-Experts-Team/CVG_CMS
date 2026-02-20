<?php

namespace App\Http\Controllers\AdminCMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCMS\ProjectRequest;
use App\Http\Responses\Response;
use App\Services\AdminAuthCMS\ProjectService;
use Throwable;

class ProjectController extends Controller
{
    private $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    // Create a new Project
    public function createProject(ProjectRequest $request)
    {
        try {
            $data = $this->projectService->createProject($request);

            // Check if the data contains a 'code' field
            if (isset($data['code']) && ! $data['code']) {
                return Response::Error($data['message'], $data['code']);
            }

            // Return the success response
            return Response::Success($data, 'Project created successfully', 201);
        } catch (Throwable $th) {
            return Response::Error('Error creating project', $th->getMessage());
        }
    }

    public function getProjects()
    {
        try {
            $data = $this->projectService->getAllProjects();

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error fetching projects section', $th->getMessage());
        }
    }

    public function updateProject(ProjectRequest $request, $id)
    {
        try {
            $data = $this->projectService->updateProject($request, $id);

            if (isset($data['status']) && $data['status'] === false) {
                return Response::Error($data['message'], $data['code']);
            }

            return Response::Success($data, 'Project updated successfully');

        } catch (Throwable $th) {
            return Response::Error('Error updating project', 500);
        }
    }

    public function getProjectById($id)
    {
        try {
            $data = $this->projectService->getProjectById($id);

            if (isset($data['status']) && $data['status'] === false) {
                return Response::Error($data['message'], $data['code']);
            }

            return Response::Success($data, 'Project retrieved successfully');

        } catch (Throwable $th) {
            return Response::Error('Error fetching project', 500);
        }
    }

    public function deleteProject($id)
    {
        try {
            $data = $this->projectService->deleteProject($id);

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error deleting project', 500);
        }
    }
}
