<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthService
{
    // Login logic
    public function login($request)
    {
        // Get the login credentials from the request
        $credentials = $request->only('email', 'password');

        // Attempt to authenticate the user using credentials
        if (! Auth::attempt($credentials)) {
            // If authentication fails, return an error response
            return [
                'data' => null,
                'message' => 'Invalid credentials',
                'code' => 401,
            ];
        }

        // If authentication is successful, get the user
        $user = $request->user();

        // Generate a new token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        // Return the successful response with user details and token
        return [
            'data' => [
                'user' => $user,
                'token' => $token,
            ],
            'message' => 'Login successful',
            'code' => 200,
        ];
    }

    // Logout logic (invalidate the token)
    public function logout($request)
    {
        // Get the currently authenticated user
        $user = $request->user();

        // Revoke all of the user's tokens (log the user out from all devices)
        $user->tokens->each(function ($token) {
            $token->delete();
        });

        // Return success response
        return [
            'data' => null,
            'message' => 'Logged out successfully',
            'code' => 200,
        ];
    }
}
