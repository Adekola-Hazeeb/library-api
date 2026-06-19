<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StoreStaffRequest;
use App\Http\Requests\Staff\UpdateStaffRequest;
use App\Http\Resources\StaffResource;
use App\Models\User;
use App\Services\StaffService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function __construct(
        private readonly StaffService $staffService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $staff = $this->staffService->getAllStaff(
            $request->only(['role', 'name'])
        );

        return response()->json([
            'data' => StaffResource::collection($staff),
            'meta' => [
                'current_page' => $staff->currentPage(),
                'last_page'    => $staff->lastPage(),
                'per_page'     => $staff->perPage(),
                'total'        => $staff->total(),
            ],
        ]);
    }
    public function store(StoreStaffRequest $request): JsonResponse
    {
        $staff = $this->staffService->createStaff($request->validated());

        return response()->json([
            'message' => 'Staff member created successfully.',
            'data'    => new StaffResource($staff),
        ], 201);
    }
    public function update(UpdateStaffRequest $request, User $staff): JsonResponse
    {
        $staff = $this->staffService->updateStaff($staff, $request->validated());

        return response()->json([
            'message' => 'Staff member updated successfully.',
            'data'    => new StaffResource($staff),
        ]);
    }
    public function destroy(User $staff): JsonResponse
    {
        $this->staffService->deleteStaff($staff);

        return response()->json([
            'message' => 'Staff member deleted successfully.',
        ]);
    }
}