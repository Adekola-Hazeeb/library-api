<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MemberTier\StoreMemberTierRequest;
use App\Http\Requests\MemberTier\UpdateMemberTierRequest;
use App\Http\Resources\MemberTierResource;
use App\Models\MemberTier;
use App\Services\MemberTierService;
use Illuminate\Http\JsonResponse;

class MemberTierController extends Controller
{
    public function __construct(
        private readonly MemberTierService $memberTierService
    ) {
    }

    public function index(): JsonResponse
    {
        $tiers = $this->memberTierService->getAllTiers();

        return response()->json([
            'data' => MemberTierResource::collection($tiers),
            'meta' => [
                'current_page' => $tiers->currentPage(),
                'last_page' => $tiers->lastPage(),
                'per_page' => $tiers->perPage(),
                'total' => $tiers->total(),
            ],
        ]);
    }
    public function store(StoreMemberTierRequest $request): JsonResponse
    {
        $tier = $this->memberTierService->createTier($request->validated());

        return response()->json([
            'message' => 'Member tier created successfully.',
            'data' => new MemberTierResource($tier),
        ], 201);
    }
    public function update(UpdateMemberTierRequest $request, MemberTier $memberTier): JsonResponse
    {
        $tier = $this->memberTierService->updateTier($memberTier, $request->validated());

        return response()->json([
            'message' => 'Member tier updated successfully.',
            'data' => new MemberTierResource($tier),
        ]);
    }
}