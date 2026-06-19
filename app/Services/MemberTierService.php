<?php

namespace App\Services;

use App\Models\MemberTier;
use Illuminate\Pagination\LengthAwarePaginator;

class MemberTierService
{
    public function getAllTiers(): LengthAwarePaginator
    {
        return MemberTier::paginate(15);
    }

    public function createTier(array $data): MemberTier
    {
        return MemberTier::create($data);
    }
    public function updateTier(MemberTier $tier, array $data): MemberTier
    {
        $tier->update(array_filter([
            'name'             => $data['name'] ?? null,
            'max_books'        => $data['max_books'] ?? null,
            'loan_period_days' => $data['loan_period_days'] ?? null,
            'fine_rate'        => $data['fine_rate'] ?? null,
        ], fn($v) => !is_null($v)));

        return $tier->fresh();
    }
}