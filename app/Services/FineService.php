<?php

namespace App\Services;

use App\Models\Fine;
use App\Models\Member;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class FineService
{
    public function getAllFines(array $filters, Member|User $user): LengthAwarePaginator
    {
        return Fine::query()
            ->with(['member', 'loan'])
            ->when($user instanceof Member, function ($query) use ($user) {
                $query->where('member_id', $user->id);
            })
            ->when(isset($filters['is_paid']), function ($query) use ($filters) {
                $query->where('is_paid', $filters['is_paid']);
            })
            ->paginate(15);
    }

    public function payFine(Fine $fine): Fine
    {
        /* Mark fine as paid */
        $fine->update([
            'is_paid' => true,
            'paid_at' => now(),
        ]);

        $remainingUnpaid = $fine->member->unpaidFinesAmount();

        if ($remainingUnpaid === 0 && $fine->member->isSuspended()) {
            $fine->member->update(['status' => 'active']);
        }

        return $fine->fresh(['member', 'loan']);
    }
}