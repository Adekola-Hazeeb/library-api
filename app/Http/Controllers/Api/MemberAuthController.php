<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\MemberLoginRequest;
use App\Http\Resources\MemberResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MemberAuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    public function login( MemberLoginRequest $request): JsonResponse 
       {
        $result = $this->authService->memberLogin(
            $request->validated('email'),
            $request->validated('password')
        );

        return response()->json([
            'message' => 'Login successful.',
            'token' => $result['token'],
            'data' => new MemberResource(
                $result['member']
            ),
        ]);
    }

    public function me(): MemberResource
    {
        return new MemberResource(
            Auth::user()
        );
    }

    public function logout(): JsonResponse
    {
        Auth::user()
            ->tokens()
            ->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}