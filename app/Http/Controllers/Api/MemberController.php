<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Member\StoreMemberRequest;
use App\Http\Requests\Member\UpdateMemberRequest;
use App\Http\Requests\Member\UpgradeTierRequest;
use App\Http\Resources\MemberResource;
use App\Models\Member;
use App\Services\MemberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function __construct(
        private readonly MemberService $memberService
    ) {
    }
    public function index(Request $request): JsonResponse
    {
        $members = $this->memberService->getAllMembers(
            $request->only(['status', 'name'])
        );

        return response()->json([
            'data' => MemberResource::collection($members),
            'meta' => [
                'current_page' => $members->currentPage(),
                'last_page' => $members->lastPage(),
                'per_page' => $members->perPage(),
                'total' => $members->total(),
            ],
        ]);
    }

    /* GET /api/members/{member} — staff only */
    public function show(Member $member): JsonResponse
    {
        $member = $this->memberService->getMember($member);

        return response()->json([
            'data' => new MemberResource($member),
        ]);
    }

    public function store(StoreMemberRequest $request): JsonResponse
    {
        $member = $this->memberService->createMember($request->validated());

        return response()->json([
            'message' => 'Member registered successfully.',
            'data' => new MemberResource($member),
        ], 201);
    }

    public function update(UpdateMemberRequest $request, Member $member): JsonResponse
    {
        $authMember = auth('member')->user();

        if ($authMember->id !== $member->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $member = $this->memberService->updateMember($member, $request->validated());

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => new MemberResource($member),
        ]);
    }

    public function suspend(Member $member): JsonResponse
    {
        $member = $this->memberService->suspendMember($member);

        return response()->json([
            'message' => 'Member suspended successfully.',
            'data' => new MemberResource($member),
        ]);
    }

    public function reinstate(Member $member): JsonResponse
    {
        $member = $this->memberService->reinstateMember($member);

        return response()->json([
            'message' => 'Member reinstated successfully.',
            'data' => new MemberResource($member),
        ]);
    }
    public function upgradeTier(UpgradeTierRequest $request, Member $member): JsonResponse
    {
        $member = $this->memberService->upgradeTier(
            $member,
            $request->validated()['member_tier_id']
        );

        return response()->json([
            'message' => 'Member tier upgraded successfully.',
            'data' => new MemberResource($member),
        ]);
    }
}