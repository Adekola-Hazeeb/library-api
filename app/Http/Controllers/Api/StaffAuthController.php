<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\StaffLoginRequest;
use App\Http\Resources\StaffResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffAuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /* Handle staff login */
    public function login(StaffLoginRequest $request): JsonResponse
    {
        $result = $this->authService->staffLogin(
            $request->validated('email'),
            $request->validated('password')
        );

        return response()->json([
            'message' => 'Login successful.',
            'token'   => $result['token'],
            'data'    => StaffResource::make($result['user']),
        ]);
    }

    /* Get authenticated staff profile */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => StaffResource::make($request->user()),
        ]);
    }

    /* Handle staff logout */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}