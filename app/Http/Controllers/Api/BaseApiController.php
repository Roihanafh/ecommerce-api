<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class BaseApiController extends Controller
{
    protected function successResponse(mixed $data, string $key = 'data', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            $key => $data,
        ], $status);
    }

    protected function createdResponse(mixed $data, string $message, string $key = 'data'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            $key => $data,
        ], 201);
    }

    protected function messageResponse(string $message, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], $status);
    }

    protected function updatedResponse(mixed $data, string $message, string $key = 'data'): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            $key => $data,
        ]);
    }

    protected function errorResponse(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
