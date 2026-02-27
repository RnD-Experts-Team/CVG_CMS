<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\UpdateRequest;
use App\Http\Responses\Response;
use App\Services\Auth\AuthService;
use Throwable;

class AuthController extends Controller
{
    // Inject the AuthService into the controller
    public $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    // Login method
    public function login(LoginRequest $request)
    {
        $data = [];
        try {
            // Call the login logic from AuthService
            $data = $this->authService->login($request);

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error Message', $th->getMessage());
        }
    }

    // Logout method
    public function logout()
    {
        $data = [];
        try {
            // Call the logout logic from AuthService
            $data = $this->authService->logout(request());

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error Message', $th->getMessage());
        }
    }

    public function updateData(UpdateRequest $request)
    {
        $data = [];
        try {
            // Call the update logic from AuthService
            $data = $this->authService->update($request);

            return Response::Success($data['data'], $data['message'], $data['code']);
        } catch (Throwable $th) {
            return Response::Error('Error Message', $th->getMessage());
        }
    }
}
