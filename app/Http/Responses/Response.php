<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;

class Response
{
    public static function Success(mixed $data = null, ?string $message = null, int $code = 200): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $data ?? (object) [],
        ];

        // ✅ message optional (especially for GET)
        if ($message !== null) {
            $response['message'] = $message;
        }

        // ✅ meta ONLY used for pagination → do NOT include it here
        return response()->json($response, $code);
    }

    public static function Error($message, $errors = null, int $code = 500): \Illuminate\Http\JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        // ✅ Only include "errors" for validation (or field-specific) errors
        if (! empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }

    public static function paginate($paginator, ?string $message = null, int $code = 200): JsonResponse
    {
        // ✅ Laravel default pagination structure must be inside "data"
        $response = [
            'success' => true,
            'data' => $paginator->toArray(),
        ];

        // ✅ message is optional
        if ($message !== null) {
            $response['message'] = $message;
        }

        // ✅ meta is ONLY used for pagination
        // Your docs didn't require extra meta fields, so keep it empty object
        $response['meta'] = (object) [];

        return response()->json($response, $code);
    }

    public static function Validation($errors, $message, $code = 422): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors, // Return the errors array as required
        ], $code);
    }
}
