<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FineResource;
use App\Models\Fine;
use App\Services\FineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FineController extends Controller
{
    public function __construct(
        private readonly FineService $fineService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user  = auth('sanctum')->user() ?? auth('member')->user();
        $fines = $this->fineService->getAllFines(
            $request->only(['is_paid']),
            $user
        );

        return response()->json([
            'data' => FineResource::collection($fines),
            'meta' => [
                'current_page' => $fines->currentPage(),
                'last_page'    => $fines->lastPage(),
                'per_page'     => $fines->perPage(),
                'total'        => $fines->total(),
            ],
        ]);
    }

    public function pay(Fine $fine): JsonResponse
    {
        if ($fine->is_paid) {
            return response()->json([
                'message' => 'Fine has already been paid.',
            ], 403);
        }

        $fine = $this->fineService->payFine($fine);

        return response()->json([
            'message' => 'Fine marked as paid.',
            'data'    => new FineResource($fine),
        ]);
    }
}