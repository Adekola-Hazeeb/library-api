<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MemberTier;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class MemberService
{
    /* Get paginated members — staff only */
    public function getAllMembers(array $filters): LengthAwarePaginator
    {
        return Member::query()
            ->with('memberTier')
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['name']), function ($q) use ($filters) {
                $q->where(function ($q2) use ($filters) {
                    $q2->where('first_name', 'like', '%' . $filters['name'] . '%')
                        ->orWhere('last_name', 'like', '%' . $filters['name'] . '%');
                });
            })
            ->paginate(15);
    }
    public function getMember(Member $member): Member
    {
        return $member->load('memberTier');
    }

    public function createMember(array $data): Member
    {
        $member = Member::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone_number' => $data['phone_number'] ?? null,
            'member_tier_id' => $data['member_tier_id'],
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return $member->load('memberTier');
    }

    public function updateMember(Member $member, array $data): Member
    {

        $member->update(array_filter([
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
        ], fn($v) => !is_null($v)));

        return $member->fresh('memberTier');
    }

    public function suspendMember(Member $member): Member
    {
        $member->update(['status' => 'suspended']);
        return $member->fresh();
    }

    /* Reinstate a suspended member */
    public function reinstateMember(Member $member): Member
    {
        $member->update(['status' => 'active']);
        return $member->fresh();
    }
    /* Upgrade member tier */
    public function upgradeTier(Member $member, int $tierId): Member
    {
        $tier = MemberTier::findOrFail($tierId);

        $member->update(['member_tier_id' => $tier->id]);

        return $member->fresh('memberTier');
    }
}